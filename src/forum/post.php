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


define('APXRUN',true);
define('INFORUM',true);
define('BASEREL','../');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('../lib/_start.php');  ///////////////////////////////////////////////////////// SYSTEMSTART ///
require('lib/_start.php');     /////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->lang->drop('thread');

$_REQUEST['id']=(int)$_REQUEST['id'];
if ( !$_REQUEST['id'] ) die('missing post-ID!');

$postinfo=post_info($_REQUEST['id']);
if ( !$postinfo['postid'] ) message($apx->lang->get('MSG_POSTNOTEXIST'));
$threadinfo=thread_info($postinfo['threadid']);
if ( !$threadinfo['threadid'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
$foruminfo=forum_info($threadinfo['forumid']);
if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
if ( $threadinfo['del'] && !( $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) ) message($apx->lang->get('MSG_THREADNOTEXIST'));
if ( $postinfo['del'] && !( $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) ) message($apx->lang->get('MSG_POSTNOTEXIST'));
if ( !forum_access_read($foruminfo) ) tmessage('noright',array(),false,false);
check_forum_password($foruminfo);


//Lastvisit für dieses Thema bestimmen
$lastvisit = max(array(
	$user->info['forum_lastonline'],
	thread_readtime($threadinfo['threadid']),
	forum_readtime($foruminfo['forumid'])
));



///////////////////////////////////////////////////////////////////////////////////////// BEITRAG

$res=$postinfo;

require_once(BASEDIR.'lib/class.mediamanager.php');
$mm=new mediamanager;

//Userinfo auslesen
if ( $res['userid'] ) $userdat=$db->first("SELECT a.userid,a.groupid,a.reg_time,a.forum_posts,a.avatar,a.avatar_title,a.signature,a.homepage,a.city,a.icq,a.aim,a.yim,a.msn,a.skype,a.forum_lastactive,a.pub_invisible,a.custom1,a.custom2,a.custom3,a.custom4,a.custom5,a.custom6,a.custom7,a.custom8,a.custom9,a.custom10,b.gtype FROM ".PRE."_user AS a LEFT JOIN ".PRE."_user_groups AS b USING(groupid) WHERE a.userid='".$res['userid']."' LIMIT 1");
else $userdat=array();

$mods=$foruminfo['moderator'];

//Text
$text=forum_replace($postinfo['text'],$postinfo['allowcodes'],$postinfo['allowsmilies']);

//Benutzerkennzeichen
$siganture=$avatar=$avatar_title='';
if ( $postinfo['userid'] ) {
	if ( $postinfo['allowsig'] ) $signature=$user->mksig($userdat);
	if ( $userdat['avatar'] ) {
		$avatar=$user->mkavatar($userdat);
		$avatar_title=$user->mkavtitle($userdat);
	}
}

//Anhänge auslesen
$atttable = array();
$imgatttable = array();
$attdata=$db->fetch("SELECT id,postid,hash,file,thumbnail,name,size FROM ".PRE."_forum_attachments WHERE postid='".$_REQUEST['id']."' ORDER BY name ASC");
if ( count($attdata) ) {
	$typeinfo=array();
	$icondata=$db->fetch("SELECT ext,icon FROM ".PRE."_forum_filetypes ORDER BY ext ASC");
	if ( count($icondata) ) {
		foreach ( $icondata AS $icon ) {
			$typeicon[$icon['ext']]=$icon['icon'];
		}
	}
	foreach ( $attdata AS $att ) {
		if ( $att['hash']!=$res['hash'] ) continue; //Nur Anhänge mit gültigem Hash-Wert
		++$ai;
		if ( $att['thumbnail'] ) {
			$imgatttable[$ai]['THUMBNAIL']=HTTPDIR.getpath('uploads').$att['thumbnail'];
			$imgatttable[$ai]['ICON']=$typeicon[strtolower($mm->getext($att['name']))];
			$imgatttable[$ai]['NAME']=replace($att['name']);
			$imgatttable[$ai]['LINK']='attachments.php?getid='.$att['id'];
			$imgatttable[$ai]['SIZE']=forum_getsize($att['size']);
		}
		else {
			$atttable[$ai]['ICON']=$typeicon[strtolower($mm->getext($att['name']))];
			$atttable[$ai]['NAME']=replace($att['name']);
			$atttable[$ai]['LINK']='attachments.php?getid='.$att['id'];
			$atttable[$ai]['SIZE']=forum_getsize($att['size']);
		}
	}
}

//Rang
$rankinfo = get_rank($userdat);

$apx->tmpl->assign('ID',$postinfo['postid']);
$apx->tmpl->assign('TITLE',replace($postinfo['title']));
$apx->tmpl->assign('TEXT',$text);
$apx->tmpl->assign('TIME',$postinfo['time']);
$apx->tmpl->assign('USERNAME',replace($postinfo['username']));
$apx->tmpl->assign('USERID',$postinfo['userid']);
$apx->tmpl->assign('USER_POSTS',$userdat['forum_posts']);
$apx->tmpl->assign('USER_REGTIME',$userdat['reg_time']);
$apx->tmpl->assign('IP',$postinfo['ip']);
$apx->tmpl->assign('HOMEPAGE',replace($userdat['homepage']));
$apx->tmpl->assign('AVATAR',$avatar);
$apx->tmpl->assign('AVATAR_TITLE',$avatar_title);
$apx->tmpl->assign('SIGNATURE',$signature);
$apx->tmpl->assign('CITY',replace($userdat['city']));
$apx->tmpl->assign('NEW',iif($postinfo['time']>$lastvisit,1,0));
$apx->tmpl->assign('ONLINE',iif(!$userdat['pub_invisible'] && $userdat['forum_lastactive']>time()-$set['user']['timeout']*60,1,0));
$apx->tmpl->assign('STARTER',iif($threadinfo['opener_userid'] && $postinfo['userid']==$threadinfo['opener_userid'],1,0));
$apx->tmpl->assign('RANK',$rankinfo['title']);
$apx->tmpl->assign('RANK_IMAGE',$rankinfo['image']);
$apx->tmpl->assign('RANK_COLOR','#'.$rankinfo['color']);
$apx->tmpl->assign('NUMBER',$i+($_REQUEST['p']-1)*$user->info['forum_ppp']);
$apx->tmpl->assign('FIRST',iif($_REQUEST['p']==1 && $i==1,1,0));
$apx->tmpl->assign('DELETED',$postinfo['del']);

//Custom-Felder
for ( $i=1; $i<=10; $i++ ) {
	$apx->tmpl->assign('CUSTOM'.$i.'_NAME',replace($set['user']['cusfield_names'][($i-1)]));
	$apx->tmpl->assign('CUSTOM'.$i,replace($userdat['custom'.$i]));
}

//Lastedit
$apx->tmpl->assign('LASTEDIT_USERNAME',$res['lastedit_by']);
$apx->tmpl->assign('LASTEDIT_TIME',$res['lastedit_time']);

//Anhänge
$apx->tmpl->assign('IMAGEATTACHMENT',$imgatttable);
$apx->tmpl->assign('ATTACHMENT',$atttable);

//Benutzertypen
if ( !$postinfo['userid'] ) $apx->tmpl->assign('IS_GUEST',1);
elseif ( $userdat['gtype']=='admin' ) $apx->tmpl->assign('IS_ADMIN',1);
elseif ( $userdat['gtype']=='indiv' ) $apx->tmpl->assign('IS_TEAM',1);
elseif ( in_array($postinfo['userid'],$mods) ) $apx->tmpl->assign('IS_MODERATOR',1);

//Kontakt
if ( $res['userid'] && $res['userid']!=$user->info['userid'] ) {
	$apx->tmpl->assign('LINK_SENDMAIL', mklink(
		'user.php?action=newmail&amp;touser='.$res['userid'],
		'user,newmail,'.$res['userid'].'.html'
	));
	$apx->tmpl->assign('LINK_SENDPM', mklink(
		'user.php?action=newpm&amp;touser='.$res['userid'],
		'user,newpm,'.$res['userid'].'.html'
	));
}
$apx->tmpl->assign('CONTACT_ICQ',replace($userdat['icq']));
$apx->tmpl->assign('CONTACT_MSN',replace($userdat['msn']));
$apx->tmpl->assign('CONTACT_AIM',replace($userdat['aim']));
$apx->tmpl->assign('CONTACT_YIM',replace($userdat['yim']));
$apx->tmpl->assign('CONTACT_SKYPE',replace($userdat['skype']));

//Optionen
if ( $res['userid'] ) {
	$apx->tmpl->assign('LINK_USERPOSTS', HTTPDIR.$set['forum']['directory'].'/search.php?send=1&author='.urlencode($res['username']));
	if ( !$user->is_buddy($res['userid']) ) {
		$apx->tmpl->assign('LINK_ADDBUDDY', mklink(
			'user.php?action=addbuddy&amp;id='.$res['userid'],
			'user,addbuddy,'.$res['userid'].'.html'
		));
	}
}

$threadlink=mkrellink(
	'thread.php?id='.$threadinfo['threadid'],
	'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
);

$forumlink=mkrellink(
	'forum.php?id='.$foruminfo['forumid'],
	'forum,'.$foruminfo['forumid'].',1'.urlformat($foruminfo['title']).'.html'
);

$apx->tmpl->assign('THREADID',$threadinfo['threadid']);
$apx->tmpl->assign('THREAD_TITLE',replace($threadinfo['title']));
$apx->tmpl->assign('THREAD_PREFIX',forum_get_prefix($threadinfo['prefix']));
$apx->tmpl->assign('THREAD_LINK',$threadlink);
$apx->tmpl->assign('THREAD_DELETED',$threadinfo['del']);
$apx->tmpl->assign('FORUMID',$foruminfo['forumid']);
$apx->tmpl->assign('FORUM_TITLE',replace($foruminfo['title']));
$apx->tmpl->assign('FORUM_LINK',$forumlink);
$apx->tmpl->assign('POSTID',$postinfo['postid']);
$apx->tmpl->assign('RIGHT_POST',forum_access_post($foruminfo,$threadinfo));

$apx->tmpl->parse('post');

////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->tmpl->assign_static('STYLESHEET',compatible_hsc($foruminfo['stylesheet']));
$apx->tmpl->assign('PATH',forum_path($foruminfo,1));
$apx->tmpl->assign('PATHEND',replace(iif($threadinfo['sticky'],$threadinfo['sticky_text'].': ').$threadinfo['title']));
titlebar($threadinfo['title']);


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');     ///////////////////////////////////////////////////////////////////////////
require('../lib/_end.php');  //////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>