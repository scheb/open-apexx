<?php

//Nur für Benutzer oder Gäste explizit erlaubt
if ( !($user->info['userid'] || $set['user']['sendmail_guests']) ) {
	filenotfound();
	return;
}


$apx->lang->drop('newmail');
headline($apx->lang->get('HEADLINE_NEWMAIL'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_NEWMAIL'));
$_REQUEST['touser']=(int)$_REQUEST['touser'];

if ( $_POST['send'] ) {
	list($touser,$email)=$db->first("SELECT userid,email FROM ".PRE."_user WHERE username='".addslashes($_POST['touser'])."' LIMIT 1");
	
	//Captcha prüfen
	if ( !$user->info['userid'] ) {
		require(BASEDIR.'lib/class.captcha.php');
		$captcha=new captcha;
		$captchafailed=$captcha->check();
	}
	
	if ( $captchafailed  ) message($apx->lang->get('MSG_WRONGCODE'),'javascript:history.back()');		
	elseif ( !$_POST['touser'] || !$_POST['subject'] || !$_POST['text'] || ( !$user->info['userid'] && ( !$_POST['name'] || !$_POST['email'] ) ) ) message('back');
	elseif ( $user->info['userid'] && $user->ignore($touser,$reason) ) {
		if ( $reason ) message($apx->lang->get('MSG_IGNORED_REASON',array('REASON'=>$reason)),'javascript:history.back()');
		else message($apx->lang->get('MSG_IGNORED'),'javascript:history.back()');
	}
	elseif ( !$touser ) message($apx->lang->get('MSG_NOTEXISTS'),'javascript:history.back()');
	elseif ( $user->info['userid'] && $touser==$user->info['userid'] ) message($apx->lang->get('MSG_SELF'),'javascript:history.back()');
	else {
		if ( $user->info['userid'] ) {
			$sender = $user->info['username'].'<'.$user->info['email'].'>';
		}
		else {
			$sender = $_POST['name'].'<'.$_POST['email'].'>';
		}
		mail($email,$_POST['subject'],$_POST['text'],'From: '.$sender);
		
		if ( $user->info['userid'] ) {
			message($apx->lang->get('MSG_OK'),mklink('user.php','user.html'));
		}
		else {
			message($apx->lang->get('MSG_OK'),mklink('index.php','index.html'));
		}
	}
}
else {
	if ( $_REQUEST['touser'] ) {
		list($username)=$db->first("SELECT username FROM ".PRE."_user WHERE userid='".intval($_REQUEST['touser'])."' LIMIT 1");
		$apx->tmpl->assign('USERNAME',compatible_hsc($username));
	}			
	
	$postto=mklink(
		'user.php?action=newmail',
		'user,newmail.html'
	);
	
	//Captcha erstellen
	if ( !$user->info['userid'] ) {
		require(BASEDIR.'lib/class.captcha.php');
		$captcha=new captcha;
		$captchacode=$captcha->generate();
	}
	
	$apx->tmpl->assign('CAPTCHA', $captchacode);
	$apx->tmpl->assign('POSTTO',$postto);
	$apx->tmpl->parse('newmail');
}

?>