<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;

interface TransformatorInterface
{
    /**
     * TransformationsInterface constructor.
     * @param ServicesFactory $services
     */
    public function __construct(ServicesFactory $services);

    /**
     * @param string $transformationFunction
     * @param array $data
     * @param string|null $fieldName
     * @return mixed
     */
    public function transform(string $transformationFunction, array $data, ?string $fieldName);
}