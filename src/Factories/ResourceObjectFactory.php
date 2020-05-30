<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Factories;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\Meta;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\LinkBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityField;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityRelationship;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityResource;
use Exception;

class ResourceObjectFactory implements LinkBuilderInterface
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
     * @param EntityField $field
     * @param array $data
     * @return mixed|string
     */
    private function getFieldValue(EntityField $field, array $data)
    {
        $response = $data[$field->getDatabaseField()];

        if ($field->isEncrypted()){
            if (($encrypter = $this->mapper->getDefaultEncrypter()) !== null){
                $response = $encrypter->encryptId(
                    $data[$field->getDatabaseField()]
                );
            }
        } else {
            $response = $field->getTransformedValue($data[$field->getDatabaseField()]);
        }

        return $response;
    }

    /**
     * @param EntityResource $resource
     * @param array $data
     * @return ResourceObject
     * @throws Exception
     */
    public function build(EntityResource $resource, array $data) : ResourceObject
    {
        $response = new ResourceObject(
            $resource->getType(),
            $this->getFieldValue($resource->getId(), $data)
        );

        foreach ($resource->getAttributes() ?? [] as $entityField) {
            $response->attributes->add(
                $entityField->getName(),
                $this->getFieldValue($entityField, $data)
            );
        }

        foreach ($resource->getLinks() ?? [] as $entityLink) {
            $meta = null;

            if ($entityLink->getMeta() !== null){
                $meta = new Meta();
                $meta->importArray($entityLink->getMeta());
            }

            $url = $this->buildLink($entityLink->getUrl(), $resource, $data);

            if (($linkBuilder = $this->mapper->getLinkBuilder()) !== null){
                $url = $linkBuilder->buildLink($url, $resource, $data);
            }

            $response->links->add(
                new Link(
                    $entityLink->getName(),
                    $url,
                    $meta
                )
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

    /**
     * @param string $url
     * @param EntityResource $resource
     * @param array $data
     * @return string
     */
    public function buildLink(string $url, EntityResource $resource, array $data) : string
    {
        $linkElements = explode('%', $url);

        for ($linkElementsCounter = 1, $linkElementsCounterMax = count($linkElements); $linkElementsCounter < $linkElementsCounterMax; $linkElementsCounter += 2) {
            if (($field = $resource->getField($linkElements[$linkElementsCounter])) !== null) {
                $linkElements[$linkElementsCounter] = $this->getFieldValue($field, $data);
            } else {
                $linkElements[$linkElementsCounter] = '%'.$linkElements[$linkElementsCounter].'%';
            }
        }

        return implode('', $linkElements);
    }
}