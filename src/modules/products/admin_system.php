<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Spiele-Liste
function products_list($select = 0, $ignore = 0)
{
    echo get_product_list($select, $ignore);
}

//Spiele-Liste erzeugen
function get_product_list($select = 0, $ignore = 0)
{
    global $apx,$db,$set;
    $select = (int) $select;

    //Leeres Feld
    $list = '<option value=""></option>';

    //Sprachplatzhalter dropen
    $apx->lang->drop('type', 'products');

    //Auslesen
    $lasttype = '';
    $data = $db->fetch('SELECT id,type,title FROM '.PRE."_products WHERE active='1' ORDER BY type ASC,title ASC");
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;
            if ($ignore == $res['id']) {
                continue;
            }
            //Gruppieren
            if ($res['type'] != $lasttype) {
                if ($lasttype) {
                    $list .= '</optgroup>';
                }
                $list .= '<optgroup label="'.$apx->lang->get('PRODTYPE_'.strtoupper($res['type'])).'">';
            }

            $list .= '<option value="'.$res['id'].'"'.iif($res['id'] == $select, ' selected="selected"').'>'.replace($res['title']).'</option>';

            $lasttype = $res['type'];
        }
        if ($lasttype) {
            $list .= '</optgroup>';
        }
    }

    return $list;
}
