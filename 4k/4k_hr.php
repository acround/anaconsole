<?php

$list = file('4k_hr');
for ($i = 0; $i < count($list); $i++) {
    $list[$i] = trim($list[$i]);
    if (!$list[$i]) {
        unset($list[$i]);
    }
}
echo '<!DOCTYPE html>
<html lang="en" class="no-js logged-in client-root">
    <head>
        <meta charset="utf-8">
        <title>Instagram карьерные консультанты</title>
        <style type="text/css">
            a.aim {
                color: #000fff;
                font-weight: bold;
            }
            a.semi {
                color: #00ffff;
                font-weight: bold;
            }
            a.bad {
                color: #ff0000;
                font-weight: bold;
            }
        </style>
		</head>
    <body>
        <h4>Legend</h4>
        <ul>
            <li><a class="aim">&gt; 10000 followers</a></li>
            <li><a class="semi">From 1000 to 9999 followers</a></li>
            <li><a class="bad">Account is missing</a></li>
            <li><a class="" href="">Nothing special</a></li>
        </ul>
        <h4>List</h4>
        <ol>
';
foreach ($list as $row) {
    list($login, $title) = explode('=', $row);
    if (!$title) {
        $title = $login;
    }
    echo '<li><a href="https://www.instagram.com/' . $login . '/" target="blank" class="">' . $title . "</a></li>\n";
}
echo '
        </ol>
    </body>
</html>
';

//print_r($list);
