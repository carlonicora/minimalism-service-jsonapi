<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Objects;

use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\Traits\LinksTrait;

class EntityRelationship
{
    use LinksTrait;

    public const RELATIONSHIP_TYPE_ONE_TO_ONE=1;
    public const RELATIONSHIP_TYPE_ONE_TO_MANY=2;

    /** @var string  */
    private string $relationshipName;

    /** @var string|null  */
    private ?string $tableName;

    /** @var bool  */
    private bool $isRequired;

    /** @var EntityResource  */
    private ?EntityResource $resource=null;

    /**
     * EntityRelationship constructor.
     * @param string $relationshipName
     * @param array $entityResource
     */
    public function __construct(string $relationshipName, array $entityResource)
    {
        $this->relationshipName = $relationshipName;

        $this->tableName = $entityResource['$databaseTable'] ?? null;
        $this->isRequired = $entityResource['$isRequired'] ?? false;

        if (array_key_exists('links', $entityResource) && count($entityResource['links']) > 0){
            $this->addLinks($entityResource['links']);
        }

        if (array_key_exists('data', $entityResource)) {
            $this->resource = new EntityResource($entityResource['data']);
        }
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * @return string
     */
    public function getRelationshipName(): string
    {
        return $this->relationshipName;
    }

    /**
     * @return EntityResource|null
     */
    public function getResource(): ?EntityResource
    {
        return $this->resource;
    }

    /**
     * @return string|null
     */
    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    /**
     * @return int
     */
    public function getType() : int
    {

        if ($this->tableName === null){
            return self::RELATIONSHIP_TYPE_ONE_TO_ONE;
        }

        return self::RELATIONSHIP_TYPE_ONE_TO_MANY;
    }
}