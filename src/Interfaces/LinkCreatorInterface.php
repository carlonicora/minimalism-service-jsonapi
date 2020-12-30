<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Interfaces;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\JsonApi;
use CarloNicora\Minimalism\Services\MySQL\MySQL;

interface LinkCreatorInterface
{
    /**
     * LinkBuilderInterface constructor.
     * @param JsonApi $jsonApi
     * @param MySQL $mysql
     * @param Cacher $cacher
     */
    public function __construct(
        JsonApi $jsonApi,
        MySQL $mysql,
        Cacher $cacher,
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