#!/usr/bin/php
<?php

$DEBUG = false;
if ((count($argv) > 1) && ($argv[1] == "-d")) {
    $DEBUG = true;
}

require_once(dirname(__FILE__) . "/config.inc.php");

if (!$c_mailto) {
    print('You need to set $c_mailto in config.inc.php!' . "\n");
    exit(1);
}

$db = new PDO('sqlite:' . $c_sqlitefile);

$calid = isset($c_calendarid) ? (int)$c_calendarid : 1;

$out = $db->query("SELECT calendardata FROM oc_calendarobjects WHERE calendarid=$calid");

$mc = '';
$ms = [];
foreach ($out as $row) {
    $s = $row['calendardata'];
    if (preg_match('/DTSTART.*' . date('Ymd') . '/', $s)) {
        preg_match('/SUMMARY[^:]*:(.*)/', $s, $m);
        $summary = "";
        if (count($m) < 2) {
            echo "WARNING: Calendar entry has no SUMMARY";
        } else {
            $summary = trim($m[1]);
        }
        if ($DEBUG) {
            echo "Found Calendar Entry " . $summary . " today\n";
        }
        array_push($ms, $summary);
        $mc .= $s;
    }
}
$ms = implode(",", $ms);

$ms = mb_encode_mimeheader($ms, 'UTF-8', 'Q');
if ($ms != '') {
    mail($c_mailto, $ms, $mc, "From: " . $c_mailfrom);
}
