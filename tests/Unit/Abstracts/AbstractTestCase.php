<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Tests\Unit\Abstracts;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Core\Services\Interfaces\ServiceConfigurationsInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Configurations\JsonDataMapperConfigurations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class AbstractTestCase extends TestCase
{
    /**
    * @return ServicesFactory
    */
    protected function getServices() : ServicesFactory
    {
        $response = new ServicesFactory();
        $this->setProperty($response->paths(), 'root', '/opt/project');

        return $response;
    }

    /**
     * @return ServiceConfigurationsInterface|MockObject|JsonDataMapperConfigurations
     */
    protected function getConfigData() : ServiceConfigurationsInterface
    {
        /** @var MockObject|ServiceConfigurationsInterface $response */
        $response = $this->getMockBuilder(JsonDataMapperConfigurations::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $response;
    }

    /**
     * @param string $name
     * @param string $value
     */
    protected function setEnv(string $name, string $value) : void
    {
        putenv($name.'='.$value);
    }

    /**
     * @param $object
     * @param $parameterName
     * @return mixed|null
     */
    protected function getProperty($object, $parameterName)
    {
        try {
            $reflection = new ReflectionClass(get_class($object));
            $property = $reflection->getProperty($parameterName);
            $property->setAccessible(true);
            return $property->getValue($object);
        } catch (ReflectionException $e) {
            return null;
        }
    }

    /**
     * @param $object
     * @param $parameterName
     * @param $parameterValue
     */
    protected function setProperty($object, $parameterName, $parameterValue): void
    {
        try {
            $reflection = new ReflectionClass(get_class($object));
            $property = $reflection->getProperty($parameterName);
            $property->setAccessible(true);
            $property->setValue($object, $parameterValue);
        } catch (ReflectionException $e) {
        }
    }
}