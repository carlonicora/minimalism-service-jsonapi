<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;

interface DataLoaderInterface
{
    /**
     * LoaderInterface constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services);
}