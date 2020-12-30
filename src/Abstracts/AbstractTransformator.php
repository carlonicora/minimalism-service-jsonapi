<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Abstracts;

use CarloNicora\Minimalism\Services\JsonApi\Interfaces\TransformatorInterface;

abstract class AbstractTransformator implements TransformatorInterface
{
    /**
     * @param string $transformationFunction
     * @param array $data
     * @param string|null $fieldName
     * @return mixed
     */
    public function transform(string $transformationFunction, array $data, ?string $fieldName=null): mixed
    {
        if (method_exists($this, $transformationFunction)) {
            return $this->$transformationFunction($data, $fieldName);
        }

        return $data[$fieldName] ?? null;
    }
}