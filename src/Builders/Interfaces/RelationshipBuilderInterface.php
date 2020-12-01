<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\LinkBuilder;

interface RelationshipBuilderInterface extends CallableInterface, BuilderLinksInterface
{
    /**
     * RelationshipBuilderInterface constructor.
     * @param ServicesFactory $services
     * @param string $name
     */
    public function __construct(
        ServicesFactory $services, 
        string $name
    );

    /**
     * @param AttributeBuilderInterface $attribute
     * @param string|null $fieldName
     * @return RelationshipBuilderInterface
     */
    public function withTargetTable(
        AttributeBuilderInterface $attribute,
        string $fieldName=null
    ): RelationshipBuilderInterface;

    /**
     * @param string $tableInterfaceClass
     * @param string $fieldName
     * @return RelationshipBuilderInterface
     */
    public function withHopTable(
        string $tableInterfaceClass,
        string $fieldName
    ): RelationshipBuilderInterface;

    /**
     * @param string $tableClassName
     * @param string|null $resourceBuilderClass
     * @param string $tableFunction
     * @param array $parameters
     * @return RelationshipBuilderInterface
     */
    public function withTableFunction(
        string $tableClassName,
        ?string $resourceBuilderClass,
        string $tableFunction,
        array $parameters
    ): RelationshipBuilderInterface;

    /**
     * @return RelationshipBuilderInterface
     */
    public function withoutChildren(): RelationshipBuilderInterface;
    
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @param LinkBuilder $link
     * @return RelationshipBuilderInterface
     */
    public function withLink(LinkBuilder $link): RelationshipBuilderInterface;

    /**
     * @param array $data
     * @param int $loadRelationshipLevel
     * @return array|ResourceObject[]|null
     */
    public function loadResources(
        array $data,
        int $loadRelationshipLevel=0
    ): ?array;

    /**
     * @return AttributeBuilderInterface|null
     */
    public function getAttribute(): ?AttributeBuilderInterface;
}