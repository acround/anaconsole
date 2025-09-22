<?php

$fileName = $argv[1] ?? null;
if ($fileName) {
    $hashFileName = 'clf.hash';
    $scriptPath   = dirname(__FILE__);
    $hashFileName = $scriptPath . DIRECTORY_SEPARATOR . $hashFileName;
    $hashFile     = trim(file_get_contents($hashFileName));
    $hash         = md5_file($fileName);
    $fileExt      = explode('.', $fileName);
    $ext          = strtolower(end($fileExt));
    $newStr       = $ext . '=' . $hash . "\n";
    file_put_contents($hashFileName, $hashFile . "\n" . $newStr);
}