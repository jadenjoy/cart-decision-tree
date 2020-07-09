<?php

namespace Darvin\CART;

/**
 * Class ArrayHelper
 * @package Darvin\CART
 */

class DataSet
{

    /**
     * Dataset
     * @var null
     */
    private $dataSet = [];


    /**
     * Фичи датасета
     * @var array
     */
    public $features = [];


    public $featArrays = [];


    public function __construct($dataSet)
    {
        $this->dataSet = $dataSet;
        $this->features = $this->extractFeatures($dataSet);
        $this->extractFeatArrays($dataSet, $this->features);
    }

    /**
     * @return array
     */
    public function getDataSet()
    {
        return $this->dataSet;
    }

    /**
     * @param array $dataSet
     */
    public function setDataSet($dataSet)
    {
        $this->dataSet = $dataSet;
    }


    public function extractFeatures($dataSet)
    {
        $dataSet = array_values($dataSet);
        return array_keys($dataSet[0]);
    }

    public function extractFeatArrays($dataSet, $keys)
    {
        foreach ($keys as $key) {
            $this->featArrays[$key] = ArrayHelper::makeFeatArray($dataSet, $key);
        }
    }

    public function feat($key)
    {
        return $this->featArrays[$key];
    }

}
