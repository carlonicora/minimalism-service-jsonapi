<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Factories;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\MetaBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ElementBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;

class MetaBuilderFactory
{
    /**
     * ElementBuilderFactory constructor.
     * @param ResourceBuilderInterface $parent
     */
    public function __construct(
        private ResourceBuilderInterface $parent
    ) {}

    /**
     * @param string $attributeName
     * @param int $positioning
     * @return ElementBuilderInterface
     */
    public function create(
        string $attributeName,
        int $positioning
    ) : ElementBuilderInterface
    {
        return new MetaBuilder(
            $this->parent,
            $attributeName,
            $positioning
        );
    }
}