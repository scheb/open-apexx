<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

////////////////////////////////////////////////////////////////////////////////////////////


//Kommentar senden
if ( $_POST['sendcom'] ) {
	require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
	$coms=new comments($_POST['module'],$_POST['mid']);
	$coms->addcom();
}



?>