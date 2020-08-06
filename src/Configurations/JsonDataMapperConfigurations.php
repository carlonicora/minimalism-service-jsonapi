<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Configurations;

use CarloNicora\Minimalism\Core\Services\Abstracts\AbstractServiceConfigurations;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Redis\Redis;

class JsonDataMapperConfigurations  extends AbstractServiceConfigurations
{
    /** @var array|string[]  */
    protected array $dependencies = [
        MySQL::class,
        Redis::class
    ];
}