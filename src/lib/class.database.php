<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//API-Version wählen
if ( $set['mysql_api']=='mysqli' ) {
	require(BASEDIR.'lib/class.database.mysqli.php');
}
else {
	require(BASEDIR.'lib/class.database.mysql.php');
}


?>