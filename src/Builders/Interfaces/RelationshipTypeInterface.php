<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces;

interface RelationshipTypeInterface
{
    public const RELATIONSHIP_ONE_TO_ONE=1;
    public const RELATIONSHIP_ONE_TO_MANY=2;
    public const RELATIONSHIP_MANY_TO_MANY=3;
}