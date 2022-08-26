<?php

use analib\Core\Xml\Fb2\FB2Informer;
use analib\Core\Xml\Fb2\FB2Author;
use analib\Util\Translit;

include_once 'LibraryIncluder.php';
LibraryIncluder::includeAnalib();

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
        $folder = $out;
        if ($handle) {
            while (false !== ($file = readdir($handle))) {
                if (($file != '.') && ($file != '..')) {
                    $oldFileName = $in . DIRECTORY_SEPARATOR . $file;
                    if (!is_dir($oldFileName)) {
                        if (substr($file, -4) == '.fb2') {
                            $name   = array();
                            $fb2    = FB2Informer::create($file);
                            /* @var $authors FB2Author */
                            $autors = $fb2->authors();
                            if (count($autors) > 5) {
                                $name[] = 'Сборник';
                            } else {
                                for ($i = 0; $i < count($autors); $i++) {
                                    $autors[$i] = $autors[$i]->getLastName();
                                }
                                $name[] = DenySymbols(trim(implode(', ', $autors)));
                            }
                            $sequence = $fb2->sequence();
                            if ($sequence->getName()) {
                                $name[] = DenySymbols($sequence->getName());
                                if ($sequence->getNumber()) {
                                    $name[] = $sequence->getNumber();
                                }
                            }
                            $name[] = DenySymbols(trim($fb2->bookTitle()));
                            $name   = implode('.', $name);

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
                                    rename($file, $fileName);
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

folderInspect(realpath('./'), realpath('./'));
