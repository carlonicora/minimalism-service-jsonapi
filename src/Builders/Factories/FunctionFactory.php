<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Factories;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;

class FunctionFactory
{
    /**
     * @var MySQL
     */
    private static MySQL $mysql;

    /**
     * @param MySQL $mysql
     */
    public static function initialise(MySQL $mysql): void
    {
        self::$mysql = $mysql;
    }

    /**
     * @param string $functionName
     * @param array $parameters
     * @param bool $isSingleRead
     * @return FunctionFacade
     */
    private static function initialiseFunctionFacade(
        string $functionName,
        array $parameters=[],
        bool $isSingleRead=false
    ): FunctionFacade
    {
        return new FunctionFacade($functionName, $parameters, $isSingleRead);
    }

    /**
     * @param TableInterface $tableInterface
     * @param string $functionName
     * @param array $parameters
     * @param bool $isSingleRead
     * @return FunctionFacade
     */
    public static function buildFromTableInterface(
        TableInterface $tableInterface,
        string $functionName,
        array $parameters=[],
        bool $isSingleRead=false
    ): FunctionFacade
    {
        $response = self::initialiseFunctionFacade($functionName, $parameters, $isSingleRead);
        $response->setTableInterface($tableInterface);

        return $response;
    }

    /**
     * @param string $tableClassName
     * @param string $functionName
     * @param array $parameters
     * @param bool $isSingleRead
     * @return FunctionFacade
     * @throws Exception
     */
    public static function buildFromTableName(
        string $tableClassName,
        string $functionName,
        array $parameters=[],
        bool $isSingleRead=false
    ): FunctionFacade
    {
        $tableInterface = self::$mysql->create($tableClassName);

        return self::buildFromTableInterface($tableInterface, $functionName, $parameters, $isSingleRead);
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

    public static function buildFromLoaderName(
        string $loaderClassName,
        string $functionName,
        array $parameters=[]
    ): FunctionFacade
    {
        $response = self::initialiseFunctionFacade($functionName, $parameters);
        $response->setLoaderClassName($loaderClassName);

        return $response;
    }
}