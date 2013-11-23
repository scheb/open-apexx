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


# Comment Class
# =============

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Template-Funktion: Kommentare zählen
function comments_usercount($userid=0,$template='commentcount') {
	global $apx,$db,$set;
	$userid=(int)$userid;
	if ( !$userid ) return;
	
	include(BASEDIR.getmodulepath('comments').'functions.php');
	
	$tmpl=new tengine();
	$apx->lang->drop('commentcount','comments');
	$tmpl->assign('COUNT',comments_count($userid));
	$tmpl->assign('USERID',$userid);
	$tmpl->parse('functions/'.$template,'comments');
}



//Statistik anzeigen
function comments_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'comments');
	$apx->lang->drop('func_stats', 'comments');
	
	if ( in_array('COUNT_COMMENTS', $parse) ) {
		list($count) = $db->first("
			SELECT count(id) FROM ".PRE."_comments
			WHERE active=1
		");
		$tmpl->assign('COUNT_COMMENTS', $count);
	}
	
	$tmpl->parse('functions/'.$template,'comments');
}


?>