<?php

abstract class WebBrowser
{
    protected $userAgent;

    public function __construct(string $userAgent = null)
    {
        $userAgent = $userAgent ?: $_SERVER['HTTP_USER_AGENT'];
        $this->userAgent = $userAgent;
    }

    abstract public function getType(): string;

    abstract public function getVersion(): ?string;

    abstract public function isWebpSupported(): bool;

    public function isMobile(): bool
    {
        $pattern = '/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|' .
            'wireless| mobi|lg380|ahong|lgku|lgu900|lg210|lg47|lg920|lg840|' .
            'lg370|sam-r|mg50|s55|g83|mk99|vx400|t66|d615|d763|sl900|el370|' .
            'mp500|samu4|samu3|vx10|xda_|samu6|samu5|samu7|samu9|a615|b832|' .
            'm881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|' .
            'r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|' .
            'i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|' .
            'htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8352rc|' .
            'sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|' .
            'p404i|s210|c5100|s940|teleca|c500|s590|foma|vx8|samsu|vx9|a1000|' .
            '_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|' .
            's800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|' .
            'd736|p-9521|telco|sl74|ktouch|m4u\/|me702|kddi|phone|lg |' .
            'sonyericsson|samsung|nokia|240x|x320vx10|sony cmd|motorola|' .
            'up.browser|up.link|mmp|symbian|android|tablet|iphone|ipad|smartphone|j2me|wap|vodafone|o2|' .
            'pocket|kindle|mobile|psp|treo)/i';
        return (boolean)preg_match($pattern, $this->userAgent);
    }

}