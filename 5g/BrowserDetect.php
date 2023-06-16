<?php

class BrowserDetect
{
    const BROWSER_CHROME = 'Chrome';
    const BROWSER_FIREFOX = 'Firefox';
    const BROWSER_OPERA = 'Opera';
    const BROWSER_SAFARI = 'Safari';
    const BROWSER_EDGE = 'Edge';
    const BROWSER_MSIE = 'MSIE';

    public static function getBrowser($userAgent = null): WebBrowser
    {
        $userAgent = $userAgent ?: $_SERVER['HTTP_USER_AGENT'];
        if (self::isFirefox($userAgent)) {
            return new WebBrowserFirefox($userAgent);
        } elseif (self::isOpera($userAgent)) {
            return new WebBrowserOpera($userAgent);
        } elseif (self::isSafari($userAgent)) {
            return new WebBrowserSafari($userAgent);
        } elseif (self::isEdge($userAgent)) {
            return new WEbBrowserEdge($userAgent);
        } elseif (self::isMsie($userAgent)) {
            return new WebBrowserMsie($userAgent);
        } else {
            return new WebBrowserChrome($userAgent);
        }
    }

    private static function isFirefox($userAgent): bool
    {
        return (
                (strpos($userAgent, 'Firefox') !== false) ||
                (strpos($userAgent, 'FxiOS') !== false) ||
                (strpos($userAgent, 'Focus') !== false) ||
                (strpos($userAgent, 'Klar') !== false)
            ) &&
            (strpos($userAgent, 'Opera') === false);
    }

    private static function isOpera($userAgent): bool
    {
        return (strpos($userAgent, 'Opera') !== false) ||
            (strpos($userAgent, 'OPR') !== false);
    }

    private static function isSafari($userAgent): bool
    {
        return !self::isOpera($userAgent) && (strpos($userAgent, 'Version') !== false) && (strpos($userAgent, 'Chrome') !== 0);
    }

    private static function isEdge($userAgent): bool
    {
        return (strpos($userAgent, 'Edg') !== false) ||
            (strpos($userAgent, 'Edge') !== false) ||
            (strpos($userAgent, 'EdgiOS') !== false) ||
            (strpos($userAgent, 'EdgA') !== false);
    }

    private static function isMsie($userAgent): bool
    {
        return (strpos($userAgent, 'MSIE') !== false) ||
            (strpos($userAgent, 'Trident') !== false);
    }

}