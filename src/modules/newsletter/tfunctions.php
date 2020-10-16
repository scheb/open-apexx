<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Newsletter-Form ausgeben
function newsletter_form()
{
    global $set,$db,$apx,$user;
    $tmpl = new tengine();

    $apx->lang->drop('form', 'newsletter');

    //Kategorien
    $catinfo = $set['newsletter']['categories'];
    if (!is_array($set['newsletter']['categories'])) {
        $set['newsletter']['categories'] = [];
    }
    asort($catinfo);

    foreach ($catinfo as $id => $name) {
        ++$i;
        $catdata[$i]['ID'] = $id;
        $catdata[$i]['TITLE'] = $name;
    }

    $tmpl->assign('CATEGORY', $catdata);
    $tmpl->parse('functions/form', 'newsletter');
}
