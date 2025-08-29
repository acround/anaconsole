<?php

class LibraryIncluder
{

    const NAME_TOTAL_LIB = 'libs';
    const NAME_ANALIB = 'analib';
    const NAME_ONPHP = 'onphp';
    const NAME_CONFIG_ANALIB = 'config.inc.php';
    const NAME_CONFIG_ONPHP = 'global.inc.php.tpl';

    static protected $libGlobal = null;
    static protected $libAnalib = null;
    static protected $libOnphp = null;

    static protected function checkLibs()
    {
        if (!self::$libGlobal) {
            self::$libGlobal = DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR . get_current_user() . DIRECTORY_SEPARATOR . 'workspace';
        }
//        if (!self::$libGlobal) {
//            self::$libGlobal = realpath(self::$libGlobal = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . self::NAME_TOTAL_LIB);
//        }
        if (!self::$libAnalib) {
            self::$libAnalib = self::$libGlobal . DIRECTORY_SEPARATOR . self::NAME_ANALIB;
        }
        if (!self::$libOnphp) {
            self::$libOnphp = self::$libGlobal . DIRECTORY_SEPARATOR . self::NAME_ONPHP;
        }
    }

    static public function includeAnalib()
    {
        self::checkLibs();
        include_once self::$libAnalib . DIRECTORY_SEPARATOR . self::NAME_CONFIG_ANALIB;
    }

    static public function includeOnphp()
    {
        self::checkLibs();
        include_once self::$libOnphp . DIRECTORY_SEPARATOR . self::NAME_CONFIG_ONPHP;
    }

    static public function getAnalib()
    {
        self::checkLibs();
        return self::$libAnalib;
    }

    static public function getOnphp()
    {
        self::checkLibs();
        return self::$libOnphp;
    }

}
