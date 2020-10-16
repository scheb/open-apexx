<?php

define('APXRUN', true);
define('INFORUM', true);
define('BASEREL', '../');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require '../lib/_start.php';  ///////////////////////////////////////////////////////// SYSTEMSTART ///
require 'lib/_start.php';     /////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->lang->drop('tell');

$_REQUEST['id'] = (int) $_REQUEST['id'];
$_REQUEST['postid'] = (int) $_REQUEST['postid'];
if (!$_REQUEST['id']) {
    die('missing thread-ID!');
}

$threadinfo = thread_info($_REQUEST['id']);
if (!$threadinfo['threadid'] || $threadinfo['del']) {
    message($apx->lang->get('MSG_THREADNOTEXIST'));
}
$foruminfo = forum_info($threadinfo['forumid']);
if (!$foruminfo['forumid']) {
    message($apx->lang->get('MSG_FORUMNOTEXIST'));
}
if (!forum_access_read($foruminfo)) {
    tmessage('noright', [], false, false);
}
check_forum_password($foruminfo);

/////////////////////////////////////////////////////////////////////////////////////// Seite empfehlen

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
        $goto = HTTP.$set['forum']['directory'].'/'.mkrellink(
            'thread.php?id='.$threadinfo['threadid'],
            'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
        );

        mail($_POST['toemail'], $_POST['subject'], $_POST['text'], 'From: '.$_POST['username'].'<'.$_POST['email'].'>');
        message($apx->lang->get('MSG_OK'), $goto);
    }
} else {
    $url = HTTP.$set['forum']['directory'].'/'.mkrellink(
        'thread.php?id='.$threadinfo['threadid'],
        'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
    );

    //Captcha erstellen
    if ($set['main']['tellcaptcha'] && !$user->info['userid']) {
        require BASEDIR.'lib/class.captcha.php';
        $captcha = new captcha();
        $captchacode = $captcha->generate();
    }

    $apx->tmpl->assign('POSTTO', $_SERVER['REQUEST_URI']);
    $apx->tmpl->assign('TITLE', trim(compatible_hsc(strip_tags(forum_get_prefix($threadinfo['prefix']).' ').$threadinfo['title'])));
    $apx->tmpl->assign('TEXT', compatible_hsc($apx->lang->get('MAIL_TELL_TEXT', ['URL' => $url])));
    $apx->tmpl->assign('CAPTCHA', $captchacode);

    $apx->tmpl->parse('tell');
}

////////////////////////////////////////////////////////////////////////////////////////////////////////

$threadpath = [[
    'TITLE' => trim(compatible_hsc(strip_tags(forum_get_prefix($threadinfo['prefix']).' ').$threadinfo['title'])),
    'LINK' => mkrellink(
        'thread.php?id='.$threadinfo['threadid'],
        'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
    ),
]];

$apx->tmpl->assign('PATH', array_merge(forum_path($foruminfo, 1), $threadpath));
$apx->tmpl->assign('PATHEND', $apx->lang->get('HEADLINE_TELL'));
titlebar($apx->lang->get('HEADLINE_TELL'));

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';     ///////////////////////////////////////////////////////////////////////////
require '../lib/_end.php';  //////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
