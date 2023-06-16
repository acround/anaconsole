<?php

class WebBrowserSafari extends WebBrowser
{

    public function getType(): string
    {
        return BrowserDetect::BROWSER_SAFARI;
    }

    public function getVersion(): ?string
    {
        $template = '~Version/([0123456789.]+)\s*~';
        if (!preg_match($template, $this->userAgent, $matches)) {
            return null;
        }
        return $matches[1];
    }

    public function isWebpSupported(): bool
    {
        $versionArr = explode('.', $this->getVersion());
        $version1 = $versionArr[0];
        $version2 = $version1;
        if (isset($versionArr[1])) {
            $version2 .= '.' . $versionArr[1];
        }
        if ($this->isMobile()) {
            return floatval($version2) >= 3.2;
        } else {
            return $version1 >= 16;
        }
    }
}