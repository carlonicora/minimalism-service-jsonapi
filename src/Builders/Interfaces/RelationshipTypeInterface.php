<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces;

interface RelationshipTypeInterface
{
    public const ONE_TO_ONE=1;
    public const ONE_TO_MANY=2;
    public const MANY_TO_MANY=3;
}