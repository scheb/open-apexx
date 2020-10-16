<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Banner-Capping
function misc_banner_cap()
{
    global $set;
    $viewid = $_REQUEST['viewid'];
    if ($viewid) {
        setcookie($set['main']['cookie_pre'].'_capping_'.$viewid, intval($_COOKIE[$set['main']['cookie_pre'].'_capping_'.$viewid]) + 1);
    }
    exit;
}
