<?php

$denyNames    = [
    'BOOMINFO.ORG'             => 'BOOMINFO.ORG',
    'Boominfo.ORG'             => 'Boominfo.ORG',
    'BOOMINFO.RU'              => 'BOOMINFO.RU',
    '@coursenav'               => '@coursenav',
    'eground.org'              => 'eground.org',
    'Eground.pro'              => 'Eground.pro',
    'Geekbrains'               => 'Geekbrains',
    'infobiza.net'             => 'infobiza.net',
    'Infosklad.org'            => 'Infosklad.org',
    'InfoViru$.BiZ'            => 'InfoViru$.BiZ',
    'InfoViruS.BiiZ'           => 'InfoViruS.BiiZ',
    'infoVirus.BiZ'            => 'infoVirus.BiZ',
    'infoVirus.Biz'            => 'infoVirus.Biz',
    'InfoViruS.BiZ'            => 'InfoViruS.BiZ',
    'izibizi'                  => 'izibizi',
    'KURSOMAN'                 => 'KURSOMAN',
    'megasliv.biz'             => 'megasliv.biz',
    'MEGASLIV.BIZ'             => 'MEGASLIV.BIZ',
    'Megasliv'                 => 'Megasliv',
    'no-reply@slifki.info'     => 'slifki.info',
    'openssource.biz'          => 'openssource.biz',
    '@PumpTheMind'             => '@PumpTheMind',
    'sharewood.band'           => 'sharewood.band',
    'sharewoodbiz.com'         => 'sharewoodbiz.com',
    'sharewood.biz'            => 'sharewood.biz',
    'sharewood.wtf'            => 'sharewood.wtf',
    'sharewood-zerkalo.online' => 'sharewood-zerkalo.online',
    '@sharksclub'              => '@sharksclub',
    'SilaSliva.biz'            => 'SilaSliva.biz',
    'slifki.info'              => 'slifki.info',
    'slivoman.com'             => 'slivoman.com',
    '@slivoman'                => '@slivoman',
    'SLIV.ONE'                 => 'SLIV.ONE',
    '@Sliv\'Project'           => '@Sliv\'Project',
    'slivysklad.com'           => 'slivysklad.com',
    '@slivytg'                 => '@slivytg',
    'sliwbl.biz'               => 'sliwbl.biz',
    'SSL.BAND'                 => 'SSL.BAND',
    'Stepik'                   => 'Stepik',
    'supersliv.biz'            => 'supersliv.biz',
    'SuperSliv.biz'            => 'SuperSliv.biz',
    'SuperSliv.BiZ'            => 'SuperSliv.BiZ',
    'SuperSliv.Biz'            => 'SuperSliv.Biz',
    'Supersliv.biz'            => 'Supersliv.biz',
    'SWBAND.CO'                => 'SWBAND.CO',
    'SW.BAND'                  => 'SW.BAND',
    'TexTerra'                 => 'TexTerra',
    '@Tlgrm_University'        => '@Tlgrm_University',
    'TU'                       => 'TU',
    'www.sharewood.biz'        => 'www.sharewood.biz',
    'www.slifki.info'          => 'www.slifki.info',
    'WWW.SLIFKI.INFO'          => 'WWW.SLIFKI.INFO',
    'Биржа Знаний'             => 'Биржа Знаний',
    'Отборные Сливы'           => 'Отборные Сливы',
    'отборные сливы'           => 'отборные сливы',
    'Скачано с boominfo.org'   => 'Скачано с boominfo.org',
];
$denyNames2   = [
    'www.sharewood.biz -' => 'www.sharewood.biz -',
    '-'                   => '-',
];
$denySymbols  = [
    chr(194),
    chr(133),
    chr(135),
    "\n",
    "\r",
];
$hashFileName = 'clf.hash';
$nameFileName = 'clf.name';
$hashes       = [];
$names        = [];

function clearOneFile($file)
{
    global $denyNames;
    global $denyNames2;
    foreach ($denyNames as $deny) {
        if (strpos(trim($file), '[' . $deny . ']') === 0) {
            $file = trim(substr(trim($file), strlen($deny) + 2));
        }
    }
    foreach ($denyNames2 as $deny) {
        if (strpos(trim($file), $deny) === 0) {
            $file = trim(substr(trim($file), strlen($deny)));
        }
    }
    return $file;
}

function clearFilesInDir($dirPath)
{
    global $hashes;
    global $names;
    $dir  = opendir($dirPath);
    while (($file = readdir($dir)) !== false) {
        if (substr($file, 0, 1) == '.') {
            continue;
        }
        if (is_dir($dirPath . DIRECTORY_SEPARATOR . $file)) {
            clearFilesInDir($dirPath . DIRECTORY_SEPARATOR . $file);
        }
        $newFile  = clearOneFile($file);
        $filename = $dirPath . DIRECTORY_SEPARATOR . $newFile;
        if ($newFile != $file) {
            rename($dirPath . DIRECTORY_SEPARATOR . $file, $filename);
        }
        if (is_file($filename)) {
            if (array_search($newFile, $names) !== FALSE) {
                unlink($filename);
                echo $filename . " - has been removed because of its name\n";
            } else {
                $fileExt = explode('.', $file);
                $ext     = strtolower(end($fileExt));
                if (isset($hashes[$ext])) {
                    $md5 = md5_file($dirPath . DIRECTORY_SEPARATOR . $newFile);
                    if (array_search($md5, $hashes[$ext]) !== FALSE) {
                        unlink($filename);
                        echo $filename . " - has been removed because of its hash\n";
                    }
                }
            }
        }
    }
}

function symbols1($dirPath)
{
    global $denySymbols;
    $dir  = opendir($dirPath);
    $s    = [];
    while (($file = readdir($dir)) !== false) {
        if (substr($file, 0, 1) == '.') {
            continue;
        }
        $s[$file] = str_replace($denySymbols, '', $file);
    }
    print_r($s);
}

function symbols2()
{
    $s  = file('Text.txt');
    $ss = [];
    for ($i = 0; $i < count($s); $i++) {
        $ss[$i] = [];
        for ($j = 0; $j < strlen($s[$i]); $j++) {
            $ss[$i][$j] = ord(substr($s[$i], $j, 1));
        }
    }
    print_r($ss);
}

function symbols3($dirPath)
{
    global $denySymbols;
    $dir  = opendir($dirPath);
    while (($file = readdir($dir)) !== false) {
        if (substr($file, 0, 1) == '.') {
            continue;
        }
        if (is_dir($dirPath . DIRECTORY_SEPARATOR . $file)) {
            symbols3($dirPath . DIRECTORY_SEPARATOR . $file);
        } else {
            $newFile = str_replace($denySymbols, '', $file);
            if ($newFile != $file) {
                echo $file . '=>' . $newFile . "\n";
                rename($dirPath . DIRECTORY_SEPARATOR . $file, $dirPath . DIRECTORY_SEPARATOR . $newFile);
            }
        }
    }
}

$scriptPath = dirname(__FILE__);
$hashFile   = file($scriptPath . DIRECTORY_SEPARATOR . $hashFileName);
foreach ($hashFile as $row) {
    $rowAr = explode('=', trim($row));
    if (!isset($hashes[$rowAr[0]])) {
        $hashes[$rowAr[0]] = [];
    }
    $hashes[$rowAr[0]][] = trim($rowAr[1]);
}
$nameFile = file($scriptPath . DIRECTORY_SEPARATOR . $nameFileName);
foreach ($nameFile as $row) {
    $names[] = trim($row);
}

$dirPath = realpath('./');
clearFilesInDir($dirPath);
//symbols3($dirPath);
//symbols2();
