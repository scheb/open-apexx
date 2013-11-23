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



//Teaser-Link weiterleiten
function misc_teaserlink() {
	global $db;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID');
	
	list($link)=$db->first("SELECT link FROM ".PRE."_teaser WHERE ( '".time()."' BETWEEN starttime AND endtime AND id='".$_REQUEST['id']."' ) LIMIT 1");
	$db->query("UPDATE ".PRE."_teaser SET hits=hits+1 WHERE ( '".time()."' BETWEEN starttime AND endtime AND id='".$_REQUEST['id']."' ) LIMIT 1");
	
	header("HTTP/1.1 301 Moved Permanently");
	header('location:'.$link);
	exit;
}

?>