<?php

class WebBrowserMsie extends WebBrowser
{

    public function getType(): string
    {
        return BrowserDetect::BROWSER_MSIE;
    }

    public function getVersion(): ?string
    {
        $template = '~MSIE ([0123456789.]+)\s*~';
        if (preg_match($template, $this->userAgent, $matches)) {
            return $matches[1];
        }
        $template = '~rv:([0123456789.]+)\s*~';
        if (preg_match($template, $this->userAgent, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public function isWebpSupported(): bool
    {
        return false;
    }
}