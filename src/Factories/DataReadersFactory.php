<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Factories;

use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Facades\DataReaderFacade;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\DataReaderInterface;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Redis\Redis;
use Exception;

class DataReadersFactory
{
    /**
     * DataReadersFactory constructor.
     * @param Redis $redis
     * @param MySQL $mysql
     * @param Cacher $cacher
     */
    public function __construct(
        private Redis $redis,
        private MySQL $mysql,
        private Cacher $cacher,
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
            $this->redis,
            $this->mysql,
            $this->cacher,
            $function,
            $functionParameters,
        );
    }
}