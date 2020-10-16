<?php

$apx->lang->drop('login');
headline($apx->lang->get('HEADLINE_LOGIN'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_LOGIN'));

if ($_POST['send']) {
    if (!$_POST['login_user'] || !$_POST['login_pwd']) {
        message('back');
    } else {
        $res = $db->first('SELECT userid,password,salt,active,reg_key FROM '.PRE."_user WHERE LOWER(username_login)='".addslashes(strtolower($_POST['login_user']))."' LIMIT 1");
        list($failcount) = $db->first('SELECT count(time) FROM '.PRE."_loginfailed WHERE ( userid='".$res['userid']."' AND time>='".(time() - 15 * 60)."' )");

        if ($failcount >= 5) {
            message($apx->lang->get('MSG_BLOCK'), 'javascript:history.back()');
        } elseif (!$res['userid'] || $res['password'] != md5(md5($_POST['login_pwd']).$res['salt'])) {
            if ($res['userid']) {
                $db->query('INSERT INTO '.PRE."_loginfailed VALUES ('".$res['userid']."','".time()."')");
            }
            if (4 == $count) {
                message($apx->lang->get('MSG_BLOCK'), 'javascript:history.back()');
            } else {
                message($apx->lang->get('MSG_FAIL'), 'javascript:history.back()');
            }
        } elseif (!$res['active']) {
            message($apx->lang->get('MSG_BANNED'), 'javascript:history.back()');
        } elseif (2 == $set['user']['useractivation'] && 'BYADMIN' == $res['reg_key']) {
            message($apx->lang->get('MSG_ADMINACTIVATION'), 'javascript:history.back()');
        } elseif (3 == $set['user']['useractivation'] && $res['reg_key']) {
            message($apx->lang->get('MSG_NOTACTIVE'), 'javascript:history.back()');
        } else {
            setcookie($set['main']['cookie_pre'].'_userid', $res['userid'], time() + 100 * 24 * 3600, '/');
            setcookie($set['main']['cookie_pre'].'_password', $res['password'], time() + 100 * 24 * 3600, '/');

            //Loginfailed löschen
            $db->query('DELETE FROM '.PRE."_loginfailed WHERE userid='".$res['userid']."'");

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
        }
    }
} else {
    $postto = mklink('user.php', 'user.html');
    $apx->tmpl->assign('POSTTO', $postto);
    $apx->tmpl->parse('login');
}
