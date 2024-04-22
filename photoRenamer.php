<?php

use analib\Util\PhotoRenamer;

//include_once 'LibraryIncluder.php';
//LibraryIncluder::includeAnalib();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor/acround/analib/autoload.php';

$dirName = realpath('./');
$folders = false;
if (count($argv) > 1) {
    if ($argv[1] === '-f') {
        $folders = true;
    }
}
PhotoRenamer::exec($dirName, $folders);
