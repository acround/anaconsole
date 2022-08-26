<?php

$t    = 'Кобурн';
$d    = 'Ли Кобурн';
$path = realpath('./');
$dir  = opendir($path);
echo $path."\n";
while (($file = readdir($dir)) !== false) {
    echo $file . ' - ';
    if ($file == '.' || $file == '..') {
        continue;
    }
    if (substr($file, -4) != '.fb2') {
        continue;
    }
    echo $file . ' - ';
    $text = file_get_contents($path . DIRECTORY_SEPARATOR . $file);
    if (mb_stripos($text, $t) !== false) {
        rename($path . DIRECTORY_SEPARATOR . $file, $path . DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR . $file);
        echo "moved\n";
    } else {
        echo "stayed\n";
    }
}