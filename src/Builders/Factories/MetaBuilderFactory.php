<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\MetaBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ElementBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;

class MetaBuilderFactory
{
    /** @var ServicesFactory */
    private ServicesFactory $services;

    /** @var ResourceBuilderInterface  */
    private ResourceBuilderInterface $parent;

    /**
     * ElementBuilderFactory constructor.
     * @param ServicesFactory $services
     * @param ResourceBuilderInterface $parent
     */
    public function __construct(
        ServicesFactory $services,
        ResourceBuilderInterface $parent
    )
    {
        $this->services = $services;
        $this->parent = $parent;
    }

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
            $this->services,
            $this->parent,
            $attributeName,
            $positioning
        );
    }
}