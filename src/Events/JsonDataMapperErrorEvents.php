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
}