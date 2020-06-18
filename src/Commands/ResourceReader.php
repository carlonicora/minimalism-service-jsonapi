<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Commands;

use CarloNicora\Minimalism\Core\Events\MinimalismInfoEvents;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\Cacher\Exceptions\CacheNotFoundException;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheFactoryInterface;
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
     * @param $value
     * @param bool $loadRelationships
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function generateResourceObjectByFieldValue(string $builderName, ?CacheFactoryInterface $cache, AttributeBuilderInterface $attribute, $value, bool $loadRelationships=false) : array
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

            if ($isMainTable && $attribute->getName() === 'id') {
                $response = $this->generateResourceObject($resourceBuilder, $cache, $tableName, 'loadFromId', [$value], $loadRelationships, true);
            } else {
                $fieldName = $isMainTable ? $attribute->getDatabaseFieldName() : $attribute->getDatabaseFieldRelationship();
                $response = $this->generateResourceObject($resourceBuilder, $cache, $tableName, 'loadByField', [$fieldName, $value], $loadRelationships);
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
     * @param string $functionName
     * @param array $parameters
     * @param bool $loadRelationships
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function generateResourceObjectsByFunction(string $builderName, ?CacheFactoryInterface $cache, string $functionName, array $parameters=[], bool $loadRelationships=false) : array
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
            $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Builder ' . $builderName . ' created'));

            if (method_exists($resourceBuilder, $functionName)) {
                $response = $resourceBuilder->$functionName(...$parameters);
            } else {
                $response = $this->generateResourceObject($resourceBuilder, $cache, $resourceBuilder->getTableName(), $functionName, $parameters, $loadRelationships);
            }

            if ($cache !== null && ($dataCache = $cache->generateCache())){
                $this->cacher->create($dataCache, serialize($response));
            }
        }

        return $response;
    }

    /**
     * @param string $builderName
     * @param array $dataList
     * @param bool $loadRelationships
     * @return array
     * @throws Exception
     */
    public function generateResourceObjectByData(string $builderName, array $dataList, bool $loadRelationships=false): array
    {
        $resourceBuilder = $this->resourceFactory->createResourceBuilder($builderName);
        $response = [];

        foreach ($dataList as $data){
            $response[] = $resourceBuilder->buildResourceObject($data, $loadRelationships);
        }

        return $response;
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     * @param CacheFactoryInterface|null $cache
     * @param string $tableName
     * @param string $functionName
     * @param array $parameters
     * @param bool $loadRelationships
     * @param bool $iSingleRead
     * @return array
     * @throws DbRecordNotFoundException
     */
    private function generateResourceObject(ResourceBuilderInterface $resourceBuilder, ?CacheFactoryInterface $cache, string $tableName, string $functionName, array $parameters, bool $loadRelationships=false, bool $iSingleRead=false) : array
    {
        $dataList = $this->readResourceObjectData($cache, $tableName, $functionName, $parameters, $iSingleRead);

        $response = [];

        foreach ($dataList as $data){
            $response[] = $resourceBuilder->buildResourceObject($data, $loadRelationships);
        }

        return $response;
    }

    /**
     * @param CacheFactoryInterface|null $cache
     * @param string $tableName
     * @param string $functionName
     * @param array $parameters
     * @param bool $iSingleRead
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function readResourceObjectData(?CacheFactoryInterface $cache, string $tableName, string $functionName, array $parameters, bool $iSingleRead): array
    {
        $response = null;
        $cacher=null;

        if ($cache !== null && ($cacher = $cache->generateCache()) && ($dataCache = $cacher->getChildCache()) !== null) {
            try {
                $response = $this->cacher->readArray($dataCache);
                if ($iSingleRead) {
                    $response = [$response];
                }
            } catch (CacheNotFoundException $e) {
                $response = null;
            }
        }

        if ($response === null){
            $readerFactory = new DataReadersFactory($this->services);
            $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Data Reader Initialised'));

            $childCacheFactory = null;
            if ($cacher !== null){
                $childCacheFactory = $cacher->getChildCacheFactory($this->services, $cache->implementsGranularCache());
            }

            /** @var DataReaderInterface $reader */
            $reader = $readerFactory->create(
                $tableName,
                $functionName,
                $parameters,
                $childCacheFactory
            );

            if ($iSingleRead) {
                $response = [];
                $response[] = $reader->getSingle();
            } else {
                $response = $reader->getList();
            }
            $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Data Read (' . $tableName . ' - ' . $functionName . ' )'));
        }

        return $response;
    }
}