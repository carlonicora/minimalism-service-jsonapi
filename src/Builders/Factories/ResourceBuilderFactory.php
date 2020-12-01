<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
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
        if (($response = $mapper->getCache()->getResourceBuilder($builderName)) === null) {
            /** @var ResourceBuilderInterface $response */
            $response = new $builderName($this->services);

            foreach ($response->getAttributes() as $attribute) {
                $mapper->getCache()->setAttributeBuilder($attribute);
            }

            $response->initialiseRelationships();
            /** @var RelationshipBuilderInterface $relationship */
            foreach ($response->getRelationships() as $relationship) {
                if ($relationship->getAttribute() !== null) {
                    $relationship->getAttribute()->setRelationshipResource($response);
                }
            }

            $mapper->getCache()->setResourceBuilder($response);
        }

        return $response;
    }
}