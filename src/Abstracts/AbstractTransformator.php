<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Abstracts;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\TransformatorInterface;

abstract class AbstractTransformator implements TransformatorInterface
{
    /** @var ServicesFactory  */
    protected ServicesFactory $services;

    /**
     * AbstractTransformator constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services)
    {
        $this->services = $services;
    }

    /**
     * @param string $transformationFunction
     * @param array $data
     * @param string|null $fieldName
     * @return mixed|void
     */
    public function transform(string $transformationFunction, array $data, ?string $fieldName=null)
    {
        if (method_exists($this, $transformationFunction)) {
            return $this->$transformationFunction($data, $fieldName);
        }

        return $data[$fieldName] ?? null;
    }
}