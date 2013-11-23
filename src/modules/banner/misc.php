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



//Banner-Capping
function misc_banner_cap() {
	global $set;
	$viewid = $_REQUEST['viewid'];
	if ( $viewid ) {
		setcookie($set['main']['cookie_pre'].'_capping_'.$viewid, intval($_COOKIE[$set['main']['cookie_pre'].'_capping_'.$viewid])+1);
	}
	exit;
}

?>