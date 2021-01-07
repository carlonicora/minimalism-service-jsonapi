<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Abstracts;

use CarloNicora\Minimalism\Interfaces\CacheBuilderInterface;
use CarloNicora\Minimalism\Interfaces\DataLoaderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\AttributeBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\LinkBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\OneToOneRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\ParametersFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Traits\LinkBuilderTrait;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Traits\ReadFunctionTrait;
use CarloNicora\Minimalism\Services\JsonApi\Commands\ResourceReader;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use Exception;
use ReflectionClass;
use RuntimeException;

abstract class AbstractRelationshipBuilder implements RelationshipBuilderInterface
{
    use ReadFunctionTrait;
    use LinkBuilderTrait;

    /** @var int  */
    protected int $type;

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

    /** @var CacheBuilderInterface|null  */
    protected ?CacheBuilderInterface $cacheBuilder=null;

    /** @var bool  */
    private bool $loadChildren=true;

    /** @var bool  */
    private bool $isRequired=false;

    /**
     * @var ResourceReader
     */
    protected ResourceReader $resourceReader;

    /**
     * AbstractRelationshipBuilder constructor.
     * @param ServicesProxy $servicesProxy
     * @param string $name
     */
    public function __construct(
        protected ServicesProxy $servicesProxy,
        protected string $name,
    ) {
        $this->resourceReader = new ResourceReader(
            servicesProxy: $this->servicesProxy
        );
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

        $resourceFactory = new ResourceBuilderFactory(
            servicesProxy: $this->servicesProxy,
        );
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
     * @param CacheBuilderInterface $cacheBuilder
     * @return RelationshipBuilderInterface
     */
    public function withCache(
        CacheBuilderInterface $cacheBuilder
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
     * @return CacheBuilderInterface|null
     */
    protected function getCache(array $data, array $parameters): ?CacheBuilderInterface
    {
        if ($this->cacheBuilder === null){
            return null;
        }
        $response = clone($this->cacheBuilder);

        $identifier = $response->getCacheIdentifier();
        if (!is_array($identifier)){
            return $response;
        }

        if (!strpos(array_key_first($identifier), '/')){
            if (array_key_exists(array_key_first($identifier), $data)){
                $response->setCacheIdentifier($data[array_key_first($identifier)]);
                return $response;
            }
            return null;
        }

        /** @noinspection GetClassUsageInspection */
        if (($type = get_class($identifier)) === false){
            return null;
        }

        if (array_key_exists($type, $parameters) && array_key_exists(array_key_first($identifier), $parameters[$type])){
            $response->setCacheIdentifier($parameters[$type][array_key_first($identifier)]);
            return $response;
        }

        return null;
    }

    /**
     * @param array $data
     * @param int $loadRelationshipLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array|null
     * @throws Exception
     */
    final public function loadResources(
        array $data,
        int $loadRelationshipLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): ?array
    {
        if ($loadRelationshipLevel <= 0){
            return null;
        }

        if ($this->loadChildren) {
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

            $temporaryParameters = $this->function->getParameters();
            $temporaryCache = $this->function->getCacheBuilder();

            $this->function->replaceParameters($values);
            $this->function->withCacheBuilder($cache);

            $response = [];

            if ($this->function->getType() === FunctionFacade::TABLE) {
                $response = $this->resourceReader->generateResourceObjectsByFunction(
                    $this->resourceBuilderName ?? $this->function->getTargetResourceBuilderClass(),
                    $cache,
                    $this->function,
                    $loadRelationshipLevel,
                    $relationshipParameters,
                    $positionInRelationship
                );
            } elseif ($this->function->getType() === FunctionFacade::LOADER) {
                $loaderClass = new ReflectionClass($this->function->getLoaderClassName());
                /** @var DataLoaderInterface $loader */
                $loader = $loaderClass->newInstanceArgs([
                    $this->servicesProxy->getDataProvider(),
                    $this->servicesProxy->getCacheProvider(),
                    $this->servicesProxy->getService()
                ]);

                $data = $loader->{$this->function->getFunctionName()}(...$this->function->getParameters());

                if (get_class($this) === OneToOneRelationshipBuilder::class){
                    $data = [$data];
                }

                $response = $this->resourceReader->generateResourceObjectByData(
                    $this->resourceBuilderName ?? $this->function->getTargetResourceBuilderClass(),
                    $cache,
                    $data,
                    $loadRelationshipLevel,
                    $relationshipParameters,
                    $positionInRelationship
                );
            }

            $this->function->replaceParameters($temporaryParameters);
            $this->function->withCacheBuilder($temporaryCache);

            return $response;
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
     * @param CacheBuilderInterface|null $cache
     * @param int $loadRelationshipLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array|null
     */
    abstract protected function loadSpecialisedResources(
        array $data,
        ?CacheBuilderInterface $cache,
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