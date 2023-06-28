<?php

$logoDirName = __DIR__ . DIRECTORY_SEPARATOR . 'logo';
$logoSharedDirName = __DIR__ . DIRECTORY_SEPARATOR . 'logo_shared';
$suffix = '_shared';
echo $logoDirName . "\n";
$logoDir = opendir($logoDirName);
if ($logoDir) {
    while (($file = readdir($logoDir)) !== false) {
        if (substr($file, 0, 1) == '.') {
            continue;
        }
        $fullFileName = $logoDirName . DIRECTORY_SEPARATOR . $file;
        $info = getimagesize($fullFileName);
        switch ($info['mime']) {
            case 'image/png':
                $image = imagecreatefrompng($fullFileName);
                break;
            case 'image/jpeg':
                $image = imagecreatefromjpeg($fullFileName);
                break;
        }
        if (isset($image)) {
            $imageShared = imagecreatetruecolor(1200, 630);
            $white = imagecolorallocate($imageShared, 255, 255, 255);
            imagefilltoborder($imageShared, 0, 0, $white, $white);
            imagecopyresized($imageShared, $image, 320, 35, 0, 0, 560, 560, $info[0], $info[1]);
            $imageSharedName = $logoSharedDirName . DIRECTORY_SEPARATOR . pathinfo($fullFileName, PATHINFO_FILENAME) . $suffix . '.jpg';
            imagejpeg($imageShared, $imageSharedName, 90);
//            imagejpeg($image, $imageSharedName);
        }
    }
}
