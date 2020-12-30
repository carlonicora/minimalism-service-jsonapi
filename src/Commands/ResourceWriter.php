<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Commands;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\Attributes;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipTypeInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\JsonApi;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Redis\Redis;
use Exception;
use RuntimeException;
use Throwable;

class ResourceWriter
{
    /** @var ResourceBuilderFactory  */
    private ResourceBuilderFactory $resourceFactory;

    /** @var ResourceReader  */
    private ResourceReader $resourceReader;

    /**
     * ResourceReader constructor.
     * @param JsonApi $jsonApi
     * @param MySQL $mysql
     * @param Cacher $cacher
     * @param Redis $redis
     */
    public function __construct(
        private JsonApi $jsonApi,
        private Cacher $cacher,
        private Redis $redis,
        private MySQL $mysql,
    ) {
        $this->resourceFactory = new ResourceBuilderFactory($this->jsonApi);
        $this->resourceReader = new ResourceReader($this->jsonApi, $this->cacher, $this->redis, $this->mysql);
    }

    /**
     * @param Document $data
     * @param CacheBuilder|null $cacheBuilder
     * @param string $resourceBuilderName
     * @param bool $updateRelationships
     * @throws Exception
     */
    public function writeDocument(Document $data, ?CacheBuilder $cacheBuilder, string $resourceBuilderName, bool $updateRelationships=false) : void
    {
        $resourceBuilder = $this->resourceFactory->createResourceBuilder($resourceBuilderName);
        $this->validateAndDecryptDocument($data, $resourceBuilder);

        foreach ($data->resources ?? [] as $resourceObject) {
            $this->writeResourceObject(
                $resourceBuilder,
                $cacheBuilder,
                $resourceObject,
                $updateRelationships
            );
        }

        $this->encryptDocument($data, $resourceBuilder);
    }

    /**
     * @param ResourceBuilderInterface $resourceBuilder
     * @param CacheBuilder|null $cacheBuilder
     * @param ResourceObject $resourceObject
     * @param bool $updateRelationships
     * @throws Exception
     */
    private function writeResourceObject(
        ResourceBuilderInterface $resourceBuilder,
        ?CacheBuilder $cacheBuilder,
        ResourceObject $resourceObject,
        bool $updateRelationships=false
    ): void
    {
        $response = $this->buildBaseEntity(
            $resourceBuilder,
            $resourceObject
        );

        if ($updateRelationships) {
            /** @var RelationshipBuilderInterface $relationship */
            foreach ($resourceBuilder->getRelationships() as $relationship) {
                if ($relationship->getType() === RelationshipTypeInterface::ONE_TO_ONE) {
                    try {
                        $relatedResources = $resourceObject->relationship($relationship->getName())->resourceLinkage->resources;
                        if (false === empty($relatedResources) && false === empty($relationshipValue = current($relatedResources)->id) && $relationship->getAttribute() !== null) {
                            $response[$relationship->getAttribute()->getDatabaseFieldRelationship()] = $relationshipValue;
                        }
                    } catch (Exception) {
                    }
                }
            }
        }

        $table = $this->mysql->create($resourceBuilder->getTableName());
        $table->update($response);

        if ($resourceObject->id === null && ($id = $resourceBuilder->getAttribute('id')) !== null){
            $resourceObject->id = $response[$id->getDatabaseFieldName()];
        }

        if ($updateRelationships) {
            /** @var RelationshipBuilderInterface $relationship */
            foreach ($resourceBuilder->getRelationships() as $relationship) {
                if ($relationship->getType() === RelationshipTypeInterface::MANY_TO_MANY) {
                    $this->updateOneToMany($resourceBuilder, $resourceObject, $relationship->getName());
                }
            }
        }

        if ($cacheBuilder !== null &&  $this->cacher->useCaching()){
            $this->cacher->invalidate($cacheBuilder);
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
                if ($relationshipResourceInfo->getAttribute() !== null) {
                    $id = $currentRelationship[$relationshipResourceInfo->getAttribute()->getDatabaseFieldName()];
                    if (!in_array($id, $newRelationshipsId, true)) {
                        $table->delete($currentRelationship);
                    } else {
                        $currentRelationshipsId[] = $id;
                    }
                }
            }

            $newRelationships = [];

            foreach ($newRelationshipsId as $newRelationshipId) {
                if (!in_array($newRelationshipId, $currentRelationshipsId, true) && ($id=$resourceBuilder->getAttribute('id')) !== null && $relationshipResourceInfo->getAttribute() !== null) {
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
     * @param ResourceObject $resourceObject
     * @return array
     * @throws Exception
     */
    private function buildBaseEntity(
        ResourceBuilderInterface $resourceBuilder,
        ResourceObject $resourceObject
    ) : array
    {
        $response = [];
        if ($resourceObject->id !== null){
            try {
                $response = $this->resourceReader->readResourceObjectData(
                    FunctionFactory::buildFromTableName(
                        $resourceBuilder->getTableName(),
                        'loadFromId',
                        [$resourceObject->id],
                        true
                    )
                )[0];
            } catch (DbRecordNotFoundException) {
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
    private function encryptDocument(Document $data, ResourceBuilderInterface $resourceBuilder): void
    {
        foreach ($data->resources ?? [] as $resourceObject){
            if ($this->jsonApi->getDefaultEncrypter() !== null) {
                $resourceObject->id = $this->jsonApi->getDefaultEncrypter()->encryptId(
                    $resourceObject->id
                );

                /** @var AttributeBuilderInterface $attribute */
                foreach ($resourceBuilder->getAttributes() ?? [] as $attribute){
                    if ($attribute->getName() !== 'id' && $attribute->isEncrypted() && $resourceObject->attributes->has($attribute->getName())){
                        $resourceObject->attributes->update(
                            $attribute->getName(),
                            $this->jsonApi->getDefaultEncrypter()->encryptId(
                                $resourceObject->attributes->get($attribute->getName())
                            )
                        );
                    }
                }
            }
        }
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

            if (!$isNewResource && $this->jsonApi->getDefaultEncrypter() !== null){
                /** @var ResourceObject $dataResource */

                try {
                    $dataResource = current(
                        $this->jsonApi->generateResourceObjectByFieldValue(
                            get_class($resourceBuilder),
                            null,
                            $resourceBuilder->getAttribute('id'),
                            $this->jsonApi->getDefaultEncrypter()->decryptId($resourceObject->id)
                        )
                    );

                    /** @var Attributes $attribute */
                    foreach ($dataResource->attributes->prepare() as $attributeName=>$attributeValue){
                        if (!$resourceObject->attributes->has($attributeName)){
                            $resourceObject->attributes->add($attributeName, $attributeValue);
                        }
                    }
                } catch (Throwable) {
                }
            }

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
            throw new RuntimeException('Missing required field: id', 500);
        }

        if ($field !== null && $resourceObject->id !== null && $this->jsonApi->getDefaultEncrypter() !== null && $field->isEncrypted()) {
            $resourceObject->id = $this->jsonApi->getDefaultEncrypter()->decryptId($resourceObject->id);
        }

        /** @var AttributeBuilderInterface $attribute */
        foreach ($resourceBuilder->getAttributes() ?? [] as $attribute){
            if ($attribute->getName() !== 'id') {
                try {
                    $attributeValue = $resourceObject->attributes->get($attribute->getName());

                    if ($attribute->isEncrypted()){
                        $attributeValue = $this->jsonApi->getDefaultEncrypter()->decryptId($attributeValue);
                    }

                    $resourceObject->attributes->update($attribute->getName(), $attributeValue);
                } catch (Exception) {
                    if ($attribute->isRequired()) {
                        throw new RuntimeException('Required field missing: ' . $attribute->getName(), 500);
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
                throw new RuntimeException('Required relationship missing: ' . $relationshipName, 500);
            }

            foreach ($resourceObject->relationship($relationshipName)->resourceLinkage->resources ?? [] as $resourceLink){
                if ( $resourceLink->type !== $relationship->getResourceObjectName()){
                    throw new RuntimeException('Relationship resource mismatch:' . $resourceLink->type, $relationship->getType(), 500);
                }

                if ($resourceLink->id !== null && $this->jsonApi->getDefaultEncrypter() !== null && $relationship->getAttribute() !== null && $relationship->getAttribute()->isEncrypted()) {
                    $resourceLink->id = $this->jsonApi->getDefaultEncrypter()->decryptId($resourceLink->id);
                }
            }
        }
    }
}