<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityResource;

interface LinkBuilderInterface
{
    /**
     * LinkBuilderInterface constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services);

    /**
     * @param string $url
     * @param EntityResource $resource
     * @param array $data
     * @return string
     */
    public function buildLink(string $url, EntityResource $resource, array $data) : string;
}