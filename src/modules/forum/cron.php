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


//Cleanup
function cron_clean($lastexec) {
	global $set,$db,$apx;
	$now = time();
	$db->query("DELETE FROM ".PRE."_forum_activity WHERE time<='".($now-3600)."'"); //1 Stunde
	$db->query("DELETE FROM ".PRE."_forum_search WHERE time<='".($now-24*3600)."'"); //24 Stunden
	
	//Anhänge löschen
	$data = $db->fetch("SELECT file FROM ".PRE."_forum_attachments WHERE postid=0 AND time<='".($now-24*3600)."'");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			if ( file_exists(BASEDIR.getpath('uploads').$res['file']) ) {
				@unlink(BASEDIR.getpath('uploads').$res['file']);
			}
		}
	}
	$db->query("DELETE FROM ".PRE."_forum_attachments WHERE postid=0 AND time<='".($now-24*3600)."'");
}



//THREADS: Sofortige eMail-Benachrichtigung
function cron_subscr_instant($lastexec) {
	global $set,$db,$apx;
	subscr_validate();
	
	$data=$db->fetch("SELECT a.userid,b.threadid,b.title FROM ".PRE."_forum_subscriptions AS a LEFT JOIN ".PRE."_forum_threads AS b ON a.source=b.threadid WHERE ( a.type='thread' AND a.notification='instant' AND NOT ( b.threadid IS NULL ) AND lastposttime>'".$lastexec."' AND NOT ( b.opener_userid=a.userid AND b.posts=1 ) ) ORDER BY lastposttime ASC");
	if ( !count($data) ) return;
	$userinfo=subscr_userinfo(get_ids($data,'userid')); //Benutzer-eMails auslesen
	
	$apx->module('forum');
	$apx->lang->drop('subscr_mail','forum');
	
	//Mailtext erzeugen
	$mailtext=array();
	foreach ( $data AS $res ) {
		$mailtext[$res['userid']].=$res['title']."\n".HTTP.$set['forum']['directory'].'/thread.php?id='.$res['threadid']."&firstunread=1\n\n";
	}
	
	//eMails verschicken	
	foreach ( $mailtext AS $userid => $text ) {
		$insert=array();
		$username=$userinfo[$userid]['username'];
		$email=$userinfo[$userid]['email'];
		if ( !$email ) continue; //Keine eMail gefunden => überspringen
		$insert['USERNAME']=$username;
		$insert['FORUMTITLE']=$set['forum']['forumtitle'];
		$insert['LINKS']=$text;
		sendmail($email,'SUBSCRTHREAD',$insert);
	}
}



//THREADS: Tägliche eMail-Benachrichtigung
function cron_subscr_daily($lastexec) {
	global $set,$db,$apx;
	subscr_validate();
	
	$data=$db->fetch("SELECT a.userid,b.threadid,b.title FROM ".PRE."_forum_subscriptions AS a LEFT JOIN ".PRE."_forum_threads AS b ON a.source=b.threadid WHERE ( a.type='thread' AND a.notification='daily' AND NOT ( b.threadid IS NULL ) AND lastposttime>'".$lastexec."' AND NOT ( b.opener_userid=a.userid AND b.posts=1 ) ) ORDER BY lastposttime ASC");
	if ( !count($data) ) return;
	$userinfo=subscr_userinfo(get_ids($data,'userid')); //Benutzer-eMails auslesen
	
	$apx->module('forum');
	$apx->lang->drop('subscr_mail','forum');
	
	//Mailtext erzeugen
	$mailtext=array();
	foreach ( $data AS $res ) {
		$mailtext[$res['userid']].=$res['title']."\n".HTTP.$set['forum']['directory'].'/thread.php?id='.$res['threadid']."&firstunread=1\n\n";
	}
	
	//eMails verschicken	
	foreach ( $mailtext AS $userid => $text ) {
		$insert=array();
		$username=$userinfo[$userid]['username'];
		$email=$userinfo[$userid]['email'];
		if ( !$email ) continue; //Keine eMail gefunden => überspringen
		$insert['USERNAME']=$username;
		$insert['FORUMTITLE']=$set['forum']['forumtitle'];
		$insert['LINKS']=$text;
		sendmail($email,'SUBSCRTHREAD',$insert);
	}
}



//THREADS: Wöchentliche eMail-Benachrichtigung
function cron_subscr_weekly($lastexec) {
	global $set,$db,$apx;
	subscr_validate();
	
	$data=$db->fetch("SELECT a.userid,b.threadid,b.title FROM ".PRE."_forum_subscriptions AS a LEFT JOIN ".PRE."_forum_threads AS b ON a.source=b.threadid WHERE ( a.type='thread' AND a.notification='weekly' AND NOT ( b.threadid IS NULL ) AND lastposttime>'".$lastexec."' AND NOT ( b.opener_userid=a.userid AND b.posts=1 ) ) ORDER BY lastposttime ASC");
	if ( !count($data) ) return;
	$userinfo=subscr_userinfo(get_ids($data,'userid')); //Benutzer-eMails auslesen
	
	$apx->module('forum');
	$apx->lang->drop('subscr_mail','forum');
	
	//Mailtext erzeugen
	$mailtext=array();
	foreach ( $data AS $res ) {
		$mailtext[$res['userid']].=$res['title']."\n".HTTP.$set['forum']['directory'].'/thread.php?id='.$res['threadid']."&firstunread=1\n\n";
	}
	
	//eMails verschicken	
	foreach ( $mailtext AS $userid => $text ) {
		$insert=array();
		$username=$userinfo[$userid]['username'];
		$email=$userinfo[$userid]['email'];
		if ( !$email ) continue; //Keine eMail gefunden => überspringen
		$insert['USERNAME']=$username;
		$insert['FORUMTITLE']=$set['forum']['forumtitle'];
		$insert['LINKS']=$text;
		sendmail($email,'SUBSCRTHREAD',$insert);
	}
}



//FORUM: Tägliche eMail-Benachrichtigung
function cron_subscr_forum_daily($lastexec) {
	global $set,$db,$apx;
	subscr_validate();
	
	//Abonnierte Foren auslesen
	$userabo=array();
	$forumreadout=array();
	$data=$db->fetch("SELECT userid,source FROM ".PRE."_forum_subscriptions WHERE ( type='forum' AND notification='daily' )");
	if ( !count($data) ) return;
	foreach ( $data AS $res ) {
		$userabo[$res['userid']][]=$res['source'];
		$forumreadout[]=$res['source'];
	}
	$forumreadout=array_unique($forumreadout);
	$userinfo=subscr_userinfo(get_ids($data,'userid')); //Benutzer-eMails auslesen
	
	//Aktualisierte Themen auslesen
	$data=$db->fetch("SELECT forumid,threadid,title FROM ".PRE."_forum_threads WHERE ( forumid IN (".implode(',',$forumreadout).") AND lastposttime>'".$lastexec."') ORDER BY lastposttime ASC");
	if ( !count($data) ) return;
	
	$apx->module('forum');
	$apx->lang->drop('subscr_mail','forum');
	
	//Mailtext erzeugen
	$mailtext=array();
	foreach ( $data AS $res ) {
		$mailtext[$res['forumid']].=$res['title']."\n".HTTP.$set['forum']['directory'].'/thread.php?id='.$res['threadid']."&firstunread=1\n\n";
	}
	
	//Texte zusammenführen und abschicken
	foreach ( $userabo AS $userid => $abos ) {
		$text='';
		foreach ( $abos AS $sourceid ) {
			$text.=$mailtext[$sourceid];
		}
		$username=$userinfo[$userid]['username'];
		$email=$userinfo[$userid]['email'];
		$insert['USERNAME']=$username;
		$insert['FORUMTITLE']=$set['forum']['forumtitle'];
		$insert['LINKS']=$text;
		sendmail($email,'SUBSCRFORUM',$insert);
	}
}



//FORUM: Tägliche eMail-Benachrichtigung
function cron_subscr_forum_weekly($lastexec) {
	global $set,$db,$apx;
	subscr_validate();
	
	//Abonnierte Foren auslesen
	$userabo=array();
	$forumreadout=array();
	$data=$db->fetch("SELECT userid,source FROM ".PRE."_forum_subscriptions WHERE ( type='forum' AND notification='weekly' )");
	if ( !count($data) ) return;
	foreach ( $data AS $res ) {
		$userabo[$res['userid']][]=$res['source'];
		$forumreadout[]=$res['source'];
	}
	$forumreadout=array_unique($forumreadout);
	$userinfo=subscr_userinfo(get_ids($data,'userid')); //Benutzer-eMails auslesen
	
	//Aktualisierte Themen auslesen
	$data=$db->fetch("SELECT forumid,threadid,title FROM ".PRE."_forum_threads WHERE ( forumid IN (".implode(',',$forumreadout).") AND lastposttime>'".$lastexec."') ORDER BY lastposttime ASC");
	if ( !count($data) ) return;
	
	$apx->module('forum');
	$apx->lang->drop('subscr_mail','forum');
	
	//Mailtext erzeugen
	$mailtext=array();
	foreach ( $data AS $res ) {
		$mailtext[$res['forumid']].=$res['title']."\n".HTTP.$set['forum']['directory'].'/thread.php?id='.$res['threadid']."&firstunread=1\n\n";
	}
	
	//Texte zusammenführen und abschicken
	foreach ( $userabo AS $userid => $abos ) {
		$text='';
		foreach ( $abos AS $sourceid ) {
			$text.=$mailtext[$sourceid];
		}
		$username=$userinfo[$userid]['username'];
		$email=$userinfo[$userid]['email'];
		$insert['USERNAME']=$username;
		$insert['FORUMTITLE']=$set['forum']['forumtitle'];
		$insert['LINKS']=$text;
		sendmail($email,'SUBSCRFORUM',$insert);
	}
}



///////////////////////////////////////////////////////////////////////////////////////////////

require_once(BASEDIR.getmodulepath('forum').'basics.php');


//Foren auslesen
function forumcron_get_forum_info($id) {
	static $cache;
	if ( !isset($cache[$id]) ) {
		$cache[$id] = forum_info($id);
	}
	return $cache[$id];
}



//Alle Abonnements validatieren 
function subscr_validate() {
	static $done;
	if ( isset($done) ) return;
	global $set,$db,$apx;
	
	//Abonnements auslesen
	$data=$db->fetch("SELECT id,userid,type,source FROM ".PRE."_forum_subscriptions");
	$userinfo=subscr_userinfo(get_ids($data,'userid'));
	if ( !is_array($data) ) $data = array();
	
	//Foreninfo auslesen
	/*$foruminfo=array();
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$tree = new RecursiveTree(PRE.'_forums', 'forumid');
	$forum = $tree->getTree(array('*'));
	foreach ( $forum AS $res ) {
		$foruminfo[$res['forumid']]=$res;
	}*/
	
	//Themen auslesen
	$threadtoforum=array();
	$threadids=array();
	foreach ( $data AS $res ) {
		if ( $res['type']=='thread' ) {
			$threadids[]=$res['source'];
		}
	}
	if ( count($threadids) ) {
		$tdata=$db->fetch("SELECT threadid,forumid FROM ".PRE."_forum_threads WHERE threadid IN (".implode(',',$threadids).")");
		if ( count($tdata) ) {
			foreach ( $tdata AS $res ) {
				$threadtoforum[$res['threadid']]=$res['forumid'];
			}
		}
	}
	
	//Alle Abonnements überprüfen
	$delete=array();
	foreach ( $data AS $res ) {
		if ( $res['type']=='forum' ) {
			$forumid=$res['source'];
			$foruminfo = forumcron_get_forum_info($forumid);
			if ( !isset($userinfo[$res['userid']]) || !isset($foruminfo) || !forum_access_read($foruminfo,$userinfo[$res['userid']]) ) {
				$delete[]=$res['id'];
			}
		}
		else {
			$forumid=$threadtoforum[$res['source']];
			$foruminfo = forumcron_get_forum_info($forumid);
			if ( !isset($userinfo[$res['userid']]) || !isset($foruminfo) || !forum_access_read($foruminfo,$userinfo[$res['userid']]) ) {
				$delete[]=$res['id'];
			}
		}
	}
	
	//Löschen
	if ( count($delete) ) {
		$db->query("DELETE FROM ".PRE."_forum_subscriptions WHERE id IN (".implode(',',$delete).")");
	}
	
	$done=true;
}



//eMail-Adressen auslesen
function subscr_userinfo($userids) {
	global $db;
	if ( !is_array($userids) || !count($userids) ) return array();
	
	$data=$db->fetch("SELECT a.userid,a.username,a.email,a.groupid,b.gtype FROM ".PRE."_user AS a LEFT JOIN ".PRE."_user_groups AS b USING(groupid) WHERE a.userid IN (".implode(',',$userids).")");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$userinfo[$res['userid']]=$res;
		}
	}
	
	return $userinfo;
}

?>