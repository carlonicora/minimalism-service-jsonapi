<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\AttributeBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\LinkBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\OneToOneRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\ParametersFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Traits\LinkBuilderTrait;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Traits\ReadFunctionTrait;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\DataLoaderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;
use ReflectionClass;
use RuntimeException;

abstract class AbstractRelationshipBuilder implements RelationshipBuilderInterface
{
    use ReadFunctionTrait;
    use LinkBuilderTrait;

    /** @var ServicesFactory */
    protected ServicesFactory $services;

    /** @var MySQL $mysql */
    protected MySQL $mysql;

    /** @var Cacher  */
    protected Cacher $cacher;

    /** @var int  */
    protected int $type;

    /** @var string  */
    protected string $name;

    /** @var AttributeBuilderInterface|null  */
    protected ?AttributeBuilderInterface $targetBuilderAttribute=null;

    /** @var string|null  */
    protected ?string $resourceBuilderName=null;

    /** @var ResourceBuilderInterface|null  */
    protected ?ResourceBuilderInterface $resourceBuilder=null;

    /** @var string|null  */
    protected ?string $resourceObjectName=null;

    /** @var FunctionFacade|null  */
    protected ?FunctionFacade $function=null;

    /** @var CacheBuilder|null  */
    protected ?CacheBuilder $cacheBuilder=null;

    /** @var JsonDataMapper  */
    protected JsonDataMapper $mapper;

    /** @var bool  */
    private bool $loadChildren=true;

    /** @var bool  */
    private bool $isRequired=false;

    /**
     * AbstractRelationshipBuilder constructor.
     * @param ServicesFactory $services
     * @param string $name
     * @throws Exception
     */
    public function __construct(
        ServicesFactory $services,
        string $name
    )
    {
        $this->services = $services;
        $this->mysql = $services->service(MySQL::class);
        $this->mapper = $services->service(JsonDataMapper::class);
        $this->cacher = $services->service(Cacher::class);

        $this->name = $name;
    }

    /**
     * @param AttributeBuilderInterface $attribute
     * @param string|null $fieldName
     * @return RelationshipBuilderInterface
     * @throws Exception
     */
    public function withBuilder(
        AttributeBuilderInterface $attribute,
        string $fieldName=null
    ): RelationshipBuilderInterface
    {
        $this->targetBuilderAttribute = $attribute;

        if ($this->targetBuilderAttribute->getRelationshipResource() !== null) {
            $this->resourceBuilderName = get_class($this->targetBuilderAttribute->getRelationshipResource());
            $this->resourceObjectName = $this->targetBuilderAttribute->getRelationshipResource()->getType();
        } else {
            $this->resourceBuilderName = get_class($this->targetBuilderAttribute->getResource());
            $this->resourceObjectName = $this->targetBuilderAttribute->getResource()->getType();
        }

        $resourceFactory = new ResourceBuilderFactory($this->services);
        $this->resourceBuilder = $resourceFactory->createResourceBuilder($this->resourceBuilderName);

        $this->targetBuilderAttribute->setDatabaseFieldRelationship(
            $fieldName ??
            $this->targetBuilderAttribute->getDatabaseFieldName()
        );

        return $this;
    }

    /**
     * @param string $tableInterfaceClass
     * @param string $fieldName
     * @return RelationshipBuilderInterface
     * @throws Exception
     */
    public function throughManyToManyTable(
        string $tableInterfaceClass,
        string $fieldName
    ): RelationshipBuilderInterface
    {
        Throw new RuntimeException('');
    }

    /**
     * @param string $tableClassName
     * @param string|null $resourceBuilderClass
     * @param string $tableFunction
     * @param array $parameters
     * @return RelationshipBuilderInterface
     * @throws Exception
     */
    public function withTableFunction(
        string $tableClassName,
        ?string $resourceBuilderClass,
        string $tableFunction,
        array $parameters
    ): RelationshipBuilderInterface
    {
        $this->function = FunctionFactory::buildFromTableName(
            $tableClassName,
            $tableFunction,
            $parameters
        )->withTargetResourceBuilderClass(
            $resourceBuilderClass
        );

        return $this;
    }

    /**
     * @param string $loaderClassName
     * @param string|null $resourceBuilderClass
     * @param string $loaderFunction
     * @param array $parameters
     * @return RelationshipBuilderInterface
     */
    public function withLoaderFunction(
        string $loaderClassName,
        ?string $resourceBuilderClass,
        string $loaderFunction,
        array $parameters
    ): RelationshipBuilderInterface
    {
        $this->function = FunctionFactory::buildFromLoaderName(
            $loaderClassName,
            $loaderFunction,
            $parameters
        )->withTargetResourceBuilderClass(
            $resourceBuilderClass
        );

        return $this;
    }

    /**
     * @param CacheBuilder $cacheBuilder
     * @return RelationshipBuilderInterface
     */
    public function withCache(
        CacheBuilder $cacheBuilder
    ): RelationshipBuilderInterface
    {
        $this->cacheBuilder = $cacheBuilder;

        return $this;
    }

    /**
     * @param LinkBuilder $link
     * @return RelationshipBuilderInterface
     */
    public function withLink(LinkBuilder $link): RelationshipBuilderInterface
    {
        $this->addLink($link);

        return $this;
    }

    /**
     * @return RelationshipBuilderInterface
     */
    public function withoutChildren(): RelationshipBuilderInterface
    {
        $this->loadChildren = false;

        return $this;
    }

    /**
     * @return int
     */
    final public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return AttributeBuilderInterface|null
     */
    public function getAttribute(): ?AttributeBuilderInterface
    {
        return $this->targetBuilderAttribute;
    }

    /**
     * @return string
     */
    public function getManyToManyRelationshipTableClass(): string
    {
        Throw new RuntimeException('');
    }

    /**
     * @return string
     */
    public function getResourceObjectName(): string
    {
        return $this->resourceObjectName;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * @param bool $isRequired
     */
    public function setIsRequired(bool $isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    /**
     * @param array $data
     * @param array $parameters
     * @return CacheBuilder|null
     */
    protected function getCache(array $data, array $parameters): ?CacheBuilder
    {
        if ($this->cacheBuilder === null){
            return null;
        }

        $identifier = $this->cacheBuilder->getIdentifier();
        if (!is_array($identifier)){
            return $this->cacheBuilder;
        }

        if (!strpos($identifier[array_key_first($identifier)], '/')){
            if (array_key_exists(array_key_first($identifier), $data)){
                $this->cacheBuilder->setIdentifier($data[array_key_first($identifier)]);
                return $this->cacheBuilder;
            }
            return null;
        }

        if (($type = get_class($identifier)) === false){
            return null;
        }

        if (array_key_exists($type, $parameters) && array_key_exists(array_key_first($identifier), $parameters[$type])){
            $this->cacheBuilder->setIdentifier($parameters[$type][array_key_first($identifier)]);
            return $this->cacheBuilder;
        }

        return null;
    }

    /**
     * @param array $data
     * @param int $loadRelationshipLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array|ResourceObject[]|null
     * @throws Exception
     */
    final public function loadResources(
        array $data,
        int $loadRelationshipLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): ?array
    {
        if ($this->loadChildren && $loadRelationshipLevel > 0) {
            $loadRelationshipLevel--;
        } else {
            $loadRelationshipLevel = 0;
        }

        $cache = $this->getCache($data, $relationshipParameters);

        if ($this->function !== null){
            $values = [];

            if ($this->function->getParameters() !== []){
                foreach ($this->function->getParameters() as $parameter){
                    if (is_object($parameter) && get_class($parameter) === AttributeBuilder::class){
                        $values[] = $data[$parameter->getDatabaseFieldName()];
                    } else {
                        $values[] = $parameter;
                    }
                }
            }

            $additionalRelationshipParameters = ParametersFacade::prepareParameters($relationshipParameters, $positionInRelationship);
            $values = array_merge($values, $additionalRelationshipParameters);

            $this->function->replaceParameters($values);
            $this->function->withCacheBuilder($cache);

            if ($this->function->getType() === FunctionFacade::TABLE) {
                return $this->mapper->generateResourceObjectsByFunction(
                    $this->resourceBuilderName ?? $this->function->getTargetResourceBuilderClass(),
                    $cache,
                    $this->function,
                    $loadRelationshipLevel,
                    $relationshipParameters,
                    $positionInRelationship
                );
            }

            if ($this->function->getType() === FunctionFacade::LOADER) {
                $loaderClass = new ReflectionClass($this->function->getLoaderClassName());
                /** @var DataLoaderInterface $loader */
                $loader = $loaderClass->newInstanceArgs([$this->services]);

                $loader->setCacher($cache);
                $data = $loader->{$this->function->getFunctionName()}(...$this->function->getParameters());
                $loader->setCacher(null);

                if (get_class($this) === OneToOneRelationshipBuilder::class){
                    $data = [$data];
                }

                return $this->mapper->generateResourceObjectByData(
                    $this->resourceBuilderName ?? $this->function->getTargetResourceBuilderClass(),
                    $cache,
                    $data,
                    $loadRelationshipLevel,
                    $relationshipParameters,
                    $positionInRelationship
                );
            }
        }

        return $this->loadSpecialisedResources(
            $data,
            $cache,
            $loadRelationshipLevel,
            $relationshipParameters,
            $positionInRelationship
        );
    }

    /**
     * @param array $data
     * @param CacheBuilder|null $cache
     * @param int $loadRelationshipLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array|ResourceObject[]|null
     */
    abstract protected function loadSpecialisedResources(
        array $data,
        ?CacheBuilder $cache,
        int $loadRelationshipLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): ?array;

    /**
     * @return string
     */
    public function getBuilder(): string
    {
        return $this->resourceBuilderName ?? $this->function->getTargetResourceBuilderClass();
    }
}