<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\LinkBuilderInterface;

class LinkBuilder implements LinkBuilderInterface
{
    /** @var string  */
    private string $name;

    /** @var string  */
    private string $link;

    /**
     * LinkBuilderInterface constructor.
     * @param string $name
     * @param string $link
     */
    public function __construct(string $name, string $link)
    {
        $this->name = $name;
        $this->link = $link;
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
}