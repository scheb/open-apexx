<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Twitter posten
function cron_twitter($lastexec) {
	if ( version_compare(phpversion(), '5', '<') ) return;
	require_once(dirname(__FILE__).'/cron_real.php');
	cron_twitter_real($lastexec);
}


?>