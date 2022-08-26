<?php

define('NAME_BEGIN', 'Раздобудько_Евгения_');
define('NAME_MIDDLE', '_Лист_');
$nameBegin  = isset($argv[1]) ? $argv[1] : NAME_BEGIN;
$nameMiddle = isset($argv[2]) ? $argv[2] : NAME_MIDDLE;
$dirName    = basename(realpath('./'));
$dir        = opendir('./');
$fileList   = array();
while (($fileName   = readdir($dir)) !== false) {
    if (substr($fileName, 0, 1) != '.') {
        $ext = end(explode('.', $fileName));
        if ($ext == 'jpg') {
            $fileList[$fileName] = $fileName;
        }
    }
}
ksort($fileList);
$tmpPrefix = rand(1000, 9999) . time() . rand(1000, 9999);
$digits    = strlen((string) count($fileList));
$i         = 0;
foreach ($fileList as $k => $name) {
    $i++;
    $n = $i;
    while (strlen((string) $n) < $digits) {
        $n = '0' . $n;
    }
    $fileList[$k] = [
        'old' => $name,
        'tmp' => $tmpPrefix . '_' . $n . '.jpg',
        'new' => str_replace(' ', '_', $nameBegin . $dirName . $nameMiddle . $n . '.jpg')
    ];
}
foreach ($fileList as $names) {
    rename($names['old'], $names['tmp']);
}
foreach ($fileList as $names) {
    rename($names['tmp'], $names['new']);
}
