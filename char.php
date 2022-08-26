<?php

// //Хрень - 194
include_once 'LibraryIncluder.php';
LibraryIncluder::includeAnalib();
$r = StdIo::ask('Символ: ');
analib\Util\StdIo::putLn(ord($r));
