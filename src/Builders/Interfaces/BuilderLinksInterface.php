<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\LinkBuilder;

interface BuilderLinksInterface
{
    /**
     * @return array
     */
    public function getLinks() : array;

    /**
     * @param LinkBuilder $link
     * @return void
     */
    public function addLink(
        LinkBuilder $link
    ): void;
}