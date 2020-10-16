<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

function search_user($items, $conn)
{
    global $set,$db,$apx,$user;

    //Suchstring generieren
    foreach ($items as $item) {
        $search[] = "username LIKE '%".addslashes_like($item)."%'";
    }

    //Ergebnisse
    $data = $db->fetch('SELECT userid,username FROM '.PRE.'_user WHERE ( '.implode($conn, $search).' ) ORDER BY username ASC');
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;
            $result[$i]['TITLE'] = $res['username'];
            $result[$i]['LINK'] = $user->mkprofile($res['userid'], $res['username']);
        }
    }

    return $result;
}
