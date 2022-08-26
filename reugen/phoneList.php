<?php

const CSV_SEPARATOR = ";";
const WRONG_NUMBER = '$$$$$$$$$$$$$$$';

$fileName = 'phoneBase.txt';
$fileNameFull = __DIR__ . DIRECTORY_SEPARATOR . $fileName;
if (!file_exists($fileNameFull)) {
    echo "No file\n";
    exit();
}
$out = [];
$file = explode("\n", file_get_contents($fileNameFull));
//$out[] = $file[0];
for ($i = 0; $i < count($file); $i++) {
    $phone = trim($file[$i]);
    if (substr($phone, 0, 1) == '*') {
        $phone = WRONG_NUMBER;
    } else {
        if (substr($phone, 0, 1) == '8') {
            $phone = '7' . substr($phone, 1);
        }
        $phone = str_replace(['+', '(', ')', ' ', '-', ' ', '‑',], '', $phone);
        if (!preg_match('/^\d*$/', $phone)) {
            $phone = WRONG_NUMBER;
        }
    }
    $out[] = $phone;
}
file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'phoneBase-out.txt', implode("\n", $out));
