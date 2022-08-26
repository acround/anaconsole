<?php

/*
 * Complete the 'lotteryCoupons' function below.
 *
 * The function is expected to return an INTEGER.
 * The function accepts INTEGER n as parameter.
 */

function lotteryCoupons($n) {

    //There might be a faster way to count what is needed but it takes more time than I have


    // Counting of the sum of the digits
    function sumOfDigits(int $num) {
        $digits = str_split($num . '');
        return array_sum($digits);
    }

    // Counting the appearance of sums of digits for numbers 1..n
    $sumsOfDigits = [];
    for ($i = 1; $i <= $n; $i++) {
        $sod = sumOfDigits($i);
        if (isset($sumsOfDigits[$sod])) {
            $sumsOfDigits[$sod]++;
        } else {
            $sumsOfDigits[$sod] = 1;
        }
    }

    // What is the maximum number of appearances
    $max = 0;
    foreach ($sumsOfDigits as $frenq) {
        if ($max < $frenq) {
            $max = $frenq;
        }
    }

    // How many sums appear most often
    $searched = 0;
    foreach ($sumsOfDigits as $frenq) {
        if ($max == $frenq) {
            $searched++;
        }
    }
    return $searched;
}

$fptr = fopen(getenv("OUTPUT_PATH"), "w");

$n = intval(trim(fgets(STDIN)));

$result = lotteryCoupons($n);

fwrite($fptr, $result . "\n");

fclose($fptr);
