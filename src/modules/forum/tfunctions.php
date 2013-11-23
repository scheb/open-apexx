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



//Neuste Themen
function forum_threads_new($count=5,$inforumid=0,$notforumid=0,$template='new') {
	require_once(BASEDIR.getmodulepath('forum').'functions.php');
	global $set,$apx,$db;
	$count=(int)$count;
	
	//Erlaubte Foren
	if ( is_int($forumid) ) $inforum=array($inforumid);
	else $inforum=intlist($inforumid);
	if ( is_int($notforumid) ) $notforum=array($notforumid);
	else $notforum=intlist($notforumid);
	$forumids=forum_allowed_forums($inforum,$notforum);
	
	//Daten auslesen
	$fields=implode(',',array(
		'threadid',
		'prefix',
		'title',
		'opener_userid',
		'opener',
		'opentime',
		'lastposter_userid',
		'lastposter',
		'lastposttime',
		'posts',
		'views'
	));
	
	if ( count($forumids) ) $data=$db->fetch("SELECT ".$fields." FROM ".PRE."_forum_threads WHERE ( del=0 AND moved=0 AND forumid IN (".implode(',',$forumids).") ) ORDER BY opentime DESC ".iif($count,"LIMIT ".$count));
	else $data=array();
	forum_threads_print($data,$template,'opentime');
}



//Themen mit neuen Beiträgen
function forum_threads_updated($count=5,$inforumid=0,$notforumid=0,$template='updated') {
	require_once(BASEDIR.getmodulepath('forum').'functions.php');
	global $set,$apx,$db;
	$count=(int)$count;
	
	//Erlaubte Foren
	if ( is_int($forumid) ) $inforum=array($inforumid);
	else $inforum=intlist($inforumid);
	if ( is_int($notforumid) ) $notforum=array($notforumid);
	else $notforum=intlist($notforumid);
	$forumids=forum_allowed_forums($inforum,$notforum);
	
	//Daten auslesen
	$fields=implode(',',array(
		'threadid',
		'prefix',
		'title',
		'opener_userid',
		'opener',
		'opentime',
		'lastposter_userid',
		'lastposter',
		'lastposttime',
		'posts',
		'views'
	));
	
	if ( count($forumids) ) $data=$db->fetch("SELECT ".$fields." FROM ".PRE."_forum_threads WHERE ( del=0 AND moved=0 AND forumid IN (".implode(',',$forumids).") ) ORDER BY lastposttime DESC ".iif($count,"LIMIT ".$count));
	else $data=array();
	
	forum_threads_print($data,$template,'lastposttime');
}



//Threads ausgeben
function forum_threads_print($data,$template,$dateheadfield) {
	global $set;
	$tmpl = new tengine;
	
	//Ausgabe
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Verschoben
			if ( $res['moved'] ) $res['threadid']=$res['moved'];
			
			//Link
			$forumdir=$set['forum']['directory'].'/';
			$link=mkrellink(
				HTTPDIR.$forumdir.'thread.php?id='.$res['threadid'],
				HTTPDIR.$forumdir.'thread,'.$res['threadid'].',1'.urlformat($res['title']).'.html'
			);
			
			$tabledata[$i]['ID']=$res['threadid'];
			$tabledata[$i]['PREFIX']=forum_get_prefix($res['prefix']);
			$tabledata[$i]['TITLE']=replace($res['title']);
			$tabledata[$i]['LINK']=$link;
			$tabledata[$i]['OPENER_USERID']=$res['opener_userid'];
			$tabledata[$i]['OPENER_USERNAME']=replace($res['opener']);
			$tabledata[$i]['OPENTIME']=$res['opentime'];
			$tabledata[$i]['LASTPOST_USERID']=$res['lastposter_userid'];
			$tabledata[$i]['LASTPOST_USERNAME']=replace($res['lastposter']);
			$tabledata[$i]['LASTPOST_TIME']=$res['lastposttime'];
			$tabledata[$i]['POSTS']=number_format($res['posts'],0,'','.');
			$tabledata[$i]['VIEWS']=number_format($res['views'],0,'','.');
			
			//Datehead
			if ( $laststamp!=date('Y/m/d',$res[$dateheadfield]-TIMEDIFF) ) {
				$tabledata[$i]['DATEHEAD']=$res[$dateheadfield];
			}
			
			$laststamp=date('Y/m/d',$res[$dateheadfield]-TIMEDIFF); 
		}
	}
	
	$tmpl->assign('THREAD',$tabledata);
	$tmpl->parse('functions/'.$template,'forum');
}



//Statistik anzeigen
function forum_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'forum');
	
	$apx->lang->drop('func_stats', 'forum');
	
	if ( in_array('COUNT_POSTS', $parse) ) {
		list($count)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE del=0");
		$tmpl->assign('COUNT_POSTS', $count);
	}
	if ( in_template(array('COUNT_THREADS', 'AVG_HITS'), $parse) ) {
		list($count, $hits)=$db->first("
			SELECT count(threadid), avg(views) FROM ".PRE."_forum_threads
			WHERE del=0 AND moved=0
		");
		$tmpl->assign('COUNT_THREADS', $count);
		$tmpl->assign('AVG_HITS', round($hits));
	}
	
	$tmpl->parse('functions/'.$template,'forum');
}

?>