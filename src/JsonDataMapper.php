<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Core\Services\Abstracts\AbstractService;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Core\Services\Interfaces\ServiceConfigurationsInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Configurations\JsonDataMapperConfigurations;
use CarloNicora\Minimalism\Services\JsonDataMapper\Wrappers\DataWrapper;

class JsonDataMapper extends AbstractService
{
    /** @var JsonDataMapperConfigurations|ServiceConfigurationsInterface  */
    protected JsonDataMapperConfigurations $configData;

    /**
     * abstractApiCaller constructor.
     * @param ServiceConfigurationsInterface $configData
     * @param ServicesFactory $services
     */
    public function __construct(ServiceConfigurationsInterface $configData, ServicesFactory $services) {
        parent::__construct($configData, $services);

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->configData = $configData;
    }

    /**
     * @param string $entityName
     * @param DataWrapper $parameter
     * @param array|null $include
     * @param array|null $fields
     * @return Document
     */
    public function build(string $entityName, DataWrapper $parameter, array $include=null, array $fields=null) : Document
    {
        return new Document();
    }
}