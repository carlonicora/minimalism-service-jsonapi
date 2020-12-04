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
        if ($position === []){
            $selectedParameters = [];
            foreach($parameters as $parameterKey=>$parameter){
                $selectedParameters[] = $parameter;
            }

            return self::prepareResponse($selectedParameters, $limitToDataField);
        }

        $selectedParameters = $parameters;

        while ($position !== []){
            $key = array_shift($position);
            if (array_key_exists($key, $selectedParameters)){
                $selectedParameters = $selectedParameters[$key];
            }
        }

        return self::prepareResponse($selectedParameters, $limitToDataField);
    }

    /**
     * @param array $selectedParameters
     * @param bool $limitToDataField
     * @return array
     */
    private static function prepareResponse(array $selectedParameters, bool $limitToDataField): array
    {
        $response = [];
        foreach($selectedParameters as $parameterKey=>$parameter){
            if (!$limitToDataField || (!is_array($parameterKey) && !strpos($parameterKey, '/'))){
                $response[] = $parameter;
            }
        }

        return $response;
    }
}