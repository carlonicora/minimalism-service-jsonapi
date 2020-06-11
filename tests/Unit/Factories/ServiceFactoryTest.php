<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Tests\Unit\Factories;

use CarloNicora\Minimalism\Services\JsonDataMapper\Configurations\JsonDataMapperConfigurations;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use CarloNicora\Minimalism\Services\JsonDataMapper\Factories\ServiceFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Tests\Unit\Abstracts\AbstractTestCase;
use Exception;

class ServiceFactoryTest extends AbstractTestCase
{
    /**
     * @return ServiceFactory
     */
    public function testServiceInitialisation() : ServiceFactory
    {
        $response = new ServiceFactory($this->getServices());

        $this->assertInstanceOf(ServiceFactory::class, $response);

        return $response;
    }

    /**
     * @param ServiceFactory $service
     * @depends testServiceInitialisation
     * @throws Exception
     */
    public function testServiceCreation(ServiceFactory $service) : void
    {
        $config = new JsonDataMapperConfigurations();
        $services = $this->getServices();
        $jsondatamapper = new JsonDataMapper($config, $services);

        $this->assertEquals($jsondatamapper, $service->create($services));
    }
}