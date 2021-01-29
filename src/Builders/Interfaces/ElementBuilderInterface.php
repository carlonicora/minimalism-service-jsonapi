<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces;

interface ElementBuilderInterface
{
    /**
     * @return ResourceBuilderInterface
     */
    public function getResourceBuilder() : ResourceBuilderInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return ElementBuilderInterface
     */
    public function setName(
        string $name
    ): ElementBuilderInterface;

    /**
     * @return string|null
     */
    public function getDatabaseFieldName(): ?string;

    /**
     * @param string|null $databaseFieldName
     * @return $this|ElementBuilderInterface
     */
    public function setDatabaseFieldName(
        ?string $databaseFieldName
    ): ElementBuilderInterface;

    /**
     * @return string|null
     */
    public function getDatabaseFieldRelationship(): ?string;

    /**
     * @param string|null $databaseFieldRelationship
     * @return $this|ElementBuilderInterface
     */
    public function setDatabaseFieldRelationship(
        ?string $databaseFieldRelationship
    ): ElementBuilderInterface;

    /**
     * @return bool
     */
    public function isEncrypted(): bool;

    /**
     * @param bool $isEncrypted
     * @return $this|ElementBuilderInterface
     */
    public function setIsEncrypted(
        bool $isEncrypted
    ): ElementBuilderInterface;

    /**
     * @return bool
     */
    public function isReadOnly(): bool;

    /**
     * @param bool $isReadOnly
     * @return $this|ElementBuilderInterface
     */
    public function setIsReadOnly(
        bool $isReadOnly
    ): ElementBuilderInterface;

    /**
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * @param bool $isRequired
     * @return $this|ElementBuilderInterface
     */
    public function setIsRequired(
        bool $isRequired
    ): ElementBuilderInterface;

    /**
     * @return bool
     */
    public function isWriteOnly(): bool;

    /**
     * @param bool $isWriteOnly
     * @return $this|ElementBuilderInterface
     */
    public function setIsWriteOnly(
        bool $isWriteOnly
    ): ElementBuilderInterface;

    /**
     * @return string|null
     */
    public function getTransformationClass(): ?string;

    /**
     * @param string|null $transformationClass
     * @return $this|ElementBuilderInterface
     */
    public function setTransformationClass(
        ?string $transformationClass
    ): ElementBuilderInterface;

    /**
     * @return string|null
     */
    public function getTransformationMethod(): ?string;

    /**
     * @param string|null $transformationMethod
     * @return $this|ElementBuilderInterface
     */
    public function setTransformationMethod(
        ?string $transformationMethod
    ): ElementBuilderInterface;

    /**
     * @return string|null
     */
    public function getValidator(): ?string;

    /**
     * @param string|null $validator
     * @return $this|ElementBuilderInterface
     */
    public function setValidator(
        ?string $validator
    ): ElementBuilderInterface;

    /**
     * @return ResourceBuilderInterface
     */
    public function getResource() : ResourceBuilderInterface;

    /**
     * @param ResourceBuilderInterface $resource
     */
    public function setRelationshipResource(
        ResourceBuilderInterface $resource
    ): void;

    /**
     * @return ResourceBuilderInterface|null
     */
    public function getRelationshipResource(): ?ResourceBuilderInterface;

    /**
     * @param $value
     * @return $this|ElementBuilderInterface
     */
    public function setStaticValue(
        $value
    ): ElementBuilderInterface;

    /**
     * @return mixed
     */
    public function getStaticValue(): mixed;

    /**
     * @return int
     */
    public function getPositioning(): int;

    /**
     * @param int $positioning
     * @return ElementBuilderInterface
     */
    public function setPositioning(
        int $positioning
    ): ElementBuilderInterface;
}