<?php
const WWW_DNS = 'https://www.g5e.com/';
$languages = [
    'en',
    'de',
    'fr',
    'it',
    'es-es',
    'es',
    'sv',
    'ru',
    'uk',
    'zh',
    'zh-hk',
    'ko',
    'ja',
    'pt-pt',
    'pt',
    'ar',
];
$sXml = simplexml_load_file('sitemap.old.xml');
$sitemapIndex = xmlwriter_open_memory();
xmlwriter_set_indent($sitemapIndex, 1);
$res = xmlwriter_set_indent_string($sitemapIndex, '  ');
xmlwriter_start_document($sitemapIndex, '1.0', 'UTF-8');
xmlwriter_start_element($sitemapIndex, 'sitemapindex');
xmlwriter_start_attribute($sitemapIndex, 'xmlns');
xmlwriter_text($sitemapIndex, 'http://www.sitemaps.org/schemas/sitemap/0.9');
xmlwriter_end_attribute($sitemapIndex);
foreach ($languages as $language) {

    $sitemapLocale = xmlwriter_open_memory();
    xmlwriter_set_indent($sitemapLocale, 1);
    $res = xmlwriter_set_indent_string($sitemapLocale, '  ');
    xmlwriter_start_document($sitemapLocale, '1.0', 'UTF-8');
    xmlwriter_start_element($sitemapLocale, 'urlset');
    xmlwriter_start_attribute($sitemapLocale, 'xmlns');
    xmlwriter_text($sitemapLocale, 'https://www.sitemaps.org/schemas/sitemap/0.9');
    xmlwriter_end_attribute($sitemapLocale);
    xmlwriter_start_attribute($sitemapLocale, 'xmlns:xsi');
    xmlwriter_text($sitemapLocale, 'https://www.w3.org/2001/XMLSchema-instance');
    xmlwriter_end_attribute($sitemapLocale);
    xmlwriter_start_attribute($sitemapLocale, 'xsi:schemaLocation');
    xmlwriter_text($sitemapLocale, 'https://www.sitemaps.org/schemas/sitemap/0.9 https://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
    xmlwriter_end_attribute($sitemapLocale);
    foreach ($sXml->url as $url) {
        $baseUrl = $url->loc->__toString();
        $urlParsed = parse_url($baseUrl);
        $locBeginning = $urlParsed['scheme'] . '://' . $urlParsed['host'];
        $loc = $locBeginning . ($language == 'en' ? '' : ('/' . $language)) . $urlParsed['path'];
        xmlwriter_start_element($sitemapLocale, 'url');
        xmlwriter_start_element($sitemapLocale, 'loc');
        xmlwriter_text($sitemapLocale, $loc);
        xmlwriter_end_element($sitemapLocale); //loc
//        if (isset($url->lastmod)) {
//            xmlwriter_start_element($sitemapLocale, 'lastmod');
//            xmlwriter_text($sitemapLocale, $url->lastmod->__toString());
//            xmlwriter_end_element($sitemapLocale); // lastmod
//        }
        xmlwriter_start_element($sitemapLocale, 'changefreq');
        xmlwriter_text($sitemapLocale, $url->changefreq->__toString());
        xmlwriter_end_element($sitemapLocale); //changefreq
        xmlwriter_end_element($sitemapLocale); // url
    }
    xmlwriter_end_element($sitemapLocale); // urlset
    xmlwriter_end_document($sitemapLocale);
    $fileName = 'sitemap.' . $language . '.xml';
    file_put_contents($fileName, xmlwriter_output_memory($sitemapLocale));

    xmlwriter_start_element($sitemapIndex, 'sitemap');
    xmlwriter_start_element($sitemapIndex, 'loc');
    xmlwriter_text($sitemapIndex, WWW_DNS . $fileName);
    xmlwriter_end_element($sitemapIndex); // loc
    xmlwriter_end_element($sitemapIndex); // sitemap
}
xmlwriter_end_element($sitemapIndex); // sitemapindex
xmlwriter_end_document($sitemapIndex);
file_put_contents('sitemap.xml', xmlwriter_output_memory($sitemapIndex));
