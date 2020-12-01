<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Factories;

use CarloNicora\Minimalism\Core\Services\Abstracts\AbstractServiceFactory;
use CarloNicora\Minimalism\Core\Services\Exceptions\ConfigurationException;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Configurations\JsonDataMapperConfigurations;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;
use Exception;

class ServiceFactory extends AbstractServiceFactory
{
    /**
     * serviceFactory constructor.
     * @param ServicesFactory $services
     * @throws ConfigurationException
     */
    public function __construct(ServicesFactory $services)
    {
        $this->configData = new JsonDataMapperConfigurations();

        parent::__construct($services);
    }

    /**
     * @param servicesFactory $services
     * @return JsonDataMapper
     * @throws Exception
     */
    public function create(servicesFactory $services) : JsonDataMapper
    {
        return new JsonDataMapper($this->configData, $services);
    }
}