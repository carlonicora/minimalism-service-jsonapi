<?php

namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ElementBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;

abstract class ElementBuilder implements ElementBuilderInterface
{
    /** @var string  */
    protected string $name;

    /** @var string|null  */
    protected ?string $validator=null;

    /** @var bool  */
    protected bool $isEncrypted=false;

    /** @var string|null  */
    protected ?string $transformationClass=null;

    /** @var string|null  */
    protected ?string $transformationMethod=null;

    /** @var string|null  */
    protected ?string $databaseFieldName;

    /** @var ResourceBuilderInterface  */
    protected ResourceBuilderInterface $parent;

    /** @var  */
    protected $staticValue;

    /**
     * @return ResourceBuilderInterface
     */
    public function getResourceBuilder() : ResourceBuilderInterface
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ElementBuilderInterface
     */
    public function setName(string $name): ElementBuilderInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDatabaseFieldName(): ?string
    {
        return $this->databaseFieldName;
    }

    /**
     * @param string|null $databaseFieldName
     * @return ElementBuilderInterface
     */
    public function setDatabaseFieldName(?string $databaseFieldName): ElementBuilderInterface
    {
        $this->databaseFieldName = $databaseFieldName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDatabaseFieldRelationship(): ?string
    {
        return null;
    }

    /**
     * @param string|null $databaseFieldRelationship
     * @return ElementBuilderInterface
     */
    public function setDatabaseFieldRelationship(?string $databaseFieldRelationship): ElementBuilderInterface
    {
        return $this;
    }

    /**
     * @param $value
     * @return ElementBuilderInterface
     */
    public function setStaticValue($value): ElementBuilderInterface
    {
        $this->staticValue = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStaticValue(): mixed
    {
        return $this->staticValue;
    }

    /**
     * @return bool
     */
    public function isEncrypted(): bool
    {
        return $this->isEncrypted;
    }

    /**
     * @param bool $isEncrypted
     * @return ElementBuilderInterface
     */
    public function setIsEncrypted(bool $isEncrypted): ElementBuilderInterface
    {
        $this->isEncrypted = $isEncrypted;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return false;
    }

    /**
     * @param bool $isReadOnly
     * @return ElementBuilderInterface
     */
    public function setIsReadOnly(bool $isReadOnly): ElementBuilderInterface
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return false;
    }

    /**
     * @param bool $isRequired
     * @return ElementBuilderInterface
     */
    public function setIsRequired(bool $isRequired): ElementBuilderInterface
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function isWriteOnly(): bool
    {
        return false;
    }

    /**
     * @param bool $isWriteOnly
     * @return ElementBuilderInterface
     */
    public function setIsWriteOnly(bool $isWriteOnly): ElementBuilderInterface
    {
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTransformationClass(): ?string
    {
        return $this->transformationClass;
    }

    /**
     * @param string|null $transformationClass
     * @return ElementBuilderInterface
     */
    public function setTransformationClass(?string $transformationClass): ElementBuilderInterface
    {
        $this->transformationClass = $transformationClass;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTransformationMethod(): ?string
    {
        return $this->transformationMethod;
    }

    /**
     * @param string|null $transformationMethod
     * @return ElementBuilderInterface
     */
    public function setTransformationMethod(?string $transformationMethod): ElementBuilderInterface
    {
        $this->transformationMethod = $transformationMethod;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getValidator(): ?string
    {
        return $this->validator;
    }

    /**
     * @param string|null $validator
     * @return ElementBuilderInterface
     */
    public function setValidator(?string $validator): ElementBuilderInterface
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * @return ResourceBuilderInterface
     */
    public function getResource(): ResourceBuilderInterface
    {
        return $this->parent;
    }

    /**
     * @param ResourceBuilderInterface $resource
     */
    public function setRelationshipResource(ResourceBuilderInterface $resource): void
    {
    }

    /**
     * @return ResourceBuilderInterface|null
     */
    public function getRelationshipResource() : ?ResourceBuilderInterface
    {
        return null;
    }

    /**
     * @return int
     */
    public function getPositioning(): int
    {
        return 0;
    }

    /**
     * @param int $positioning
     * @return ElementBuilderInterface
     */
    public function setPositioning(int $positioning): ElementBuilderInterface
    {
        return $this;
    }
}