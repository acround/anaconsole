<?php
/*
 *
 * Thin client FictionBook convertor support script
 *
 * $Id: index.php,v 1.18 2005/02/14 21:18:01 eliterr Exp eliterr $
 *
 */


// Array: parametername => dir root
$libdirs = array();

if (0) {
    $progdir             = 'd:\var\www\htdocs\Any2FB2';
    $cachedir            = 'd:\var\www\htdocs\Any2FB2\cache';
    $libdirs ['libfile'] = 'L:\public_html\book';
} else {
    $progdir                   = '/usr/local/lib/Any2FB2/';
    $cachedir                  = '/var/cache/apache/Any2FB2';
    $libdirs ['docfile']       = '/var/www/doc';
    $libdirs ['debiandocfile'] = '/var/www/debiandoc';
}

$cachedprefix = 'phpany2fb2_result_';

// Simple garbage collection and convertion limitation:
//
//   All converted files older than $cachetimeout seconds are purged
//
//   If $fileslimit is greater than 0 and number of converted files are
//   greater than $fileslimit then access is denied
//
$cachetimeout = 60 * 60; // 1 hour
$fileslimit   = 60;

$curtime = time();

if (!isset($PHP_SELF))
    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF');

inisets();

loadgd();

loadxslt();

require_once ('tx2fb.php');

unset($error);

delexpired();

unset($infile);

foreach ($libdirs as $paramname => $libdir)
    if (isset($_GET [$paramname]))
        $infile = $_GET [$paramname];

if (substr($libdir, -1) != $levelseparator)
    $libdir .= $levelseparator;

do {
    if (isset($_GET ['getfile'])) {
        $tmpfile = unslash($_GET ['getfile']);

        if ((strpos($tmpfile, '/') !== false) ||
            (strpos($tmpfile, "\\") !== false) ||
            (strpos($tmpfile, '..') !== false)) {
            $error = "Bad file";
            continue;
        }

        $tmpfile = $cachedir . $levelseparator . $tmpfile;

        if (!is_readable($tmpfile)) {
            $error = "Can't read result file";
            continue;
        }

        $text = file_get_contents($tmpfile);

        $oname = '';

        if (isset($_GET ['origfile']))
            $oname = $_GET ['origfile'];

        if ($oname == '')
            $oname = 'Any2FB2_result';

        $oname = strtr($oname, ' ', '_');

        $loname = strtolower($oname);
        $olen   = strlen($oname);

        if (($olen > 4) &&
            ((substr($loname, $olen - 4, 4) == '.txt') ||
            (substr($loname, $olen - 4, 4) == '.htm')))
            $oname = substr($oname, 0, $olen - 4);
        else
        if (($olen > 5) && (substr($loname, $olen - 4, 4) == '.html'))
            $oname = substr($oname, 0, $olen - 5);

        $oname .= '.fb2';

        if (isset($_GET ['format']))
            $format = $_GET ['format'];
        else
            $format = 'fb2';

        $format = strtolower($format);

        if (($format == 'html') && !$xsltloaded)
            $format = 'fb2';

        switch ($format) {
            case 'zip':
                $text         = mkzipstr($oname, $text);
                $oname        .= '.zip';
                $content_type = 'application/zip';
                break;

            case 'html':
                $xsltfile = $progdir . $levelseparator . 'FB2_2_xhtml.xsl';
                $xslt     = file_get_contents($xsltfile);

                $args = array('/_xml' => $text, '/_xsl' => $xslt);

                $parser = xslt_create();
                xslt_set_encoding($parser, 'cp1251');
                $text   = xslt_process($parser, 'arg:/_xml', 'arg:/_xsl', null, $args);
                xslt_free($parser);

                $oname        .= '.html';
                $content_type = 'text/html';
                break;

            default:
                $content_type = 'text/x-fictionbook2';
        }

        $textlen = strlen($text);

        header("Content-Type: $content_type");
        header("Content-Length: $textlen");
        if ($format != 'html')
            header("Content-Disposition: attachment; filename=$oname");
        header("Content-Description: Text converted by Any2fb2");

        echo "$text";
        exit;
    } else
    if (!canconvert()) {
        $error = "Can't convert any file: limit exceeded.<br>" .
            "Try a bit later";
        continue;
    } else
    if (isset($infile)) {
        $infile = unslash($infile);

        $filename = $infile;

        $filename = str_replace("\\", '/', $filename);

        if ((strpos($filename, ':') !== false) ||
            (strpos($filename, '..') !== false)) {
            $error = 'Bad input filename';
            continue;
        }


        $curdir = substr($filename, 0, strrpos($filename, '/'));

        if (($filename {0} != '/') && ($filename {0} != "\\"))
            $curdir = $libdir . $curdir;

        $curdir = strtr($curdir, '/', $levelseparator);

        if (!chdir($curdir)) {
            $error = "Can't change dir to file's one";
            continue;
        }


        if (($filename {0} != '/') && ($filename {0} != "\\"))
            $filename = $libdir . $filename;

        if (!is_readable($filename)) {
            $error = "Can't read file";
            continue;
        }


        $tx2fb2 = new tx2fb;

        $myparams = $tx2fb2->Params;

        $dotprinted              = false;
        $tx2fb2->informfunc      = 'inform';
        $tx2fb2->warningfunc     = 'warn';
        $tx2fb2->progressfunc    = 'progress';
        $tx2fb2->unknownfilefunc = 'unknownfile';

        if (isset($_POST ['Params']))
            foreach ($myparams as $name => $val) {
                unset($newval);

                foreach ($_POST ['Params'] as $nname => $nval)
                    if (!strcasecmp($nname, $name)) {
                        $newval = $nval;

                        break;
                    }

                if (is_bool($val)) {
                    $myparams [$name] = isset($newval);

                    continue;
                }

                if (!isset($newval))
                    continue;

                $newval = unslash($newval);

                if (is_int($val)) {
                    if (is_numeric($newval))
                        $myparams [$name] = (int) $newval;

                    continue;
                }

                if (is_string($val)) {
                    $myparams [$name] = $newval;

                    continue;
                }

                if (is_array($val)) {
                    $newvals = preg_split("-\r?\n-", $newval);
                    $cnt     = count($newvals);

                    if ($newvals [$cnt - 1] == '')
                        unset($newvals [$cnt - 1]);

                    $myparams [$name] = $newvals;
                }
            }

        $tx2fb2->Params = $myparams;

        if (isset($_POST ['Convert'])) {
            htmlheader("Converting file to FictionBook format");

            progressheader();

            $text = $tx2fb2->ParseText($filename);

            unset($tx2fb2);

            progressfooter();

            if ($text != false) {
                if (!chdir($cachedir)) {
                    $error = "Can't chdir to cache dir";
                    continue;
                }

                $otries = 0;

                do {
                    $otries++;
                    $oname = $cachedprefix . $otries . $curtime;
                    $fp    = @fopen($oname, 'x');
                } while ($fp === false);

                fwrite($fp, $text);
                fclose($fp);

                $origname = $filename;

                $pos      = strrpos($origname, "/");
                if ($pos !== false)
                    $origname = substr($origname, $pos + 1);

                $pos      = strrpos($origname, "\\");
                if ($pos !== false)
                    $origname = substr($origname, $pos + 1);

                $ourl = "$PHP_SELF?getfile=$oname&origfile=$origname";

                if ($xsltloaded) {
                    echo "<br>";
                    echo "<a href='$ourl&format=html'>Preview resulted document as xHTML</a>";
                }

                echo "<br>";
                echo "<a href='$ourl&format=fb2'>Download resulted FB2 file</a>";

                echo "<br>";
                echo "<a href='$ourl&format=zip'>Download resulted FB2 file as ZIP file</a>";
            } else {
                echo "<b>Conversion failed<b>";
            }

            htmlfooter();

            exit;
        }

        htmlheader("FictionBook convertor settings");

        paramsheader($paramname . '=' . urlencode($infile));

        foreach ($myparams as $name => $value)
            paraminput($name, $value);

        paramsfooter();

        htmlfooter();

        exit;
    } else {
        $error = "Input file was not specified";
    }
} while (0);

if ($error) {
    htmlheader("Error executing conversion script");

    echo "<b>$error</b>";

    htmlfooter();
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
            if ($loaded = @dl($win ? 'php_gd2.dll' : 'gd.so'))
                break;
        }
        @ini_set('extension_dir', $oldextlib);
    }
}

function loadxslt()
{
    global $win, $xsltloaded;

    if (!extension_loaded('xslt')) {
        $oldextlib = ini_get('extension_dir');

        foreach (array($oldextlib, ($win ? ".\\" : './')) as $extdir) {
            @ini_set('extension_dir', $extdir);
            if ($loaded = @dl($win ? 'php_xslt.dll' : 'xslt.so'))
                break;
        }
        @ini_set('extension_dir', $oldextlib);
    }

    $xsltloaded = function_exists('xslt_create') &&
        function_exists('xslt_set_encoding') &&
        function_exists('xslt_process') &&
        function_exists('xslt_free');
}

// Convertor feedback function

function inform($line)
{
    global $dotprinted;

    $line = htmlspecialchars($line);

    if ($dotprinted)
        echo '</td><tr><td>';
    else
        echo '</td><td>&nbsp;</td><tr><td>';

    $dotprinted = false;

    echo "$line\n";

    flush();
}

function warn($line)
{
    global $dotprinted;

    $line = htmlspecialchars($line);

    if ($dotprinted)
        echo '</td><tr><td>';
    else
        echo '</td><td>&nbsp;</td><tr><td>';

    $dotprinted = false;

    echo "<font color=red>$line</font>";

    flush();
}

function progress($percent)
{
    global $dotprinted;

    if (!$dotprinted)
        echo '&nbsp;</td><td>';

    $dotprinted = true;

    echo '.';

    flush();
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

            while (!gzeof($gz))
                $content .= gzread($gz, 1024);

            fclose($gz);

            $filename = $matches [1] . $matches [2];
        }

        unlink($tmpfile);

        $result |= ($gz !== false);
    }

    return $result;
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

// HTTP input parameters supporting function

function unslash($str)
{
    if (get_magic_quotes_gpc())
        $str = stripslashes($str);

    return $str;
}

// HTML output functions

function htmlheader($title = '')
{
    ?>
    <html>
        <head>
            <title><?= $title ?></title>
            <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
        </head>
        <body>
            <div align=center>
                <?php
            }

            function htmlfooter()
            {
                ?>
            </div>
        </body>
    </html>
    <?php
}

function progressheader()
{
    ?>
    <table border=1>
        <tr>
            <th>Action</th>
            <th>Progress</th>
        </tr>
        <tr>
            <td>&nbsp;
                <?php
            }

            function progressfooter()
            {
                global $dotprinted;

                if (!$dotprinted)
                    echo "</td><td>&nbsp;</td></tr>";
                ?>
    </table>
    <?php
}

function paramsheader($formparams)
{
    global $PHP_SELF, $colors;

    $url = $PHP_SELF;

    $colors = new Cicler(array('#f0f0f0', '#e0e0e0'));

    if ($formparams != '')
        $url .= '?' . $formparams;
    ?>
    <form action='<?= $url ?>' method=POST>
        <table>
            <tr>
                <th>Name</th>
                <th>Value</th>
            </tr>
            <tr>
                <td colspan=2>
                    <hr noshade align=left width="100%">
                </td>
                <?php
            }

            function paramsfooter()
            {
                ?>
            <tr>
                <td colspan=2>
                    <hr noshade align=left width="100%">
                </td>
        </table>
        <br>
        <input name=SubmitParams type=submit value='Accept changed paramters'>
        <input name=Convert type=submit value='Start convertion'>
    </form>
    <?php
}

function paraminput($name, $value)
{
    global $colors;

    $oname  = $name;
    $ovalue = $value;

    $input = '';

    $iname = "Params[$name]";

    if (is_array($value))
        $value = join("\n", $value);

    $itext = htmlspecialchars($value);

    if (is_bool($ovalue)) {
        $checked = ($value === true ? 'checked=1' : '');
        $input   = "<input name=$iname type=checkbox value=on $checked>";
    } else
    if (is_int($ovalue))
        $input = "<input name=$iname type=text value=\"$itext\">";
    else
    if (is_string($ovalue))
        $input = "<input name=$iname type=text size=40 value=\"$itext\">";
    else
    if (is_array($ovalue))
        $input = "<textarea name=$iname rows=10 cols=40>$itext</textarea>";

    $bgcolor = $colors->next();

    echo "<tr bgcolor='$bgcolor'>";
    echo "<td><b>$name</b></td>";
    echo "<td>$input</td>";
    echo "</tr>";
}

// Cached files functions

function cachedfileslist()
{
    global $cachedprefix, $cachedir, $levelseparator;

    $list = array();

    if ($dir = @opendir($cachedir)) {
        while (($file = readdir($dir)) !== false)
            if (strpos($file, $cachedprefix) === 0) {
                $fname = $cachedir . $levelseparator . $file;

                $list [$fname] = filemtime($fname);
            }

        closedir($dir);
    }

    return $list;
}

function canconvert()
{
    global $fileslimit;

    $list = cachedfileslist();

    if ($fileslimit <= 0)
        return true;
    else
        return (count($list) <= $fileslimit);
}

function delexpired()
{
    global $cachetimeout, $curtime;

    $list = cachedfileslist();

    foreach ($list as $file => $mtime)
        if ($curtime - $mtime > $cachetimeout)
            unlink($file);
}

//

class Cicler
{

    var $values;
    var $numvalues;
    var $counter;

    function Cicler($vals = array())
    {
        $this->numvalues = 0;

        if (is_array($vals)) {
            $i = 0;
            reset($vals);

            $val = current($vals);

            while ($val != false) {
                $this->values [$i++] = $val;
                $val                 = next($vals);
            }

            $this->numvalues = $i;
        }

        $this->counter = 0;
    }

    function cur()
    {
        if ($this->numvalues == 0)
            return;
        else
            return $this->values [($this->counter + $this->numvalues - 1) % $this->numvalues];
    }

    function next()
    {
        if ($this->numvalues == 0)
            return;

        $cur           = $this->counter;
        $this->counter = ($cur + 1) % $this->numvalues;

        return $this->values [$cur];
    }

}

/* vim600: set foldmethod=indent: */
