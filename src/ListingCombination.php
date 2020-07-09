<?php

namespace Darvin\CART;

class ListingCombination
{

    /**
     * Функция вычисляет комбинации
     * @param array $data
     * @return array
     */
    public static function combinations(array $data)
    {
        $combinationsArray = array();
        $return = array();

        // Рассчитываем все комбинации
        for ($i = 0; $i < count($data) - 1; $i++) {
            $res = self::calculateCombination($data, $i + 1);
            foreach ($res as $key => $value) {
                array_push($combinationsArray, $value);
            }

        }

        //
        $length = floor(count($data) / 2);
        // Четное или нечетное кол-во параметров
        $flg    = count($data) % 2;

        foreach ($combinationsArray as $key => $combination) {
            if (count($combination) <= $length) {
                // Если четное кол-во
                if ($flg == 0) {
                    $max = max($data);
                    if (!array_search($max, $combination)) {
                        array_push($return, $combination);
                    }
                } elseif ($flg == 1) {
                    array_push($return, $combination);
                }
            }
        }
        return $return;
    }


    /**
     * @param array $array
     * @param int $number
     * @return array|void
     */
    public static function calculateCombination(array $array, int $number)
    {
        $arrNumber = count($array);

        //print_r(">".$arrNumber."\n");
        $returnArr = [];
        if ($arrNumber < $number) {
            return;
        } elseif ($number == 1) {
            for ($i = 0; $i < $arrNumber; $i++) {
                $returnArr[$i] = array($array[$i]);
            }
        } elseif ($number > 1) {
            $j = 0;
            for ($i = 0; $i < $arrNumber - $number + 1; $i++) {
                $ts = self::calculateCombination(array_slice($array, $i + 1), $number - 1);
                foreach ($ts as $t) {
                    array_unshift($t, $array[$i]);
                    $returnArr[$j] = $t;
                    $j++;
                }
            }
        }

        return $returnArr;
    }
}
