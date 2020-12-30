<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\MetaBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;

class CacheFacade
{
    /** @var array  */
    private array $cache = [
        'resources' => [],
        'attributes' => [],
        'meta' => []
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


    /**
     * @param MetaBuilderInterface $metaBuilder
     */
    public function setMetaBuilder(MetaBuilderInterface $metaBuilder): void
    {
        $this->cache['meta'][get_class($metaBuilder->getResourceBuilder())][$metaBuilder->getName()] = $metaBuilder;
    }
}