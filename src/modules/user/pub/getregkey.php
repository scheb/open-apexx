<?php

if ( $set['user']['useractivation']!=3 ) exit;
$apx->lang->drop('getregkey');
headline($apx->lang->get('HEADLINE_GETREGKEY'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_GETREGKEY'));

if ( $_POST['send'] ) {
	if ( !$_POST['username'] || !$_POST['email'] ) message('back');
	else {
		$res=$db->first("SELECT userid,username_login,email,reg_key FROM ".PRE."_user WHERE ( username_login='".addslashes($_POST['username'])."' AND email='".addslashes($_POST['email'])."' )");
		
		if ( !$res['userid'] ) message($apx->lang->get('MSG_NOMATCH'),'javascript:history.back()');
		elseif ( !$res['reg_key'] ) message($apx->lang->get('MSG_ISACTIVE'),'javascript:history.back()');
		else {
			$reglink=mklink(
				'user.php?action=activate&userid='.$res['userid'].'&key='.$res['reg_key'],
				'user,activate.html?userid='.$res['userid'].'&key='.$res['reg_key']
			);
			
			$input['USERNAME']=replace($res['username_login']);
			$input['WEBSITE']=$set['main']['websitename'];
			$input['URL']=HTTP_HOST.$reglink;
			sendmail($res['email'],'GETKEY',$input);
			
			message($apx->lang->get('MSG_OK_REGKEY'),mklink('user.php','user.html'));
		}
	}
}
else {
	$postto=mklink(
		'user.php?action=getregkey',
		'user,getregkey.html'
	);
	
	$apx->tmpl->assign('POSTTO',$postto);
	$apx->tmpl->parse('getregkey');
}

?>