<?php

$file = __DIR__ . DIRECTORY_SEPARATOR . 'timelog.txt';
$str  = date('Y-m-d H:i:s') . "\n";
file_put_contents($file, $str, FILE_APPEND);
