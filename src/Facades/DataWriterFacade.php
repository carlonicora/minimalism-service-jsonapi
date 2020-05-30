<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Facades;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Interfaces\DataWriterInterface;

class DataWriterFacade implements DataWriterInterface
{
    /** @var ServicesFactory  */
    protected ServicesFactory $services;
}