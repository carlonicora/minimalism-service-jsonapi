<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;

class CacheFacade
{
    /** @var array  */
    private array $cache = [
        'resources' => [],
        'attributes' => []
    ];

    /**
     * @param string $resourceBuilderName
     * @return ResourceBuilderInterface|null
     */
    public function getResourceBuilder(string $resourceBuilderName): ?ResourceBuilderInterface
    {
        return $this->cache['resources'][$resourceBuilderName] ?? null;
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     */
    public function setResourceBuilder(ResourceBuilderInterface $resourceBuilder): void
    {
        $this->cache['resources'][get_class($resourceBuilder)] = $resourceBuilder;
    }

    /**
     * @param string $resourceBuilderName
     * @param string $attributeBuilderName
     * @return AttributeBuilderInterface|null
     */
    public function getAttributeBuilder(string $resourceBuilderName, string $attributeBuilderName): ?AttributeBuilderInterface
    {
        return $this->cache['attributes'][$resourceBuilderName][$attributeBuilderName] ?? null;
    }

    /**
     * @param AttributeBuilderInterface $attributeBuilder
     */
    public function setAttributeBuilder(AttributeBuilderInterface $attributeBuilder): void
    {
        $this->cache['attributes'][get_class($attributeBuilder->getResourceBuilder())][$attributeBuilder->getName()] = $attributeBuilder;
    }
}