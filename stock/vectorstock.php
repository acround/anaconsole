<?php

function getExtension($filename)
{
    $tmp = explode(".", $filename);
    if (count($tmp) > 1) {
        return end($tmp);
    } else {
        return '';
    }
}

echo "Read files...";
$currentDirName = realpath('./');
$currentDir     = opendir($currentDirName);
$files          = array();
while (($file           = readdir($currentDir)) !== false) {
    if (substr($file, 0, 1) == '.') {
        continue;
    }
    $fileExp                                          = pathinfo($file);
    $files[$fileExp['filename']][getExtension($file)] = $file;
}
echo "OK\n";
ksort($files);
echo "Processing...\n";
foreach ($files as $zipName => $zipParts) {
    if (isset($zipParts['eps']) && isset($zipParts['jpg'])) {
        echo $zipName . ' - ';
        $zip = new ZipArchive();
        $zip->open($zipName . '.zip', ZIPARCHIVE::CREATE);
        $zip->addFile($zipParts['eps']);
        $zip->close();
        unlink($zipParts['eps']);
        echo "OK\n";
    }
}
