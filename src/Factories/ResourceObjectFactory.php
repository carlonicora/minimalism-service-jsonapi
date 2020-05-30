<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Factories;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityRelationship;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityResource;
use Exception;

class ResourceObjectFactory
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var EntityDocument|null  */
    private ?EntityDocument $document=null;

    /** @var JsonDataMapper  */
    private JsonDataMapper $mapper;

    /**
     * ResourceObjectFactory constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;

        $this->mapper = $services->service(JsonDataMapper::class);
    }

    /**
     * @param EntityDocument $document
     */
    public function setDocument(EntityDocument $document): void
    {
        $this->document = $document;
    }

    /**
     * @param EntityResource $resource
     * @param array $data
     * @return ResourceObject
     * @throws Exception
     */
    public function build(EntityResource $resource, array $data) : ResourceObject
    {
        $response = new ResourceObject($resource->getType(), $data[$resource->getId()->getDatabaseField()]);

        foreach ($resource->getAttributes() ?? [] as $entityField) {
            if ($entityField->isEncrypted()){
                if (($encrypter = $this->mapper->getDefaultEncrypter()) !== null){
                    $fieldValue = $encrypter->encryptId(
                        $data[$entityField->getDatabaseField()]
                    );
                } else {
                    $fieldValue = $data[$entityField->getDatabaseField()];
                }
            } else {
                $fieldValue = $entityField->getTransformedValue($data[$entityField->getDatabaseField()]);
            }

            $response->attributes->add(
                $entityField->getName(),
                $fieldValue
            );
        }

        if ($this->document !== null) {
            foreach ($this->document->getRelationships() ?? [] as $relationship) {
                if ($relationship->getType() === EntityRelationship::RELATIONSHIP_TYPE_ONE_TO_ONE) {

                    $dataWrapperFactory = $this->mapper->generateDataWrapperFactory($relationship->getResource()->getType());
                    $dataWrapper = $dataWrapperFactory->generateSimpleLoader('id', $data[$relationship->getResource()->getId()->getDatabaseField()]);
                    $entityResource = $dataWrapperFactory->getEntityDocument()->getResource();
                    $resourceObjectFactory = new ResourceObjectFactory($this->services);

                    $resourceData = $dataWrapper->loadData();

                    $response->relationship($relationship->getRelationshipName())
                        ->resourceLinkage
                        ->add(
                            $resourceObjectFactory->build($entityResource, $resourceData)
                        );
                }
            }
        }

        return $response;
    }
}