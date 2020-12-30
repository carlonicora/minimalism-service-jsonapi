<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Factories;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\JsonApi;
use Exception;

class ResourceBuilderFactory
{
    /**
     * ResourceBuilderFactory constructor.
     * @param JsonApi $jsonApi
     */
    public function __construct(private JsonApi $jsonApi) {}

    /**
     * @param string $builderName
     * @return ResourceBuilderInterface
     * @throws Exception
     */
    public function createResourceBuilder(string $builderName) : ResourceBuilderInterface
    {
        if (($response = $this->jsonApi->getCache()->getResourceBuilder($builderName)) === null) {
            /** @var ResourceBuilderInterface $response */
            $response = new $builderName($this->jsonApi);

            foreach ($response->getAttributes() ?? [] as $attribute) {
                $this->jsonApi->getCache()->setAttributeBuilder($attribute);
            }

            foreach ($response->getMeta() ?? [] as $meta){
                $this->jsonApi->getCache()->setMetaBuilder($meta);
            }

            $this->jsonApi->getCache()->setResourceBuilder($response);
        }

        return $response;
    }
}