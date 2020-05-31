<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Facades;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;
use Exception;

class DocumentFacade
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
    }

    /**
     * @param EntityDocument $entity
     * @param Document $data
     * @throws Exception
     */
    public function writeDocument(EntityDocument $entity, Document $data) : void
    {
        foreach ($data->resources as $resource){
            $resourceObjectFacade = new ResourceObjectFacade($this->services);
            $resourceObjectFacade->setDocument($entity);

            $resourceObjectFacade->writeResourceObject($entity->getResource(), $resource);
        }
    }
}