<?php

include_once 'LibraryIncluder.php';

use analib\Core\Xml\Fb2\FB2Informer;
use analib\Core\Xml\Fb2\FB2Sequence;
use analib\Util\Translit;

LibraryIncluder::includeAnalib();

define('SETTING_INPUT_DIRECTORY', 'indir');
define('SETTING_OUTPUT_DIRECTORY', 'outdir');
define('SETTING_MODE_DIRECTORY', 'modeDir');
define('SETTING_MODE_FILE', 'modeFile');
define('SETTING_KEEP_SOURCE_FILE', 'keepSource');
define('SETTING_MAKE_AUTHOR_FOLDER', 'makeAuthor');
define('SETTING_MAKE_SERIES_FOLDER', 'makeSeries');
define('SETTING_MAKE_PUBLISH_FOLDER', 'makePublish');

define('PARAM_INPUT_DIRECTORY_FULL', 'input');
define('PARAM_INPUT_DIRECTORY_SHORT', 'i');
define('PARAM_OUTPUT_DIRECTORY_FULL', 'output');
define('PARAM_OUTPUT_DIRECTORY_SHORT', 'o');
define('PARAM_MODE_DIRECTORY_FULL', 'dir-mode');
define('PARAM_MODE_DIRECTORY_SHORT', 'd');
define('PARAM_MODE_FILE_FULL', 'file-mode');
define('PARAM_MODE_FILE_SHORT', 'f');
define('PARAM_KEEP_SOURCE_FILE_FULL', 'keep-source');
define('PARAM_KEEP_SOURCE_FILE_SHORT', 'k');
define('PARAM_MAKE_AUTHOR_FOLDER_FULL', 'make-author');
define('PARAM_MAKE_AUTHOR_FOLDER_SHORT', 'a');
define('PARAM_MAKE_SERIES_FOLDER_FULL', 'make-series');
define('PARAM_MAKE_SERIES_FOLDER_SHORT', 's');
define('PARAM_MAKE_PUBLISH_FOLDER_FULL', 'make-publish');
define('PARAM_MAKE_PUBLISH_FOLDER_SHORT', 'p');

define('MULTI_AUTOR_FOLDER', 'Сборник');
define('MULTI_AUTOR_FOLDER_NUM', '3');

$denyMap = array(
    '\'' => '_',
    '"'  => '``',
    '\\' => '_',
    '/'  => '_',
    '|'  => '_',
    '?'  => '',
//	' ' => '_',
    ':'  => '_',
    '*'  => '_',
);

/* * *****************************************************************************
 * Классы
 * ***************************************************************************** */

class Settings
{

    static protected $settings = array(
        SETTING_INPUT_DIRECTORY     => './',
        SETTING_OUTPUT_DIRECTORY    => '',
        SETTING_MODE_DIRECTORY      => 0777,
        SETTING_MODE_FILE           => 0666,
        SETTING_KEEP_SOURCE_FILE    => false,
        SETTING_MAKE_AUTHOR_FOLDER  => false,
        SETTING_MAKE_SERIES_FOLDER  => false,
        SETTING_MAKE_PUBLISH_FOLDER => false,
    );

    static public function init()
    {
        self::$settings[SETTING_INPUT_DIRECTORY]  = realpath('./');
        self::$settings[SETTING_OUTPUT_DIRECTORY] = realpath('./');
    }

    static public function getSetting($name)
    {
        if (isset(self::$settings[$name])) {
            return self::$settings[$name];
        } else {
            return null;
        }
    }

    static public function setSetting($name, $value)
    {
        self::$settings[$name] = $value;
    }

    static public function getAll()
    {
        return self::$settings;
    }

}

/* * *****************************************************************************
 * Функции
 * ***************************************************************************** */

function DenySymbols($string)
{
    global $denyMap;
    $out = '';
    for ($i = 0; $i < mb_strlen($string, 'utf-8'); $i++) {
        $symbol = mb_substr($string, $i, 1, 'utf-8');
        if (isset($denyMap[$symbol])) {
            $out .= $denyMap[$symbol];
        } else {
            $out .= $symbol;
        }
    }
    $out = trim($out, '_');
    if (!$out) {
        $out = '_';
    }
    return $out;
}

/**
 * Обход всего дерева папки
 * @param string $in
 * @param string $out
 */
function folderInspect($in, $out)
{
    $in  = realpath($in);
    $out = realpath($out);
    if (
        file_exists($in) &&
        is_dir($in) &&
        is_readable($in)
    ) {
        if (!file_exists($out)) {
            mkdir($out);
            chmod($out, Settings::getSetting(SETTING_MODE_DIRECTORY));
        }
        $handle = opendir($in);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if (substr($file, 0, 1) != '.') {
                    $oldFileName = $in . DIRECTORY_SEPARATOR . $file;
                    if (!is_dir($oldFileName)) {
                        if (substr($file, -4) == '.fb2') {
                            $fb2    = FB2Informer::create($file);
                            $name   = DenySymbols(trim($fb2->bookTitle()));
                            $folder = $out;
                            if (Settings::getSetting(SETTING_MAKE_AUTHOR_FOLDER)) {
                                $authors = $fb2->authors();
                                $a       = array();
                                foreach ($authors as $author) {
                                    /* @var $author FB2Author */
                                    $a[] = $author->toString();
                                }
                                if (count($a) > MULTI_AUTOR_FOLDER_NUM) {
                                    $folder = $folder . DS . MULTI_AUTOR_FOLDER;
                                } else {
                                    $a      = array_unique($a);
                                    sort($a);
                                    $folder = $folder . DS . DenySymbols(implode(', ', $a));
                                }
                                if (!file_exists($folder)) {
                                    echo $folder . "\n";
                                    mkdir($folder);
                                    chmod($folder, Settings::getSetting(SETTING_MODE_DIRECTORY));
                                }
                            }
                            if (Settings::getSetting(SETTING_MAKE_SERIES_FOLDER)) {
                                /* @var $sequence FB2Sequence */
                                $sequence = $fb2->sequence();
                                if ($sequence->getName()) {
                                    $folder = $folder . DS . DenySymbols($sequence->getName());
                                    if (!file_exists($folder)) {
                                        echo $folder . "\n";
                                        mkdir($folder);
                                        chmod($folder, Settings::getSetting(SETTING_MODE_DIRECTORY));
                                    }
                                    if ($sequence->getNumber()) {
                                        $name = $sequence->getNumber() . '.' . $name;
                                    }
                                }
                            }
                            if (Settings::getSetting(SETTING_MAKE_PUBLISH_FOLDER)) {
                                /* @var $sequence FB2Sequence */
                                $sequence = $fb2->sequencePublish();
                                if ($sequence->getName()) {
                                    $folder = $folder . DS . DenySymbols($sequence->getName());
                                    if (!file_exists($folder)) {
                                        echo $folder . "\n";
                                        mkdir($folder);
                                        chmod($folder, Settings::getSetting(SETTING_MODE_DIRECTORY));
                                    }
                                    if ($sequence->getNumber()) {
                                        $name = $sequence->getNumber() . '.' . $name;
                                    }
                                }
                            }

                            $name = trim($name);

                            $fileName = $folder . DS . Translit::clearDenySymbols($name) . '.fb2';

                            if (($name != '_') && ($fileName != $oldFileName) && ($fileName != '.fb2')) {
                                $fileNum = 0;
                                do {
                                    if (file_exists($fileName)) {
                                        if (md5_file($fileName) == md5_file($oldFileName)) {
                                            unlink($oldFileName);
                                        } else {
//											$name .= '_';
                                            $fileName = $folder . DS . $name . ($fileNum ? '.n(' . $fileNum . ')' : '') . '.fb2';
                                        }
                                    }
                                    $fileNum++;
                                } while (file_exists($fileName) && file_exists($oldFileName));

                                if (file_exists($oldFileName)) {
                                    if (Settings::getSetting(SETTING_KEEP_SOURCE_FILE)) {
                                        copy($file, $fileName);
                                    } else {
                                        rename($file, $fileName);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            closedir($handle);
        }
    }
}

/* * *****************************************************************************
 * Главный модуль
 * ***************************************************************************** */

$shortOptions = "i::o::d::f::k::asp";
$longOptions  = array(
    'input::',
    'output::',
    'dir-mode::',
    'file-mode::',
    'keep-source::',
    'make-author',
    'make-series',
    'make-publish',
);
$options      = getopt($shortOptions, $longOptions);

Settings::init();

foreach ($options as $name => $value) {
    switch ($name) {
        case PARAM_INPUT_DIRECTORY_SHORT:
        case PARAM_INPUT_DIRECTORY_FULL:
            if ($value) {
                Settings::setSetting(SETTING_INPUT_DIRECTORY, realpath($value));
            }
            break;
        case PARAM_OUTPUT_DIRECTORY_SHORT:
        case PARAM_OUTPUT_DIRECTORY_FULL:
            if ($value) {
                Settings::setSetting(SETTING_OUTPUT_DIRECTORY, realpath($value));
            }
            break;
        case PARAM_MODE_DIRECTORY_SHORT:
        case PARAM_MODE_FILE_FULL:
            if ($value) {
                Settings::setSetting(SETTING_MODE_DIRECTORY, octdec($value));
            }
            break;
        case PARAM_MODE_FILE_SHORT:
        case PARAM_MODE_DIRECTORY_FULL:
            if ($value) {
                Settings::setSetting(SETTING_MODE_FILE, octdec($value));
            }
            break;
        case PARAM_KEEP_SOURCE_FILE_FULL:
        case PARAM_KEEP_SOURCE_FILE_SHORT:
            Settings::setSetting(SETTING_KEEP_SOURCE_FILE, true);
            break;
        case PARAM_MAKE_AUTHOR_FOLDER_FULL:
        case PARAM_MAKE_AUTHOR_FOLDER_SHORT:
            Settings::setSetting(SETTING_MAKE_AUTHOR_FOLDER, true);
            break;
        case PARAM_MAKE_SERIES_FOLDER_FULL:
        case PARAM_MAKE_SERIES_FOLDER_SHORT:
            Settings::setSetting(SETTING_MAKE_SERIES_FOLDER, true);
            break;
        case PARAM_MAKE_PUBLISH_FOLDER_FULL:
        case PARAM_MAKE_PUBLISH_FOLDER_SHORT:
            Settings::setSetting(SETTING_MAKE_PUBLISH_FOLDER, true);
            break;
    }
}

if (!Settings::getSetting(SETTING_OUTPUT_DIRECTORY)) {
    Settings::setSetting(SETTING_OUTPUT_DIRECTORY, Settings::getSetting(SETTING_INPUT_DIRECTORY));
}
folderInspect(Settings::getSetting(SETTING_INPUT_DIRECTORY), Settings::getSetting(SETTING_OUTPUT_DIRECTORY));
