<?php

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_start.php';  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once BASEDIR.getmodulepath('calendar').'functions.php';

$apx->module('calendar');
$apx->lang->drop('send');
headline($apx->lang->get('HEADLINE'), mklink('sendevent.php', 'sendevent.html'));
titlebar($apx->lang->get('HEADLINE'));

////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($_POST['send']) {
    list($spam) = $db->first('SELECT addtime FROM '.PRE."_calendar_events WHERE send_ip='".get_remoteaddr()."' ORDER BY addtime DESC");

    //Captcha prüfen
    if ($set['calendar']['captcha'] && !$user->info['userid']) {
        require BASEDIR.'lib/class.captcha.php';
        $captcha = new captcha();
        $captchafailed = $captcha->check();
    }

    if ($captchafailed) {
        message($apx->lang->get('MSG_WRONGCODE'), 'javascript:history.back()');
    } elseif ((!$_POST['send_username'] && !$user->info['userid']) || !$_POST['catid'] || !$_POST['start_day'] || !$_POST['start_month'] || !$_POST['start_year']) {
        message('back');
    } elseif (($spam + 1 * 60) > time()) {
        message($apx->lang->get('MSG_BLOCKSPAM', ['SEC' => ($spam + 1 * 60) - time()]), 'back');
    } else {
        if ($user->info['userid']) {
            $_POST['userid'] = $user->info['userid'];
            $_POST['send_username'] = $_POST['send_email'] = '';
        } else {
            $_POST['userid'] = 0;
        }

        $_POST['addtime'] = time();
        $_POST['send_ip'] = get_remoteaddr();
        $_POST['addtime'] = time();
        $_POST['secid'] = 'all';
        $_POST['startday'] = calendar_generate_stamp($_POST['start_day'], $_POST['start_month'], $_POST['start_year']);
        $_POST['text'] = strtr(strip_tags($_POST['text']), [
            "\r\n" => "<br />\r\n",
            "\n" => "<br />\n",
        ]);

        //Startzeit
        $_POST['starttime'] = -1;
        if ('' !== $_POST['start_hour'] && '' !== $_POST['start_minute']) {
            $_POST['starttime'] = sprintf('%02d%02d', $_POST['start_hour'], $_POST['start_minute']);
        }

        //Termin Ende
        $_POST['endday'] = 0;
        if ('' !== $_POST['end_day'] && '' !== $_POST['end_month'] && '' !== $_POST['end_year']) {
            $_POST['endday'] = calendar_generate_stamp($_POST['end_day'], $_POST['end_month'], $_POST['end_year']);
            $_POST['endtime'] = -1;
            if ('' !== $_POST['end_hour'] && '' !== $_POST['end_minute']) {
                $_POST['endtime'] = sprintf('%02d%02d', $_POST['end_hour'], $_POST['end_minute']);
            }
        } else {
            $_POST['endday'] = $_POST['startday'];
            $_POST['endtime'] = -1;
        }

        //eMail-Benachrichtigung
        if ($set['calendar']['mailonnew']) {
            $input = ['URL' => HTTP];
            sendmail($set['calendar']['mailonnew'], 'SENDEVENT', $input);
        }

        //Captcha löschen
        if ($set['calendar']['captcha'] && !$user->info['userid']) {
            $captcha->remove();
        }

        $db->dinsert(PRE.'_calendar_events', 'userid,secid,send_username,send_email,send_ip,catid,title,text,location,location_link,addtime,startday,starttime,endday,endtime,active');
        message($apx->lang->get('MSG_OK'), mklink('events.php', 'events.html'));
    }

    //SCRIPT BEENDEN
    require 'lib/_end.php';
}

////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($set['calendar']['subcats']) {
    require_once BASEDIR.'lib/class.recursivetree.php';
    $tree = new RecursiveTree(PRE.'_calendar_cat', 'id');
    $data = $tree->getTree(['*']);
} else {
    $data = $db->fetch('SELECT * FROM '.PRE.'_calendar_cat ORDER BY title ASC');
}
if (count($data)) {
    foreach ($data as $cat) {
        ++$i;

        $catdata[$i]['ID'] = $cat['id'];
        $catdata[$i]['TITLE'] = $cat['title'];
        $catdata[$i]['LEVEL'] = $cat['level'];
    }
}

//Captcha erstellen
if ($set['calendar']['captcha'] && !$user->info['userid']) {
    require BASEDIR.'lib/class.captcha.php';
    $captcha = new captcha();
    $captchacode = $captcha->generate();
}

$posto = mklink('sendevent.php', 'sendevent.html');

$apx->tmpl->assign('CAPTCHA', $captchacode);
$apx->tmpl->assign('CATEGORY', $catdata);
$apx->tmpl->assign('POSTTO', $postto);
$apx->tmpl->parse('send');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
