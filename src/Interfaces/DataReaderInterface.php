<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Interfaces;

use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Redis\Redis;

interface DataReaderInterface
{
    /**
     * DataReaderInterface constructor.
     * @param Redis $redis
     * @param MySQL $mysql
     * @param Cacher $cacher
     * @param FunctionFacade $function
     * @param array $functionParameters
     */
    public function __construct(
        Redis $redis,
        MySQL $mysql,
        Cacher $cacher,
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