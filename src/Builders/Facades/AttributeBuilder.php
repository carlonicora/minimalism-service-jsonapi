<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ElementBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;

class AttributeBuilder extends ElementBuilder implements AttributeBuilderInterface
{
    /** @var bool  */
    private bool $isRequired=false;

    /** @var bool  */
    private bool $isReadOnly=false;

    /** @var bool  */
    private bool $isWriteOnly=false;

    /** @var string|null  */
    private ?string $databaseFieldRelationship=null;

    /** @var ResourceBuilderInterface|null  */
    private ?ResourceBuilderInterface $relationship=null;

    /**
     * AttributeBuilder constructor.
     * @param ResourceBuilderInterface $parent
     * @param string $name
     */
    public function __construct(
        ResourceBuilderInterface $parent,
        string $name
    )
    {
        $this->name = $name;
        $this->databaseFieldName = $name;
        $this->parent = $parent;
    }

    /**
     * @return string|null
     */
    public function getDatabaseFieldRelationship(): ?string
    {
        return $this->databaseFieldRelationship;
    }

    /**
     * @param string|null $databaseFieldRelationship
     * @return ElementBuilderInterface
     */
    public function setDatabaseFieldRelationship(
        ?string $databaseFieldRelationship
    ): ElementBuilderInterface
    {
        $this->databaseFieldRelationship = $databaseFieldRelationship;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->isReadOnly;
    }

    /**
     * @param bool $isReadOnly
     * @return ElementBuilderInterface
     */
    public function setIsReadOnly(
        bool $isReadOnly
    ): ElementBuilderInterface
    {
        $this->isReadOnly = $isReadOnly;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * @param bool $isRequired
     * @return ElementBuilderInterface
     */
    public function setIsRequired(
        bool $isRequired
    ): ElementBuilderInterface
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    /**
     * @return bool
     */
    public function isWriteOnly(): bool
    {
        return $this->isWriteOnly;
    }

    /**
     * @param bool $isWriteOnly
     * @return ElementBuilderInterface
     */
    public function setIsWriteOnly(
        bool $isWriteOnly
    ): ElementBuilderInterface
    {
        $this->isWriteOnly = $isWriteOnly;

        return $this;
    }

    /**
     * @param ResourceBuilderInterface $resource
     */
    public function setRelationshipResource(
        ResourceBuilderInterface $resource
    ): void
    {
        $this->relationship = $resource;
    }

    /**
     * @return ResourceBuilderInterface|null
     */
    public function getRelationshipResource() : ?ResourceBuilderInterface
    {
        return $this->relationship;
    }
}