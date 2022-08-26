<?php

use analib\Core\Xml\Fb2\FB2Informer;

include_once 'LibraryIncluder.php';
LibraryIncluder::includeAnalib();

/* * *****************************************************************************
 * Функции
 * ***************************************************************************** */

/* * *****************************************************************************
 * Главный модуль
 * ***************************************************************************** */

$arguments = $argv;
//print_r($arguments);
array_shift($arguments);
for ($i = 0; $i < count($arguments); $i++) {
    if (file_exists($arguments[$i])) {
        $fb2    = FB2Informer::create($arguments[$i]);
        $images = $fb2->images(true);
        foreach ($images as $image) {
            $name = $image['name'];
            $body = base64_decode($image['image']);
            file_put_contents($name, $body);
        }
    }
}
