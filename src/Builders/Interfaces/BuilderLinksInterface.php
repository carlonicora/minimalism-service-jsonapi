<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces;

use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\LinkBuilder;

interface BuilderLinksInterface
{
    /**
     * @return array|LinkBuilder[]
     */
    public function getLinks() : array;

    /**
     * @param LinkBuilder $link
     */
    public function addLink(LinkBuilder $link): void;
}