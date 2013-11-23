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



//Eintrag verschoben
function nodemoved() {
	global $apx;
	if ( !checkToken() ) return;
	if ( !$apx->user->has_right('teaser.edit') ) return;
	
	$id = (int)$_REQUEST['id'];
	$beforeid = (int)$_REQUEST['before'];
	$afterid = (int)$_REQUEST['after'];
	if ( !$id || ( !$beforeid && !$afterid ) ) return;
	
	require_once(BASEDIR.'lib/class.orderedlist.php');
	$list = new OrderedList(PRE.'_teaser', 'id');
	
	//Vor einen Knoten verschieben
	if ( $beforeid ) {
		$list->moveBefore($id, $beforeid);
	}
	
	//Nach einen Knoten
	elseif ( $afterid ) {
		$list->moveAfter($id, $afterid);
	}
}


?>
