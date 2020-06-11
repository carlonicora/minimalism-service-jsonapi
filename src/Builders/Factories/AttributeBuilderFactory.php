<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\AttributeBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;

class AttributeBuilderFactory
{
    /** @var ServicesFactory */
    private ServicesFactory $services;

    /** @var ResourceBuilderInterface  */
    private ResourceBuilderInterface $parent;

    /**
     * AttributeBuilderFactory constructor.
     * @param ServicesFactory $services
     * @param ResourceBuilderInterface $parent
     */
    public function __construct(ServicesFactory $services, ResourceBuilderInterface $parent)
    {
        $this->services = $services;
        $this->parent = $parent;
    }

    /**
     * @param string $attributeName
     * @return AttributeBuilderInterface
     */
    public function create(string $attributeName) : AttributeBuilderInterface
    {
        return new AttributeBuilder($this->services, $this->parent, $attributeName);
    }
}