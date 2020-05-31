<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Facades;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Factories\DataReadersFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityRelationship;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityResource;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;

class ResourceObjectFacade
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var MySQL  */
    private MySQL $mysql;

    /** @var EntityDocument  */
    private ?EntityDocument $document=null;

    /**
     * ResourceObjectFacade constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
        $this->mysql = $services->service(MySQL::class);
    }

    /**
     * @param EntityDocument $document
     */
    public function setDocument(EntityDocument $document): void
    {
        $this->document = $document;
    }

    /**
     * @param EntityResource $entity
     * @param ResourceObject $data
     * @throws Exception
     */
    public function writeResourceObject(EntityResource $entity, ResourceObject $data) : void
    {
        /**
         * 1. Build base entity
         * 2. Extend base enity with one to ones
         * 3. Save base entity
         * 4. Update one to many
         */
        $response = $this->buildBaseEntity($entity, $data);

        foreach ($this->buildOneToOne($data) as $additionalAttributeKey=>$additionalAttributeValue){
            $response[$additionalAttributeKey] = $additionalAttributeValue;
        }

        /** @var TableInterface $table */
        $table = $this->mysql->create($entity->getTable());
        $table->update($response);

        //$this->updateOneToMany($data, $response);
    }

    /**
     * @param EntityResource $entity
     * @param ResourceObject $data
     * @return array
     * @throws Exception
     */
    private function buildBaseEntity(EntityResource $entity, ResourceObject $data) : array
    {
        $response = [];
        if ($data->id !== null){
            $reader = new DataReadersFactory($this->services);
            try {
                $response = $reader->create($entity->getTable(), 'loadFromId', [$data->id])->getSingle();
            } catch (DbRecordNotFoundException $e) {
                $response = [];
            }
        }

        if ($data->attributes !== null){
            foreach ($data->attributes->prepare() as $fieldName=>$fieldValue){
                if (($field = $entity->getField($fieldName)) !== null) {
                    $response[$field->getDatabaseField()] = $fieldValue;
                }
            }
        }

        return $response;
    }

    /**
     * @param ResourceObject $data
     * @return array
     */
    private function buildOneToOne(ResourceObject $data) : array
    {
        $response = [];

        foreach ($data->relationships as $relationshipName=>$relationship){
            $relationshipResourceInfo = $this->document->getRelationship($relationshipName);
            if (
                $relationshipResourceInfo !== null
                && $relationshipResourceInfo->getType() === EntityRelationship::RELATIONSHIP_TYPE_ONE_TO_ONE
                && count($relationship->resourceLinkage->resources) === 1
            ){
                $response[
                $relationshipResourceInfo->getResource()->getId()->getDatabaseRelationshipField()
                ] = $relationship->resourceLinkage->resources[0]->id;
            }
        }

        return $response;
    }

    /**
     * @param ResourceObject $data
     * @param array $databaseData
     */
    private function updateOneToMany(ResourceObject $data, array $databaseData) : void
    {
        /*
        foreach ($data->relationships as $relationshipName=>$relationship){
            $relationshipResourceInfo = $this->document->getRelationship($relationshipName);
            if ($relationshipResourceInfo !== null && $relationshipResourceInfo->getType() === EntityRelationship::RELATIONSHIP_TYPE_ONE_TO_MANY){
                //LOAD ALL THE MANY-TO-MANY
            }

            foreach ($relationship->resourceLinkage->resources ?? [] as $resourceObject){
                $responseRelationship = $this->writeResourceObject($relationshipResourceInfo->getResource(), $resourceObject);

                if ($relationshipResourceInfo !== null && $relationshipResourceInfo->getType() === EntityRelationship::RELATIONSHIP_TYPE_ONE_TO_MANY){

                    //Use $response and $relationshipResponse
                }
            }

            if ($relationshipResourceInfo !== null && $relationshipResourceInfo->getType() === EntityRelationship::RELATIONSHIP_TYPE_ONE_TO_MANY){
                //DELETE THE NON UPDATED AND UPDATE THE OTHERS
            }
        }
        */
    }
}