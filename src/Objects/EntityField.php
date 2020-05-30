<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Objects;

class EntityField
{
    /** @var EntityResource  */
    private EntityResource $resource;

    /** @var string  */
    private string $name;

    /** @var string  */
    private string $type;

    /** @var bool  */
    private bool $isEncrypted=false;

    /** @var bool  */
    private bool $isRequired=false;

    /** @var string|null  */
    private ?string $databaseField;

    /** @var string|null  */
    private ?string $databaseRelationshipField=null;

    /** @var bool  */
    private bool $isPrimaryKey;

    /** @var string|null  */
    private ?string $validator=null;

    /** @var string|null  */
    private ?string $transformClass=null;

    /** @var string|null  */
    private ?string $transformFunction=null;

    /**
     * EntityField constructor.
     * @param EntityResource $resource
     * @param string $name
     * @param array $field
     * @param bool $isId
     */
    public function __construct(EntityResource $resource, string $name, array $field, bool $isId=false)
    {
        $this->resource = $resource;

        $this->name = $name;
        $this->isPrimaryKey = $isId;
        $this->type = $field['$type'];

        /** FIELDS USED DURING READ */
        if (array_key_exists('$databaseField', $field)){
            $this->databaseField = $field['$databaseField'];
        } else {
            $this->databaseField = $name;
        }

        if (array_key_exists('$transformClass', $field)
            && array_key_exists('$transformFunction', $field)
            && !empty($field['$transformClass'])
            && !empty($field['$transformFunction'])
        ){
            $this->transformClass = $field['$transformClass'];
            $this->transformFunction = $field['$transformFunction'];
        }

        if (array_key_exists('$databaseRelationshipField', $field)){
            $this->databaseRelationshipField = $field['$databaseRelationshipField'];
        }

        if (array_key_exists('$encrypted', $field)){
            $this->isEncrypted = $field['$encrypted'];
        }

        /** FIELDS USED DURING WRITE */
        if (array_key_exists('$required', $field)){
            $this->isRequired = $field['$required'];
        }

        if (array_key_exists('$validator', $field)){
            $this->validator = $field['$validator'];
        }
    }

    /**
     * @param $originalValue
     * @return mixed
     */
    public function getTransformedValue($originalValue)
    {
        if ($this->transformClass === null){
            return $originalValue;
        }

        $transformer = new $this->transformClass();
        return $transformer->{$this->transformFunction}($originalValue);
    }

    /**
     * @return bool
     */
    public function isEncrypted(): bool
    {
        return $this->isEncrypted;
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
    public function getTable() : string
    {
        return $this->resource->getTable();
    }

    /**
     * @return bool
     */
    public function isPrimaryKey() : bool
    {
        return $this->isPrimaryKey;
    }

    /**
     * @return string|null
     */
    public function getDatabaseField(): ?string
    {
        return $this->databaseField;
    }
}