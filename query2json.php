<?php

if ($argc > 1) {
    $filename = $argv[1];
    if (file_exists($filename)) {
        $formData = file_get_contents($filename);
        $formArray = [];
        parse_str($formData, $formArray);
        $filenameS = new SplFileInfo($filename);
        $baseFileName = $filenameS->getBasename('.' . $filenameS->getExtension());
        $path = $filenameS->getPath();
        if ($path) {
            $path .= DIRECTORY_SEPARATOR;
        }
        $newFileName = $path . $baseFileName . '.json';
        file_put_contents($newFileName, json_encode($formArray));
    }
}
