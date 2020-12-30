<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;
use RuntimeException;

class FunctionFacade
{
    public const LOADER=1;
    public const TABLE=2;
    public const BUILDER=3;

    /** @var TableInterface|null  */
    private ?TableInterface $tableInterface=null;

    /** @var string|null  */
    private ?string $loaderClassName=null;

    /** @var ResourceBuilderInterface|null  */
    private ?ResourceBuilderInterface $resourceBuilder=null;

    /** @var string|null  */
    private ?string $targetResourceBuilderClass=null;

    /** @var string  */
    private string $functionName;

    /** @var bool  */
    private bool $isSingleRead;
    
    /** @var CacheBuilder|null  */
    private ?CacheBuilder $cacheBuilder=null;

    /**
     * @var array
     */
    private array $parameters;

    /**
     * FunctionFacade constructor.
     * @param string $functionName
     * @param array $parameters
     * @param bool $isSingleRead
     */
    public function __construct(
        string $functionName,
        array $parameters=[],
        bool $isSingleRead=false
    )
    {
        $this->functionName = $functionName;
        $this->parameters = $parameters;
        $this->isSingleRead = $isSingleRead;
    }

    public function getType(): int
    {
        if ($this->loaderClassName !== null){
            return self::LOADER;
        }

        if ($this->resourceBuilder !== null){
            return self::BUILDER;
        }

        return self::TABLE;
    }

    /**
     * @param string|null $targetResourceBuilderClass
     * @return FunctionFacade
     */
    public function withTargetResourceBuilderClass(?string $targetResourceBuilderClass): FunctionFacade
    {
        $this->targetResourceBuilderClass = $targetResourceBuilderClass;

        return $this;
    }

    /**
     * @return array
     */
    public function getFunction(): array
    {
        return [$this->tableInterface, $this->functionName];
    }

    /**
     * @return bool
     */
    public function isSingleRead(): bool
    {
        return $this->isSingleRead;
    }

    /**
     * @return bool
     */
    public function isResourceBuilder(): bool
    {
        return $this->resourceBuilder !== null;
    }

    /**
     * @return ResourceBuilderInterface
     */
    public function getResourceBuilder(): ResourceBuilderInterface
    {
        return $this->resourceBuilder;
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     */
    public function setResourceBuilder(ResourceBuilderInterface $resourceBuilder): void
    {
        $this->resourceBuilder = $resourceBuilder;
    }

    /**
     * @return string
     */
    public function getResourceBuilderClass(): string
    {
        if ($this->resourceBuilder !== null) {
            return get_class($this->resourceBuilder);
        }

        return '';
    }

    /**
     * @param string $loaderClassName
     */
    public function setLoaderClassName(string $loaderClassName): void
    {
        $this->loaderClassName = $loaderClassName;
    }

    /**
     * @return string
     */
    public function getLoaderClassName(): string
    {
        if($this->loaderClassName === null){
            throw new RuntimeException('');
        }

        return $this->loaderClassName;
    }

    /**
     * @return TableInterface
     */
    public function getTable(): TableInterface
    {
        return $this->tableInterface;
    }

    /**
     * @param TableInterface $tableInterface
     */
    public function setTableInterface(TableInterface $tableInterface): void
    {
        $this->tableInterface = $tableInterface;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableInterface->getTableName();
    }

    /**
     * @return string|null
     */
    public function getTargetResourceBuilderClass(): ?string
    {
        return $this->targetResourceBuilderClass;
    }

    /**
     * @return string
     */
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function replaceParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return CacheBuilder|null
     */
    public function getCacheBuilder(): ?CacheBuilder
    {
        return $this->cacheBuilder;
    }

    /**
     * @param CacheBuilder|null $cacheBuilder
     * @return FunctionFacade
     */
    public function withCacheBuilder(?CacheBuilder $cacheBuilder): FunctionFacade
    {
        $this->cacheBuilder = $cacheBuilder;
        
        return $this;
    }
}