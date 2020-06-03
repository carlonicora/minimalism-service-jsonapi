<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Events\JsonDataMapperErrorEvents;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;
use CarloNicora\Minimalism\Services\JsonDataMapper\Wrappers\DataWrapper;
use Exception;

class DataWrapperFactory
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var EntityDocument  */
    private EntityDocument $entityDocument;

    /**
     * Parameter constructor.
     * @param ServicesFactory $services
     * @param string $entityName
     * @throws Exception
     */
    public function __construct(ServicesFactory $services, string $entityName)
    {
        $this->services = $services;


        $this->entityDocument = new EntityDocument($services);
        $this->entityDocument->loadEntity($entityName);
    }

    /**
     * @param string $fieldName
     * @param $fieldValue
     * @return DataWrapper
     * @throws Exception
     */
    public function generateSimpleLoader(string $fieldName, $fieldValue) : DataWrapper
    {
        $response = new DataWrapper($this->services);

        if (($field = $this->entityDocument->getField($fieldName)) === null) {
            $this->services->logger()->error()->log(
                JsonDataMapperErrorEvents::LOADER_FIELD_NOT_FOUND($fieldName, $this->entityDocument->getEntityName())
            )->throw(Exception::class);
        }

        if (strpos($fieldName, '.') === false){
            $response->setTableName($field->getTable());
            $response->setIsSingle($field->isPrimaryKey());
            if ($field->isPrimaryKey()) {
                $response->setFunction('loadFromId');
                $response->setParameters([$fieldValue]);
            } else {
                $response->setFunction('loadByField');
                $response->setParameters([$field->getDatabaseField(), $fieldValue]);
            }
        } else {
            $response->setTableName($this->entityDocument->getResource()->getTable());
            $response->setFunction('loadByField');
            $response->setParameters([$field->getDatabaseRelationshipField(), $fieldValue]);
        }

        return $response;
    }

    /**
     * @param string $tableName
     * @param string $customFunction
     * @param array $parameters
     * @return DataWrapper
     */
    public function generateCustomLoader(string $tableName, string $customFunction, array $parameters=[]) : DataWrapper
    {
        $response = new DataWrapper($this->services);
        $response->setTableName($tableName);
        $response->setFunction($customFunction);
        $response->setParameters($parameters);

        return $response;
    }

    /**
     * @return EntityDocument
     */
    public function getEntityDocument(): EntityDocument
    {
        return $this->entityDocument;
    }
}