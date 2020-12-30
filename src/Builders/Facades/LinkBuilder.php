<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\LinkBuilderInterface;

class LinkBuilder implements LinkBuilderInterface
{
    /** @var string  */
    private string $name;

    /** @var string  */
    private string $link;

    /** @var array|null  */
    private ?array $meta;

    /**
     * LinkBuilderInterface constructor.
     * @param string $name
     * @param string $link
     * @param array|null $meta
     */
    public function __construct(string $name, string $link, array $meta=null)
    {
        $this->name = $name;
        $this->link = $link;
        $this->meta = $meta;
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