<?php 

define('APXRUN',true);
define('INFORUM',true);
define('BASEREL','../');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('../lib/_start.php');  ///////////////////////////////////////////////////////// SYSTEMSTART ///
require('lib/_start.php');     /////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->lang->drop('postform');
$apx->lang->drop('editor');

$_REQUEST['id']=(int)$_REQUEST['id'];
if ( !$_REQUEST['id'] ) die('missing forum-ID!');
 
$foruminfo=forum_info($_REQUEST['id']);
if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
if ( !forum_access_read($foruminfo) ) tmessage('noright',array(),false,false);
if ( !forum_access_open($foruminfo) ) tmessage('noright',array(),false,false);
check_forum_password($foruminfo);



////////////////////////////////////////////////////////////////////////////////////////// THEMA ERSTELLEN

//Vorschau generieren
if ( $_POST['preview'] ) {
	$preview=$_POST['text'];
	if ( $_POST['transform_links'] ) $preview=transform_urls($preview);
	$preview=forum_replace($preview,$_POST['allowcodes'],$_POST['allowsmilies']);
	$apx->tmpl->assign('PREVIEW',$preview);
}


//Thema erstellen
elseif ( $_POST['send'] ) {
	
	//Captcha prüfen
	if ( $set['forum']['captcha'] && !$user->info['userid'] ) {
		require(BASEDIR.'lib/class.captcha.php');
		$captcha=new captcha;
		$captchafailed=$captcha->check();
	}
	
	//Zeitpunkt des letzten Beitrags vom Benutzer
	if ( $set['forum']['spamprot'] ) {
		list($spam)=$db->first("SELECT time FROM ".PRE."_forum_posts WHERE ip='".get_remoteaddr()."' ".iif($user->info['userid'], " OR userid='".$user->info['userid']."'" )." ORDER BY time DESC");
	}
	
	if ( $captchafailed  ) message($apx->lang->get('MSG_WRONGCODE'),'javascript:history.back()');
	elseif ( !$_POST['title'] || !$_POST['text'] || ( !$user->info['userid'] && !$_POST['username'] ) || (  $_POST['sticky_type']=='own' && !$_POST['sticky_text'] ) ) message('back');
	elseif ( $set['forum']['spamprot'] && ($spam+$set['forum']['spamprot']*60)>time() ) message($apx->lang->get('MSG_BLOCKSPAM',array('SEC'=>($spam+$set['forum']['spamprot']*60)-time())),'back');
	else {
		
		//Hash-Wert
		if ( !isset($_POST['hash']) ) {
			$_POST['hash'] = md5(microtime());
		}
		
		//Captcha löschen
		if ( $set['forum']['captcha'] && !$user->info['userid'] ) {
			$captcha->remove();
		}
		
		//Benutzername
		if ( $user->info['userid'] ) $username=$user->info['username'];
		else {
			$username=$_POST['username'];
			setcookie($set['main']['cookie_pre'].'_forum_username',$_POST['username'],time()+7*24*3600);
		}
		
		//Links parsen
		if ( $_POST['transform_links'] ) {
			$_POST['text']=transform_urls($_POST['text']);
		}
		
		//Sticky
		if ( forum_access_announce($foruminfo) && $_POST['sticky_type'] && $_POST['sticky_type']!='no' ) {
			$_POST['sticky']=1;
			if ( $_POST['sticky_type']=='announcement' ) $_POST['sticky_text']=$apx->lang->get('ANNOUNCEMENT');
			if ( $_POST['sticky_type']=='important' ) $_POST['sticky_text']=$apx->lang->get('IMPORTANT');
		}
		else {
			$_POST['sticky']=0;
			$_POST['sticky_text']='';
		}
		
		$now=time();
		$_POST['forumid']=$foruminfo['forumid'];
		$_POST['posts']=1;
		$_POST['open']=1;
		$_POST['icon']=iif($_POST['icon'] && $_POST['icon']!='none',$_POST['icon'],-1);
		$_POST['opener']=$username;
		$_POST['opener_userid']=$user->info['userid'];
		$_POST['opentime']=$now;
		$_POST['lastposter']=$username;
		$_POST['lastposter_userid']=$user->info['userid'];
		$_POST['lastposttime']=$now;
		
		//Thema erstellen
		$db->dinsert(PRE.'_forum_threads','forumid,prefix,title,icon,opener,opener_userid,opentime,lastposter,lastposter_userid,lastposttime,open,sticky,sticky_text,posts');
		$tid=$db->insert_id();
		
		$_POST['threadid']=$tid;
		$_POST['username']=$username;
		$_POST['userid']=$user->info['userid'];
		$_POST['time']=$now;
		$_POST['ip']=get_remoteaddr();
		
		//Posting erstellen
		$db->dinsert(PRE.'_forum_posts','threadid,userid,username,title,text,allowsmilies,allowcodes,allowsig,time,ip,hash');
		$pid=$db->insert_id();
		
		//Thema und Forum aktualisieren
		//thread_update_cache($tid, 0, true);
		$db->query("UPDATE ".PRE."_forum_threads SET firstpost='".$pid."', lastpost='".$pid."' WHERE threadid='".$tid."' LIMIT 1");
		forum_update_cache($foruminfo['forumid'], 1, 1);
		
		//Postingzahl des Benutzers erhöhen
		if ( $user->info['userid'] && $foruminfo['countposts'] ) {
			$db->query("UPDATE ".PRE."_user SET forum_posts=forum_posts+1 WHERE userid='".$user->info['userid']."' LIMIT 1");
		}
		
		//Anhänge hinzufügen
		if ( forum_access_addattachment($foruminfo) ) {
			$db->query("UPDATE ".PRE."_forum_attachments SET postid='".$pid."' WHERE hash='".addslashes($_POST['hash'])."' AND time>'".(time()-3600)."'");
		}
		
		//Index aktualisieren
		if ( $foruminfo['searchable'] ) {
			update_index($_POST['text'],$tid,$pid);
			update_index($_POST['title'],$tid,$pid,true);
		}
		
		//Abonnements
		if ( $user->info['userid'] ) {
			if ( in_array($_POST['subscription'],array('none','instant','daily','weekly')) ) {
				$notify=$_POST['subscription'];
				$db->query("INSERT INTO ".PRE."_forum_subscriptions (userid,type,source,notification) VALUES ('".$user->info['userid']."','thread','".$tid."','".$notify."')"); 
			}
		}
		
		//Thema als gelesen markieren
		thread_isread($tid);
		
		//Weiterleiten zum Thema
		$forwarder=mkrellink(
			'thread.php?id='.$tid.'#p'.$pid,
			'thread,'.$tid.',1'.urlformat($_POST['title']).'.html#p'.$pid
		);
		
		message($apx->lang->get('MSG_THREAD_ADD_OK'),$forwarder);
		require('lib/_end.php');
		require('../lib/_end.php');
	}
}


//Voreinstellungen
else {
	$_POST['icon']='none';
	$_POST['sticky_type']='no';
	$_POST['allowcodes']=1;
	$_POST['allowsmilies']=1;
	$_POST['allowsig']=1;
	$_POST['transform_links']=1;
	$_POST['username']=$_COOKIE[$set['main']['cookie_pre'].'_forum_username'];
	$_POST['hash']=md5(microtime());
	if ( $user->info['forum_autosubscribe'] && !$_POST['subscription'] ) {
		$_POST['subscription'] = 'instant';
	}
}


//Themen-Icons
$icons=$set['forum']['icons'];
if ( is_array($icons) ) $icons=array_sort($icons,'ord','ASC');
if ( count($icons) ) {
	foreach ( $icons AS $key => $res ) {
		++$ii;
		$icondata[$ii]['ID']=$key;
		$icondata[$ii]['IMAGE']=$res['file'];
	}
}

//Smilies
if ( count($set['main']['smilies']) ) {
	foreach ( $set['main']['smilies'] AS $res ) {
		++$si;
		$smiledata[$si]['INSERTCODE']=addslashes($res['code']);
		if ( $res['file'][0]!='/' && defined('BASEREL') ) $smiledata[$si]['IMAGE']=BASEREL.$res['file'];
		else $smiledata[$si]['IMAGE']=$res['file'];
		if ( $si==16 ) break;
	}
}

//Dateitypen
$filetypes=array();
$typeinfo=array();
$data=$db->fetch("SELECT * FROM ".PRE."_forum_filetypes ORDER BY ext ASC");
if ( count($data) ) {
	foreach ( $data AS $res ) {
		$filetypes[]=$res['ext'];
		$typeinfo[$res['ext']]=array($res['size']*1024,$res['icon']);
	}
}

//Anhänge auslesen wenn gepostet
$attachments='';
if ( $_POST['hash'] ) {
	$data=$db->fetch("SELECT * FROM ".PRE."_forum_attachments WHERE ( postid='0' AND hash='".addslashes($_POST['hash'])."' ) ORDER BY name ASC");
	if ( count($data) ) {
		require(BASEDIR.'lib/class.mediamanager.php');
		$mm=new mediamanager;
		foreach ( $data AS $res ) {
			$ext=strtolower($mm->getext($res['name']));
			$attachments.='<img src="'.$typeinfo[$ext][1].'" alt="" style="vertical-align:middle;" /> '.$res['name'].' ('.round($res['size']/1024).' KB)';
		}
	}
}

//Captcha erstellen
if ( $set['forum']['captcha'] && !$user->info['userid'] ) {
	require(BASEDIR.'lib/class.captcha.php');
	$captcha=new captcha;
	$captchacode=$captcha->generate();
}


//Präfixe
$prefixdata = array();
$prefixInfo = forum_prefixes($foruminfo['forumid']);
foreach ( $prefixInfo AS $prefix ) {
	$prefixdata[] = array(
		'ID' => $prefix['prefixid'],
		'TITLE' => compatible_hsc($prefix['title']),
		'SELECTED' => $_POST['prefix']==$prefix['prefixid']
	);
}

$apx->tmpl->assign('CAPTCHA',$captchacode);
$apx->tmpl->assign('USERNAME',compatible_hsc($_POST['username']));
$apx->tmpl->assign('PREFIX',$prefixdata);
$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
$apx->tmpl->assign('ICON',iif($_POST['icon']==='none',$_POST['icon'],(int)$_POST['icon']));
$apx->tmpl->assign('ICONLIST',$icondata);
$apx->tmpl->assign('SMILEYLIST',$smiledata);
$apx->tmpl->assign('STICKY_TYPE',compatible_hsc($_POST['sticky_type']));
$apx->tmpl->assign('STICKY_TEXT',compatible_hsc($_POST['sticky_text']));
$apx->tmpl->assign('TRANSFORM_LINKS',(int)$_POST['transform_links']);
$apx->tmpl->assign('ATTACHMENTS',$attachments);
$apx->tmpl->assign('ATTACHMENT_TYPES',implode(', ',$filetypes));
$apx->tmpl->assign('SUBSCRIPTION',$_POST['subscription']);
$apx->tmpl->assign('ALLOWCODES',(int)$_POST['allowcodes']);
$apx->tmpl->assign('ALLOWSMILIES',(int)$_POST['allowsmilies']);
$apx->tmpl->assign('ALLOWSIG',(int)$_POST['allowsig']);
$apx->tmpl->assign('SET_CODES',$set['forum']['codes']);
$apx->tmpl->assign('SET_SMILIES',$set['forum']['smilies']);

$apx->tmpl->assign('ANNOUNCE',forum_access_announce($foruminfo));
$apx->tmpl->assign('ATTACH',forum_access_addattachment($foruminfo));
$apx->tmpl->assign('ID',$foruminfo['forumid']);
$apx->tmpl->assign('HASH',$_POST['hash']);

$apx->tmpl->parse('newthread');


////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->tmpl->assign_static('STYLESHEET',compatible_hsc($foruminfo['stylesheet']));
$apx->tmpl->assign('PATH',forum_path($foruminfo,1));
$apx->tmpl->assign('PATHEND',$apx->lang->get('HEADLINE_NEWTHREAD'));
titlebar($apx->lang->get('HEADLINE_NEWTHREAD'));


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');     ///////////////////////////////////////////////////////////////////////////
require('../lib/_end.php');  //////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>