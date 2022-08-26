<?php

use analib\Core\Xml\Fb2\FB2Document;
use analib\Util\FileUtils;

include_once 'LibraryIncluder.php';
LibraryIncluder::includeAnalib();
define('INFO_FILE', 'info');
define('WORD_FILE', 'EnglishTochka Wordbook');
$targetDir = realpath('./');

class WordBookMaker
{

    const FILE_TYPE_TEXT       = 'txt';
    const FILE_TYPE_WORDBOOK   = 'wb';
    const FILE_TYPE_DICTIONARY = 'dic';
    const FILE_TYPE_LESSON     = 'lsn';
    const OUT_MODE_TABLE       = 1;
    const OUT_MODE_PARAGRAPH   = 2;

    private $tags = [
        'p',
    ];

    /**
     *
     * @var FB2Document
     */
    private $fb2;
    private $outMode   = self::OUT_MODE_PARAGRAPH;
    private $bookTitle = 'EnglishTochka Wordbook';
    private $INFO_EXTS = [
        self::FILE_TYPE_DICTIONARY,
        self::FILE_TYPE_TEXT,
        self::FILE_TYPE_WORDBOOK,
        self::FILE_TYPE_LESSON,
    ];

    public function __construct()
    {
        $this->init();
    }

    public function getBookTitle()
    {
        return $this->bookTitle;
    }

    /**
     *
     * @return FB2Document
     */
    private function init()
    {
        $path            = explode(DIRECTORY_SEPARATOR, realpath('.' . DIRECTORY_SEPARATOR));
        $this->bookTitle = end($path);
        $this->fb2       = FB2Document::create()->initNew();
        $author          = analib\Core\Xml\Fb2\FB2Author::create('EnglishTochka');
        $author->setLastName('EnglishTochka');
        $this->fb2->
            addAuthor($author)->
            setGenres(['sci_linguistic'])->
            setBookTitle($this->bookTitle);
        return $this;
    }

    private function getFileList($dir)
    {
        $directory = opendir($dir);
        $filelist  = [];
        while (($file      = readdir($directory)) !== false) {
            if (substr($file, 0, 1) != '.') {
                if (is_dir($file)) {
                    $filelist[$file] = $file;
                } else {
                    $ext = FileUtils::getExtension(realpath('./') . DIRECTORY_SEPARATOR . $file);
                    if (in_array($ext, $this->INFO_EXTS)) {
                        $filelist[substr($file, 0, strrpos($file, '.'))] = $file;
                    }
                }
            }
        }
        ksort($filelist);
        if (isset($filelist['dev'])) {
            $filelist = ['Develop' => $filelist['dev']];
        }
        return $filelist;
    }

    /**
     *
     * @param DOMElement $body
     * @param type $title
     * @return DOMElement
     */
    private function makeSectionNode(DOMElement $body, $title)
    {
        $sectionNode = $this->fb2->createNode('section');
        $titleNode   = $this->fb2->createNode('title');
        $pNode       = $this->fb2->createNode('p');
        $this->fb2->addTextNode($pNode, $title);
        $this->fb2->appendChild($pNode, $titleNode);
        $this->fb2->appendChild($titleNode, $sectionNode);
        $this->fb2->appendChild($sectionNode, $body);
        return $sectionNode;
    }

    private function makeSectionStart($sectionNode, $content)
    {
        switch ($this->outMode) {
            case self::OUT_MODE_TABLE:
                $table = $this->fb2->addNode($sectionNode, 'table');
                $this->makeTableHeader($table, $content);
                return $table;
            case self::OUT_MODE_PARAGRAPH:
                break;
        }
        return $sectionNode;
    }

    private function parseFile(array $fileContent)
    {
        $index  = 0;
        $return = [];
        foreach ($fileContent as $row) {
            $rowParsed = [
                'tag'   => '',
                'text'  => '',
                'title' => '',
                'en'    => '',
                'ru'    => '',
                'tr'    => '',
                'ex'    => [],
            ];
            $index++;
            if (substr($row, 0, 1) == '/') {
                $text = trim(substr($row, 1));
                foreach ($this->tags as $tag) {
                    if (strpos($text, $tag) === 0) {
                        $rowParsed['tag']  = $tag;
                        $rowParsed['text'] = trim(substr($text, strlen($tag)));
                        break;
                    }
                }
            } elseif (substr($row, 0, 1) == '@') {
                $rowParsed['tag']  = 'subtitle';
                $rowParsed['text'] = trim(substr($row, 1));
            } else {
                $content = explode('|', trim($row));
                foreach ($content as $part) {
                    $part = trim($part);
                    if (!$part) {
                        continue;
                    }
                    $first = substr($part, 0, 1);
                    if ($first == '{') {
                        $rowParsed['ex'][] = [
                            'en' => substr($part, 1),
                            'ru' => '',
                        ];
                    } elseif ($first == '#') {
                        $last = count($rowParsed['ex']) - 1;
                        if (isset($rowParsed['ex'][$last])) {
                            $rowParsed['ex'][$last]['ru'] = substr($part, 1);
                        }
                    } elseif (($first == '[') && (substr($part, -1) == ']')) {
                        $rowParsed['tr'] = $part;
                    } else {
                        for ($i = 0; $i < mb_strlen($part, 'utf-8'); $i++) {
                            $first = mb_substr($part, $i, 1, 'utf-8');
                            if (strpos('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYX', $first) !== false) {
                                $rowParsed['en'] = $part;
                                continue 2;
                            } elseif (strpos('абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ', $first) !== false) {
                                $rowParsed['ru'] = $part;
                                continue 2;
                            }
                        }
                    }
                }
            }
            $return[] = $rowParsed;
        }
        return $return;
    }

    private function makeTableHeader(DOMElement $table, array $content)
    {
        $tr = $this->fb2->addNode($table, 'tr');
        $th = $this->fb2->addNode($tr, 'th');
        $this->fb2->addTextNode($th, 'English');
        if (count($content['tr'])) {
            $th = $this->fb2->addNode($tr, 'th');
            $this->fb2->addTextNode($th, 'Transcription');
        }
        $th = $this->fb2->addNode($tr, 'th');
        $this->fb2->addTextNode($th, 'Russian');
    }

    private function prepareString($text)
    {
        $for = [
            '\n',
        ];
        $to  = [
            "\n",
//            '<empty-line />'
        ];
        $out = trim(str_replace($for, $to, $text));
        return $out;
    }

    private function outFile($node, $content)
    {
        switch ($this->outMode) {
            case self::OUT_MODE_TABLE:
                $this->outFileAsTable($node, $content);
                break;
            case self::OUT_MODE_PARAGRAPH:
                $this->outFileAsParagraph($node, $content);
                break;
        }
    }

    private function outFileAsTable($table, $content)
    {
        foreach ($content['en'] as $number => $en) {
            $tr = $this->fb2->addNode($table, 'tr');
            if ($en == '@') {
                $th = $this->fb2->addNode($tr, 'th');
                $th->setAttribute('colspan', 4);
                $this->fb2->addTextNode($th, $this->prepareString($content['ru'][$number]));
            } else {
                if ($en) {
                    $td = $this->fb2->addNode($tr, 'td');
                    $this->fb2->addTextNode($td, $this->prepareString($en));
                    if (count($content['tr'])) {
                        if (isset($content['tr'][$number])) {
                            $td = $this->fb2->addNode($tr, 'td');
                            $this->fb2->addTextNode($td, $content['tr'][$number]);
                        } else {
                            $td = $this->fb2->addNode($tr, 'td');
                            $this->fb2->addTextNode($td, '');
                        }
                    }
                    $td = $this->fb2->addNode($tr, 'td');
                    if (isset($content['ru'][$number])) {
                        $this->fb2->addTextNode($td, $this->prepareString($content['ru'][$number]));
                    }
                }
                if (isset($content['ex'][$number])) {
                    foreach ($content['ex'][$number] as $numEx => $ex) {
                        $tr = $this->fb2->addNode($table, 'tr');
                        $td = $this->fb2->addNode($tr, 'th');
                        $this->fb2->addTextNode($td, 'Example');
                        $td = $this->fb2->addNode($tr, 'td');
                        $this->fb2->addTextNode($td, $this->prepareString($content['ex'][$number][$numEx]));
                        if (isset($content['exR'][$number][$numEx][$numEx])) {
                            $td = $this->fb2->addNode($tr, 'td');
                            $this->fb2->addTextNode($td, $this->prepareString($content['exR'][$number][$numEx]));
                        } else {
                            $td = $this->fb2->addNode($tr, 'td');
                            $this->fb2->addTextNode($td, '');
                        }
                    }
                }
            }
        }
    }

    private function outFileAsParagraph(DOMElement $node, $content)
    {
        foreach ($content as $part) {
            if ($part['tag']) {
                $st = $this->fb2->addNode($node, $part['tag']);
                if ($part['text']) {
                    $this->fb2->addTextNode($st, $part['text']);
                }
            } elseif ($part['title']) {
                $st = $this->fb2->addNode($node, 'subtitle');
                $this->fb2->addTextNode($st, $part['title']);
            } else {
                if ($part['en']) {
                    $p      = $this->fb2->addNode($node, 'p');
                    $strong = $this->fb2->addNode($p, 'strong');
                    $this->fb2->addTextNode($strong, $part['en']);
                    if ($part['tr']) {
                        $emphasis = $this->fb2->addNode($p, 'emphasis');
                        $this->fb2->addTextNode($emphasis, ' ' . $part['tr'] . ' ');
                    }
                    if ($part['ru']) {
                        $this->fb2->addTextNode($p, ' — ' . $part['ru']);
                    }
                }
                if (count($part['ex'])) {
//                    $p = $this->fb2->addNode($node, 'empty-line');
                    foreach ($part['ex'] as $example) {
                        $exEn = $example['en'];
                        $exRu = $example['ru'];
                        $p    = $this->fb2->addNode($node, 'p');
                        $em   = $this->fb2->addNode($p, 'emphasis');
                        $this->fb2->addTextNode($em, $exEn);
                        $p    = $this->fb2->addNode($node, 'p');
                        $this->fb2->addTextNode($p, $exRu);
//                        $p = $this->fb2->addNode($node, 'empty-line');
                    }
                }
            }
        }
    }

    public function run(DOMElement $parent = null, $dir = null)
    {
        if (!$parent) {
            $parent = $this->fb2->getFirstNode('/FictionBook/body');
        }
        if (!$dir) {
            $dir = realpath('./');
        }
        $filelist = $this->getFileList($dir);
        foreach ($filelist as $title => $file) {
            $file = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                $sectionNode = $this->makeSectionNode($parent, $title);
                chdir($file);
                $this->run($sectionNode, $file);
            } else {
                $sectionNode = $this->makeSectionNode($parent, $title);
                $fileContent = file($file);
                $content     = $this->parseFile($fileContent);
                $node        = $this->makeSectionStart($sectionNode, $content);
                $this->outFile($node, $content);
            }
        }
        return $this->fb2;
    }

}

$wbm = new WordBookMaker();
$fb2 = $wbm->run();
$fb2->saveToFile($targetDir . DIRECTORY_SEPARATOR . $wbm->getBookTitle() . '.fb2');
