<?php 

define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->module('guestbook');
$apx->lang->drop('guestbook');
headline($apx->lang->get('HEADLINE'),mklink('guestbook.php','guestbook.html'));


//Ist die IP des Posters gesperrt?
function ip_is_blocked() {
	global $set;
	
	if ( !is_array($set['guestbook']['blockip']) ) $set['guestbook']['blockip']=array();
	foreach ( $set['guestbook']['blockip'] AS $res ) {
		if ( ( $res['endip']===false && $res['startip']==ip2float(get_remoteaddr()) ) || ( ip2float(get_remoteaddr())>=$res['startip'] && ip2float(get_remoteaddr())<=$res['endip'] ) ) {
			return true;
		}
	}
	
	return false;
}


//Enthält der Text verbotene Zeichenketten?
function text_is_blocked() {
	global $set;
	$text=$_POST['text'];
	
	if ( !is_array($set['guestbook']['blockstring']) ) $set['guestbook']['blockstring']=array();
	foreach ( $set['guestbook']['blockstring'] AS $string ) {
		$string=trim($string);
		$string=preg_quote($string);
		$string=str_replace('\\*','(.*)',$string);
		if ( preg_match('#'.$string.'#miU',$text) ) return true; 
	}
	
	return false;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////


if ( $_REQUEST['add'] ) {
	$apx->lang->drop('addentry');
	headline($apx->lang->get('HEADLINE_ADD'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
	titlebar($apx->lang->get('HEADLINE').': '.$apx->lang->get('HEADLINE_ADD'));
	
	//Eintrag senden
	if ( $_POST['send'] ) {
		list($spam)=$db->first("SELECT time FROM ".PRE."_guestbook WHERE ip='".get_remoteaddr()."' ORDER BY time DESC");
		
		//Captcha prüfen
		if ( $set['guestbook']['captcha'] && !$user->info['userid'] ) {
			require(BASEDIR.'lib/class.captcha.php');
			$captcha=new captcha;
			$captchafailed=$captcha->check();
		}
		
		if ( $user->info['userid'] ) {
			if ( $captchafailed  ) message($apx->lang->get('MSG_WRONGCODE'),'javascript:history.back()');
			elseif ( ip_is_blocked() ) message($apx->lang->get('MSG_BLOCKIP'),'back');
			elseif ( !$_POST['text'] || ( $set['guestbook']['req_title'] && !$_POST['title'] ) ) message('back');
			elseif ( text_is_blocked() ) message($apx->lang->get('MSG_BLOCKTEXT'),'back');
			elseif ( $set['guestbook']['entrymaxlen'] && strlen($_POST['text'])>$set['guestbook']['entrymaxlen'] ) message($apx->lang->get('MSG_TOOLONG'),'back');
			elseif ( ($spam+$set['guestbook']['spamprot']*60)>time() ) message($apx->lang->get('MSG_BLOCKSPAM',array('SEC'=>($spam+$set['guestbook']['spamprot']*60)-time())),'back');
			else {
			
				if ( $set['guestbook']['mod'] && !$user->is_team_member() ) $_POST['active']=0;
				else $_POST['active']=1;
				
				$_POST['userid']=$user->info['userid'];
				$_POST['username']=$user->info['username'];
				$_POST['time']=time();
				$_POST['ip']=get_remoteaddr();
				
				$db->dinsert(PRE.'_guestbook','userid,username,title,text,time,ip,active,custom1,custom2,custom3,custom4,custom5');
				
				//eMail-Benachrichtigung
				if ( $set['guestbook']['mailonnew'] ) {
					$text = strip_tags(dbcodes($_POST['text']));
					$input=array(
						'URL' => HTTP,
						'GOTO' => HTTP_HOST.mklink('guestbook.php','guestbook.html'),
						'TEXT' => $text
					);
					sendmail($set['guestbook']['mailonnew'],'SENDENTRY',$input);
				}
				
				//Captcha löschen
				if ( $set['guestbook']['captcha'] && !$user->info['userid'] ) {
					$captcha->remove();
				}
				
				message($apx->lang->get('MSG_OK'),mklink(
					'guestbook.php',
					'guestbook.html')
				);
			}
		}
		else {
			if ( !checkmail($_POST['email']) ) {
				if ( $set['guestbook']['req_email'] ) $emailnotvalid=true;
				else $_POST['email']='';
			}
			
			if ( $captchafailed  ) message($apx->lang->get('MSG_WRONGCODE'),'javascript:history.back()');
			elseif ( ip_is_blocked() ) message($apx->lang->get('MSG_BLOCKIP'),'back');
			elseif ( !$_POST['username'] || !$_POST['text'] 
				|| ( $set['guestbook']['req_email'] && !$_POST['email'] )
				|| ( $set['guestbook']['req_homepage'] && !$_POST['homepage'] )
				|| ( $set['guestbook']['req_title'] && !$_POST['title'] )
			) message('back');
			elseif ( $set['guestbook']['maxlen'] && strlen($_POST['text'])>$set['guestbook']['maxlen'] ) message($apx->lang->get('MSG_TOOLONG'),'back');
			elseif ( text_is_blocked() ) message($apx->lang->get('MSG_BLOCKTEXT'),'back');
			elseif ( $emailnotvalid ) message($apx->lang->get('MSG_EMAILNOTVALID'),'back');
			elseif ( ($spam+$set['guestbook']['spamprot']*60)>time() ) message($apx->lang->get('MSG_BLOCKSPAM',array('SEC'=>($spam+$set['guestbook']['spamprot']*60)-time())),'back');
			else {
				if ( substr($_POST['homepage'],0,4)=='www.' ) $_POST['homepage']='http://'.$_POST['homepage'];
				
				if ( $set['guestbook']['mod'] ) $_POST['active']=0;
				else $_POST['active']=1;
				
				$_POST['time']=time();
				$_POST['ip']=get_remoteaddr();
				
				$db->dinsert(PRE.'_guestbook','username,email,homepage,title,text,time,ip,active,custom1,custom2,custom3,custom4,custom5');
				
				//eMail-Benachrichtigung
				if ( $set['guestbook']['mailonnew'] ) {
					$text = strip_tags(dbcodes($_POST['text']));
					$input=array(
						'URL' => HTTP,
						'GOTO' => HTTP_HOST.mklink('guestbook.php','guestbook.html'),
						'TEXT' => $text
					);
					sendmail($set['guestbook']['mailonnew'],'SENDENTRY',$input);
				}
				
				//Captcha löschen
				if ( $set['guestbook']['captcha'] && !$user->info['userid'] ) {
					$captcha->remove();
				}
				
				message($apx->lang->get('MSG_OK'),mklink(
					'guestbook.php',
					'guestbook.html')
				);
			}
		}
	}
	
	//Formular ausgeben
	else {
		for ( $i=1; $i<=5; $i++ ) {
			$apx->tmpl->assign('CUSTOM'.$i.'_NAME',$set['guestbook']['cusfield_names'][$i-1]);
		}
		
		$postto=mklink(
			'guestbook.php?add=1',
			'guestbook,add.html'
		);
		
		//Captcha erstellen
		if ( $set['guestbook']['captcha'] && !$user->info['userid'] ) {
			require(BASEDIR.'lib/class.captcha.php');
			$captcha=new captcha;
			$captchacode=$captcha->generate();
		}
		
		$apx->tmpl->assign('CAPCHA',$captchacode); //Abwärtskompatiblität
		$apx->tmpl->assign('CAPTCHA',$captchacode);
		$apx->tmpl->assign('POSTTO',$postto);
		$apx->tmpl->parse('form');
	}
	
	//SCRIPT BEENDEN
	require('lib/_end.php');
}


////////////////////////////////////////////////////////////////////////////////////////////////////////


//Headline
titlebar($apx->lang->get('HEADLINE'));

//Seitenzahlen
list($count)=$db->first("SELECT count(id) FROM ".PRE."_guestbook WHERE active='1'");
pages(
	mklink('guestbook.php','guestbook,{P}.html'),
	$count,
	$set['guestbook']['epp']
);


$data=$db->fetch("SELECT a.*,b.username AS com_username,b.email AS com_email,b.pub_hidemail AS com_hidemail FROM ".PRE."_guestbook AS a LEFT JOIN ".PRE."_user AS b ON a.com_userid=b.userid WHERE a.active='1' ORDER BY a.time DESC ".getlimit($set['guestbook']['epp']));
$entrynumber=$count-($_REQUEST['p']-1)*$set['guestbook']['epp'];
if ( count($data) ) {
	foreach ( $data AS $res ) {
		++$i;
		
		if ( $res['userid'] ) {
			if ( !isset($userinfo[$res['userid']]) ) {
				$userinfo[$res['userid']]=$user->get_info($res['userid'],'username,email,pub_hidemail,homepage,avatar,avatar_title,signature,lastactive,pub_invisible');
			}
			
			$tabledata[$i]['USERID']=$res['userid'];
			$tabledata[$i]['NAME']=replace($userinfo[$res['userid']]['username']);
			$tabledata[$i]['EMAIL']=replace(iif(!$userinfo[$res['userid']]['pub_hidemail'],$userinfo[$res['userid']]['email']));
			$tabledata[$i]['EMAIL_ENCRYPTED']=replace(iif(!$userinfo[$res['userid']]['pub_hidemail'],cryptMail($userinfo[$res['userid']]['email'])));
			$tabledata[$i]['HOMEPAGE']=replace($userinfo[$res['userid']]['homepage']);
			$tabledata[$i]['AVATAR']=$user->mkavatar($userinfo[$res['userid']]);
			$tabledata[$i]['AVATAR_TITLE']=$user->mkavtitle($userinfo[$res['userid']]);
			$tabledata[$i]['SIGNATURE']=$user->mksig($userinfo[$res['userid']]);
			$tabledata[$i]['ONLINE'] = iif(!$userinfo[$res['userid']]['pub_invisible'] && ($userinfo[$res['userid']]['lastactive']+$set['user']['timeout']*60)>=time(),1,0);
			$tabledata[$i]['ISONLINE'] = $tabledata[$i]['ONLINE'];
		}
		else {
			$tabledata[$i]['NAME']=replace($res['username']);
			$tabledata[$i]['EMAIL']=replace($res['email']);
			$tabledata[$i]['EMAIL_ENCRYPTED']=replace(cryptMail($res['email']));
			$tabledata[$i]['HOMEPAGE']=replace($res['homepage']);
		}
		
		//Text
		$text=$res['text'];
		if ( $set['guestbook']['badwords'] ) $text=badwords($text);
		$text=replace($text,1);
		if ( $set['guestbook']['breakline'] ) $text=wordwrapHTML($text,$set['guestbook']['breakline'],"\n",1);
		if ( $set['guestbook']['allowsmilies'] ) $text=dbsmilies($text);
		if ( $set['guestbook']['allowcode'] ) $text=dbcodes($text);
		
		//Titel
		$title=$res['title'];
		if ( $set['guestbook']['breakline'] ) $title=wordwrap($title,$set['guestbook']['breakline'],"\n",1);
		if ( $set['guestbook']['badwords'] ) $title=badwords($title);
		$title=replace($title);
		
		$tabledata[$i]['TEXT']=$text;
		$tabledata[$i]['TITLE']=$title;
		$tabledata[$i]['TIME']=$res['time'];
		$tabledata[$i]['NUMBER']=$entrynumber--;
		
		//Custom-Felder
		for ( $ii=1; $ii<=5; $ii++ ) {
			$tabledata[$i]['CUSTOM'.$ii]=replace($res['custom'.$ii]);
		}
		
		//Admin-Kommentar
		if ( $res['com_text'] ) {
			$tabledata[$i]['COMMENT_USERID']=$res['com_userid'];
			$tabledata[$i]['COMMENT_USERNAME']=replace($res['com_username']);
			$tabledata[$i]['COMMENT_EMAIL']=replace(iif(!$res['com_hidemail'],$res['com_email']));
			$tabledata[$i]['COMMENT_EMAIL_ENCRYPTED']=replace(iif(!$res['com_hidemail'],cryptMail($res['com_email'])));
			$tabledata[$i]['COMMENT_TIME']=$res['com_time'];
			
			$text=replace($res['com_text'],1);
			$text=dbsmilies($text);
			$text=dbcodes($text);
			
			$tabledata[$i]['COMMENT_TEXT']=$text;
		}
		
		//Admin-Links
		if ( $_COOKIE[$set['main']['cookie_pre'].'_admin_userid'] && $_COOKIE[$set['main']['cookie_pre'].'_admin_password'] ) {
			$tabledata[$i]['EDITLINK']=HTTPDIR.'admin/action.php?action=guestbook.edit&amp;id='.$res['id'];
			$tabledata[$i]['DELETELINK']=HTTPDIR.'admin/action.php?action=guestbook.del&amp;id='.$res['id'];
		}
	}
}


//Custom-Titel
for ( $i=1; $i<=5; $i++ ) {
	$apx->tmpl->assign('CUSTOM'.$i.'_NAME',$set['guestbook']['cusfield_names'][$i-1]);
}

$postto=mklink(
	'guestbook.php?add=1',
	'guestbook,add.html'
);

//Captcha erstellen
if ( $set['guestbook']['captcha'] && !$user->info['userid'] ) {
	require(BASEDIR.'lib/class.captcha.php');
	$captcha=new captcha;
	$captchacode=$captcha->generate();
}

$apx->tmpl->assign('CAPCHA',$captchacode); //Abwärtskompatiblität
$apx->tmpl->assign('CAPTCHA',$captchacode);
$apx->tmpl->assign('LINK_SIGN',$postto);
$apx->tmpl->assign('POSTTO',$postto);
$apx->tmpl->assign('ENTRY',$tabledata);
$apx->tmpl->parse('guestbook');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>