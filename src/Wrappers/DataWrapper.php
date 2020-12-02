<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Wrappers;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Factories\DataReadersFactory;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use Exception;

class DataWrapper
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var FunctionFacade|null  */
    private ?FunctionFacade $function=null;

    /** @var array|null  */
    private ?array $parameters=null;

    /** @var bool  */
    private bool $isSingle=false;

    /**
     * Parameter constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
    }

    /**
     * @return array|null
     * @throws Exception|DbRecordNotFoundException
     */
    public function loadData() : ?array
    {
        $dataReadersFactory = new DataReadersFactory($this->services);

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