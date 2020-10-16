<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//////////////////////////////////////////////////////////////////////////////////////////// FUNKTIONEN DEFINIEREN

//Parameter aus URL filtern
function forum_filter_url($params = [])
{
    $url = $_SERVER['REQUEST_URI'];

    foreach ($params as $param) {
        $url = preg_replace('#\?'.$param.'=(.*)(&|$)#siUe', "'\\2'=='&' ? '?' : ''", $url);
        $url = preg_replace('#\&'.$param.'=(.*)(&|$)#siU', '\\2', $url);
    }

    return HTTP_HOST.str_replace('&', '&amp;', $url);
}
