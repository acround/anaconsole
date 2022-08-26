<?php

$r = 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя';
$R = 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';
$s = ' ';

$dir  = opendir(realpath('./'));
while (($file = readdir($dir)) !== false) {
    if (substr($file, 0, 1) == '.') {
        continue;
    }
    if (substr($file, -4) !== '.mp4') {
//        echo substr($file, -4)."\n";
        continue;
    }
    $number = trim(substr($file, 0, strlen($file) - 4), $r . $R . $s);
    while (strlen($number) < 3) {
        $number = '0' . $number;
    }
    $newName = 'Lesson ' . $number . '.mp4';
    echo $file . '->' . $newName . "\n";
    rename($file, $newName);
}