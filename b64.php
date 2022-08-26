<?php

$splitLength = 2048;
if ($argc > 1) {
    $fileName = $argv[1];
    if (file_exists($fileName)) {
        $f = base64_encode(file_get_contents($fileName));
        if (isset($argv[2])) {
            $splitLength = intval($argv[2]);
        }
        if ($splitLength) {
            $out = array();
            while (strlen($f)) {
                $out[] = substr($f, 0, $splitLength);
                $f     = substr($f, $splitLength);
            }
            $f = implode("\n", $out);
        }
        file_put_contents($fileName . '.b64', $f);
    }
}
