<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//////////////////////////////////////////////////////////////////////////////////////////// FUNKTIONEN DEFINIEREN

//Statistik: Alte Einträge löschen => bessere Performance
function cron_stats_clean($lastexec)
{
    global $set,$db,$apx;
    $db->query('DELETE FROM '.PRE."_stats_referer WHERE daystamp<'".date('Ymd', (time() - 30 * 24 * 3600))."'");
    $db->query('DELETE FROM '.PRE."_stats_userenv WHERE daystamp<'".date('Ymd', (time() - 30 * 24 * 3600))."'");
}
