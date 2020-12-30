<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

use CarloNicora\Minimalism\Interfaces\CacheBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Abstracts\AbstractRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipTypeInterface;
use Exception;

class ManyToManyRelationshipBuilder extends AbstractRelationshipBuilder
{
    /** @var int  */
    protected int $type=RelationshipTypeInterface::MANY_TO_MANY;

    /** @var string  */
    private string $manyToManyRelationshipTableClass;

    /** @var string  */
    private string $manyToManyRelationshipTableName;

    /** @var string  */
    private string $manyToManyRelationshipField;

    /**
     * @param string $tableInterfaceClass
     * @param string $fieldName
     * @return RelationshipBuilderInterface
     * @throws Exception
     */
    public function throughManyToManyTable(
        string $tableInterfaceClass,
        string $fieldName
    ): RelationshipBuilderInterface
    {
        $this->manyToManyRelationshipField = $fieldName;

        $this->manyToManyRelationshipTableClass = $tableInterfaceClass;

        $table = $this->servicesProxy->getDataProvider()->create($tableInterfaceClass);
        $this->manyToManyRelationshipTableName = $table->getTableName();

        return $this;
    }

    /**
     * @param array $data
     * @param CacheBuilderInterface|null $cache
     * @param int $loadRelationshipLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array|null
     * @throws Exception
     */
    protected function loadSpecialisedResources(
        array $data,
        ?CacheBuilderInterface $cache,
        int $loadRelationshipLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): ?array
    {
        $parameters = [
            $this->manyToManyRelationshipTableName,
            $this->targetBuilderAttribute->getDatabaseFieldRelationship(),
            $this->manyToManyRelationshipField,
            $data[$this->targetBuilderAttribute->getDatabaseFieldRelationship()],
        ];

        return $this->resourceReader->generateResourceObjectsByFunction(
            $this->resourceBuilderName,
            $cache,
            FunctionFactory::buildFromTableName(
                $this->resourceBuilder->getTableName(),
                'getFirstLevelJoin',
                $parameters
            ),
            $loadRelationshipLevel,
            $relationshipParameters,
            $positionInRelationship
        );
    }

    /**
     * @return string
     */
    public function getManyToManyRelationshipTableClass(): string
    {
        return $this->manyToManyRelationshipTableClass;
    }
}