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
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $fileName, FILEINFO_MIME_TYPE);
        $out   = '<binary content-type="' . $mime . '" id="' . basename($fileName) . '">' . $f . '</binary>';
        file_put_contents($fileName . '.b64', $out);
    }
}
