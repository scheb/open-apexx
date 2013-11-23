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



//Informationen auslesen
function gallery_info($id) {
	global $apx,$db,$set;
	static $cache;
	$id=(int)$id;
	if ( !$id ) return array();
	if ( isset($cache[$id]) ) return $cache[$id];
	
	$cache[$id]=$db->first("SELECT * FROM ".PRE."_gallery WHERE ( id='".$id."' AND '".time()."' BETWEEN starttime AND endtime ) LIMIT 1");
	return $cache[$id];
}

?>