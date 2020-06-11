<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;

interface LinkCreatorInterface
{
    /**
     * LinkBuilderInterface constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services);

    /**
     * @param string $url
     * @param ResourceBuilderInterface $resource
     * @param array $data
     * @return string
     */
    public function buildLink(string $url, ResourceBuilderInterface $resource, array $data) : string;
}