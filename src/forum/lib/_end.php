<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Eigenes Template für Druckversion
if ( $_REQUEST['print']=='1' ) {
	$apx->tmpl->assign('FORUM_NAME',replace($set['forum']['forumtitle']));
	$apx->tmpl->assign('FORUM_URL',HTTP.$set['forum']['directory'].'/');
	$apx->tmpl->loaddesign('print_forum');
}


list($totalpms)=$db->first("SELECT count(id) FROM ".PRE."_user_pms WHERE ( touser='".$user->info['userid']."' AND del_to='0' )");
list($newpms)=$db->first("SELECT count(id) FROM ".PRE."_user_pms WHERE ( touser='".$user->info['userid']."' AND del_to='0' AND isread='0' )");

//Variablen für Design setzen
$apx->tmpl->assign('LASTVISIT',$user->info['forum_lastonline']);
$apx->tmpl->assign('PMCOUNT',$totalpms);
$apx->tmpl->assign('PMNEW',$newpms);
$apx->tmpl->assign('FORUMTITLE',$set['forum']['forumtitle']);

?>