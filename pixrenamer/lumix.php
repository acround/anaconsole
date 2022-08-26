<?php

include '../extensions.php';
define('LUMIX_DEFAULT_WORK_DIR', 'Lumix');
if ($argc > 1) {
    $dirName = $argv[1];
} else {
    $dirName = '';
}
if (file_exists($dirName) && is_dir($dirName)) {
    $workDirName = $dirName;
} else {
    $workDirName = './' . LUMIX_DEFAULT_WORK_DIR;
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
$names       = array();
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
        $names[] = $fileName;
    }
}

sort($names);

foreach ($names as $fileName) {
    $fullFileName = $workDirName . DIRECTORY_SEPARATOR . $fileName;
    $exif         = exif_read_data($fullFileName, 0, true);
    if (isset($exif['EXIF']['DateTimeOriginal'])) {
        $dateTime      = explode(' ', $exif['EXIF']['DateTimeOriginal']);
        $date          = $dateTime[0];
        $time          = $dateTime[1];
        $fileNukedName = str_replace(':', '-', $date) . '.' . str_replace(':', '', $time);
    } else {
        $fileNukedName = date("Y-m-d_Hms", filemtime($fullFileName));
    }
    $ext     = strtolower(getExtension1($fileName));
    $number  = 0;
    $numberS = '';
    $newName = $fileNukedName . $numberS . '.' . $ext;
    while (file_exists($currentDir . DIRECTORY_SEPARATOR . $newName)) {
        $number++;
        while (strlen($number) < 3) {
            $number = '0' . $number;
        }
        $numberS = '.' . $number;
        $newName = $fileNukedName . $numberS . '.' . $ext;
    }
    rename($workDirName . DIRECTORY_SEPARATOR . $fileName, $currentDir . DIRECTORY_SEPARATOR . $newName);
}
