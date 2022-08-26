<?php
/*
 *
 * Apache dirlist replacement with links to FictionBook converter
 *
 * $Id: dirlist.php,v 1.7 2005/02/10 20:55:52 eliterr Exp eliterr $
 *
 */

$libdirs = array(
    'docfile'       => '/var/www/doc/',
    'debiandocfile' => '/var/www/debiandoc'
);

function mkfblink($fullfile, $dir, $file)
{
    global $libdirs;

    $found = false;
    reset($libdirs);
    while (!$found && (list ($paramname, $libdir) = each($libdirs))) {
        if (!strncmp($dir, $libdir, strlen($libdir)))
            $found = true;

        if ((substr($libdir, -1) != '/') && (substr($libdir, -1) != "\\"))
            $libdir .= '/';
    }

    if (!$found ||
        ($file == '..') ||
        !(
        ((is_file($fullfile) || is_link($fullfile)) &&
        is_readable($fullfile) &&
        preg_match('/(html|htm|txt)(.gz)?$/', $file)) ||
        (is_dir($fullfile) &&
        (is_readable("$fullfile/index.html") ||
        is_readable("$fullfile/index.htm")
        )
        )
        )
    )
        return '&nbsp;';

    if (is_dir($fullfile)) {
        if (is_readable("$fullfile/index.htm"))
            $wwwfile = $dir . $file . '/index.htm';
        else
        if (is_readable("$fullfile/index.html"))
            $wwwfile = $dir . $file . '/index.html';
    } else
        $wwwfile = $dir . $file;

    $paramfile = substr($wwwfile, strlen($libdir));
    $param     = $paramname . '=' . urlencode($paramfile);

    return "<a href=/Any2FB2/index.php?$param>Make FB2</a>";
}

function mytime($time)
{
    return date('d.m.Y H:m:s', $time);
}

function mysize($filename, $isdir)
{
    if ($isdir)
        return '-';

    return filesize($filename);
}

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

$request = urldecode(filter_input(INPUT_SERVER, 'REQUEST_URI'));
$sroot   = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');

$dir = $sroot . $request;

$icondir  = '/usr/share/apache/icons';
$iconpath = '/icons';
$iconext  = '.png';

$dirs = array();

$files = array('name'     => array(),
    'icons'    => array(),
    'iconalts' => array(),
    'modified' => array(),
    'size'     => array(),
    'fblink'   => array());

if ($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
        if ($file == '.')
            continue;

        if (($file != '..') && ($file{0} == '.'))
            continue;

        $fullfile = $dir . $file;
        $isdir    = is_dir($fullfile);

        $icon    = "$iconpath/unknown$iconext";
        $iconalt = '[   ]';

        unset($mime);

        if ($isdir) {
            $icon    = "$iconpath/folder$iconext";
            $iconalt = "[DIR]";
        } else
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($fullfile);

            if (preg_match('+(.*)/(x-)?(.*)+', $mime, $matches)) {
                $major = $matches [1];
                $minor = $matches [3];

                if (($minor == 'zip') || ($minor == 'rar') || ($minor == 'arj'))
                    $minor = 'compressed';

                if (file_exists("$icondir/$minor$iconext")) {
                    $icon    = "$iconpath/$minor$iconext";
                    $iconalt = "[$minor]";
                } else
                if (file_exists("$icondir/$major$iconext")) {
                    $icon    = "$iconpath/$major$iconext";
                    $iconalt = "[$major]";
                }
            }
        }

        $files ['name'] []     = $file . ($isdir ? '/' : '');
        $files ['icons'] []    = $icon;
        $files ['iconalts'] [] = $iconalt;
        $files ['modified'] [] = mytime(filemtime($fullfile));
        $files ['size'] []     = mysize($fullfile, $isdir);
        $files ['fblink'] []   = mkfblink($fullfile, $dir, $file);
    }

    closedir($dh);
}

array_multisort($files ['name'], $files ['icons'], $files ['iconalts'], $files ['modified'], $files ['size'], $files ['fblink']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN"
    "http://www.w3.org/TR/REC-html40/loose.dtd">
<HTML>
    <HEAD>
        <TITLE>Index of <?= $request ?></TITLE>
        <META NAME="generator", CONTENT="any2fb2_dirlist">
        <meta http-equiv='Content-Type' content='text/html; charset=koi8-r'>
        <style type='text/css'>
            td.filename { font-family: monospace }
        </style>
    </HEAD>
    <BODY bgcolor="#ffffff" text="#000000">

        <TABLE><TR><TD bgcolor="#ffffff" class="title">
                    <FONT size="+3" face="Helvetica,Arial,sans-serif">
                        <B>Index of <?= $request ?></B></FONT>

                </TD></TR></TABLE>
        <TABLE>
            <TR>
                <TH>&nbsp;</TH>
                <TH>Name</TH>
                <TH>Last modified</TH>
                <TH>Size</TH>
                <TH>&nbsp;</TH>
            </TR>
            <tr>
                <td colspan=5><hr noshade align=left width="100%">
            </tr>
            <?
            $colors = new Cicler (array ('#f0f0f0', '#e0e0e0'));

            foreach ($files ['name'] as $idx => $name)
            {
            $linkname = htmlspecialchars ($name);
            $linklink = mklink ($name);
            $color = $colors->next ();
            echo "<tr bgcolor='$color'>\n";
            echo "<td><img src='{$files ['icons'] [$idx]}' alt='{$files ['iconalts'] [$idx]}'></td>\n";
            echo "<td class=filename><a href='$linklink'>$linkname</a></td>\n";
            echo "<td>{$files ['modified'] [$idx]}</td>\n";
            echo "<td align=right>{$files ['size'] [$idx]}</td>\n";
            echo "<td>{$files ['fblink'] [$idx]}</td>\n";
            echo "</tr>\n";
            }
            ?>
            <tr>
                <td colspan=5><hr noshade align=left width="100%">
            </tr>
        </TABLE>
    </BODY></HTML>
<?

function mklink ($link)
{
$link = str_replace (' ', '%20', $link);
$link = str_replace ("'", '%27', $link);
$link = str_replace ('"', '%22', $link);

return $link;
}
/* vim600: set foldmethod=indent: */
?>
