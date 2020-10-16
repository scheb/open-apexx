<?php

$_REQUEST['id'] = (int) $_REQUEST['id'];
if (!$_REQUEST['id']) {
    die('missing ID!');
}
$apx->lang->drop('delpm');

if ($_POST['send']) {
    $db->query('UPDATE '.PRE."_user_pms SET del_to='1' WHERE ( id='".$_REQUEST['id']."' AND touser='".$user->info['userid']."' ) LIMIT 1");
    $db->query('UPDATE '.PRE."_user_pms SET del_from='1' WHERE ( id='".$_REQUEST['id']."' AND fromuser='".$user->info['userid']."' ) LIMIT 1");
    message($apx->lang->get('MSG_OK'), mklink('user.php?action=pms', 'user,pms.html'));
} else {
    tmessage('delpm', ['ID' => $_REQUEST['id']]);
}
