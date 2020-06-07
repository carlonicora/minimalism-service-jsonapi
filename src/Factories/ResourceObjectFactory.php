<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Factories;

use CarloNicora\JsonApi\Objects\Link;
use CarloNicora\JsonApi\Objects\Meta;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\LinkCreatorInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityField;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityRelationship;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityResource;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;

class ResourceObjectFactory implements LinkCreatorInterface
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /** @var EntityDocument|null  */
    private ?EntityDocument $document=null;

    /** @var JsonDataMapper  */
    private JsonDataMapper $mapper;

    /** @var MySQL  */
    private MySQL $mysql;

    /**
     * ResourceObjectFactory constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;

        $this->mapper = $services->service(JsonDataMapper::class);
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
            $response = $field->getTransformedValue($this->services, $data[$field->getDatabaseField()]);
        }

        return $response;
    }

    /**
     * @param EntityResource $resource
     * @param array $data
     * @return ResourceObject
     * @throws Exception
     */
    public function buildResourceObject(EntityResource $resource, array $data) : ResourceObject
    {
        $response = new ResourceObject(
            $resource->getType(),
            $this->getFieldValue($resource->getId(), $data)
        );

        foreach ($resource->getAttributes() ?? [] as $entityField) {
            if (!$entityField->isWriteOnly()) {
                $response->attributes->add(
                    $entityField->getName(),
                    $this->getFieldValue($entityField, $data)
                );
            }
        }

        foreach ($this->addLinks($resource->getLinks(), $resource, $data) as $newLink){
            $response->links->add($newLink);
        }

        if ($this->document !== null) {
            foreach ($this->document->getResource()->getRelationships() ?? [] as $relationship) {
                foreach ($this->addLinks($relationship->getLinks(), $resource, $data) as $newLink){
                    $response->relationship($relationship->getRelationshipName())
                        ->links->add($newLink);
                }

                if ($relationship->getResource() !== null) {
                    $dataWrapperFactory = new DataWrapperFactory($this->services, $relationship->getResource()->getType());
                    $entityResource = $dataWrapperFactory->getEntityDocument()->getResource();
                    $resourceObjectFactory = new ResourceObjectFactory($this->services);

                    if ($relationship->getType() === EntityRelationship::RELATIONSHIP_TYPE_ONE_TO_ONE) {
                        $dataWrapper = $dataWrapperFactory->generateSimpleLoader('id', $data[$relationship->getResource()->getId()->getDatabaseRelationshipField()]);
                        try {
                            $resourceData = $dataWrapper->loadData();
                            $response->relationship($relationship->getRelationshipName())
                                ->resourceLinkage
                                ->add(
                                    $resourceObjectFactory->buildResourceObject($entityResource, $resourceData)
                                );
                        } catch (DbRecordNotFoundException $e) {
                            if ($relationship->isRequired()) {
                                throw $e;
                            }
                        }
                    } elseif ($relationship->getType() === EntityRelationship::RELATIONSHIP_TYPE_ONE_TO_MANY) {
                        $table = $this->mysql->create($relationship->getTableName());

                        $dataWrapper = $dataWrapperFactory->generateCustomLoader(
                            $relationship->getResource()->getTable(),
                            'getFirstLevelJoin',
                            [
                                $table->getTableName(),
                                $resource->getId()->getDatabaseField(),
                                $relationship->getResource()->getId()->getDatabaseField(),
                                $data[$resource->getId()->getDatabaseField()]
                            ]
                        );

                        $resourceData = $dataWrapper->loadData();

                        foreach ($resourceData ?? [] as $singleResourceData) {
                            $response->relationship($relationship->getRelationshipName())
                                ->resourceLinkage
                                ->add(
                                    $resourceObjectFactory->buildResourceObject($entityResource, $singleResourceData)
                                );
                        }
                    }
                }
            }
        }

        return $response;
    }

    /**
     * @param array|null $links
     * @param EntityResource $resource
     * @param array $data
     * @return array|null
     * @throws Exception
     */
    private function addLinks(?array $links, EntityResource $resource, array $data) : ?array
    {
        $response = [];

        foreach ($links ?? [] as $entityLink) {
            $meta = null;

            if ($entityLink->getMeta() !== null){
                $meta = new Meta();
                $meta->importArray($entityLink->getMeta());
            }

            $url = $this->buildLink($entityLink->getUrl(), $resource, $data);

            if (($linkBuilder = $this->mapper->getLinkBuilder()) !== null){
                $url = $linkBuilder->buildLink($url, $resource, $data);
            }

            $response[] = new Link(
                    $entityLink->getName(),
                    $url,
                    $meta
                );
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