<?php

include '4k_1.php';
echo '<!DOCTYPE html>
<html lang="en" class="no-js logged-in client-root">
    <head>
        <meta charset="utf-8">
        <title>Instagram карьерные консультанты</title>
		</head>
    <body>
';
foreach ($logins as $login) {
    echo '<li><a href="https://www.instagram.com/' . $login . '/" target="blank" class="">' . $login . "</a></li>\n";
}
echo '
    </body>
</html>
';
