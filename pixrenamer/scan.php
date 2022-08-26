<?php

function scanDirImageRename($workDirName, array $date, array $suffix, $dir, $outDir)
{
    if ($dir) {
        if ((count($suffix) == 0) && ($dir == (string) (int) $dir)) {
            $date[] = $dir;
        } else {
            $suffix[] = $dir;
        }
    }
    $currentDirName = implode(DIRECTORY_SEPARATOR, array_merge(array($workDirName), $date, $suffix));
    $dirFileName    = '';
    if (count($date)) {
        $dirFileName = implode('-', $date);
    }
    if (count($suffix)) {
        if ($dirFileName) {
            $dirFileName .= '.(';
        }
        $dirFileName .= implode('.', $suffix) . ')';
    }
    $dirFileName = str_replace(' ', '_', $dirFileName);
    $currentDir  = opendir($currentDirName);
    $finfo       = finfo_open(FILEINFO_MIME_TYPE);
    $names       = array();
    while (($fileName    = readdir($currentDir)) !== false) {
        if (substr($fileName, 0, 1) == '.') {
            continue;
        }
        if (is_dir($currentDirName . DIRECTORY_SEPARATOR . $fileName)) {
            scanDirImageRename($workDirName, $date, $suffix, $fileName, $outDir);
        } else {
            $fileInfo  = finfo_file($finfo, $currentDirName . DIRECTORY_SEPARATOR . $fileName, FILEINFO_MIME_TYPE);
            $infoSplit = explode('/', $fileInfo);
            if ($infoSplit[0] == 'image') {
                $names[] = $fileName;
            }
        }
    }
    sort($names);
    closedir($currentDir);
    foreach ($names as $num => $name) {
        $number = $num + 1;
        while (strlen($number) < 3) {
            $number = '0' . $number;
        }
        $newName = $dirFileName . '.' . $number . '.' . getExtension1($name);
        copy($currentDirName . DIRECTORY_SEPARATOR . $name, $outDir . DIRECTORY_SEPARATOR . $newName);
    }
}

include '../extensions.php';
define('SCAN_DEFAULT_WORK_DIR', 'scan');
if ($argc > 1) {
    $dirName = $argv[1];
} else {
    $dirName = '';
}
if (file_exists($dirName) && is_dir($dirName)) {
    $workDirName = $dirName;
} else {
    $workDirName = './' . SCAN_DEFAULT_WORK_DIR;
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

scanDirImageRename($workDirName, array(), array(), null, $outDir);
echo "\n";

exit;
$workDir = opendir($workDirName);
$finfo   = finfo_open(FILEINFO_MIME_TYPE);

while (($fileName = readdir($workDir)) !== false) {
    if (substr($fileName, 0, 1) == '.') {
        continue;
    }
    if (is_dir($fileName)) {
        continue;
    }
    $fileInfo  = finfo_file($finfo, $workDirName . DIRECTORY_SEPARATOR . $fileName, FILEINFO_MIME_TYPE);
    $infoSplit = explode('/', $fileInfo);
    if ($infoSplit[0] == 'image') {

    }
}
