<?php

$docArray  = array(
    '<?xml version="1.0" encoding="utf-8"?>',
    '<FictionBook xmlns="http://www.gribuser.ru/xml/fictionbook/2.0" xmlns:l="http://www.w3.org/1999/xlink">',
    '	<description>',
    '		<title-info>',
    '			<genre></genre>',
    '			<author>',
    '				<first-name></first-name>',
    '				<middle-name></middle-name>',
    '				<last-name></last-name>',
    '			</author>',
    '			<book-title></book-title>',
    '			<annotation></annotation>',
    '			<date value="00-00-00"/>',
    '			<coverpage>',
    '				<image l:href="#cover.jpg"/>',
    '			</coverpage>',
    '			<lang>ru</lang>',
    '			<src-lang>ru</src-lang>',
    '			<sequence name="" number=""/>',
    '		</title-info>',
    '		<document-info>',
    '		</document-info>',
    '		<publish-info>',
    '		</publish-info>',
    '	</description>',
    '	<body>',
    '		<image xlink:href="" />',
    '		<title>',
    '			<p></p>',
    '		</title>',
    '		<epigraph>',
    '			<p></p>',
    '			<text-author></text-author>',
    '		</epigraph>',
    '		<section>',
    '		</section>',
    '	</body>',
    '	<binary id="cover.jpg" content-type="image/jpeg"></binary>',
    '</FictionBook>',
);
$docString = implode("\n", $docArray);
$fileName  = isset($argv[1]) ? $argv[1] : 'empty.fb2';
if (substr($fileName, -4) != '.fb2') {
    $fileName .= '.fb2';
}
file_put_contents($fileName, $docString);
chmod($fileName, 0666);
