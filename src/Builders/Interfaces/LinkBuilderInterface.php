<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces;

interface LinkBuilderInterface
{
    /**
     * LinkBuilderInterface constructor.
     * @param string $name
     * @param string $link
     */
    public function __construct(string $name, string $link);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getLink(): string;
}