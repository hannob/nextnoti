#!/usr/bin/php
<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once('vendor/autoload.php');

$DEBUG = false;
if ((count($argv) > 1) && ($argv[1] == "-d")) {
    $DEBUG = true;
}

require_once(dirname(__FILE__) . "/config.inc.php");

if (!$_mail_to) {
    print('You need to set $_mail_to in config.inc.php!' . "\n");
    exit(1);
}

$db = new PDO('sqlite:' . $_sqlitefile);

$calid = isset($_calendarid) ? (int) $_calendarid : 1;

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

if ($ms != '') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Username = $_mail_user;
        $mail->Password = $_mail_pass;
        $mail->Host = $_mail_server;
        $mail->SMTPAuth = true;
        $mail->Port = 465;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        $mail->setFrom($_mail_from);
        $mail->addAddress($_mail_to);

        $mail->Subject = $ms;
        $mail->Body = $mc;

        $mail->send();
    } catch (Exception $e) {
        echo "An error happened...\n";
        mail($_mail_from, "Mailerror nextnoti", $mail->ErrorInfo . "\n\n" . $e->getMessage());
        die();
    }

}
