<?php

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_start.php';  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->module('user');
$apx->lang->drop('all');
headline($apx->lang->get('HEADLINE'), mklink('user.php', 'user.html'));
titlebar($apx->lang->get('HEADLINE'));

//Alte PNs der User löschen
$db->query('DELETE FROM '.PRE."_user_pms WHERE ( del_to='1' AND del_from='1' )");

////////////////////////////////////////////////////////////////////////////////////////// LOGOUT

if ('logout' == $_REQUEST['action']) {
    $apx->lang->drop('logout');
    setcookie($set['forum_cookiename_userid'], '', time() - 99999, $set['forum_cookie_path'], $set['forum_cookie_domain']);
    setcookie($set['forum_cookiename_password'], '', time() - 99999, $set['forum_cookie_path'], $set['forum_cookie_domain']);
    setcookie($set['forum_cookiename_session'], '', time() - 99999, $set['forum_cookie_path'], $set['forum_cookie_domain']);

    //Weiterleitung zur zuletzt besuchten Seite
    $filter = [
        'user.html',
        'user,login.html',
        'user.php?action=login',
        'user.php',
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
        $goto = mklink('index.php', 'index.html');
    }

    message($apx->lang->get('MSG_OK'), $goto);
} elseif (!$user->info['userid']) {
    ////////////////////////////////////////////////////////////////////////////////////////// LOGIN

    $apx->lang->drop('login');
    headline($apx->lang->get('HEADLINE_LOGIN'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
    titlebar($apx->lang->get('HEADLINE_LOGIN'));

    //Forum-Verbindung vorhanden?
    $forumdb = $user->getForumConn();

    //Anmelden
    if ($_POST['send']) {
        if (!$_POST['login_user'] || !$_POST['login_pwd']) {
            message('back');
        } else {
            //Benutzer geblockt?
            $strikes = $forumdb->first('
				SELECT COUNT(*) AS strikes, MAX(striketime) AS lasttime
				FROM '.VBPRE."strikes
				WHERE strikeip = '".addslashes(get_remoteaddr())."'
			");
            if ($_POST['login_user']) {
                $strikes_user = $forumdb->first('
					SELECT COUNT(*) AS strikes
					FROM '.VBPRE."strikes
					WHERE strikeip = '".addslashes(get_remoteaddr())."' AND username = '".addslashes(strtolower($_POST['login_user']))."'
				");
            }
            if (0 == $strikes['strikes']) {
                $strikes_user['strikes'] = 1;
            }
            if ($strikes['strikes'] >= 5 && $strikes['lasttime'] > time() - 900) {
                message($apx->lang->get('MSG_BLOCK'));
            } else {
                //Login versuchen
                $res = $forumdb->first('
					SELECT userid,usergroupid,password,salt
					FROM '.VBPRE."user
					WHERE LOWER(username)='".addslashes(strtolower($_POST['login_user']))."'
					LIMIT 1
				");

                //Login fehlgeschlagen
                if (!$res['userid'] || $res['password'] != md5(md5($_POST['login_pwd']).$res['salt'])) {
                    $forumdb->query('
						INSERT INTO '.VBPRE.'strikes
						(striketime, strikeip, username)
						VALUES
						('.time().", '".addslashes(get_remoteaddr())."', '".addslashes($_POST['login_user'])."')
					");
                    message($apx->lang->get('MSG_FAIL'), 'javascript:history.back()');
                }

                //Account nicht aktiviert
                elseif (3 == $res['usergroupid']) {
                    message($apx->lang->get('MSG_NOTACTIVE'), 'javascript:history.back()');
                }

                //Login erfolgreich
                else {
                    $pwdcrypt = md5($res['password'].$set['forum_cookie_salt']);
                    setcookie($set['forum_cookiename_userid'], $res['userid'], time() + 100 * 24 * 3600, $set['forum_cookie_path'], $set['forum_cookie_domain']);
                    setcookie($set['forum_cookiename_password'], $pwdcrypt, time() + 100 * 24 * 3600, $set['forum_cookie_path'], $set['forum_cookie_domain']);
                    $_COOKIE[$set['forum_cookiename_userid']] = $res['userid'];
                    $_COOKIE[$set['forum_cookiename_password']] = $pwdcrypt;

                    //Strikes löschen
                    $forumdb->query('DELETE FROM '.VBPRE."strikes WHERE strikeip = '".addslashes(get_remoteaddr())."' AND username='".addslashes($_POST['login_user'])."'");

                    //Session erzeugen
                    if ($set['forum_autologin']) {
                        $user->createForumSession($res['userid']);
                    }

                    //Weiterleitung zur zuletzt besuchten Seite
                    $filter = [
                        'user.html',
                        'user,login.html',
                        'user.php?action=login',
                        'user.php',
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
                        $goto = mklink('index.php', 'index.html');
                    }

                    message($apx->lang->get('MSG_OK'), $goto);
                }
            }
        }
    } else {
        $postto = mklink('user.php', 'user.html');
        $apx->tmpl->assign('POSTTO', $postto);
        $apx->tmpl->parse('login');
    }
} //END: NOT LOGGED IN

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
