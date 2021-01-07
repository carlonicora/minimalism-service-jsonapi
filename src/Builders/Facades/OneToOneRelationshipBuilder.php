<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

use CarloNicora\Minimalism\Interfaces\CacheBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Abstracts\AbstractRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipTypeInterface;
use Exception;

class OneToOneRelationshipBuilder extends AbstractRelationshipBuilder
{
    /** @var int  */
    protected int $type=RelationshipTypeInterface::ONE_TO_ONE;

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
        if ($data[$this->targetBuilderAttribute->getDatabaseFieldRelationship()] === null) {
            return null;
        }

        return $this->resourceReader->generateResourceObjectByFieldValue(
            $this->resourceBuilderName,
            $cache,
            $this->targetBuilderAttribute,
            [$data[$this->targetBuilderAttribute->getDatabaseFieldRelationship()]],
            $loadRelationshipLevel,
            $relationshipParameters,
            $positionInRelationship
        );
    }
}