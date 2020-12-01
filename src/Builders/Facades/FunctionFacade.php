<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;

class FunctionFacade
{
    /** @var TableInterface|null  */
    private ?TableInterface $tableInterface=null;

    /** @var ResourceBuilderInterface|null  */
    private ?ResourceBuilderInterface $resourceBuilder=null;

    /** @var string|null  */
    private ?string $targetResourceBuilderClass=null;

    /** @var string  */
    private string $functionName;

    /**
     * @var array
     */
    private array $parameters;

    /**
     * FunctionFacade constructor.
     * @param string $functionName
     * @param array $parameters
     */
    public function __construct(
        string $functionName,
        array $parameters=[]
    )
    {
        $this->functionName = $functionName;
        $this->parameters = $parameters;
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
     * @param string|null $targetResourceBuilderClass
     */
    public function setTargetResourceBuilderClass(?string $targetResourceBuilderClass): void
    {
        $this->targetResourceBuilderClass = $targetResourceBuilderClass;
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
}