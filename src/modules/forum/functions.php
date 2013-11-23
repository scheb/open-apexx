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


//Foren, die der Besucher auslesen darf
function forum_allowed_forums($inforumid=array(),$notforumid=array()) {
	static $readable;
	require_once(BASEDIR.getmodulepath('forum').'basics.php');
	
	//Erlaubte Foren auslesen
	if ( !isset($readable) ) {
		$readable_info=forum_get_readable();
		$readable=get_ids($readable_info,'forumid');
	}
	$ids=$readable;
	
	//Gewünschte Foren ermitteln
	if ( is_array($inforumid) && count($inforumid) ) $ids=array_intersect($ids,$inforumid);
	if ( is_array($notforumid) && count($notforumid) ) $ids=array_diff($ids,$notforumid);
	
	return $ids;
}



//Rekursive Funktion zum Auslesen der Forenstruktur
function forum_get_readable($maxlevel=999999999,$parents='|',$inherit=array(),$level=1) {
	global $set,$db,$apx,$inheritfields;
	$forumlist=array();
	
	$data=$db->fetch("SELECT forumid,inherit,moderator,right_read,forumid AS password_fromid,children FROM ".PRE."_forums WHERE parents='".addslashes($parents)."' ORDER BY ord ASC");
	if ( !count($data) ) return array();
	
	//Für jedes Forum Unterforen auslesen
	foreach ( $data AS $res ) {
		$res['level']=$level;
		$res['moderator']=dash_unserialize($res['moderator']);
		if ( $res['inherit'] ) $res=array_merge($res,$inherit); //Rechte erben
		$foruminfo=$res;
		
		//Rechte prüfen
		if ( !forum_access_read($res) ) {
			$foruminfo['forumid']=0;
		}
		
		$forumlist[]=$foruminfo;
		
		//Vererbbare Rechte nach unten weitergeben
		$handdown['right_read']=$res['right_read'];
		
		//Unterforen auslesen
		if ( $level<$maxlevel && $res['children'] && $res['children']!='|' ) {
			$forumlist=array_merge($forumlist,forum_get_readable($maxlevel,$parents.$res['forumid'].'|',$handdown,$level+1));
		}
	}
	
	return $forumlist;
}

?>