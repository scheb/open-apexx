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

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->module('links');
$apx->lang->drop('send');
headline($apx->lang->get('HEADLINE'),mklink('sendlink.php','sendlink.html'));
titlebar($apx->lang->get('HEADLINE'));

////////////////////////////////////////////////////////////////////////////////////////////////////////


if ( $_POST['send'] ) {
	list($spam)=$db->first("SELECT addtime FROM ".PRE."_links WHERE send_ip='".get_remoteaddr()."' ORDER BY addtime DESC");
	
	//Captcha prüfen
	if ( $set['links']['captcha'] && !$user->info['userid'] ) {
		require(BASEDIR.'lib/class.captcha.php');
		$captcha=new captcha;
		$captchafailed=$captcha->check();
	}
	
	if ( $captchafailed  ) message($apx->lang->get('MSG_WRONGCODE'),'javascript:history.back()');
	elseif ( ( !$_POST['send_username'] && !$user->info['userid'] ) || !$_POST['catid'] || !$_POST['title'] || !$_POST['url'] || !$_POST['text'] ) message('back');
	elseif ( ($spam+$set['links']['spamprot']*60)>time() ) message($apx->lang->get('MSG_BLOCKSPAM',array('SEC'=>($spam+$set['links']['spamprot']*60)-time())),'back');
	else {
		
		if ( $user->info['userid'] ) {
			$_POST['userid']=$user->info['userid'];
			$_POST['send_username'] = $_POST['send_email'] = '';
		}
		else {
			$_POST['userid'] = 0;
		}
		
		$_POST['addtime']=time();
		$_POST['send_ip']=get_remoteaddr();
		$_POST['secid']='all';
		$_POST['text']=strtr(strip_tags($_POST['text']),array(
			"\r\n" => "<br />\r\n",
			"\n" => "<br />\n"
		));
		
		//eMail-Benachrichtigung
		if ( $set['links']['mailonnew'] ) {
			$input=array('URL'=>HTTP);
			sendmail($set['links']['mailonnew'],'SENDLINK',$input);
		}
		
		//Captcha löschen
		if ( $set['links']['captcha'] && !$user->info['userid'] ) {
			$captcha->remove();
		}
		
		$db->dinsert(PRE.'_links','userid,secid,send_username,send_email,send_ip,catid,title,url,text,addtime');
		message($apx->lang->get('MSG_OK'),mklink('links.php','links.html'));
	}
	
	//SCRIPT BEENDEN
	require('lib/_end.php');
}



////////////////////////////////////////////////////////////////////////////////////////////////////////

//Kategorien auflisten
require_once(BASEDIR.'lib/class.recursivetree.php');
$tree = new RecursiveTree(PRE.'_links_cat', 'id');
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
if ( $set['links']['captcha'] && !$user->info['userid'] ) {
	require(BASEDIR.'lib/class.captcha.php');
	$captcha=new captcha;
	$captchacode=$captcha->generate();
}

$posto=mklink('sendlink.php','sendlink.html');

$apx->tmpl->assign('CAPTCHA',$captchacode);
$apx->tmpl->assign('CATEGORY',$catdata);
$apx->tmpl->assign('POSTTO',$postto);
$apx->tmpl->parse('send');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>