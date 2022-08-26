<?php

function normalyzeName($name)
{
    $lowerRuSymbols = 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя';
    $upperRuSymbols = 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';
    $nameParts      = explode('-', $name);
    foreach ($nameParts as $k => $namePart) {
        /**
         * windows-1251
         */
        $namePart    = ucfirst(strtolower(trim($namePart)));
        /**
         * utf-8
         */
        $namePart    = mb_strtoupper($namePart);
        $firstSymbol = mb_substr($namePart, 0, 1, 'utf-8');
        $index       = mb_strpos($lowerRuSymbols, $firstSymbol, 0, 'utf-8');
        if ($index !== FALSE) {
            $namePart = mb_substr($upperRuSymbols, $index, 1, 'utf-8') . mb_substr($namePart, 1, mb_strlen($namePart, 'utf-8'), 'utf-8');
        }
        $nameParts[$k] = $namePart;
    }
    return implode('-', $nameParts);
}

//echo normalyzeName('арбеньев александр николаевич')."\n";
//echo normalyzeName('АРБЕНЬЕВ')."\n";
//echo normalyzeName('АЛЕКСАНДР')."\n";
//echo normalyzeName('НИКОЛАЕВИЧ')."\n";
////echo normalyzeName('Арбеньев Александр Николаевич')."\n";
//echo normalyzeName('Арбеньев')."\n";
//echo normalyzeName('Александр')."\n";
//echo normalyzeName('Николаевич')."\n";
echo normalyzeName('аРБЕНЬЕВ аЛЕКСАНДР нИКОЛАЕВИЧ') . "\n";
//echo normalyzeName('аРБЕНЬЕВ')."\n";
//echo normalyzeName('аЛЕКСАНДР')."\n";
//echo normalyzeName('нИКОЛАЕВИЧ')."\n";
//echo normalyzeName('арбеньев-александр-николаевич')."\n";
//echo normalyzeName('арбеньев')."\n";
//echo normalyzeName('александр')."\n";
//echo normalyzeName('николаевич')."\n";
