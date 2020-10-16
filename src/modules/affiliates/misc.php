<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Affiliate-Link weiterleiten
function misc_afflink()
{
    global $db;
    $_REQUEST['id'] = (int) $_REQUEST['id'];
    if (!$_REQUEST['id']) {
        die('missing ID');
    }

    list($link) = $db->first('SELECT link FROM '.PRE."_affiliates WHERE ( active='1' AND id='".$_REQUEST['id']."' ) LIMIT 1");
    $db->query('UPDATE '.PRE."_affiliates SET hits=hits+1 WHERE ( active='1' AND id='".$_REQUEST['id']."' ) LIMIT 1");

    header('HTTP/1.1 301 Moved Permanently');
    header('location:'.$link);
    exit;
}
