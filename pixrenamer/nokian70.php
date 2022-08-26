<?php

include '../extensions.php';
define('NOKIA_DEFAULT_WORK_DIR', 'Nokia_N70');
if ($argc > 1) {
    $dirName = $argv[1];
} else {
    $dirName = '';
}
if (file_exists($dirName) && is_dir($dirName)) {
    $workDirName = $dirName;
} else {
    $workDirName = './' . NOKIA_DEFAULT_WORK_DIR;
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
    if ($infoSplit[0] == 'image') {
        $fileNukedName = substr($fileName, 0, strpos($fileName, '.'));
        $ext           = getExtension1($fileName);
        $day           = substr($fileNukedName, 0, 2);
        $month         = substr($fileNukedName, 2, 2);
        $year          = substr($fileNukedName, 4, 4);
        $number        = substr($fileNukedName, 8);
        $newName       = $year . '-' . $month . '-' . $day . '.' . $number . '.' . $ext;
        while (file_exists($currentDir . DIRECTORY_SEPARATOR . $newName)) {
            $newName = $year . '-' . $month . '-' . $day . '.' . ++$number . '.' . $ext;
        }
        copy($workDirName . DIRECTORY_SEPARATOR . $fileName, $currentDir . DIRECTORY_SEPARATOR . $newName);
    }
}
