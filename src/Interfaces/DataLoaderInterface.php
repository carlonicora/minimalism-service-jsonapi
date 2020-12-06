<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;

interface DataLoaderInterface
{
    /**
     * LoaderInterface constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services);

    /**
     * @param CacheBuilder|null $cacheBuilder
     */
    public function setCacher(?CacheBuilder $cacheBuilder): void;
}