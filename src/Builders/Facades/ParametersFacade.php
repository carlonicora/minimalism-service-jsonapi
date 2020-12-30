<?php
namespace CarloNicora\Minimalism\Services\JsonApi\Builders\Facades;

class ParametersFacade
{
    /**
     * @param array $parameters
     * @param array $positionInRelationship
     * @param bool $limitToDataField
     * @return array
     */
    public static function prepareParameters(
        array $parameters,
        array $positionInRelationship,
        bool $limitToDataField=false
    ): array
    {
        if ($positionInRelationship === []){
            $selectedParameters = [];
            foreach($parameters as $parameterKey=>$parameter){
                $selectedParameters[] = $parameter;
            }

            return self::prepareResponse($selectedParameters, $limitToDataField);
        }

        $baseParameters = [];

        foreach ($parameters as $parameterKey=>$parameterValue){
            if (!is_array($parameterValue) && !strpos($parameterKey, '/')){
                $baseParameters[$parameterKey] = $parameterValue;
            }
        }

        $selectedParameters = $parameters;

        while ($positionInRelationship !== []){
            $key = array_shift($positionInRelationship);
            if (array_key_exists($key, $selectedParameters)){
                $selectedParameters = $selectedParameters[$key];
            } else {
                $selectedParameters = [];
            }
        }

        $selectedParameters = array_merge($baseParameters, $selectedParameters);

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