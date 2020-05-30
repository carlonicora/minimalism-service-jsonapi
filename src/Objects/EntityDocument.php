<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Objects;

use CarloNicora\Minimalism\Core\Services\Exceptions\ConfigurationException;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Events\JsonDataMapperErrorEvents;
use Exception;
use JsonException;

class EntityDocument
{
    /** @var string|null  */
    private ?string $entityName=null;

    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var EntityResource  */
    private ?EntityResource $resource=null;

    /** @var array|null  */
    private ?array $relationships=null;

    /**
     * ParameterDocument constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
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

            if (array_key_exists('relationships', $document) && count($document['relationships']) > 0){
                $this->relationships = [];
                foreach ($document['relationships'] ?? [] as $relationships) {
                    foreach ($relationships ?? [] as $relationshipName=>$relationship) {
                        $this->relationships[$relationshipName] = new EntityRelationship($relationshipName, $relationship['data']);
                    }
                }
            }

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

        return $this->relationships[$relationshipName]->getField($fieldName);
    }

    /**
     * @param string $entityName
     * @return string
     */
    private function getEntityFile(string $entityName) : string
    {
        $documentFile = $this->services->paths()->getRoot() . DIRECTORY_SEPARATOR
            . 'data' . DIRECTORY_SEPARATOR
            . 'entities' . DIRECTORY_SEPARATOR
            . $entityName . '.json';
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