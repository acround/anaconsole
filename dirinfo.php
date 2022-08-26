<?php

$startDir = realpath('./');
$csv      = array();
$csv[]    = implode(';', array(
    'Папка',
    'Файл',
    'Название',
    'Название оригинальное',
    'Год',
    'Номер сезона',
    'Название сезона',
    'Название сезона оригинальное',
    'Фильм',
    'Название фильма',
    'Название фильма оригинальное',
    'Серия',
    'Название серии',
    'Название серии оригинальное',
    'Страна',
    'Студия',
    'Качество',
    'Тип',
    'Размер',
    ));

function readVideoDir($dirStr)
{
    $folders = array();
    $files   = array();
    $dir     = opendir($dirStr);
    if ($dir) {
        while ($file = readdir($dir)) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            if (is_dir($dirStr . DIRECTORY_SEPARATOR . $file)) {
                $folders[$file] = array();
            } else {
                $files[] = $dirStr . DIRECTORY_SEPARATOR . $file;
            }
        }
    }
    ksort($folders);
    sort($files);
    foreach ($folders as $name => $content) {
        $folders[$name] = readVideoDir($dirStr . DIRECTORY_SEPARATOR . $name);
    }
    return array(
        'folders' => $folders,
        'files'   => $files,
    );
}

function parseFileName($fileName, $isFolder)
{
    global $startDir;
    $sizeNames           = array(
        ' Б',
        ' КБ',
        ' МБ',
        ' ГБ',
    );
    $ret                 = array(
        'filepath'             => '',
        'relativepath'         => '',
        'filename'             => '',
        'title'                => '',
        'title-original'       => '',
        'year'                 => '',
        'season'               => '',
        'season-name'          => '',
        'season-name-original' => '',
        'movie'                => '',
        'movie-name'           => '',
        'movie-name-original'  => '',
        'serie'                => '',
        'serie-name'           => '',
        'serie-name-original'  => '',
        'country'              => '',
        'production'           => '',
        'capacity'             => '',
        'type'                 => '',
        'size'                 => '',
    );
    $ret['filepath']     = dirname($fileName);
    $ret['relativepath'] = substr($ret['filepath'], strlen($startDir));
    $ret['filename']     = basename($fileName);
    $prs                 = explode('.', $ret['filename']);
    if (!$isFolder) {
        $ret['type'] = array_pop($prs);
        $size        = filesize($fileName);
        reset($sizeNames);
        if ($size > 1000) {
            while ($size > 1000) {
                $size = round($size / 1000, 1);
                next($sizeNames);
            }
        }
        $ret['size'] = $size . current($sizeNames);
    }
    $title           = array();
    $titleOrig       = array();
    $titleSeason     = array();
    $titleSeasonOrig = array();
    $titleSerie      = array();
    $titleSerieOrig  = array();
    $titleMovie      = array();
    $titleMovieOrig  = array();
    $titleType       = 1;
    $year            = $movie           = $season          = $serie           = $country         = $production      = $capacity        = null;
    for ($i = 0; $i < count($prs); $i++) {
        $cell = trim($prs[$i]);
        if (preg_match('/^\d\d\d\d$/', $cell)) {
            $year = $cell;
        } elseif (preg_match('/^s\d{1,3}$/', $cell)) {
            $titleType = 2;
            $season    = substr($cell, 1);
        } elseif (preg_match('/^\d{1,3}$/', $cell)) {
            $titleType = 3;
            $serie     = $cell;
        } elseif (preg_match('/^e\d{1,3}$/', $cell)) {
            $titleType = 3;
            $serie     = substr($cell, 1);
        } elseif (preg_match('/^m\d{1,3}$/', $cell)) {
            $titleType = 4;
            $movie     = substr($cell, 1);
        } elseif (preg_match('/^{.*}$/', $cell)) {
            $pr      = explode('-', trim($cell, '{}'), 2);
            $country = $pr[0];
            if (count($pr) > 1) {
                $production = $pr[1];
            } else {
                $production = null;
            }
        } elseif (preg_match('/^\[.*\]$/', $cell)) {
            $pr       = trim($cell, '[]');
            $capacity = $pr;
        } else {
            $orig = substr($cell, 0, 1) == '@';
            switch ($titleType) {
                case 1:
                    if ($orig) {
                        $titleOrig[] = substr($cell, 1);
                    } else {
                        $title[] = $cell;
                    }
                    break;
                case 2:
                    if ($orig) {
                        $titleSeasonOrig[] = substr($cell, 1);
                    } else {
                        $titleSeason[] = $cell;
                    }
                    break;
                case 3:
                    if ($orig) {
                        $titleSerieOrig[] = substr($cell, 1);
                    } else {
                        $titleSerie[] = $cell;
                    }
                    break;
                case 4:
                    if ($orig) {
                        $titleMovieOrig[] = substr($cell, 1);
                    } else {
                        $titleMovie[] = $cell;
                    }
                    break;
            }
        }
    }
    $ret['title'] = implode('. ', $title);
    if ($titleOrig) {
        $ret['title-original'] = implode('. ', $titleOrig);
    }
    if ($year) {
        $ret['year'] = $year;
    }
    if ($season) {
        $ret['season'] = $season;
    }
    if ($titleSeason) {
        $ret['season-name'] = implode('. ', $titleSeason);
    }
    if ($titleSeasonOrig) {
        $ret['season-name-original'] = implode('. ', $titleSeasonOrig);
    }
    if ($movie) {
        $ret['movie'] = $movie;
    }
    if ($titleMovie) {
        $ret['movie-name'] = implode('. ', $titleMovie);
    }
    if ($titleMovieOrig) {
        $ret['movie-name-original'] = implode('. ', $titleMovieOrig);
    }
    if ($serie) {
        $ret['serie'] = $serie;
    }
    if ($titleSerie) {
        $ret['serie-name'] = implode('. ', $titleSerie);
    }
    if ($titleSerieOrig) {
        $ret['serie-name-original'] = implode('. ', $titleSerieOrig);
    }
    if ($country) {
        $ret['country'] = $country;
    }
    if ($production) {
        $ret['production'] = $production;
    }
    if ($capacity) {
        $ret['capacity'] = $capacity;
    }
    if ($isFolder) {
        if (
            (boolean) $ret['title-original'] ||
            (boolean) $ret['year'] ||
            (boolean) $ret['season'] ||
            (boolean) $ret['season-name'] ||
            (boolean) $ret['season-name-original'] ||
            (boolean) $ret['movie'] ||
            (boolean) $ret['movie-name'] ||
            (boolean) $ret['movie-name-original'] ||
            (boolean) $ret['serie'] ||
            (boolean) $ret['serie-name'] ||
            (boolean) $ret['serie-name-original'] ||
            (boolean) $ret['country'] ||
            (boolean) $ret['production'] ||
            (boolean) $ret['capacity']
        ) {
            $ret['type'] = 'movie';
        } else {
            $ret['type'] = 'genre';
        }
    }
    return $ret;
}

function arr2xml(array $arr, DOMDocument $xml = null, DOMNode $node = null)
{
    global $csv;
    if (!$xml) {
        $xml  = new DOMDocument('1.0', 'utf-8');
        $xml->appendChild($xml->createElement('filesystem'));
        $node = $xml->firstChild;
    }
    foreach ($arr['folders'] as $name => $content) {
        $fileParsed = parseFileName($name, true);
        $folderX    = $xml->createElement($fileParsed['type']);
        $folderX->setAttribute('name', $name);
        $node->appendChild($folderX);
        arr2xml($content, $xml, $folderX);
    }
    foreach ($arr['files'] as $file) {
        $fileParsed = parseFileName($file, false);
        $fileX      = $xml->createElement('file');
        $fileX->setAttribute('filename', $fileParsed['filename']);
        $fileX->setAttribute('title', $fileParsed['title']);
        if ($fileParsed['title-original']) {
            $fileX->setAttribute('title-original', $fileParsed['title-original']);
        }
        if ($fileParsed['year']) {
            $fileX->setAttribute('year', $fileParsed['year']);
        }
        if ($fileParsed['season']) {
            $fileX->setAttribute('season', $fileParsed['season']);
        }
        if ($fileParsed['season-name']) {
            $fileX->setAttribute('season-name', $fileParsed['season-name']);
        }
        if ($fileParsed['season-name-original']) {
            $fileX->setAttribute('season-name-original', $fileParsed['season-name-original']);
        }
        if ($fileParsed['movie']) {
            $fileX->setAttribute('movie', $fileParsed['movie']);
        }
        if ($fileParsed['movie-name']) {
            $fileX->setAttribute('movie-name', $fileParsed['movie-name']);
        }
        if ($fileParsed['movie-name-original']) {
            $fileX->setAttribute('movie-name-original', $fileParsed['movie-name-original']);
        }
        if ($fileParsed['serie']) {
            $fileX->setAttribute('serie', $fileParsed['serie']);
        }
        if ($fileParsed['serie-name']) {
            $fileX->setAttribute('serie-name', $fileParsed['serie-name']);
        }
        if ($fileParsed['serie-name-original']) {
            $fileX->setAttribute('serie-name-original', $fileParsed['serie-name-original']);
        }
        if ($fileParsed['country']) {
            $fileX->setAttribute('country', $fileParsed['country']);
        }
        if ($fileParsed['production']) {
            $fileX->setAttribute('production', $fileParsed['production']);
        }
        if ($fileParsed['capacity']) {
            $fileX->setAttribute('capacity', $fileParsed['capacity']);
        }
        $fileX->setAttribute('type', $fileParsed['type']);
        $fileX->setAttribute('size', $fileParsed['size']);
        unset($fileParsed['filepath']);
        $csv[] = implode(';', $fileParsed);

        $node->appendChild($fileX);
    }
    return $xml;
}

$dir = realpath('./');

$arr               = readVideoDir($dir);
$xml               = arr2xml($arr);
$xml->formatOutput = true;
file_put_contents('movies.csv', implode("\n", $csv));

echo $xml->saveXML();

echo "\n";
