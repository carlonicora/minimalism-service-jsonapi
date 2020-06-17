<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Traits;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;
use CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonDataMapper\JsonDataMapper;

trait LinkCreatorTrait
{
    /** @var ServicesFactory  */
    protected ServicesFactory $services;

    /** @var JsonDataMapper */
    private JsonDataMapper $mapper;

    /**
     * @param string $url
     * @param ResourceBuilderInterface $resource
     * @param array $data
     * @return string
     */
    public function buildLink(string $url, ResourceBuilderInterface $resource, array $data) : string
    {
        if ($url[0] !== '%'){
            $url = $this->services->paths()->getUrl() . $url;
        }

        $linkElements = explode('%', $url);

        for ($linkElementsCounter = 1, $linkElementsCounterMax = count($linkElements); $linkElementsCounter < $linkElementsCounterMax; $linkElementsCounter += 2) {
            if (($attribute = $resource->getAttribute($linkElements[$linkElementsCounter])) !== null) {

                $value = $data[$attribute->getDatabaseFieldName()];

                if ($attribute->isEncrypted() && ($encrypter = $this->mapper->getDefaultEncrypter()) !== null && is_int($value)){
                    $value = $encrypter->encryptId($value);
                }

                $linkElements[$linkElementsCounter] = $value;
            } else {
                $linkElements[$linkElementsCounter] = '%'.$linkElements[$linkElementsCounter].'%';
            }
        }

        return implode('', $linkElements);
    }
}