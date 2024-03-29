<?php

/*
 * Main Any2fb2 file, class implementation
 *
 * $Id: tx2fb.php,v 1.55 2005/02/15 20:25:23 eliterr Exp eliterr $
 *
 */

define('SUNFORMAT', false);
define('STAG', 1);
define('SREADY', 100);

/*
 * PArr is designed for holding natural-numbered data
 * with associated values.
 *
 * Those who use it can access count/data/flags directly or via references
 *
 * To have insertion and deletion work data and values keys should
 * be always in sync
 *
 * Deleting is quite heavy procedures so it is recommended
 * to make array by mkleaves, mark deleted lines as false and call leavekeys
 *
 */

class PArr
{

    var $arr   = array();
    var $flags = array();
    var $count = 0;

    public function PArr()
    {

    }

    public function clear()
    {
        $this->arr   = array();
        $this->flags = array();
        $this->count = 0;
    }

    public function copyfrom(&$oldparr)
    {
        $this->arr   = $oldparr->arr;
        $this->flags = $oldparr->flags;
        $this->count = $oldparr->count;
    }

    public function fromarr(&$arr)
    {
        $this->arr   = array();
        $this->flags = array();

        foreach ($arr as $line)
            $this->arr [] = $line;

        $this->count = count($this->arr);
        $this->flags = array_fill(0, $this->count, false);
    }

    public function fromstring(&$string)
    {
        $arr = preg_split("/\n/", $string);

        $this->fromarr($arr);
    }

    public function tostring()
    {
        return join("\n", $this->arr);
    }

    public function get($key)
    {
        return $this->arr [$key];
    }

    public function getflag($key)
    {
        return $this->flags [$key];
    }

    public function add($value, $flag = false)
    {
        $this->arr []   = $value;
        $this->flags [] = $flag;
        $this->count++;

        return $this->count - 1;
    }

    public function insert($key, $val, $flag = false)
    {
        array_splice($this->arr, $key, 0, $val);
        array_splice($this->flags, $key, 0, $flag);

        $this->count++;
    }

    public function delete($key, $howmany = 1)
    {
        if (isset($this->arr [$key]) && $howmany) {
            array_splice($this->arr, $key, $howmany);
            array_splice($this->flags, $key, $howmany);

            $this->count -= $howmany;
        }
    }

    public function mkleaves()
    {
        return array_fill(0, $this->count, true);
    }

    public function leavekeys(&$keys)
    {
        $count    = $this->count;
        $newcount = 0;

        foreach ($keys as $key => $leave)
            if ($leave)
                $keys [$key] = $newcount++;
            else
                $keys [$key] = $count;

        $arr   = $this->arr;
        $flags = $this->flags;

        array_multisort($keys, $arr, $flags);

        $this->arr   = array_slice($arr, 0, $newcount);
        $this->flags = array_slice($flags, 0, $newcount);
        $this->count = $newcount;
    }

    public function set($key, $val, $flag)
    {
        if (!isset($this->arr [$key]))
            $this->count++;

        $this->arr [$key]   = $val;
        $this->flags [$key] = $flag;
    }

    public function indexof($test)
    {
        foreach ($this->arr as $key => $val)
            if (!strcmp($val, $test))
                return $key;

        return false;
    }

    public function dump()
    {
        echo $this->tostring();
    }

}

@include ('gif.php');

class tx2fb
{

    var $classversion       = 'any2fb2.php class rev $Revision: 1.55 $ $Date: 2005/02/15 20:25:23 $';
    var $Params             = array('Skip_Unknown_Extensions'          => false
        , 'Do_Not_Detect_Charset'            => false
        , 'Do_Not_Autodetect_Filetype'       => false
        , 'Skip_Html_Tags'                   => false
        , 'Do_Not_Cleanup_Html_Forms'        => false
        , 'Text_Structure_Emptylines'        => false
        , 'Do_Not_Autodetect_Headers'        => false
        , 'Header_Must_Match_Regexp'         => ''
        , 'Ignore_Spaces_At_Line_Start'      => false
        , 'Min_Line_Length'                  => 80
        , 'Do_Not_Detect_Italic_Text'        => false
        , 'Do_Not_Detect_Footnotes'          => false
        , 'Do_Not_Convert_Quotes'            => false
        , 'Do_Not_Make_First_Dash_Long'      => false
        , 'Do_Not_Make_Emptylines'           => false
        , 'Do_Not_Search_Epigraphs'          => false
        , 'Do_Not_Restore_Broken_Paragraphs' => false
        , 'Do_Not_Search_Description'        => false
        , 'Do_Not_Detect_Poems'              => false
        , 'Regexps_At_Start'                 => array()
        , 'Regexps_At_Finish'                => array()
        , 'Follow_Links_Matching'            => ''
        , 'Do_Not_Follow_Links_Matching'     => ''
        , 'Link_Follow_Deep'                 => 0
        , 'Remove_External_Links'            => false
        , 'Download_External_Files'          => false
        , 'Remove_All_Images'                => false
        , 'Keep_Dynamic_Images'              => false
        , 'Do_Not_Check_XML'                 => false
        , 'Tries_To_Fix_XML'                 => 0
    );
    var $HTMLTags           = array('!',
        'a',
        'abbr',
        'acronym',
        'address',
        'applet',
        'area',
        'b',
        'base',
        'basefont',
        'bdo',
        'big',
        'blockquote',
        'body',
        'br',
        'button',
        'caption',
        'center',
        'cite',
        'code',
        'col',
        'colgroup',
        'dd',
        'del',
        'dfn',
        'dir',
        'div',
        'dl',
        'dt',
        'em',
        'fieldset',
        'font',
        'form',
        'frame',
        'frameset',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'head',
        'hr',
        'html',
        'i',
        'iframe',
        'img',
        'input',
        'ins',
        'isindex',
        'kbd',
        'label',
        'legend',
        'li',
        'link',
        'listing',
        'map',
        'menu',
        'meta',
        'noframes',
        'noscript',
        'object',
        'ol',
        'optgroup',
        'option',
        'p',
        'param',
        'plaintext',
        'pre',
        'q',
        'rb',
        'rbc',
        'rp',
        'rt',
        'rtc',
        'ruby',
        's',
        'samp',
        'script',
        'select',
        'small',
        'span',
        'strike',
        'strong',
        'style',
        'sub',
        'sup',
        'table',
        'tbody',
        'td',
        'textarea',
        'tfoot',
        'th',
        'thead',
        'title',
        'tr',
        'tt',
        'u',
        'ul',
        'var',
        'xmp',
        'nextid',
        'align',
        'bgsound',
        'blink',
        'comment',
        'embed',
        'ilayer',
        'keygen',
        'layer',
        'marquee',
        'multicol',
        'nobr',
        'noembed',
        'nolayer',
        'nosave',
        'server',
        'servlet',
        'spacer',
        'wbr');
    var $informfunc         = '';   // Inform   public function, argument - text
    var $warningfunc        = '';   // Warning  public function, argument - text
    var $progressfunc       = '';   // Progress public function, argument - percents
    var $unknownfilefunc    = ''; // File convertion: unk (&$filename, &$content)
    //   returns
    //     true if converted
    //     new text in $content is converted
    //     new filename in $filename if converted
    var $getexternaldocfunc = ''; // Get external document public function, argument - name
    var $getexternalimgfunc = ''; // Get external image public function, argument - name
    var $Tempdir;
    var $_Entities;
    var $_badentsearch;
    var $_badentreplace;
    var $_ThisDocName;
    var $_EndZnak;
    var $_HeaderSmartRE     = '';
    var $_HeadDetectChars   = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'V', 'X', 'I', 'L', 'M', '*', '@');
    var $_winlower;
    var $_winupper;
    var $_LineChar;
    var $_IsInHTMLMode      = false;
    var $_fauthor           = '';
    var $_mauthor           = '';
    var $_lauthor           = '';
    var $_title             = '';
    var $_description;
    var $_idprefix;
    var $_done;
    var $_skipped;
    var $_footnotes;
    var $_binaries;
    var $_usedfiles;
    var $_gdgiftopng;
    var $_windows;
    var $_maindirsep;

    public function tx2fb()
    {
        $ents = array('lt'     => chr(60),
            'gt'     => chr(62),
            'quot'   => chr(34),
            'nbsp'   => chr(160),
            'mdash'  => chr(227),
            'iexcl'  => chr(161),
            'cent'   => chr(162),
            'pound'  => chr(163),
            'curren' => chr(164),
            'yen'    => chr(165),
            'brvbar' => chr(166),
            'sect'   => chr(167),
            'uml'    => chr(168),
            'copy'   => chr(169),
            'ordf'   => chr(170),
            'laquo'  => chr(171),
            'not'    => chr(172),
            'shy'    => chr(173),
            'reg'    => chr(174),
            'macr'   => chr(175),
            'deg'    => chr(176),
            'plusmn' => chr(177),
            'sup2'   => chr(178),
            'sup3'   => chr(179),
            'acute'  => chr(180),
            'micro'  => chr(181),
            'para'   => chr(182),
            'middot' => chr(183),
            'cedil'  => chr(184),
            'sup1'   => chr(185),
            'ordm'   => chr(186),
            'raquo'  => chr(187),
            'frac14' => chr(188),
            'frac12' => chr(189),
            'frac34' => chr(190),
            'iquest' => chr(191),
            'Agrave' => chr(192),
            'Aacute' => chr(193),
            'Acirc'  => chr(194),
            'Atilde' => chr(195),
            'Auml'   => chr(196),
            'Aring'  => chr(197),
            'AElig'  => chr(198),
            'Ccedil' => chr(199),
            'Egrave' => chr(200),
            'Eacute' => chr(201),
            'Eirc'   => chr(202),
            'Euml'   => chr(203),
            'Igrave' => chr(204),
            'Iacute' => chr(205),
            'Icirc'  => chr(206),
            'Iuml'   => chr(207),
            'ETH'    => chr(208),
            'Ntilde' => chr(209),
            'Ograve' => chr(210),
            'Oacute' => chr(211),
            'Ocirc'  => chr(212),
            'Otilde' => chr(213),
            'Ouml'   => chr(214),
            'times'  => chr(215),
            'Oslash' => chr(216),
            'Ugrave' => chr(217),
            'Uacute' => chr(218),
            'Ucirc'  => chr(219),
            'Uuml'   => chr(220),
            'Yacute' => chr(221),
            'THORN'  => chr(222),
            'szlig'  => chr(223),
            'agrave' => chr(224),
            'aacute' => chr(225),
            'acirc'  => chr(226),
            'atilde' => chr(227),
            'auml'   => chr(228),
            'aring'  => chr(229),
            'aelig'  => chr(230),
            'ccedil' => chr(231),
            'egrave' => chr(232),
            'eacute' => chr(233),
            'ecirc'  => chr(234),
            'euml'   => chr(235),
            'igrave' => chr(236),
            'iacute' => chr(237),
            'icirc'  => chr(238),
            'iuml'   => chr(239),
            'eth'    => chr(240),
            'ntilde' => chr(241),
            'ograve' => chr(242),
            'oacute' => chr(243),
            'ocirc'  => chr(244),
            'otilde' => chr(245),
            'ouml'   => chr(246),
            'divide' => chr(247),
            'oslash' => chr(248),
            'ugrave' => chr(249),
            'uacute' => chr(250),
            'ucirc'  => chr(251),
            'uuml'   => chr(252),
            'yacute' => chr(253),
            'thorn'  => chr(254),
            'yuml'   => chr(255));

        foreach ($ents as $ent => $char)
            $this->_Entities [$ent] = $char;

        $this->_badentsearch  = array();
        $this->_badentreplace = array();

        $this->_badentsearch []  = "/&#/i";
        $this->_badentreplace [] = "_%38%_#";

        foreach ($this->_Entities as $ent => $char)
            if (($ent == 'lt') || ($ent == 'gt') || ($ent == 'amp')) {
                $this->_badentsearch []  = "/&$ent(;?|\b)/i";
                $this->_badentreplace [] = "_%38%_$ent;";
            } else {
                $this->_badentsearch []  = "/&$ent(;?|\b)/i";
                $this->_badentreplace [] = $char;
            }

        $this->_badentsearch []  = '/&/';
        $this->_badentreplace [] = '&amp;';

        $this->_badentsearch []  = '/_%38%_/';
        $this->_badentreplace [] = '&';

        $eznak = array('.', '!', '?', '"', ':', chr(187), "'", chr(133));

        foreach ($eznak as $znak)
            $this->_EndZnak [] = $znak;

        $this->LineChar = chr(150);

        $this->_winlower = convert_cyr_string('БВЧЗДЕЈЦЪЙКЛМНОПРТУФХЖИГЮЫЭЯЩШЬАС', 'k', 'w');
        $this->_winupper = convert_cyr_string('бвчздеіцъйклмнопртуфхжигюыэящшьас', 'k', 'w');

        $this->_gdgiftopng = extension_loaded('gd') &&
            function_exists('imagecreatefromgif') &&
            function_exists('imagepng');

        $this->_windows = (strtoupper(substr(PHP_OS, 0, 3) == 'WIN'));

        if ($this->_windows) {
            $this->Tempdir     = 'C:\Windows\Temp';
            $this->_maindirsep = "\\";
        } else {
            $this->Tempdir     = '/tmp';
            $this->_maindirsep = '/';
        }
    }

    public function _warn($text)
    {
        if ($this->warningfunc <> '') {
            $func = $this->warningfunc;
            $func($text);
        }
    }

    public function _inform($text)
    {
        if ($this->informfunc <> '') {
            $func = $this->informfunc;
            $func($text);
        }
    }

    public function _progress($text)
    {
        if ($this->progressfunc <> '') {
            $func = $this->progressfunc;
            $func($text);
        }
    }

    public function _unknownfile(&$filename, &$filecontent)
    {
        if ($this->unknownfilefunc <> '') {
            $func = $this->unknownfilefunc;
            return $func($filename, $filecontent);
        } else
            return false;
    }

    public function _getexternalimage($filelocation)
    {
        if ($this->getexternalimgfunc <> '') {
            $func = $this->getexternalimgfunc;
            return $func($filelocation);
        } else
            return false;
    }

    public function _getexternaldoc($filelocation)
    {
        if ($this->getexternaldocfunc <> '') {
            $func = $this->getexternaldocfunc;
            return $func($filelocation);
        } else
            return false;
    }

    var $_emptylines = array('', "\x03", '<p>&nbsp;</p>', '<p> &nbsp;</p>', '<empty-line/>');

    public function _lineempty($s)
    {
        return in_array(trim($s), $this->_emptylines);
    }

    public function _allowedtag($buf)
    {
        $tags = array('<strong>', '</strong>',
            '<emphasis>', '</emphasis>',
            '<imagex', '<a ',
            '<id ', '<p>', '</x:a>',
            '<empty-line/>');

        foreach ($tags as $tag)
            if (strpos($buf, $tag) !== false)
                return true;

        return false;
    }

    public function _isdialchar($c)
    {
        return in_array($c, array(chr(150), '-', chr(227)));
    }

    public function _cleantags($s)
    {
        return trim(preg_replace('/<[^>]*>/', '', $s));
    }

    public function _myextractfilename($str)
    {
        $s = preg_replace('-.*[\\/]([^\\/])(\?.*)?-', '\1', $str);

        if (($pos = strpos($s, '?')) !== false)
            $s   = substr($s, 0, $pos - 1);

        $s = str_replace('"', '_', $s);
        $s = str_replace('&', '&amp;', $s);

        return $s;
    }

    public function _delent($s)
    {
        return preg_replace($this->_badentsearch, $this->_badentreplace, $s);
    }

    public function _firstchar($s)
    {
        $s = preg_replace('/^ +/', '', $s);

        if (strlen($s))
            return $s{0};
        else
            return ' ';
    }

    public function _decodestring($s)
    {
        $lookslikedos = 0;

        foreach (array(160, 161, 162, 167, 173, 160, 173, 149, 151, 175) as $test)
            if (strpos($test, $s) !== false)
                $lookslikedos++;

        $lookslikekoi = 0;

        $to = strlen($s);
        if (strlen($s) > 2000)
            $to = 2000;

        for ($i = 1000; $i < $to; $i++) {
            $chr = ord($s {$i});

            if (($chr >= 0xE0) && ($chr <= 0xFF))
                $lookslikekoi--;
            else
            if (($chr >= 0xC0) && ($chr <= 0xDF))
                $lookslikekoi++;
        }

        if ($this->Params ['Do_Not_Detect_Charset']) {
            if ($lookslikedos > 8) {
                $this->_warn('Text seems to be IBM866 encoded, but text conversion was disabled');
            } else
            if ($lookslikekoi > 200) {
                $this->_warn('Text seems to be KOI8-R encoded, but text conversion was disabled');
            }

            return $s;
        }

        if ($lookslikedos > 8) {
            $this->_inform('IBM866 encoding detected, converting to CP1251');
            $s = convert_cyr_string($s, 'd', 'w');
        } else
        if ($lookslikekoi > 200) {
            $this->_inform('KOI8-R encoding detected, converting to CP1251');
            $s = convert_cyr_string($s, 'k', 'w');
        }

        return $s;
    }

    public function _wintoupper($s)
    {
        return strtr($s, $this->_winlower, $this->_winupper);
    }

    public function _wintolower($s)
    {
        return strtr($s, $this->_winupper, $this->_winlower);
    }

    public function _iswinupper($s)
    {
        return !strcmp($s, $this->_wintoupper($s));
    }

    public function _findheader(&$str, &$fauthor, &$mauthor, &$lauthor, &$title)
    {
        $s       = $author  = $fauthor = $mauthor = $lauthor = $title   = '';

        $s = substr($str, 0, min(3000, strlen($str)));

        $starttaglen = 1;

        $titleclose = "\x15";

        $i = strpos($s, '<title>');

        if ($i !== false) {
            $titleclose  = '</title>';
            $starttaglen = 7;
        } else
            $i = strpos($s, "\x14");

        if ($i !== false) {
            // TODO: optimize
            $stitle = substr($s, $i + $starttaglen, strlen($s) - $i - $starttaglen);
            $stitle = substr($stitle, 0, strpos($stitle, $titleclose));
            $stitle = substr($str, $i + $starttaglen, strlen($stitle));

            $title = $stitle;

            $title = str_replace('<', '&lt;', $title);
        }

        // TODO: preg
        $i = strpos($s, '<meta name="author" content="');

        if ($i !== false) {
            // TODO: optimize
            $author = substr($str, $i + 29, strlen($s) - $i - 29);
            $author = substr($author, 0, strpos($author, '"'));
        } else
        if (($title != '') && ($lauthor == '') && (strpos($title, '.') !== false)) {
            $author = substr($title, 0, strpos($title, '.'));
            $title  = substr($title, strlen($author) + 2);
        }

        $title = trim($title);

        if ($author != '') {
            $author = trim($author);

            if (($spacepos = strpos($author, ' ')) !== false) {
                $fauthor = substr($author, 0, $spacepos);
                $author  = trim(substr($author, $spacepos + 1));

                if (($spacepos = strpos($author, ' ')) !== false) {
                    $mauthor = substr($author, 0, $spacepos);
                    $author  = trim(substr($author, $spacepos + 1));
                    $lauthor = $author;
                } else
                    $lauthor = $author;
            } else
                $lauthor = $author;
        }
    }

    public function _detectformat($str)
    {
        if (($this->Params ['Do_Not_Autodetect_Filetype']) ||
            (strlen($str) < 2000))
            return;

        if (strlen($str) < 1000)
            $ends = strlen($str) - 1;
        else
            $ends = 10000;

        $s = substr($str, 2000, $ends - 2000);

        $lines = split("\n", $s);

        if (count($lines) < 6)
            return;

        $ashtml    = 0;
        $asfixed76 = 0;
        $asfixed80 = 0;
        $asline    = 0;
        $nospaces  = 0;

        for ($i = 1; $i < count($lines) - 2; $i++) {
            $s = strtolower($lines [$i]);

            if (strpos($s, '     ') === 0) {
                $asfixed76 += 2;
                $asfixed80 += 2;
                $nospaces  -= 10;
            } else
                $nospaces++;

            if (strpos($s, '              ') === 0)
                $nospaces -= 100;

            if (strpos($s, '<pre') !== false) {
                $asfixed76 += 10;
                $asfixed80 += 10;
            }

            if ((strlen($s) <= 80) && (strlen($s) > 76))
                $asfixed80++;
            else
            if (strlen($s) == 76)
                $asfixed76++;
            else
            if (strlen($s) > 80)
                $asline++;

            if ((strpos($s, '<br>') !== false) ||
                (strpos($s, '<p') !== false) ||
                (strpos($s, '<div') !== false))
                $ashtml += 2;

            if (strpos($s, '<!doctype html') !== false)
                $ashtml += 30;
        }

        $asfixed80 /= 1.3;
        $asfixed76 /= 1.3;

        if (!$this->Params ['Skip_Html_Tags'] &&
            ($ashtml > $asfixed80) && ($ashtml > $asfixed76) &&
            ($ashtml > $asline)) {
            $this->Params ['Min_Line_Length']             = 1000000000;
            $this->Params ['Ignore_Spaces_At_Line_Start'] = true;

            $this->_inform('Text was recognized as html');
            $this->_IsInHTMLMode = true;
        } else {
            $this->Params ['Ignore_Spaces_At_Line_Start'] = false;

            if (($asline > $ashtml) && ($asline > $asfixed80) && ($asline > $asfixed76)) {
                $this->Params ['Min_Line_Length'] = 1;
                $this->_inform('Text was recognized as traditional TXT (line==paragraph)');
            } else
            if ($asfixed76 > $asfixed80) {
                $this->Params ['Min_Line_Length'] = 76;
                $this->_inform('Text was recognized as fixed-width TXT (width=76)');
            } else {
                $this->Params ['Min_Line_Length'] = 80;
                $this->_inform('Text was recognized as fixed-width TXT (width=80)');
            }

            if ($nospaces > 20) {
                $this->Params ['Text_Structure_Emptylines'] = true;
                $this->_inform('Headers detection is set to LIGHT, some headers may be missed...');
            }
        }
    }

    public function _cleanuptext($input)
    {
        if (count($this->Params ['Regexps_At_Start'])) {
            $this->_inform('Running user regular expressions...');

            $regexps = $this->Params ['Regexps_At_Start'];

            if (count($regexps) % 2 == 1)
                $regexps [] = '';

            for ($i = 0; $i < count($regexps); $i += 2) {
                $r1 = trim($regexps [$i]);
                $r2 = trim($regexps [$i + 1]);

                $this->_inform("Regexp: /$r1/$r2/");
                $newtext = @preg_replace("/$r1/", $r2, $input);
                if (!is_null($newtext))
                    $input   = $newtext;
                else
                    $this->_warn("Regexp error");
            }
        }

        $this->_inform('Removing non-html tags');

        $input = preg_replace("/[\x01\x02\x03\x04\x05\x06\x07]*/", '', $input);

        if (!$this->Params ['Skip_Html_Tags']) {
            $matches = array();

            $alltags = join('|', $this->HTMLTags);

            $input = preg_replace('-<\s*(/?)\s*\b(' . $alltags . ')\b([^>]*)>-i', '<\1\2\3>', $input);

            foreach ($this->HTMLTags as $tag)
                $input = preg_replace('-<(/?)\b' . $tag . '\b([^>]*)>-i', "\x01\\1$tag\\2\x02", $input);

            $input = preg_replace('-<\s*!([^>]*)>-i', "\x01!\\1\\2\x02", $input);

            $input = str_replace('<', '&lt;', $input);
            $input = str_replace('>', '&gt;', $input);

            $input = str_replace("\x01", '<', $input);
            $input = str_replace("\x02", '>', $input);
        } else {
            $input = str_replace('<', '&lt;', $input);
            $input = str_replace('>', '&gt;', $input);
        }


        $this->_inform('Preparing text for parsing...');
        $input = str_replace("\x14", '<h1>', $input);
        $input = str_replace("\x15", '</h1>', $input);

        $this->_progress(8);

        if (!$this->Params ['Remove_All_Images'])
            $input = preg_replace('/<\s*img[\s][^>]*?src=(["\']?)([^\'">]+)\1[^>]*?>/im', "\x03\\2\x03", $input);

        $input = preg_replace('-<\s*a\s[^>]*>\s*<\s*/a[^>]*>-im', '', $input);

        $input = preg_replace('-<\s*a\s[^>]*?name=(["\']?)([^\'">]+)\1[^>]*?>(.*?)<\s*/a[^>]*>-im', "\x01\\2\x01\\3", $input);

        $this->_progress(10);

        $input = preg_replace('-<\s*a\s[^>]*?name=(["\']?)([^\'">]+)\1[^>]*?>-im', "\x01\\2\x01\\3", $input);

        $this->_progress(15);

        if (!$this->Params ['Remove_External_Links']) {
            $input = preg_replace("\x01" . '<\s*a\s[^>]*?href=(["\']?)' . $this->_ThisDocName .
                '#([^\'">]+)\1[^>]*?>([\s\S]*?)</\s*a\s*>' . "\x01im", "\x02#\\2\x02\\3\x02", $input);
            $input = preg_replace("\x01" . '<\s*a\s[^>]*?href=(["\']?)([^\'">]+)\1[^>]*?>' .
                '([\s\S]*?)</\s*a\s*>' . "\x01im", "\x02\\2\x02\\3\x02", $input);
        } else
            $input = preg_replace('-<\s*a\s[^>]*?href=(["\']?)' . $this->_ThisDocName .
                '#([^\'">]+)\1[^>]*?>([\s\S]*?)</\s*a\s*>-im', "\x02#\\2\x02\\3\x02", $input);

        $this->_progress(20);
        $input = preg_replace('-<\s*([\w]+)[^>]*?>-', '<\1>', $input);

        $this->_progress(28);
        $input = preg_replace("/\x03([^\x03]+?)\x03/", '<p><imagex xlink:href="\1"/></p>', $input);

        $this->_progress(35);
        $input = preg_replace("/\x01([^\x01]+?)\x01/", '<id id="\1"/>', $input);

        $this->_progress(42);
        $input = preg_replace("/\x02([^\x02]*?)\x02([^\x02]*?)\x02/", '<a xlink:href="\1">\2</x:a>', $input);

        $this->_progress(48);

        $input = preg_replace('-<a xlink:href="[^"]*[\[\{][^"]*"(.*?)</x:a>-', '\1', $input);
        $this->_progress(50);
        $input = preg_replace('-<script>[\s\S]*?</script>-im', '', $input);

        $this->_progress(58);
        $input = preg_replace('-</?(span|font|o:p)>-i', '', $input);
        $input = preg_replace('/<![^>]*>/', '', $input);

        $this->_progress(65);
        $input = preg_replace('-<(script|head)>[\s\S]*?</\1>-im', '', $input);
        $input = preg_replace('-</?(html|head|meta|body)>-i', '', $input);

        $this->_progress(72);
        if (!$this->Params ['Do_Not_Cleanup_Html_Forms'])
            $input = preg_replace('-<form>[\s\S]{0,2000}?</form>-im', '', $input);
        $input = preg_replace('-<(/?)(i|sup|sub|em|code|tt)>-im', '<\1emphasis>', $input);

        $this->_progress(80);
        $input = preg_replace('/<(strong|emphasis)>([^<]*?)<\1>/i', '<\1>\2', $input);

        $this->_progress(90);
        $input = preg_replace('-<(/?)(b|strong)>-i', '<\1strong>', $input);

        $input = preg_replace('/<!--.*?-->/', '', $input);
        $input = preg_replace('/<!--.*/', '', $input);

        $input = preg_replace('-</p[^>]*>-', '', $input);

        $input = preg_replace('-</?(font|cpan)>-i', '', $input);

        $input = preg_replace('/\r+\n/', "\n", $input);

        $this->_progress(100);

        return $input;
    }

    public function _getthetext($text, $loadfromstring)
    {
        $list = array();

        $inputstring        = $text;
        $this->_ThisDocName = "[^'\"#>]*?";

        if (!$loadfromstring) {
            if (strpos(strtolower($text), 'http://') === 0) {
                // TODO: retrive from http
                $this->_inform("Retriving external document '$text'");

                $inputstring = $this->_getexternaldoc($text);

                if (strpos($text, '?') !== false) {
                    $buf                = preg_replace('-^.*/-', '', $text);
                    $buf                = preg_quote(substr($buf, 1));
                    $this->_ThisDocName = '(?:[^\'\'"#>]?' . $buf . ')?';
                } else
                    $this->_ThisDocName = '';
            } else {
                $this->_inform("Loading data from file '$text'...");

                $inputstring = @file_get_contents($text);

                $buf                = preg_replace('/^(.*)\.[^.]+$/', '\1', $text);
                $buf                = preg_replace('/\./', '\.', $buf);
                $buf                = preg_quote($buf);
                $this->_ThisDocName = "(?:[^'\"#>]*?" . $buf . ")?";
            }

            if (!strlen($inputstring)) {
                $this->_warn("Can't fetch, sorry");
                return false;
            }

            if (strlen($inputstring) < 10)
                $this->_warn('File is VERY short. This may cause an error');

            $unknown = false;

            if (!preg_match('/(txt|htm|html|prt)$/i', $text)) {
                $newfilename = $text;

                $unknown = !$this->_unknownfile($text, $inputstring);
            }

            if ($unknown) {
                $this->_warn('Unknown file extension!');

                if ($this->Params ['Skip_Unknown_Extensions'])
                    return false;
            }

            $ext = strtoupper(preg_replace('/^.*\./', '', $text));

            if (preg_match('/(htm|html)$/i', $text))
                $this->_IsInHTMLMode = true;
        }

        $inputstring = $this->_decodestring($inputstring);

        if (!$this->_usedfiles->count)
            $this->_findheader($inputstring, $this->_fauthor, $this->_mauthor, $this->_lauthor, $this->_title);

        $this->_detectformat($inputstring);

        $inputstring = $this->_cleanuptext($inputstring);

        $result = preg_split("/\r?\n/", $inputstring);

        $this->_inform('Text loaded OK, ' . count($result) . ' lines total.');
        return $result;
    }

    public function _cleartag($s)
    {
        $s = preg_replace('/ +([<>])/', '\1', $s);
        $s = preg_replace('/([<>]) +/', '\1', $s);
        return preg_replace('/ +/', ' ', $s);
    }

    public function _reallyempty($s)
    {
        return preg_match("/^[\r\n\t ]*$/", $s);
    }

    public function _validhtml(&$strings)
    {
        // Parsing text line by line, stripping tags, tabs, comments etc

        $this->_inform('Marking html tags...');

        $news = new PArr ();

        $news->add('');
        $news->add('');

        $arr   = &$strings->arr;
        $flags = &$strings->flags;
        $count = &$strings->count;

        $narr   = &$news->arr;
        $nflags = &$news->flags;
        $ncount = &$news->count;

        $prev = -1;

        $i       = 0;
        $curline = '';
        $curtag  = '';
        $oldi    = -1;

        $notinhtmlstate = 0;
        $msgshown       = false;

        while ($i < $count) {
            // Display progress
            if (($cur = round(100 * $i / $count)) != $prev) {
                $this->_progress($cur);
                $prev = $cur;
            }

            // If we are progressed to the next line, beautify previos one
            if ($i > $oldi) {
                if (($arr [$i] != '') && ($curline != ''))
                    $curline .= " ";

                $curline .= $arr [$i];

                $curline = str_replace("\x09", "  ", $curline);
                $curline = str_replace("\xA0", " ", $curline);
            }

            $oldi = $i;

            // Comments already stripped
            // Look for tags
            $posit = strpos($curline, '<');

            if ($posit === false) {
                if ($notinhtmlstate || !$this->_IsInHTMLMode) {
                    // If there is no tags but we are in HTML, skip newline
                    // Else move to current buffer results
                    if ($this->_reallyempty($curline))
                        $news->add('', SUNFORMAT);
                    else
                        $news->add($curline, SUNFORMAT);

                    $curline = '';
                }

                $i++;
                continue;
            }

            if ($posit) {
                // Tag isn't in the beginning of line.
                // Copy start of line to previous

                $buf = substr($curline, 0, $posit);

                if (!$notinhtmlstate && $this->_IsInHTMLMode &&
                    ($nflags [$ncount - 1] == SUNFORMAT))
                    $narr [$ncount - 1] .= ' ' . $buf;
                else {
                    if (($notinhtmlstate == 1) || !$this->_IsInHTMLMode ||
                        !$this->_lineempty($buf))
                        $news->add($buf, SUNFORMAT);
                }

                $curline = substr($curline, $posit);

                continue;
            }

            // There is a tag at the start of this line
            $posit = strpos($curline, '>');
            if ($posit !== false) {
                // If tag end is found, wipe it out

                if ((strpos($curline, '</emphasis>') === ($posit - 11)) ||
                    (strpos($curline, '</strong>') === ($posit - 8)) ||
                    (strpos($curline, '</a>') === ($posit - 3))) {
                    $falseposit = strpos($curline, '<', 1);

                    if ($falseposit == false) {
                        $news->add($curline, SUNFORMAT);
                        $i++;
                        $curline = '';
                    } else {
                        $news->add(substr($curline, 0, $falseposit), SUNFORMAT);
                        $curline = substr($curline, $falseposit);
                    }

                    continue;
                } else {
                    $curtag = $this->_cleartag(substr($curline, 0, $posit + 1));

                    $news->add($curtag, STAG);

                    if (strpos($curtag, '<pre') === 0)
                        $notinhtmlstate++;
                    else
                    if (strpos($curtag, '</pre') === 0)
                        $notinhtmlstate--;

                    $curline = substr($curline, $posit + 1);

                    if ($curline == '')
                        $i++;
                    else
                        $curline = preg_replace("/^[ \x09]+/", '', $curline);
                }

                continue;
            } else
            if (!$msgshown && ($curline{0} == '<') && (strlen($curline) > 1024)) {
                $this->_warn('VERY long tag found. This probably is an error, large amount of text may be lost! Tag text:');
                $this->_warn(substr($curline, 0, 256));
                $msgshown = true;
            }

            $i++;
        }

        if ($curline != '') {
            if ($curline {0} == '<')
                $curline = substr_replace($curline, '&lt;', 0, 1);

            $news->add($curline, SUNFORMAT);
        }

        $news->add('');
        $news->add('');

        $strings->copyfrom($news);
    }

    public function _formatq(&$strings)
    {
        if ($this->Params ['Do_Not_Convert_Quotes'])
            return;

        $this->_inform('Formatting quotes...');

        $prev = -1;

        $count = &$strings->count;
        $arr   = &$strings->arr;
        $flags = &$strings->flags;

        for ($i = 6; $i < $count; $i++) {
            $line = $arr [$i];

            if (($flags [$i] == STAG) ||
                ($this->_lineempty($line)))
                continue;

            // Left quote
            $line = preg_replace('/([ -(;])"/', "\\1\xab", $line);

            if (strlen($line) && ($line{0} == '"'))
                $line {0} = chr(0xab);


            // Right quote
            $line = preg_replace('/"([ <&.,;:?!\)-])/', "\xbb\\1", $line);

            if (($len             = strlen($line)) && ($line {$len - 1} == '"'))
                $line {$len - 1} = chr(0xbb);

            $arr [$i] = $line;

            if (($cur = round(100 * $i / $count)) != $prev) {
                $this->_progress($cur);
                $prev = $cur;
            }
        }
    }

    public function _removeformsscripts(&$strings)
    {
        $this->_inform('Removing form and script tags...');

        $dellevel = 0;

        $count = &$strings->count;
        $arr   = &$strings->arr;

        $prev = -1;

        $leaves = $strings->mkleaves();

        for ($i = 1; $i < $count; $i++) {
            $leave = true;

            $line = $arr [$i];

            if (preg_match('/^<(script|style)/i', $line))
                $dellevel++;

            if (preg_match('-^</(script|style)>-i', $line)) {
                $leave = false;
                //$dellevel = max ($dellevel - 1, 0);
                $dellevel--;
                $leave = false;
            } else
                $leave = true;

            //$leave &= ($dellevel == 0);
            $leave &= ($dellevel <= 0);

            $leaves [$i] = $leave;

            if (($cur = round(100 * $i / $count)) != $prev) {
                $this->_progress($cur);
                $prev = $cur;
            }
        }

        $strings->leavekeys($leaves);
    }

    public function _ispardelimiter($s)
    {
        $result = ($s == '');

        $result |= preg_match('-^<(/?center|/?div|/?dd|/?dt|/?p|br/?|li)>$-i', $s);
        $result |= preg_match('-^<(/h|/?t|imagex|/?blockquote)-i', $s);

        return $result;
    }

    public function _isparbeginner($s)
    {
        $result = ($s == '');

        $result |= preg_match('-^<(center|div|dd|dt|p|li|br/?)>$-i', $s);
        $result |= preg_match('/^<(t|blockquote)/i', $s);

        return $result;
    }

    public function _findparend(&$strings, &$i)
    {
        $count = &$strings->count;
        $arr   = &$strings->arr;
        $flags = &$strings->flags;

        do {
            if ($i > $count - 3)
                break;

            if ($flags [$i + 1] != STAG)
                $arr [$i + 1] = $arr [$i] . " " . $arr [$i + 1];
            else
            if ($this->_allowedtag($arr [$i + 1]) &&
                (strpos($arr [$i + 1], '<p>') === false))
                $arr [$i + 1] = $arr [$i] . $arr [$i + 1];
            else
                $arr [$i + 1] = $arr [$i];

            $strings->set($i, "\x03", SREADY);
            $i++;
        }
        while (!$this->_ispardelimiter($arr [$i + 1]));

        if (($arr [$i] == '<p>') && !$this->Params['Do_Not_Make_Emptylines'])
            $si = '<empty-line/>';
        else
            $si = $arr [$i] . '</p>';

        $strings->set($i, $si, SREADY);
    }

    public function _detectparagraphs(&$strings)
    {
        $this->_inform('Searching existing HTML formatting...');

        $count = &$strings->count;
        $arr   = &$strings->arr;
        $flags = &$strings->flags;

        $prev = -1;

        $i = 0;
        while ($i < $count - 2) {
            if (($cur = round(100 * $i / $strings->count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }

            if ($flags [$i] != STAG) {
                $i++;
                continue;
            }

            $str = $arr [$i];

            if (strpos($str, '<h') !== false) {
                $arr [$i] = '</section><section><title><p>';
                $this->_findparend($strings, $i);
                $arr [$i] .= '</title>';

                while (($i < $count - 2) &&
                ($this->_lineempty($arr [$i + 1]) ||
                (($flags [$i + 1] == STAG) &&
                !($this->_allowedtag($arr [$i + 1]) ||
                (strpos($arr [$i + 1], '<h') !== false))))) {
                    $strings->set($i + 1, $arr [$i], SREADY);
                    $strings->set($i, "\x03", SREADY);

                    $i++;
                }

                $arr [$i - 1] = $arr [$i];
                $arr [$i]     = "\x03";

                continue;
            }

            if ($this->_isparbeginner($arr [$i])) {
                if (strpos($arr [$i + 1], '<h') === false) {
                    $arr [$i] = '<p>';

                    $this->_findparend($strings, $i);
                } else
                    $arr [$i] = "\x03";

                $i++;
                continue;
            }

            if (!$this->_allowedtag($arr [$i]))
                $strings->set($i, "\x03", SREADY);
            else
                $i++;
        }
    }

    public function _isroman($s)
    {
        if (($pos = strpos($s, '>')) !== false) {
            $s   = substr($s, $pos + 1);
            if (($pos = strpos($s, '<')) !== false)
                $s   = substr($s, 0, $pos);
        }

        $s = trim($s);

        if (strlen($s) > 8)
            return false;

        return
            preg_match('-^m?m?m?(c[md]|d?c{0,3})(x[lc]|l?x{0,3})(i[xv]|v?i{0,3})$-i', $s);
    }

    public function _detectheaders(&$strings)
    {
        if ($this->Params['Do_Not_Autodetect_Headers'] &&
            ($this->Params['Header_Must_Match_Regexp'] == ''))
            return;

        $this->_inform('Searching for implicit headers...');

        $count = &$strings->count;
        $arr   = &$strings->arr;
        $flags = &$strings->flags;

        $prev = -1;

        $leaves = $strings->mkleaves();

        $i  = 0;
        $im = -1;
        $i0 = 0;
        $i1 = 1;

        while ($i1 < $count - 4) {
            if ($this->_lineempty($arr [$i0]) &&
                $this->_lineempty($arr [$i1]) &&
                !$this->_lineempty($arr [$i1 + 1]) &&
                $this->_lineempty($arr [$i1 + 2]) &&
                ($flags [$i1 + 1] === SUNFORMAT) &&
                !$this->Params['Do_Not_Autodetect_Headers']) {
                $strings->set($i0, "</section>\n<section><title><p>" .
                    $arr [$i1 + 1] . "</p></title>\n", SREADY);

                $leaves [$i1]     = false;
                $leaves [$i1 + 1] = false;

                $i  += 2;
                $i0 = $i1 + 1;
                $i1 = $i0 + 1;

                while (($i > 0) &&
                ($i1 < $count) && (($im < 0) || $this->_lineempty($arr [$im]))) {
                    if ($im >= 0)
                        $leaves [$im] = false;

                    $i--;
                    for ($im--; $im > 0 && !$leaves [$im]; $im--)
                        ;
                }

                while (($i1 < $count - 1) &&
                $this->_lineempty($arr [$i1]) &&
                $this->_lineempty($arr [$i1 + 1])) {
                    $leaves [$i1] = false;

                    $i++;
                    $i1++;
                }
            } else {
                $lineemptyi = $this->_lineempty($arr [$i0]);

                if ($i1 < $count - 2) {
                    $lineempty1 = $this->_lineempty($arr [$i1]);
                    $lineempty2 = $this->_lineempty($arr [$i1 + 1]);
                    $lineempty3 = $this->_lineempty($arr [$i1 + 2]);
                } else {
                    $lineempty1 = false;
                    $lineempty2 = false;
                    $lineempty3 = false;
                }

                $thisisit = false;

                if (strpos($arr [$i1], '<section>') === false) {
                    $thisisit = !$this->Params ['Do_Not_Autodetect_Headers'] &&
                        ($flags [$i1] != SREADY) &&
                        (
                        $lineemptyi &&
                        !$lineempty1 &&
                        $lineempty2 &&
                        (
                        $lineempty3 ||
                        (strpos($arr [$i1], '        ') !== false) ||
                        (
                        !$this->_isdialchar($this->_firstchar($arr [$i1])) &&
                        !$this->Params ['Text_Structure_Emptylines'] &&
                        !in_array($arr [$i1] {strlen($arr [$i1]) - 1}, $this->_EndZnak)
                        ) ||
                        (
                        $flags [$i1] === SUNFORMAT &&
                        in_array($this->_firstchar($arr [$i1]), $this->_HeadDetectChars)
                        ) ||
                        $this->_isroman($arr [$i1])
                        )
                        );

                    $thisisit = $thisisit ||
                        (!$lineempty1 &&
                        ($this->Params ['Header_Must_Match_Regexp'] != '') &&
                        preg_match('/' . $this->Params ['Header_Must_Match_Regexp'] . '/', $arr [$i1]));
                }

                if ($thisisit) {
                    $arr [$i1]   = "</section>\n<section><title><p>{$arr [$i1]}</p></title>\n";
                    $flags [$i1] = SREADY;

                    $leaves [$i0] = false;

                    $i0 = $i1;
                    $i1 = $i0 + 1;

                    while ($this->_lineempty($arr [$i1]) &&
                    ($i1 < $count - 1)) {
                        $leaves [$i1] = false;

                        $i1++;
                    }
                }
            }

            $i++;
            $im = $i0;
            $i0 = $i1;
            $i1 = $i0 + 1;

            if (($cur = round(100 * $i1 / $count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }
        }

        $strings->leavekeys($leaves);
    }

    public function _firstsingdial($s)
    {
        if (!strlen($s))
            return false;

        $s = preg_replace('-^ +-', '', $s);

        return (strlen($s) > 0) &&
            ($this->_isdialchar($s{0}) ||
            preg_match("/\d[)-\x96\x97 .;]/", $s));
    }

    public function _detectepigraph(&$strings)
    {
        if ($this->Params ['Do_Not_Search_Epigraphs'])
            return;

        $this->_inform('Detecting epigraphs...');

        $count = &$strings->count;
        $arr   = &$strings->arr;
        $flags = &$strings->flags;

        $leaves = $strings->mkleaves();

        $prev = -1;

        $i = 2;

        while ($i < $count - 20) {
            if (($cur = round(100 * $i / $count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }

            if (strpos($arr [$i], '<section>') !== false) {
                $firsti = $i;

                do
                    $i++;
                while (($i >= $count - 2) && ($flags [$i] != SUNFORMAT));

                if (($i > $firsti + 16) || ($flags [$i] == STAG))
                    continue;


                if (($i == $firsti + 1) && !$this->_lineempty($arr [$i]) &&
                    (strpos($arr [$i], '         ') === false))
                    continue;

                do {
                    $onceagain = false;

                    while (($i < $count - 2) &&
                    $this->_lineempty($arr [$i]))
                        $i++;

                    $ilast = max($i + 60, $count - 2);

                    $endfound = 0;

                    for ($i1 = $i; $i1 <= $ilast; $i1++)
                        if ($this->_lineempty($arr [$i1]) ||
                            (strlen($arr [$i1]) > 80) ||
                            (strlen($arr [$i1]) < 5) ||
                            ($flags [$i1] == STAG)) {
                            $endfound = $i1;
                            break;
                        } else
                        if (strpos($arr [$i1], '<section>') !== false) {
                            $endfound = $i1 - 2;
                            break;
                        }

                    if (($endfound > 0) && ($endfound != $i)) {
                        // If empty line was found relatively near,
                        // then check if text is like to eligraph
                        $coollinesfound = 0;

                        for ($i1 = $i; $i1 <= $endfound; $i1++) {
                            $str = $arr [$i1];

                            if ((strpos($str, '          ') !== false) ||
                                (strlen(trim($str)) < 60) && !$this->_lineempty($str))
                                $coollinesfound++;

                            if (strlen(trim($str)) > 60)
                                $coollinesfound -= 5;

                            if ($this->_firstsingdial($str))
                                $coollinesfound--;
                        }

                        // If looks like then wrap lines right
                        if (($endfound == $i) ||
                            ($coollinesfound / ($endfound - $i) > 0.8)) {
                            $si = '<epigraph><p>' . $arr [$i];

                            for ($i; $i < $endfound - 2; $i++) {
                                if ($arr [$i + 1] != chr(3))
                                    $si .= ' ' . $arr [$i + 1];

                                $leaves [$i] = false;
                                $i++;
                            }

                            $si1 = $arr [$i + 1];
                            $ch  = $this->_firstchar($si1);

                            if (!$this->_lineempty($si1) &&
                                $this->_iswinupper($ch) &&
                                ($flags [$i + 1] != STAG))
                                $si .= "</p><text-author>{$arr [$i + 1]}</text-author></epigraph>";
                            else
                                $si .= ' ' . $arr [$i + 1] . '</p></epigraph>';

                            $strings->set($i + 1, $si, SREADY);

                            $leaves [$i] = false;
                            $i++;

                            $i1             = $i - 1;
                            while (!$leaves [$i1] || $this->_lineempty($arr [$i1]))
                                $leaves [$i1--] = false;

                            while (($i < $count - 1) &&
                            !$this->_lineempty($arr [$i]))
                                $i++;


                            if ((strpos($arr [$i + 1], '      ') !== false) ||
                                (strlen($arr [$i + 1]) < 60))
                                $onceagain = true;
                            // go to $onceagain loop
                        }
                    }
                } while ($onceagain);
            }

            $i++;
        }

        $strings->leavekeys($leaves);
    }

    public function _makeparfromstring($s)
    {
        return "<p>$s</p>";
    }

    public function _nextstringlistshort(&$strings, $i)
    {
        $si  = $strings->arr [$i];
        $si1 = $strings->arr [$i + 1];

        return (strlen($si1) &&
            in_array($si1 {strlen($si1) - 1}, $this->_EndZnak) &&
            (strlen($si1) != strlen($si)));
    }

    public function _isdial($string)
    {
        for ($i = 0; $i < strlen($string); $i++)
            if ($this->_isdialchar($string {$i}))
                return false;
            else
            if ($string {$i} != ' ')
                break;

        return false;
    }

    public function _nextlinestartsnew(&$strings, $i)
    {
        if ($i >= $strings->count - 1)
            return true;

        if ($strings->flags [$i + 1] == SREADY)
            return true;

        $si1 = $strings->arr [$i + 1];

        if ($this->_isdial($si1))
            return true;

        $si  = $strings->arr [$i];
        $lsi = strlen($si);

        if ((strpos($si1, '  ') === 0) &&
            !$this->Params ['Ignore_Spaces_At_Line_Start'] &&
            (!$lsi || in_array($si {$lsi - 1}, $this->_EndZnak) ||
            $this->Params ['Do_Not_Restore_Broken_Paragraphs']))
            return true;

        if ($this->_lineempty($si1))
            return true;

        if (strlen($si1) > $this->Params ['Min_Line_Length'])
            return true;

        return false;
    }

    public function _createparagraphs(&$strings)
    {
        $this->_inform("Detecting TXT-like paragraphs");

        $count = &$strings->count;
        $arr   = &$strings->arr;
        $flags = &$strings->flags;

        $prev = -1;

        $newstrs = new PArr ();
        $ncount  = &$newstrs->count;
        $narr    = &$newstrs->arr;
        $nflags  = &$newstrs->flags;

        if ($count > 0)
            $newstrs->set(0, $arr [0], $flags [0]);

        if ($count > 1)
            $newstrs->set(1, $arr [1], $flags [1]);

        // Looks through all the lines
        $ni = 0;
        $i  = 0;
        while ($i < $count - 2) {
            if ($nflags [$ni] == SREADY) {
                $i++;
                $ni++;
                $newstrs->set($ni + 1, $arr [$i + 1], $flags [$i + 1]);
                continue;
            }

            if ($this->_nextlinestartsnew($newstrs, $ni)) {
                $newstrs->set($ni, $this->_makeparfromstring($narr [$ni]), SREADY);

                $i++;
                $ni++;

                $newstrs->set($ni + 1, $arr [$i + 1], $flags [$i + 1]);

                if ($this->_lineempty($narr [$ni])) {
                    $newstrs->set($ni, $narr [$ni + 1], $nflags [$ni + 1]);

                    $i++;
                    if ($i < $count - 1)
                        $newstrs->set($ni + 1, $arr [$i + 1], $flags [$i + 1]);
                    else
                        $newstrs->delete($ni + 1);
                }
            } else {
                $narr [$ni] .= ' ' . $narr [$ni + 1];

                $i++;
                $newstrs->set($ni + 1, $arr [$i + 1], $flags [$i + 1]);
            }

            if (($cur = round(100 * ($i + 1) / $count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }
        }

        $strings->copyfrom($newstrs);

        for ($i = 0; $i < $count; $i++)
            if ($flags [$i] == SUNFORMAT)
                $arr [$i] = $this->_makeparfromstring($arr [$i]);
    }

    public function _strongfix(&$strings)
    {
        $this->_inform('Fixing bold/strong tags');

        $count = &$strings->count;
        $arr   = &$strings->arr;
        $flags = &$strings->flags;

        $prev = -1;

        $instrong = false;

        for ($i = 0; $i < $count; $i++) {
            if (($cur = round(100 * $i / $strings->count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }

            $si = $arr [$i];

            $pos = 0;
            while (($pos = strpos($si, '<', $pos)) !== false) {
                $del = 0;

                if (!strncasecmp(substr($si, $pos), '<strong>', 8)) {
                    $instrong = true;
                    $del      = 8;
                } else
                if (!strncasecmp(substr($si, $pos), '</strong>', 9)) {
                    $instrong = false;
                    $del      = 9;
                }

                if ($del) {
                    $start = $pos;
                    $si    = substr_replace($si, '', $pos, $del);
                } else
                    $start = strpos($si, '>', $pos) + 1;

                $end = strpos($si, '<', $start);

                if ($end === false)
                    $end = strlen($si);

                $oldstring = substr($si, $start, $end - $start);

                if ($instrong)
                    $newstring = '<strong>' . $oldstring . '</strong>';
                else
                    $newstring = $oldstring;

                $si = substr_replace($si, $newstring, $start, $end - $start);

                $pos++;
            }

            $arr [$i] = $si;
        }
    }

    public function _italicize($str, $inemph)
    {
        if ($inemph)
            return '<emphasis>' . $str . '</emphasis>';

        $matches = array();

        while (preg_match('/(.*<)([^>]*)(>.*)/', $str, $matches))
            $str = $matches [1] .
                str_replace('_', '%5f', $matches [2]) .
                $matches [3];

        $l = $this->_winlower;
        $u = $this->_winupper;

        while (preg_match("/^(|.*[^\w$l$u])(_([\w$l$u]_)+)(.*)/", $str, $matches)) {
            $str = $matches [1] .
                '<emphasis>' . str_replace('_', '', $matches [2]) . '</emphasis>' .
                $matches [4];
        }

        while (preg_match("/^(|.*[^_\w$u$l])_+([^_]+)_+(|[^_\w$l$u].*)$/", $str, $matches)) {
            $str = $matches [1] .
                '<emphasis>' . $matches [2] . '</emphasis>' .
                $matches [3];
        }

        $str = str_replace('%5f', '_', $str);

        return $str;
    }

    public function _italiccreate(&$strings)
    {
        if ($this->Params ['Do_Not_Detect_Italic_Text'])
            return;

        $this->_inform('Searching for _italic_ text');

        $count = &$strings->count;
        $arr   = &$strings->arr;
        $flags = &$strings->flags;

        $prev = -1;

        $inemph = false;

        for ($i = 0; $i < $count; $i++) {
            if (($cur = round(100 * $i / $strings->count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }

            $si = $arr [$i];

            $pos = 0;
            while (($pos = strpos($si, '<', $pos)) !== false) {
                $del = 0;

                if (!strncasecmp(substr($si, $pos), '<emphasis>', 10)) {
                    $inemph = true;
                    $del    = 10;
                } else
                if (!strncasecmp(substr($si, $pos), '</emphasis>', 11)) {
                    $inemph = false;
                    $del    = 11;
                }

                if ($del) {
                    $start = $pos;
                    $si    = substr_replace($si, '', $pos, $del);
                } else
                    $start = strpos($si, '>', $pos) + 1;

                $end = strpos($si, '<', $start);

                if ($end === false)
                    $end = strlen($si);

                $oldstring = substr($si, $start, $end - $start);

                $newstring = $this->_italicize($oldstring, $inemph);

                $si = substr_replace($si, $newstring, $start, $end - $start);

                $pos++;
            }

            $arr [$i] = $si;
        }
    }

    public function _clears($sub, $s, $i)
    {
        while (($pos = strpos($s, $sub)) !== false)
            $s   = substr_replace($s, '', $pos + $i, 1);

        return $s;
    }

    public function _regescape($s)
    {
        return preg_quote($s);
    }

    public function _findsubstr($str, $pattern)
    {
        if (($pos = strpos($str, $pattern)) !== false)
            return substr($str, $pos);
        else
            return false;
    }

    public function _checkintlinks(&$s)
    {
        if (!$s->count) {
            $this->_warn('RemoveAllDSpace: empty text! This may be an error');
            return;
        }

        $this->_inform('Verifying internal links...');

        $prev = -1;

        $count = &$s->count;
        $arr   = &$s->arr;
        $flags = &$s->flags;

        $idscollected = array();

        if ($arr [0] == '<empty-line/>')
            $arr [0] = '';

        for ($i = 0; $i < $count; $i++) {
            $buf = $arr [$i];

            if (($buf == '') || ($buf == "\x03"))
                continue;

            $buf = preg_replace('/^(<p>) +/', '\1', $buf);

            if (($buf == '<p></p>') && !$this->Params['Do_Not_Make_Emptylines'])
                $buf = '<empty-line/>';

            if ($i &&
                ($buf == '<empty-line/>') &&
                ((strpos($arr [$i - 1], '<section>') !== false) ||
                ($arr [$i - 1] == '<empty-line/>') ||
                (($i < $count - 2) &&
                (strpos($arr [$i + 1], '</section>') !== false)))) {
                $buf = '';
                continue;
            }

            if ((strpos($buf, '<p id="') !== false) &&
                preg_match('-<p id="([^"]*)"></p>-i', $buf) &&
                ($i < $count - 2)) {
                $buf = preg_replace('-<p id="([^"]*)"></p>-', '"\1"', $buf);

                for ($i1 = $i + 1; $i1 < $count - 1; $i1++) {
                    $ppos = strpos($arr [$i + 1], '<p>');

                    if (($ppos !== false) &&
                        (strpos($arr [$i + 1], '<p></p>') === false)) {
                        $arr [$i1] = substr_replace($arr [$i + 1], " id=$buf", $ppos + 2, 0);

                        break;
                    }
                }

                $buf = '<empty-line/>';
            }

            if (strpos($buf, '<id id=') !== false) {
                $buf = preg_replace('-<p>(.*?)<id id="([^"]*?)"/>-', '<p id="\2">\1', $buf);

                while (preg_match('/<p id="([^"]*)[^\w-"]/i', $buf))
                    $buf = preg_replace('/<p id="([^"]*)[^\w-"]/', '<p id="\1_Q_', $buf);

                $buf = preg_replace('-<id id="[^"]*?"/>-', '', $buf);

                $buf = preg_replace('/<p id="(\d)/', '<p id="fb_\1', $buf);

                $hrfound = substr($buf, 7, strpos($buf, '">') - 7);

                if (preg_match('-<p id="[^"]*?">\s*</p>-i', $buf)) {
                    for ($i1 = $i + 1; $i1 < $count - 1; $i1++) {
                        $ppos = strpos($arr [$i1], '<p>');

                        if (($ppos !== false) && (strpos($arr [$i1], '<p></p>') === false)) {
                            $arr [$i] = substr_replace($arr [$i1], " id=\"$hrfound\"", $ppos + 2, 0);
                            break;
                        }
                    }

                    $buf = '<empty-line/>';
                }

                if (strpos($buf, '<p id="') === 0)
                    if (!in_array($hrfound, $idscollected))
                        $idscollected [] = $hrfound;
                    else
                        $buf             = preg_replace('/<p id="[^"]*">/', '<p>', $buf);
            } else
            if (strpos($buf, '<p><imagex xlink:href="') === 0)
                $buf = substr($buf, 3, strlen($buf) - 7);

            if (strpos($buf, '<a xlink:href="#') !== false) {
                while (preg_match('/<a xlink:href="#(\w*?)([^\w-"])/i', $buf))
                    $buf = preg_replace('/<a xlink:href="#([\w]*?)([^\w-"])/', '<a xlink:href="#\1_Q_', $buf);

                $buf = preg_replace('/<a xlink:href="#(\d)/', '<a xlink:href="#fb_\1', $buf);
            }

            $arr [$i] = $buf;

            if (($cur = round(40 * $i / $count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }
        }

        for ($i = 1; $i < $count; $i++) {
            if (($cur = round(40 + 60 * $i / $count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }

            $buf = $arr [$i];

            if (($buf == '') || ($buf == "\x03"))
                continue;

            $phref = $this->_findsubstr($buf, '<a xlink:href="#');

            while ($phref !== false) {
                $hrfound = $this->_regescape(substr($phref, 16, strpos($phref, '">') - 16));

                if (!in_array($hrfound, $idscollected) &&
                    (substr($hrfound, 0, 20) != 'FbAutId_')) {
                    if (preg_match("\x01<a xlink:href=\"#$hrfound\">(.*?)</x:a>\x01i", $buf))
                        $buf = preg_replace("\x01<a xlink:href=\"#$hrfound\">(.*?)</x:a>\x01", '\1', $buf);
                    else
                        $buf = preg_replace("\x01<a xlink:href=\"#$hrfound\">\x01i", '', $buf);

                    $phref = $this->_findsubstr($buf, '<a xlink:href="#');
                } else
                    $phref = $this->_findsubstr($this->_findsubstr($phref, '">'), '<a xlink:href="#');
            }
        }
    }

    public function _removealldspaces(&$s)
    {
        $newlines = new PArr ();
        $ncount   = &$newlines->count;

        $newprev = '';

        $this->_inform('Removing doublespaces...');

        $prev = -1;

        $count = &$s->count;
        $arr   = &$s->arr;
        $flags = &$s->flags;

        $str1 = '';
        $str2 = '';

        for ($i = 1; $i < 32; $i++) {
            if ($i == 0x0d || $i == 0x0a)
                continue;

            $str1 .= chr($i);
            $str2 .= ' ';
        }

        for ($i = 1; $i < $count; $i++)
            $arr [$i] = strtr($arr [$i], $str1, $str2);


        $obraschenij = convert_cyr_string('</section><section><title><p>' .
            'пВТБЭЕОЙК У ОБЮБМБ НЕУСГБ:', 'k', 'w');

        $reps = array('  '           => 0, '> <'          => 1,
            '> , <'        => 1, '> . <'        => 1, '> ! <'        => 1, '> ? <'        => 1,
            '< '           => 1, ' >'           => 0, ' </p>'        => 0, '</x:a>'       => 2,
            '</:a>'        => 2, ' <p'          => 0,
            ' </strong>'   => 0, ' </emphasis>' => 0,
            '<p> '         => 3, "<p>\xa0"      => 3,
            '<strong> '    => 8, '<emphasis> '  => 10,
            '--'           => 0);

        $i = 0;

        while ($i < $count) {
            $si  = $arr [$i];
            $str = $si;

            if (strpos($str, $obraschenij) === 0) {
                $i++;
                continue;
            }

            foreach ($reps as $rep => $pos)
                $str = $this->_clears($rep, $str, $pos);

            if ($str == '<p></p>')
                if (!$this->Params ['Do_Not_Make_Emptylines'])
                    $str = '<empty-line/>';
                else
                    $str = chr(3);

            if ($str != $si) {
                $arr [$i] = $str;
                continue;
            }

            if (!(($str == '') ||
                ($str == chr(3)) ||
                ($ncount &&
                ($str == '<empty-line/>') &&
                ((strpos($newprev, '<section>') !== false) ||
                ($newprev == '<empty-line/>') ||
                (($i < $count - 2) &&
                (strpos($arr [$i + 1], '</section>') !== false)
                )
                )
                )
                )
            ) {
                $newlines->add($str);
                $newprev = $str;
            }

            $i++;

            if (($cur = round(100 * $i / $count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }
        }

        $s->copyfrom($newlines);
    }

    public function _killentity(&$strings)
    {
        $this->_inform('Checking named entities...');

        $strings->arr = $this->_delent($strings->arr);
    }

    public function _formatt(&$strings)
    {
        if ($this->Params ['Do_Not_Make_First_Dash_Long'])
            return;

        $this->_inform('Fixing dialogs...');

        $lc = $this->LineChar;

        $regexp1 = "/((>|&gt;)" . preg_quote($lc) . ") /";
        $regexp2 = "\\1" . chr(160);

        $count = &$strings->count;
        $arr   = &$strings->arr;

        $prev = -1;

        for ($i = 0; $i < $count; $i++) {
            $str = $arr [$i];

            $str = str_replace('- ', "$lc ", $str);
            $str = str_replace(' -', " $lc", $str);

            $str = preg_replace($regexp1, $regexp2, $str);

            $arr [$i] = $str;

            if (($cur = round(100 * $i / $count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }
        }
    }

    public function _formatp(&$strings)
    {
        $rep1 = '...';
        $rep2 = chr(133);

        $count = &$strings->count;
        $arr   = &$strings->arr;

        for ($i = 0; $i < $strings->count; $i++)
            $arr [$i] = str_replace($rep1, $rep2, $arr [$i]);
    }

    public function _onelevel($string)
    {
        $arr = array();

        $pos = -1;

        $count = 0;

        while (($pos = strpos($string, '<', $pos + 1)) !== false) {
            if (($epos = strpos($string, '>', $pos + 1)) === false)
                return false;

            $tag = substr($string, $pos, $epos - $pos);

            if ($tag {0} != '/')
                $arr [$count++] = $tag;
            else
            if ($count && ($arr [$count - 1] == $tag))
                $count--;
            else
                $arr [$count++] = $tag;

            $pos = $epos;
        }

        return (count($arr) == 0);
    }

    public function _checkblock($c1, $c2, $i, &$id, &$strings, &$notesbody)
    {
        $si = $strings->arr [$i];

        $pos = -1;

        while (($pos = strpos($si, $c1, $pos + 1)) !== false) {
            if (($epos = strpos($si, $c2, $pos + 1)) === false)
                break;

            $text = substr($si, $pos + strlen($c1), $epos - $pos - strlen($c2) - strlen($c1) + 1);

            if ($this->_onelevel($text)) {
                $newtext = $this->_cleartag($text);

                $notesbody->add("<section id=\"FbAutId_$id\">" .
                    "<title><p>Note $id</p></title>");
                $notesbody->add("<p>" . $newtext . "</p></section>");

                $si = substr_replace($si, "<a xlink:href=\"#FbAutId_$id\" type=\"note\">note $id</a>", $pos, strlen($text) + strlen($c1) + strlen($c2));
                $id++;
            }
        }

        $strings->arr [$i] = $si;
    }

    public function _createfootnotes(&$strings, &$notesbody)
    {
        if ($this->Params ['Do_Not_Detect_Footnotes'])
            return;

        $this->_inform('Detecting notes...');

        $prev = -1;

        $id = 1;

        for ($i = 0; $i < $strings->count - 2; $i++) {
            $this->_checkblock('[', ']', $i, $id, $strings, $notesbody);
            $this->_checkblock('{', '}', $i, $id, $strings, $notesbody);

            if (($cur = round(100 * $i / $strings->count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }
        }
    }

    public function _enclosetag(&$buf, $tag)
    {
        $otag = "<$tag";
        $ctag = "</$tag";

        $tpos = array();

        $pos         = -1;
        while (($pos         = strpos($buf, $otag, $pos + 1)) !== false)
            $tpos [$pos] = 1;

        $pos         = -1;
        while (($pos         = strpos($buf, $ctag, $pos + 1)) !== false)
            $tpos [$pos] = -1;

        ksort($tpos);

        $dels = array();

        $level = 0;

        foreach ($tpos as $pos => $open) {
            $level += $open;

            if ($level < 0) {
                $level++;
                $dels [] = $pos;
                unset($tpos [$pos]);
            }
        }

        foreach ($tpos as $pos => $open) {
            if ($open > 0)
                $dels [] = $pos;
            else
                array_pop($dels);
        }

        foreach ($dels as $pos)
            $buf = substr_replace($buf, '<dd', $pos, 3);
    }

    public function _removenotclosed(&$strings)
    {
        $this->_inform('Removing incorrect tags...');

        $count = &$strings->count;
        $arr   = &$strings->arr;

        $prev = -1;

        for ($i = 0; $i < $strings->count; $i++) {
            $si = $arr [$i];

            $this->_enclosetag($si, 'emphasis');
            $this->_enclosetag($si, 'strong');
            $this->_enclosetag($si, 'a');

            $si = preg_replace('/<dd[^>]*>/', '', $si);

            $arr [$i] = $si;

            if (($cur = round(100 * $i / $count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }
        }
    }

    public function _detectverses(&$strings)
    {
        if ($this->Params ['Do_Not_Detect_Poems'])
            return;

        $this->_inform('Detecting verses');

        $count = &$strings->count;
        $arr   = &$strings->arr;

        $news = new PArr;

        $prev = -1;

        $i = 0;

        while ($i < $count - 5) {
            if (($cur = round(100 * $i / $count)) != $prev) {
                $prev = $cur;
                $this->_progress($cur);
            }

            $si  = $arr [$i];
            $si1 = $arr [$i + 1];
            $si2 = $arr [$i + 2];
            $si3 = $arr [$i + 3];

            $clean = $this->_cleantags($si);
            $fl    = strlen($clean);

            if ((strlen($si) < 80) &&
                (strlen($si1) < 80) &&
                (strlen($si2) < 80) &&
                (strlen($si3) < 80) &&
                (strpos($si, '<section>') === false) &&
                ($fl < 60) &&
                ($si != '<empty-line/>') &&
                ($si1 != '<empty-line/>') &&
                ($si2 != '<empty-line/>') &&
                ($si3 != '<empty-line/>')) {
                for ($i1 = $i; $i1 < $count - 1; $i1++) {
                    $si1   = $arr [$i1];
                    $si1fl = strlen($this->_cleantags($si1));

                    $qp = strpos($si1, '>');

                    if (((abs($si1fl - $fl) > 15) && ($si1 != '<empty-line>')) ||
                        (strpos($si1, '<section>') !== false) ||
                        (($qp !== false) && ($qp < strlen($si1) - 1) &&
                        $this->_isdialchar($si1 {$qp + 1})))
                        break;
                }

                if ($i1 - $i > 3) {
                    $news->add('<poem><stanza>');

                    while ($i < $i1) {
                        $si = $arr [$i];

                        if ($si == '<empty-line/>')
                            if ($i != $i1 - 1)
                                $si = '</stanza><stanza>';
                            else
                                $si = '';
                        else {
                            $si = str_replace('<p', '<v', $si);
                            $si = str_replace('</p>', '</v>', $si);
                        }

                        $news->add($si);
                        $i++;
                    }

                    $news->add('</stanza></poem>');
                }
            }

            $news->add($arr [$i]);

            $i++;
        }

        while ($i < $count)
            $news->add($arr [$i++]);

        $strings->copyfrom($news);
    }

    public function _detectnesting(&$strings)
    {
        $curdeep = 0;
        $newdeep = 0;

        if (!$strings->count)
            return;

        $this->_inform('Detecting section nesting...');

        $count = &$strings->count;
        $arr   = &$strings->arr;

        $prev = -1;

        for ($i = 0; ($i != $count - 1) && ($arr [$i] == '<empty-line/>'); $i++)
            $arr [$i] = '';

        $i1  = -1;
        if (($count - 1 > 100) &&
            (!$this->Params ['Do_Not_Search_Description']))
            for ($i = 0; $i < $count / 3; $i++)
                if (($pos = strpos($arr [$i], '<section>')) !== false) {
                    $arr [$i] = substr($arr [$i], $pos + 9);
                    $i1       = $i;
                    break;
                }

        if (($i1 >= 0) && $this->_description == '') {
            for ($i = 0; $i < $i1; $i++) {
                $this->_description .= $arr [$i];
                $arr [$i]           = '';
            }
        }

        if ($i1 < 0)
            $i1 = 0;

        if ($i1 < $count - 1)
            $arr [$i1] = '<section>' . $arr [$i1];

        for ($i = $i1; $i < $count - 2; $i++) {
            if ((strpos($arr [$i], '<section>') !== false) &&
                (strpos($arr [$i + 1], 'epigraph>') !== false)) {
                $arr [$i + 1] = $arr [$i] . $arr [$i + 1];
                $arr [$i]     = '';
            } else
                $i++;
        }

        for ($i = $i1; $i < $count - 5; $i++) {
            for ($i1 = 1; $i1 <= 5; $i1++) {
                if ((strpos($arr [$i], '<section>') !== false) &&
                    (strpos($arr [$i + $i1], '</section>') === 0)) {
                    if ($curdeep > 0) {
                        $arr [$i] = '</section>' . $arr [$i];
                        $curdeep--;
                    }
                    $arr [$i + $i1] = substr($arr [$i + $i1], 10);
                    $newdeep++;
                } else
                    break;
            }

            $curdeep += $newdeep;
            $newdeep = 0;
        }

        if ($count > 0)
            for ($i = 0; $i <= $curdeep; $i++)
                $arr [$count - 1] .= '</section>';
    }

    public function _postprocess($string)
    {
        $this->_inform('Postprocessing text...');

        $string = preg_replace('/(<p><(a|strong|emphasis)[^>]*>)\s+/', '\1', $string);
        $this->_progress(10);

        $string = preg_replace('/<\/section>[^<]*<section><title><p>([^<]*)<\/p><\/title>[^<]*<\/section>/', '<subtitle>\1</subtitle></section>', $string);
        $this->_progress(20);

        $string = preg_replace('/<(?=[^>]*<)/', '&lt;', $string);
        $this->_progress(30);

        $string = preg_replace('/<(emphasis|strong)>(([^<]*)<\1>)+/', '\3<\1>', $string);
        $this->_progress(40);

        $string = preg_replace("/<empty-line\\/>[\\s\r\n]*<\\/section>/", '</section>', $string);
        $this->_progress(40);

        $string = preg_replace('/(>[^<]*?)>/', '\1&gt;', $string);
        $this->_progress(50);

        $string = preg_replace('/<p id="([^"]*)">/', "<p id=\"{$this->_idprefix}\\1\">", $string);
        $this->_progress(60);

        $string = preg_replace('/<a xlink:href="#([^"]*)">/', "<a xlink:href=\"#{$this->_idprefix}\\1\">", $string);
        $this->_progress(90);

        $string = str_replace(chr(3), '', $string);

        return $string;
    }

    public function _recognizetext(&$inputlines, &$notesbody)
    {
        $debug = false;
        //$debug = true;

        if ($debug)
            echo "Input lines\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "==========================\n";

        $this->_validhtml($inputlines);
        if ($debug)
            echo "\nValidHtml\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_formatq($inputlines);
        if ($debug)
            echo "\nFormatQ\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_removeformsscripts($inputlines);
        if ($debug)
            echo "\nRemoveFormsScripts\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_detectparagraphs($inputlines);
        if ($debug)
            echo "\nDetectParagraphs\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_detectheaders($inputlines);
        if ($debug)
            echo "\nDetectHeaders\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_detectepigraph($inputlines);
        if ($debug)
            echo "\nDetectEpigraph\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_createparagraphs($inputlines);
        if ($debug)
            echo "\nCreateParagraphs\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_strongfix($inputlines);
        if ($debug)
            echo "\nStrongFix\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_italiccreate($inputlines);
        if ($debug)
            echo "\nItalicCreate\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_checkintlinks($inputlines);
        if ($debug)
            echo "\nCheckIntLinks\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_removealldspaces($inputlines);
        if ($debug)
            echo "\nRemoveAllDSpaces\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_killentity($inputlines);
        if ($debug)
            echo "\nKillEntity\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_formatt($inputlines);
        if ($debug)
            echo "\nFormatT\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_formatp($inputlines);
        if ($debug)
            echo "\nFormatP\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_createfootnotes($inputlines, $notesbody);
        if ($debug)
            echo "\nCreateFootnotes\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_removenotclosed($inputlines);
        if ($debug)
            echo "\nRemoveNotClosed\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_checkintlinks($inputlines);
        if ($debug)
            echo "\nCheckIntLinks\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_removealldspaces($inputlines);
        if ($debug)
            echo "\nRemoveAllDSpaces\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_detectverses($inputlines);
        if ($debug)
            echo "\nDetectVerses\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $this->_detectnesting($inputlines);
        if ($debug)
            echo "\nDetectNesting\n--------------------------\n";
        if ($debug)
            echo $inputlines->dump() . "\n==========================\n";

        $inputlines->fromstring($this->_postprocess($inputlines->tostring()));
    }

    public function _checkxml(&$text, &$errorline, &$errordesc)
    {
        if ($this->Params ['Do_Not_Check_XML'])
            return true;

        $error     = XML_ERROR_NONE;
        $errorline = -1;
        $errordesc = '';

        $parser = xml_parser_create();

        if (!xml_parse($parser, $text)) {
            $error     = xml_get_error_code($parser);
            $errorline = xml_get_current_line_number($parser);
            $errordesc = xml_error_string($error);
        }

        xml_parser_free($parser);

        return ($error == XML_ERROR_NONE);
    }

    public function _validatetext(&$lines, $fn)
    {
        $line = '<body ';

        if ($this->_done)
            $line .= 'name="' . $this->_myextractfilename($fn) . '" ';

        $line .= 'xmlns:fb="http://www.gribuser.ru/xml/fictionbook/2.0" ' .
            'xmlns:xlink="http://www.w3.org/1999/xlink">';

        $lines->insert(0, $line);
        $lines->add('</body>');

        $fixtrialisdone = -50;
        $preverror      = 0;
        $prepreverror   = 0;

        $result = false;

        do {
            $checked = $this->_checkxml($lines->tostring(), $errline, $errdescr);

            if (!$checked) {
                $fixtrialisdone++;

                if ($errline < 2)
                    break;

                if ($errline != $preverror) {
                    $this->_warn('XML validation failed (pass ' . ($fixtrialisdone + 50) . ')' .
                        " at line $errline, " .
                        "text: '" . $this->_cleantags($lines->arr [$errline - 1]) . "', " .
                        "error: '$errdescr'");
                    $this->_warn('Trying to fix...');

                    $line = '<p><style name="converterror">' .
                        $this->_cleantags($lines->arr [$errline - 2]) .
                        '</style></p>';

                    if (strpos($lines->arr [$errline - 1], '<section><title>') !== false)
                        $line = "<section><title>$line</title>";

                    $lines->arr [$errline - 1] = $line;
                } else
                if ($prepreverror != $preverror) {
                    $prev                      = ($errline > 2 ? $lines->arr [$errline - 3] : '');
                    $lines->arr [$errline - 2] = '<p><style name="converterror">' .
                        $this->_cleantags($prev) .
                        '</style></p>';
                    $lines->arr [$errline - 1] = '<p><style name="converterror">' .
                        $this->_cleantags($lines->arr [$errline - 2]) .
                        '</style></p>';
                    $lines->arr [$errline]     = '<p><style name="converterror">' .
                        $this->_cleantags($lines->arr [$errline - 1]) .
                        '</style></p>';

                    $this->_warn("Validation failed twice at line $errline.");
                    $this->_warn('Trying to hard fix three line and retry...');

                    $prepreverror = $errline;
                } else {
                    $this->_warn("Validation failed three times at line $errline.");
                    $this->_warn('Trying to remove damaged lines and retry.');
                    $this->_warn('Removed text is: "' . $lines->arr [$errline - 1] . '"');

                    $lines->arr [$errline - 1] = '<p><style name="converterror">' .
                        'In this place was an unrecoverable import error. ' .
                        'Source text was completly removed, so you may need to re-enter it here manually' .
                        '</style></p>';
                    $prepreverror              = 0;
                }

                $preverror = $errline;
            }
        } while ((!$checked) &&
        ($fixtrialisdone < $this->Params ['Tries_To_Fix_XML']));

        if ($checked) {
            $this->_inform('XML validation passed');
            $result = true;
        } else {
            $this->_warn('Unable to fix invalid XML generated.');
            $this->_warn('You can disable Do_Not_Check_XML to find out what happened');
            $this->_warn('Aborting convertion...');

            $result = false;
        }

        if ($result && count($this->Params ['Regexps_At_Finish'])) {
            $this->_inform('Running user regular expressions on ready document...');

            $text = $lines->tostring();

            $regexps = $this->Params ['Regexps_At_Finish'];

            if (count($regexps) % 2)
                $regexps [] = '';

            for ($i = 0; $i < count($regexps); $i += 2) {
                $reg1 = $regexps [$i];
                $reg2 = $regexps [$i + 1];

                $this->_inform("Regexp: /$reg1/$reg2/");

                $newtext = preg_replace("/$reg1/", $reg2, $text);
                if (!is_null($newtext))
                    $text    = $newtext;
                else
                    $this->_warn("Regexp error");
            }

            if ($this->_checkxml($text, $errline, $errdescr))
                $lines->fromstring($text);
            else {
                $this->_warn('XML is invalid after user regular expressions');
                $this->_warn('Leaving it without application');

                // TODO: Dump to file
            }
        }

        return $result;
    }

    public function _markstart(&$lines)
    {
        $count = &$lines->count;
        $arr   = &$lines->arr;

        for ($i = 0; $i < $count; $i++)
            if (strpos($arr [$i], '<p>') === 0) {
                $arr [$i] = substr_replace($arr [$i], '<p id="' . $this->_idprefix . 'DocRoot">', 0, 3);
                break;
            }
    }

    public function _findbasehref($s, &$hrbase, &$rootpath, &$levelseparator)
    {
        $s = preg_replace(",(^|[^.])\./,", '\1', $s);

        $hrbase = $s;

        $levelseparator = '/';

        if (strpos(strtolower($s), 'http://') === 0) {
            $s = substr($s, 0, strrpos($s, '/') + 1);

            if (strcasecmp($s, 'http://')) {
                $hrbase   = $s;
                $rootpath = substr($s, 0, strpos($s, '/', 7));
            } else
                $rootpath = $hrbase;

            if (substr($rootpath, -1) == '/')
                $rootpath = substr($rootpath, 0, -1);

            if (substr($hrbase, -1) == '/')
                $hrbase = substr($hrbase, 0, -1);
        } else {
            $levelseparator = $this->_maindirsep;

            // TODO: UNIX
            if (strpos($hrbase, $levelseparator) !== false)
                $hrbase = substr($hrbase, 0, strrpos($hrbase, $levelseparator) + 1);
            else
                $hrbase = getcwd();

            if ($this->_windows)
                $rootpath = substr($hrbase, 0, 3);
            else
                $rootpath = '/';
        }
    }

    public function _splithref(&$hr, &$id)
    {
        $lhr = strtolower($hr);

        if ((strpos($lhr, 'mailto:') === 0) || (strpos($lhr, 'news:') === 0)) {
            $hr = '';
            $id = '';
            return;
        }

        if ($hr {0} == '#') {
            $id = 0;
            return;
        }

        $arr = explode('#', $hr);

        $hr = $arr [0];
        $id = (isset($arr [1]) ? $arr [1] : '');
    }

    public function _expandhref($href, $rootpath, $hrbase, $levelseparator)
    {
        $href = preg_replace(",(^|[^.])\./,", '\1', $href);

        $result = trim($href);

        if ($result == '')
            return '';

        $lhref = strtolower($href);

        foreach (array('mailto', 'news', 'ftp') as $prefix)
            if (strpos($lhref, $prefix . ':') === 0)
                return $href;

        if ((strpos($lhref, 'http://') === 0) || (strpos($href, ':') == 2))
            return $result;

        if ($result{0} == '/')
            return $rootpath . $result;

        $newhref = $hrbase;

        while (strpos($result, '../') === 0) {
            $newhref = substr($newhref, 0, strlen($newhref) -
                strlen(strrchr(substr($newhref, 0, -1), $levelseparator)));
            $result  = substr($result, 3);
        }

        substr_replace('./', '', $result);

        if ($newhref {strlen($newhref) - 1} != $levelseparator)
            $result = $newhref . $levelseparator . $result;
        else
            $result = $newhref . $result;

        if ($levelseparator == "\\")
            $result = str_replace('/', "\\", $result);

        return ($result);
    }

    public function _collecthrefs(&$lines, &$usedfiles, $rootpath, $hrbase, $levelseparator, $maydeeper)
    {
        if ($this->Params ['Remove_External_Links'])
            return;

        $foundid = '';

        $prev = -1;

        $this->_inform('Collecting external links...');

        for ($i = 0; $i < $lines->count; $i++)
            if (strpos($lines->arr [$i], '<a xl') !== false) {
                if (($cur = round(100 * $i / $lines->count)) != $prev) {
                    $prev = $cur;
                    $this->_progress($cur);
                }

                $buf = $lines->arr [$i];

                while (($pos = strpos($buf, '<a xlink:href="')) !== false) {
                    $buf = substr($buf, $pos + 15);

                    $foundhr = substr($buf, 0, strpos($buf, '"'));

                    if ($buf [0] == '#')
                        continue;

                    $this->_splithref($foundhr, $foundid);

                    $substr = $this->_expandhref($foundhr, $rootpath, $hrbase, $levelseparator);

                    if (($foundhr != '') &&
                        ($this->Params ['Download_External_Files'] ||
                        (strpos(strtolower($substr), $rootpath) === 0)) &&
                        (($this->Params ['Follow_Links_Matching'] == '') ||
                        preg_match('/' . $this->Params ['Follow_Links_Matching'] . '/', $substr)) &&
                        (($this->Params ['Do_Not_Follow_Links_Matching'] == '') ||
                        !preg_match('/' . $this->Params ['Do_Not_Follow_Links_Matching'] . '/', $substr))) {
                        if ($maydeeper || ($usedfiles->indexof($substr) !== false)) {
                            $re = '<a xlink:href="' . $foundhr;
                            if ($foundid != '')
                                $re .= '#' . $foundid;
                            $re .= '"';

                            $foundhr = $this->_expandhref($foundhr, $rootpath, $hrbase, $levelseparator);

                            $i1 = $usedfiles->indexof($foundhr);
                            if ($i1 === false)
                                $i1 = $usedfiles->add($foundhr);

                            $rep = '<a xlink:href="#AutBody_' . $i1;
                            if ($foundid != '') {
                                if (is_numeric($foundid {0}))
                                    $foundid = 'fb_' . $foundid;

                                str_replace('"', '_', $foundid);

                                $rep .= $foundid;
                            } else
                                $rep .= 'DocRoot';

                            $rep .= '"';

                            $lines->arr [$i] = str_replace($re, $rep, $lines->arr [$i]);
                        }
                    }
                }
            }
    }

    public function _isdynamic($url)
    {
        return preg_match('/(\?|\.php|\.cgi|\.pl|\.asp|\.jsp|banner|adv)/i', $url);
    }

    public function _loadimages(&$lines, &$binaries, $rootpath, $hrbase, $levelseparator)
    {
        $count = &$lines->count;
        $arr   = &$lines->arr;

        for ($i = 0; $i < $lines->count; $i++) {
            $imgpos = strpos($lines->arr [$i], '<imagex');

            while ($imgpos !== false) {
                $buf1 = substr($lines->arr [$i], 0, $imgpos);
                $tag  = substr($lines->arr [$i], $imgpos);
                $tag  = substr($tag, 0, strpos($tag, '>') + 1);
                $buf2 = substr($lines->arr [$i], strlen($buf1) + strlen($tag));
                $url  = substr($tag, strpos($tag, '"') + 1);
                $url  = substr($url, 0, strpos($url, '"'));
                $url  = $this->_expandhref($url, $rootpath, $hrbase, $levelseparator);

                if (((strpos(strtolower($url), $rootpath) === 0) ||
                    !$this->Params ['Remove_External_Links']) &&
                    (!$this->_isdynamic($url) || $this->Params ['Keep_Dynamic_Images'])) {
                    if ($binaries->indexof($url) == false)
                        $binaries->add($url);

                    $tag = '<image xlink:href="#Any2FbImgLoader' .
                        $binaries->indexof($url) . '"/>';
                } else
                    $tag = '';

                if (($buf1 == '<p>') && ($buf2 == '</p>'))
                    $lines->arr [$i] = $tag;
                else
                    $lines->arr [$i] = $buf1 . $tag . $buf2;

                $imgpos = strpos($lines->arr [$i], '<imagex');
            }
        }
    }

    public function _buildonebody($text, &$footnotes, &$usedfiles, &$binaries, $Params, $loadfromstring = false)
    {
        $this->Params = $Params;

        $hrbase         = '';
        $rootpath       = '';
        $levelseparator = '';

        $this->_description = '';
        $linesinwork        = new PArr();

        if (!$loadfromstring)
            $text = preg_replace(',(^|[^.])\./,', '\1', $text);

        if ($usedfiles->indexof($text) === false)
            $usedfiles->add($text, 1);

        $tarr = $this->_getthetext($text, $loadfromstring);

        if ($tarr === false) {
            $usedfiles->flags [$usedfiles->indexof($text)] = STAG;
            return false;
        }

        $linesinwork->fromarr($tarr);

        unset($tarr);

        $this->_idprefix = 'AutBody_' . $this->_usedfiles->indexof($text);

        $this->_recognizetext($linesinwork, $footnotes);

        if (!$this->_validatetext($linesinwork, $text))
            return false;

        $this->_markstart($linesinwork);

        $usedfiles->flags [$usedfiles->indexof($text)] = SREADY;

        $this->_findbasehref($text, $hrbase, $rootpath, $levelseparator);

        if (!$this->Params ['Remove_All_Images'])
            $this->_loadimages($linesinwork, $binaries, $rootpath, $hrbase, $levelseparator);

        $result = $linesinwork->tostring();

        if ($this->Params ['Link_Follow_Deep'] > 0) {
            $this->_collecthrefs($linesinwork, &$usedfiles, $rootpath, $hrbase, $levelseparator, true);
            $result = $linesinwork->tostring();

            $this->_inform("Going to download files linked from '$text'...");

            $i = 0;
            $this->Params ['Link_Follow_Deep']--;
            while ($i < $usedfiles->count) {
                if ($usedfiles->flags [$i] == false) {
                    $buf = $usedfiles->arr [$i];

                    $this->_inform('(' . ($i + 1) . '/' . $usedfiles->count . ') ' .
                        'Working with linked file "' . $buf . '"');

                    $Params = $this->Params;

                    $doc = $this->_buildonebody($buf, $footnotes, $usedfiles, $binaries, $this->Params);

                    $this->Params = $Params;

                    if ($doc === false) {
                        $this->_inform('Document ' . $usedfiles->arr [$i] . ' skipped');
                        $this->_skipped++;
                    } else {
                        $result .= $doc;
                        $this->_done++;
                    }
                }

                $i++;
            }

            $this->Params ['Link_Follow_Deep']++;
        } else {
            $this->_collecthrefs($linesinwork, &$usedfiles, $rootpath, $hrbase, $levelseparator, false);
            $result = $linesinwork->tostring();
        }

        return $result;
    }

    public function _giftopng($gifcontent)
    {
        $png = false;

        if ($this->_gdgiftopng) {
            $oldpwd = getcwd();

            chdir($this->Tempdir);

            $gifname = tempnam($this->Tempdir, "any2fb2_gif_image");

            $giffd = fopen($gifname, 'w');
            fwrite($giffd, $gifcontent);
            fclose($giffd);

            if ($gif = @imagecreatefromgif($gifname)) {
                $tmpfile = tempnam($this->Tempdir, 'any2fb2_png_image');
                imagepng($gif, $tmpfile);

                if ($png = @imagecreatefrompng($tmpfile)) {
                    imagedestroy($png);

                    $png = file_get_contents($tmpfile);
                }

                @unlink($tmpfile);
                imagedestroy($gif);
            }

            unlink($gifname);

            chdir($oldpwd);
        } else {
            if (!class_exists('CGIF'))
                return false;

            if (($gif = @gif_loadFile($gifcontent, 0, true)) === false)
                return false;

            $png = @$gif->getPng(-1);
        }

        return $png;
    }

    public function _downloadimages(&$images)
    {
        if (!$images->count)
            return true;

        $this->_inform('Loading images (' . $images->count . ')...');

        $result = true;

        for ($i = 0; $i < $images->count; $i++) {
            $filename         = str_replace('%20', ' ', $images->arr [$i]);
            $images->arr [$i] = $filename;

            $this->_inform('(' . ($i + 1) . '/' . $images->count . ') ' . $filename);

            $encoded = false;

            switch (preg_replace('/^.*\./', '', strtolower($filename))) {
                case 'jpg':
                case 'jpeg':
                    $contenttype = 'image/jpeg';
                    break;
                case 'png':
                    $contenttype = 'image/png';
                    break;
                case 'gif':
                    $contenttype = 'image/gif';
                    break;
                default:
                    $contenttype = 'image';
            }

            $file = false;

            if (strpos(strtolower($images->arr [$i]), 'http://') === 0)
                $file = $this->_getexternalimage($images->arr [$i]);
            else
            if (is_readable($filename))
                $file = @file_get_contents($filename);

            if ($file === false)
                $this->_warn('Not loaded');

            if (strlen($file) && $contenttype == 'image/gif') {
                $this->_inform('Converting gif->png');

                $png = $this->_giftopng($file);

                if ($png !== false && strlen($png)) {
                    $file        = $png;
                    $contenttype = 'image/png';
                } else
                    $this->_warn('Cannot convert gif to png. Your reader probably would not show image');
            }

            if (strlen($file) && $contenttype != 'image') {
                $encoded          = base64_encode($file);
                $images->arr [$i] = '<binary content-type="' . $contenttype . '" ' .
                    'id="Any2FbImgLoader' . $i . '">' .
                    $encoded .
                    '</binary>';
            } else {
                $images->arr [$i] = '';
                $result           = false;
            }
        }

        return $result;
    }

    public function _outplaintext($s)
    {
        return $this->_cleantags($this->_delent($s));
    }

    public function _collecthead()
    {
        $res = '<?xml version="1.0" encoding="Windows-1251"?>' .
            '<FictionBook xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.gribuser.ru/xml/fictionbook/2.0">' .
            '<description>' .
            '<title-info>' .
            '<genre></genre>' .
            '<author>' .
            '<first-name>' . $this->_outplaintext($this->_fauthor) . '</first-name>' .
            '<middle-name>' . $this->_outplaintext($this->_mauthor) . '</middle-name>' .
            '<last-name>' . $this->_outplaintext($this->_lauthor) . '</last-name>' .
            '</author>' .
            '<book-title>' . $this->_outplaintext($this->_title) . '</book-title>';

        if ($this->_description != '')
            $res .= '<annotation>' . $this->_outplaintext($this->_description) . '</annotation>';

        $res .= '</title-info></description>';

        $progversion = preg_replace('/\$[^:]*: ([^$]*?) \$/', '\1', $this->classversion);

        $res .= '<document-info>';
        $res .= '<program-used>' . $progversion . '</program-used>';
        $res .= '</document-info>';

        return $res;
    }

    public function ParseText($text, $loadfromstring = false)
    {
        $this->_footnotes = new PArr();
        $this->_usedfiles = new PArr();
        $this->_binaries  = new PArr();

        $this->_done    = 0;
        $this->_skipped = 0;

        $xmlhead  = '';
        $nedclean = '';

        $donepart = $this->_buildonebody($text, &$this->_footnotes, &$this->_usedfiles, &$this->_binaries, $this->Params, $loadfromstring);

        if (!strcmp($donepart, ''))
            return false;

        if (!$this->_downloadimages($this->_binaries))
            $this->_warn('Error downloading images');

        $xmltext = $this->_collecthead() . $donepart;

        if ($this->_footnotes->count)
            $xmltext .= '<body name="notes">' .
                $this->_footnotes->tostring() .
                '</body>';

        $xmltext .= $this->_binaries->tostring() .
            '</FictionBook>';

        if ($this->_checkxml($xmltext, $errline, $errdescr))
            $result = $xmltext;
        else {
            $this->_description = '<p>' . $this->_cleantags($this->_description) . '</p>';

            $xmltext = $this->_collecthead() . $donepart;

            if ($this->_footnotes->count)
                $xmltext .= '<body name="notes">' .
                    $this->_footnotes->tostring() .
                    '</body>';

            $xmltext .= $this->_binaries->tostring() .
                '</FictionBook>';

            if ($this->_checkxml($xmltext, $errline, $errdescr))
                $result = $xmltext;
            else {
                $this->_warn("Error creating document header: '" . $errdescr . "'");
                $this->_warn('Try edit <head> section of html document and turn description generation off');

                $result = '';
            }
        }

        if ($result != '') {
            $this->_inform("Total linked documents imported: " . $this->_done);
            $this->_inform("Total linked documents skipped:  " . $this->_skipped);
        }

        return $result;
    }

}

/* vim600: set foldmethod=indent: */
