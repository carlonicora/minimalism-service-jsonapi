<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Facades;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\DataReaderInterface;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Redis\Redis;
use Exception;

class DataReaderFacade implements DataReaderInterface
{
    /** @var ServicesFactory  */
    protected ServicesFactory $services;

    /** @var FunctionFacade  */
    public FunctionFacade $function;

    /** @var array  */
    public array $functionParameters;

    /** @var Redis  */
    protected Redis $redis;

    /** @var MySQL  */
    protected MySQL $database;

    /** @var Cacher  */
    protected Cacher $cacher;
    
    /**
     * DataReaderFacade constructor.
     * @param ServicesFactory $services
     * @param FunctionFacade $function
     * @param array $functionParameters
     * @throws Exception
     */
    public function __construct(
        ServicesFactory $services,
        FunctionFacade $function,
        array $functionParameters = []
    ) {
        $this->services = $services;
        $this->function = $function;
        $this->functionParameters = $functionParameters;

        $this->redis = $services->service(Redis::class);
        $this->database = $services->service(MySQL::class);
        $this->cacher = $services->service(Cacher::class);
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