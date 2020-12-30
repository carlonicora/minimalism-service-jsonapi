<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Traits;

use CarloNicora\JsonApi\Objects\ResourceObject;
use CarloNicora\Minimalism\Services\JsonApi\Builders\Interfaces\ResourceBuilderInterface;
use CarloNicora\Minimalism\Services\JsonApi\Proxies\ServicesProxy;
use Exception;

trait LinkCreatorTrait
{
    /** @var ServicesProxy  */
    protected ServicesProxy $servicesProxy;

    /**
     * @param string $url
     * @param ResourceBuilderInterface $resource
     * @param array $data
     * @param ResourceObject|null $resourceObject
     * @return string
     */
    public function buildLink(string $url, ResourceBuilderInterface $resource, array $data, ResourceObject $resourceObject=null) : string
    {
        if ($url[0] !== '%'){
            $url = $this->servicesProxy->getPath()->getUrl() . $url;
        }

        $linkElements = explode('%', $url);

        for ($linkElementsCounter = 1, $linkElementsCounterMax = count($linkElements); $linkElementsCounter < $linkElementsCounterMax; $linkElementsCounter += 2) {
            if (($attribute = $resource->getAttribute($linkElements[$linkElementsCounter])) !== null) {

                try {
                    $value = '';
                    if (array_key_exists($attribute->getDatabaseFieldName(), $data) && $data[$attribute->getDatabaseFieldName()] !== null){
                        $value = $data[$attribute->getDatabaseFieldName()];
                    } elseif (array_key_exists($attribute->getDatabaseFieldRelationship(), $data) && $data[$attribute->getDatabaseFieldRelationship()] !== null){
                        $value = $data[$attribute->getDatabaseFieldRelationship()];
                    } elseif ($resourceObject !== null) {
                        $value = $resourceObject->attributes->get($attribute->getDatabaseFieldName());
                    }
                } catch (Exception) {
                    $value = '';
                }

                if (is_int($value) && $attribute->isEncrypted() && $encrypter = $this->servicesProxy->getEncrypter() !== null){
                    $value = $this->servicesProxy->getEncrypter()->encryptId($value);
                }

                $linkElements[$linkElementsCounter] = $value;
            } elseif (array_key_exists($linkElements[$linkElementsCounter], $data)){
                try {
                    if ($this->servicesProxy->getEncrypter() !== null) {
                        $linkElements[$linkElementsCounter] = $this->servicesProxy->getEncrypter()->encryptId($data[$linkElements[$linkElementsCounter]]);
                    }
                } catch (Exception) {
                    $linkElements[$linkElementsCounter] = $data[$linkElements[$linkElementsCounter]];
                }
                if ([$linkElementsCounter] === '') {
                    $linkElements[$linkElementsCounter] = $data[$linkElements[$linkElementsCounter]];
                }
            } else {
                $linkElements[$linkElementsCounter] = '%'.$linkElements[$linkElementsCounter].'%';
            }
        }

        return implode('', $linkElements);
    }
}