<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Events;

use CarloNicora\Minimalism\Core\Events\Abstracts\AbstractInfoEvent;
use CarloNicora\Minimalism\Core\Events\Interfaces\EventInterface;

class JsonDataMapperInfoEvents extends AbstractInfoEvent
{
    /** @var string  */
    protected string $serviceName = 'json-data-mapper';

    public static function GENERIC(string $eventInfo) : EventInterface
    {
        return new self(1, null, $eventInfo);
    }
}