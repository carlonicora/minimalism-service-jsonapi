<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\Factories\ParameterValidatorFactory;
use CarloNicora\Minimalism\Services\ParameterValidator\Interfaces\ParameterValidatorInterface;
use CarloNicora\Minimalism\Services\ParameterValidator\ParameterValidator;
use Exception;

class AttributeBuilder implements AttributeBuilderInterface
{
    /** @var string  */
    private string $name;

    /** @var string  */
    private string $type=ParameterValidator::PARAMETER_TYPE_STRING;

    /** @var string|null  */
    private ?string $validator=null;

    /** @var bool  */
    private bool $isRequired=false;

    /** @var bool  */
    private bool $isEncrypted=false;

    /** @var string|null  */
    private ?string $transformationClass=null;

    /** @var string|null  */
    private ?string $transformationMethod=null;

    /** @var bool  */
    private bool $isReadOnly=false;

    /** @var bool  */
    private bool $isWriteOnly=false;

    /** @var string|null  */
    private ?string $databaseFieldName;

    /** @var string|null  */
    private ?string $databaseFieldRelationship=null;

    /** @var ServicesFactory */
    private ServicesFactory $services;

    /** @var ResourceBuilderInterface  */
    private ResourceBuilderInterface $parent;

    /** @var ResourceBuilderInterface|null  */
    private ?ResourceBuilderInterface $relationship=null;

    /** @var  */
    private $staticValue;

    /**
     * AttributeBuilder constructor.
     * @param ServicesFactory $services
     * @param ResourceBuilderInterface $parent
     * @param string $name
     */
    public function __construct(ServicesFactory $services, ResourceBuilderInterface $parent, string $name)
    {
        $this->name = $name;
        $this->databaseFieldName = $name;
        $this->services = $services;
        $this->parent = $parent;
    }

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
     * @return $this|AttributeBuilderInterface
     */
    public function setName(string $name): AttributeBuilderInterface
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
     * @return $this|AttributeBuilderInterface
     */
    public function setDatabaseFieldName(?string $databaseFieldName): AttributeBuilderInterface
    {
        $this->databaseFieldName = $databaseFieldName;

        return $this;
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
     * @return $this|AttributeBuilderInterface
     */
    public function setDatabaseFieldRelationship(?string $databaseFieldRelationship): AttributeBuilderInterface
    {
        $this->databaseFieldRelationship = $databaseFieldRelationship;

        return $this;
    }

    /**
     * @param $value
     * @return $this|AttributeBuilderInterface
     */
    public function setStaticValue($value): AttributeBuilderInterface
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
     * @return $this|AttributeBuilderInterface
     */
    public function setIsEncrypted(bool $isEncrypted): AttributeBuilderInterface
    {
        $this->isEncrypted = $isEncrypted;

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
     * @return $this|AttributeBuilderInterface
     */
    public function setIsReadOnly(bool $isReadOnly): AttributeBuilderInterface
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
     * @return $this|AttributeBuilderInterface
     */
    public function setIsRequired(bool $isRequired): AttributeBuilderInterface
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
     * @return $this|AttributeBuilderInterface
     */
    public function setIsWriteOnly(bool $isWriteOnly): AttributeBuilderInterface
    {
        $this->isWriteOnly = $isWriteOnly;

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
     * @return $this|AttributeBuilderInterface
     */
    public function setTransformationClass(?string $transformationClass): AttributeBuilderInterface
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
     * @return $this|AttributeBuilderInterface
     */
    public function setTransformationMethod(?string $transformationMethod): AttributeBuilderInterface
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
     * @return $this|AttributeBuilderInterface
     */
    public function setType(string $type): AttributeBuilderInterface
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
     * @return $this|AttributeBuilderInterface
     */
    public function setValidator(?string $validator): AttributeBuilderInterface
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