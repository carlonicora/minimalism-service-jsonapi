<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Interfaces;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;

interface LinkCreatorInterface
{
    /**
     * LinkBuilderInterface constructor.
     * @param ServicesProxy $servicesProxy
     */
    public function __construct(
        ServicesProxy $servicesProxy,
    );

    /**
     * @param string $url
     * @param ResourceBuilderInterface $resource
     * @param array $data
     * @param ResourceObject|null $resourceObject
     * @return string
     */
    public function buildLink(string $url, ResourceBuilderInterface $resource, array $data, ResourceObject $resourceObject=null) : string;
}