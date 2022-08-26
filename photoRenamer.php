<?php

use analib\Util\PhotoRenamer;

include_once 'LibraryIncluder.php';
LibraryIncluder::includeAnalib();

$dirName = realpath('./');
PhotoRenamer::exec($dirName);
