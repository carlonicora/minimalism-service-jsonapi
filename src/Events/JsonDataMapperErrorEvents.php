<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Events;

use CarloNicora\Minimalism\Core\Events\Abstracts\AbstractErrorEvent;
use CarloNicora\Minimalism\Core\Events\Interfaces\EventInterface;
use CarloNicora\Minimalism\Core\Modules\Interfaces\ResponseInterface;

class JsonDataMapperErrorEvents extends AbstractErrorEvent
{
    /** @var string  */
    protected string $serviceName = 'jsondatamapper';

    public static function CONFIGURATION_FILE_MISCONFIGURED(string $entityName) : EventInterface
    {
        return new self(1, ResponseInterface::HTTP_STATUS_500, 'Entity configuration file for %s is incorrect', [$entityName]);
    }

    public static function LOADER_FIELD_NOT_FOUND(string $fieldName, string $entityName) : EventInterface
    {
        return new self(2, ResponseInterface::HTTP_STATUS_500, 'Entity field name %s not found in %s', [$fieldName, $entityName]);
    }

    public static function REQUIRED_FIELD_MISSING(string $parameterName) : EventInterface
    {
        return new self(3, ResponseInterface::HTTP_STATUS_412, 'Document malformed: parameter %s missing', [$parameterName]);
    }

    public static function REQUIRED_RELATIONSHIP_MISSING(string $parameterName) : EventInterface
    {
        return new self(4, ResponseInterface::HTTP_STATUS_412, 'Document malformed: relationship %s missing', [$parameterName]);
    }

    public static function RELATIONSHIP_RESOURCE_MISMATCH(string $passedType, string $expectedType) : EventInterface
    {
        return new self(
            5,
            ResponseInterface::HTTP_STATUS_412,
            'Document malformed: relationship type %s passed, but %s expected',
            [$passedType, $expectedType]
        );
    }

    public static function NO_ATTRIBUTE_FOUND(string $resourceBuilderName, string $attributeName) : EventInterface
    {
        return new self(
            6,
            ResponseInterface::HTTP_STATUS_500,
            'Attribute %s not found in resource builder %s',
            [$attributeName, $resourceBuilderName]
        );
    }
}