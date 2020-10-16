<?php

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_start.php';  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->module('news');
$apx->lang->drop('send');
headline($apx->lang->get('HEADLINE'), mklink('sendnews.php', 'sendnews.html'));
titlebar($apx->lang->get('HEADLINE'));

////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($_POST['send']) {
    list($spam) = $db->first('SELECT addtime FROM '.PRE."_news WHERE send_ip='".get_remoteaddr()."' ORDER BY addtime DESC");

    //Captcha prüfen
    if ($set['news']['captcha'] && !$user->info['userid']) {
        require BASEDIR.'lib/class.captcha.php';
        $captcha = new captcha();
        $captchafailed = $captcha->check();
    }

    if ($captchafailed) {
        message($apx->lang->get('MSG_WRONGCODE'), 'javascript:history.back()');
    } elseif ((!$_POST['send_username'] && !$user->info['userid']) || !$_POST['catid'] || !$_POST['title'] || !$_POST['text'] || (($_POST['source_title'] || $_POST['source_url']) && (!$_POST['source_title'] || !$_POST['source_url']))) {
        message('back');
    } elseif (($spam + $set['news']['spamprot'] * 60) > time()) {
        message($apx->lang->get('MSG_BLOCKSPAM', ['SEC' => ($spam + $set['news']['spamprot'] * 60) - time()]), 'back');
    } else {
        if ($user->info['userid']) {
            $_POST['userid'] = $user->info['userid'];
            $_POST['send_username'] = $_POST['send_email'] = '';
        } else {
            $_POST['userid'] = 0;
        }

        $_POST['addtime'] = time();
        $_POST['send_ip'] = get_remoteaddr();
        $_POST['secid'] = 'all';
        $_POST['text'] = strtr(strip_tags($_POST['text']), [
            "\r\n" => "<br />\r\n",
            "\n" => "<br />\n",
        ]);
        $links = [];
        if ($_POST['source_title'] && $_POST['source_url']) {
            $links[] = [
                'title' => $apx->lang->get('SOURCE'),
                'text' => $_POST['source_title'],
                'url' => $_POST['source_url'],
                'popup' => 1,
            ];
        }
        $_POST['links'] = serialize($links);

        //eMail-Benachrichtigung
        if ($set['news']['mailonnew']) {
            $input = ['URL' => HTTP];
            sendmail($set['news']['mailonnew'], 'SENDNEWS', $input);
        }

        //Captcha löschen
        if ($set['news']['captcha'] && !$user->info['userid']) {
            $captcha->remove();
        }

        $db->dinsert(PRE.'_news', 'userid,secid,send_username,send_email,send_ip,catid,title,subtitle,text,links,addtime'.iif($set['news']['teaser'], ',teaser'));
        message($apx->lang->get('MSG_OK'), mklink('news.php', 'news.html'));
    }

    //SCRIPT BEENDEN
    require 'lib/_end.php';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($set['news']['subcats']) {
    require_once BASEDIR.'lib/class.recursivetree.php';
    $tree = new RecursiveTree(PRE.'_news_cat', 'id');
    $data = $tree->getTree(['title', 'open']);
} else {
    $data = $db->fetch('SELECT * FROM '.PRE.'_news_cat ORDER BY title ASC');
}
if (count($data)) {
    foreach ($data as $res) {
        ++$i;
        $catdata[$i]['ID'] = $res['id'];
        $catdata[$i]['TITLE'] = $res['title'];
        $catdata[$i]['LEVEL'] = $res['level'];
        $catdata[$i]['OPEN'] = $res['open'];
    }
}

//Captcha erstellen
if ($set['news']['captcha'] && !$user->info['userid']) {
    require BASEDIR.'lib/class.captcha.php';
    $captcha = new captcha();
    $captchacode = $captcha->generate();
}

$posto = mklink('sendnews.php', 'sendnews.html');

$apx->tmpl->assign('CAPTCHA', $captchacode);
$apx->tmpl->assign('CATEGORY', $catdata);
$apx->tmpl->assign('POSTTO', $postto);
$apx->tmpl->parse('send');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
