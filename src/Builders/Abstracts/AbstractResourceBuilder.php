<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts;

use CarloNicora\JsonApi\Objects\Relationship;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Events\MinimalismInfoEvents;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\Cacher\Factories\CacheFactory;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheFactoryInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\AttributeBuilderFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\RelationshipBuilderInterfaceFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Traits\LinkBuilderTrait;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Traits\ReadFunctionTrait;
use CarloNicora\Minimalism\Services\JsonDataMapper\Events\JsonDataMapperErrorEvents;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\LinkCreatorInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\TransformatorInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use Exception;

abstract class AbstractResourceBuilder implements ResourceBuilderInterface, LinkCreatorInterface
{
    use LinkBuilderTrait;
    use ReadFunctionTrait;

    /** @var RelationshipBuilderInterfaceFactory  */
    protected RelationshipBuilderInterfaceFactory $relationshipBuilderInterfaceFactory;

    /** @var ServicesFactory|null  */
    private static ?ServicesFactory $staticServices=null;

    /** @var JsonDataMapper|null  */
    private static ?JsonDataMapper $staticMapper=null;

    /** @var array  */
    private static array $fieldCache = [];

    /** @var array  */
    private static array $relationshipFieldCache = [];

    /** @var string */
    public string $type;

    /** @var string|null */
    public ?string $tableName = null;

    /** @var array|AttributeBuilderInterface[] */
    protected array $attributes = [];

    /** @var array|RelationshipBuilderInterface[] */
    protected array $relationships = [];

    /** @var array  */
    protected array $meta = [
        'base' => [],
        'relationship' => []
    ];

    /** @var AttributeBuilderFactory */
    private AttributeBuilderFactory $attributeBuilderFactory;

    /** @var string|null  */
    protected ?string $dataCache=null;

    /** @var string|null  */
    protected ?string $resourceCache=null;

    /** @var CacheFactoryInterface|null  */
    protected ?CacheFactoryInterface $cacheFactory=null;

    /**
     * @param ServicesFactory $services
     * @throws Exception
     */
    public static function initialise(ServicesFactory $services) : void
    {
        self::$staticServices = $services;
        self::$staticMapper = $services->service(JsonDataMapper::class);
    }

    /**
     * AbstractResourceBuilder constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->attributeBuilderFactory = new AttributeBuilderFactory($services, $this);

        $this->services = $services;
        $this->mapper = $services->service(JsonDataMapper::class);

        $this->cacheFactory = new CacheFactory();

        $this->setAttributes();
        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Object Attributes Created (' . get_class($this) . ')'));
        $this->setLinks();
        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Object Links Created (' . get_class($this) . ')'));
        $this->setMeta();
        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Object Meta Created (' . get_class($this) . ')'));

        $this->relationshipBuilderInterfaceFactory = new RelationshipBuilderInterfaceFactory($this->services);
    }

    /**
     * @param CacheFactoryInterface $cacheFactory
     */
    public function setCacheFactoryInterface(CacheFactoryInterface $cacheFactory): void
    {
        $this->cacheFactory = $cacheFactory;
    }

    /**
     * @return string|null
     */
    public function getDataCacheName(): ?string
    {
        return $this->dataCache;
    }

    /**
     * @return string|null
     */
    public function getResourceCacheName(): ?string
    {
        return $this->resourceCache;
    }

    /**
     * @return array|AttributeBuilderInterface[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array|AttributeBuilderInterface[]
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @return array
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     *
     * @throws Exception
     */
    public function initialiseRelationships(): void
    {
        $this->setRelationships();

        /** @var RelationshipBuilderInterface $relationship */
        foreach ($this->getRelationships() as $relationship) {
            if ($relationship->getAttribute() !== null) {
                $relationship->getAttribute()->setRelationshipResource($this);
            }
        }

        $this->mapper->getCache()->setResourceBuilder($this);

        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Object Relationships Created (' . get_class($this) . ')'));
    }

    /**
     * @return AttributeBuilderInterface
     * @throws Exception
     */
    public static function attributeId() : AttributeBuilderInterface
    {
        return self::attribute('id');
    }

    /**
     * @param string $tableName
     */
    final protected function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @return string
     */
    final public static function tableName() : string
    {
        try {
            $resourceBuilderFactory = new ResourceBuilderFactory(self::$staticServices);
            $resourceBuilder = $resourceBuilderFactory->createResourceBuilder(static::class);

            return $resourceBuilder->getTableName();
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param string $attributeName
     * @return AttributeBuilderInterface
     * @throws Exception
     */
    protected static function attribute(string $attributeName) : AttributeBuilderInterface
    {
        if (($response = self::$staticMapper->getCache()->getAttributeBuilder(static::class, $attributeName)) === null)
        {
            $resourceBuilderFactory = new ResourceBuilderFactory(self::$staticServices);
            $resourceBuilder = $resourceBuilderFactory->createResourceBuilder(static::class);

            $response = $resourceBuilder->getAttribute($attributeName);
        }

        if ($response === null) {
            self::$staticServices->logger()->error()->log(
                JsonDataMapperErrorEvents::NO_ATTRIBUTE_FOUND(static::class, $attributeName)
            )->throw();
        }

        return clone $response;
    }

    /**
     * @param string $relationshipName
     * @return AttributeBuilderInterface
     * @throws Exception
     */
    protected static function relationship(string $relationshipName) : AttributeBuilderInterface
    {
        $resourceBuilderFactory = new ResourceBuilderFactory(self::$staticServices);
        $resourceBuilder = $resourceBuilderFactory->createResourceBuilder(static::class);
        /** @var RelationshipBuilderInterface $relationshipBuilder */
        $relationshipBuilder = $resourceBuilder->relationships[$relationshipName];

        return clone $relationshipBuilder->getAttribute();
    }

    /**
     *
     */
    protected function setAttributes(): void {}

    /**
     *
     */
    protected function setLinks(): void {}

    /**
     *
     */
    protected function setMeta(): void {}

    /**
     *
     */
    abstract protected function setRelationships(): void;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * @return string|null
     */
    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    /**
     * @param string $relationshipName
     * @return RelationshipBuilderInterface|null
     */
    public function getRelationship(string $relationshipName): ?RelationshipBuilderInterface
    {
        return $this->relationships[$relationshipName] ?? null;
    }

    /**
     * @param string $attributeName
     * @return AttributeBuilderInterface|null
     */
    public function getAttribute(string $attributeName): ?AttributeBuilderInterface
    {
        if (array_key_exists($attributeName, $this->attributes)){
            return $this->attributes[$attributeName];
        }

        if (array_key_exists($attributeName, $this->relationships)){
            return $this->relationships[$attributeName]->getAttribute();
        }

        return null;
    }


    /**
     * @param string $attributeName
     * @return AttributeBuilderInterface
     */
    final protected function generateAttribute(string $attributeName): AttributeBuilderInterface
    {
        $response = $this->attributeBuilderFactory->create($attributeName);

        $this->attributes[$attributeName] = $response;

        return $response;
    }

    /**
     * @param RelationshipBuilderInterface $relationshipBuilder
     */
    final protected function addRelationship(
        RelationshipBuilderInterface $relationshipBuilder
    ): void
    {
        $this->relationships[$relationshipBuilder->getName()] = $relationshipBuilder;
    }

    /**
     * @param array $data
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return ResourceObject
     * @throws Exception
     */
    final public function buildResourceObject(
        array $data,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): ResourceObject
    {
        $response = new ResourceObject($this->type);

        $this->buildAttributes($response, $data);
        $this->buildMeta($response, ($positionInRelationship !== []));
        $this->buildLinks($this, $this, $response->links, $data, $response);

        if ($loadRelationshipsLevel > 0){
            $this->buildRelationships($response, $data, $loadRelationshipsLevel, $relationshipParameters, $positionInRelationship);
        }

        return $response;
    }

    /**
     * @param ResourceObject $response
     * @param array $data
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     */
    private function buildRelationships(
        ResourceObject $response,
        array $data,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): void
    {
        /** @var RelationshipBuilderInterface $relationshipBuilder */
        foreach ($this->relationships as $relationshipBuilder){
            $positionInRelationship[] = $relationshipBuilder->getBuilder();
            try {
                $relation = new Relationship();

                $resources = $relationshipBuilder->loadResources(
                    $data,
                    $loadRelationshipsLevel,
                    $relationshipParameters,
                    $positionInRelationship
                );

                if ($resources !== null) {
                    $relation->resourceLinkage->resources = $resources;

                    $this->buildLinks(
                        $relationshipBuilder,
                        $this,
                        $relation->links,
                        $data
                    );

                    $response->relationships[$relationshipBuilder->getName()] = $relation;
                }
            } catch (Exception $e) {}
            array_pop($positionInRelationship);
        }
    }

    /**
     * @param ResourceObject $response
     * @param array $data
     * @throws Exception
     */
    private function buildAttributes(ResourceObject $response, array $data): void
    {
        foreach ($this->attributes as $attribute) {
            if (!$attribute->isWriteOnly()){
                if ($attribute->getName() === 'id'){
                    $response->id = $this->getAttributeValue($attribute, $data);
                } else {
                    $response->attributes->add(
                        $attribute->getName(),
                        $this->getAttributeValue($attribute, $data)
                    );
                }
            }
        }
    }

    /**
     * @param ResourceObject $response
     * @param bool $isRelationship
     * @throws Exception
     */
    private function buildMeta(ResourceObject $response, bool $isRelationship=false): void
    {
        $meta = $this->getMeta();
        if ($isRelationship) {
            $meta = $meta['relationship'];
        } else {
            $meta = $meta['base'];
        }

        foreach ($meta ?? [] as $singleMeta) {
            $response->meta->add(
                $singleMeta['name'],
                $singleMeta['value']
            );
        }
    }

    /**
     * @param AttributeBuilderInterface $attribute
     * @param array $data
     * @return mixed
     */
    private function getAttributeValue(AttributeBuilderInterface $attribute, array $data)
    {
        $response = $attribute->getStaticValue() ?? $data[$attribute->getDatabaseFieldName()] ?? null;

        if ($attribute->isEncrypted()){
            /** @var EncrypterInterface $encrypter */
            if ($response !== null && ($encrypter = $this->mapper->getDefaultEncrypter()) !== null) {
                $response = $encrypter->encryptId(
                    $response
                );
            }
        } elseif ($attribute->getTransformationClass() !== null && $attribute->getTransformationMethod() !== null) {
            $transformatorClass = $attribute->getTransformationClass();

            /** @var TransformatorInterface $transformator */
            $transformator = new $transformatorClass($this->services);
            $response = $transformator->transform(
                $attribute->getTransformationMethod(),
                $data,
                $attribute->getDatabaseFieldName()
            );
        } elseif (($type = $attribute->getType()) !== null) {
            $response = $type->transformValue($response);
        }

        return $response;
    }
}