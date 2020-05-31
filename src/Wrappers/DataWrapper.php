<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Wrappers;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Factories\DataReadersFactory;
use Exception;

class DataWrapper
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var string|null  */
    private ?string $tableName=null;

    /** @var string|null  */
    private ?string $function=null;

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
     * @throws Exception
     */
    public function loadData() : ?array
    {
        $dataReadersFactory = new DataReadersFactory($this->services);

        $function = $dataReadersFactory->create($this->tableName, $this->function, $this->parameters);
        if ($this->isSingle) {
            $response = $function->getSingle();
        } else {
            $response = $function->getList();
        }

        return $response;
    }

    /**
     * @param string|null $function
     */
    public function setFunction(?string $function): void
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
     * @param string|null $tableName
     */
    public function setTableName(?string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * @param bool $isSingle
     */
    public function setIsSingle(bool $isSingle): void
    {
        $this->isSingle = $isSingle;
    }
}