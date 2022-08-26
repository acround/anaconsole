<?php

$settings   = array(
    'id'         => '',
    'title'      => '',
    'genre'      => 'sf_fantasy',
    'cover'      => '',
    'annotation' => '',
);
$hexLetters = '1234567890ABCDEF';
$id         = array();
for ($i = 0; $i < 5; $i++) {
    $let = '';
    $len = rand(4, 6);
    for ($j = 0; $j < $len; $j++) {
        $let .= $hexLetters[rand(0, 15)];
    }
    $id[] = $let;
}
$id             = implode('-', $id);
$settings['id'] = $id;
if (count($argv) > 1) {
    for ($i = 1; $i < $argc; $i++) {
        if (substr($argv[$i], 0, 1) == '-') {
            switch (substr($argv[$i], 1, 1)) {
                case 't':
                    $settings['title'] = substr($argv[$i], 2);
                    break;
                case 'g':
                    $genres            = explode(',', substr($argv[$i], 2));
                    $genres            = implode('</genre><genre>', $genres);
                    $settings['genre'] = $genres;
                    break;
                case 'a':
                    $fileName          = substr($argv[$i], 2);
                    if (file_exists($fileName)) {
                        $t                      = file($fileName);
                        $settings['annotation'] = "\n<p>" . implode("</p>\n<p>", $t) . "</p>\n";
                    }
                    break;
                case 'c':
                    $fileName = substr($argv[$i], 2);
                    if (substr($fileName, -4) == '.b64') {
                        $settings['cover'] = file_get_contents($fileName);
                    } else {
                        $settings['cover'] = base64_encode(file_get_contents($fileName));
                    }
                    break;
            }
        }
    }
}
if (!$settings['cover'] && file_exists('cover.jpg.b64')) {
    $settings['cover'] = file_get_contents('cover.jpg.b64');
} else {

}
if (!$settings['annotation']) {
    $fileName = 'info.txt';
    if (file_exists($fileName)) {
        $t = file($fileName);
        for ($i = 0; $i < count($t); $i++) {
            $t[$i] = trim($t[$i]);
        }
        $settings['annotation'] = iconv('windows-1251', 'utf-8', "\n<p>" . implode("</p>\n<p>", $t) . "</p>\n");
    }
}

ob_start();
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<FictionBook xmlns="http://www.gribuser.ru/xml/fictionbook/2.0" xmlns:l="http://www.w3.org/1999/xlink">' . "\n";
echo '  <description>' . "\n";
echo '    <title-info>' . "\n";
echo '      <genre>' . $settings['genre'] . '</genre>' . "\n";
echo '      <author>' . "\n";
echo '        <first-name></first-name>' . "\n";
echo '        <middle-name></middle-name>' . "\n";
echo '        <last-name></last-name>' . "\n";
echo '      </author>' . "\n";
echo '      <book-title>' . $settings['title'] . '</book-title>' . "\n";
echo '      <annotation>' . $settings['annotation'] . '</annotation>' . "\n";
echo '      <date value="00-00-00" />' . "\n";
echo '      <coverpage>' . "\n";
echo '        <image l:href="#cover.jpg" />' . "\n";
echo '      </coverpage>' . "\n";
echo '      <lang>ru</lang>' . "\n";
echo '      <src-lang>ru</src-lang>' . "\n";
echo '      <sequence name="В одном томе" number="0" />' . "\n";
echo '    </title-info>' . "\n";
echo '    <document-info>' . "\n";
echo '      <author>' . "\n";
echo '        <nickname>acround</nickname>' . "\n";
echo '      </author>' . "\n";
echo '      <program-used>WebEdit 3.1</program-used>' . "\n";
echo '      <date value="2009-01-10">10.01.2009</date>' . "\n";
echo '      <id>' . $settings['id'] . '</id>' . "\n";
echo '      <version>1.1</version>' . "\n";
echo '    </document-info>' . "\n";
echo '    <publish-info>' . "\n";
echo '      <book-name>' . $settings['title'] . '</book-name>' . "\n";
echo '      <publisher>Альфа-книга</publisher>' . "\n";
echo '      <city>Москва</city>' . "\n";
echo '      <year>2010</year>' . "\n";
echo '      <isbn></isbn>' . "\n";
echo '      <sequence name="В одном томе" number="0" />' . "\n";
echo '    </publish-info>' . "\n";
echo '  </description>' . "\n";
echo '  <body>' . "\n";
echo '    <section>' . "\n";
echo '      <title>' . "\n";
echo '        <p></p>' . "\n";
echo '      </title>' . "\n";
echo '    </section>' . "\n";
echo '  </body>' . "\n";
echo '  <binary id="cover.jpg" content-type="image/jpeg">' . $settings['cover'] . '</binary>' . "\n";
echo '</FictionBook>' . "\n";
$fb2      = ob_get_clean();
$fileName = ($settings['title'] ? $settings['title'] : 'empty') . '.fb2';
file_put_contents($fileName, $fb2);
