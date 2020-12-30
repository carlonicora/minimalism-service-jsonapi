<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Commands;

use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\ParametersFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Factories\DataReadersFactory;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\DataReaderInterface;
use CarloNicora\Minimalism\Services\JsonApi\JsonApi;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Redis\Redis;
use Exception;

class ResourceReader
{
    /** @var ResourceBuilderFactory  */
    private ResourceBuilderFactory $resourceFactory;

    /**
     * ResourceReader constructor.
     * @param JsonApi $jsonApi
     * @param Cacher $cacher
     * @param Redis $redis
     * @param MySQL $mysql
     */
    public function __construct(
        private JsonApi $jsonApi,
        private Cacher $cacher,
        private Redis $redis,
        private MySQL $mysql,
    ) {
        $this->resourceFactory = new ResourceBuilderFactory($this->jsonApi);
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
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function readResourceObjectData(
        FunctionFacade $function,
        array $positionInRelationship=[]
    ): array
    {
        $response = null;

        $readerFactory = new DataReadersFactory(
            $this->redis,
            $this->mysql,
            $this->cacher
        );
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
        }

        return $response;
    }
}