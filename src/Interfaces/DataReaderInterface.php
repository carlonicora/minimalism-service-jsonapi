<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;

interface DataReaderInterface
{
    /**
     * DataReaderInterface constructor.
     * @param ServicesFactory $services
     * @param FunctionFacade $function
     * @param array $functionParameters
     */
    public function __construct(
        ServicesFactory $services,
        FunctionFacade $function,
        array $functionParameters = []
    );

    /**
     * @return array
     * @throws DbRecordNotFoundException
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