<?php

$tags = array(
    'description',
    'title-info',
    'genre',
    'author',
    'first-name',
    'middle-name',
    'last-name',
    'book-title',
    'annotation',
    'date',
    'coverpage',
    'lang',
    'document-info',
    'nickname',
    'program-used',
    'src-ocr',
    'id',
    'version',
    'publish-info',
    'book-name',
    'publisher',
    'city',
    'year',
    'isbn',
    'body',
    'section',
    'title',
    'p',
    'cite',
    'binary',
    'FictionBook',
);

if ($argc > 1) {
    $fileName = $argv[1];
    if (file_exists($fileName)) {
        $f = file_get_contents($fileName);
        foreach ($tags as $tag) {
            $f = preg_replace('~>\s*<' . $tag . '~', ">\n<" . $tag, $f);
//			$f	 = preg_replace('~>\s*<' . $tag . '~', ">\n<" . $tag, $f);
            $f = preg_replace('~</' . $tag . '>\s*<~', "</" . $tag . ">\n<", $f);
        }
//		rename($fileName, $fileName . '.bak');
        file_put_contents($fileName, $f);
    }
}
