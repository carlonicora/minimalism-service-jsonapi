<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\Cacher\Interfaces\CacheBuilderFactoryInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Abstracts\AbstractResourceBuilder;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\FunctionFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Factories\ResourceBuilderFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use Exception;

class ResourceBuildersPreLoader
{
    /**
     * @var ServicesFactory
     */
    private ServicesFactory $services;

    /**
     * ResourceBuildersPreLoader constructor.
     * @param ServicesFactory $services
     * @throws Exception
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
        AbstractResourceBuilder::initialise($this->services);
        FunctionFactory::initialise($this->services);
    }

    /**
     * @param string $buildersFolder
     * @param CacheBuilderFactoryInterface $cacheFactory
     * @throws Exception
     */
    public function preLoad(string $buildersFolder, CacheBuilderFactoryInterface $cacheFactory): void
    {
        $builderFactory = new ResourceBuilderFactory($this->services);
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
                if (strpos($line, 'namespace') === 0) {
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