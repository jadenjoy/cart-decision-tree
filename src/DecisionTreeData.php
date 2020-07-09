<?php

namespace Darvin\CART;

/**
 * Class DecisionTreeData
 * @package Darvin\CART
 */
class DecisionTreeData
{
    public $number;
    public $match;
    public $unmatch;

    public $splitKey;
    public $splitValue;


    public function __construct($data, $baseKey, $value, $splitKey = null, $splitValue = null)
    {

        $splitArray = ArrayHelper::splitByPredictionKey($data, $baseKey);

        $this->number = count($data);
        if (isset($splitArray[$value])) {
            $this->match = count($splitArray[$value]);
        } else {
            $this->match = 0;
        }
        $this->unmatch   = $this->number - $this->match;
        $this->splitKey = $splitKey;
        $this->splitValue = $splitValue;
    }
}