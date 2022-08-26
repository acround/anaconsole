<?php

$in      = 'groups.json';
$groups  = file_get_contents($in);
$groupsO = json_decode($groups);
$out     = [];
foreach ($groupsO->list as $group) {
    $out[] = $group->customer_login;
}
$outStr = implode("\n", array_unique($out));
file_put_contents('customers.txt', $outStr);
