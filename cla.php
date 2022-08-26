<?php

$tags = array(
    'title',
    'target',
    'class',
    'rel',
    'style',
    'data-sessionlink',
    'data-visibility-tracking',
);

$dir  = opendir(realpath('./'));
$rels = array();
foreach ($tags as $tag) {
    $rels[] = '/ ' . $tag . '=".*?"/';
}

while (($fileName = readdir($dir)) !== false) {
    if (substr($fileName, 0, 1) != '.') {
        if (is_file($fileName)) {
            $ext = end(explode('.', $fileName));
            if ($ext == 'html') {
                $file  = file_get_contents($fileName);
                $fileN = preg_replace($rels, '', $file);
                if ($file != $fileN) {
                    file_put_contents($fileName, $fileN);
                    echo $fileName . "- OK\n";
                }
            }
        }
    }
}