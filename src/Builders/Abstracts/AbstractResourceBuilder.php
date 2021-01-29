<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Abstracts;

use CarloNicora\JsonApi\Objects\Relationship;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\AttributeBuilderFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\MetaBuilderFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\RelationshipBuilderInterfaceFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ElementBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\MetaBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Traits\LinkBuilderTrait;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Traits\ReadFunctionTrait;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\LinkCreatorInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use Exception;
use RuntimeException;

abstract class AbstractResourceBuilder implements ResourceBuilderInterface, LinkCreatorInterface
{
    use LinkBuilderTrait;
    use ReadFunctionTrait;

    /** @var RelationshipBuilderInterfaceFactory  */
    protected RelationshipBuilderInterfaceFactory $relationshipBuilderInterfaceFactory;

    /** @var ServicesProxy|null  */
    private static ?ServicesProxy $staticServicesProxy=null;

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
    protected array $meta = [];

    /** @var AttributeBuilderFactory */
    private AttributeBuilderFactory $attributeBuilderFactory;

    /** @var MetaBuilderFactory  */
    private MetaBuilderFactory $metaBuilderFactory;

    /** @var string|null  */
    protected ?string $dataCache=null;

    /** @var string|null  */
    protected ?string $resourceCache=null;

    /**
     * @param ServicesProxy $servicesProxy
     */
    public static function initialise(
        ServicesProxy $servicesProxy,
    ) : void
    {
        self::$staticServicesProxy = $servicesProxy;
    }

    /**
     * AbstractResourceBuilder constructor.
     * @param ServicesProxy $servicesProxy
     */
    public function __construct(
        protected ServicesProxy $servicesProxy,
    )
    {
        $this->attributeBuilderFactory = new AttributeBuilderFactory($this);
        $this->metaBuilderFactory = new MetaBuilderFactory($this);

        $this->setAttributes();
        $this->setLinks();
        $this->setMeta();

        $this->relationshipBuilderInterfaceFactory = new RelationshipBuilderInterfaceFactory(
            servicesProxy: $this->servicesProxy
        );
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
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array
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

        $this->servicesProxy->getCacheFacade()->setResourceBuilder($this);
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
            $resourceBuilderFactory = new ResourceBuilderFactory(
                servicesProxy: self::$staticServicesProxy
            );
            $resourceBuilder = $resourceBuilderFactory->createResourceBuilder(static::class);

            return $resourceBuilder->getTableName();
        } catch (Exception) {
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
        if (($response = self::$staticServicesProxy->getCacheFacade()->getAttributeBuilder(static::class, $attributeName)) === null)
        {
            $resourceBuilderFactory = new ResourceBuilderFactory(
                servicesProxy: self::$staticServicesProxy
            );
            $resourceBuilder = $resourceBuilderFactory->createResourceBuilder(static::class);

            $response = $resourceBuilder->getAttribute($attributeName);
        }

        if ($response === null) {
            throw new RuntimeException('Attribute not found', $attributeName);
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
        $resourceBuilderFactory = new ResourceBuilderFactory(
            servicesProxy: self::$staticServicesProxy
        );
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
    public function getRelationship(
        string $relationshipName
    ): ?RelationshipBuilderInterface
    {
        return $this->relationships[$relationshipName] ?? null;
    }

    /**
     * @param string $attributeName
     * @return AttributeBuilderInterface|null
     */
    public function getAttribute(
        string $attributeName
    ): ?AttributeBuilderInterface
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
    final protected function generateAttribute(
        string $attributeName
    ): AttributeBuilderInterface
    {
        $response = $this->attributeBuilderFactory->create($attributeName);

        $this->attributes[$attributeName] = $response;

        return $response;
    }

    /**
     * @param string $metaName
     * @param int $positioning
     * @return ElementBuilderInterface
     */
    final protected function generateMeta(
        string $metaName,
        int $positioning
    ): ElementBuilderInterface
    {
        $response = $this->metaBuilderFactory->create($metaName, $positioning);

        $this->meta[$metaName] = $response;

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
        $this->buildMeta($response, $data, ($positionInRelationship === []));
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
            } catch (Exception) {}
            array_pop($positionInRelationship);
        }
    }

    /**
     * @param ResourceObject $response
     * @param array $data
     * @throws Exception
     */
    private function buildAttributes(
        ResourceObject $response,
        array $data
    ): void
    {
        foreach ($this->attributes as $attribute) {
            if (!$attribute->isWriteOnly()){
                if ($attribute->getName() === 'id'){
                    $response->id = $this->getElementValue($attribute, $data);
                } else {
                    $response->attributes->add(
                        $attribute->getName(),
                        $this->getElementValue($attribute, $data)
                    );
                }
            }
        }
    }

    /**
     * @param ResourceObject $response
     * @param array $data
     * @param bool $isResource
     * @throws Exception
     */
    private function buildMeta(
        ResourceObject $response,
        array $data,
        bool $isResource
    ): void
    {
        /** @var MetaBuilderInterface $meta */
        foreach ($this->meta as $meta) {
            if ($meta->getPositioning() === MetaBuilderInterface::ALL
                ||
                ($meta->getPositioning() === MetaBuilderInterface::RESOURCE && $isResource)
                ||
                ($meta->getPositioning() === MetaBuilderInterface::RELATIONSHIP && !$isResource)
            )
            {
                $response->meta->add(
                    $meta->getName(),
                    $this->getElementValue($meta, $data)
                );
            }
        }
    }

    /**
     * @param ElementBuilderInterface $element
     * @param array $data
     * @return mixed
     * @throws Exception
     */
    private function getElementValue(
        ElementBuilderInterface $element,
        array $data
    ): mixed
    {
        $response = $element->getStaticValue() ?? $data[$element->getDatabaseFieldName()] ?? null;

        if ($element->isEncrypted()){
            if ($response !== null && $this->servicesProxy->getEncrypter() !== null) {
                $response = $this->servicesProxy->getEncrypter()->encryptId(
                    $response
                );
            }
        } elseif ($element->getTransformationClass() !== null && $element->getTransformationMethod() !== null) {
            $transformator = $this->servicesProxy->getBuilderTransformator($element->getTransformationClass());
            $response = $transformator->transform(
                $element->getTransformationMethod(),
                $data,
                $element->getDatabaseFieldName()
            );
        }

        return $response;
    }
}