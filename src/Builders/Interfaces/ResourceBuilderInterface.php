<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Interfaces\CacheBuilderFactoryInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;

interface ResourceBuilderInterface extends CallableInterface, BuilderLinksInterface
{
    /**
     * ResourceBuilderInterface constructor.
     * @param ServicesProxy $servicesProxy
     */
    public function __construct(
        ServicesProxy $servicesProxy,
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
    public function getAttribute(
        string $attributeName
    ) : ?AttributeBuilderInterface;

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
    public function getRelationship(
        string $relationshipName
    ) : ?RelationshipBuilderInterface;

    /**
     * @param CacheBuilderFactoryInterface $cacheFactory
     */
    public function setCacheFactoryInterface(
        CacheBuilderFactoryInterface $cacheFactory
    ): void;

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