<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Commands;

use CarloNicora\Minimalism\Core\Events\MinimalismInfoEvents;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\Cacher\Exceptions\CacheNotFoundException;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheFactoryInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts\ParametersFacade;
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
     * @param CacheFactoryInterface|null $cache
     * @param AttributeBuilderInterface $attribute
     * @param array $parameters
     * @param int $loadRelationshipsLevel
     * @param array $position
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function generateResourceObjectByFieldValue(
        string $builderName,
        ?CacheFactoryInterface $cache,
        AttributeBuilderInterface $attribute,
        array $parameters,
        int $loadRelationshipsLevel=0,
        array $position=[]
    ) : array
    {
        $response = null;

        if ($cache !== null && ($dataCache = $cache->generateCache())){
            try {
                /** @noinspection UnserializeExploitsInspection */
                $response = unserialize($this->cacher->read($dataCache));
            } catch (CacheNotFoundException $e) {
                $response = null;
            }
        }

        if ($response === null || $response === false) {
            $resourceBuilder = $this->resourceFactory->createResourceBuilder($builderName);
            $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Builder ' . $builderName . ' created'));

            $isMainTable = true;
            $tableName = $resourceBuilder->getTableName();

            if ($attribute->getResourceBuilder()->getTableName() !== $tableName) {
                $isMainTable = false;
            }

            $values = ParametersFacade::prepareParameters($parameters, $position);

            if ($isMainTable && $attribute->getName() === 'id') {
                $response = $this->generateResourceObject(
                    $resourceBuilder,
                    $cache,
                    FunctionFactory::buildFromTableName(
                        $tableName,
                        'loadFromId'
                    ),
                    $values,
                    $loadRelationshipsLevel,
                    true
                );
            } else {
                $fieldName = $isMainTable ? $attribute->getDatabaseFieldName() : $attribute->getDatabaseFieldRelationship();
                $response = $this->generateResourceObject(
                    $resourceBuilder,
                    $cache,
                    FunctionFactory::buildFromTableName(
                        $tableName,
                        'loadByField'
                    ),
                    [$fieldName, $values[0]],
                    $loadRelationshipsLevel
                );
            }

            if ($cache !== null && ($dataCache = $cache->generateCache())){
                $this->cacher->create($dataCache, serialize($response));
            }
        }

        return $response;
    }

    /**
     * @param string $builderName
     * @param CacheFactoryInterface|null $cache
     * @param FunctionFacade $function
     * @param array $parameters
     * @param int $loadRelationshipsLevel
     * @param array $position
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function generateResourceObjectsByFunction(
        string $builderName,
        ?CacheFactoryInterface $cache,
        FunctionFacade $function,
        array $parameters=[],
        int $loadRelationshipsLevel=0,
        array $position=[]
    ) : array
    {
        $response = null;

        if ($cache !== null && ($dataCache = $cache->generateCache())){
            try {
                /** @noinspection UnserializeExploitsInspection */
                $response = unserialize($this->cacher->read($dataCache));
            } catch (CacheNotFoundException $e) {
                $response = null;
            }
        }

        if ($response === null) {
            $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Builder ' . $builderName . ' created'));

            if ($function->isResourceBuilder() && $function->getResourceBuilderClass() === $builderName && method_exists($function->getResourceBuilder(), $function->getFunctionName())) {
                $values = ParametersFacade::prepareParameters($parameters, $position);
                $callable = [$function->getResourceBuilder(), $function->getFunctionName()];
                $response = $callable(...$values);
            } else {
                $resourceBuilder = $this->resourceFactory->createResourceBuilder($builderName);
                $response = $this->generateResourceObject($resourceBuilder, $cache, $function, $parameters, $loadRelationshipsLevel, false, $position);
            }

            if ($cache !== null && ($dataCache = $cache->generateCache())){
                $this->cacher->create($dataCache, serialize($response));
            }
        }

        return $response;
    }

    /**
     * @param string $builderName
     * @param CacheFactoryInterface|null $cache
     * @param array $dataList
     * @param int $loadRelationshipsLevel
     * @param array $parameters
     * @param array $position
     * @return array
     * @throws Exception
     */
    public function generateResourceObjectByData(
        string $builderName,
        ?CacheFactoryInterface $cache,
        array $dataList,
        int $loadRelationshipsLevel=0,
        array $parameters=[],
        array $position=[]
    ): array
    {
        $response = null;

        if ($cache !== null && ($dataCache = $cache->generateCache())){
            try {
                /** @noinspection UnserializeExploitsInspection */
                $response = unserialize($this->cacher->read($dataCache));
            } catch (CacheNotFoundException $e) {
                $response = null;
            }
        }

        if ($response === null) {
            $resourceBuilder = $this->resourceFactory->createResourceBuilder($builderName);

            foreach ($dataList as $data) {
                $response[] = $resourceBuilder->buildResourceObject($data, $parameters, $loadRelationshipsLevel, $position);
            }

            if ($cache !== null && ($dataCache = $cache->generateCache())){
                $this->cacher->create($dataCache, serialize($response));
            }
        }

        return $response;
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     * @param CacheFactoryInterface|null $cache
     * @param FunctionFacade $function
     * @param array $parameters
     * @param int $loadRelationshipsLevel
     * @param bool $iSingleRead
     * @param array $position
     * @return array
     * @throws DbRecordNotFoundException
     */
    private function generateResourceObject(
        ResourceBuilderInterface $resourceBuilder,
        ?CacheFactoryInterface $cache,
        FunctionFacade $function,
        array $parameters,
        int $loadRelationshipsLevel=0,
        bool $iSingleRead=false,
        array $position=[]
    ) : array
    {
        $dataCache = null;
        if ($cache !== null && ($cacher = $cache->generateCache()) !== null) {
            $dataCache = $cacher->getChildCacheFactory($this->services, $cache->implementsGranularCache());
        }

        $values = ParametersFacade::prepareParameters($parameters, $position);
        $dataList = $this->readResourceObjectData($dataCache, $function, $values, $iSingleRead);

        if (!empty($dataList) && !array_key_exists(0, $dataList)){
            $dataList = [$dataList];
        }

        $response = [];

        foreach ($dataList as $data){
            $response[] = $resourceBuilder->buildResourceObject($data, $parameters, $loadRelationshipsLevel, $position);
        }

        return $response;
    }

    /**
     * @param CacheFactoryInterface|null $cacheFactory
     * @param FunctionFacade $function
     * @param array $parameters
     * @param bool $iSingleRead
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function readResourceObjectData(
        ?CacheFactoryInterface $cacheFactory,
        FunctionFacade $function,
        array $parameters,
        bool $iSingleRead
    ): array
    {
        $response = null;

        $readerFactory = new DataReadersFactory($this->services);
        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Data Reader Initialised'));

        /** @var DataReaderInterface $reader */
        $reader = $readerFactory->create(
            $function,
            $parameters,
            $cacheFactory
        );

        if ($iSingleRead) {
            $response = [];
            $response[] = $reader->getSingle();
        } else {
            $response = $reader->getList();
        }


        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Data Read (' . $function->getTableName() . ' - ' . $function->getFunctionName() . ' )'));

        return $response;
    }
}