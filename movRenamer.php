<?php

include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib.php');
$movieExts    = 'mp4,avi,mkv';
$onlyVideo    = false;
$contFile     = 'cont.txt';
$newFile      = 'newnames.txt';
$workDir      = realpath('./');
$shortOptions = "v:e::d::i::";
$longOptions  = array(
    'only-video::',
    'movie-exts::',
    'dir::',
    'imitation::',
);
$options      = getopt($shortOptions, $longOptions);

print_r($options);

if (isset($options['v'])) {
    $onlyVideo = true;
} elseif (isset($options['only-video'])) {
    $onlyVideo = true;
}

if (isset($options['e']) && $options['e']) {
    $movieExts = $options['e'];
} elseif (isset($options['movie-exts']) && $options['movie-exts']) {
    $movieExts = $options['movie-exts'];
}

if (isset($options['d']) && $options['d']) {
    $workDir = $options['d'];
} elseif (isset($options['dir']) && $options['dir']) {
    $workDir = $options['dir'];
}
$workDir = realpath($workDir);

$imitation = false;
if (isset($options['i'])) {
    $imitation = true;
} elseif (isset($options['imitation'])) {
    $imitation = true;
}

$movieExts = explode(',', preg_replace('~[^[:alpha:][:digit:],]+~', '', strtolower($movieExts)));
$contFile  = $workDir . DIRECTORY_SEPARATOR . $contFile;
$newFile   = $workDir . DIRECTORY_SEPARATOR . $newFile;

$error = false;
if (!file_exists($workDir) || !is_dir($workDir)) {
    $error = true;
    echo "Work directory - not exists\n";
}
if (!file_exists($newFile) && !$numeration) {
    $error = true;
    echo $newFile . "\n";
    echo "New names file - not exists\n";
    file_put_contents($newFile, '');
}
if ($error) {
    exit;
}

if (file_exists($contFile)) {
    $fileList = file($contFile);
    for ($i = 0; $i < count($fileList); $i++) {
        $fileList[$i] = $workDir . DIRECTORY_SEPARATOR . trim($fileList[$i]);
    }
} else {
    $fileList = array();
    $dir      = opendir($workDir);
    while (($file     = readdir($dir)) !== false) {
        if (in_array(getExtension($file), $movieExts)) {
            $fileList[] = $workDir . DIRECTORY_SEPARATOR . $file;
        }
    }
    closedir($dir);
    sort($fileList);
}
$nameList = file($newFile);
for ($i = 0; $i < count($nameList); $i++) {
    $nameList[$i] = $workDir . DIRECTORY_SEPARATOR . trim($nameList[$i]);
}
$k = count($fileList);
if ($k > count($nameList)) {
    $k = count($nameList);
}

for ($i = 0; $i < $k; $i++) {
    if ($imitation) {
        echo basename($fileList[$i]) . ' => ' . basename($nameList[$i]) . "\n";
    } else {
        if (file_exists($fileList[$i]) && !file_exists($nameList[$i])) {
//			echo 'rename:' . $fileList[$i] . ' => ' . $nameList[$i] . "\n";
            rename($fileList[$i], addExtension($nameList[$i], getExtension($fileList[$i])));
        }
    }
}
