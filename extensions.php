<?php

function getExtension1($fileName)
{
    return end(explode('.', $fileName));
}

function getExtension2($fileName)
{
    $path_info = pathinfo($fileName);
    return $path_info['extension'];
}

function getExtension3($fileName)
{
    return substr($fileName, strrpos($fileName, '.') + 1);
}

function getExtension4($fileName)
{
    return substr(strrchr($fileName, '.'), 1);
}

function getExtension5($fileName)
{
    return array_pop(explode('.', $fileName));
}
