<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


require_once(BASEDIR.getmodulepath('poll').'functions.php');


//Kommentare im Popup
function misc_poll_comments() {
	global $set,$db,$apx,$user;
	$apx->tmpl->loaddesign('blank');
	poll_showcomments($_REQUEST['id']);
}

?>