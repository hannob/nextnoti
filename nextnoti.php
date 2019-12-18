#!/usr/bin/php
<?php

require_once("config.inc.php");

if (!$c_mailto) {
    print('You need to set $c_mailto in config.inc.php!'."\n");
    exit(1);
}

$db = new PDO('sqlite:'.$c_sqlitefile);

$out = $db->query('SELECT calendardata FROM oc_calendarobjects');

$mc = '';
$ms = '';
foreach ($out as $row) {
    $s = $row['calendardata'];
    if (preg_match('/DTSTART.*'.date('Ymd').'/', $s)) {
        preg_match('/SUMMARY:(.*)/', $s, $m);
        $ms .= $m[1].',';
        $mc .= $s;
    }
}

if ($ms != '') {
    mail($c_mailto, $ms, $mc);
}
