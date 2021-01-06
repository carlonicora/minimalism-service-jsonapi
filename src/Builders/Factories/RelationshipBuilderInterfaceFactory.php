<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Factories;

use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\ManyToManyRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\OneToManyRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\OneToOneRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\RelationshipTypeInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use Exception;

class RelationshipBuilderInterfaceFactory
{
    /**
     * RelationshipBuilderInterfaceFactory constructor.
     * @param ServicesProxy $servicesProxy
     */
    public function __construct(
        protected ServicesProxy $servicesProxy,
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
        if ($relationshipType === RelationshipTypeInterface::ONE_TO_ONE) {
            return new OneToOneRelationshipBuilder(
                servicesProxy: $this->servicesProxy,
                name: $name,
            );
        }

        if ($relationshipType === RelationshipTypeInterface::ONE_TO_MANY){
            return new OneToManyRelationshipBuilder(
                servicesProxy: $this->servicesProxy,
                name: $name
            );
        }

        return new ManyToManyRelationshipBuilder(
            servicesProxy: $this->servicesProxy,
            name: $name
        );
    }
}