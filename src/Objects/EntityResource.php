<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Objects;

use CarloNicora\Minimalism\Services\JsonDataMapper\Factories\EntityLink;

class EntityResource
{
    /** @var string  */
    private string $type;

    /** @var EntityField  */
    private EntityField $id;

    /** @var array|null  */
    private ?array $attributes=null;

    /** @var string|null  */
    private ?string $tableName=null;

    /** @var array|null  */
    private ?array $links=null;

    /**
     * EntityResource constructor.
     * @param array $resource
     */
    public function __construct(array $resource)
    {
        $this->type = $resource['type'];

        $this->id = new EntityField($this, 'id', $resource['id'], true);

        if (array_key_exists('$databaseTable', $resource)){
            $this->tableName = $resource['$databaseTable'];
        }

        if (array_key_exists('attributes', $resource) && count($resource['attributes']) > 0){
            $this->attributes = [];
            foreach ($resource['attributes'] ?? [] as $attributeName=>$attribute) {
                $this->attributes[] = new EntityField($this, $attributeName, $attribute);
            }
        }

        if (array_key_exists('links', $resource) && count($resource['links']) > 0){
            $this->links = [];
            foreach ($resource['links'] ?? [] as $linkName=>$link) {
                if (is_array($link)){
                    $this->links[] = new EntityLink($linkName, $link['href'], $link['meta']);
                } else {
                    $this->links[] = new EntityLink($linkName, $link);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $fieldName
     * @return EntityField|null
     */
    public function getField(string $fieldName) : ?EntityField
    {
        if ($this->id->getName() === $fieldName){
            return $this->id;
        }

        /** @var EntityField $field */
        foreach ($this->attributes ?? [] as $field){
            if ($field->getName() === $fieldName){
                return $field;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getTable() : string
    {
        return $this->tableName;
    }

    /**
     * @return EntityField
     */
    public function getId(): EntityField
    {
        return $this->id;
    }

    /**
     * @return array|null|EntityField[]
     */
    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    /**
     * @return array|null|EntityLink[]
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }
}