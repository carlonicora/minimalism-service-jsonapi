<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheFactoryInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts\AbstractRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipTypeInterface;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;

class OneToOneRelationshipBuilder extends AbstractRelationshipBuilder
{
    /** @var int  */
    protected int $type=RelationshipTypeInterface::ONE_TO_ONE;

    /**
     * @param array $data
     * @param CacheFactoryInterface|null $cache
     * @param int $loadRelationshipLevel
     * @return array|ResourceObject[]|null
     * @throws DbRecordNotFoundException
     */
    protected function loadSpecialisedResources(
        array $data,
        ?CacheFactoryInterface $cache,
        int $loadRelationshipLevel=0
    ): ?array
    {
        if ($data[$this->targetBuilderAttribute->getDatabaseFieldRelationship()] === null) {
            return null;
        }

        return $this->mapper->generateResourceObjectByFieldValue(
            $this->resourceBuilderName,
            $cache,
            $this->targetBuilderAttribute,
            $data[$this->targetBuilderAttribute->getDatabaseFieldRelationship()],
            $loadRelationshipLevel
        );
    }
}