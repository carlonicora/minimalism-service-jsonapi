<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Interfaces;

use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use Exception;

interface DataReaderInterface
{
    /**
     * DataReaderInterface constructor.
     * @param ServicesProxy $servicesProxy
     * @param FunctionFacade $function
     * @param array $functionParameters
     */
    public function __construct(
        ServicesProxy $servicesProxy,
        FunctionFacade $function,
        array $functionParameters = []
    );

    /**
     * @return array
     * @throws Exception|RecordNotFoundException
     */
    public function getSingle() : array;

    /**
     * @return array|null
     */
    public function getList() : ?array;

    /**
     * @return int
     */
    public function getCount() : int;
}