<?php

/**
 * Description of pixRenamer
 *
 * @author acround
 */
class pixRenamer
{

    const MODE_COPY       = 0;
    const MODE_RENAME     = 1;
    const METHOD_ALL      = 0;
    const METHOD_EXIF     = 1;
    const METHOD_FILETIME = 2;
    const METHOD_FILENAME = 3;
    const METHOD_FOLDERS  = 4;

    static protected $finfo    = null;
    static protected $settings = array(
        'in'     => null,
        'out'    => null,
        'mode'   => null,
        'method' => null,
    );

    public function __construct()
    {
        self::init();
    }

    static public function init()
    {
        self::$finfo = finfo_open(FILEINFO_MIME_TYPE);
        self::defaultSettings();
    }

    static protected function defaultSettings()
    {
        self::$settings['in']     = self::$settings['out']    = realpath('./');
        self::$settings['mode']   = self::MODE_RENAME;
        self::$settings['method'] = self::METHOD_ALL;
    }

    static public function mode($mode = null)
    {
        if ($mode !== null) {
            self::$settings['mode'] = (int) (boolean) ($mode);
        }
        return self::$settings['mode'];
    }

    static public function getExtension($fileName)
    {
        return end(explode('.', $fileName));
    }

    static public function isImage($filename)
    {
        $fileInfo  = finfo_file(self::$finfo, $fileName, FILEINFO_MIME_TYPE);
        $infoSplit = explode('/', $fileInfo);
        return $infoSplit[0] == 'image';
    }

    static public function getDateByExif($filename)
    {
        if (self::isImage($filename)) {
            $exif = exif_read_data($filename, 0, true);
            if (isset($exif['EXIF']['DateTimeOriginal'])) {
                $dateTime = explode(' ', $exif['EXIF']['DateTimeOriginal']);
                $date     = str_replace(':', '-', $dateTime[0]);
                $time     = str_replace(':', '', $dateTime[1]);
                $name     = $date . '.' . $time . strtolower(self::getExtension($fileName));
            } else {
                $name = null;
            }
        } else {
            $name = false;
        }
        return $name;
    }

    static public function getDateByFileTime($filename)
    {
        if (self::isImage($filename)) {
            $name = date("Y-m-d.Hms", filemtime($name));
        } else {
            $name = false;
        }
        return $name;
    }

    static public function getDateByFileName($filename)
    {
        if (self::isImage($filename)) {
            $fileNukedName = substr($fileName, 0, strpos($fileName, '.'));
            $ext           = getExtension1($fileName);
            $year          = substr($fileNukedName, 0, 4);
            $month         = substr($fileNukedName, 4, 2);
            $day           = substr($fileNukedName, 6, 2);
            $number        = substr($fileNukedName, 9);
            $name          = $year . '-' . $month . '-' . $day . '.' . $number . '.' . $ext;
        } else {
            $name = false;
        }
        return $name;
    }

    static public function getDateByFolders($filename)
    {

    }

    protected function scanDirImageRename($workDirName, array $date, array $suffix, $dir = null, $outDir = null)
    {
        if (!$outDir) {
            $outDir = realpath('./');
        }
        if (!$dir) {
            $dir = realpath('./');
        }
        if ($dir) {
            if ((count($suffix) == 0) && ($dir == (string) (int) $dir)) {
                $date[] = $dir;
            } else {
                $suffix[] = $dir;
            }
        }
        $currentDirName = implode(DIRECTORY_SEPARATOR, array_merge(array($workDirName), $date, $suffix));
        $dirFileName    = '';
        if (count($date)) {
            $dirFileName = implode('-', $date);
        }
        if (count($suffix)) {
            if ($dirFileName) {
                $dirFileName .= '.(';
            }
            $dirFileName .= implode('.', $suffix) . ')';
        }
        $dirFileName = str_replace(' ', '_', $dirFileName);
        $currentDir  = opendir($currentDirName);
        $finfo       = finfo_open(FILEINFO_MIME_TYPE);
        $names       = array();
        while (($fileName    = readdir($currentDir)) !== false) {
            if (substr($fileName, 0, 1) == '.') {
                continue;
            }
            if (is_dir($currentDirName . DIRECTORY_SEPARATOR . $fileName)) {
                scanDirImageRename($workDirName, $date, $suffix, $fileName, $outDir);
            } else {
                $fileInfo  = finfo_file($finfo, $currentDirName . DIRECTORY_SEPARATOR . $fileName, FILEINFO_MIME_TYPE);
                $infoSplit = explode('/', $fileInfo);
                if ($infoSplit[0] == 'image') {
                    $names[] = $fileName;
                }
            }
        }
        sort($names);
        closedir($currentDir);
        foreach ($names as $num => $name) {
            $number = $num + 1;
            while (strlen($number) < 3) {
                $number = '0' . $number;
            }
            $newName = $dirFileName . '.' . $number . '.' . getExtension1($name);
            switch (self::$settings['mode']) {
                case self::MODE_COPY:
                    copy($currentDirName . DIRECTORY_SEPARATOR . $name, $outDir . DIRECTORY_SEPARATOR . $newName);
                    break;
                case self::MODE_RENAME:
                    rename($currentDirName . DIRECTORY_SEPARATOR . $name, $outDir . DIRECTORY_SEPARATOR . $newName);
                    break;
            }
        }
    }

}
