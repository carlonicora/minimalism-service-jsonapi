<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Objects;

class EntityRelationship
{
    public const RELATIONSHIP_TYPE_ONE_TO_ONE=1;
    public const RELATIONSHIP_TYPE_ONE_TO_MANY=2;

    /** @var string  */
    private string $relationshipName;

    /** @var string|null  */
    private ?string $tableName;

    /** @var string|null  */
    private ?string $databaseRelationshipField;

    /** @var EntityResource  */
    private EntityResource $resource;

    /**
     * EntityRelationship constructor.
     * @param string $relationshipName
     * @param array $entityResource
     */
    public function __construct(string $relationshipName, array $entityResource)
    {
        $this->relationshipName = $relationshipName;

        $this->tableName = $entityResource['$databaseTable'] ?? null;
        $this->databaseRelationshipField = $entityResource['$databaseRelationshipField'] ?? null;

        $this->resource = new EntityResource($entityResource['data']);
    }

    /**
     * @return string
     */
    public function getRelationshipName(): string
    {
        return $this->relationshipName;
    }

    /**
     * @return EntityResource
     */
    public function getResource(): EntityResource
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
     * @return string|null
     */
    public function getDatabaseRelationshipField(): ?string
    {
        return $this->databaseRelationshipField;
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