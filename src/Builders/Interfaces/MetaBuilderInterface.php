<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;

interface MetaBuilderInterface extends ElementBuilderInterface
{
    public const RESOURCE=1;
    public const RELATIONSHIP=2;
    public const ALL=3;

    /**
     *
     * AttributeBuilder constructor.
     * @param ServicesFactory $services
     * @param ResourceBuilderInterface $parent
     * @param string $name
     * @param int $positioning
     */
    public function __construct(ServicesFactory $services, ResourceBuilderInterface $parent, string $name, int $positioning);
}