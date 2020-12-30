<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Factories;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\AttributeBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;

class AttributeBuilderFactory
{
    /** @var ResourceBuilderInterface  */
    private ResourceBuilderInterface $parent;

    /**
     * AttributeBuilderFactory constructor.
     * @param ResourceBuilderInterface $parent
     */
    public function __construct(ResourceBuilderInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @param string $attributeName
     * @return AttributeBuilderInterface
     */
    public function create(string $attributeName) : AttributeBuilderInterface
    {
        return new AttributeBuilder($this->parent, $attributeName);
    }
}