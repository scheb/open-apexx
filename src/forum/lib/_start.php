<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

////////////////////////////////////////////////////////////////////////////////////////////////

$apx->module('forum');
$apx->tmpl->loaddesign('forum');
$apx->lang->drop('global');
$now=time();

require_once('lib/functions.php');
require_once(BASEDIR.getmodulepath('forum').'basics.php');


//Forum-Uservars
if ( !$user->info['forum_ppp'] ) $user->info['forum_ppp']=$set['forum']['ppp'];
if ( !$user->info['forum_tpp'] ) $user->info['forum_tpp']=$set['forum']['tpp'];


//Lastonline aktualisieren, wenn angemeldet
if ( $user->info['userid'] ) {
	if ( ($user->info['forum_lastactive']+$set['forum']['timeout']*60)<$now ) {
		$db->query("UPDATE ".PRE."_user SET forum_lastonline=forum_lastactive,forum_lastactive='".$now."' WHERE userid='".$user->info['userid']."' LIMIT 1");
		$user->info['forum_lastonline']=$user->info['forum_lastactive'];
		$user->info['forum_lastactive']=$now;
	}
	else {
		$db->query("UPDATE ".PRE."_user SET forum_lastactive='".$now."' WHERE userid='".$user->info['userid']."' LIMIT 1");
		$user->info['forum_lastactive']=$now;
	}
	setcookie($set['main']['cookie_pre'].'_forum_lastonline',0,time()-100*24*3600);
	setcookie($set['main']['cookie_pre'].'_forum_lastactive',0,time()-100*24*3600);
}

//Lastonline über Cookies, wenn nicht angemeldet
else {
	$user->info['forum_lastactive']=(int)$_COOKIE[$set['main']['cookie_pre'].'_forum_lastactive'];
	$user->info['forum_lastonline']=(int)$_COOKIE[$set['main']['cookie_pre'].'_forum_lastonline'];
	
	if ( ($user->info['forum_lastactive']+$set['forum']['timeout']*60)<$now ) {
		setcookie($set['main']['cookie_pre'].'_forum_lastonline',$user->info['forum_lastactive'],time()+14*24*3600);
		setcookie($set['main']['cookie_pre'].'_forum_lastactive',$now,time()+14*24*3600);
		$user->info['forum_lastonline']=$user->info['forum_lastactive'];
		$user->info['forum_lastactive']=$now;
	}
	else {
		setcookie($set['main']['cookie_pre'].'_forum_lastactive',$now,time()+14*24*3600);
		$user->info['forum_lastactive']=$now;
	}
}

?>