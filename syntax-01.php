<?php

use analib\System\Console\IO;
use analib\System\Console\Colors;

include_once 'LibraryIncluder.php';
LibraryIncluder::includeAnalib();

//include './config.php';
function a01($p1 = 1, $p2)
{
    return $p1 + $p2;
}

//Colors::colorBlack();
//Colors::backGray();
//Colors::fontBold();

Colors::colorPut(
    [
        Colors::COLOR_BLACK,
        Colors::BACK_GREY,
        Colors::FONT_BOLD,
//			Colors::FONT_UNDERLINE,
    ]
);
echo a01(1, 2);
Colors::colorsDefault();
IO::Ll();
