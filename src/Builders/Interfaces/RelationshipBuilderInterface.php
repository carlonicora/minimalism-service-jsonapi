<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces;

use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\LinkBuilder;
use CarloNicora\Minimalism\Services\JsonApi\JsonApi;
use CarloNicora\Minimalism\Services\MySQL\MySQL;

interface RelationshipBuilderInterface extends CallableInterface, BuilderLinksInterface
{
    /**
     * RelationshipBuilderInterface constructor.
     * @param JsonApi $jsonApi
     * @param MySQL $mysql
     * @param Cacher $cacher
     * @param string $name
     */
    public function __construct(
        JsonApi $jsonApi,
        MySQL $mysql,
        Cacher $cacher,
        string $name
    );

    /**
     * @param AttributeBuilderInterface $attribute
     * @param string|null $fieldName
     * @return RelationshipBuilderInterface
     */
    public function withBuilder(
        AttributeBuilderInterface $attribute,
        string $fieldName=null
    ): RelationshipBuilderInterface;

    /**
     * @param string $tableInterfaceClass
     * @param string $fieldName
     * @return RelationshipBuilderInterface
     */
    public function throughManyToManyTable(
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
     * @param string $loaderClassName
     * @param string|null $resourceBuilderClass
     * @param string $loaderFunction
     * @param array $parameters
     * @return RelationshipBuilderInterface
     */
    public function withLoaderFunction(
        string $loaderClassName,
        ?string $resourceBuilderClass,
        string $loaderFunction,
        array $parameters
    ): RelationshipBuilderInterface;


    /**
     * @param CacheBuilder $cacheBuilder
     * @return RelationshipBuilderInterface
     */
    public function withCache(
        CacheBuilder $cacheBuilder
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
     * @return string
     */
    public function getBuilder(): string;

    /**
     * @param LinkBuilder $link
     * @return RelationshipBuilderInterface
     */
    public function withLink(LinkBuilder $link): RelationshipBuilderInterface;

    /**
     * @param array $data
     * @param int $loadRelationshipLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array|null
     */
    public function loadResources(
        array $data,
        int $loadRelationshipLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): ?array;

    /**
     * @return AttributeBuilderInterface|null
     */
    public function getAttribute(): ?AttributeBuilderInterface;
}