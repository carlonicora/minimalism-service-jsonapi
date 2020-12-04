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
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Commands\ResourceReader;
use CarloNicora\Minimalism\Services\JsonDataMapper\Commands\ResourceWriter;
use CarloNicora\Minimalism\Services\JsonDataMapper\Configurations\JsonDataMapperConfigurations;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\LinkCreatorInterface;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use Exception;

class JsonDataMapper extends AbstractService
{
    /** @var JsonDataMapperConfigurations|ServiceConfigurationsInterface  */
    protected JsonDataMapperConfigurations $configData;

    /** @var EncrypterInterface|null  */
    private ?EncrypterInterface $defaultEncrypter=null;

    /** @var LinkCreatorInterface|null  */
    private ?LinkCreatorInterface $linkBuilder=null;

    /** @var CacheFacade  */
    private CacheFacade $cache;

    /** @var ResourceReader  */
    private ResourceReader $resourceReader;

    /** @var ResourceWriter  */
    private ResourceWriter $resourceWriter;

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

        $this->cache = new CacheFacade();
        $this->resourceReader = new ResourceReader($this->services);
        $this->resourceWriter = new ResourceWriter($this->services);
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
        FunctionFactory::initialise($services);
        AbstractResourceBuilder::initialise($services);
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
        return $this->resourceReader->generateResourceObjectByFieldValue(
            $builderName,
            $cache,
            $attribute,
            [$value],
            $loadRelationshipsLevel
        );
    }

    /**
     * @param string $builderName
     * @param CacheFactoryInterface|null $cache
     * @param FunctionFacade $function
     * @param array $parameters
     * @param int $loadRelationshipsLevel
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function generateResourceObjectsByFunction(
        string $builderName,
        ?CacheFactoryInterface $cache,
        FunctionFacade $function,
        array $parameters=[],
        int $loadRelationshipsLevel=0
    ) : array
    {
        return $this->resourceReader->generateResourceObjectsByFunction(
            $builderName,
            $cache,
            $function,
            $parameters,
            $loadRelationshipsLevel
        );
    }

    /**
     * @param string $builderName
     * @param CacheFactoryInterface|null $cache
     * @param array $dataList
     * @param int $loadRelationshipsLevel
     * @param array $externalParameters
     * @return array
     * @throws Exception
     */
    public function generateResourceObjectByData(
        string $builderName,
        ?CacheFactoryInterface $cache,
        array $dataList,
        int $loadRelationshipsLevel=0,
        array $externalParameters=[]
    ): array
    {
        return $this->resourceReader->generateResourceObjectByData(
            $builderName,
            $cache,
            $dataList,
            $loadRelationshipsLevel,
            $externalParameters
        );
    }

    /**
     * @param CacheFactoryInterface|null $cacheFactory
     * @param FunctionFacade $function
     * @param array $parameters
     * @param bool $iSingleRead
     * @return array
     * @throws DbRecordNotFoundException
     * @throws Exception
     */
    public function readData(
        ?CacheFactoryInterface $cacheFactory,
        FunctionFacade $function,
        array $parameters,
        bool $iSingleRead
    ): array
    {
        return $this->resourceReader->readResourceObjectData(
            $cacheFactory,
            $function,
            $parameters,
            $iSingleRead
        );
    }

    /**
     * @param Document $data
     * @param CacheFactoryInterface|null $cache
     * @param string $resourceBuilderName
     * @param bool $updateRelationships
     * @throws Exception
     */
    public function writeDocument(Document $data, ?CacheFactoryInterface $cache, string $resourceBuilderName, bool $updateRelationships=false) : void
    {
        $this->resourceWriter->writeDocument(
            $data,
            $cache,
            $resourceBuilderName,
            $updateRelationships
        );
    }

    /*
    public function writeData(
        ?CacheFactoryInterface $cacheFactory,
        FunctionFacade $function,
        array $parameters): array
    {
        return $this->resourceWriter->writeData();
    }
    */

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