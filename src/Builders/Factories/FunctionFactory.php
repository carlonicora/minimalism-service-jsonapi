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
    public static function initialise(
        ServicesProxy $servicesProxy
    ): void
    {
        self::$dataProvider = $servicesProxy->getDataProvider();
    }

    /**
     * @param string $functionName
     * @param array $parameters
     * @return FunctionFacade
     */
    private static function initialiseFunctionFacade(
        string $functionName,
        array $parameters=[],
    ): FunctionFacade
    {
        return new FunctionFacade(
            $functionName,
            $parameters
        );
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
        array $parameters=[],
    ): FunctionFacade
    {
        $response = self::initialiseFunctionFacade(
            $functionName,
            $parameters,
        );
        $response->setTableInterface($tableInterface);

        return $response;
    }

    /**
     * @param string $tableClassName
     * @param string $functionName
     * @param array $parameters
     * @return FunctionFacade
     * @throws Exception
     */
    public static function buildFromTableName(
        string $tableClassName,
        string $functionName,
        array $parameters=[],
    ): FunctionFacade
    {
        $tableInterface = self::$dataProvider->create($tableClassName);

        return self::buildFromTableInterface(
            $tableInterface,
            $functionName,
            $parameters,
        );
    }

    /**
     * @param string $serviceInterfaceName
     * @param string $serviceInterfaceFunctionName
     * @param array $parameters
     * @return FunctionFacade
     */
    public static function buildFromServiceInterface(
        string $serviceInterfaceName,
        string $serviceInterfaceFunctionName,
        array $parameters
    ): FunctionFacade
    {
        $response = self::initialiseFunctionFacade($serviceInterfaceFunctionName, $parameters);
        $response->setServiceInterfaceName($serviceInterfaceName);

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

    /**
     * @param string $loaderClassName
     * @param string $functionName
     * @param array $parameters
     * @return FunctionFacade
     */
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