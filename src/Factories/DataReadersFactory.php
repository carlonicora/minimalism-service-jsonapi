<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheFactoryInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Facades\DataReaderFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\DataReaderInterface;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;

class DataReadersFactory
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var MySQL  */
    private MySQL $mysql;

    /**
     * DataReadersFactory constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
        $this->mysql = $services->service(MySQL::class);
    }

    /**
     * @param string $tableName
     * @param string $functionName
     * @param array $functionParameters
     * @param CacheFactoryInterface|null $dataCache
     * @return DataReaderFacade
     * @throws Exception
     */
    public function create(
        string $tableName,
        string $functionName,
        array $functionParameters = [],
        CacheFactoryInterface $dataCache = null
    ) : DataReaderInterface
    {
        $table = $this->mysql->create($tableName);
        return new DataReaderFacade(
            $this->services,
            $table,
            $functionName,
            $functionParameters,
            $dataCache
        );
    }
}