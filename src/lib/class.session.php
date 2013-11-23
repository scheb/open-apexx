<?php 


global $set;


//API-Version wählen
if ( $set['session_api']=='db' ) {
	require(BASEDIR.'lib/class.dbsession.php');
}
else {
	require(BASEDIR.'lib/class.phpsession.php');
}


?>