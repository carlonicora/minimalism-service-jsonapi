<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts\AbstractRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipTypeInterface;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use Exception;

class OneToManyRelationshipBuilder extends AbstractRelationshipBuilder
{
    /** @var int  */
    protected int $type=RelationshipTypeInterface::ONE_TO_MANY;

    /**
     * @param array $data
     * @param CacheBuilder|null $cache
     * @param int $loadRelationshipLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array|ResourceObject[]|null
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    protected function loadSpecialisedResources(
        array $data,
        ?CacheBuilder $cache,
        int $loadRelationshipLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): ?array
    {
        return $this->mapper->generateResourceObjectByFieldValue(
            $this->resourceBuilderName,
            $cache,
            $this->targetBuilderAttribute,
            $data[$this->targetBuilderAttribute->getDatabaseFieldRelationship()],
            $loadRelationshipLevel,
            $relationshipParameters,
            $positionInRelationship
        );
    }
}