<?php

class WEbBrowserEdge extends WebBrowser
{

    public function getType(): string
    {
        return BrowserDetect::BROWSER_EDGE;
    }

    public function getVersion(): ?string
    {
        $templates = [
            'Edg',
            'Edge',
            'EdgiOS',
            'EdgA',
        ];
        foreach ($templates as $template) {
            $template = '~' . $template . '/([0123456789.]+)\s*~';
            if (preg_match($template, $this->userAgent, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public function isWebpSupported(): bool
    {
        $versionArr = explode('.', $this->getVersion());
        $version1 = $versionArr[0];
        return $version1 >= 18;
    }
}