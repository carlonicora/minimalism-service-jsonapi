<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Factories\CacheFactory;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheFactoryInterface;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheInterface;

class CacheBuilderFacade
{
    /** @var string|null  */
    private string $className;

    /** @var bool  */
    private bool $implementsGranularCache;

    /**
     * CacheBuilderFacade constructor.
     * @param string $className
     * @param bool $implementsGranularCache
     */
    public function __construct(string $className, bool $implementsGranularCache=false)
    {
        $this->className = $className;
        $this->implementsGranularCache = $implementsGranularCache;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->className;
    }

    public function getParameters(): array
    {
        /** @var CacheInterface $cacher */
        $cacher = new $this->className();
        return $cacher->getCacheParameters();
    }

    /**
     * @param ServicesFactory $services
     * @param array $parameters
     * @return CacheFactoryInterface
     */
    public function generateCacheFactoryInterface(ServicesFactory $services, array $parameters): CacheFactoryInterface
    {
        return new CacheFactory(
            $services,
            $this->className,
            $parameters,
            $this->implementsGranularCache
        );
    }
}