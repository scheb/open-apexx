<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2009, Christian Scheb            |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| THIS SOFTWARE IS NOT FREE! MAKE SURE YOU OWN A VALID LICENSE! |
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/



//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



function search_user($items,$conn) {
	global $set,$db,$apx,$user;
	
	//Suchstring generieren
	foreach ( $items AS $item ) {
		$search[]="username LIKE '%".addslashes_like($item)."%'";
	}
	
	//Ergebnisse
	$data=$db->fetch("SELECT userid,username FROM ".PRE."_user WHERE ( ".implode($conn,$search)." ) ORDER BY username ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$result[$i]['TITLE']=$res['username'];
			$result[$i]['LINK']=$user->mkprofile($res['userid'],$res['username']);
		}
	}
	
	return $result;
}

?>