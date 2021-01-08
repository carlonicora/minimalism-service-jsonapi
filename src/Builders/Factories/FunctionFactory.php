<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Factories;

use CarloNicora\Minimalism\Interfaces\DataInterface;
use CarloNicora\Minimalism\Interfaces\TableInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use Exception;

class FunctionFactory
{
    /**
     * @var DataInterface
     */
    public static DataInterface $dataProvider;

    /**
     * @param ServicesProxy $servicesProxy
     */
    public static function initialise(ServicesProxy $servicesProxy): void
    {
        self::$dataProvider = $servicesProxy->getDataProvider();
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
        $tableInterface = self::$dataProvider->create($tableClassName);

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