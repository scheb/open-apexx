<?php

$apx->lang->drop('bookmarks');

if ( $_POST['title'] && $_POST['url'] ) {
	$db->query("INSERT INTO ".PRE."_user_bookmarks (userid,title,url,addtime) VALUES ('".$user->info['userid']."','".addslashes($_POST['title'])."','".addslashes($_REQUEST['url'])."','".time()."')");
	if ( $_GET['url'] ) $goto = $_REQUEST['url'];
	else $goto = mklink('user.php','user.html');
	message($apx->lang->get('MSG_OK_ADD'),$goto);
}
else tmessage('addbookmark',array('URL'=>$_REQUEST['url'],'TITLE'=>$_REQUEST['title']));

?>