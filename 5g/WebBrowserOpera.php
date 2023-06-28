<?php

class WebBrowserOpera extends WebBrowser
{

    public function getType(): string
    {
        return BrowserDetect::BROWSER_OPERA;
    }

    public function getVersion(): ?string
    {
        $template = '~Version/([0123456789.]+)\s*~';
        if (preg_match($template, $this->userAgent, $matches)) {
            return $matches[1];
        }
        $template = '~Opera ([0123456789.]+)\s*~';
        if (preg_match($template, $this->userAgent, $matches)) {
            return $matches[1];
        }
        $template = '~Opera/([0123456789.]+)\s*~';
        if (preg_match($template, $this->userAgent, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function isWebpSupported(): bool
    {
        $versionArr = explode('.', $this->getVersion());
        $version1 = $versionArr[0];
        if ($this->isMobile()) {
            if (strpos($this->userAgent, 'Opera Mini') !== false) {
                return true;
            } else {
                return $version1 >= 12;
            }
        } else {
            return $version1 >= 19;
        }
    }
}