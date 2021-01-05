<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Commands;

use CarloNicora\Minimalism\Exceptions\RecordNotFoundException;
use CarloNicora\Minimalism\Interfaces\CacheBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\ParametersFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Factories\DataReadersFactory;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\DataReaderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use Exception;

class ResourceReader
{
    /** @var ResourceBuilderFactory  */
    private ResourceBuilderFactory $resourceFactory;

    /**
     * ResourceReader constructor.
     * @param ServicesProxy $servicesProxy
     */
    public function __construct(
        private ServicesProxy $servicesProxy,
    ) {
        $this->resourceFactory = new ResourceBuilderFactory(
            servicesProxy: $this->servicesProxy,
        );
    }

    /**
     * @param string $builderName
     * @param CacheBuilderInterface|null $cacheBuilder
     * @param AttributeBuilderInterface $attribute
     * @param array $parameters
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws Exception|RecordNotFoundException
     */
    public function generateResourceObjectByFieldValue(
        string $builderName,
        ?CacheBuilderInterface $cacheBuilder,
        AttributeBuilderInterface $attribute,
        array $parameters,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ) : array
    {
        $response = null;

        if ($cacheBuilder !== null && $this->servicesProxy->useCache()){
            $response = $this->servicesProxy->getCacheProvider()
                ? $this->servicesProxy->getCacheProvider()->read($cacheBuilder, CacheBuilderInterface::JSON)
                : null;
            if ($response !== null) {
                /** @noinspection UnserializeExploitsInspection */
                $response = unserialize($response);
            }
        }

        if ($response === null || $response === false) {
            $resourceBuilder = $this->resourceFactory->createResourceBuilder($builderName);

            $isMainTable = true;
            $tableName = $resourceBuilder->getTableName();

            if ($attribute->getResourceBuilder()->getTableName() !== $tableName) {
                $isMainTable = false;
            }

            $values = ParametersFacade::prepareParameters($parameters, $positionInRelationship);

            if ($isMainTable && $attribute->getName() === 'id') {
                $response = $this->generateResourceObject(
                    $resourceBuilder,
                    FunctionFactory::buildFromTableName(
                        $tableName,
                        'loadById',
                        $values,
                        true
                    ),
                    $loadRelationshipsLevel,
                    $relationshipParameters,
                    $positionInRelationship
                );
            } else {
                $fieldName = $isMainTable ? $attribute->getDatabaseFieldName() : $attribute->getDatabaseFieldRelationship();
                $response = $this->generateResourceObject(
                    $resourceBuilder,
                    FunctionFactory::buildFromTableName(
                        $tableName,
                        'loadByField',
                        [$fieldName => $values[0]]
                    ),
                    $loadRelationshipsLevel,
                    $relationshipParameters,
                    $positionInRelationship
                );
            }

            if ($cacheBuilder !== null && $this->servicesProxy->useCache()) {
                $this->servicesProxy->getCacheProvider()?->save($cacheBuilder, serialize($response), CacheBuilderInterface::JSON);
            }
        }

        return $response;
    }

    /**
     * @param string $builderName
     * @param CacheBuilderInterface|null $cacheBuilder
     * @param FunctionFacade $function
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws Exception|RecordNotFoundException
     */
    public function generateResourceObjectsByFunction(
        string $builderName,
        ?CacheBuilderInterface $cacheBuilder,
        FunctionFacade $function,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ) : array
    {
        $response = null;

        if ($cacheBuilder !== null && $this->servicesProxy->useCache()) {
            $response = $this->servicesProxy->getCacheProvider()
                ? $this->servicesProxy->getCacheProvider()->read($cacheBuilder, CacheBuilderInterface::JSON)
                : null;
            if ($response !== null) {
                /** @noinspection UnserializeExploitsInspection */
                $response = unserialize($response);
            }
        }

        if ($response === null) {
            if ($function->isResourceBuilder() && $function->getResourceBuilderClass() === $builderName && method_exists($function->getResourceBuilder(), $function->getFunctionName())) {
                $values = ParametersFacade::prepareParameters($relationshipParameters, $positionInRelationship);
                $callable = [$function->getResourceBuilder(), $function->getFunctionName()];
                $response = $callable(...$values);
            } else {
                $resourceBuilder = $this->resourceFactory->createResourceBuilder($builderName);
                $response = $this->generateResourceObject(
                    $resourceBuilder,
                    $function,
                    $loadRelationshipsLevel,
                    $relationshipParameters,
                    $positionInRelationship
                );
            }

            if ($cacheBuilder !== null && $this->servicesProxy->useCache()) {
                $this->servicesProxy->getCacheProvider()?->save($cacheBuilder, serialize($response), CacheBuilderInterface::JSON);
            }
        }

        return $response;
    }

    /**
     * @param string $builderName
     * @param CacheBuilderInterface|null $cacheBuilder
     * @param array $dataList
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws Exception
     */
    public function generateResourceObjectByData(
        string $builderName,
        ?CacheBuilderInterface $cacheBuilder,
        array $dataList,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): array
    {
        $response = null;

        if ($cacheBuilder !== null && $this->servicesProxy->useCache()){
            $response = $this->servicesProxy->getCacheProvider()
                ? $this->servicesProxy->getCacheProvider()->read($cacheBuilder, CacheBuilderInterface::JSON)
                : null;
            if ($response !== null) {
                /** @noinspection UnserializeExploitsInspection */
                $response = unserialize($response);
            }
        }

        if ($response === null) {
            $response = [];
            $resourceBuilder = $this->resourceFactory->createResourceBuilder($builderName);

            foreach ($dataList as $data) {
                $response[] = $resourceBuilder->buildResourceObject($data, $loadRelationshipsLevel, $relationshipParameters, $positionInRelationship);
            }

            if ($cacheBuilder !== null && $this->servicesProxy->useCache()) {
                $this->servicesProxy->getCacheProvider()->save($cacheBuilder, serialize($response), CacheBuilderInterface::JSON);
            }
        }

        return $response;
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     * @param FunctionFacade $function
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws Exception|RecordNotFoundException
     */
    private function generateResourceObject(
        ResourceBuilderInterface $resourceBuilder,
        FunctionFacade $function,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ) : array
    {
        $dataList = $this->readResourceObjectData($function);

        if (!empty($dataList) && !array_key_exists(0, $dataList)) {
            $dataList = [$dataList];
        }

        $response = [];

        foreach ($dataList as $data) {
            $response[] = $resourceBuilder->buildResourceObject($data, $loadRelationshipsLevel, $relationshipParameters, $positionInRelationship);
        }

        return $response;
    }

    /**
     * @param FunctionFacade $function
     * @param array $positionInRelationship
     * @return array
     * @throws Exception|RecordNotFoundException
     */
    public function readResourceObjectData(
        FunctionFacade $function,
        array $positionInRelationship=[]
    ): array
    {
        $response = null;

        $readerFactory = new DataReadersFactory(
            servicesProxy: $this->servicesProxy
        );
        $parameters = ParametersFacade::prepareParameters($function->getParameters(), $positionInRelationship, true);

        if ($function->getType() === FunctionFacade::LOADER){
            $loaderClassName = $function->getLoaderClassName();
            $loader = new $loaderClassName(
                $this->servicesProxy
            );

            $response = $loader->{$function->getFunctionName()}(...$parameters);
        } elseif ($function->getType() === FunctionFacade::TABLE) {
            /** @var DataReaderInterface $reader */
            $reader = $readerFactory->create(
                $function,
                $parameters
            );

            if ($function->isSingleRead()) {
                $response = [$reader->getSingle()];
            } else {
                $response = $reader->getList();
            }
        }

        return $response;
    }
}