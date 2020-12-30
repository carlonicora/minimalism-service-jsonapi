<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Abstracts\AbstractRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipTypeInterface;
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
     * @return array|null
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
        return $this->jsonApi->generateResourceObjectByFieldValue(
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