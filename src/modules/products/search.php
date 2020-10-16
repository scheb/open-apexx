<?php

// GAMES CLASS
// ===========

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

function search_products($items, $conn)
{
    global $set,$db,$apx,$user;
    require_once BASEDIR.getmodulepath('products').'functions.php';

    //Suchstring generieren
    $tagmatches = products_match_tags($items);
    foreach ($items as $item) {
        $tagmatch = array_shift($tagmatches);
        $search[] = ' ( '.iif($tagmatch, ' id IN ('.implode(',', $tagmatch).') OR ')." title LIKE '%".addslashes_like($item)."%' OR text LIKE '%".addslashes_like($item)."%' ) ";
    }
    $searchstring = implode($conn, $search);

    //Ergebnisse
    $data = $db->fetch('SELECT id,title FROM '.PRE."_products WHERE ( active='1' AND searchable='1' AND ( ".$searchstring.' ) ) ORDER BY title ASC');
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            $link = mklink(
                'products.php?id='.$res['id'],
                'products,id'.$res['id'].urlformat($res['title']).'.html'
            );

            $result[$i]['TITLE'] = strip_tags($res['title']);
            $result[$i]['LINK'] = $link;
        }
    }

    return $result;
}
