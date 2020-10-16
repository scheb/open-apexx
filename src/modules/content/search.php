<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

function search_content($items, $conn)
{
    global $set,$db,$apx,$user;

    //Suchstring generieren
    foreach ($items as $item) {
        $search[] = " ( title LIKE '%".addslashes_like($item)."%' OR text LIKE '%".addslashes_like($item)."%' ) ";
    }
    $searchstring = implode($conn, $search);

    //Ergebnisse
    $data = $db->fetch('SELECT id,title FROM '.PRE."_content WHERE ( searchable='1' AND active='1' ".section_filter().' AND ( '.$searchstring.' ) ) ORDER BY title ASC');
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            $temp = explode('->', $res['title']);
            $title = array_pop($temp);

            $link = mklink(
                'content.php?id='.$res['id'],
                'content,'.$res['id'].urlformat($title).'.html'
            );

            $result[$i]['TITLE'] = strip_tags($title);
            $result[$i]['LINK'] = $link;
        }
    }

    return $result;
}
