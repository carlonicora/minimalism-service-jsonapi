<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;

class FunctionFactory
{
    /**
     * @param string $functionName
     * @param array $parameters
     * @return FunctionFacade
     */
    private static function initialiseFunctionFacade(
        string $functionName,
        array $parameters=[]
    ): FunctionFacade
    {
        return new FunctionFacade($functionName, $parameters);
    }

    /**
     * @param TableInterface $tableInterface
     * @param string $functionName
     * @param array $parameters
     * @return FunctionFacade
     */
    public static function buildFromTableInterface(
        TableInterface $tableInterface,
        string $functionName,
        array $parameters=[]
    ): FunctionFacade
    {
        $response = self::initialiseFunctionFacade($functionName, $parameters);
        $response->setTableInterface($tableInterface);

        return $response;
    }

    /**
     * @param ServicesFactory $services
     * @param string $tableClassName
     * @param string|null $targetResourceBuilderClass
     * @param string $functionName
     * @param array $parameters
     * @return FunctionFacade
     * @throws Exception
     */
    public static function buildFromTableName(
        ServicesFactory $services,
        string $tableClassName,
        ?string $targetResourceBuilderClass,
        string $functionName,
        array $parameters=[]
    ): FunctionFacade
    {
        /** @var MySQL $mysql */
        $mysql = $services->service(MySQL::class);

        $tableInterface = $mysql->create($tableClassName);

        $response = self::buildFromTableInterface($tableInterface, $functionName, $parameters);
        $response->setTargetResourceBuilderClass($targetResourceBuilderClass);

        return $response;
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     * @param string $functionName
     * @param array $parameters
     * @return FunctionFacade
     */
    public static function buildFromResourceBuilder(
        ResourceBuilderInterface $resourceBuilder,
        string $functionName,
        array $parameters=[]
    ): FunctionFacade
    {
        $response = self::initialiseFunctionFacade($functionName, $parameters);
        $response->setResourceBuilder($resourceBuilder);

        return $response;
    }
}