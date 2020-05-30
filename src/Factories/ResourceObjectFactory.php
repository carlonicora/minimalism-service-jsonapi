<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Factories;

use CarloNicora\JsonApi\Document;
use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityResource;

class ResourceObjectFactory
{
    /** @var ServicesFactory  */
    private ServicesFactory $services;

    /**
     * ResourceObjectFactory constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
    }

    /**
     * @param EntityResource $resource
     * @param array $data
     * @return ResourceObject
     */
    public function build(EntityResource $resource, array $data) : ResourceObject
    {

    }
}