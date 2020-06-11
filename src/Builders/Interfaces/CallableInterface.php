<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces;

interface CallableInterface
{
    /**
     * @return string
     */
    public function getReadFunction(): ?string ;

    /**
     * @param string $callable
     */
    public function setReadFunction(string $callable): void;
}