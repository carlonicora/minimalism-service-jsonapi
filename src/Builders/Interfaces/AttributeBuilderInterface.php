<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces;


use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterValidatorInterface;

interface AttributeBuilderInterface
{
    /**
     * AttributeBuilderInterface constructor.
     * @param ServicesFactory $services
     * @param ResourceBuilderInterface $parent
     * @param string $name
     */
    public function __construct(ServicesFactory $services, ResourceBuilderInterface $parent, string $name);

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
     * @return AttributeBuilderInterface
     */
    public function setName(string $name): AttributeBuilderInterface;

    /**
     * @return string|null
     */
    public function getDatabaseFieldName(): ?string;

    /**
     * @param string|null $databaseFieldName
     * @return $this|AttributeBuilderInterface
     */
    public function setDatabaseFieldName(?string $databaseFieldName): AttributeBuilderInterface;

    /**
     * @return string|null
     */
    public function getDatabaseFieldRelationship(): ?string;

    /**
     * @param string|null $databaseFieldRelationship
     * @return $this|AttributeBuilderInterface
     */
    public function setDatabaseFieldRelationship(?string $databaseFieldRelationship): AttributeBuilderInterface;

    /**
     * @return bool
     */
    public function isEncrypted(): bool;

    /**
     * @param bool $isEncrypted
     * @return $this|AttributeBuilderInterface
     */
    public function setIsEncrypted(bool $isEncrypted): AttributeBuilderInterface;

    /**
     * @return bool
     */
    public function isReadOnly(): bool;

    /**
     * @param bool $isReadOnly
     * @return $this|AttributeBuilderInterface
     */
    public function setIsReadOnly(bool $isReadOnly): AttributeBuilderInterface;

    /**
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * @param bool $isRequired
     * @return $this|AttributeBuilderInterface
     */
    public function setIsRequired(bool $isRequired): AttributeBuilderInterface;

    /**
     * @return bool
     */
    public function isWriteOnly(): bool;

    /**
     * @param bool $isWriteOnly
     * @return $this|AttributeBuilderInterface
     */
    public function setIsWriteOnly(bool $isWriteOnly): AttributeBuilderInterface;

    /**
     * @return string|null
     */
    public function getTransformationClass(): ?string;

    /**
     * @param string|null $transformationClass
     * @return $this|AttributeBuilderInterface
     */
    public function setTransformationClass(?string $transformationClass): AttributeBuilderInterface;

    /**
     * @return string|null
     */
    public function getTransformationMethod(): ?string;

    /**
     * @param string|null $transformationMethod
     * @return $this|AttributeBuilderInterface
     */
    public function setTransformationMethod(?string $transformationMethod): AttributeBuilderInterface;

    /**
     * @return ParameterValidatorInterface
     */
    public function getType(): ParameterValidatorInterface;

    /**
     * @param string $type
     * @return $this|AttributeBuilderInterface
     */
    public function setType(string $type): AttributeBuilderInterface;

    /**
     * @return string|null
     */
    public function getValidator(): ?string;

    /**
     * @param string|null $validator
     * @return $this|AttributeBuilderInterface
     */
    public function setValidator(?string $validator): AttributeBuilderInterface;

    /**
     * @return ResourceBuilderInterface
     */
    public function getResource() : ResourceBuilderInterface;

    /**
     * @param ResourceBuilderInterface $resource
     */
    public function setRelationshipResource(ResourceBuilderInterface $resource): void;

    /**
     * @return ResourceBuilderInterface|null
     */
    public function getRelationshipResource(): ?ResourceBuilderInterface;

    /**
     * @return string|null
     */
    public function serialise(): ?string;
}