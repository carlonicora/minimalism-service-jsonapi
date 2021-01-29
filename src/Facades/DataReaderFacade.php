<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Facades;

use CarloNicora\Minimalism\Interfaces\CacheBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Facades\FunctionFacade;
use CarloNicora\Minimalism\Services\JsonApi\Interfaces\DataReaderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use Exception;

class DataReaderFacade implements DataReaderInterface
{
    /** @var FunctionFacade  */
    public FunctionFacade $function;

    /** @var array  */
    public array $functionParameters;

    /**
     * DataReaderFacade constructor.
     * @param ServicesProxy $servicesProxy
     * @param FunctionFacade $function
     * @param array $functionParameters
     */
    public function __construct(
        private ServicesProxy $servicesProxy,
        FunctionFacade $function,
        array $functionParameters = []
    ) {
        $this->function = $function;
        $this->functionParameters = $functionParameters;
    }

    /**
     * @return array|null
     * @throws Exception
     */
    public function getList() : ?array
    {
        if ($this->function->getCacheBuilder() !== null && $this->servicesProxy->useCache()) {
            $response = $this->servicesProxy->getCacheProvider()
                ? $this->servicesProxy->getCacheProvider()->readArray($this->function->getCacheBuilder(), CacheBuilderInterface::DATA)
                : null;
            if ($response === null) {
                $response = call_user_func($this->function->getFunction(), ...$this->functionParameters);

                if ($response !== null && $response !== []) {
                    $this->function->getCacheBuilder()->setType(CacheBuilderInterface::DATA);
                    $this->servicesProxy->getCacheProvider()?->saveArray($this->function->getCacheBuilder(), $response, CacheBuilderInterface::DATA);
                }
            }
        } else {
            $response = call_user_func($this->function->getFunction(), ...$this->functionParameters);
        }

        return $response;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getCount() : int
    {
        return (int)call_user_func($this->function->getFunction(), ...$this->functionParameters);
    }
}