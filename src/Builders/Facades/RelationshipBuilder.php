<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Traits\LinkBuilderTrait;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Traits\ReadFunctionTrait;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;

class RelationshipBuilder implements RelationshipBuilderInterface
{
    use ReadFunctionTrait;
    use LinkBuilderTrait;

    /** @var string  */
    private string $name;

    /** @var int  */
    private int $type;

    /** @var AttributeBuilderInterface  */
    private AttributeBuilderInterface $attribute;

    /** @var bool  */
    private bool $loadData=true;

    /** @var string  */
    private string $resourceBuilderName;

    /** @var string  */
    private string $resourceObjectName;

    /** @var string|null  */
    private ?string $manyToManyRelationshipTableName=null;

    /** @var string|null  */
    private ?string $manyToManyRelationshipTableClass=null;

    /** @var string|null  */
    private ?string $manyToManyRelationshipField;

    /** @var array|null  */
    private ?array $manyToManyAdditionalValues=null;

    /** @var bool  */
    private bool $isRequired=false;

    /**
     * RelationshipBuilder constructor.
     * @param ServicesFactory $services
     * @param string $name
     * @param int $type
     * @param AttributeBuilderInterface $attribute
     * @param string|null $fieldName
     * @param string|null $manyToManyRelationshipTableName
     * @param string|null $manyToManyRelationshipField
     * @param array|null $manyToManyAdditionalValues
     * @throws Exception
     */
    public function __construct(ServicesFactory $services, string $name, int $type, AttributeBuilderInterface $attribute, string $fieldName=null, string $manyToManyRelationshipTableName=null, string $manyToManyRelationshipField=null, ?array $manyToManyAdditionalValues=null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->services = $services;

        $this->attribute = $attribute;
        if ($attribute->getRelationshipResource() !== null) {
            $this->resourceBuilderName = get_class($attribute->getRelationshipResource());
            $this->resourceObjectName = $attribute->getRelationshipResource()->getType();
        } else {
            $this->resourceBuilderName = get_class($attribute->getResource());
            $this->resourceObjectName = $attribute->getResource()->getType();
        }
        $this->attribute->setDatabaseFieldRelationship($fieldName ?? $this->attribute->getDatabaseFieldName());

        $this->manyToManyRelationshipField = $manyToManyRelationshipField;

        if ($manyToManyRelationshipTableName !== null) {
            $this->manyToManyRelationshipTableClass = $manyToManyRelationshipTableName;
            /** @var MySQL $mysql */
            $mysql = $this->services->service(MySQL::class);
            $table = $mysql->create($manyToManyRelationshipTableName);
            $this->manyToManyRelationshipTableName = $table->getTableName();
            $this->manyToManyAdditionalValues=$manyToManyAdditionalValues;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     */
    public function setIsRequired(bool $isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getResourceBuilderName(): ?string
    {
        return $this->resourceBuilderName;
    }

    /**
     * @return AttributeBuilderInterface
     */
    public function getAttribute(): AttributeBuilderInterface
    {
        return $this->attribute;
    }

    /**
     * @return string|null
     */
    public function getManyToManyRelationshipTableName(): ?string
    {
        return $this->manyToManyRelationshipTableName;
    }

    /**
     * @return string|null
     */
    public function getManyToManyRelationshipField(): ?string
    {
        return $this->manyToManyRelationshipField;
    }

    /**
     * @return string
     */
    public function getResourceObjectName(): string
    {
        return $this->resourceObjectName;
    }

    /**
     * @return string|null
     */
    public function getManyToManyRelationshipTableClass(): ?string
    {
        return $this->manyToManyRelationshipTableClass;
    }

    /**
     * @return array|null
     */
    public function getManyToManyAdditionalValues(): ?array
    {
        return $this->manyToManyAdditionalValues;
    }
}