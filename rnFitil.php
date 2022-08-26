<?php

//Фитиль  Всё выше и выше...  (1983) смотреть онлайн.mp4
//Фитиль, выпуск № 213 (1980) смотреть онлайн.mp4
$patterns     = array(
    '/Фитиль, выпуск № /',
    '/Фитиль[\s]+/',
    '/[\s]+\(/',
    '/\) смотреть онлайн.mp4/',
);
$replacements = array(
    'Выпуск № ',
    '',
    '.',
    '.mp4',
);
$dirName      = realpath('./');
echo $dirName;
$dir          = opendir($dirName);
while (($fileName     = readdir($dir)) !== false) {
    if (substr($fileName, 0, 1) == '.') {
        continue;
    }
    if (!is_file($fileName)) {
        continue;
    }
    if (end(explode('.', $fileName)) == 'mp4') {
        $fileNameNew = preg_replace($patterns, $replacements, $fileName);
        $oldName     = $dirName . DIRECTORY_SEPARATOR . $fileName;
        $newName     = $dirName . DIRECTORY_SEPARATOR . $fileNameNew;
        rename($oldName, $newName);
        echo $fileName . '->' . $fileNameNew . "\n";
    }
}