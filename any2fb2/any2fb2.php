<?php

/*
 * Console tx2fb frontend
 *
 * $Id: any2fb2,v 1.18 2005/02/11 19:06:38 eliterr Exp eliterr $
 *
 */

//$progdir  = '/usr/local/lib/Any2FB2/';
$progdir = dirname(__FILE__);

inisets();

loadgd();

require_once ('tx2fb.php');

$stderr = fopen('php://stderr', 'w');

$prog = array_shift($argv);

$filename = false;
$outfname = false;

$tx2fb2 = new tx2fb;

$myparams = $tx2fb2->Params;

$myparamsset = array();
foreach (array_keys($myparams) as $name) {
    $myparamsset [$name] = false;
}

$zip         = false;
$forcezipout = false;

$showprogress = true;
$showdots     = false;

while (count($argv) && !is_null($arg = array_shift($argv))) {
    switch ($arg) {
        case '-z':
        case '-Z':
        case '--zip':
            $zip          = true;
            break;
        case '-q':
        case '--quiet':
            $showprogress = false;
            break;
        case '-v':
        case '--verbose':
            $showdots     = true;
            break;
        case '-f':
        case '--force':
            $forcezipout  = true;
            break;
        case '-o':
        case '--output':
            $outfname     = array_shift($argv);
            break;
        case '-h':
        case '--help':
            help();
            exit;
        case '-l':
        case '--list':
        case '--list-parameters':
            echo "Parameters:\n";

            foreach ($myparams as $name => $val) {
                if (is_bool($myparams [$name])) {
                    $val = ($val ? '1 (true)' : '0 (false)');
                } else {
                    if (is_string($myparams [$name])) {
                        $val = "'$val'";
                    }
                }

                if (!is_array($myparams [$name])) {
                    echo "  $name => $val\n";
                } else {
                    $header  = "  $name ";
                    $prepend = preg_replace('/./', ' ', $header);

                    if (!count($myparams [$name])) {
                        echo "$header => [] (empty)\n";
                    } else {
                        foreach ($myparams [$name] as $id => $value) {
                            if ($id == 0) {
                                echo $header;
                            } else {
                                echo $prepend;
                            }

                            echo ' => ' . $value . "\n";
                        }
                    }
                }
            }

            exit;
        case '-p':
        case '--parameter':
            $par = array_shift($argv);
            $val = array_shift($argv);

            if (!is_null($par) && !is_null($val)) {
                $par = str_replace('_', '', strtolower($par));

                foreach ($myparams as $name => $value) {
                    $tstname = str_replace('_', '', strtolower($name));

                    if ($tstname == $par) {
                        if (is_int($myparams [$name]) && is_numeric($val)) {
                            $myparams [$name] = (int) $val;
                        } else
                        if (is_bool($myparams [$name]) &&
                            ($val == '0' || $val == '1')) {
                            $myparams [$name] = ($val == '1');
                        } else {
                            if (is_string($myparams [$name])) {
                                $myparams [$name] = $val;
                            } else {
                                if (is_array($myparams [$name])) {
                                    $myparams [$name] [] = $val;
                                }
                            }
                        }
                    }
                }
            }
            break;
        default:
            $filename = $arg;
            break;
    }
}

if (is_bool($filename)) {
    help();
    exit;
}

if ($zip && (is_bool($outfname) || (trim($outfname) == '-')) && !$forcezipout) {
    echo "Refusing to put compressed data to screen\n";
    exit;
}

$dotprinted = false;
if ($showprogress) {
    $tx2fb2->informfunc  = 'inform';
    $tx2fb2->warningfunc = 'warn';

    if ($showdots) {
        $tx2fb2->progressfunc = 'progress';
    }
}
$tx2fb2->unknownfilefunc    = 'unknownfile';
$tx2fb2->getexternaldocfunc = 'getexternaldoc';
$tx2fb2->getexternalimgfunc = 'getexternalimg';
$tx2fb2->Params             = $myparams;

$text = $tx2fb2->ParseText($filename);

unset($tx2fb2);

if ($text === false) {
    fwrite($stderr, "Failed\n");
} else {
    if ($zip) {
        $compfilename = preg_replace('/\.(txt|htm|doc|prt|rtf).*/', '', $filename);
        $compfilename .= '.fb2';

        $text = mkzipstr($compfilename, $text);
    }

    if (is_bool($outfname) || (trim($outfname) == '-')) {
        echo $text;
    } else {
        $h = fopen($outfname, "w");
        fwrite($h, $text);
        fclose($h);
    }
}

exit;

// Initializing functions

function inisets()
{
    global $win, $levelseparator, $tmpdir, $progdir;

    $win = (strtoupper(substr(PHP_OS, 0, 3) == 'WIN'));
    if ($win) {
        $levelseparator = "\\";
        $tmpdir         = 'c:\windows\temp';
    } else {
        $levelseparator = '/';
        $tmpdir         = '/tmp';
    }

    $incpath = ini_get("include_path");
    $incpath .= ($win ? ';' : ':') . dirname(__FILE__);
    $incpath .= ($win ? ';' : ':') . $progdir;

    ini_set("include_path", $incpath);
    ini_set('output-buffering', false);
    ini_set('max_execution_time', 24 * 60 * 60);
}

function loadgd()
{
    global $win;

    if (!extension_loaded('gd')) {
        $oldextlib = ini_get('extension_dir');

        foreach (array($oldextlib, ($win ? ".\\" : './')) as $extdir) {
            @ini_set('extension_dir', $extdir);
            if ($loaded = @dl($win ? 'php_gd2.dll' : 'gd.so')) {
                break;
            }
        }
        @ini_set('extension_dir', $oldextlib);
    }
}

// Convertor feedback function

function inform($line)
{
    global $stderr, $dotprinted;

    if ($dotprinted) {
        fwrite($stderr, "\n");
    }

    fwrite($stderr, "$line\n");

    $dotprinted = false;
}

function warn($line)
{
    global $stderr, $dotprinted;

    if ($dotprinted) {
        fwrite($stderr, "\n");
    }

    fwrite($stderr, "$line\n");

    $dotprinted = false;
}

function progress($line)
{
    global $stderr, $dotprinted;

    #fwrite ($stderr, $line."\n");
    fwrite($stderr, '.');
    $dotprinted = true;
}

// Help

function help()
{
    global $prog;

    echo "Usage: $prog <arguments> <input_file_or_url>\n";
    echo "Arguments:\n";
    echo "  -z|--zip - compress output to zip file (not to terminal)\n";
    echo "  -f|--force - allow zip-file be ouput to terminal\n";
    echo "  -o|--output <filename> - write output to specified file\n";
    echo "  -h|--help - this help\n";
    echo "  -l|--list - show list of available options\n";
    echo "  -p|--parameter <par> <val> - set (or add to) value to parameter\n";
    echo "     parameter name can be in any case and without _\n";
    echo "  -v|--verbose show detailed progress\n";
    echo "  -q|--quiet do not show progress at all\n";
    echo "\n";
}

// Unknown file callback

function unknownfile(&$filename, &$content)
{
    global $tmpdir;

    $result = false;

    $matches = array(0);

    if (preg_match('/^(.*?\.)(txt|htm|html)\.gz$/i', $filename, $matches) &&
        (($content = @file_get_contents($filename)) !== false) &&
        strlen($content)) {
        $tmpfile = tempnam($tmpdir, 'Any2fb2_gz_file');

        $file = fopen($tmpfile, 'w');
        fwrite($file, $content);
        fclose($file);

        if (($gz = @gzopen($tmpfile, 'r')) !== false) {
            $content = '';

            while (!gzeof($gz)) {
                $content .= gzread($gz, 1024);
            }

            fclose($gz);

            $filename = $matches [1] . $matches [2];

            $result = true;
        }

        unlink($tmpfile);
    }

    $execresult = -1;

    if (preg_match('/^(.*?\.)pdf$/i', $filename, $matches) &&
        (exec('pdftohtml -v 2>/dev/null', $matches, $execresult) !== false) &&
        ($execresult == 0)) {
        $tmpfile = tempnam($tmpdir, 'Any2fb2_pdf_file');

        $file = fopen($tmpfile, 'w');
        fwrite($file, $content);
        fclose($file);

        $fd = popen("pdftohtml -stdout -noframes -enc KOI8-R -i $tmpfile", "r");

        if ($fd !== false) {
            $content = '';
            while (!feof($fd)) {
                $content .= fread($fd, 1024);
            }

            pclose($fd);

            $filename = $matches [1] . 'html';

            $result = true;
        }

        unlink($tmpfile);
    }

    return $result;
}

// External file callback

function getexternaldoc(&$filename)
{
    $result  = -1;
    $content = array();

    $shfilename = escapeshellarg($filename);

    exec("wget $shfilename -O- -q 2>/dev/null", $content, $result);

    if ($result == 0) {
        $content = join("\n", $content);

        return $content;
    }

    return false;
}

function getexternalimg(&$filename)
{
    global $tmpdir;

    $result  = -1;
    $content = false;

    $tmpfile = tempnam($tmpdir, 'Any2fb2_image_download_file');

    $shfilename = escapeshellarg($filename);

    exec("wget $shfilename -O \"$tmpfile\" -q 2>/dev/null", $broken_content, $result);

    if ($result == 0) {
        $content = file_get_contents($tmpfile);
    }

    unlink($tmpfile);

    return $content;
}

// Making ZIP file (one file, deflate only, maximal compression)

function mkzipstr($filename, $content)
{
    $time = time();
    $date = getdate($time);

    $mtime = ($date ['hours'] << 11) + ($date ['minutes'] << 5) +
        ($date ['seconds'] / 2);
    $mdate = (($date ['year'] - 1980) << 9) + ($date ['mon'] << 5) +
        $date ['mday'];

    $crc        = crc32($content);
    $compressed = gzdeflate($content, 9);

    $comment = 'Converted by Any2FB';

    $localextra = '';

    $centralextra = '';

    $zipstring = pack("VvvvvvVVVvv", 0x04034b50, // Local header
        20, // Version to extract
        2, // flag, for compression 8 or 9
        8, // method - deflated
        $mtime, // date
        $mdate, // date
        $crc, strlen($compressed), strlen($content), strlen($filename), strlen($localextra) // extra data length
    );

    $zipstring .= $filename;
    $zipstring .= $localextra;
    $zipstring .= $compressed;

    $centralextra = '';

    $coffset = strlen($zipstring);

    $zipstring .= pack("VvvvvvvVVVvvvvvVV", 0x02014b50, // Central header
        //0x0317,  // Version made by: UNIX, zip 2.3
        0, // Version made by: unknown
        20, // Version to extract
        2, // flag, for compression 8 or 9
        8, // method - deflated
        $mtime, // date
        $mdate, // date
        $crc, strlen($compressed), strlen($content), strlen($filename), strlen($centralextra), // extra
        strlen($comment), 0, // disk
        0, // internal
        0x81b60000, // external attributes
        0 // offset (this file starts from the very beginning)
    );

    $zipstring .= $filename;
    $zipstring .= $centralextra;
    $zipstring .= $comment;

    $csize = strlen($zipstring) - $coffset;

    $zipstring .= pack("VvvvvVVv", 0x06054b50, // End-if-central header
        0, // Disk bumber
        0, // Disk number
        1, // Number of entries in central directory
        1, // Number of entries in central directory
        $csize, // Central directory start
        $coffset, // Central directory offset
        0 // size of comment
    );

    return $zipstring;
}

/* vim600: set foldmethod=indent: */
