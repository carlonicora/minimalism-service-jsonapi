<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheFactoryInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts\AbstractRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipTypeInterface;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
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
     * @return $this|RelationshipBuilderInterface
     * @throws Exception
     */
    public function throughManyToManyTable(
        string $tableInterfaceClass,
        string $fieldName
    ): RelationshipBuilderInterface
    {
        $this->manyToManyRelationshipField = $fieldName;

        $this->manyToManyRelationshipTableClass = $tableInterfaceClass;

        $table = $this->mysql->create($tableInterfaceClass);
        $this->manyToManyRelationshipTableName = $table->getTableName();

        return $this;
    }

    /**
     * @param array $data
     * @param CacheFactoryInterface|null $cache
     * @param int $loadRelationshipLevel
     * @return array|ResourceObject[]|null
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    protected function loadSpecialisedResources(
        array $data,
        ?CacheFactoryInterface $cache,
        int $loadRelationshipLevel=0
    ): ?array
    {
        return $this->mapper->generateResourceObjectsByFunction(
            $this->resourceBuilderName,
            $cache,
            FunctionFactory::buildFromTableName(
                $this->resourceBuilder->getTableName(),
                'getFirstLevelJoin'
            ),
            [
                $this->manyToManyRelationshipTableName,
                $this->targetBuilderAttribute->getDatabaseFieldRelationship(),
                $this->manyToManyRelationshipField,
                $data[$this->targetBuilderAttribute->getDatabaseFieldRelationship()],
            ],
            $loadRelationshipLevel
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