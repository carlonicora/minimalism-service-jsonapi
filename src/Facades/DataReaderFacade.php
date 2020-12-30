<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Facades;

use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\DataReaderInterface;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Redis\Redis;
use Exception;

class DataReaderFacade implements DataReaderInterface
{
    /** @var FunctionFacade  */
    public FunctionFacade $function;

    /** @var array  */
    public array $functionParameters;

    /**
     * DataReaderFacade constructor.
     * @param Redis $redis
     * @param MySQL $mysql
     * @param Cacher $cacher
     * @param FunctionFacade $function
     * @param array $functionParameters
     */
    public function __construct(
        private Redis $redis,
        private MySQL $mysql,
        private Cacher $cacher,
        FunctionFacade $function,
        array $functionParameters = []
    ) {
        $this->function = $function;
        $this->functionParameters = $functionParameters;
    }

    /**
     * @return array
     * @throws DbRecordNotFoundException|Exception
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function getSingle() : array
    {
        if ($this->function->getCacheBuilder() !== null && $this->cacher->useCaching()) {
            if (($response = $this->cacher->readArray($this->function->getCacheBuilder(), CacheBuilder::DATA)) === null){
                $response = call_user_func($this->function->getFunction(), ...$this->functionParameters);
                if (is_array($response)) {
                    $this->cacher->saveArray($this->function->getCacheBuilder(), $response, CacheBuilder::DATA);
                } else {
                    $this->cacher->save($this->function->getCacheBuilder(), (string)$response, CacheBuilder::DATA);
                }
            }
        } else {
            $response = call_user_func($this->function->getFunction(), ...$this->functionParameters);
        }

        return $response;
    }

    /**
     * @return array|null
     * @throws Exception
     */
    public function getList() : ?array
    {
        if ($this->function->getCacheBuilder() !== null && $this->cacher->useCaching()) {
            if (($response = $this->cacher->readArray($this->function->getCacheBuilder(), CacheBuilder::DATA)) === null) {
                $response = call_user_func($this->function->getFunction(), ...$this->functionParameters);

                if ($response !== null && $response !== []) {
                    $this->function->getCacheBuilder()->setType(CacheBuilder::DATA);
                    $this->cacher->saveArray($this->function->getCacheBuilder(), $response, CacheBuilder::DATA);
                }
            }
        } else {
            $response = call_user_func($this->function->getFunction(), ...$this->functionParameters);
        }

        return $response;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getCount() : int
    {
        return (int)call_user_func($this->function->getFunction(), ...$this->functionParameters);
    }
}