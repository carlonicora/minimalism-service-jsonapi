<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ElementBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\MetaBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;

class MetaBuilder extends ElementBuilder implements MetaBuilderInterface
{
    /** @var int */
    private int $positioning;

    /**
     * AttributeBuilder constructor.
     * @param ResourceBuilderInterface $parent
     * @param string $name
     * @param int $positioning
     */
    public function __construct(
        ResourceBuilderInterface $parent,
        string $name,
        int $positioning
    )
    {
        $this->name = $name;
        $this->positioning = $positioning;
        $this->databaseFieldName = $name;
        $this->parent = $parent;
    }

    /**
     * @return int
     */
    public function getPositioning(): int
    {
        return $this->positioning;
    }

    /**
     * @param int $positioning
     * @return ElementBuilderInterface
     */
    public function setPositioning(
        int $positioning
    ): ElementBuilderInterface
    {
        $this->positioning = $positioning;

        return $this;
    }
}