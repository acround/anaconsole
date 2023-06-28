<?php
require_once 'BrowserDetect.php';
require_once 'WebBrowser.php';
require_once 'WebBrowserChrome.php';
require_once 'WebBrowserFirefox.php';
require_once 'WebBrowserMsie.php';
require_once 'WebBrowserOpera.php';
require_once 'WebBrowserSafari.php';
require_once 'WEbBrowserEdge.php';
$msq = mysqli_connect('localhost', 'acround', '12cool09', 'test');
$select = mysqli_query($msq, 'SELECT * FROM `user_agent`');
if ($select) {
    while ($res = mysqli_fetch_assoc($select)) {
        $browser = BrowserDetect::getBrowser($res['agent']);
        if (($browser->getType() == $res['browser']) && $browser->getVersion() == $res['version']) {
            $update = 'UPDATE `user_agent` SET `device` = \'' . $browser->isMobile() . '\' WHERE `id`=' . $res['id'];
            mysqli_query($msq, $update);
        } else {
            echo $res['id'] . '=>';
            echo 'ERROR:';
            echo $browser->getType() . '=' . $res['browser'] . '/' . $browser->getVersion() . '=' . $res['version'];
            echo "\n";
        }
    }
}
mysqli_close($msq);
echo "Local: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
$browser = BrowserDetect::getBrowser();
echo 'Browser:' . $browser->getType() . "<br />\n";
echo 'Version:' . $browser->getVersion() . "<br />\n";
echo 'Supports webp:' . ($browser->isWebpSupported() ? 'yes' : 'no') . "<br />\n";
