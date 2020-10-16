<?php 

define('APXRUN',true);
define('INFORUM',true);
define('BASEREL','../');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('../lib/_start.php');  ///////////////////////////////////////////////////////// SYSTEMSTART ///
require('lib/_start.php');     /////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->module('forum');
$apx->lang->drop('global');
$apx->lang->drop('attachments');
$apx->tmpl->loaddesign('blank');
$message='';
$_REQUEST['postid']=(int)$_REQUEST['postid'];
$_REQUEST['getid']=(int)$_REQUEST['getid'];
if ( !$_REQUEST['getid'] && !$_POST['postid'] && !$_REQUEST['hash'] ) die('invalid use of attachments!');

///////////////////////////////////////////////////////////////////////////////////// ANHANG HERUNTERLADEN

if ( $_REQUEST['getid'] ) {
	
	//Rechte-Check
	$att=$db->first("SELECT * FROM ".PRE."_forum_attachments WHERE id='".$_REQUEST['getid']."' LIMIT 1");
	if ( !$att['postid'] ) die('found no postid!');
	
	require_once(BASEDIR.getmodulepath('forum').'basics.php');
	$postinfo=post_info($att['postid']);
	if ( !$postinfo['postid'] ) message($apx->lang->get('MSG_POSTNOTEXIST'));
	$threadinfo=thread_info($postinfo['threadid']);
	if ( !$threadinfo['threadid'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( $threadinfo['del'] && !( $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	if ( $postinfo['del'] && !( $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) ) message($apx->lang->get('MSG_POSTNOTEXIST'));
	if ( !forum_access_read($foruminfo) ) tmessage('noright',array(),false,false);
	if ( !forum_access_readattachment($foruminfo) ) tmessage('noright',array(),false,false);
	
	require(BASEDIR.'lib/class.mediamanager.php');
	$mm=new mediamanager;
	$ext=$mm->getext($att['file']);
	$filep=explode('_',$mm->getname($att['file']));
	array_pop($filep);
	$outname=implode('_',$filep).'.'.strtolower($ext);
	
	//Mime-Type bei alten Dateien
	if ( !$att['mime'] ) {
		if ( $ext=='GIF' ) $att['mime']='image/gif';
		elseif ( $ext=='JPG' || $ext=='JPE' || $ext=='JPEG' ) $att['mime']='image/jpeg';
		elseif ( $ext=='PNG' ) $att['mime']='image/png';
		else $att['mime']='application/octet-stream';
	}
	
	//Datei ausgeben
	if( headers_sent() ) die('Some data has already been output to browser, can not send file!');
	Header('Content-Type: '.$att['mime']);
	Header('Content-Length: '.filesize(BASEDIR.getpath('uploads').$att['file']));
	Header('Content-disposition: inline; filename='.$outname);
	readfile(BASEDIR.getpath('uploads').$att['file']);
	
	exit;
}


///////////////////////////////////////////////////////////////////////////////////// ANHÄNGE HOCHLADEN

//Postid vorhanden => Beitrag wird bearbeitet
if ( $_REQUEST['postid'] ) {
	require_once(BASEDIR.getmodulepath('forum').'basics.php');
	$postinfo=post_info($_REQUEST['postid']);
	if ( !$postinfo['postid'] ) message($apx->lang->get('MSG_POSTNOTEXIST'));
	$threadinfo=thread_info($postinfo['threadid']);
	if ( !$threadinfo['threadid'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( $threadinfo['del'] && !( $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	if ( $postinfo['del'] && !( $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) ) message($apx->lang->get('MSG_POSTNOTEXIST'));
	if ( !forum_access_addattachment($foruminfo) ) tmessage('noright',array(),false,false);
}

require(BASEDIR.'lib/class.mediamanager.php');
$mm=new mediamanager;

//Dateitypen
$typeinfo=array();
$data=$db->fetch("SELECT * FROM ".PRE."_forum_filetypes ORDER BY ext ASC");
if ( count($data) ) {
	foreach ( $data AS $res ) {
		$i++;
		$filedata[$i]['EXT']=$res['ext'];
		$filedata[$i]['ICON']=$res['icon'];
		$filedata[$i]['MAXSIZE']=$res['size'];
		$typeinfo[$res['ext']]=array(
			$res['size']*1024,
			$res['icon']
		);
	}
}

//Dateien hochladen
if ( $_POST['send'] ) {
	for ( $i=1; $i<=3; $i++ ) {
		$file=&$_FILES['file'.$i];
		$ext=strtolower($mm->getext($file['name']));
		
		if ( $file['error'] ) continue;
		elseif ( !$file['tmp_name'] ) continue; //Nix hochgeladen
		elseif ( !isset($typeinfo[$ext]) ) { //Nicht erlaubt
			if ( $message ) $message.='<br />';
			$message.=$apx->lang->get('MSG_NOUPLOAD',array('FILE'=>$file['name'])).' ';
			$message.=$apx->lang->get('MSG_WRONGTYPE');
			continue;
		}
		elseif ( $file['size']>$typeinfo[$ext][0] ) {
			if ( $message ) $message.='<br />';
			$message.=$apx->lang->get('MSG_NOUPLOAD',array('FILE'=>$file['name'])).' ';
			$message.=$apx->lang->get('MSG_TOOBIG',array('MAXSIZE'=>$typeinfo[$ext][0]));
			continue;
		}
		
		$fileid = str_replace(' ','_',$mm->getname($file['name'])).'_'.time();
		$newname=$fileid.'.'.$ext;
		$thumbnailPath = '';
		$mm->uploadfile($_FILES['file'.$i],'forum',$newname);
		
		//Thumbnail erzeugen
		if ( in_array($ext, array('gif', 'jpg', 'jpe', 'jpeg', 'png')) ) {
			require_once(BASEDIR.'lib/class.image.php');
			$img = new image();
			
			$thumbnailPath = 'forum/'.$fileid.'_thumb.'.$ext;
			list($picture,$picturetype)=$img->getimage('forum/'.$newname);
			
			//////// THUMBNAIL
			$thumbnail=$img->resize($picture, 120, 90, true);
			$img->saveimage($thumbnail,$picturetype,$thumbnailPath);
			
			//Cleanup
			imagedestroy($picture);
			imagedestroy($thumbnail);
			unset($picture,$thumbnail);
		}
		
		$db->query("INSERT INTO ".PRE."_forum_attachments (hash,postid,file,thumbnail,name,size,mime,time) VALUES ('".addslashes($_REQUEST['hash'])."','".$_REQUEST['postid']."','".addslashes('forum/'.$newname)."','".addslashes($thumbnailPath)."','".addslashes($file['name'])."','".intval($file['size'])."','".addslashes($file['type'])."','".time()."')");
		
		if ( $message ) $message.='<br />';
		$message.=$apx->lang->get('MSG_OK',array('FILE'=>$file['name']));
	}
}

//Dateien löschen
if ( $_POST['delete'] && is_array($_POST['delete']) ) {
	reset($_POST['delete']);
	$delete=(int)key($_POST['delete']);
	if ( $delete ) {
		list($file,$filename)=$db->first("SELECT file,name FROM ".PRE."_forum_attachments WHERE ( id='".$delete."' AND postid='".$_REQUEST['postid']."' AND hash='".addslashes($_REQUEST['hash'])."' ) LIMIT 1");
		$mm->deletefile($file);
		$db->query("DELETE FROM ".PRE."_forum_attachments WHERE ( id='".$delete."' AND postid='".$_REQUEST['postid']."' AND hash='".addslashes($_REQUEST['hash'])."' ) LIMIT 1");
		$message.=$apx->lang->get('MSG_DELETE',array('FILE'=>$filename));
	}
}

//Dateien auflisten
$attrefresh='';
$data=$db->fetch("SELECT * FROM ".PRE."_forum_attachments WHERE ( postid='".$_REQUEST['postid']."' AND hash='".addslashes($_REQUEST['hash'])."' ) ORDER BY name ASC");
if ( count($data) ) {
	foreach ( $data AS $res ) {
		++$i;
		$ext=strtolower($mm->getext($res['name']));
		$attdata[$i]['ID']=$res['id'];
		$attdata[$i]['FILENAME']=$res['name'];
		$attdata[$i]['ICON']=$typeinfo[$ext][1];
		$attdata[$i]['SIZE']=forum_getsize($res['size']);
		$attrefresh.="window.opener.att.add('".addslashes($res['name'])."','".round($res['size']/1024)." KB','".addslashes($typeinfo[$ext][1])."');\n";
	}
}

//Javascript zur Aktualisierung des Hauptfensters
if ( $_POST['send'] || $_POST['delete'] ) {
	$refresh='<script language="JavaScript" type="text/javascript">'."\n<!--\n\n";
	$refresh.="if ( typeof window.opener.att!='undefined' ) {\nwindow.opener.att.reset();\n";
	$refresh.=$attrefresh;
	$refresh.="}\n\n";
	$refresh.="//-->\n</script>";
}

$apx->tmpl->assign('TYPE',$filedata);
$apx->tmpl->assign('ATTACHMENT',$attdata);
$apx->tmpl->assign('MESSAGE',$message);
$apx->tmpl->assign('HASH',$_REQUEST['hash']);
$apx->tmpl->assign('REFRESH',$refresh);

$apx->tmpl->loaddesign('blank');
$apx->tmpl->parse('attachments');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');     ///////////////////////////////////////////////////////////////////////////
require('../lib/_end.php');  //////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>