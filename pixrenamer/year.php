<?php

include '../extensions.php';
define('DEFAULT_WORK_DIR', 'in');
if ($argc > 1) {
    $dirName = $argv[1];
} else {
    $dirName = '';
}
if (file_exists($dirName) && is_dir($dirName)) {
    $workDirName = $dirName;
} else {
    $workDirName = './' . DEFAULT_WORK_DIR;
}
if (!file_exists($workDirName) || !is_dir($workDirName)) {
    echo "Working directory not found\n";
    exit;
}
$outDir = realpath('./out');
if (!$outDir) {
    $outDir = realpath('./');
}
$workDirName = realpath($workDirName);
$workDir     = opendir($workDirName);

$names    = array();
while (($fileName = readdir($workDir)) !== false) {
    if (substr($fileName, 0, 1) == '.') {
        continue;
    }
    if (is_dir($fileName)) {
        continue;
    }
    $nameSplit  = explode('.', $fileName);
    $nameSplit  = explode('-', $nameSplit[0]);
    $year       = $nameSplit[0];
    $nameOutDir = $outDir . DIRECTORY_SEPARATOR . $year;
    if (!file_exists($nameOutDir)) {
        mkdir($nameOutDir);
    }
    $newName = $nameOutDir . DIRECTORY_SEPARATOR . $fileName;
    copy($workDirName . DIRECTORY_SEPARATOR . $fileName, $newName);
}
