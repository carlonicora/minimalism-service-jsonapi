<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Tests\Unit\Configurations;

use CarloNicora\Minimalism\Services\JsonDataMapper\Configurations\JsonDataMapperConfigurations;
use CarloNicora\Minimalism\Services\JsonDataMapper\Tests\Unit\Abstracts\AbstractTestCase;

class JsonDataMapperConfigurationsTest extends AbstractTestCase
{
    /**
     *
     */
    public function testUnconfiguredConfiguration() : void
    {
        $config = new JsonDataMapperConfigurations();

        $this->assertNotNull($config);
    }
}