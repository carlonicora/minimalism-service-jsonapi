<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\Redis\Exceptions\RedisConnectionException;
use CarloNicora\Minimalism\Services\Redis\Exceptions\RedisKeyNotFoundException;
use CarloNicora\Minimalism\Services\Redis\Redis;
use Exception;

class CacheFacade
{
    /** @var ServicesFactory  */
    protected ServicesFactory $services;

    /** @var Redis  */
    private Redis $redis;

    /** @var array  */
    private array $cache = [
        'resources' => [],
        'attributes' => []
    ];

    /**
     * CacheFacade constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;

        $this->redis = $this->services->service(Redis::class);
    }

    /**
     * @param string $resourceBuilderName
     * @return ResourceBuilderInterface|null
     */
    public function getResourceBuilder(string $resourceBuilderName): ?ResourceBuilderInterface
    {
        $cache = $this->cache['resources'][$resourceBuilderName] ?? null;

        if ($cache === null) {
            try {
                /** @noinspection UnserializeExploitsInspection */
                $cache = unserialize($this->redis->get('minimalism-builder-resources-' . $resourceBuilderName));
            } catch (RedisConnectionException|RedisKeyNotFoundException $e) {
                $cache = null;
            }
        }

        return $cache;
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     */
    public function setResourceBuilder(ResourceBuilderInterface $resourceBuilder): void
    {
        $this->cache['resources'][get_class($resourceBuilder)] = $resourceBuilder;
        try {
            $this->redis->set('minimalism-builder-resources-' . get_class($resourceBuilder), serialize($resourceBuilder));
        } catch (RedisConnectionException $e) {
        }
    }

    /**
     * @param string $resourceBuilderName
     * @param string $attributeBuilderName
     * @return AttributeBuilderInterface|null
     */
    public function getAttributeBuilder(string $resourceBuilderName, string $attributeBuilderName): ?AttributeBuilderInterface
    {

        $cache = $this->cache['attributes'][$resourceBuilderName][$attributeBuilderName] ?? null;

        if ($cache === null) {
            try {
                /** @noinspection UnserializeExploitsInspection */
                $cache = unserialize($this->redis->get('minimalism-builder-resources-' . $resourceBuilderName . '-' . $attributeBuilderName));
            } catch (RedisConnectionException|RedisKeyNotFoundException $e) {
                $cache = null;
            }
        }

        return $cache;
    }

    /**
     * @param AttributeBuilderInterface $attributeBuilder
     */
    public function setAttributeBuilder(AttributeBuilderInterface $attributeBuilder): void
    {
        $this->cache['attributes'][get_class($attributeBuilder->getResourceBuilder())][$attributeBuilder->getName()] = $attributeBuilder;
        try {
            $this->redis->set('minimalism-builder-resources-' . get_class($attributeBuilder->getResourceBuilder()) . '-' . $attributeBuilder->getName(), serialize($attributeBuilder));
        } catch (RedisConnectionException $e) {
        }
    }
}