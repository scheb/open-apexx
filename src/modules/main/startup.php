<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

////////////////////////////////////////////////////////////////////////////////////////////

//Seite sperren
if ($set['main']['closed']) {
    //Prüfen ob Admin
    if (isset($user) && $user->info['groupid']) {
        list($gtype) = $db->first('SELECT gtype FROM '.PRE."_user_groups WHERE groupid='".$user->info['groupid']."' LIMIT 1");
    }

    //Kein Admin!
    if ('admin' != $gtype && 'indiv' != $gtype && !$_COOKIE[$set['main']['cookie_pre'].'_admin_userid']) {
        message($set['main']['close_message']);
        require BASEDIR.'lib/_end.php';
    }

    $apx->tmpl->assign_static('CLOSE_MESSAGE', $set['main']['close_message']);
}

//Parameter aus URL filtern
function main_filter_url($params = [])
{
    $url = $_SERVER['REQUEST_URI'];

    foreach ($params as $param) {
        $url = preg_replace_callback('#\?'.$param.'=(.*)(&|$)#siU', function ($m) {return   '&' == $m[2] ? '?' : ''; }, $url);
        $url = preg_replace_callback('#\&'.$param.'=(.*)(&|$)#siU', function ($m) {return $m[2]; }, $url);
    }

    return HTTP_HOST.str_replace('&', '&amp;', $url);
}

//Druckversion
if ('1' == $_REQUEST['print']) {
    $currenturl = main_filter_url(['print']);

    $apx->tmpl->assign_static('WEBSITE_NAME', $set['main']['websitename']);
    $apx->tmpl->assign_static('WEBSITE_URL', HTTP);
    $apx->tmpl->assign_static('CURRENT_URL', $currenturl);

    $apx->tmpl->loaddesign('print');
}

//Seite empfehlen
if ('1' == $_REQUEST['tell'] && $set['main']['tell']) {
    $apx->module('main');
    $apx->lang->drop('tell');

    headline($apx->lang->get('HEADLINE'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
    titlebar($apx->lang->get('HEADLINE'));

    if ($_POST['send']) {
        //Captcha prüfen
        if ($set['main']['tellcaptcha'] && !$user->info['userid']) {
            require BASEDIR.'lib/class.captcha.php';
            $captcha = new captcha();
            $captchafailed = $captcha->check();
        }

        if ($captchafailed) {
            message($apx->lang->get('MSG_WRONGCODE'), 'javascript:history.back()');
        } elseif (!$_POST['username'] || !$_POST['email'] || !$_POST['toemail'] || !$_POST['subject'] || !$_POST['text']) {
            message('back');
        } elseif (!checkmail($_POST['email']) || !checkmail($_POST['toemail'])) {
            message($apx->lang->get('MSG_MAILNOTVALID'), 'back');
        } else {
            //Captcha löschen
            if ($set['main']['tellcaptcha'] && !$user->info['userid']) {
                $captcha->remove();
            }

            mail($_POST['toemail'], $_POST['subject'], $_POST['text'], 'From: '.$_POST['username'].'<'.$_POST['email'].'>');
            message($apx->lang->get('MSG_OK'), main_filter_url(['tell']));
        }

        //SCRIPT BEENDEN
        require BASEDIR.'lib/_end.php';
    }

    //Captcha erstellen
    if ($set['main']['tellcaptcha'] && !$user->info['userid']) {
        require BASEDIR.'lib/class.captcha.php';
        $captcha = new captcha();
        $captchacode = $captcha->generate();
    }

    $url = main_filter_url(['tell']);
    $apx->tmpl->assign('POSTTO', $postto);
    $apx->tmpl->assign('TITLE', compatible_hsc($apx->lang->get('MAIL_TELL_TITLE')));
    $apx->tmpl->assign('TEXT', compatible_hsc($apx->lang->get('MAIL_TELL_TEXT', ['URL' => $url])));
    $apx->tmpl->assign('CAPTCHA', $captchacode);

    $apx->tmpl->parse('tell');

    require BASEDIR.'lib/_end.php';
}
