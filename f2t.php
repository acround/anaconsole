<?php

use analib\Core\Xml\Fb2\FB2Tools;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/acround/analib/autoload.php';
//include_once 'LibraryIncluder.php';
//LibraryIncluder::includeAnalib();
$shortOptions = "o::p::f::";
$longOptions  = array(
    'operation::',
    'params::',
    'params2::',
);
$options      = getopt($shortOptions, $longOptions);
$operation    = '?';
$params       = '';
$params2      = '';
foreach ($options as $name => $value) {
    switch ($name) {
        case 'o':
        case 'operation':
            $operation = $value;
            break;
        case 'p':
        case 'params':
            $params    = $value;
            break;
        case 'f':
        case 'params2':
            $params2   = $value;
            break;
    }
}

FB2Tools::create()->execute($operation, $params, $params2);
