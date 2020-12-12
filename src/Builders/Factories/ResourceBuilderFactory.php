<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use Exception;

class ResourceBuilderFactory
{
    /** @var ServicesFactory */
    private ServicesFactory $services;

    /** @var JsonDataMapper  */
    private JsonDataMapper $mapper;

    /**
     * ResourceBuilderFactory constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
        $this->mapper = $services->service(JsonDataMapper::class);
    }

    /**
     * @param string $builderName
     * @return ResourceBuilderInterface
     * @throws Exception
     */
    public function createResourceBuilder(string $builderName) : ResourceBuilderInterface
    {
        if (($response = $this->mapper->getCache()->getResourceBuilder($builderName)) === null) {
            /** @var ResourceBuilderInterface $response */
            $response = new $builderName($this->services);

            foreach ($response->getAttributes() ?? [] as $attribute) {
                $this->mapper->getCache()->setAttributeBuilder($attribute);
            }

            foreach ($response->getMeta() ?? [] as $meta){
                $this->mapper->getCache()->setMetaBuilder($meta);
            }

            $this->mapper->getCache()->setResourceBuilder($response);
        }

        return $response;
    }
}