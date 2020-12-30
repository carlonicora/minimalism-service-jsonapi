<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Wrappers;

use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonApi\Factories\DataReadersFactory;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Redis\Redis;
use Exception;

class DataWrapper
{
    /** @var FunctionFacade|null  */
    private ?FunctionFacade $function=null;

    /** @var array|null  */
    private ?array $parameters=null;

    /** @var bool  */
    private bool $isSingle=false;

    /**
     * Parameter constructor.
     * @param Redis $redis
     * @param MySQL $mysql
     * @param Cacher $cacher
     */
    public function __construct(
        private Redis $redis,
        private MySQL $mysql,
        private Cacher $cacher,
    ){}

    /**
     * @return array|null
     * @throws Exception|DbRecordNotFoundException
     */
    public function loadData() : ?array
    {
        $dataReadersFactory = new DataReadersFactory($this->redis, $this->mysql, $this->cacher);

        $function = $dataReadersFactory->create(
            FunctionFactory::buildFromTableName(
                $this->function->getTableName(),
                $this->function->getFunctionName()),
            $this->parameters
        );
        if ($this->isSingle) {
            $response = $function->getSingle();
        } else {
            $response = $function->getList();
        }

        return $response;
    }

    /**
     * @param FunctionFacade $function
     */
    public function setFunction(FunctionFacade $function): void
    {
        $this->function = $function;
    }

    /**
     * @param array|null $parameters
     */
    public function setParameters(?array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @param bool $isSingle
     */
    public function setIsSingle(bool $isSingle): void
    {
        $this->isSingle = $isSingle;
    }
}