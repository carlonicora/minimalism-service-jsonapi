<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheFactoryInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipTypeInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Events\JsonDataMapperErrorEvents;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use CarloNicora\Minimalism\Services\MySQL\Interfaces\TableInterface;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;

class ResourceWriter
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var ResourceBuilderFactory  */
    private ResourceBuilderFactory $resourceFactory;

    /** @var JsonDataMapper  */
    private JsonDataMapper $mapper;

    /** @var ResourceReader  */
    private ResourceReader $resourceReader;

    /** @var MySQL  */
    private MySQL $mysql;

    /**
     * ResourceReader constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services) {
        $this->services = $services;
        $this->mapper = $services->service(JsonDataMapper::class);
        $this->mysql = $services->service(MySQL::class);

        $this->resourceFactory = new ResourceBuilderFactory($this->services);
        $this->resourceReader = new ResourceReader($this->services);
    }

    /**
     * @param Document $data
     * @param CacheFactoryInterface|null $cache
     * @param string $resourceBuilderName
     * @throws DbSqlException
     * @throws Exception
     */
    public function writeDocument(Document $data, ?CacheFactoryInterface $cache, string $resourceBuilderName) : void
    {
        $resourceBuilder = $this->resourceFactory->createResourceBuilder($resourceBuilderName);
        $this->validateAndDecryptDocument($data, $resourceBuilder);

        foreach ($data->resources ?? [] as $resourceObject) {
            $this->writeResourceObject($resourceBuilder, $cache, $resourceObject);

        }
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     * @param CacheFactoryInterface|null $cache
     * @param ResourceObject $resourceObject
     * @throws DbSqlException
     * @throws Exception
     */
    private function writeResourceObject(ResourceBuilderInterface $resourceBuilder, ?CacheFactoryInterface $cache, ResourceObject $resourceObject): void
    {
        $response = $this->buildBaseEntity($resourceBuilder, $cache, $resourceObject);

        /** @var RelationshipBuilderInterface $relationship */
        foreach ($resourceBuilder->getRelationships() as $relationship){
            if ($relationship->getType() === RelationshipTypeInterface::RELATIONSHIP_ONE_TO_ONE) {
                try {
                    $relationshipValue = $resourceObject->relationship($relationship->getName())->resourceLinkage->resources[0]->id;
                    if ($this->mapper->getDefaultEncrypter() !== null && $relationship->getAttribute()->isEncrypted()) {
                        $relationshipValue = $this->mapper->getDefaultEncrypter()->decryptId($relationshipValue);
                    }
                    $response[$relationship->getAttribute()->getDatabaseFieldRelationship()] = $relationshipValue;
                } catch (Exception $e) {}
            }
        }

        /** @var TableInterface $table */
        $table = $this->mysql->create($resourceBuilder->getTableName());
        $table->update($response);

        if ($resourceObject->id === null && ($id = $resourceBuilder->getAttribute('id')) !== null){
            $resourceObject->id = $response[$id->getDatabaseFieldName()];
        }

        /** @var RelationshipBuilderInterface $relationship */
        foreach ($resourceBuilder->getRelationships() as $relationship) {
            if ($relationship->getType() === RelationshipTypeInterface::RELATIONSHIP_MANY_TO_MANY) {
                $this->updateOneToMany($resourceBuilder, $resourceObject, $relationship->getName());
            }
        }
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     * @param ResourceObject $resourceObject
     * @param string $relationshipName
     * @throws Exception
     */
    private function updateOneToMany(ResourceBuilderInterface $resourceBuilder, ResourceObject $resourceObject, string $relationshipName) : void
    {
        $relationship = $resourceObject->relationship($relationshipName);
        if (($relationshipResourceInfo = $resourceBuilder->getRelationship($relationshipName)) !== null) {
            /** @var TableInterface $table */
            $table = $this->mysql->create($relationshipResourceInfo->getManyToManyRelationshipTableClass());

            $currentRelationshipArray = [];
            $newRelationshipsId = [];

            if ($resourceObject->id !== null && ($id = $resourceBuilder->getAttribute('id')) !== null) {
                $currentRelationshipArray = $table->loadByField($id->getDatabaseFieldRelationship() ?? $id->getDatabaseFieldName(), $resourceObject->id);
            }

            foreach ($relationship->resourceLinkage->resources ?? [] as $singleResourceObject) {
                $newRelationshipsId[] = (int)$singleResourceObject->id;
            }

            $currentRelationshipsId = [];
            foreach ($currentRelationshipArray as $currentRelationship) {
                $id = $currentRelationship[$relationshipResourceInfo->getAttribute()->getDatabaseFieldName()];
                if (!in_array($id, $newRelationshipsId, true)) {
                    $table->delete($currentRelationship);
                } else {
                    $currentRelationshipsId[] = $id;
                }
            }

            $newRelationships = [];

            foreach ($newRelationshipsId as $newRelationshipId) {
                if (!in_array($newRelationshipId, $currentRelationshipsId, true) && ($id=$resourceBuilder->getAttribute('id')) !== null) {
                    $newRelationships[] = [
                        $id->getDatabaseFieldName() => $resourceObject->id,
                        $relationshipResourceInfo->getAttribute()->getDatabaseFieldName() => $newRelationshipId
                    ];
                }
            }

            if (!empty($newRelationships)) {
                $table->update($newRelationships);
            }
        }
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     * @param CacheFactoryInterface|null $cache
     * @param ResourceObject $resourceObject
     * @return array
     * @throws Exception
     */
    private function buildBaseEntity(ResourceBuilderInterface $resourceBuilder, ?CacheFactoryInterface $cache, ResourceObject $resourceObject) : array
    {
        $response = [];
        if ($resourceObject->id !== null){
            try {
                $response = $this->resourceReader->readResourceObjectData($cache, $resourceBuilder->getTableName(), 'loadFromId', [$resourceObject->id], true)[0];
            } catch (DbRecordNotFoundException $e) {
                $response = [];
            }
        }

        if ($resourceObject->attributes !== null){
            foreach ($resourceObject->attributes->prepare() as $fieldName=>$fieldValue){
                if ((($field = $resourceBuilder->getAttribute($fieldName)) !== null) && !$field->isReadOnly()) {
                    $response[$field->getDatabaseFieldName()] = $fieldValue;
                }
            }
        }

        return $response;
    }

    /**
     * @param Document $data
     * @param ResourceBuilderInterface $resourceBuilder
     * @throws Exception
     */
    private function validateAndDecryptDocument(Document $data, ResourceBuilderInterface $resourceBuilder): void
    {
        foreach ($data->resources ?? [] as $resourceObject){
            $isNewResource = $resourceObject->id === null;

            $this->validateAndTranslateAttributes($isNewResource, $resourceObject, $resourceBuilder);
            $this->validateAndDecryptRelationships($isNewResource, $resourceObject, $resourceBuilder);
        }
    }

    /**
     * @param bool $isNewResource
     * @param ResourceObject $resourceObject
     * @param ResourceBuilderInterface $resourceBuilder
     * @throws Exception
     */
    private function validateAndTranslateAttributes(bool $isNewResource, ResourceObject $resourceObject, ResourceBuilderInterface $resourceBuilder): void
    {
        $field = $resourceBuilder->getAttribute('id');

        if (!$isNewResource && $field === null){
            $this->services->logger()->error()->log(
                JsonDataMapperErrorEvents::REQUIRED_FIELD_MISSING('id')
            )->throw();
        }elseif ($field !== null && $resourceObject->id !== null && $this->mapper->getDefaultEncrypter() !== null && $field->isEncrypted()) {
            $resourceObject->id = $this->mapper->getDefaultEncrypter()->decryptId($resourceObject->id);
        }

        /** @var AttributeBuilderInterface $attribute */
        foreach ($resourceBuilder->getAttributes() ?? [] as $attribute){
            if ($attribute->getName() !== 'id') {
                try {
                    $attributeValue = $resourceObject->attributes->get($attribute->getName());

                    $attributeValue = $attribute->getType()->transformValue($attributeValue);

                    $resourceObject->attributes->update($attribute->getName(), $attributeValue);
                } catch (Exception $e) {
                    if ($attribute->isRequired()) {
                        $this->services->logger()->error()->log(
                            JsonDataMapperErrorEvents::REQUIRED_FIELD_MISSING($attribute->getName())
                        )->throw();
                    }
                }
            }
        }
    }

    /**
     * @param bool $isNewResource
     * @param ResourceObject $resourceObject
     * @param ResourceBuilderInterface $resourceBuilder
     * @throws Exception
     */
    private function validateAndDecryptRelationships(bool $isNewResource, ResourceObject $resourceObject, ResourceBuilderInterface $resourceBuilder): void
    {
        /**
         * @var  $relationshipName
         * @var RelationshipBuilderInterface $relationship
         */
        foreach ($resourceBuilder->getRelationships() ?? [] as $relationshipName=>$relationship){
            if ($isNewResource
                && $relationship->isRequired()
                &&
                (
                    !array_key_exists($relationship->getName(), $resourceObject->relationships)
                    ||
                    count($resourceObject->relationships[$relationship->getName()]->resourceLinkage->resources) === 0
                )
            ){
                $this->services->logger()->error()->log(
                    JsonDataMapperErrorEvents::REQUIRED_RELATIONSHIP_MISSING($relationshipName)
                )->throw();
            }

            foreach ($resourceObject->relationship($relationshipName)->resourceLinkage->resources ?? [] as $resourceLink){
                if ( $resourceLink->type !== $relationship->getResourceObjectName()){
                    $this->services->logger()->error()->log(
                        JsonDataMapperErrorEvents::RELATIONSHIP_RESOURCE_MISMATCH($resourceLink->type, $relationship->getType())
                    )->throw();
                }

                if ($resourceLink->id !== null && $this->mapper->getDefaultEncrypter() !== null && $relationship->getAttribute()->isEncrypted()) {
                    $resourceLink->id = $this->mapper->getDefaultEncrypter()->decryptId($resourceLink->id);
                }
            }
        }
    }
}