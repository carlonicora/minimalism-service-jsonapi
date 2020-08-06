<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Core\Services\Abstracts\AbstractService;
use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Core\Services\Interfaces\ServiceConfigurationsInterface;
use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheFactoryInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\CacheFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Commands\ResourceReader;
use CarloNicora\Minimalism\Services\JsonDataMapper\Commands\ResourceWriter;
use CarloNicora\Minimalism\Services\JsonDataMapper\Configurations\JsonDataMapperConfigurations;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\LinkCreatorInterface;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbSqlException;
use Exception;

class JsonDataMapper extends AbstractService
{
    /** @var JsonDataMapperConfigurations|ServiceConfigurationsInterface  */
    protected JsonDataMapperConfigurations $configData;

    /** @var EncrypterInterface|null  */
    private ?EncrypterInterface $defaultEncrypter=null;

    /** @var LinkCreatorInterface|null  */
    private ?LinkCreatorInterface $linkBuilder=null;

    /** @var CacheFacade|null  */
    private ?CacheFacade $cache=null;

    /**
     * abstractApiCaller constructor.
     * @param ServiceConfigurationsInterface $configData
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServiceConfigurationsInterface $configData, ServicesFactory $services) {
        parent::__construct($configData, $services);

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->configData = $configData;
    }

    /**
     * @return CacheFacade
     */
    public function getCache(): CacheFacade
    {
        return $this->cache;
    }

    /**
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function initialiseStatics(ServicesFactory $services): void
    {
        parent::initialiseStatics($services);
        AbstractResourceBuilder::initialise($services);
        $this->cache = new CacheFacade($services);
    }

    /**
     *
     */
    public function destroyStatics(): void
    {
        $this->cache = null;
    }

    /**
     * @param string $builderName
     * @param CacheFactoryInterface|null $cache
     * @param AttributeBuilderInterface $attribute
     * @param $value
     * @param int $loadRelationshipsLevel
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function generateResourceObjectByFieldValue(string $builderName, ?CacheFactoryInterface $cache, AttributeBuilderInterface $attribute, $value, int $loadRelationshipsLevel=0) : array
    {
        $resourceReader = new ResourceReader($this->services);
        return $resourceReader->generateResourceObjectByFieldValue($builderName, $cache, $attribute, $value, $loadRelationshipsLevel);
    }

    /**
     * @param string $builderName
     * @param CacheFactoryInterface|null $cache
     * @param string $functionName
     * @param array $parameters
     * @param int $loadRelationshipsLevel
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function generateResourceObjectsByFunction(string $builderName, ?CacheFactoryInterface $cache, string $functionName, array $parameters=[], int $loadRelationshipsLevel=0) : array
    {
        $resourceReader = new ResourceReader($this->services);
        return $resourceReader->generateResourceObjectsByFunction($builderName, $cache, $functionName, $parameters, $loadRelationshipsLevel);
    }

    /**
     * @param string $builderName
     * @param array $dataList
     * @param int $loadRelationshipsLevel
     * @return array
     * @throws Exception
     */
    public function generateResourceObjectByData(string $builderName, array $dataList, int $loadRelationshipsLevel=0): array
    {
        $resourceReader = new ResourceReader($this->services);
        return $resourceReader->generateResourceObjectByData($builderName, $dataList, $loadRelationshipsLevel);
    }

    /**
     * @param Document $data
     * @param CacheFactoryInterface|null $cache
     * @param string $resourceBuilderName
     * @throws DbSqlException
     * @throws Exception
     */
    public function writeDocument(Document $data, ?CacheFactoryInterface $cache, string $resourceBuilderName) : void
    {
        $resourceWriter = new ResourceWriter($this->services);
        $resourceWriter->writeDocument($data, $cache, $resourceBuilderName);
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
     * @return LinkCreatorInterface|null
     */
    public function getLinkBuilder(): ?LinkCreatorInterface
    {
        return $this->linkBuilder;
    }

    /**
     * @param LinkCreatorInterface|null $linkBuilder
     */
    public function setLinkBuilder(?LinkCreatorInterface $linkBuilder): void
    {
        $this->linkBuilder = $linkBuilder;
    }
}