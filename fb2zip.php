<?php

/* * *****************************************************************************
 * Глобальные переменные и константы
 * ***************************************************************************** */
define('SETTING_INPUT_DIRECTORY', 'indir');
define('SETTING_OUTPUT_DIRECTORY', 'outdir');
define('SETTING_RECURSIVE', 'recurrent');
define('SETTING_MODE_DIRECTORY', 'modeDir');
define('SETTING_MODE_FILE', 'modeFile');
define('SETTING_DELETE_SOURCE_FILE', 'delSource');

define('PARAM_INPUT_DIRECTORY_FULL', '--input');
define('PARAM_INPUT_DIRECTORY_SHORT', '-i');
define('PARAM_OUTPUT_DIRECTORY_FULL', '--output');
define('PARAM_OUTPUT_DIRECTORY_SHORT', '-o');
define('PARAM_RECURRENT_FULL', '--recurrent');
define('PARAM_RECURRENT_SHORT', '-r');
define('PARAM_NO_RECURRENT_FULL', '--no-recurrent');
define('PARAM_NO_RECURRENT_SHORT', '-R');
define('PARAM_MODE_DIRECTORY_FULL', '--dir-mode');
define('PARAM_MODE_DIRECTORY_SHORT', '-d');
define('PARAM_MODE_FILE_FULL', '--file-mode');
define('PARAM_MODE_FILE_SHORT', '-f');
define('PARAM_DELETE_SOURCE_FILE_FULL', '--delete-source');
define('PARAM_DELETE_SOURCE_FILE_SHORT', '-l');

$translitMap = array(
    'а'  => 'a',
    'б'  => 'b',
    'в'  => 'w',
    'г'  => 'g',
    'д'  => 'd',
    'е'  => 'e',
    'ё'  => 'jo',
    'ж'  => 'hz',
    'з'  => 'z',
    'и'  => 'i',
    'й'  => 'j',
    'к'  => 'k',
    'л'  => 'l',
    'м'  => 'm',
    'н'  => 'n',
    'о'  => 'o',
    'п'  => 'p',
    'р'  => 'r',
    'с'  => 's',
    'т'  => 't',
    'у'  => 'u',
    'ф'  => 'f',
    'х'  => 'h',
    'ц'  => 'c',
    'ч'  => 'ch',
    'ш'  => 'sh',
    'щ'  => 'sch',
    'ъ'  => '',
    'ы'  => 'y',
    'ь'  => '',
    'э'  => 'e',
    'ю'  => 'ju',
    'я'  => 'ja',
    'А'  => 'A',
    'Б'  => 'B',
    'В'  => 'W',
    'Г'  => 'G',
    'Д'  => 'D',
    'Е'  => 'E',
    'Ё'  => 'JO',
    'Ж'  => 'HZ',
    'З'  => 'Z',
    'И'  => 'I',
    'Й'  => 'J',
    'К'  => 'K',
    'Л'  => 'L',
    'М'  => 'M',
    'Н'  => 'N',
    'О'  => 'O',
    'П'  => 'P',
    'Р'  => 'R',
    'С'  => 'S',
    'Т'  => 'T',
    'У'  => 'U',
    'Ф'  => 'F',
    'Х'  => 'H',
    'Ц'  => 'C',
    'Ч'  => 'CH',
    'Ш'  => 'SH',
    'Щ'  => 'SCH',
    'Ъ'  => '',
    'Ы'  => 'Y',
    'Ь'  => '',
    'Э'  => 'E',
    'Ю'  => 'JU',
    'Я'  => 'JA',
    '\'' => '.',
    '"'  => '``',
    '\\' => '.',
    '/'  => '.',
    '|'  => '.',
    '?'  => '',
    ':'  => '-',
    ' '  => '_',
);

$translitAllow = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890()!-=+.,[]{}_';

/* * *****************************************************************************
 * Классы
 * ***************************************************************************** */

class Settings
{

    static protected $settings = array(
        SETTING_INPUT_DIRECTORY  => './',
        SETTING_OUTPUT_DIRECTORY => '',
        SETTING_RECURSIVE        => true,
        SETTING_MODE_DIRECTORY   => 0777,
        SETTING_MODE_FILE        => 0755,
    );

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

}

/* * *****************************************************************************
 * Функции
 * ***************************************************************************** */

/**
 * 	Перевод кириллицы в транслит
 * @param string $string
 * @return string
 */
function Translite($string)
{
    global $translitMap, $translitAllow;
    $out = '';
    for ($i = 0; $i < mb_strlen($string, 'utf-8'); $i++) {
        $symbol = mb_substr($string, $i, 1, 'utf-8');
        if (isset($translitMap[$symbol])) {
            $out .= $translitMap[$symbol];
        } elseif (mb_strpos($translitAllow, $symbol, null, 'utf-8') !== false) {
            $out .= $symbol;
        }
    }
    $out = trim($out, '_');
    return $out;
}

/**
 * Обход всего дерева папки
 * @param string $in
 * @param string $out
 */
function folderInspect($in, $out)
{
    if (
        file_exists($in) &&
        is_dir($in) &&
        is_readable($in)
    ) {
        if (!file_exists($out)) {
            mkdir($out);
            chmod($out, Settings::getSetting(SETTING_MODE_DIRECTORY));
        }
        $recurrent = Settings::getSetting(SETTING_RECURSIVE);
        $handle    = opendir($in);
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if (substr($file, 0, 1) != '.') {
                    if (is_dir($in . DIRECTORY_SEPARATOR . $file)) {
                        if ($recurrent) {
                            $in2  = $in . DIRECTORY_SEPARATOR . $file;
                            $out2 = $out . DIRECTORY_SEPARATOR . $file;
                            folderInspect($in2, $out2);
                        }
                    } else {
                        if (substr($file, -4) == '.fb2') {
                            $newFile = Translite($file);
                            $zip     = new ZipArchive();
                            $r       = $zip->open($out . DIRECTORY_SEPARATOR . $newFile . '.zip', ZipArchive::CREATE);
                            if ($r) {
                                $r = $zip->addFile($in . DIRECTORY_SEPARATOR . $file, $newFile);
                                $r = $zip->close();
                            }
                            if (Settings::getSetting(SETTING_DELETE_SOURCE_FILE)) {
                                @unlink($in . DIRECTORY_SEPARATOR . $file);
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

$arguments = $argv;
array_shift($arguments);
for ($i = 0; $i < count($arguments); $i++) {
    if (substr($arguments[$i], 0, 1) == '-') {
        switch ($arguments[$i]) {
            case PARAM_INPUT_DIRECTORY_SHORT:
            case PARAM_INPUT_DIRECTORY_FULL:
                $i++;
                if (isset($arguments[$i])) {
                    Settings::setSetting(SETTING_INPUT_DIRECTORY, realpath($arguments[$i]));
                }
                break;
            case PARAM_OUTPUT_DIRECTORY_SHORT:
            case PARAM_OUTPUT_DIRECTORY_FULL:
                $i++;
                if (isset($arguments[$i])) {
                    Settings::setSetting(SETTING_OUTPUT_DIRECTORY, realpath($arguments[$i]));
                }
                break;
            case PARAM_RECURRENT_SHORT:
            case PARAM_RECURRENT_FULL:
                Settings::setSetting(SETTING_RECURSIVE, true);
                break;
            case PARAM_NO_RECURRENT_SHORT:
            case PARAM_NO_RECURRENT_FULL:
                Settings::setSetting(SETTING_RECURSIVE, false);
                break;
            case PARAM_MODE_DIRECTORY_SHORT:
            case PARAM_MODE_FILE_FULL:
                $i++;
                if (isset($arguments[$i])) {
                    Settings::setSetting(SETTING_MODE_DIRECTORY, octdec($arguments[$i]));
                }
                break;
            case PARAM_MODE_FILE_SHORT:
            case PARAM_MODE_DIRECTORY_FULL:
                $i++;
                if (isset($arguments[$i])) {
                    Settings::setSetting(SETTING_MODE_FILE, octdec($arguments[$i]));
                }
                break;
            case PARAM_DELETE_SOURCE_FILE_FULL:
            case PARAM_DELETE_SOURCE_FILE_SHORT:
                Settings::setSetting(SETTING_DELETE_SOURCE_FILE, true);
                break;
        }
    }
}

if (!Settings::getSetting(SETTING_OUTPUT_DIRECTORY)) {
    Settings::setSetting(SETTING_OUTPUT_DIRECTORY, Settings::getSetting(SETTING_INPUT_DIRECTORY));
}

if (Settings::getSetting(SETTING_INPUT_DIRECTORY) && Settings::getSetting(SETTING_OUTPUT_DIRECTORY)) {
    folderInspect(Settings::getSetting(SETTING_INPUT_DIRECTORY), Settings::getSetting(SETTING_OUTPUT_DIRECTORY));
} else {
    echo "Укажите директории\n";
}
