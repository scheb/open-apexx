<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

require_once BASEDIR.getmodulepath('glossar').'functions.php';

function misc_glossar_comments()
{
    global $set,$db,$apx,$user;
    $_REQUEST['id'] = (int) $_REQUEST['id'];
    if (!$_REQUEST['id']) {
        die('missing ID!');
    }
    $apx->tmpl->loaddesign('blank');
    glossar_showcomments($_REQUEST['id']);
}
