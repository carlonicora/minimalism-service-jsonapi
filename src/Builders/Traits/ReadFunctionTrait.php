<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Traits;

trait ReadFunctionTrait
{
    /** @var string|null  */
    protected ?string $readFunction=null;

    /** @var array|null  */
    protected ?array $readValues=null;

    /**
     * @return string|null
     */
    public function getReadFunction(): ?string
    {
        return $this->readFunction;
    }

    /**
     * @param string $callable
     */
    public function setReadFunction(string $callable): void
    {
        $this->readFunction = $callable;
    }


    /**
     * @return array|null
     */
    public function getReadValues(): ?array
    {
        return $this->readValues;
    }

    /**
     * @param array $values
     */
    public function setReadValues(array $values): void
    {
        $this->readValues = $values;
    }
}