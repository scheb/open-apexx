<?php

$apx->lang->drop('newpm');
headline($apx->lang->get('HEADLINE_NEWPM'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_NEWPM'));
$_REQUEST['answer'] = (int) $_REQUEST['answer'];
$_REQUEST['touser'] = (int) $_REQUEST['touser'];

//Eigenen Speicher prüfen
list($pmcount_own) = $db->first('SELECT count(id) FROM '.PRE."_user_pms WHERE ( ( touser='".$user->info['userid']."' AND del_to='0' ) OR ( fromuser='".$user->info['userid']."' AND del_from='0' ) )");
if ($pmcount_own >= $set['user']['maxpmcount']) {
    message($apx->lang->get('MSG_OWNFULL'), 'javascript:history.back()');
    require 'lib/_end.php';
}

if ($_POST['send']) {
    //EmpfängerInfos auslesen
    list($touser, $email, $pop, $mailpm) = $db->first('SELECT userid,email,pub_poppm,pub_mailpm FROM '.PRE."_user WHERE username='".addslashes($_POST['touser'])."' LIMIT 1");

    //Speicher des Empfängers prüfen
    if ($touser) {
        list($pmcount_rec) = $db->first('SELECT count(id) FROM '.PRE."_user_pms WHERE ( ( touser='".$touser."' AND del_to='0' ) OR ( fromuser='".$touser."' AND del_from='0' ) )");
        if ($pmcount_rec >= $set['user']['maxpmcount']) {
            $input['USERNAME'] = $user->info['username'];
            $input['WEBSITE'] = $set['main']['websitename'];
            sendmail($email, 'FULL', $input);
            message($apx->lang->get('MSG_FULL'), 'javascript:history.back()');
            require 'lib/_end.php';
        }
    }

    if (!$_POST['touser'] || !$_POST['subject'] || !$_POST['text']) {
        message('back');
    } elseif (!$touser) {
        message($apx->lang->get('MSG_NOTEXISTS'), 'javascript:history.back()');
    } elseif ($touser == $user->info['userid']) {
        message($apx->lang->get('MSG_SELF'), 'javascript:history.back()');
    } elseif ($user->ignore($touser, $reason)) {
        if ($reason) {
            message($apx->lang->get('MSG_IGNORED_REASON', ['REASON' => $reason]), 'javascript:history.back()');
        } else {
            message($apx->lang->get('MSG_IGNORED'), 'javascript:history.back()');
        }
    } else {
        $db->query('INSERT INTO '.PRE."_user_pms (fromuser,touser,subject,text,time,addsig) VALUES ('".$user->info['userid']."','".$touser."','".addslashes($_POST['subject'])."','".addslashes($_POST['text'])."','".time()."','".intval($_POST['addsig'])."')");
        if ($pop) {
            $db->query('UPDATE '.PRE."_user SET pmpopup='1' WHERE userid='".$touser."' LIMIT 1");
        }

        //eMail-Benachrichtigung bei neuer PM
        if ($mailpm) {
            $text = $_POST['text'];
            if ($set['user']['pm_badwords']) {
                $text = badwords($text);
            }
            $text = replace($text, 1);
            if ($set['user']['pm_allowsmilies']) {
                $text = dbsmilies($text);
            }
            if ($set['user']['pm_allowcode']) {
                $text = dbcodes($text);
            }
            $text = strip_tags($text);
            $inboxlink = HTTP_HOST.mklink('user.php?action=pms', 'user,pms.html');
            $input = [
                'USERNAME' => $user->info['username'],
                'WEBSITE' => $set['main']['websitename'],
                'INBOX' => $inboxlink,
                'SUBJECT' => $_POST['subject'],
                'TEXT' => $text,
            ];
            sendmail($email, 'NEWPM', $input);
        }

        message($apx->lang->get('MSG_OK'), mklink(
            'user.php?action=pms&amp;dir=out',
            'user,pms,out.html'
        ));
    }
} else {
    if ($_POST['preview']) {
        $text = $_POST['text'];
        if ($set['user']['pm_badwords']) {
            $text = badwords($text);
        }
        $text = replace($text, 1);
        if ($set['user']['pm_allowsmilies']) {
            $text = dbsmilies($text);
        }
        if ($set['user']['pm_allowcode']) {
            $text = dbcodes($text);
        }
        $apx->tmpl->assign('PREVIEW', $text);
        $apx->tmpl->assign('USERNAME', compatible_hsc($_POST['touser']));
        $apx->tmpl->assign('SUBJECT', compatible_hsc($_POST['subject']));
        $apx->tmpl->assign('TEXT', compatible_hsc($_POST['text']));
        $apx->tmpl->assign('ADDSIG', intval($_POST['addsig']));
    } else {
        $text = '';
        if ($_REQUEST['answer']) {
            $res = $db->first('SELECT a.subject,a.text,b.userid,b.username,c.username AS username2 FROM '.PRE.'_user_pms AS a LEFT JOIN '.PRE.'_user AS b ON a.fromuser=b.userid LEFT JOIN '.PRE."_user AS c ON a.touser=c.userid WHERE ( a.id='".$_REQUEST['answer']."' AND ( a.touser='".$user->info['userid']."' OR a.fromuser='".$user->info['userid']."' ) )");
            if ($res['userid'] == $user->info['userid']) {
                $username = compatible_hsc($res['username2']);
                $subject = compatible_hsc($res['subject']);
                $text = '[QUOTE]'.compatible_hsc($res['text'])."[/QUOTE]\n";
            } else {
                $username = compatible_hsc($res['username']);
                $subject = iif($res['subject'] && 'Re: ' != substr($res['subject'], 0, 4), 'Re: ').compatible_hsc($res['subject']);
                $text = '[QUOTE]'.compatible_hsc($res['text'])."[/QUOTE]\n";
            }
        } elseif ($_REQUEST['touser']) {
            list($username) = $db->first('SELECT username FROM '.PRE."_user WHERE userid='".$_REQUEST['touser']."' LIMIT 1");
            $username = compatible_hsc($username);
        }

        $apx->tmpl->assign('USERNAME', replace($username));
        $apx->tmpl->assign('SUBJECT', $subject);
        $apx->tmpl->assign('TEXT', $text);
        $apx->tmpl->assign('ADDSIG', 1);
    }

    $postto = mklink(
        'user.php?action=newpm',
        'user,newpm.html'
    );

    $apx->tmpl->assign('POSTTO', $postto);
    $apx->tmpl->parse('newpm');
}
