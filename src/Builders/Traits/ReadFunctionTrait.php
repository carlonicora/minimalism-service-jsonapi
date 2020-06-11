<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Traits;

trait ReadFunctionTrait
{
    /** @var string  */
    protected ?string $readFunction=null;

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
}