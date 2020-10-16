<?php 

define('APXRUN',true);
define('MODULE','downloads');
define('PAGEID','senddownload');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->module('downloads');
$apx->lang->drop('send');
headline($apx->lang->get('HEADLINE'),mklink('senddownload.php','senddownload.html'));
titlebar($apx->lang->get('HEADLINE'));

////////////////////////////////////////////////////////////////////////////////////////////////////////


if ( $_POST['send'] ) {
	list($spam)=$db->first("SELECT addtime FROM ".PRE."_downloads WHERE send_ip='".get_remoteaddr()."' ORDER BY addtime DESC");
	
	//Captcha prüfen
	if ( $set['downloads']['captcha'] && !$user->info['userid'] ) {
		require(BASEDIR.'lib/class.captcha.php');
		$captcha=new captcha;
		$captchafailed=$captcha->check();
	}
	
	if ( $captchafailed  ) message($apx->lang->get('MSG_WRONGCODE'),'javascript:history.back()');
	elseif ( ( !$_POST['send_username'] && !$user->info['userid'] ) || !$_POST['catid'] || !$_POST['title'] || !$_POST['text'] || !$_FILES['file']['tmp_name'] ) message('back');
	elseif ( ($spam+$set['downloads']['spamprot']*60)>time() ) message($apx->lang->get('MSG_BLOCKSPAM',array('SEC'=>($spam+$set['downloads']['spamprot']*60)-time())),'back');
	else {
		$ext=substr(strrchr($_FILES['file']['name'],'.'),1);
		list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".strtoupper($ext)."' LIMIT 1");
		if ( $special=='block' ) message($apx->lang->get('MSG_NOTALLOWED'),'back');
		
		require(BASEDIR.'lib/class.mediamanager.php');
		$mm=new mediamanager;
		
		$stamp=md5(microtime());
		$mm->uploadfile($_FILES['file'],'downloads/uploads',$stamp.'-'.$_FILES['file']['name']);
		
		if ( $user->info['userid'] ) {
			$_POST['userid']=$user->info['userid'];
			$_POST['send_username'] = $_POST['send_email'] = '';
		}
		else {
			$_POST['userid'] = 0;
		}
		
		$_POST['file']=$_FILES['file']['name'];
		$_POST['tempfile']='downloads/uploads/'.$stamp.'-'.$_FILES['file']['name'];
		$_POST['addtime']=time();
		$_POST['send_ip']=get_remoteaddr();
		$_POST['local']=1;
		$_POST['secid']='all';
		$_POST['text']=strtr(strip_tags($_POST['text']),array(
			"\r\n" => "<br />\r\n",
			"\n" => "<br />\n"
		));
		
		if ( $set['downloads']['coms'] ) $_POST['allowcoms']=1;
		if ( $set['downloads']['ratings'] ) $_POST['allowrating']=1;
		if ( checkmail($_POST['author_link']) ) $_POST['author_link']='mailto:'.$_POST['author_link'];
		
		//eMail-Benachrichtigung
		if ( $set['downloads']['mailonnew'] ) {
			$input=array('URL'=>HTTP);
			sendmail($set['downloads']['mailonnew'],'SENDDOWNLOAD',$input);
		}
		
		//Captcha löschen
		if ( $set['downloads']['captcha'] && !$user->info['userid'] ) {
			$captcha->remove();
		}
		
		$db->dinsert(PRE.'_downloads','userid,secid,catid,send_username,send_email,send_ip,file,tempfile,local,title,text,author,author_link,addtime,allowcoms,allowrating');
		message($apx->lang->get('MSG_OK'),mklink('downloads.php','downloads.html'));
	}
	
	//SCRIPT BEENDEN
	require('lib/_end.php');
}



////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once(BASEDIR.'lib/class.recursivetree.php');
$tree = new RecursiveTree(PRE.'_downloads_cat', 'id');
$data = $tree->getTree(array('title', 'open'));
if ( count($data) ) {
	foreach ( $data AS $res ) {
		++$i;
		$catdata[$i]['ID']=$res['id'];
		$catdata[$i]['TITLE']=$res['title'];
		$catdata[$i]['LEVEL']=$res['level'];
		$catdata[$i]['OPEN']=$res['open'];
	}
}

//Captcha erstellen
if ( $set['downloads']['captcha'] && !$user->info['userid'] ) {
	require(BASEDIR.'lib/class.captcha.php');
	$captcha=new captcha;
	$captchacode=$captcha->generate();
}

$posto=mklink('senddownload.php','senddownload.html');

$apx->tmpl->assign('CAPTCHA',$captchacode);
$apx->tmpl->assign('CATEGORY',$catdata);
$apx->tmpl->assign('POSTTO',$postto);
$apx->tmpl->assign('MAXUPLOAD',str_replace('M','MB',ini_get('upload_max_filesize')));
$apx->tmpl->parse('send');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>