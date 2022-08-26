<?php

use analib\Util\StdIo;

include_once 'LibraryIncluder.php';
LibraryIncluder::includeAnalib();
$sym = StdIo::ask('Символ:');
$d   = ord($sym);
StdIo::put('Код:' . $d);
