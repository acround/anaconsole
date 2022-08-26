<?php

use \analib\Util\StdIo;
use \analib\Core\Xml\Fb2 as Fb2;

include_once 'LibraryIncluder.php';
LibraryIncluder::includeAnalib();

define('FB2_GENRE', 'genre');
define('FB2_AUTHOR', 'author');
define('FB2_TITLE', 'title');
define('FB2_ANNOTATION', 'annotation');
define('FB2_LANG', 'lang');
define('FB2_SEQUENCE', 'sequence');
define('FB2_MAKE', 'make');
define('CANCEL', 'cancel');
$titleInfoSteps = array(
    FB2_GENRE      => 'Добавить жанр',
    FB2_AUTHOR     => 'Добавить автора',
    FB2_TITLE      => 'Ввести название',
    FB2_ANNOTATION => 'Ввести аннотацию',
    FB2_LANG       => 'Ввести язык',
    FB2_SEQUENCE   => 'Ввести серию',
    FB2_MAKE       => 'Генерация',
    CANCEL         => 'Отмена',
);

//------------------------------------------------------------------------------
$denyMap = array(
    '\'' => '`',
    '"'  => '`',
    '\\' => '_',
    '/'  => '_',
    '|'  => '_',
    '?'  => '.',
//	' ' => '_',
    ':'  => '.',
    '*'  => '.',
);

$bookArray = array(
    'genre'      => array(),
    'author'     => array(),
    'title'      => '',
    'annotation' => '',
    'lang'       => 'ru',
    'sequence'   => null,
    'sections'   => 0,
);
$loop      = true;
StdIo::putLn();
while ($loop) {
    $r = StdIo::askChoice('Выбор: ', $titleInfoSteps);
    switch ($r) {
        case FB2_GENRE:
            StdIo::putLn();
            $group                = StdIo::askChoice('Раздел: ', Fb2\Fb2Genres::getGenresGroup());
            StdIo::putLn($group);
            $genre                = StdIo::askChoice('Жанр: ', Fb2\Fb2Genres::getGenresOfGroup($group));
            $bookArray['genre'][] = $genre;
            break;
        case FB2_AUTHOR:
            $author               = StdIo::ask('Ф/И/О: ');
            $author               = explode('/', $author);
            while (count($author) < 3) {
                $author[] = '';
            }
            $author                  = Fb2\FB2Author::create($author[0], $author[1], $author[2]);
            $bookArray['author'][]   = $author;
            break;
        case FB2_TITLE:
            $title                   = StdIo::ask('Название: ');
            $bookArray['title']      = $title;
            break;
        case FB2_ANNOTATION:
            $annotation              = StdIo::ask('Аннотация: ');
            $bookArray['annotation'] = $annotation;
            break;
        case FB2_LANG:
            $lang                    = StdIo::ask('Язык: ');
            $bookArray['lang']       = $lang;
            break;
        case FB2_SEQUENCE:
            $name                    = trim(StdIo::ask('Название серии: '));
            if ($name) {
                $number                = (int) trim(StdIo::ask('Номер в серии: '));
                $bookArray['sequence'] = Fb2\FB2Sequence::create(
                        array(
                            'name'   => $name,
                            'number' => $number,
                        )
                );
            }
            break;
        case FB2_MAKE:
            $bookArray['sections'] = (int) StdIo::ask('Секций:');
            $fb2                   = Fb2\FB2Document::makeFromArray($bookArray);

            $fileName = ($bookArray['title'] ? $bookArray['title'] : 'newFb2Document');
            $out      = '';
            for ($i = 0; $i < mb_strlen($fileName, 'utf-8'); $i++) {
                $symbol = mb_substr($fileName, $i, 1, 'utf-8');
                if (isset($denyMap[$symbol])) {
                    $out .= $denyMap[$symbol];
                } else {
                    $out .= $symbol;
                }
            }
            $out = trim($out, '_.');
            if (!$out) {
                $out = '_';
            }
            $fileName = $out;
            $fb2->saveToFile($fileName . '.fb2');
            $loop     = false;
            break;
        case CANCEL:
            $loop     = false;
            break;
        default :
            var_dump($r);
    }
}
