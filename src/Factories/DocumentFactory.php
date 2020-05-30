<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Factories;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;

class DocumentFactory
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    public function __construct(ServicesFactory $services)
    {
    }

    /**
     * @param EntityDocument $document
     * @param array $data
     * @return Document
     */
    public function build(EntityDocument $document, array $data) : Document
    {
        $response = new Document();

        $resourceObjectFactory = new ResourceObjectFactory($this->services);

        if (array_key_exists($document->getResource()->getId()->getName(), $data)){
            $response->addResource(
                $resourceObjectFactory->build($document->getResource(), $data)
            );
        } else {
            foreach ($data ?? [] as $resourceData){
                $response->addResource(
                    $resourceObjectFactory->build($document->getResource(), $resourceData)
                );
            }
        }
    }
}