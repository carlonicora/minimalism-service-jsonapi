<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Factories;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Facades\DataReaderFacade;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\DataReaderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use Exception;

class DataReadersFactory
{
    /**
     * DataReadersFactory constructor.
     * @param ServicesProxy $servicesProxy
     */
    public function __construct(
        private ServicesProxy $servicesProxy
    )
    {
    }

    /**
     * @param FunctionFacade $function
     * @param array $functionParameters
     * @return DataReaderFacade
     * @throws Exception
     */
    public function create(
        FunctionFacade $function,
        array $functionParameters = []
    ) : DataReaderInterface
    {
        return new DataReaderFacade(
            servicesProxy: $this->servicesProxy,
            function: $function,
            functionParameters: $functionParameters,
        );
    }
}