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
$_REQUEST['quote']=(int)$_REQUEST['quote'];
if ( !$_REQUEST['id'] ) die('missing thread-ID!');

$threadinfo=thread_info($_REQUEST['id']);
if ( !$threadinfo['threadid'] || $threadinfo['del'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
$foruminfo=forum_info($threadinfo['forumid']);
if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
if ( !forum_access_read($foruminfo) ) tmessage('noright',array(),false,false);
if ( !forum_access_post($foruminfo,$threadinfo) ) tmessage('noright',array(),false,false);
check_forum_password($foruminfo);



////////////////////////////////////////////////////////////////////////////////////////// POST ERSTELLEN

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
	elseif ( !$_POST['text'] || ( !$user->info['userid'] && !$_POST['username'] ) ) message('back');
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
		
		$now=time();
		$_POST['threadid']=$threadinfo['threadid'];
		$_POST['username']=$username;
		$_POST['userid']=$user->info['userid'];
		$_POST['time']=$now;
		$_POST['ip']=get_remoteaddr();
		
		//Posting erstellen
		$db->dinsert(PRE.'_forum_posts','threadid,userid,username,title,text,allowsmilies,allowcodes,allowsig,time,ip,hash');
		$pid=$db->insert_id();
		
		//Thema und Forum aktualisieren
		thread_update_cache($threadinfo['threadid'], 1);
		forum_update_cache($foruminfo['forumid'], 1);
		
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
			update_index($_POST['text'],$threadinfo['threadid'],$pid);
			update_index($_POST['title'],$threadinfo['threadid'],$pid,true);
		}
		
		//Abonnements
		if ( isset($_POST['subscription']) && $user->info['userid'] ) {
			if ( !$_POST['subscription'] ) $db->query("DELETE FROM ".PRE."_forum_subscriptions WHERE ( userid='".$user->info['userid']."' AND type='thread' AND source='".$threadinfo['threadid']."' ) LIMIT 1");
			elseif ( in_array($_POST['subscription'],array('none','instant','daily','weekly')) ) {
				$notify=$_POST['subscription'];
				list($subexists)=$db->first("SELECT id FROM ".PRE."_forum_subscriptions WHERE ( userid='".$user->info['userid']."' AND type='thread' AND source='".$threadinfo['threadid']."' ) LIMIT 1");
				if ( $subexists ) $db->query("UPDATE ".PRE."_forum_subscriptions SET notification='".$notify."' WHERE id='".$subexists."' LIMIT 1");
				else $db->query("INSERT INTO ".PRE."_forum_subscriptions (userid,type,source,notification) VALUES ('".$user->info['userid']."','thread','".$threadinfo['threadid']."','".$notify."')");
			}
		}
		
		//Thema als gelesen markieren
		thread_isread($threadinfo['threadid']);
		
		//Weiterleiten zum Thema
		$forwarder='thread.php?id='.$threadinfo['threadid'].'&amp;goto=lastpost';
		
		message($apx->lang->get('MSG_POST_ADD_OK'),$forwarder);
		require('lib/_end.php');
		require('../lib/_end.php');
	}
}


//Voreinstellungen
else {
	$_POST['allowcodes']=1;
	$_POST['allowsmilies']=1;
	$_POST['allowsig']=1;
	$_POST['transform_links']=1;
	$_POST['username']=$_COOKIE[$set['main']['cookie_pre'].'_forum_username'];
	$_POST['hash']=md5(microtime());
	$_POST['subscription']=is_thread_subscr($threadinfo['threadid']);
	if ( $user->info['forum_autosubscribe'] && !$_POST['subscription'] ) {
		$_POST['subscription'] = 'instant';
	}
}


//Zitieren
if ( $_REQUEST['quote'] ) {
	$post=$db->first("SELECT title,text,username FROM ".PRE."_forum_posts WHERE threadid='".$_REQUEST['id']."' AND postid='".$_REQUEST['quote']."' LIMIT 1");
	$post['text']=preg_replace('#\[(PHP|CODE|HTML)\](.*?)\[/\\1\]#si','',$post['text']);
	if ( $post['title'] ) $_POST['title']=iif(substr($post['title'],0,4)=='Re: ',$post['title'],'Re: '.$post['title']);
	$_POST['text']='[QUOTE='.$post['username'].']'.$post['text']."[/QUOTE]\n";
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

$apx->tmpl->assign('CAPTCHA',$captchacode);
$apx->tmpl->assign('USERNAME',compatible_hsc($_POST['username']));
$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
$apx->tmpl->assign('SMILEYLIST',$smiledata);
$apx->tmpl->assign('TRANSFORM_LINKS',(int)$_POST['transform_links']);
$apx->tmpl->assign('ATTACHMENT_TYPES',implode(', ',$filetypes));
$apx->tmpl->assign('ATTACHMENTS',$attachments);
$apx->tmpl->assign('SUBSCRIPTION',$_POST['subscription']);
$apx->tmpl->assign('ALLOWCODES',(int)$_POST['allowcodes']);
$apx->tmpl->assign('ALLOWSMILIES',(int)$_POST['allowsmilies']);
$apx->tmpl->assign('ALLOWSIG',(int)$_POST['allowsig']);
$apx->tmpl->assign('SET_CODES',$set['forum']['codes']);
$apx->tmpl->assign('SET_SMILIES',$set['forum']['smilies']);

//Die letzten 10 Beiträge
$data=$db->fetch("SELECT postid,userid,username,text,time,allowcodes,allowsmilies FROM ".PRE."_forum_posts WHERE ( del=0 AND threadid='".$threadinfo['threadid']."' ) ORDER BY time DESC LIMIT 10");
if ( count($data) ) {
	foreach ( $data AS $res ) {
		++$i;
		
		//Text
		$text = forum_replace($res['text'],$res['allowcodes'],$res['allowsmilies']);
		
		$postdata[$i]['ID']=$res['postid'];
		$postdata[$i]['USERID']=$res['userid'];
		$postdata[$i]['USERNAME']=replace($res['username']);
		$postdata[$i]['TEXT']=$text;
		$postdata[$i]['TIME']=$res['time'];
	}
}

$apx->tmpl->assign('POST',$postdata);
$apx->tmpl->assign('ATTACH',forum_access_addattachment($foruminfo));
$apx->tmpl->assign('ID',$threadinfo['threadid']);
$apx->tmpl->assign('HASH',$_POST['hash']);

$apx->tmpl->parse('newpost');


////////////////////////////////////////////////////////////////////////////////////////////////////////

$threadpath=array(array(
	'TITLE' => trim(compatible_hsc(strip_tags(forum_get_prefix($threadinfo['prefix']).' ').$threadinfo['title'])),
	'LINK' => mkrellink(
		'thread.php?id='.$threadinfo['threadid'],
		'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
	)
));

$apx->tmpl->assign_static('STYLESHEET',compatible_hsc($foruminfo['stylesheet']));
$apx->tmpl->assign('PATH',array_merge(forum_path($foruminfo,1),$threadpath));
$apx->tmpl->assign('PATHEND',$apx->lang->get('HEADLINE_NEWPOST'));
titlebar($apx->lang->get('HEADLINE_NEWPOST'));


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');     ///////////////////////////////////////////////////////////////////////////
require('../lib/_end.php');  //////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>