<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Informationen auslesen
function gallery_info($id)
{
    global $apx,$db,$set;
    static $cache;
    $id = (int) $id;
    if (!$id) {
        return [];
    }
    if (isset($cache[$id])) {
        return $cache[$id];
    }
    $cache[$id] = $db->first('SELECT * FROM '.PRE."_gallery WHERE ( id='".$id."' AND '".time()."' BETWEEN starttime AND endtime ) LIMIT 1");

    return $cache[$id];
}
