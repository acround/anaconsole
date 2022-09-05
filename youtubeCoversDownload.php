<?php

define('QUIT', '\q');
define('IMAGE_URL', 'https://i3.ytimg.com/vi/');

include_once 'LibraryIncluder.php';

use analib\Core\System\User;
use analib\Util\Translit;

LibraryIncluder::includeAnalib();

$imageNames = [
    'maxresdefault',
    'hqdefault',
];
$titleNameEnd = ' - YouTube';

function videoCover($url, $query)
{
    global $imageNames;
    global $titleNameEnd;
    global $outDir;
    parse_str($query, $result);
    $code = $result['v'];
    $imageName = '';
    foreach ($imageNames as $imageNameTmp) {
        $imageName = $imageNameTmp;
        $videoUrl = IMAGE_URL . $code . '/' . $imageNameTmp . '.jpg';
        $options = [
            CURLOPT_URL => $videoUrl,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
        ];
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $image = curl_exec($curl);
        curl_close($curl);
        if ($image) {
            break;
        }
    }

    if ($image) {
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
        ];
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $html = curl_exec($curl);
        curl_close($curl);
        $pttrn = '~<title>(.*)</title>~';
        preg_match($pttrn, $html, $matches);
        $name = isset($matches[1]) ? $matches[1] : $imageName;

        if (substr($name, -strlen($titleNameEnd)) == $titleNameEnd) {
            $name = Translit::clearDenySymbols(substr($name, 0, strlen($name) - strlen($titleNameEnd)));
        }
        echo "OK\n";
        file_put_contents($outDir . DIRECTORY_SEPARATOR . $name . '.jpg', $image);
    } else {
        echo " Something is wrong...\n";
    }
}

function playListCovers($url, $query)
{
    
}

if ((count($argv) > 1) && (($argv[1] == '-h') || ($argv[1] == '--here'))) {
    $outDir = realpath('./');
} else {
    $userinfo = User::userInfo();
    $outDir = null;
    $homeDir = $userinfo['dir'];
    $iniFile = 'settings';
    $localDir = $homeDir . DIRECTORY_SEPARATOR . 'share' . DIRECTORY_SEPARATOR . 'youtubeCoversDownload';
    if (file_exists($localDir) && is_dir($localDir) && file_exists($localDir . DIRECTORY_SEPARATOR . $iniFile)) {
        $outDir = trim(file_get_contents($localDir . DIRECTORY_SEPARATOR . $iniFile));
    }
    if (empty($outDir)) {
        $outDir = __DIR__;
    }
}

do {
    echo "Url (" . QUIT . "):";
    $url = readline();
    if (($url == '') || ($url == QUIT)) {
        break;
    }


    $arr = parse_url($url);
    if (isset($arr['host']) && $arr['host'] == 'www.youtube.com') {
        switch ($arr['path']) {
            case '/watch':
                echo "Looks like a video. I'll try to get the cover:";
                videoCover($url, $arr['query']);
                break;
            case '/playlist':
                echo "Looks like a playlist. I'll try to get the covers:";
                playListCovers($url, $arr['query']);
                break;
        }
    } else {
        echo "Looks like it is not Youtube. Try one more...\n";
    }
    echo "\n";
} while (true);

echo "Thanks!\n";
