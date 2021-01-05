<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

use CarloNicora\Minimalism\Interfaces\CacheBuilderFactoryInterface;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use Exception;

class ResourceBuildersPreLoader
{
    /**
     * ResourceBuildersPreLoader constructor.
     * @param ServicesProxy $servicesProxy
     */
    public function __construct(
        private ServicesProxy $servicesProxy,
    )
    {
        AbstractResourceBuilder::initialise($this->servicesProxy);
        FunctionFactory::initialise($this->servicesProxy);
    }

    /**
     * @param string $buildersFolder
     * @param CacheBuilderFactoryInterface $cacheFactory
     * @throws Exception
     */
    public function preLoad(
        string $buildersFolder,
        CacheBuilderFactoryInterface $cacheFactory
    ): void
    {
        $builderFactory = new ResourceBuilderFactory(
            servicesProxy: $this->servicesProxy
        );
        $files = scandir($buildersFolder);

        $builders = [];

        foreach ($files as $file){
            if (!is_dir($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php'){
                $fullPath = $buildersFolder . DIRECTORY_SEPARATOR . $file;
                $namespace = $this->extract_namespace($fullPath);
                $className = $namespace . '\\' . $this->getClassname($fullPath);

                $resourceBuilder = $builderFactory->createResourceBuilder($className);
                if ($cacheFactory !== null) {
                    $resourceBuilder->setCacheFactoryInterface($cacheFactory);
                }
                $builders[] = $resourceBuilder;
            }
        }

        /** @var ResourceBuilderInterface $builder */
        foreach ($builders as $builder){
            $builder->initialiseRelationships();
        }
    }

    /**
     * @param $file
     * @return string
     */
    private function extract_namespace($file): string
    {
        $ns = NULL;
        $handle = fopen($file, 'rb');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (str_starts_with($line, 'namespace')) {
                    $parts = explode(' ', $line);
                    $ns = rtrim(trim($parts[1]), ';');
                    break;
                }
            }
            fclose($handle);
        }
        return $ns;
    }

    /**
     * @param $filename
     * @return string
     */
    private function getClassname($filename): string
    {
        $directoriesAndFilename = explode('/', $filename);
        $filename = array_pop($directoriesAndFilename);
        $nameAndExtension = explode('.', $filename);
        return array_shift($nameAndExtension);
    }
}