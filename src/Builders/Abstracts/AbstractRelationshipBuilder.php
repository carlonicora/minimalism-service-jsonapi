<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheFactoryInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\AttributeBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\CacheBuilderFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\LinkBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\ParametersFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Traits\LinkBuilderTrait;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Traits\ReadFunctionTrait;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;
use RuntimeException;

abstract class AbstractRelationshipBuilder implements RelationshipBuilderInterface
{
    use ReadFunctionTrait;
    use LinkBuilderTrait;

    /** @var ServicesFactory */
    protected ServicesFactory $services;

    /** @var MySQL $mysql */
    protected MySQL $mysql;

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

    /** @var CacheBuilderFacade|null  */
    protected ?CacheBuilderFacade $cacheBuilder=null;

    /** @var array|null  */
    protected ?array $cacheBuilderParameters=null;

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
    public function withFunction(
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
     * @param string $cacheBuilder
     * @param array|null $parameters
     * @return RelationshipBuilderInterface
     */
    public function withCache(
        string $cacheBuilder,
        array $parameters=null
    ): RelationshipBuilderInterface
    {
        $this->cacheBuilder = new CacheBuilderFacade($cacheBuilder);
        $this->cacheBuilderParameters = $parameters;

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
     * @return CacheFactoryInterface|null
     */
    protected function getCache(array $data, array $parameters): ?CacheFactoryInterface
    {
        if ($this->cacheBuilder === null){
            return null;
        }

        $cacheBuilderParametersDefinition = $this->cacheBuilder->getParameters();

        $cacheParameters = [];

        foreach ($cacheBuilderParametersDefinition ?? [] as $parameterDefinition){
            $found = false;

            /** @var AttributeBuilderInterface $cacheBuilderParameter */
            foreach ($this->cacheBuilderParameters as $cacheBuilderParameter){
                try {
                    $type = get_class($cacheBuilderParameter->getResource());
                    if (array_key_exists($type, $parameters) && array_key_exists($parameterDefinition, $parameters[$type])){
                        $cacheParameters[] = $parameters[$type][$parameterDefinition];
                        $found = true;
                        break;
                    }
                } catch (Exception $e) {
                }
            }

            if (!$found) {
                if (array_key_exists($parameterDefinition, $data)) {
                    $cacheParameters[] = $data[$parameterDefinition];
                } else {
                    $cacheParameters[] = null;
                }
            }
        }

        return $this->cacheBuilder->generateCacheFactoryInterface(
            $this->services,
            $cacheParameters
        );
    }

    /**
     * @param array $data
     * @param int $loadRelationshipLevel
     * @param array $externalParameters
     * @param array $position
     * @return array|ResourceObject[]|null
     */
    final public function loadResources(
        array $data,
        int $loadRelationshipLevel=0,
        array $externalParameters=[],
        array $position=[]
    ): ?array
    {
        if ($this->loadChildren && $loadRelationshipLevel > 0) {
            $loadRelationshipLevel--;
        } else {
            $loadRelationshipLevel = 0;
        }

        $cache = $this->getCache($data, $externalParameters);

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

            $additionalValues = ParametersFacade::prepareParameters($externalParameters, $position);
            foreach ($additionalValues ?? [] as $additionalValueKey=>$additionalValue){
                if (!strpos($additionalValueKey, '/')){
                    $values[] = $additionalValue;
                }
            }

            return $this->mapper->generateResourceObjectsByFunction(
                $this->resourceBuilderName ?? $this->function->getTargetResourceBuilderClass(),
                $cache,
                $this->function,
                $values,
                $loadRelationshipLevel
            );
        }

        return $this->loadSpecialisedResources(
            $data,
            $cache,
            $loadRelationshipLevel
        );
    }

    /**
     * @param array $data
     * @param CacheFactoryInterface|null $cache
     * @param int $loadRelationshipLevel
     * @return array|ResourceObject[]|null
     */
    abstract protected function loadSpecialisedResources(
        array $data,
        ?CacheFactoryInterface $cache,
        int $loadRelationshipLevel=0
    ): ?array;

    /**
     * @return string
     */
    public function getBuilder(): string
    {
        return $this->resourceBuilderName ?? $this->function->getTargetResourceBuilderClass();
    }
}