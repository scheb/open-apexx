<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



require_once(BASEDIR.getmodulepath('content').'functions.php');



function misc_content_comments() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->tmpl->loaddesign('blank');
	content_showcomments($_REQUEST['id']);
}


?>