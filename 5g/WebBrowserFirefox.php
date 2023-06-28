<?php

class WebBrowserFirefox extends WebBrowser
{

    public function getType(): string
    {
        return BrowserDetect::BROWSER_FIREFOX;
    }

    public function getVersion(): ?string
    {
        $template = '~(Firefox|FxiOS)/([0123456789.]+)\s*~';
        if (!preg_match($template, $this->userAgent, $matches)) {
            return null;
        }
        return $matches[2];
    }

    public function isWebpSupported(): bool
    {
        $versionArr = explode('.', $this->getVersion());
        $version1 = $versionArr[0];
        if ($this->isMobile()) {
            return $version1 >= 110;
        } else {
            return $version1 >= 65;
        }
    }
}