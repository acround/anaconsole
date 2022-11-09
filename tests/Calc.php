<?php

namespace tests;

class Calc
{

    private $operations = ['+', '-', '−', '*', '/'];

    public function itemCalc($value1, $value2, $operation)
    {
        switch ($operation) {
            case '+':
                return $value1 + $value2;
            case '-':
            case '−':
                return $value1 - $value2;
            case '*':
                return $value1 * $value2;
            case '/':
                return $value1 / $value2;
            default:
                return null;
        }
    }

    public function polCalc(array $sequence)
    {
        while (count($sequence) > 1) {
            $first = count($sequence);
            foreach ($this->operations as $operator) {
                $tmp = array_search($operator, $sequence);
                if ($tmp !== false) {
                    $first = min($first, $tmp);
                }
            }
            $sequence[$first - 2] = $this->itemCalc($sequence[$first - 2], $sequence[$first - 1], $sequence[$first]);
            unset($sequence[$first - 1]);
            unset($sequence[$first]);
            $sequence = array_values($sequence);
        }
        return reset($sequence);
    }

}

$ic = new Calc();
$string = '7 2 3 * −';
$ps = explode(' ', $string);
echo $ic->polCalc($ps) . "\n";
