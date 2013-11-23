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


# Guestbook Class
# ===============

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Statistik anzeigen
function guestbook_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'guestbook');
	
	$apx->lang->drop('func_stats', 'guestbook');
	
	if ( in_array('COUNT_GUESTBOOK', $parse) ) {
		list($count) = $db->first("
			SELECT count(id) FROM ".PRE."_guestbook
			WHERE active=1
		");
		$tmpl->assign('COUNT_GUESTBOOK', $count);
	}
	
	$tmpl->parse('functions/'.$template,'guestbook');
}


?>