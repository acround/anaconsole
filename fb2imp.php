<?php

include_once 'LibraryIncluder.php';
LibraryIncluder::includeAnalib();

class Fb2
{

    const STATUS_DESC       = 1;
    const STATUS_BODY       = 2;
    const STATUS_SECTION    = 3;
    const STATUS_AUTHOR     = 4;
    const STATUS_ANNOTATION = 5;
    const COMMAND_PREFIX    = '// ';
    const END_STATUS        = '/*';

    private $status     = [];
    private $genres     = [];
    private $authors    = [];
    private $bookTitle  = null;
    private $annotation = null;
    private $date       = null;
    private $epigraph   = [];
    private $sections   = [];

    public static function create()
    {
        return new Fb2();
    }

    public function setStatus($status)
    {
        array_unshift($this->status, $status);
        return $this;
    }

    public function getStatus()
    {
        if (count($this->status)) {
            return $this->status[0];
        } else {
            return null;
        }
    }

    public function dropStatus()
    {
        return array_shift($this->status);
    }

    public function make($text)
    {
        $file          = explode("\n", $text);
        $this->setStatus(Fb2::STATUS_DESC);
        $currentAuthor = [];
        foreach ($file as $string) {
            $string = trim($string);
            if ($string) {
                if (substr($string, 0, 3) == Fb2::COMMAND_PREFIX) {
                    $string  = substr($string, 3);
                    $command = explode(' ', trim($string), 2);
                    switch ($command[0]) {
                        case 'genres':
                            if ($this->getStatus() == self::STATUS_DESC) {
                                if (isset($command[1])) {
                                    $genres = explode(',', $command[1]);
                                    foreach ($genres as $genre) {
                                        if (!in_array($genre, $this->genres)) {
                                            $this->genres[] = $genre;
                                        }
                                    }
                                }
                            }
                            break;
                        case 'author':
                            $this->setStatus(self::STATUS_AUTHOR);
                            $currentAuthor = [];
                            break;
                        case 'firstname':
                            if ($this->getStatus() == self::STATUS_AUTHOR) {
                                if (isset($command[1])) {
                                    $currentAuthor['first-name'] = $command[1];
                                }
                            }
                            break;
                        case 'middlename':
                            if ($this->getStatus() == self::STATUS_AUTHOR) {
                                if (isset($command[1])) {
                                    $currentAuthor['middle-name'] = $command[1];
                                }
                            }
                            break;
                        case 'lastname':
                            if ($this->getStatus() == self::STATUS_AUTHOR) {
                                if (isset($command[1])) {
                                    $currentAuthor['last-name'] = $command[1];
                                }
                            }
                            break;
                        case 'booktitle':
                            if ($this->getStatus() == self::STATUS_DESC) {
                                if (isset($command[1])) {
                                    $this->bookTitle = $command[1];
                                }
                            }
                            break;
                        case 'annotation':
                            if ($this->getStatus() == self::STATUS_DESC) {
                                $this->setStatus(self::STATUS_ANNOTATION);
                            }
                            break;
                        case 'date':
                            if ($this->getStatus() == self::STATUS_DESC) {
                                if (isset($command[1])) {
                                    $this->date = $command[1];
                                }
                            }
                            break;
                        case 'epigraph':
                            if ($this->getStatus() == self::STATUS_BODY) {
                                if (isset($command[1])) {
                                    $this->epigraph[] = $command[1];
                                }
                            } elseif ($this->getStatus() == self::STATUS_DESC) {

                            }
                            break;
                        case 'body':
                            if ($this->getStatus() == self::STATUS_DESC) {
                                $this->dropStatus();
                                $this->setStatus(self::STATUS_BODY);
                            }
                            break;
                        case 'title':
                            if ($this->getStatus() == self::STATUS_BODY) {
                                $this->sections[] = [
                                    'title'    => $command[1],
                                    'epigraph' => [],
                                    'text'     => [],
                                ];
                                $this->setStatus(self::STATUS_SECTION);
                            } elseif ($this->getStatus() == self::STATUS_SECTION) {
                                $this->sections[] = [
                                    'title'    => $command[1],
                                    'epigraph' => [],
                                    'text'     => [],
                                ];
                            }
                            break;
                    }
                } elseif ($string == self::END_STATUS) {
                    switch ($this->getStatus()) {
                        case self::STATUS_DESC:
                            break;
                        case self::STATUS_BODY:
                            break;
                        case self::STATUS_SECTION:
                            break;
                        case self::STATUS_AUTHOR:
                            if ($currentAuthor) {
                                $this->authors[] = $currentAuthor;
                            }
                            break;
                    }
                    $this->dropStatus();
                } else {
                    switch ($this->getStatus()) {
                        case self::STATUS_DESC:
                            break;
                        case self::STATUS_BODY:
                            break;
                        case self::STATUS_SECTION:
                            if ((substr($string, 0, 1) != '<') && (substr($string, 0, 11) != '>')) {
                                $this->sections[count($this->sections) - 1]['text'][] = '<p>' . $string . '</p>';
                            }
                            break;
                        case self::STATUS_AUTHOR:
                            break;
                    }
                }
            }
        }
        return $this;
    }

    public function dump()
    {
        $docArray = array(
            '<?xml version="1.0" encoding="utf-8"?>',
            '<FictionBook xmlns="http://www.gribuser.ru/xml/fictionbook/2.0" xmlns:l="http://www.w3.org/1999/xlink">',
            '<description>',
            '<title-info>',
        );
        foreach ($this->genres as $genre) {
            $docArray[] = '<genre>' . $genre . '</genre>';
        }
        foreach ($this->authors as $author) {
            $docArray[] = '<author>';
            if (isset($author['first-name'])) {
                $docArray[] = '<first-name>' . $author['first-name'] . '</first-name>';
            }
            if (isset($author['middle-name'])) {
                $docArray[] = '<middle-name>' . $author['middle-name'] . '</middle-name>';
            }
            if (isset($author['last-name'])) {
                $docArray[] = '<last-name>' . $author['last-name'] . '</last-name>';
            }
            $docArray[] = '</author>';
        }
        if ($this->bookTitle) {
            $docArray[] = '<book-title>' . $this->bookTitle . '</book-title>';
        }
        $docArray[] = '</title-info>';
        $docArray[] = '</description>';
        $docArray[] = '<body>';
        foreach ($this->sections as $section) {
            $docArray[] = '<section>';
            if ($section['title']) {
                $docArray[] = '<title>';
                $docArray[] = '<p>' . $section['title'] . '</p>';
                $docArray[] = '</title>';
            }
            if ($section['epigraph']) {

            }
            foreach ($section['text'] as $string) {
                $docArray[] = $string;
            }
            $docArray[] = '</section>';
        }
        $docArray[] = '</body>';
        $docArray[] = '</FictionBook>';
        return implode("\n", $docArray);
    }

}

define('STATUS_DESC', 1);
define('STATUS_BODY', 2);
define('END_STATUS', '/*');

$arguments = $argv;
//print_r($arguments);
array_shift($arguments);
for ($i = 0; $i < count($arguments); $i++) {
    if (file_exists($arguments[$i])) {
        $fb2         = Fb2::create()->make(file_get_contents($arguments[$i]));
        $fb2Text     = $fb2->dump();
        $fb2Filename = $arguments[$i] . '.fb2';
        file_put_contents($fb2Filename, $fb2Text);
        chmod($fb2Filename, 0666);
    }
}
