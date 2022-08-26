<?php

include '../extensions.php';
define('SAMSUNG_DEFAULT_WORK_DIR', 'Samsung');
if ($argc > 1) {
    $dirName = $argv[1];
} else {
    $dirName = '';
}
if (file_exists($dirName) && is_dir($dirName)) {
    $workDirName = $dirName;
} else {
    $workDirName = './' . SAMSUNG_DEFAULT_WORK_DIR;
}
if (!file_exists($workDirName) || !is_dir($workDirName)) {
    echo "Working directory not found\n";
    exit;
}
$currentDir = realpath('./out');
if (!$currentDir) {
    $currentDir = realpath('./');
}
$workDirName = realpath($workDirName);
$workDir     = opendir($workDirName);
$finfo       = finfo_open(FILEINFO_MIME_TYPE);
while (($fileName    = readdir($workDir)) !== false) {
    if (substr($fileName, 0, 1) == '.') {
        continue;
    }
    if (is_dir($fileName)) {
        continue;
    }
    $fileInfo  = finfo_file($finfo, $workDirName . DIRECTORY_SEPARATOR . $fileName, FILEINFO_MIME_TYPE);
    $infoSplit = explode('/', $fileInfo);
    if (($infoSplit[0] == 'image') || ($infoSplit[0] == 'video')) {
        $fileNukedName = substr($fileName, 0, strpos($fileName, '.'));
        $ext           = getExtension1($fileName);
        $year          = substr($fileNukedName, 0, 4);
        $month         = substr($fileNukedName, 4, 2);
        $day           = substr($fileNukedName, 6, 2);
        $number        = substr($fileNukedName, 9);
        $newName       = $year . '-' . $month . '-' . $day . '.' . $number . '.' . $ext;
        $i             = 0;
        while (file_exists($currentDir . DIRECTORY_SEPARATOR . $newName)) {
            $newName = $year . '-' . $month . '-' . $day . '.' . $number . '.' . ++$i . '.' . $ext;
        }
        rename($workDirName . DIRECTORY_SEPARATOR . $fileName, $currentDir . DIRECTORY_SEPARATOR . $newName);
    }
}
