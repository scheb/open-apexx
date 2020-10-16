<?php

$apx->lang->drop('logout');
setcookie($set['main']['cookie_pre'].'_userid', '', time() - 100 * 24 * 3600, '/');
setcookie($set['main']['cookie_pre'].'_password', '', time() - 100 * 24 * 3600, '/');

//Auth-Cookies löschen
foreach ($_COOKIE as $name => $value) {
    if ('usergallery_pwd_' == substr($name, 0, 16)) {
        setcookie($name, '', time() - 99999);
    }
}

//Weiterleitung zur zuletzt besuchten Seite
$filter = [
    'user,login.html',
    'user.php?action=login',
];
$refforward = true;
foreach ($filter as $url) {
    if (false !== strpos($_SERVER['HTTP_REFERER'], $url)) {
        $refforward = false;

        break;
    }
}
if ($refforward && $_SERVER['HTTP_REFERER']) {
    $goto = $_SERVER['HTTP_REFERER'];
} else {
    $goto = mklink('user.php', 'user.html');
}

message($apx->lang->get('MSG_OK'), $goto);
