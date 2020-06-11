<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts;

use CarloNicora\JsonApi\Objects\Relationship;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Events\MinimalismInfoEvents;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\AttributeBuilderFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\RelationshipBuilderFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipTypeInterface;
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

    /** @var AttributeBuilderFactory */
    private AttributeBuilderFactory $attributeBuilderFactory;

    /** @var RelationshipBuilderFactory */
    private RelationshipBuilderFactory $relationshipBuilderFactory;

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
        $this->relationshipBuilderFactory = new RelationshipBuilderFactory($services);

        $this->services = $services;
        $this->mapper = $services->service(JsonDataMapper::class);

        $this->setAttributes();
        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Object Attributes Created (' . get_class($this) . ')'));
        $this->setLinks();
        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Object Links Created (' . get_class($this) . ')'));
    }

    /**
     * @return array|AttributeBuilderInterface[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
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

        return clone $resourceBuilder->relationships[$relationshipName]->getAttribute();
    }

    /**
     *
     */
    abstract protected function setAttributes(): void;

    /**
     *
     */
    abstract protected function setLinks(): void;

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
        if (!array_key_exists($relationshipName, $this->relationships)) {
            return null;
        }

        return $this->relationships[$relationshipName];
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
     * @param string $name
     * @param int $type
     * @param AttributeBuilderInterface $attribute
     * @param string $fieldName
     * @param string|null $manyToManyRelationshipTableName
     * @param string|null $manyToManyRelationshipField
     * @return RelationshipBuilderInterface
     * @throws Exception
     */
    final protected function generateRelationship(string $name, int $type, AttributeBuilderInterface $attribute, string $fieldName=null, string $manyToManyRelationshipTableName=null, string $manyToManyRelationshipField=null): RelationshipBuilderInterface
    {
        $response = $this->relationshipBuilderFactory->create($name, $type, $attribute, $fieldName, $manyToManyRelationshipTableName, $manyToManyRelationshipField);

        $this->relationships[$name] = $response;

        return $response;
    }

    /**
     * @param array $data
     * @param bool $loadRelationships
     * @return ResourceObject
     * @throws Exception
     */
    final public function buildResourceObject(array $data, bool $loadRelationships = false): ResourceObject
    {
        $response = new ResourceObject($this->type);

        $this->buildAttributes($response, $data);
        $this->buildLinks($this, $this, $response->links, $data);

        if ($loadRelationships){
            $this->buildRelationships($response, $data);
        }

        return $response;
    }

    /**
     * @param ResourceObject $response
     * @param array $data
     * @throws Exception
     */
    private function buildRelationships(ResourceObject $response, array $data): void
    {
        foreach ($this->relationships as $relationship){
            $relation = new Relationship();

            if ($relationship->getReadFunction() !== null) {
                $relation->resourceLinkage->resources = $this->mapper->generateResourceObjectsByFunction(
                    $relationship->getResourceBuilderName(),
                    $relationship->getReadFunction(),
                    $data
                );
            } else {
                switch ($relationship->getType()) {
                    case RelationshipTypeInterface::RELATIONSHIP_ONE_TO_ONE:
                        $relation->resourceLinkage->resources = $this->mapper->generateResourceObjectByFieldValue(
                            $relationship->getResourceBuilderName(),
                            $relationship->getAttribute(),
                            $data[$relationship->getAttribute()->getDatabaseFieldName()]
                        );
                        break;
                    case RelationshipTypeInterface::RELATIONSHIP_ONE_TO_MANY:
                        $relation->resourceLinkage->resources = $this->mapper->generateResourceObjectByFieldValue(
                            $relationship->getResourceBuilderName(),
                            $relationship->getAttribute(),
                            $data[$relationship->getAttribute()->getDatabaseFieldRelationship()]
                        );
                        break;
                    case RelationshipTypeInterface::RELATIONSHIP_MANY_TO_MANY:
                        $relation->resourceLinkage->resources = $this->mapper->generateResourceObjectsByFunction(
                            $relationship->getResourceBuilderName(),
                            'getFirstLevelJoin',
                            [
                                $relationship->getManyToManyRelationshipTableName(),
                                $relationship->getAttribute()->getDatabaseFieldRelationship(),
                                $relationship->getManyToManyRelationshipField(),
                                $data[$relationship->getAttribute()->getDatabaseFieldRelationship()]
                            ]
                        );

                        break;
                }
            }

            $this->buildLinks($relationship, $this, $relation->links, $data);

            $response->relationships[$relationship->getName()] = $relation;
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
     * @param AttributeBuilderInterface $attribute
     * @param array $data
     * @return mixed
     */
    private function getAttributeValue(AttributeBuilderInterface $attribute, array $data)
    {
        $response = $data[$attribute->getDatabaseFieldName()] ?? null;

        if ($attribute->isEncrypted()){
            if (($encrypter = $this->mapper->getDefaultEncrypter()) !== null){
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