<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Objects;

use CarloNicora\Minimalism\Core\Services\Exceptions\ConfigurationException;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Events\JsonDataMapperErrorEvents;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use Exception;
use JsonException;

class EntityDocument
{
    /** @var string|null  */
    private ?string $entityName=null;

    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var JsonDataMapper  */
    private JsonDataMapper $mapper;

    /** @var EntityResource  */
    private ?EntityResource $resource=null;

    /**
     * ParameterDocument constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
        $this->mapper = $services->service(JsonDataMapper::class);
    }

    /**
     * @param string $entityName
     * @throws Exception|ConfigurationException
     */
    public function loadEntity(string $entityName) : void
    {
        $this->entityName = $entityName;

        try {
            $document = json_decode(
                $this->getEntityFile($entityName),
                true,
                512,
                JSON_THROW_ON_ERROR);

            $this->resource = new EntityResource($document['data']);

        } catch (JsonException $e) {
            $this->services->logger()->error()->log(
                JsonDataMapperErrorEvents::CONFIGURATION_FILE_MISCONFIGURED($entityName)
            )->throw(ConfigurationException::class);
        }
    }

    /**
     * @return string|null
     */
    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    /**
     * @param string $fieldName
     * @return EntityField
     * @throws Exception|null
     */
    public function getField(string $fieldName) : ?EntityField
    {
        if (strpos($fieldName, '.') === false){
            return $this->resource->getField($fieldName);
        }

        [$relationshipName, $fieldName] = explode('.', $fieldName);

        if (($relationship = $this->resource->getRelationship($relationshipName)) !== null){
            return $relationship->getResource()->getField($fieldName);
        }

        return null;
    }

    /**
     * @param string $entityName
     * @return string
     */
    private function getEntityFile(string $entityName) : string
    {
        if (($jsonDirectory = $this->mapper->getJsonEntitiesPath()) === null){
            $jsonDirectory = $this->services->paths()->getRoot() . DIRECTORY_SEPARATOR
                . 'data' . DIRECTORY_SEPARATOR
                . 'entities' . DIRECTORY_SEPARATOR;
        }

        if (substr($jsonDirectory, -1) !== DIRECTORY_SEPARATOR) {
            $jsonDirectory .= DIRECTORY_SEPARATOR;
        }

        $documentFile = $jsonDirectory . $entityName . '.json';
        return file_get_contents($documentFile);
    }

    /**
     * @return EntityResource
     */
    public function getResource(): EntityResource
    {
        return $this->resource;
    }
}