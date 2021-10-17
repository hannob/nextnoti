#!/usr/bin/php
<?php

require_once(dirname(__FILE__)."/config.inc.php");

if (!$c_mailto) {
    print('You need to set $c_mailto in config.inc.php!'."\n");
    exit(1);
}

$db = new PDO('sqlite:'.$c_sqlitefile);

$calid = isset($c_calendarid) ? (int)$c_calendarid : 1;

$out = $db->query("SELECT calendardata FROM oc_calendarobjects WHERE calendarid=$calid");

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

$ms = mb_encode_mimeheader($ms, 'UTF-8', 'Q');
if ($ms != '') {
    mail($c_mailto, $ms, $mc, "From: ".$c_mailfrom);
}
