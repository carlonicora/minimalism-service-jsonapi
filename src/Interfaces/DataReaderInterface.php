<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheFactoryInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;

interface DataReaderInterface
{
    /**
     * DataReaderInterface constructor.
     * @param ServicesFactory $services
     * @param FunctionFacade $function
     * @param array $functionParameters
     * @param CacheFactoryInterface|null $dataInterface
     */
    public function __construct(
        ServicesFactory $services,
        FunctionFacade $function,
        array $functionParameters = [],
        CacheFactoryInterface $dataInterface = null
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