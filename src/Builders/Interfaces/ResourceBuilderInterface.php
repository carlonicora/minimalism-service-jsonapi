<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheBuilderFactoryInterface;
use CarloNicora\Minimalism\Services\JsonApi\JsonApi;
use CarloNicora\Minimalism\Services\MySQL\MySQL;

interface ResourceBuilderInterface extends CallableInterface, BuilderLinksInterface
{
    /**
     * ResourceBuilderInterface constructor.
     * @param JsonApi $jsonApi
     * @param MySQL $mysql
     * @param Cacher $cacher
     */
    public function __construct(
        JsonApi $jsonApi,
        MySQL $mysql,
        Cacher $cacher,
    );

    /**
     *
     */
    public function initialiseRelationships(): void;

    /**
     * @return string|null
     */
    public function getDataCacheName(): ?string;

    /**
     * @return string|null
     */
    public function getResourceCacheName(): ?string;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return string|null
     */
    public function getTableName() : ?string;

    /**
     * @param string $attributeName
     * @return AttributeBuilderInterface|null
     */
    public function getAttribute(string $attributeName) : ?AttributeBuilderInterface;

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @return array
     */
    public function getMeta(): array;

    /**
     * @param string $relationshipName
     * @return RelationshipBuilderInterface|null
     */
    public function getRelationship(string $relationshipName) : ?RelationshipBuilderInterface;

    /**
     * @param CacheBuilderFactoryInterface $cacheFactory
     */
    public function setCacheFactoryInterface(CacheBuilderFactoryInterface $cacheFactory): void;

    /**
     * @return array
     */
    public function getRelationships(): array;

    /**
     * @param array $data
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return ResourceObject
     */
    public function buildResourceObject(
        array $data,
        int $loadRelationshipsLevel = 0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): ResourceObject;
}