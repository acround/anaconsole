<?php

class WebBrowserChrome extends WebBrowser
{

    public function getType(): string
    {
        return BrowserDetect::BROWSER_CHROME;
    }

    public function getVersion(): ?string
    {
        $template = '~(Chrome|Chromium|CriOS)/([0123456789.]+)\s*~';
        $r = preg_match($template, $this->userAgent, $matches);
        if (!$r) {
            return null;
        }
        return $matches[2];
    }

    public function isWebpSupported(): bool
    {
        $versionArr = explode('.', $this->getVersion());
        $version1 = $versionArr[0];
        if ($this->isMobile()) {
            return $version1 >= 112;
        } else {
            return $version1 >= 32;
        }
    }
}