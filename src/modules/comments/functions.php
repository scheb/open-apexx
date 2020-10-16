<?php 

# Comment Class
# =============

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Kommentare zählen
function comments_count($userid=0) {
	global $apx,$db,$set;
	$userid=(int)$userid;
	if ( !$userid ) return 0;
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( userid='".$userid."' AND active='1' )");
	return $count;
}


?>