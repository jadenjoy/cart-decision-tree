<?php

namespace Darvin\CART;
use League\Csv\Exception;

/**
 * Class DecisionTreeClassifier
 * @package Darvin\CART
 */
class DecisionTreeClassifier
{

    private $binaryVariableData;

    /**
     * Training Dataset
     *
     * @var
     */
    private $data;

    /**
     * Base key for classification
     *
     * @var
     */
    private $baseKey;

    /**
     * Base value for classification
     *
     * @var
     */
    private $baseValue;

    /**
     * False value for classification
     *
     * @var
     */
    private $falseValue;


    private $tree;


    public $maxLeafNumber = 20;


    private $featuresDeltaI = [];

    /**
     * DecisionTree constructor.
     *
     * @param $data array Dataset
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Функция производит классификацию датасета разбивая на $baseValue и $falseValue по $baseKey
     *
     * @param string $baseKey Ключ для которого необходимо сделать классификацию
     * @param string $baseValue Базовое значение для классификации
     * @param string $falseValue Значение при котором будет возвращено false
     * @return DecisionTreeNode
     */
    public function fit($baseKey, $baseValue = null, $falseValue = null)
    {
        $values = [];
        if (is_null($baseKey) || is_null($falseValue)) {
            foreach ($this->data as $item) {
                if (!isset($values[$item[$baseKey]])) {
                    $values[$item[$baseKey]] = $item[$baseKey];
                }
                if (count($values) == 2) {
                    break;
                }
            }
            $values = array_keys($values);
            if (count($values) < 2) {
                return false;
            }
            $this->baseValue = $values[0];
            $this->falseValue = $values[1];
        } else {
            $this->baseValue = $baseValue;
            $this->falseValue = $falseValue;
        }
        $this->baseKey = $baseKey;

        $this->binaryVariableData = $this->makeBinaryVariableData($this->data, $baseKey);

        $decisionTree = $this->makeDecisionTree(
            $this->binaryVariableData,
            $this->baseKey,
            $this->baseValue,
            $this->baseKey,
            $this->baseValue,
            $this->maxLeafNumber
        );


        $this->tree = $decisionTree;

        return $this->tree;
    }


    /**
     * @param array $target
     * @return mixed
     */
    public function predict(array $target)
    {
        return $this->executePrediction($this->tree, $target);
    }


    public function score($dataSet, $reference = null)
    {
        $true = 0;
        if (is_null($reference)) {
            $reference = $dataSet;
        }
        $splitKey = $this->tree->data->splitKey;
        foreach ($dataSet as $key => $data) {
            $res = $this->predict($data);
            if ($reference[$key][$splitKey] == $res) {
                $true++;
            }
        }
        return $true / count($dataSet);
    }


    /**
     * @param DecisionTreeNode $tree
     * @param array $target
     */
    public function executePrediction(DecisionTreeNode $tree, array $target)
    {


        //$this->log("START > ");
        // Проверяем последняя ли ветвь
        if ($tree->terminal) {

            $trueNum  = $tree->data->match;
            $falseNum = $tree->data->unmatch;
            $this->log("TERMINAL(".$tree->data->splitKey."|".$tree->data->splitValue.") > ");


            if ($trueNum > $falseNum) {
                $pars = $trueNum / ($trueNum + $falseNum);
                //echo "PARS:" . $pars . "|";
                $this->log("END - ".$this->baseValue."\n");
                return $this->baseValue;
            } else {
                $pars = $falseNum / ($trueNum + $falseNum);
                //echo "PARS:" . $pars . "|";
                $this->log("END - ".$this->falseValue."\n");
                return $this->falseValue;
            }


        } else {
            //$this->log("LEAF > ");
            $splitKey = $tree->left->data->splitKey;
            $leftValue = $tree->left->data->splitValue;
            $rightValue = $tree->right->data->splitValue;
        }





        $featArray = ArrayHelper::makeFeatArray($this->data, $splitKey);


        if (count($featArray) > 2  && is_numeric($featArray[0])) {
            if (!(strstr('<=x', (string) $leftValue) == false)) {
                //$this->log("FLAG=1 > ");
                $flg = 1;
                $border = trim($leftValue, '<=x');
            } else {
                //$this->log("FLAG=2 > ");
                $flg = 2;
                $border = trim($rightValue, '<=x');
            }
        } else {
            //$this->log("FLAG=0 > ");
            $flg = 0;
        }



        // Вычисляем ветвь
        switch ($flg) {
            case 0:
                if(empty((string) $target[$splitKey])) {
//                    echo "--------------------------------------\n";
//                    echo "S:".$splitKey."| L:".$leftValue."| R:".$rightValue."\n";
//                    echo "^^^^^".((string) $target[$splitKey])."^^^^^^\n";
//                    print_r($target);
                    //echo "BREAK > ";
                    $this->log("BREAK > ");
                    break;
                }

                if (!(strstr($leftValue, (string) $target[$splitKey]) == false)) {
                    $this->log("LEFT(".$splitKey."|".$leftValue.") > ");
                    return $this->executePrediction($tree->left, $target);
                } elseif (!(strstr($rightValue, (string) $target[$splitKey]) == false)) {
                    $this->log("RIGHT(".$splitKey."|".$rightValue.") > ");
                    return $this->executePrediction($tree->right, $target);
                }
                break;
            case 1:
                //echo "FLG1 > ";
                if ($border >= $target[$splitKey]) {
                    $this->log("RIGHT(".$splitKey."|".$rightValue.") > ");
                    return $this->executePrediction($tree->right, $target);
                } else {
                    $this->log("LEFT(".$splitKey."|".$leftValue.") > ");
                    return $this->executePrediction($tree->left, $target);
                }
                break;
            case 2:
                //echo "FLG2 > ";
                if ($border >= $target[$splitKey]) {
                    $this->log("LEFT(".$splitKey."|".$leftValue.") > ");
                    return $this->executePrediction($tree->left, $target);
                } else {
                    $this->log("RIGHT(".$splitKey."|".$rightValue.") > ");
                    return $this->executePrediction($tree->right, $target);
                }
                break;
            default:
                break;
        }
        $this->log("END\n");
    }


    /**
     * @param $binaryData
     * @param $baseKey
     * @param $baseValue
     * @param $splitKey
     * @param $splitValue
     * @return DecisionTreeNode
     */
    private function makeDecisionTree($binaryData, $baseKey, $baseValue, $splitKey, $splitValue, $maxLeafNumber = 10, $leafNumber = 0)
    {

        //       echo $leafNumber.">".$baseKey."\n";
        $deltaIArray = [];
        $dtNode = new DecisionTreeNode();
        $dtData = new DecisionTreeData($binaryData, $baseKey, $baseValue, $splitKey, $splitValue);
        $dtNode->data = $dtData;
        $dtNode->terminal = false;

        $keys = array_keys($binaryData[0]);


//        echo "FOREACH\n";
        foreach ($keys as $k => $key) {
            if ($key == $baseKey & $leafNumber <= $maxLeafNumber) {
                continue;
            }
            $deltaIArray[$key] = CART::calculateDeltaI($binaryData, $baseKey, $key);
        }
        if ($leafNumber == 0) {
            $this->featuresDeltaI = $deltaIArray;
        }

//        echo "END FOREACH\n";
        $flg = 0;

//        echo "FLG:".$flg."\n";



        foreach ($deltaIArray as $key => $value) {
            if ($value != 0.0) {
                //               echo $key."!=".$value."\n";
                $flg = 1;
            } else {
//                echo $key."==".$value."\n";
            }
        }

        if ($flg == 0) {
            $dtNode->terminal = true;
            return $dtNode;
        }

        $splitKey = array_keys($deltaIArray, max($deltaIArray));
        $splitArray = ArrayHelper::splitByPredictionKey($binaryData, $splitKey[0]);

        $i = 0;

        foreach ($splitArray as $key => $value) {
            switch ($i) {
                case 0:
                    $dtNode->left = $this->makeDecisionTree(
                        $value,
                        $baseKey,
                        $baseValue,
                        $splitKey[0],
                        $key,
                        $maxLeafNumber,
                        $leafNumber + 1
                    );
                    break;
                case 1:
                    $dtNode->right = $this->makeDecisionTree(
                        $value,
                        $baseKey,
                        $baseValue,
                        $splitKey[0],
                        $key,
                        $maxLeafNumber,
                        $leafNumber + 1
                    );
                    break;
                default:
                    break;
            }
            $i++;
        }

        return $dtNode;
    }


    /**
     * @param array $rawData
     * @param string $baseKey
     * @return array|mixed
     */
    public function makeBinaryVariableData(array $rawData, string $baseKey)
    {
        // Строковые параметры
        $multipleParam = [];

        // Числовые параметры
        $continuousParam = [];

        // Датасет
        $dataSet = new DataSet($rawData);
        // Ключи массива датасета




        foreach ($dataSet->features as $k => $key) {
            if ($key == $baseKey) {
                continue;
            }
            // Получаем все возможные значения по ключу
            $featArray = $dataSet->feat($key);

            // Заполняем строковые и числовые пармаетры
            if (count($featArray) >= 3) {
                foreach ($featArray as $fkey => $fvalue) {
                    if ($fvalue != '') {
                        if (is_numeric($featArray[$fkey])) {
                            array_push($continuousParam, $key);
                        } else {
                            array_push($multipleParam, $key);
                        }
                        break;
                    }
                }
            }
        }
        $data = $dataSet->getDataSet();
        foreach ($multipleParam as $key => $predictionKey) {
            $data = $this->multiplyToBinary($data, $baseKey, $predictionKey);
        }

        foreach ($continuousParam as $key => $predictionKey) {
            $data = $this->continuousToBinary($data, $baseKey, $predictionKey);
        }


        return $data;
    }


    /**
     * @param DataSet $dataSet Датасет
     * @param string $baseKey Базовый ключ
     * @param string $predictionKey Ключ для прдсказания
     * @return array
     */
    private function multiplyToBinary(array $rawData, string $baseKey, string $predictionKey)
    {

        $featArray = ArrayHelper::makeFeatArray($rawData, $predictionKey);
        $combinations = ListingCombination::combinations($featArray);
        $deltaIArray = [];

        foreach ($combinations as $key => $combination) {
            $tmpData = $this->toBinary($rawData, $predictionKey, $combination, 'type1', 'type2');
            $deltaIArray[$key] = CART::calculateDeltaI($tmpData, $baseKey, $predictionKey);
        }

        $maxDeltaIKey = array_keys($deltaIArray, max($deltaIArray))[0];

        $typeName1 = "";
        $typeName2 = "";

        foreach ($featArray as $key => $value) {
            if (in_array($value, $combinations[$maxDeltaIKey])) {
                $typeName1 .= $value;
            } else {
                $typeName2 .= $value;
            }
        }

        return self::toBinary($rawData, $predictionKey, $combinations[$maxDeltaIKey], $typeName1, $typeName2);
    }


    /**
     * @param array $rawData
     * @param string $baseKey
     * @param string $predictionKey
     * @return mixed
     */
    public function continuousToBinary(array $rawData, string $baseKey, string $predictionKey)
    {
        $featArray = ArrayHelper::makeFeatArray($rawData, $predictionKey);
        asort($featArray);
        $combinations = [];
        // Массив с дельта I
        $deltaIArray = [];

        for ($i = 1; $i < count($featArray); $i++) {
            // Разделяем на комбинации
            $combinations[$i] = array_slice($featArray, 0, $i);
            $tmpdata = $this->toBinary($rawData, $predictionKey, $combinations[$i], 'type1', 'type2');
            // Вычисляем Delta I
            $deltaIArray[$i] = CART::calculateDeltaI($tmpdata, $baseKey, $predictionKey);
        }


        $maxDeltaIKey = array_keys($deltaIArray, max($deltaIArray))[0];

        $typeName1 = "";
        $typeName2 = "";
        $groupMax = max($combinations[$maxDeltaIKey]);

        $typeName1 = $groupMax . " <=x";
        $typeName2 = $groupMax . " >x";

        foreach ($rawData as $num => $array) {
            $chk = $array[$predictionKey];
            if (in_array($chk, $combinations[$maxDeltaIKey])) {
                $tmpArray = $array;
                $tmpArray[$predictionKey] = $typeName1;
                $tmpData[$num] = $tmpArray;
            } else {
                $tmpArray = $array;
                $tmpArray[$predictionKey] = $typeName2;
                $tmpData[$num] = $tmpArray;
            }
        }

        return $tmpData;
    }


    /**
     * @param $file
     */
    public function saveTree($file)
    {
        $data = [
            'baseKey' => $this->baseKey,
            'baseValue' => $this->baseValue,
            'falseValue' => $this->falseValue,
            'tree' => $this->tree,
            'featuresDeltaI' => $this->featuresDeltaI
        ];
        file_put_contents($file, serialize($data));
    }

    public function loadTree($file)
    {
        $data = unserialize(file_get_contents($file));
        $this->tree = $data['tree'];
        $this->baseValue = $data['baseValue'];
        $this->falseValue = $data['falseValue'];
        $this->baseKey = $data['baseKey'];
        $this->featuresDeltaI = $data['featuresDeltaI'];
    }


    /**
     * @param array $rawData
     * @param string $predictionKey
     * @param array $combination
     * @param string $name1
     * @param string $name2
     * @return mixed
     */
    private function toBinary(array $rawData, string $predictionKey, array $combination, string $name1, string $name2)
    {

        foreach ($rawData as $num => $array) {
            $chk = $array[$predictionKey];
            if (in_array($chk, $combination)) {
                $tmparray = $array;
                $tmparray[$predictionKey] = $name1;
                $tmpdata[$num] = $tmparray;
            } else {
                $tmparray = $array;
                $tmparray[$predictionKey] = $name2;
                $tmpdata[$num] = $tmparray;
            }
        }
        return $tmpdata;
    }


    public function log($text) {
       // echo $text;
    }

    /**
     * @return mixed
     */
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * @return mixed
     */
    public function getBaseKey()
    {
        return $this->baseKey;
    }

    /**
     * @return mixed
     */
    public function getBaseValue()
    {
        return $this->baseValue;
    }

    /**
     * @return mixed
     */
    public function getFalseValue()
    {
        return $this->falseValue;
    }


    /**
     * @return array
     */
    public function getFeaturesDeltaI(): array
    {
        return $this->featuresDeltaI;
    }

}