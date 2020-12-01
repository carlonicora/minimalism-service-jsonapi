<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts\AbstractRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipTypeInterface;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;

class OneToOneRelationshipBuilder extends AbstractRelationshipBuilder
{
    /** @var int  */
    protected int $type=RelationshipTypeInterface::ONE_TO_ONE;

    /**
     * @param array $data
     * @param int $loadRelationshipLevel
     * @return array|ResourceObject[]|null
     * @throws DbRecordNotFoundException
     */
    protected function loadSpecialisedResources(
        array $data,
        int $loadRelationshipLevel=0
    ): ?array
    {
        if ($data[$this->targetBuilderAttribute->getDatabaseFieldRelationship()] === null) {
            return null;
        }

        return $this->mapper->generateResourceObjectByFieldValue(
            $this->resourceBuilderName,
            null,
            $this->targetBuilderAttribute,
            $data[$this->targetBuilderAttribute->getDatabaseFieldRelationship()],
            $loadRelationshipLevel
        );
    }
}