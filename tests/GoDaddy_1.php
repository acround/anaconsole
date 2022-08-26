<?php

/*
 * Complete the 'slowestKey' function below.
 *
 * The function is expected to return a CHARACTER.
 * The function accepts 2D_INTEGER_ARRAY keyTimes as parameter.
 */

function slowestKey($keyTimes) {
    // Write your code here
    if (!is_array($keyTimes)) {
        throw new Exception('slowestKey: parameter must be an array');
    }
    $keyTimesTotal = [];
    // We're looking for the longest time to press every button
    $prevPressing = 0;
    foreach ($keyTimes as $keyTime) {
        $pressing = $keyTime[1] - $prevPressing;
        if (!isset($keyTimesTotal[$keyTime[0]])) {
            $keyTimesTotal[$keyTime[0]] = $pressing;
        } elseif ($keyTimesTotal[$keyTime[0]] < $pressing) {
            $keyTimesTotal[$keyTime[0]] = $pressing;
        }
        $prevPressing = $keyTime[1];
    }
    // We're looking for the longest time to press among buttons
    if (count($keyTimesTotal) > 0) {
        $searchedButton = 0;
        $searchedTime = 0;
        foreach ($keyTimesTotal as $key => $time) {
            if ($time > $searchedTime) {
                $searchedButton = $key;
                $searchedTime = $time;
            }
        }
        // We're turning $searchedButton (0-25) into symbol (a-z)
        return chr(ord('a') + $searchedButton);
    } else {
        // There was no right data
        return chr(0);
    }
}

$fptr = fopen(getenv("OUTPUT_PATH"), "w");

$keyTimes_rows = intval(trim(fgets(STDIN)));
$keyTimes_columns = intval(trim(fgets(STDIN)));

$keyTimes = array();

for ($i = 0; $i < $keyTimes_rows; $i++) {
    $keyTimes_temp = rtrim(fgets(STDIN));

    $keyTimes[] = array_map('intval', preg_split('/ /', $keyTimes_temp, -1, PREG_SPLIT_NO_EMPTY));
}

$result = slowestKey($keyTimes);

fwrite($fptr, $result . "\n");

fclose($fptr);
