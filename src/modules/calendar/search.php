<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

function search_calendar($items, $conn)
{
    global $set,$db,$apx,$user;
    require_once BASEDIR.getmodulepath('calendar').'functions.php';

    //Suchstring generieren
    $tagmatches = calendar_match_tags($items);
    foreach ($items as $item) {
        $tagmatch = array_shift($tagmatches);
        $search[] = ' ( '.iif($tagmatch, ' id IN ('.implode(',', $tagmatch).') OR ')." title LIKE '%".addslashes_like($item)."%' OR text LIKE '%".addslashes_like($item)."%' ) ";
    }
    $searchstring = implode($conn, $search);

    //Ergebnisse
    $data = $db->fetch('SELECT id,title FROM '.PRE.'_calendar_events WHERE ( active!=0 AND private=0 '.section_filter().' AND ( '.$searchstring.' ) ) ORDER BY startday ASC, starttime ASC, title ASC');
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            $link = mklink(
                'events.php?id='.$res['id'],
                'events,id'.$res['id'].urlformat($res['title']).'.html'
            );

            $result[$i]['TITLE'] = strip_tags($res['title']);
            $result[$i]['LINK'] = $link;
        }
    }

    return $result;
}
