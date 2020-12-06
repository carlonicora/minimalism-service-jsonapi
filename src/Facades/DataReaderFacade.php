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
    
    /** @var CacheBuilder|null  */
    protected ?CacheBuilder $cacheBuilder;

    /**
     * DataReaderFacade constructor.
     * @param ServicesFactory $services
     * @param FunctionFacade $function
     * @param array $functionParameters
     * @param CacheBuilder|null $cacheBuilder
     * @throws Exception
     */
    public function __construct(
        ServicesFactory $services,
        FunctionFacade $function,
        array $functionParameters = [],
        CacheBuilder $cacheBuilder = null
    ) {
        $this->services = $services;
        $this->function = $function;
        $this->functionParameters = $functionParameters;
        $this->cacheBuilder = $cacheBuilder;

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
        if ($this->cacheBuilder !== null && $this->cacher->useCaching()) {
            if (($response = $this->cacher->readArray($this->cacheBuilder)) === null){
                $response = call_user_func($this->function->getFunction(), ...$this->functionParameters);
                if (is_array($response)) {
                    $this->cacher->createArray($this->cacheBuilder, $response);
                } else {
                    $this->cacher->create($this->cacheBuilder, (string)$response);
                }
            }
        } else {
            $response = call_user_func($this->function->getFunction(), ...$this->functionParameters);
        }

        return $response;
    }

    /**
     * @return array|null
     */
    public function getList() : ?array
    {
        if ($this->cacheBuilder !== null && $this->cacher->useCaching()) {
            if (($response = $this->cacher->readArray($this->cacheBuilder)) === null) {
                $response = call_user_func($this->function->getFunction(), ...$this->functionParameters);

                if ($response !== null) {
                    $this->cacher->createArray($this->cacheBuilder, $response);
                }
            }
        } else {
            $response = call_user_func($this->function->getFunction(), ...$this->functionParameters);
        }

        return $response;
    }

    /**
     * @return int
     */
    public function getCount() : int
    {
        if ($this->cacheBuilder !== null && $this->cacher->useCaching()) {
            if (($response = (int)$this->cacher->read($this->cacheBuilder)) === null) {
                $response = (int)call_user_func($this->function->getFunction(), ...$this->functionParameters);

                if ($response !== null) {
                    $this->cacher->create($this->cacheBuilder, $response);
                }
            }
        } else {
            $response = (int)call_user_func($this->function->getFunction(), ...$this->functionParameters);
        }

        return $response;
    }
}