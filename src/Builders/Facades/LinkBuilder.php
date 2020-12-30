<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\LinkBuilderInterface;

class LinkBuilder implements LinkBuilderInterface
{
    /**
     * LinkBuilderInterface constructor.
     * @param string $name
     * @param string $link
     * @param array|null $meta
     */
    public function __construct(
        private string $name,
        private string $link,
        private ?array $meta=null,
    )
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @return array|null
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }
}