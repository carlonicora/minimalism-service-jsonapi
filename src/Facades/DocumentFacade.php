<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Facades;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Events\JsonDataMapperErrorEvents;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityField;
use Exception;

class DocumentFacade
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var EncrypterInterface  */
    private ?EncrypterInterface $encrypter;

    /**
     * DocumentFacade constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
        /** @var JsonDataMapper $mapper */
        $mapper = $services->service(JsonDataMapper::class);

        $this->encrypter = $mapper->getDefaultEncrypter();
    }

    /**
     * @param EntityDocument $entity
     * @param Document $data
     * @throws Exception
     */
    public function writeDocument(EntityDocument $entity, Document $data) : void
    {
        $this->validateAndDecryptDocument($entity, $data);

        foreach ($data->resources as $resource){
            $resourceObjectFacade = new ResourceObjectFacade($this->services);
            $resourceObjectFacade->setDocument($entity);

            $resourceObjectFacade->writeResourceObject($entity->getResource(), $resource);
        }
    }

    /**
     * @param EntityDocument $entity
     * @param Document $data
     * @throws Exception
     */
    private function validateAndDecryptDocument(EntityDocument $entity, Document $data) : void
    {
        foreach ($data->resources ?? [] as $resourceObject){
            $isNewResource = $resourceObject->id === null;
            $field = $entity->getField('id');

            if ($field !== null && $this->encrypter !== null && $resourceObject->id !== null && $field->isEncrypted()) {
                $resourceObject->id = $this->encrypter->decryptId($resourceObject->id);
            }

            /**
             * @var string $attributeName
             * @var EntityField $attribute
             */
            foreach ($resourceObject->attributes as $attributeName=>$attribute){
                if ($isNewResource && $attribute->isRequired()) {
                    try {
                        $resourceObject->attributes->get($attributeName);
                    } catch (Exception $e) {
                        $this->services->logger()->error()->log(
                            JsonDataMapperErrorEvents::REQUIRED_FIELD_MISSING($attributeName)
                        )->throw();
                    }
                }
            }

            foreach ($entity->getRelationships() ?? [] as $relationshipName=>$relationship){
                if ($isNewResource && $relationship->isRequired() && count($resourceObject->relationship($relationshipName)->resourceLinkage->resources) === 0){
                    $this->services->logger()->error()->log(
                        JsonDataMapperErrorEvents::REQUIRED_RELATIONSHIP_MISSING($relationshipName)
                    )->throw();
                }

                foreach ($resourceObject->relationship($relationshipName)->resourceLinkage->resources ?? [] as $resourceLink){
                    if ($resourceLink->type !== $relationship->getType()){
                        $this->services->logger()->error()->log(
                            JsonDataMapperErrorEvents::RELATIONSHIP_RESOURCE_MISMATCH($resourceLink->type, $relationship->getType())
                        )->throw();
                    }

                    if ($resourceLink->id === null && $relationship->getResource()->getId()->isRequired()){
                        $this->services->logger()->error()->log(
                            JsonDataMapperErrorEvents::REQUIRED_FIELD_MISSING($relationshipName . '.id')
                        )->throw();
                    }

                    if ($this->encrypter !== null && $resourceLink->id !== null && $relationship->getResource()->getId()->isEncrypted()) {
                        $resourceLink->id = $this->encrypter->decryptId($resourceLink->id);
                    }
                }
            }
        }
    }
}