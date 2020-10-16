<?php

$apx->module('forum'); //Diese Aktion gehört dem Forum
$_REQUEST['id'] = (int) $_REQUEST['id'];
if (!$_REQUEST['id']) {
    die('missing ID!');
}
$apx->lang->drop('subscribe');
$subinfo = $db->first('SELECT type,notification FROM '.PRE."_forum_subscriptions WHERE id='".$_REQUEST['id']."' AND userid='".$user->info['userid']."' LIMIT 1");

if ($_POST['send']) {
    //Benachrichtigung
    if ('thread' == $subinfo['type'] && !in_array($_POST['subscription'], ['none', 'instant', 'daily', 'weekly'])) {
        die('invalid notification type');
    }
    if ('forum' == $subinfo['type'] && !in_array($_POST['subscription'], ['none', 'daily', 'weekly'])) {
        die('invalid notification type');
    }

    $db->query('UPDATE '.PRE."_forum_subscriptions SET notification='".$_POST['subscription']."' WHERE id='".$_REQUEST['id']."' AND userid='".$user->info['userid']."' LIMIT 1");
    message($apx->lang->get('MSG_SUBEDIT_OK'), mklink('user.php?action=subscriptions', 'user,subscriptions.html'));
} else {
    $input = [
        'ID' => $_REQUEST['id'],
        'SUBSCRIPTION' => $subinfo['notification'],
        'ISTHREAD' => iif('thread' == $subinfo['type'], 1, 0),
    ];
    tmessage('subscription_edit', $input);
}
