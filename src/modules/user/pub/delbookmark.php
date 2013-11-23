<?php

$_REQUEST['id']=(int)$_REQUEST['id'];
if ( !$_REQUEST['id'] ) die('missing ID!');
$apx->lang->drop('bookmarks');

if ( $_POST['send'] ) {
	$db->query("DELETE FROM ".PRE."_user_bookmarks WHERE id='".$_REQUEST['id']."' AND userid='".$user->info['userid']."' LIMIT 1");
	message($apx->lang->get('MSG_OK_DEL'),mklink('user.php','user.html'));
}
else tmessage('delbookmark',array('ID'=>$_REQUEST['id']));

?>