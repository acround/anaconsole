<?php

function getExtension($fileName)
{
    $l = explode('.', $fileName);
    return end($l);
}

function addExtension($file, $ext)
{
    return $file . '.' . trim(trim($ext), './\\|:?><');
}
