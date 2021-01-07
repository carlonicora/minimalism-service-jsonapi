<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Proxies;

use CarloNicora\Minimalism\Interfaces\CacheInterface;
use CarloNicora\Minimalism\Interfaces\DataInterface;
use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\CacheFacade;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\LinkCreatorInterface;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\TransformatorInterface;
use CarloNicora\Minimalism\Services\Path;
use Exception;
use ReflectionClass;
use RuntimeException;

class ServicesProxy
{
    /** @var LinkCreatorInterface|null  */
    private ?LinkCreatorInterface $linkBuilder=null;

    /** @var ServiceInterface|null  */
    private ?ServiceInterface $service=null;

    /** @var array  */
    private array $builderTransformators=[];

    /**
     * ServicesProxy constructor.
     * @param DataInterface $dataProvider
     * @param CacheInterface|null $cacheProvider
     * @param EncrypterInterface|null $encrypter
     * @param Path $path
     * @param CacheFacade $cacheFacade
     */
    public function __construct(
        private DataInterface $dataProvider,
        private ?CacheInterface $cacheProvider,
        private ?EncrypterInterface $encrypter,
        private Path $path,
        private CacheFacade $cacheFacade,
    ) {}

    /**
     * @return bool
     */
    public function useCache(): bool
    {
        if ($this->cacheProvider !== null) {
            return $this->cacheProvider->useCaching();
        }

        return false;
    }

    /**
     * @return EncrypterInterface|null
     */
    public function getEncrypter(): ?EncrypterInterface
    {
        return $this->encrypter;
    }

    /**
     * @return CacheFacade
     */
    public function getCacheFacade(): CacheFacade
    {
        return $this->cacheFacade;
    }

    /**
     * @return CacheInterface|null
     */
    public function getCacheProvider(): ?CacheInterface
    {
        return $this->cacheProvider;
    }

    /**
     * @return DataInterface
     */
    public function getDataProvider(): DataInterface
    {
        return $this->dataProvider;
    }

    /**
     * @return Path
     */
    public function getPath(): Path
    {
        return $this->path;
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

    /**
     * @param TransformatorInterface $transformator
     * @throws Exception
     */
    public function addBuilderTransformator(TransformatorInterface $transformator): void
    {
        $class = new ReflectionClass($transformator);
        $this->builderTransformators[$class->getName()] = $transformator;
    }

    /**
     * @param string $transformatorClass
     * @return TransformatorInterface
     * @throws Exception
     */
    public function getBuilderTransformator(string $transformatorClass): TransformatorInterface
    {
        if (!array_key_exists($transformatorClass, $this->builderTransformators)){
            throw new RuntimeException('Builder transformator missing', 500);
        }

        return $this->builderTransformators[$transformatorClass];
    }

    /**
     * @return ServiceInterface|null
     */
    public function getService(): ?ServiceInterface
    {
        return $this->service;
    }

    /**
     * @param ServiceInterface $service
     */
    public function setService(ServiceInterface $service): void
    {
        $this->service = $service;
    }
}