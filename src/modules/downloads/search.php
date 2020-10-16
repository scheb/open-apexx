<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

function search_downloads($items, $conn)
{
    global $set,$db,$apx,$user;
    require_once BASEDIR.getmodulepath('downloads').'functions.php';

    //Suchstring generieren
    $tagmatches = downloads_match_tags($items);
    foreach ($items as $item) {
        $tagmatch = array_shift($tagmatches);
        $search[] = ' ( '.iif($tagmatch, ' id IN ('.implode(',', $tagmatch).') OR ')." title LIKE '%".addslashes_like($item)."%' OR text LIKE '%".addslashes_like($item)."%' OR author LIKE '%".addslashes_like($item)."%' ) ";
    }
    $searchstring = implode($conn, $search);

    //Downloads durchsuchen
    $data = $db->fetch('SELECT id,title FROM '.PRE."_downloads WHERE ( searchable='1' AND '".time()."' BETWEEN starttime AND endtime ".section_filter().' AND ( '.$searchstring.' ) ) ORDER BY addtime DESC');
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            $result[$i]['TITLE'] = $res['title'];
            $result[$i]['LINK'] = mklink(
                'downloads.php?id='.$res['id'],
                'downloads,id'.$res['id'].urlformat($res['title']).'.html'
            );
        }
    }

    return $result;
}
