<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ElementBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\MetaBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;

class MetaBuilder extends ElementBuilder implements MetaBuilderInterface
{
    /** @var int */
    private int $positioning;

    /**
     * AttributeBuilder constructor.
     * @param ServicesFactory $services
     * @param ResourceBuilderInterface $parent
     * @param string $name
     * @param int $positioning
     */
    public function __construct(ServicesFactory $services, ResourceBuilderInterface $parent, string $name, int $positioning)
    {
        $this->name = $name;
        $this->positioning = $positioning;
        $this->databaseFieldName = $name;
        $this->services = $services;
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
    public function setPositioning(int $positioning): ElementBuilderInterface
    {
        $this->positioning = $positioning;

        return $this;
    }
}