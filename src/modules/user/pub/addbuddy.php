<?php

$_REQUEST['id'] = (int) $_REQUEST['id'];
if (!$_REQUEST['id']) {
    die('missing ID!');
}
$apx->lang->drop('addbuddy');

//BESTÄTIGUNG
if ($_REQUEST['id'] && $_REQUEST['verify']) {
    $authid = md5($_REQUEST['id'].$set['main']['crypt'].$user->info['userid']);
    if ($_REQUEST['id'] == $user->info['userid']) {
        message($apx->lang->get('MSG_NOTSELFCONFIRM'));
    } elseif ($authid != $_REQUEST['verify']) {
        message($apx->lang->get('MSG_INVALIDKEY'));
    } else {
        //Benachrichtigung?
        list($inlist) = $db->first('SELECT friendid FROM '.PRE."_user_friends WHERE userid='".$_REQUEST['id']."' AND friendid='".$user->info['userid']."' LIMIT 1");
        if (!$inlist) {
            $userinfo = $db->first('SELECT userid,username,email,pub_mailpm,pub_poppm FROM '.PRE."_user WHERE userid='".$_REQUEST['id']."' LIMIT 1");

            //Nachricht verschicken
            $touser = $userinfo['userid'];
            $pop = $userinfo['pub_poppm'];
            $subject = $apx->lang->get('MAIL_FINISHED_TITLE');
            $text = $apx->lang->get('MAIL_FINISHED_TEXT', [
                'USERNAME' => $userinfo['username'],
                'SENDER' => $user->info['username'],
                'WEBSITE' => $set['main']['websitename'],
            ]);
            $db->query('INSERT INTO '.PRE."_user_pms (fromuser,touser,subject,text,time,addsig) VALUES ('".$user->info['userid']."','".$touser."','".addslashes($subject)."','".addslashes($text)."','".time()."','0')");
            if ($pop) {
                $db->query('UPDATE '.PRE."_user SET pmpopup='1' WHERE userid='".$touser."' LIMIT 1");
            }

            //Mail verschicken
            if ($userinfo['pub_mailpm']) {
                $inboxlink = HTTP_HOST.mklink('user.php?action=pms', 'user,pms.html');
                $input = [
                    'USERNAME' => $userinfo['username'],
                    'SENDER' => $user->info['username'],
                    'WEBSITE' => $set['main']['websitename'],
                ];
                sendmail($userinfo['email'], 'FINISHED', $input);
            }
        }

        //In die Liste eintragen
        $db->query('INSERT IGNORE INTO '.PRE."_user_friends VALUES ('".$_REQUEST['id']."','".$user->info['userid']."')");
        $db->query('INSERT IGNORE INTO '.PRE."_user_friends VALUES ('".$user->info['userid']."','".$_REQUEST['id']."')");

        message($apx->lang->get('MSG_OK'), mklink(
            'user.php?action=friends',
            'user,friends.html'
        ));
    }
}

//ANFRAGE VERSCHICKEN
elseif ($_POST['send']) {
    //Benutzer befindet sich bereits auf der Buddyliste?
    if ($user->is_buddy($_REQUEST['id'])) {
        message($apx->lang->get('MSG_EXISTS'), 'back');
    }

    //Benutzer wird ignoriert
    if ($user->ignore($_REQUEST['id'], $reason)) {
        if ($reason) {
            message($apx->lang->get('MSG_IGNORED_REASON', ['REASON' => $reason]), 'javascript:history.back()');
        } else {
            message($apx->lang->get('MSG_IGNORED'), 'javascript:history.back()');
        }
    }

    //User suchen
    $userinfo = $db->first('SELECT userid,username,email,pub_mailpm,pub_poppm FROM '.PRE."_user WHERE userid='".$_REQUEST['id']."' LIMIT 1");
    if (!$userinfo['userid']) {
        message($apx->lang->get('MSG_NOTEXISTS'), 'back');
    } elseif ($userinfo['userid'] == $user->info['userid']) {
        message($apx->lang->get('MSG_NOTSELF'), 'back');
    } else {
        $authid = md5($user->info['userid'].$set['main']['crypt'].$_REQUEST['id']);
        $authlink = HTTP_HOST.mklink(
            'user.php?action=addbuddy&id='.$user->info['userid'].'&verify='.$authid,
            'user,addbuddy,'.$user->info['userid'].'.html?verify='.$authid
        );

        //Nachricht verschicken
        $touser = $userinfo['userid'];
        $pop = $userinfo['pub_poppm'];
        $subject = $apx->lang->get('PM_TITLE');
        $text = $apx->lang->get('PM_TEXT', [
            'USERNAME' => $userinfo['username'],
            'SENDER' => $user->info['username'],
            'WEBSITE' => $set['main']['websitename'],
            'URL' => $authlink,
        ]);
        $db->query('INSERT INTO '.PRE."_user_pms (fromuser,touser,subject,text,time,addsig) VALUES ('".$user->info['userid']."','".$touser."','".addslashes($subject)."','".addslashes($text)."','".time()."','0')");
        if ($pop) {
            $db->query('UPDATE '.PRE."_user SET pmpopup='1' WHERE userid='".$touser."' LIMIT 1");
        }

        //Mail verschicken
        if ($userinfo['pub_mailpm']) {
            $inboxlink = HTTP_HOST.mklink('user.php?action=pms', 'user,pms.html');
            $input = [
                'USERNAME' => $userinfo['username'],
                'SENDER' => $user->info['username'],
                'WEBSITE' => $set['main']['websitename'],
                'INBOX' => $inboxlink,
            ];
            sendmail($userinfo['email'], 'NEWPM', $input);
        }

        message($apx->lang->get('MSG_REQ_OK'), mklink(
            'user.php?action=pms&amp;dir=out',
            'user,pms,out.html'
        ));
    }
}

//START
else {
    //Benutzer befindet sich bereits auf der Buddyliste?
    if ($user->is_buddy($_REQUEST['id'])) {
        message($apx->lang->get('MSG_EXISTS'), 'back');
    }

    //Benutzer will sich selbst adden
    elseif ($_REQUEST['id'] == $user->info['userid']) {
        message($apx->lang->get('MSG_NOTSELF'), 'back');
    }

    //Benutzer wird ignoriert
    elseif ($user->ignore($_REQUEST['id'], $reason)) {
        if ($reason) {
            message($apx->lang->get('MSG_IGNORED_REASON', ['REASON' => $reason]), 'javascript:history.back()');
        } else {
            message($apx->lang->get('MSG_IGNORED'), 'javascript:history.back()');
        }
    }

    //Bis hier ok
    else {
        tmessage('addbuddy', ['ID' => $_REQUEST['id']]);
    }
}
