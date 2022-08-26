<?php

function getExtension($fileName)
{
    $name = explode('.', $fileName);
    if (count($name) > 1) {
        return end($name);
    } else {
        return null;
    }
}

function getClearName($fileName)
{
    $name = explode('.', $fileName);
    if (count($name) > 1) {
        unset($name[count($name) - 1]);
    }
    return implode('.', $name);
}

function safeMove($oldName, $newName)
{
    if (file_exists($newName)) {
        $path = dirname($newName);
        $name = basename($newName);
        $ext  = getExtension($name);
        if ($ext) {
            $ext = '.' . $ext;
        }
        $name  = getClearName($name);
        $index = 1;
        while (file_exists($newName)) {
            $newName = $path . DIRECTORY_SEPARATOR . $name . '(' . $index++ . ')' . $ext;
        }
    }
    rename($oldName, $newName);
}

function filesUp($target, $source)
{
    $sourceDir = opendir($source);
    $file      = readdir($sourceDir);
    while ($file != false) {
        if (substr($file, 0, 1) !== '.') {
            $fullFileName = $source . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullFileName)) {
                filesUp($target, $source . DIRECTORY_SEPARATOR . $file);
            } else {
                $newName = $target . DIRECTORY_SEPARATOR . $file;
                safeMove($fullFileName, $newName);
            }
        }
        $file = readdir($sourceDir);
    }
}

$currentDirName = realpath('./');
if ($currentDirName) {
    $currentDir = opendir($currentDirName);
    $file       = readdir($currentDir);
    while ($file !== false) {
        if (substr($file, 0, 1) !== '.') {
            $fullFileName = $currentDirName . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullFileName)) {
                filesUp($currentDirName, $currentDirName . DIRECTORY_SEPARATOR . $file);
            }
        }
        $file = readdir($currentDir);
    }
}
