<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Events\JsonDataMapperInfoEvents;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use Exception;

class ResourceBuilderFactory
{
    /** @var ServicesFactory */
    private ServicesFactory $services;

    /**
     * ResourceBuilderFactory constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
    }

    /**
     * @param string $builderName
     * @return ResourceBuilderInterface
     * @throws Exception
     */
    public function createResourceBuilder(string $builderName) : ResourceBuilderInterface
    {
        /** @var JsonDataMapper $mapper */
        $mapper = $this->services->service(JsonDataMapper::class);
        $this->services->logger()->info()->log(JsonDataMapperInfoEvents::GENERIC('ResourceBuilderFactory createResourceBuilder initialised'));

        if (($response = $mapper->getCache()->getResourceBuilder($builderName)) === null) {
            $this->services->logger()->info()->log(JsonDataMapperInfoEvents::GENERIC('ResourceBuilderFactory createResourceBuilder ResourceBuilder not found in cache'));

            /** @var ResourceBuilderInterface $response */
            $response = new $builderName($this->services);

            foreach ($response->getAttributes() as $attribute) {
                $mapper->getCache()->setAttributeBuilder($attribute);
            }

            $response->initialiseRelationships();
            foreach ($response->getRelationships() as $relationship) {
                $relationship->getAttribute()->setRelationshipResource($response);
            }

            $mapper->getCache()->setResourceBuilder($response);
        }

        $this->services->logger()->info()->log(JsonDataMapperInfoEvents::GENERIC('ResourceBuilderFactory createResourceBuilder Resource Builder initialised'));

        return $response;
    }
}