<?php
namespace CarloNicora\Minimalism\Services\JsonApi;

use CarloNicora\JsonApi\Document;
use CarloNicora\Minimalism\Interfaces\EncrypterInterface;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use CarloNicora\Minimalism\Services\Cacher\Builders\CacheBuilder;
use CarloNicora\Minimalism\Services\Cacher\Cacher;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\CacheFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\AttributeBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Commands\ResourceReader;
use CarloNicora\Minimalism\Services\JsonApi\Commands\ResourceWriter;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\LinkCreatorInterface;
use CarloNicora\Minimalism\Services\MySQL\Exceptions\DbRecordNotFoundException;
use CarloNicora\Minimalism\Services\MySQL\MySQL;
use CarloNicora\Minimalism\Services\Redis\Redis;
use Exception;

class JsonApi implements ServiceInterface
{
    /** @var EncrypterInterface|null  */
    private ?EncrypterInterface $defaultEncrypter=null;

    /** @var LinkCreatorInterface|null  */
    private ?LinkCreatorInterface $linkBuilder=null;

    /** @var ResourceReader|null  */
    private ?ResourceReader $resourceReader=null;

    /** @var CacheFacade  */
    private CacheFacade $cache;

    /** @var ResourceWriter|null  */
    private ?ResourceWriter $resourceWriter=null;

    /**
     * abstractApiCaller constructor.
     * @param MySQL $mysql
     * @param Cacher $cacher
     * @param Redis $redis
     */
    public function __construct(
        private MySQL $mysql,
        private Cacher $cacher,
        private Redis $redis,
    ) {
        $this->cache = new CacheFacade();
    }

    /**
     * @return CacheFacade
     */
    public function getCache(): CacheFacade
    {
        return $this->cache;
    }

    /**
     * @param string $builderName
     * @param CacheBuilder|null $cache
     * @param AttributeBuilderInterface $attribute
     * @param $value
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws DbRecordNotFoundException
     */
    public function generateResourceObjectByFieldValue(
        string $builderName,
        ?CacheBuilder $cache,
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
     * @param CacheBuilder|null $cache
     * @param FunctionFacade $function
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws DbRecordNotFoundException
     */
    public function generateResourceObjectsByFunction(
        string $builderName,
        ?CacheBuilder $cache,
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
     * @param CacheBuilder|null $cache
     * @param array $dataList
     * @param int $loadRelationshipsLevel
     * @param array $relationshipParameters
     * @param array $positionInRelationship
     * @return array
     * @throws Exception
     */
    public function generateResourceObjectByData(
        string $builderName,
        ?CacheBuilder $cache,
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
     * @throws DbRecordNotFoundException
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
     * @param CacheBuilder|null $cache
     * @param string $resourceBuilderName
     * @param bool $updateRelationships
     * @throws Exception
     */
    public function writeDocument(Document $data, ?CacheBuilder $cache, string $resourceBuilderName, bool $updateRelationships=false) : void
    {
        $this->resourceWriter->writeDocument(
            $data,
            $cache,
            $resourceBuilderName,
            $updateRelationships
        );
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

    public function initialise(): void
    {
        FunctionFactory::initialise($this->mysql);
        AbstractResourceBuilder::initialise($this);

        $this->resourceReader = new ResourceReader($this, $this->cacher, $this->redis, $this->mysql);
        $this->resourceWriter = new ResourceWriter($this, $this->cacher, $this->redis, $this->mysql);
    }

    public function destroy(): void
    {
        // TODO: Implement destroy() method.
    }
}