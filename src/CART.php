<?php
namespace Darvin\CART;

/**
 * Class ArrayHelper
 * @package Darvin\CART
 */
class CART
{


    /**
     * @param array $data
     * @param $baseKey
     * @param $predictionKey
     * @return float|int|number
     */
    public static function calculateDeltaI(array $data, $baseKey, $predictionKey)
    {
        $splitArray = array();
        $giniArray  = array();

        // Разделяем массив
        $splitArray = ArrayHelper::splitByPredictionKey($data, $predictionKey);

        // Прохоимся по элементам с обоих частей массива вычисляя
        // Коэффициент Джини — статистический показатель степени расслоения
        foreach ($splitArray as $key => $value) {
            $giniArray[$key] = self::calculateGiniIndex($value, $baseKey);

        }
        $giniRoot = self::calculateGiniIndex($data, $baseKey);
        $deltaI = $giniRoot;


        foreach ($splitArray as $key => $value) {
            $odd = self::calculateProbability($data, $predictionKey, $key);
            $gini = $giniArray[$key];
            $deltaI -= $odd * $gini;
        }

        return $deltaI;
    }


    /**
     * Вычисляет в массиве показатель степени расслоения
     * @param array $data
     * @param string $baseKey
     * @return int|number
     */
    public static function calculateGiniIndex(array $data, string $baseKey)
    {
        // Вероятности
        $odds = array();

        $featArray = ArrayHelper::makeFeatArray($data, $baseKey);

        foreach ($featArray as $key => $value) {
            // Рассчитываем вероятность
            $odd = self::calculateProbability($data, $baseKey, $value);
            array_push($odds, $odd);
        }

        // Рассчитываем коэфициент Джини
        $gini = 1;
        foreach ($odds as $key => $value) {
            $gini -= pow($value, 2);
        }
        return $gini;
    }


    /**
     * Функция расчета вероятности
     * @param array $data
     * @param string $baseKey
     * @param $value
     * @return float|int
     */
    public static function calculateProbability(array $data, string $baseKey, $value)
    {
        $tmpSum = 0;
        foreach ($data as $dvalue) {
            if ($dvalue[$baseKey] == $value) {
                $tmpSum++;
            }
        }
        $odd = $tmpSum / count($data);
        return $odd;
    }

}