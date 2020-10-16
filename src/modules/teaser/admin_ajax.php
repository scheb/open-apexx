<?php 

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
