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

    /**
     * @return array
     */
    public function getReadValues(): ?array ;

    /**
     * @param array $values
     */
    public function setReadValues(array $values): void;
}