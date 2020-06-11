<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Commands;

use CarloNicora\Minimalism\Core\Events\MinimalismInfoEvents;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
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

    /**
     * ResourceReader constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services) {
        $this->services = $services;
        $this->resourceFactory = new ResourceBuilderFactory($this->services);
    }

    /**
     * @param string $builderName
     * @param AttributeBuilderInterface $attribute
     * @param $value
     * @param bool $loadRelationships
     * @return array
     * @throws DbRecordNotFoundException|Exception
     */
    public function generateResourceObjectByFieldValue(string $builderName, AttributeBuilderInterface $attribute, $value, bool $loadRelationships=false) : array
    {
        $resourceBuilder = $this->resourceFactory->createResourceBuilder($builderName);
        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Builder ' . $builderName . ' created'));

        $isMainTable = true;
        $tableName = $resourceBuilder->getTableName();

        if ($attribute->getResourceBuilder()->getTableName() !== $tableName){
            $isMainTable = false;
        }

        if ($isMainTable && $attribute->getName() === 'id') {
            return $this->generateResourceObject($resourceBuilder, $tableName, 'loadFromId', [$value], $loadRelationships, true);
        }

        $fieldName = $isMainTable ? $attribute->getDatabaseFieldName() : $attribute->getDatabaseFieldRelationship();
        return $this->generateResourceObject($resourceBuilder, $tableName, 'loadByField', [$fieldName, $value], $loadRelationships);
    }

    /**
     * @param string $builderName
     * @param string $functionName
     * @param array $parameters
     * @param bool $loadRelationships
     * @return array
     * @throws DbRecordNotFoundException|Exception
     */
    public function generateResourceObjectsByFunction(string $builderName, string $functionName, array $parameters=[], bool $loadRelationships=false) : array
    {
        $resourceBuilder = $this->resourceFactory->createResourceBuilder($builderName);
        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Resource Builder ' . $builderName . ' created'));

        if (method_exists($resourceBuilder, $functionName)){
            return $resourceBuilder->$functionName(...$parameters);
        }

        return $this->generateResourceObject($resourceBuilder, $resourceBuilder->getTableName(), $functionName, $parameters, $loadRelationships);
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     * @param string $tableName
     * @param string $functionName
     * @param array $parameters
     * @param bool $loadRelationships
     * @param bool $iSingleRead
     * @return array
     * @throws DbRecordNotFoundException|Exception
     */
    private function generateResourceObject(ResourceBuilderInterface $resourceBuilder, string $tableName, string $functionName, array $parameters, bool $loadRelationships=false, bool $iSingleRead=false) : array
    {
        $dataList = $this->readResourceObjectData($tableName, $functionName, $parameters, $iSingleRead);

        $response = [];

        foreach ($dataList as $data){
            $response[] = $resourceBuilder->buildResourceObject($data, $loadRelationships);
        }

        return $response;
    }

    /**
     * @param string $tableName
     * @param string $functionName
     * @param array $parameters
     * @param bool $iSingleRead
     * @return array
     * @throws DbRecordNotFoundException|Exception
     */
    public function readResourceObjectData(string $tableName, string $functionName, array $parameters, bool $iSingleRead): array
    {
        $readerFactory = new DataReadersFactory($this->services);
        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Data Reader Initialised'));

        /** @var DataReaderInterface $reader */
        $reader = $readerFactory->create(
            $tableName,
            $functionName,
            $parameters
        );

        if ($iSingleRead) {
            $response = [];
            $response[] = $reader->getSingle();
        } else {
            $response = $reader->getList();
        }
        $this->services->logger()->info()->log(new MinimalismInfoEvents(9, null, 'Data Read (' . $tableName . ' - ' . $functionName . ' )'));

        return $response;
    }
}