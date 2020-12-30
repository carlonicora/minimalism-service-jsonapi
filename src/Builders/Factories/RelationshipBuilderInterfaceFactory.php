<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Factories;

use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\ManyToManyRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\OneToManyRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\OneToOneRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipTypeInterface;
use CarloNicora\Minimalism\Services\JsonApi\JsonApi;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use Exception;

class RelationshipBuilderInterfaceFactory
{
    /**
     * RelationshipBuilderInterfaceFactory constructor.
     * @param JsonApi $jsonApi
     * @param MySQL $mysql
     * @param Cacher $cacher
     */
    public function __construct(
        protected JsonApi $jsonApi,
        protected MySQL $mysql,
        protected Cacher $cacher,
    )
    {
    }

    /**
     * @param int $relationshipType
     * @param string $name
     * @return RelationshipBuilderInterface
     * @throws Exception
     */
    public function create(int $relationshipType, string $name): RelationshipBuilderInterface
    {
        if ($relationshipType === RelationshipTypeInterface::ONE_TO_ONE){
            return new OneToOneRelationshipBuilder($this->jsonApi, $this->mysql, $this->cacher, $name);
        }

        if ($relationshipType === RelationshipTypeInterface::ONE_TO_MANY){
            return new OneToManyRelationshipBuilder($this->jsonApi, $this->mysql, $this->cacher, $name);
        }

        return new ManyToManyRelationshipBuilder($this->jsonApi, $this->mysql, $this->cacher, $name);
    }
}