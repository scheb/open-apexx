<?php

$apx->module('forum'); //Diese Aktion gehört dem Forum
$_REQUEST['id']=(int)$_REQUEST['id'];
if ( !$_REQUEST['id'] ) die('missing ID!');
$apx->lang->drop('subscribe');

if ( $_POST['send'] ) {
	$db->query("DELETE FROM ".PRE."_forum_subscriptions WHERE id='".$_REQUEST['id']."' AND userid='".$user->info['userid']."' LIMIT 1");
	message($apx->lang->get('MSG_SUBDEL_OK'),mklink('user.php?action=subscriptions','user,subscriptions.html'));
}
else tmessage('subscription_del',array('ID'=>$_REQUEST['id']));

?>