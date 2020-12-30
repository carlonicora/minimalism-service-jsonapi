<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces;

interface CallableInterface
{
    /**
     * @return string|null
     */
    public function getReadFunction(): ?string;

    /**
     * @param string $callable
     */
    public function setReadFunction(string $callable): void;

    /**
     * @return array|null
     */
    public function getReadValues(): ?array;

    /**
     * @param array $values
     */
    public function setReadValues(array $values): void;
}