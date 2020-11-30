<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\RelationshipBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\RelationshipBuilderInterface;
use Exception;

class RelationshipBuilderFactory
{
    /**
     * @var ServicesFactory
     */
    private ServicesFactory $services;

    /**
     * AttributeBuilderFactory constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
    }

    /**
     * @param string $name
     * @param int $type
     * @param AttributeBuilderInterface $attribute
     * @param string|null $fieldName
     * @param string|null $manyToManyRelationshipTableName
     * @param string|null $manyToManyRelationshipField
     * @param array|null $manyToManyAdditionalValues
     * @param bool $loadChildren
     * @return RelationshipBuilderInterface
     * @throws Exception
     */
    public function create(
        string $name,
        int $type,
        AttributeBuilderInterface $attribute,
        string $fieldName=null,
        string $manyToManyRelationshipTableName=null,
        string $manyToManyRelationshipField=null,
        ?array $manyToManyAdditionalValues=null,
        bool $loadChildren=true
    ) : RelationshipBuilderInterface
    {
        return new RelationshipBuilder(
            $this->services,
            $name,
            $type,
            $attribute,
            $fieldName,
            $manyToManyRelationshipTableName,
            $manyToManyRelationshipField,
            $manyToManyAdditionalValues,
            $loadChildren
        );
    }
}