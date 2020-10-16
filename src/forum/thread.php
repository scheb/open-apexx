<?php 

define('APXRUN',true);
define('INFORUM',true);
define('BASEREL','../');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('../lib/_start.php');  ///////////////////////////////////////////////////////// SYSTEMSTART ///
require('lib/_start.php');     /////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->lang->drop('thread');
$apx->lang->drop('editor');

$_REQUEST['id']=(int)$_REQUEST['id'];
$_REQUEST['postid']=(int)$_REQUEST['postid'];
if ( !$_REQUEST['id'] ) die('missing thread-ID!');

$threadinfo=thread_info($_REQUEST['id']);
if ( !$threadinfo['threadid'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
$foruminfo=forum_info($threadinfo['forumid']);
if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
if ( $threadinfo['del'] && !( $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) ) message($apx->lang->get('MSG_THREADNOTEXIST'));
if ( !forum_access_read($foruminfo) ) tmessage('noright',array(),false,false);
check_forum_password($foruminfo);


//Lastvisit für dieses Thema bestimmen
$lastvisit = max(array(
	$user->info['forum_lastonline'],
	thread_readtime($threadinfo['threadid']),
	forum_readtime($foruminfo['forumid'])
));


////////////////////////////////////////////////////////////////////////////////////////////// FORWARDER

//Letzter Beitrag
if ( $_REQUEST['goto']=='lastpost' || $_REQUEST['goto']=='firstunread' ) {
	list($count)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE ( threadid='".$threadinfo['threadid']."' )");
	
	if ( $_REQUEST['goto']=='lastpost' ) {
		$page=ceil($count/$user->info['forum_ppp']);
		$postid=$threadinfo['lastpost'];
	}
	else {
		list($readcount)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE ( threadid='".$threadinfo['threadid']."' AND time<='".$lastvisit."' )");
		$page=ceil(($readcount+1)/$user->info['forum_ppp']);
		list($postid)=$db->first("SELECT postid FROM ".PRE."_forum_posts WHERE ( threadid='".$threadinfo['threadid']."' ) ORDER BY time ASC LIMIT ".$readcount.",1");
	}
	
	$link=str_replace('&amp;', '&', mkrellink(
		'thread.php?id='.$threadinfo['threadid'].'&p='.$page.iif($postid,'#p'.$postid),
		'thread,'.$threadinfo['threadid'].','.$page.urlformat($threadinfo['title']).'.html'.iif($postid,'#p'.$postid)
	));
	
	header("HTTP/1.1 301 Moved Permanently");
	header('location:'.$link);
	exit;
}


//Zu einem besimmten Posting gehen (Seite bestimmen)
if ( $_REQUEST['postid'] ) {
	$postinfo=$db->first("SELECT postid,time FROM ".PRE."_forum_posts WHERE ( threadid='".$threadinfo['threadid']."' AND postid='".$_REQUEST['postid']."' ) LIMIT 1");
	if ( $postinfo['postid'] ) {
		list($precount)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE ( threadid='".$threadinfo['threadid']."' AND time<='".$postinfo['time']."' )");
		$page=ceil($precount/$user->info['forum_ppp']);
		
		$link=str_replace('&amp;', '&', mkrellink(
			'thread.php?id='.$threadinfo['threadid'].'&p='.$page.'#p'.$postinfo['postid'],
			'thread,'.$threadinfo['threadid'].','.$page.urlformat($threadinfo['title']).'.html#p'.$postinfo['postid']
		));
		
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.$link);
		exit;
	}
}



///////////////////////////////////////////////////////////////////////////////////////////////// TPP SETZEN

if ( intval($_POST['ppp']) && $user->info['userid'] ) {
	$db->query("UPDATE ".PRE."_user SET forum_ppp='".intval($_POST['ppp'])."' WHERE userid='".$user->info['userid']."' LIMIT 1");
	$user->info['forum_ppp']=intval($_POST['ppp']);
}



///////////////////////////////////////////////////////////////////////////////////////// THEMA ANZEIGEN

require_once(BASEDIR.'lib/class.mediamanager.php');
$mm=new mediamanager;


//Views eins hochzählen
$db->query("UPDATE ".PRE."_forum_threads SET views=views+1 WHERE threadid='".$threadinfo['threadid']."' LIMIT 1");


//Seitenzahlen
list($count)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE ( threadid='".$threadinfo['threadid']."' )");
pages(
	mkrellink(
		'thread.php?id='.$threadinfo['threadid'].iif($_REQUEST['highlight'],'&amp;highlight='.urlencode($_REQUEST['highlight'])),
		'thread,'.$threadinfo['threadid'].',{P}'.urlformat($threadinfo['title']).'.html'.iif($_REQUEST['highlight'],'?highlight='.urlencode($_REQUEST['highlight']))
	),
	$count,
	$user->info['forum_ppp']
);

//Thema als gelesen markieren (=auf der letzten Seite gewesen und es gibt neue Beiträge)
if ( $_REQUEST['p']==ceil($count/$user->info['forum_ppp']) && $threadinfo['lastposttime']>$lastvisit ) {
	thread_isread($threadinfo['threadid']);
}

//Beiträge auslesen
$data=$db->fetch("SELECT * FROM ".PRE."_forum_posts WHERE ( threadid='".$threadinfo['threadid']."' ) ORDER BY time ASC ".getlimit($user->info['forum_ppp']));

//Anhänge auslesen
$postids = get_ids($data,'postid');
$attinfo = array();
$attimage = array();
if ( count($postids) ) {
	$attdata=$db->fetch("SELECT id,postid,hash,file,thumbnail,name,size FROM ".PRE."_forum_attachments WHERE postid IN (".implode(',',$postids).") ORDER BY name ASC");
	if ( count($attdata) ) {
		$typeinfo=array();
		$icondata=$db->fetch("SELECT ext,icon FROM ".PRE."_forum_filetypes ORDER BY ext ASC");
		if ( count($icondata) ) {
			foreach ( $icondata AS $res ) {
				$typeicon[$res['ext']]=$res['icon'];
			}
		}
		foreach ( $attdata AS $res ) {
			if ( $res['thumbnail'] ) {
				$attimage[$res['postid']][]=array_merge(
					$res,
					array('icon' => $typeicon[strtolower($mm->getext($res['name']))])
				);
			}
			else {
				$attinfo[$res['postid']][]=array_merge(
					$res,
					array('icon' => $typeicon[strtolower($mm->getext($res['name']))])
				);
			}
		}
	}
}


//Userinfo auslesen
$userids=get_ids($data,'userid');
$userinfo=array();
if ( count($userids) ) {
	$userdata=$db->fetch("SELECT a.userid,a.groupid,a.reg_time,a.forum_posts,a.avatar,a.avatar_title,a.signature,a.homepage,a.city,a.icq,a.aim,a.yim,a.msn,a.skype,a.forum_lastactive,a.pub_invisible,a.custom1,a.custom2,a.custom3,a.custom4,a.custom5,a.custom6,a.custom7,a.custom8,a.custom9,a.custom10,b.gtype FROM ".PRE."_user AS a LEFT JOIN ".PRE."_user_groups AS b USING(groupid) WHERE a.userid IN (".implode(',',$userids).")");
	if ( count($userdata) ) {
		foreach ( $userdata AS $res ) {
			$userinfo[$res['userid']]=$res;
		}
	}
}


//Postings auflisten
if ( count($data) ) {
	$i=0;
	foreach ( $data AS $res ) {
		++$i;
		$userdat=&$userinfo[$res['userid']];
		
		//Text + Titel
		$title=replace($res['title']);
		$text=forum_replace($res['text'],$res['allowcodes'],$res['allowsmilies']);
		if ( $_REQUEST['highlight'] ) {
			$title=text_highlight($title);
			$text=text_highlight($text);
		}
		
		//Benutzerkennzeichen
		$signature=$avatar=$avatar_title='';
		if ( $res['userid'] ) {
			if ( $res['allowsig'] ) $signature=$user->mksig($userdat);
			if ( $userdat['avatar'] ) {
				$avatar=$user->mkavatar($userdat);
				$avatar_title=$user->mkavtitle($userdat);
			}
		}
		
		//Rang
		$rankinfo = get_rank($userdat);
		
		$postdata[$i]['ID']=$res['postid'];
		$postdata[$i]['TITLE']=$title;
		$postdata[$i]['TEXT']=$text;
		$postdata[$i]['TIME']=$res['time'];
		$postdata[$i]['USERNAME']=replace($res['username']);
		$postdata[$i]['USERID']=$res['userid'];
		$postdata[$i]['USER_POSTS']=$userdat['forum_posts'];
		$postdata[$i]['USER_REGTIME']=$userdat['reg_time'];
		$postdata[$i]['IP']=$res['ip'];
		$postdata[$i]['HOMEPAGE']=replace($userdat['homepage']);
		$postdata[$i]['AVATAR']=$avatar;
		$postdata[$i]['AVATAR_TITLE']=$avatar_title;
		$postdata[$i]['SIGNATURE']=$signature;
		$postdata[$i]['CITY']=replace($userdat['city']);
		$postdata[$i]['NEW']=iif($res['time']>$lastvisit,1,0);
		$postdata[$i]['ONLINE']=iif(!$userdat['pub_invisible'] && $userdat['forum_lastactive']>time()-$set['user']['timeout']*60,1,0);
		$postdata[$i]['STARTER']=iif($threadinfo['opener_userid'] && $res['userid']==$threadinfo['opener_userid'],1,0);
		$postdata[$i]['RANK']=replace($rankinfo['title']);
		$postdata[$i]['RANK_IMAGE']=$rankinfo['image'];
		$postdata[$i]['RANK_COLOR']='#'.$rankinfo['color'];
		$postdata[$i]['NUMBER']=$i+($_REQUEST['p']-1)*$user->info['forum_ppp'];
		$postdata[$i]['FIRST']=iif($_REQUEST['p']==1 && $i==1,1,0);
		$postdata[$i]['DELETED']=$res['del'];
		
		//Custom-Felder
		for ( $ii=1; $ii<=10; $ii++ ) {
			$postdata[$i]['CUSTOM'.$ii.'_NAME'] = $set['user']['cusfield_names'][($ii-1)];
			$postdata[$i]['CUSTOM'.$ii] = compatible_hsc($userdat['custom'.$ii]);
		}
		
		//Lastedit
		$postdata[$i]['LASTEDIT_TIME']=replace($res['lastedit_time']);
		$postdata[$i]['LASTEDIT_USERNAME']=replace($res['lastedit_by']);
		
		//Anhänge Grafiken
		$imgatttable=array();
		if ( isset($attimage[$res['postid']]) ) {
			foreach ( $attimage[$res['postid']] AS $att ) {
				if ( $att['hash']!=$res['hash'] ) continue; //Nur Anhänge mit gültigem Hash-Wert
				++$ai;
				$imgatttable[$ai]['THUMBNAIL']=HTTPDIR.getpath('uploads').$att['thumbnail'];
				$imgatttable[$ai]['ICON']=$att['icon'];
				$imgatttable[$ai]['NAME']=replace($att['name']);
				$imgatttable[$ai]['LINK']='attachments.php?getid='.$att['id'];
				$imgatttable[$ai]['SIZE']=forum_getsize($att['size']);
			}
		}
		$postdata[$i]['IMAGEATTACHMENT']=$imgatttable;
		
		//Anhänge
		$atttable=array();
		if ( isset($attinfo[$res['postid']]) ) {
			foreach ( $attinfo[$res['postid']] AS $att ) {
				if ( $att['hash']!=$res['hash'] ) continue; //Nur Anhänge mit gültigem Hash-Wert
				++$ai;
				$atttable[$ai]['ICON']=$att['icon'];
				$atttable[$ai]['NAME']=replace($att['name']);
				$atttable[$ai]['LINK']='attachments.php?getid='.$att['id'];
				$atttable[$ai]['SIZE']=forum_getsize($att['size']);
			}
		}
		$postdata[$i]['ATTACHMENT']=$atttable;
		
		//Benutzertypen
		if ( !$res['userid'] ) $postdata[$i]['IS_GUEST']=1;
		elseif ( $userdat['gtype']=='admin' ) $postdata[$i]['IS_ADMIN']=1;
		elseif ( $userdat['gtype']=='indiv' ) $postdata[$i]['IS_TEAM']=1;
		elseif ( in_array($res['userid'],$foruminfo['moderator']) ) $postdata[$i]['IS_MODERATOR']=1;
		
		//Kontakt
		if ( $res['userid'] && $res['userid']!=$user->info['userid'] ) {
			$postdata[$i]['LINK_SENDMAIL']=mklink(
				'user.php?action=newmail&amp;touser='.$res['userid'],
				'user,newmail,'.$res['userid'].'.html'
			);
			$postdata[$i]['LINK_SENDPM']=mklink(
				'user.php?action=newpm&amp;touser='.$res['userid'],
				'user,newpm,'.$res['userid'].'.html'
			);
		}
		$postdata[$i]['CONTACT_ICQ']=replace($userdat['icq']);
		$postdata[$i]['CONTACT_MSN']=replace($userdat['msn']);
		$postdata[$i]['CONTACT_AIM']=replace($userdat['aim']);
		$postdata[$i]['CONTACT_YIM']=replace($userdat['yim']);
		$postdata[$i]['CONTACT_SKYPE']=replace($userdat['skype']);
		
		//Optionen
		if ( $res['userid'] ) {
			$postdata[$i]['LINK_USERPOSTS'] = HTTPDIR.$set['forum']['directory'].'/search.php?send=1&author='.urlencode($res['username']);
			if ( !$user->is_buddy($res['userid']) ) {
				$postdata[$i]['LINK_ADDBUDDY'] = mklink(
					'user.php?action=addbuddy&amp;id='.$res['userid'],
					'user,addbuddy,'.$res['userid'].'.html'
				);
			}
		}
		
		//Rechte
		$postdata[$i]['RIGHT_EDITPOST']=forum_access_editpost($foruminfo,$threadinfo,$res);
		$postdata[$i]['RIGHT_DELPOST']=forum_access_delpost($foruminfo,$threadinfo,$res);
		$postdata[$i]['RIGHT_DELTHREAD']=forum_access_delthread($foruminfo,$threadinfo);
	}
}


//Bewertungen
if ( $apx->is_module('ratings') && $set['forum']['ratings'] ) {
	require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
	$rate=new ratings('forum',$threadinfo['threadid']);
	$rate->assign_ratings();
}

//Optionen-Links
$printlink=mkrellink(
	'thread.php?id='.$threadinfo['threadid'].'&p='.$_REQUEST['p'].'&amp;print=1',
	'thread,'.$threadinfo['threadid'].','.$_REQUEST['p'].urlformat($threadinfo['title']).'.html?print=1'
);
$telllink='tell.php?id='.$threadinfo['threadid'];
if ( $user->info['userid'] && !is_thread_subscr($threadinfo['threadid']) ) {
	$subscribelink=HTTPDIR.mkrellink(
		'user.php?action=subscribe&option=addthread&amp;id='.$threadinfo['threadid'],
		'user,subscribe.html?option=addthread&amp;id='.$threadinfo['threadid']
	);
}

$threadlink=mkrellink(
	'thread.php?id='.$threadinfo['threadid'],
	'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
);

$forumlink=mkrellink(
	'forum.php?id='.$foruminfo['forumid'],
	'forum,'.$foruminfo['forumid'].',1'.urlformat($foruminfo['title']).'.html'
);

$apx->tmpl->assign('POST',$postdata);
$apx->tmpl->assign('CLOSED',!$threadinfo['open']);
$apx->tmpl->assign('THREADID',$threadinfo['threadid']);
$apx->tmpl->assign('THREAD_TITLE',replace($threadinfo['title']));
$apx->tmpl->assign('THREAD_LINK',$threadlink);
$apx->tmpl->assign('THREAD_DELETED',$threadinfo['del']);
$apx->tmpl->assign('FORUMID',$foruminfo['forumid']);
$apx->tmpl->assign('FORUM_TITLE',replace($foruminfo['title']));
$apx->tmpl->assign('FORUM_LINK',$forumlink);
$apx->tmpl->assign('LINK_PRINT',$printlink);
$apx->tmpl->assign('LINK_TELL',$telllink);
$apx->tmpl->assign('LINK_SUBSCRIBE',$subscribelink);
$apx->tmpl->assign('RIGHT_OPEN',forum_access_open($foruminfo));
$apx->tmpl->assign('RIGHT_POST',forum_access_post($foruminfo,$threadinfo));
$apx->tmpl->assign('RIGHT_DELTHREAD',forum_access_delthread($foruminfo,$threadinfo));
$apx->tmpl->assign('POSTSPERPAGE',$user->info['forum_ppp']);
$apx->tmpl->assign('HASH',md5(microtime()));

//Aktivität
forum_activity('forum', $foruminfo['forumid']);
forum_activity('thread', $threadinfo['threadid']);
list($userCount, $guestCount, $activelist) = forum_get_activity('thread', $threadinfo['threadid'], $foruminfo['moderator']);
$apx->tmpl->assign('ACTIVITY_USERS',$userCount);
$apx->tmpl->assign('ACTIVITY_GUESTS',$userCount);
$apx->tmpl->assign('ACTIVITY',$activelist);

$apx->tmpl->assign('LOGGED_IS_ADMIN',iif($user->info['gtype']=='admin',1,0));
$apx->tmpl->assign('LOGGED_IS_MODERATOR',iif(in_array($user->info['userid'],$foruminfo['moderator']),1,0));

//Spezielles Template für Druckansicht
if ( $_REQUEST['print'] ) $apx->tmpl->parse('thread_print');
else $apx->tmpl->parse('thread');

////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->tmpl->assign_static('STYLESHEET',compatible_hsc($foruminfo['stylesheet']));
$apx->tmpl->assign('PATH',forum_path($foruminfo,1));
$apx->tmpl->assign('PATHEND',iif($threadinfo['sticky'],replace($threadinfo['sticky_text']).': ').iif($threadinfo['prefix'], '<span class="thread_prefix">'.forum_get_prefix($threadinfo['prefix']).'</span> ').replace($threadinfo['title']));
$apx->tmpl->assign('PATHEND_LINK', $threadlink);
titlebar($threadinfo['title']);


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');     ///////////////////////////////////////////////////////////////////////////
require('../lib/_end.php');  //////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>