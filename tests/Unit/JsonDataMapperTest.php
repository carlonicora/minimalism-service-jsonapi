<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Tests\Unit;

use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use CarloNicora\Minimalism\Services\JsonDataMapper\Tests\Unit\Abstracts\AbstractTestCase;

class JsonDataMapperTest extends AbstractTestCase
{
    /**
     * @return JsonDataMapper
     */
    public function testInitialise() : JsonDataMapper
    {
        $response = new JsonDataMapper(
            $this->getConfigData(),
            $this->getServices()
        );

        $this->assertInstanceOf(JsonDataMapper::class, $response);

        return $response;
    }
}