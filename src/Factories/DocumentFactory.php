<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;
use Exception;

class DocumentFactory
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
    }

    /**
     * @param EntityDocument $document
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function build(EntityDocument $document, array $data) : array
    {
        $response = [];

        $resourceObjectFactory = new ResourceObjectFactory($this->services);
        $resourceObjectFactory->setDocument($document);

        if (array_key_exists($document->getResource()->getId()->getDatabaseField(), $data)){
            $response[] = $resourceObjectFactory->build($document->getResource(), $data);
        } else {
            foreach ($data ?? [] as $resourceData){
                $response[] = $resourceObjectFactory->build($document->getResource(), $resourceData);
            }
        }

        return $response;
    }
}