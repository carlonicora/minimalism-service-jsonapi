<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces;

interface MetaBuilderInterface extends ElementBuilderInterface
{
    public const RESOURCE=1;
    public const RELATIONSHIP=2;
    public const ALL=3;

    /**
     *
     * AttributeBuilder constructor.
     * @param ResourceBuilderInterface $parent
     * @param string $name
     * @param int $positioning
     */
    public function __construct(
        ResourceBuilderInterface $parent,
        string $name,
        int $positioning
    );
}