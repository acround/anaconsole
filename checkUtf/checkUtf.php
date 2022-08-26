<?php

function isUtf($text)
{
    $symbols = array();
    for ($i = 0; $i < strlen($text); $i++) {
        $s = substr($text, $i, 1);
        $n = ord($s);
        if (!isset($symbols[$n])) {
            $symbols[$n] = 0;
        }
        $symbols[$n]++;
    }
    $Ppercents = $symbols[208] / strlen($text) * 100;
    $Cpercents = $symbols[209] / strlen($text) * 100;
    $Apercents = $Ppercents + $Cpercents;
    return ($Apercents > 20);
}

$dirName   = realpath('./') . '/texts/';
$dir       = opendir($dirName);
$filesutf  = array();
$files     = array();
$msS       = array();
$Ppercents = array();
$Cpercents = array();
$Apercents = array();
while (($file      = readdir($dir)) !== false) {
    if (substr($file, 0, 1) != '.') {
        $text = file_get_contents($dirName . $file);
//		$symbols = array();
//		for ($i = 0; $i < strlen($text); $i++) {
//			$s = substr($text, $i, 1);
//			$n = ord($s);
//			if (!isset($symbols[$n])) {
//				$symbols[$n] = 0;
//			}
//			$symbols[$n] ++;
//		}
//		$Ppercents[$file] = $symbols[208] / strlen($text) * 100;
//		$Cpercents[$file] = $symbols[209] / strlen($text) * 100;
//		$Apercents[$file] = $Ppercents[$file] + $Cpercents[$file];
//		asort($symbols);
//		end($symbols);
//		$max1 = each($symbols);
//		end($symbols);
//		prev($symbols);
//		$max2 = each($symbols);
//		$maxSym1 = $max1['key'];
//		$maxSym2 = $max2['key'];
//		$part1 = $max1['value'] / strlen($text) * 100;
//		$part2 = $max2['value'] / strlen($text) * 100;
//		$part = $part1 + $part2;
//		$maxSyms = array(
//			$maxSym1,
//			$maxSym2
//		);
//		$maxSymsS = array();
//		foreach ($maxSyms as $value) {
//			$maxSymsS[] = chr($value);
//		}
//		$ms = iconv('windows-1251', 'utf-8', implode('', $maxSymsS));
//		$msS[] = $ms;
//		if ($Apercents[$file] > 20) {
//			$filesutf[] = $file . ' - utf-8: (PC-фактор:' . floor($Apercents[$file]) . '% (' . $ms . '))';
//		} else {
//			$files[] = $file . ' - windows-1251: (PC-фактор:' . floor($Apercents[$file]) . '% (' . $ms . '))';
//		}
        if (isUtf($text)) {
            $filesutf[] = $file . ' - utf-8: ' . $text;
        } else {
            $files[] = $file . ' - not utf-8: ' . iconv('windows-1251', 'utf-8', $text);
        }
    }
}
sort($files);
sort($filesutf);
print_r($filesutf);
print_r($files);
