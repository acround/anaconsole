<?php

$title = isset($argv[1]) ? $argv[1] : '';
if ($title) {
    $title  = '***** ' . $title . ' *****';
    $border = str_pad('', strlen($title), '*');
    echo $border . "\n";
    echo $title . "\n";
    echo $border . "\n";
}