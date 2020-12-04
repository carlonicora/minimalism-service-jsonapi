<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Facades;

class ParametersFacade
{
    /**
     * @param array $parameters
     * @param array $position
     * @param bool $limitToDataField
     * @return array
     */
    public static function prepareParameters(
        array $parameters,
        array $position,
        bool $limitToDataField=false
    ): array
    {
        $response = [];

        if ($position === []){
            foreach($parameters as $parameterKey=>$parameter){
                if (!is_string($parameterKey)){
                    $response[] = $parameter;
                }
            }

            return $response;
        }

        $selectedParameters = $parameters;

        while ($position !== []){
            $key = array_shift($position);
            if (array_key_exists($key, $selectedParameters)){
                $selectedParameters = $selectedParameters[$key];
            }
        }

        foreach($selectedParameters as $parameterKey=>$parameter){
            if (!$limitToDataField || strpos($parameterKey, '/')){
                $response[] = $parameter;
            }
        }

        return $response;
    }
}