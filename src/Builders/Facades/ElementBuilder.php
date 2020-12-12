<?php

namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ElementBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\Factories\ParameterValidatorFactory;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterValidatorInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

abstract class ElementBuilder implements ElementBuilderInterface
{
    /** @var string  */
    protected string $name;

    /** @var string  */
    protected string $type=ParameterValidator::PARAMETER_TYPE_STRING;

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

    /** @var ServicesFactory */
    protected ServicesFactory $services;

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
     * @return $this|ElementBuilderInterface
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
     * @return $this|ElementBuilderInterface
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
     * @return $this|ElementBuilderInterface
     */
    public function setDatabaseFieldRelationship(?string $databaseFieldRelationship): ElementBuilderInterface
    {
        return $this;
    }

    /**
     * @param $value
     * @return $this|ElementBuilderInterface
     */
    public function setStaticValue($value): ElementBuilderInterface
    {
        $this->staticValue = $value;

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getStaticValue()
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
     * @return $this|ElementBuilderInterface
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
     * @return $this|ElementBuilderInterface
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
     * @return $this|ElementBuilderInterface
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
     * @return $this|ElementBuilderInterface
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
     * @return $this|ElementBuilderInterface
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
     * @return $this|ElementBuilderInterface
     */
    public function setTransformationMethod(?string $transformationMethod): ElementBuilderInterface
    {
        $this->transformationMethod = $transformationMethod;

        return $this;
    }

    /**
     * @return ParameterValidatorInterface
     * @throws Exception
     */
    public function getType(): ParameterValidatorInterface
    {
        $parameterValidatorFactory = new ParameterValidatorFactory();
        return $parameterValidatorFactory->createParameterValidator($this->services, $this->type);
    }

    /**
     * @param string $type
     * @return $this|ElementBuilderInterface
     */
    public function setType(string $type): ElementBuilderInterface
    {
        $this->type = $type;

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
     * @return $this|ElementBuilderInterface
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