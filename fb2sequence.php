<?php

use analib\Core\Xml\Fb2\FB2Sequence;
use \analib\Core\Xml\Fb2\FB2Informer;
use \analib\Core\Exceptions\BaseException;

function setSequence(FB2Informer $fb2, FB2Sequence $sequence = null)
{
    try {
        if ($sequence) {
            $v      = explode('::', $sequence);
            $values = array(
                'name' => $v[0]
            );
            if (isset($v[1])) {
                $values['number'] = $v[1];
            }
            $fb2->setSequence(FB2Sequence::create($values));
        } else {
            echo 'Файл: ' . $fb2->getFileName() . "\t\tСерия: " . $fb2->sequence() . "\n";
        }
    } catch (BaseException $e) {
        echo $e->getMessage();
    }
}

function setSequencePublish(FB2Informer $fb2, FB2Sequence $sequence = null)
{
    try {
        if ($sequence) {
            $v      = explode('::', $sequence);
            $values = array(
                'name' => $v[0]
            );
            if (isset($v[1])) {
                $values['number'] = $v[1];
            }
            $fb2->setSequencePublish(FB2Sequence::create($values));
        } else {
            echo 'Файл: ' . $fb2->getFileName() . "\t\tСерия: " . $fb2->sequencePublish() . "\n";
        }
    } catch (BaseException $e) {
        echo $e->getMessage();
    }
}

include_once 'LibraryIncluder.php';
include_once 'lib.php';
LibraryIncluder::includeAnalib();
$shortOptions = "f::s::d::ap";
$longOptions  = array(
    'file::',
    'sequence::',
    'dir::',
    'publish',
);
$options      = getopt($shortOptions, $longOptions);

$dirPath = realpath('./');

if (isset($options['a'])) {
    $sequence = basename($dirPath);
    $dir      = opendir($dirPath);
    if ($dir) {
        while (($file = readdir($dir)) !== false) {
            if (substr($file, 0, 1) == '.') {
                continue;
            }
            if (getExtension($file) != 'fb2') {
                continue;
            }
            $fn     = explode('.', $file);
            $number = (string) (int) $fn[0];
            $fb2    = FB2Informer::create($file);
            $s      = FB2Sequence::create()->setName($sequence)->setNumber($number);
            if (isset($options['p'])) {
                setSequencePublish($fb2, $s);
            } else {
                setSequence($fb2, $s);
            }
        }
    }
    exit;
} else {
    if (isset($options['f']) && $options['f']) {
        $file = $options['f'];
    } elseif (isset($options['file']) && $options['file']) {
        $file = $options['file'];
    } else {
        $file = null;
    }

    if (isset($options['s']) && $options['s']) {
        $sequence = $options['s'];
    } elseif (isset($options['sequence']) && $options['sequence']) {
        $sequence = $options['sequence'];
    } else {
        $sequence = null;
    }
    if ($sequence == 'dir') {
        $sequence = basename($dirPath);
    }
}

$error = false;
if (($file != 'all') && (!$file || !file_exists($file))) {
    echo $file . " - File not found\n";
    $error = true;
}

if ($error) {
    exit;
}
try {
    if ($sequence) {
        $v      = explode(':', $sequence);
        $values = array(
            'name' => $v[0]
        );
        if (isset($v[1])) {
            $values['number'] = $v[1];
        }
        $s = FB2Sequence::create($values);
    } else {
        $s = null;
    }

    if ($file == 'all') {
        $dir = opendir($dirPath);
        if ($dir) {
            while (($file = readdir($dir)) !== false) {
                if (substr($file, 0, 1) == '.') {
                    continue;
                }
                if (getExtension($file) != 'fb2') {
                    continue;
                }
                $fb2 = FB2Informer::create($file);
                if (isset($options['p'])) {
                    setSequencePublish($fb2, $s);
                } else {
                    setSequence($fb2, $s);
                }
            }
        }
    } else {
        $fb2 = FB2Informer::create($file);
        if (isset($options['p'])) {
            setSequencePublish($fb2, $s);
        } else {
            setSequence($fb2, $s);
        }
    }
} catch (BaseException $e) {
    echo $e;
    echo $e->getMessage();
    echo "\n";
}