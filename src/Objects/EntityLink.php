<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Objects;

class EntityLink
{
    /** @var string  */
    private string $name;

    /** @var string  */
    private string $url;

    /** @var array|null  */
    private ?array $meta;

    /**
     * EntityLink constructor.
     * @param string $name
     * @param string $url
     * @param array|null $meta
     */
    public function __construct(string $name, string $url, array $meta=null)
    {
        $this->name = $name;
        $this->url = $url;
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
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return array|null
     */
    public function getMeta(): ?array
    {
        return $this->meta;
    }
}