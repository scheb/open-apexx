<?php

$_REQUEST['id']=(int)$_REQUEST['id'];
if ( !$_REQUEST['id'] ) die('missing ID!');
$apx->lang->drop('delbuddy');

if ( $_POST['send'] ) {
	if ( $user->is_buddy($_REQUEST['id']) ) {
		$db->query("DELETE FROM ".PRE."_user_friends WHERE userid='".$user->info['userid']."' AND friendid='".$_REQUEST['id']."' LIMIT 1");
	}
	message($apx->lang->get('MSG_OK'),mklink(
		'user.php?action=friends',
		'user,friends.html'
	));
}
else tmessage('delbuddy',array('ID'=>$_REQUEST['id']));

?>