<?php

include '../extensions.php';
define('COMMON_DEFAULT_INPUT_DIR', 'in');
define('COMMON_DEFAULT_OUTPUT_DIR', 'out');
if ($argc > 1) {
    $dirName = $argv[1];
} else {
    $dirName = '';
}
if (file_exists($dirName) && is_dir($dirName)) {
    $workDirName = $dirName;
} else {
    $workDirName = './' . COMMON_DEFAULT_INPUT_DIR;
}
if (!file_exists($workDirName) || !is_dir($workDirName)) {
    echo "Working directory not found\n";
    exit;
}
$currentDir = realpath('./' . COMMON_DEFAULT_INPUT_DIR);
if (!$currentDir) {
    $currentDir = realpath('./');
}
$workDirName = realpath($workDirName);
$workDir     = opendir($workDirName);
$finfo       = finfo_open(FILEINFO_MIME_TYPE);
while (($fileName    = readdir($workDir)) !== false) {
    if (substr($fileName, 0, 1) == '.') {
        continue;
    }
    if (is_dir($fileName)) {
        continue;
    }
    $fileInfo  = finfo_file($finfo, $workDirName . DIRECTORY_SEPARATOR . $fileName, FILEINFO_MIME_TYPE);
    $infoSplit = explode('/', $fileInfo);
    if ($infoSplit[0] == 'image') {
        $fullFileName = $workDirName . DIRECTORY_SEPARATOR . $fileName;
        $exif         = @exif_read_data($fullFileName, 0, true);
        if (isset($exif['EXIF']['DateTimeOriginal'])) {
            $filearray     = explode('.', $fileName);
            $newFileArray  = array();
            $ext           = strtolower(array_pop($filearray));
            $dateTime      = explode(' ', $exif['EXIF']['DateTimeOriginal']);
            $date          = $dateTime[0];
            $time          = $dateTime[1];
            $date          = str_replace(':', '-', $date);
            $time          = str_replace(':', '', $time);
            $dateTimeArray = array(
                $date,
                $time
            );

            $oldDate = isset($filearray[0]) ? $filearray[0] : '';
            if (preg_match('/\d{4}-\d{2}-\d{2}/', $oldDate)) {
                array_shift($filearray);
            }
            $oldTime = isset($filearray[0]) ? $filearray[0] : '';
            if (preg_match('/^\d+$/', $oldTime) || preg_match('/^\[\d+\]$/', $oldTime)) {
                array_shift($filearray);
            }
            if (preg_match('/SAM_\d+/', $oldTime)) {
                array_shift($filearray);
            }
            $newFileArray = array_merge($dateTimeArray, $filearray, array($ext));
            $newFileName  = implode('.', $newFileArray);

            $oldFullFileName = $workDirName . DIRECTORY_SEPARATOR . $fileName;
            $newFullFileName = $currentDir . DIRECTORY_SEPARATOR . $newFileName;

            if ($newFullFileName != $oldFullFileName) {
                if (!file_exists($newFullFileName)) {
                    echo $fileName . ' -> ' . $newFileName . "\n";
                    rename($oldFullFileName, $newFullFileName);
                } else {
                    $n = 0;
                    while (file_exists($newFullFileName)) {
                        $n++;
                        $ns = $n;
                        while (strlen($ns) < 2) {
                            $ns = '0' . $ns;
                        }
                        $newFileArray    = array_merge($dateTimeArray, array($ns), $filearray, array($ext));
                        $newFileName     = implode('.', $newFileArray);
                        $newFullFileName = $currentDir . DIRECTORY_SEPARATOR . $newFileName;
                    }
                    echo $fileName . ' -> ' . $newFileName . "\n";
                    rename($oldFullFileName, $newFullFileName);
                }
            }
        } else {
            echo $fileName . " - no EXIF\n";
        }
    }
}
