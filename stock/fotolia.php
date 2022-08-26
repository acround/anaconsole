<?php

echo "Read files...";
$currentDirName = realpath('./');
$currentDir     = opendir($currentDirName);
$files          = array();
while (($file           = readdir($currentDir)) !== false) {
    if (substr($file, 0, 1) == '.') {
        continue;
    }
    $fileExp                       = pathinfo($file);
    $files[$fileExp['filename']][] = $file;
}
echo "OK\n";
ksort($files);
echo "Processing...\n";
foreach ($files as $zipName => $zipParts) {
    if (count($zipParts) > 1) {
        echo $zipName . ' - ';
        $zip = new ZipArchive();
        $zip->open($zipName . '.zip', ZIPARCHIVE::CREATE);
        foreach ($zipParts as $part) {
            $zip->addFile($part);
        }
        $zip->close();
        foreach ($zipParts as $part) {
            unlink($part);
        }
        echo "OK\n";
    }
}
