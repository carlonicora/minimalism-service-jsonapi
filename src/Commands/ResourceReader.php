<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Commands;

use CarloNicora\Minimalism\Core\Events\MinimalismInfoEvents;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\ParametersFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Factories\DataReadersFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\DataReaderInterface;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use Exception;

class ResourceReader
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var ResourceBuilderFactory  */
    private ResourceBuilderFactory $resourceFactory;

    /** @var Cacher  */
    private Cacher $cacher;

    /**
     * ResourceReader constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services) {
        $this->services = $services;
        $this->cacher = $services->service(Cacher::class);
        $this->resourceFactory = new ResourceBuilderFactory($this->services);
    }

    /**
     * @param string $builderName
     * @param CacheBuilder|null $cacheBuilder
     * @param AttributeBuilderInterface $attribute
     * @param array $parameters
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function generateResourceObjectByFieldValue(
        string $builderName,
        ?CacheBuilder $cacheBuilder,
        AttributeBuilderInterface $attribute,
        array $parameters,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ) : array
    {
        $response = null;

        if ($cacheBuilder !== null && $this->cacher->useCaching() && ($response = $this->cacher->read($cacheBuilder, CacheBuilder::JSON)) !== null) {
            /** @noinspection UnserializeExploitsInspection */
            $response = unserialize($response);
        }

        if ($response === null || $response === false) {
            $resourceBuilder = $this->resourceFactory->createResourceBuilder($builderName);
            $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Builder ' . $builderName . ' created'));

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
                        'loadFromId',
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

            if ($cacheBuilder !== null && $this->cacher->useCaching()) {
                $this->cacher->save($cacheBuilder, serialize($response), CacheBuilder::JSON);
            }
        }

        return $response;
    }

    /**
     * @param string $builderName
     * @param CacheBuilder|null $cacheBuilder
     * @param FunctionFacade $function
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function generateResourceObjectsByFunction(
        string $builderName,
        ?CacheBuilder $cacheBuilder,
        FunctionFacade $function,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ) : array
    {
        $response = null;

        if ($cacheBuilder !== null && $this->cacher->useCaching() && ($response = $this->cacher->read($cacheBuilder, CacheBuilder::JSON)) !== null) {
            /** @noinspection UnserializeExploitsInspection */
            $response = unserialize($response);
        }

        if ($response === null) {
            $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Builder ' . $builderName . ' created'));

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

            if ($cacheBuilder !== null && $this->cacher->useCaching()) {
                $this->cacher->save($cacheBuilder, serialize($response), CacheBuilder::JSON);
            }
        }

        return $response;
    }

    /**
     * @param string $builderName
     * @param CacheBuilder|null $cacheBuilder
     * @param array $dataList
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws Exception
     */
    public function generateResourceObjectByData(
        string $builderName,
        ?CacheBuilder $cacheBuilder,
        array $dataList,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): array
    {
        $response = null;

        if ($cacheBuilder !== null && $this->cacher->useCaching() && ($response = $this->cacher->read($cacheBuilder, CacheBuilder::JSON)) !== null){
            /** @noinspection UnserializeExploitsInspection */
            $response = unserialize($response);
        }

        if ($response === null) {
            $response = [];
            $resourceBuilder = $this->resourceFactory->createResourceBuilder($builderName);

            foreach ($dataList as $data) {
                $response[] = $resourceBuilder->buildResourceObject($data, $loadRelationshipsLevel, $relationshipParameters, $positionInRelationship);
            }

            if ($cacheBuilder !== null && $this->cacher->useCaching()) {
                $this->cacher->save($cacheBuilder, serialize($response), CacheBuilder::JSON);
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
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    private function generateResourceObject(
        ResourceBuilderInterface $resourceBuilder,
        FunctionFacade $function,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ) : array
    {
        $response = null;

        if ($function->getCacheBuilder() !== null && $this->cacher->useCaching() && ($response = $this->cacher->read($function->getCacheBuilder(), CacheBuilder::JSON)) !== null) {
            /** @noinspection UnserializeExploitsInspection */
            $response = unserialize($response);
        }

        if ($response === null) {
            $dataList = null;
            if ($function->getCacheBuilder() !== null && $this->cacher->useCaching() && ($dataList = $this->cacher->readArray($function->getCacheBuilder(), CacheBuilder::DATA)) !== null) {
                /** @noinspection UnserializeExploitsInspection */
                $dataList = unserialize($response);
            }

            if ($dataList === null) {
                $dataList = $this->readResourceObjectData($function);

                if (!empty($dataList) && !array_key_exists(0, $dataList)) {
                    $dataList = [$dataList];
                }

                if ($function->getCacheBuilder() !== null && $this->cacher->useCaching()) {
                    $this->cacher->saveArray($function->getCacheBuilder(), $dataList, CacheBuilder::DATA);
                }
            }

            $response = [];

            foreach ($dataList as $data) {
                $response[] = $resourceBuilder->buildResourceObject($data, $loadRelationshipsLevel, $relationshipParameters, $positionInRelationship);
            }

            if ($function->getCacheBuilder() !== null && $this->cacher->useCaching()){
                $this->cacher->save($function->getCacheBuilder(), serialize($response), CacheBuilder::JSON);
            }
        }

        return $response;
    }

    /**
     * @param FunctionFacade $function
     * @param array $positionInRelationship
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function readResourceObjectData(
        FunctionFacade $function,
        array $positionInRelationship=[]
    ): array
    {
        $response = null;

        $readerFactory = new DataReadersFactory($this->services);
        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Data Reader Initialised'));

        $parameters = ParametersFacade::prepareParameters($function->getParameters(), $positionInRelationship, true);

        if ($function->getType() === FunctionFacade::LOADER){
            $loaderClassName = $function->getLoaderClassName();
            $loader = new $loaderClassName($this->services);

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
            $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Data Read (' . $function->getTableName() . ' - ' . $function->getFunctionName() . ' )'));
        }

        return $response;
    }
}