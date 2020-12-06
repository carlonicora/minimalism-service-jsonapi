<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Facades\DataReaderFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\DataReaderInterface;
use Exception;

class DataReadersFactory
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /**
     * DataReadersFactory constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
    }

    /**
     * @param FunctionFacade $function
     * @param array $functionParameters
     * @param CacheBuilder|null $cacheBuilder
     * @return DataReaderFacade
     * @throws Exception
     */
    public function create(
        FunctionFacade $function,
        array $functionParameters = [],
        CacheBuilder $cacheBuilder = null
    ) : DataReaderInterface
    {
        return new DataReaderFacade(
            $this->services,
            $function,
            $functionParameters,
            $cacheBuilder
        );
    }
}