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
    private EntityDocument $parameterDocument;

    /**
     * Parameter constructor.
     * @param ServicesFactory $services
     * @param EntityDocument $parameterDocument
     */
    public function __construct(ServicesFactory $services, EntityDocument $parameterDocument)
    {
        $this->services = $services;
        $this->parameterDocument = $parameterDocument;
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

        if (($field = $this->parameterDocument->getField($fieldName)) === null) {
            $this->services->logger()->error()->log(
                JsonDataMapperErrorEvents::LOADER_FIELD_NOT_FOUND($fieldName, $this->parameterDocument->getEntityName())
            )->throw(Exception::class);
        }

        $response->setIsSingle($field->isPrimaryKey());
        $response->setTableName($field->getTable());
        $response->setFunction('loadById');
        $response->setParameters([$fieldValue]);

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
}