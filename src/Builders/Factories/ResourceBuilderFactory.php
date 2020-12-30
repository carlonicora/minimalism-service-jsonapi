<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Factories;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use Exception;

class ResourceBuilderFactory
{
    /**
     * ResourceBuilderFactory constructor.
     * @param ServicesProxy $servicesProxy
     */
    public function __construct(
        private ServicesProxy $servicesProxy
    ) {}

    /**
     * @param string $builderName
     * @return ResourceBuilderInterface
     * @throws Exception
     */
    public function createResourceBuilder(string $builderName) : ResourceBuilderInterface
    {
        if (($response = $this->servicesProxy->getCacheFacade()->getResourceBuilder($builderName)) === null) {
            /** @var ResourceBuilderInterface $response */
            $response = new $builderName(
                servicesProxy: $this->servicesProxy,
            );

            foreach ($response->getAttributes() ?? [] as $attribute) {
                $this->servicesProxy->getCacheFacade()->setAttributeBuilder($attribute);
            }

            foreach ($response->getMeta() ?? [] as $meta){
                $this->servicesProxy->getCacheFacade()->setMetaBuilder($meta);
            }

            $this->servicesProxy->getCacheFacade()->setResourceBuilder($response);
        }

        return $response;
    }
}