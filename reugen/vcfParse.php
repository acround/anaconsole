<?php
const CSV_SEPARATOR = ';';
const STR_SEPARATOR = ',';
$fileName = 'vCard.vcf';
$fileNameFull = __DIR__ . DIRECTORY_SEPARATOR . $fileName;
if (!file_exists($fileNameFull)) {
    echo "No file\n";
    exit();
}
$file = explode("\n", file_get_contents($fileNameFull));
$titles = [
//    'VERSION',
//    'PRODID',
    'FN',
    'N',
    'TEL',
//    'CATEGORIES',
];
$items = [
    implode(CSV_SEPARATOR, $titles),
];
$item = [];
foreach ($file as $string) {
    $strParsed = explode(':', $string);
    $keyParsed = explode(';', $strParsed[0]);
    $valueParsed = trim($strParsed[1] ?? '');
    $valueParsed = trim(str_replace(CSV_SEPARATOR, STR_SEPARATOR, $valueParsed), STR_SEPARATOR);
//    print_r([$keyParsed[0], $strParsed[1]]);
    switch ($keyParsed[0]) {
        case 'BEGIN':
            $item = array_fill(0, 3, '');
            break;
//        case 'VERSION':
//            $item[0] = $valueParsed;
//            break;
//        case 'PRODID':
//            $item[1] = $valueParsed;
//            break;
        case 'FN':
            $item[0] = $valueParsed;
            break;
        case 'N':
            $item[1] = $valueParsed;
            break;
        case 'TEL':
            $item[2] = $valueParsed;
            break;
//        case 'CATEGORIES':
//            $item[5] = $valueParsed;
//            break;
        case 'END':
            $items[] = implode(CSV_SEPARATOR, $item);
            break;
    }
}
//echo "\n" . implode("\n", $items);
file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'vCard.csv', implode("\n", $items));