<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Kommentar-Seite
function content_showcomments($id) {
	global $set,$db,$apx,$user;
	
	$res=$db->first("SELECT allowcoms FROM ".PRE."_content WHERE ( id='".$id."' AND active='1' ".section_filter()." ) LIMIT 1");
	if ( !$apx->is_module('comments') || !$set['content']['coms'] || !$res['allowcoms'] ) return;
	
	require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
	$coms=new comments('content',$id);
	$coms->assign_comments();
	
	$apx->tmpl->parse('comments','comments');
	require('lib/_end.php');
}


?>