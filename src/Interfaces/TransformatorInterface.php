<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Interfaces;

interface TransformatorInterface
{
    /**
     * TransformationsInterface constructor.
     */
    public function __construct();

    /**
     * @param string $transformationFunction
     * @param array $data
     * @param string|null $fieldName
     * @return mixed
     */
    public function transform(string $transformationFunction, array $data, ?string $fieldName): mixed;
}