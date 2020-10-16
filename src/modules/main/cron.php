<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//////////////////////////////////////////////////////////////////////////////////////////// FUNKTIONEN DEFINIEREN

//Cache leeren
function cron_clear_cache($lastexec)
{
    global $db,$set;

    $handle = opendir(BASEDIR.getpath('cache'));
    while ($file = readdir($handle)) {
        if ('.' == $file || '..' == $file) {
            continue;
        }
        //Datei löschen, wenn älter als 7 Tage
        $lastchange = filemtime(BASEDIR.getpath('cache').$file);
        if ($lastchange + 7 * 24 * 3600 + 3600 < time()) { //7 Tage + 1 Std.
            unlink(BASEDIR.getpath('cache').$file);
        }
    }
    closedir($handle);

    //Captchas löschen
    $now = time();
    $data = $db->fetch('SELECT hash FROM '.PRE.'_captcha WHERE time<='.($now - 3600));
    $db->fetch('DELETE FROM '.PRE.'_captcha WHERE time<='.($now - 3600));
    foreach ($data as $res) {
        @unlink(BASEDIR.getpath('uploads').'temp/captcha_'.$res['hash'].'.png');
    }
}

//Datenbank optimieren
function cron_optimize_database($lastexec)
{
    global $db,$set;

    //Alte Searches löchen
    $db->query('DELETE FROM '.PRE."_search WHERE time<='".(time() - 12 * 3600)."'");
    $db->query('DELETE FROM '.PRE."_search_item WHERE time<='".(time() - 30 * 24 * 3600)."'");
    $db->query('DELETE FROM '.PRE."_sessions WHERE starttime<='".(time() - 12 * 3600)."'");

    //Datenbank optimieren
    $data = $db->fetch('SHOW TABLES FROM '.$set['mysql_db']);
    $tables = [];
    if (count($data)) {
        foreach ($data as $res) {
            $tables[] = $res[0];
        }
    }
    if (count($tables)) {
        $db->query('OPTIMIZE TABLE '.implode(',', $tables));
    }
}
