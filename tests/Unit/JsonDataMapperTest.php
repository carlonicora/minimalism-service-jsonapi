<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Tests\Unit;

use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use CarloNicora\Minimalism\Services\JsonDataMapper\Tests\Unit\Abstracts\AbstractTestCase;
use Exception;

class JsonDataMapperTest extends AbstractTestCase
{
    /**
     * @return JsonDataMapper
     * @throws Exception
     */
    public function testInitialise() : JsonDataMapper
    {
        $response = new JsonDataMapper(
            $this->getConfigData(),
            $this->getServices()
        );

        self::assertInstanceOf(JsonDataMapper::class, $response);

        return $response;
    }
}