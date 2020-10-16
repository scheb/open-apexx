<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

////////////////////////////////////////////////////////////////////////////////////////////


//Bewertung senden
if ( $_POST['sendrate'] ) {
	require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
	$rate=new ratings($_POST['module'],$_POST['mid']);
	$rate->addrate();
}


?>