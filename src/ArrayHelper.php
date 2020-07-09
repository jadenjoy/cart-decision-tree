<?php

namespace Darvin\CART;

/**
 * Class ArrayHelper
 * @package Darvin\CART
 */
class ArrayHelper
{

    /**
     * Возвращает список значений для ключа в датасете
     * @param array $data
     * @param string $predictionKey
     * @return array
     */
    public static function makeFeatArray(array $data, string $predictionKey)
    {
        $base_array = [];
        $feat_array = [];
        $result = [];



        foreach ($data as $key => $value) {
            $base_array[$key] = $value[$predictionKey];
        }

        $feat_array = array_unique($base_array);

        $i = 0;
        foreach ($feat_array as $key => $value) {
            $result[$i] = $value;
            $i++;
        }

        return $result;
    }


    /**
     * Разделяет массив данных по значению ключа $predictionKey
     * @param array $rawData
     * @param string $predictionKey
     * @return array
     */
    public static function splitByPredictionKey(array $rawData, string $predictionKey)
    {
        $splitArray = array();
        $featArray = ArrayHelper::makeFeatArray($rawData, $predictionKey);


        foreach ($featArray as $value) {
            $i = 0;
            $newArray = array();

            if (is_array($rawData) && count($rawData) > 0) {
                foreach (array_keys($rawData) as $key2) {
                    $temp[$i] = $rawData[$key2][$predictionKey];
                    if ($temp[$i] == $value) {
                        $newArray[$i] = $rawData[$key2];
                        $i++;
                    }
                }
            }
            $splitArray[$value] = $newArray;
        }

        return $splitArray;
    }



}