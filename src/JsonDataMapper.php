<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper;

use CarloNicora\Minimalism\Core\Services\Abstracts\AbstractService;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Core\Services\Interfaces\ServiceConfigurationsInterface;
use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Configurations\JsonDataMapperConfigurations;
use CarloNicora\Minimalism\Services\JsonDataMapper\Factories\DataWrapperFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Factories\DocumentFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\LinkBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Objects\EntityDocument;
use CarloNicora\Minimalism\Services\JsonDataMapper\Wrappers\DataWrapper;
use Exception;

/**
 * Class JsonDataMapper
 * @package CarloNicora\Minimalism\Services\JsonDataMapper
 *
 * TODO write
 *
 */
class JsonDataMapper extends AbstractService
{
    /** @var JsonDataMapperConfigurations|ServiceConfigurationsInterface  */
    protected JsonDataMapperConfigurations $configData;

    /** @var EncrypterInterface|null  */
    private ?EncrypterInterface $defaultEncrypter=null;

    /** @var LinkBuilderInterface|null  */
    private ?LinkBuilderInterface $linkBuilder=null;

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
     * @param EntityDocument $document
     * @param DataWrapper $wrapper
     * @return array
     * @throws Exception
     */
    private function read(EntityDocument $document, DataWrapper $wrapper) : array
    {
        $documentFactory = new DocumentFactory($this->services);
        $data = $wrapper->loadData();

        return $documentFactory->build($document, $data);
    }

    /**
     * @param string $entityName
     * @return DataWrapperFactory
     * @throws Exception
     */
    public function generateDataWrapperFactory(string $entityName) : DataWrapperFactory
    {
        return new DataWrapperFactory($this->services, $entityName);
    }

    /**
     * @param string $entityName
     * @param string $fieldName
     * @param $fieldValue
     * @return array
     * @throws Exception
     */
    public function readSimple(string $entityName, string $fieldName, $fieldValue) : array
    {
        $wrapperFactory = $this->generateDataWrapperFactory($entityName);
        $entityDocument = $wrapperFactory->getEntityDocument();
        $wrapper = $wrapperFactory->generateSimpleLoader($fieldName, $fieldValue);

        return $this->read($entityDocument, $wrapper);
    }

    /**
     * @param string $entityName
     * @param string $tableName
     * @param string $customFunction
     * @param array $parameters
     * @return array
     * @throws Exception
     */
    public function readCustom(string $entityName, string $tableName, string $customFunction, array $parameters=[]) : array
    {
        $wrapperFactory = $this->generateDataWrapperFactory($entityName);
        $entityDocument = $wrapperFactory->getEntityDocument();
        $wrapper = $wrapperFactory->generateCustomLoader($tableName, $customFunction, $parameters);

        return $this->read($entityDocument, $wrapper);
    }

    /**
     * @param EncrypterInterface|null $defaultEncrypter
     */
    public function setDefaultEncrypter(?EncrypterInterface $defaultEncrypter): void
    {
        $this->defaultEncrypter = $defaultEncrypter;
    }

    /**
     * @return EncrypterInterface|null
     */
    public function getDefaultEncrypter(): ?EncrypterInterface
    {
        return $this->defaultEncrypter;
    }

    /**
     * @return LinkBuilderInterface|null
     */
    public function getLinkBuilder(): ?LinkBuilderInterface
    {
        return $this->linkBuilder;
    }

    /**
     * @param LinkBuilderInterface|null $linkBuilder
     */
    public function setLinkBuilder(?LinkBuilderInterface $linkBuilder): void
    {
        $this->linkBuilder = $linkBuilder;
    }
}