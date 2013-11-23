<?php

$apx->module('forum'); //Diese Aktion gehört dem Forum
$_REQUEST['id']=(int)$_REQUEST['id'];
if ( !$_REQUEST['id'] ) die('missing ID!');
$apx->lang->drop('subscribe');

if ( $_POST['send'] ) {
	
	//Auf Rechte prüfen
	require_once(BASEDIR.getmodulepath('forum').'basics.php');
	if ( $_REQUEST['option']=='addforum' ) {
		$foruminfo=forum_info($_REQUEST['id']);
		if ( !$foruminfo['forumid'] ) die('forum does not exist!');
		if ( !forum_access_read($foruminfo) ) die('access denied!');
		if ( $foruminfo['iscat'] ) die('access denied!');
	}
	else {
		$threadinfo=$db->first("SELECT threadid,forumid FROM ".PRE."_forum_threads WHERE threadid='".$_REQUEST['id']."' LIMIT 1");
		if ( !$threadinfo['threadid'] ) die('thread does not exist!');
		$foruminfo=forum_info($threadinfo['forumid']);
		if ( !$foruminfo['forumid'] ) die('forum does not exist!');
		if ( !forum_access_read($foruminfo) ) die('access denied!');
	}
	
	//Abo-Typ
	if ( $_REQUEST['option']=='addforum' ) $_POST['type']='forum';
	else $_POST['type']='thread'; 
	
	//Benachrichtigung
	if ( in_array($_POST['subscription'],array('none','instant','daily','weekly')) ) $_POST['notification']=$_POST['subscription'];
	else $_POST['notification']='none';
	
	$_POST['userid']=$user->info['userid'];
	$_POST['source']=$_POST['id'];
	
	//Duplikate vermeiden
	list($duplicate)=$db->first("SELECT id FROM ".PRE."_forum_subscriptions WHERE ( type='".addslashes($_POST['type'])."' AND source='".$_REQUEST['id']."' AND userid='".$user->info['userid']."' ) LIMIT 1");
	if ( !$duplicate ) { 
		$db->dinsert(PRE.'_forum_subscriptions','userid,type,source,notification');
	}
	message($apx->lang->get('MSG_SUBADD_OK'),mklink('user.php?action=subscriptions','user,subscriptions.html'));
}
else {
	require_once(BASEDIR.getmodulepath('forum').'basics.php');
	
	//Titel auslesen
	if ( $_REQUEST['option']=='addforum' ) {
		list($title)=$db->first("SELECT title FROM ".PRE."_forums WHERE forumid='".$_REQUEST['id']."' LIMIT 1");
	}
	else {
		list($prefix,$title)=$db->first("SELECT prefix,title FROM ".PRE."_forum_threads WHERE threadid='".$_REQUEST['id']."' LIMIT 1");
		$title = trim(compatible_hsc(strip_tags(forum_get_prefix($prefix).' ').$title));
	}
	
	$input=array(
		'ID' => $_REQUEST['id'],
		'TITLE' => $title,
		'OPTION' => $_REQUEST['option']
	);
	tmessage('subscription_add',$input);
}

?>