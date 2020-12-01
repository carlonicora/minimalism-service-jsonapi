<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\ManyToManyRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\OneToManyRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\OneToOneRelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipTypeInterface;
use Exception;

class RelationshipBuilderInterfaceFactory
{
    /**
     * @var ServicesFactory
     */
    private ServicesFactory $services;

    /**
     * RelationshipBuilderInterfaceFactory constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
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
            return new OneToOneRelationshipBuilder($this->services, $name);
        }

        if ($relationshipType === RelationshipTypeInterface::ONE_TO_MANY){
            return new OneToManyRelationshipBuilder($this->services, $name);
        }

        return new ManyToManyRelationshipBuilder($this->services, $name);
    }
}