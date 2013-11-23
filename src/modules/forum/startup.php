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


//////////////////////////////////////////////////////////////////////////////////////////// FUNKTIONEN DEFINIEREN


//Parameter aus URL filtern
function forum_filter_url($params=array()) {
	$url=$_SERVER['REQUEST_URI'];
	
	foreach ( $params AS $param ) {
		$url=preg_replace('#\?'.$param.'=(.*)(&|$)#siUe',"'\\2'=='&' ? '?' : ''",$url);
		$url=preg_replace('#\&'.$param.'=(.*)(&|$)#siU','\\2',$url);
	}
	
	return HTTP_HOST.str_replace('&','&amp;',$url);
}


?>