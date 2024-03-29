<?php
namespace CarloNicora\Minimalism\Services\JsonApi;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Interfaces\CacheBuilderFactoryInterface;
use CarloNicora\Minimalism\Interfaces\CacheBuilderInterface;
use CarloNicora\Minimalism\Interfaces\CacheInterface;
use CarloNicora\Minimalism\Interfaces\DataInterface;
use CarloNicora\Minimalism\Interfaces\DefaultServiceInterface;
use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\LoaderInterface;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\CacheFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\ResourceBuildersPreLoader;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Commands\ResourceReader;
use CarloNicora\Minimalism\Services\JsonApi\Commands\ResourceWriter;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\LinkCreatorInterface;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\TransformatorInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use CarloNicora\Minimalism\Services\Path;
use Exception;

class JsonApi implements ServiceInterface
{
    /** @var ResourceReader|null  */
    private ?ResourceReader $resourceReader;

    /** @var ResourceWriter|null  */
    private ?ResourceWriter $resourceWriter;

    /** @var ServicesProxy  */
    private ServicesProxy $servicesProxy;
    
    /** @var array  */
    private array $builders = [];

    /**
     * abstractApiCaller constructor.
     * @param DataInterface $dataProvider
     * @param CacheInterface|null $cacheProvider
     * @param EncrypterInterface|null $encrypter
     * @param Path $path
     */
    public function __construct(
        DataInterface $dataProvider,
        ?CacheInterface $cacheProvider,
        ?EncrypterInterface $encrypter,
        Path $path,
    ) {
        $this->servicesProxy = new ServicesProxy(
            dataProvider: $dataProvider,
            cacheProvider: $cacheProvider,
            encrypter: $encrypter,
            path: $path,
            cacheFacade: new CacheFacade()
        );
        
        $this->initialise();
    }

    /**
     * @param string $buildersFolder
     * @param CacheBuilderFactoryInterface|null $cacheFactory
     * @throws Exception
     */
    public function preLoadBuilders(
        string $buildersFolder,
        ?CacheBuilderFactoryInterface $cacheFactory=null,
    ): void
    {
        $resourceBuilderPreLoader = new ResourceBuildersPreLoader(
            servicesProxy: $this->servicesProxy
        );

        $this->builders = array_merge(
            $this->builders, 
            $resourceBuilderPreLoader->preLoad(
                buildersFolder:  $buildersFolder,
                cacheFactory: $cacheFactory,
            )
        );
    }

    /**
     * @return bool
     */
    public function areBuildersPreLoaded(): bool
    {
        return $this->builders !== [];
    }

    /**
     * @param ServiceInterface $service
     */
    public function addBuilderService(
        ServiceInterface $service
    ): void
    {
        $this->servicesProxy->addBuilderService($service);
    }

    /**
     * @param TransformatorInterface $transformator
     * @throws Exception
     */
    public function addBuilderTransformator(
        TransformatorInterface $transformator
    ): void
    {
        $this->servicesProxy->addBuilderTransformator($transformator);
    }

    /**
     * @param DefaultServiceInterface $service
     */
    public function setActiveService(
        DefaultServiceInterface $service
    ): void
    {
        $this->servicesProxy->setService($service);
    }

    /**
     * @param LoaderInterface $loader
     */
    public function setLoaderInterface(
        LoaderInterface $loader
    ): void
    {
        $this->servicesProxy->setLoaderInterface($loader);
    }

    /**
     * @param string $builderName
     * @param CacheBuilderInterface|null $cache
     * @param AttributeBuilderInterface $attribute
     * @param $value
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws Exception
     */
    public function generateResourceObjectByFieldValue(
        string $builderName,
        ?CacheBuilderInterface $cache,
        AttributeBuilderInterface $attribute,
        $value,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ) : array
    {
        return $this->resourceReader->generateResourceObjectByFieldValue(
            $builderName,
            $cache,
            $attribute,
            [$value],
            $loadRelationshipsLevel,
            $relationshipParameters,
            $positionInRelationship
        );
    }

    /**
     * @param string $builderName
     * @param CacheBuilderInterface|null $cache
     * @param FunctionFacade $function
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws Exception
     */
    public function generateResourceObjectsByFunction(
        string $builderName,
        ?CacheBuilderInterface $cache,
        FunctionFacade $function,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ) : array
    {
        return $this->resourceReader->generateResourceObjectsByFunction(
            $builderName,
            $cache,
            $function,
            $loadRelationshipsLevel,
            $relationshipParameters,
            $positionInRelationship
        );
    }

    /**
     * @param string $builderName
     * @param CacheBuilderInterface|null $cache
     * @param array $dataList
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws Exception
     */
    public function generateResourceObjectByData(
        string $builderName,
        ?CacheBuilderInterface $cache,
        array $dataList,
        int $loadRelationshipsLevel=0,
        array $relationshipParameters=[],
        array $positionInRelationship=[]
    ): array
    {
        return $this->resourceReader->generateResourceObjectByData(
            $builderName,
            $cache,
            $dataList,
            $loadRelationshipsLevel,
            $relationshipParameters,
            $positionInRelationship
        );
    }

    /**
     * @param FunctionFacade $function
     * @return array
     * @throws Exception
     */
    public function readData(
        FunctionFacade $function
    ): array
    {
        return $this->resourceReader->readResourceObjectData(
            $function
        );
    }

    /**
     * @param Document $data
     * @param CacheBuilderInterface|null $cache
     * @param string $resourceBuilderName
     * @param bool $updateRelationships
     * @throws Exception
     */
    public function writeDocument(
        Document $data,
        ?CacheBuilderInterface $cache,
        string $resourceBuilderName,
        bool $updateRelationships=false
    ) : void
    {
        $this->resourceWriter->writeDocument(
            $data,
            $cache,
            $resourceBuilderName,
            $updateRelationships
        );
    }

    /**
     * @param LinkCreatorInterface|null $linkBuilder
     */
    public function setLinkBuilder(
        ?LinkCreatorInterface $linkBuilder
    ): void
    {
        $this->servicesProxy->setLinkBuilder($linkBuilder);
    }

    /**
     *
     */
    public function initialise(): void
    {
        FunctionFactory::initialise($this->servicesProxy);
        AbstractResourceBuilder::initialise($this->servicesProxy);

        $this->resourceReader = new ResourceReader(
            servicesProxy: $this->servicesProxy,
        );
        $this->resourceWriter = new ResourceWriter(
            servicesProxy: $this->servicesProxy,
        );
    }

    /**
     *
     */
    public function destroy(): void {}
}