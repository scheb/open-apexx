<?php

if ( !$set['user']['guestbook'] ) die('function disabled!');

//Nur für Registrierte
if ( $set['user']['profile_regonly'] && !$user->info['userid'] ) {
	tmessage('profileregonly',array(),false,false);
	require('lib/_end.php');
}

//Eintrag löschen
$_REQUEST['del'] = (int)$_REQUEST['del'];
if ( $user->info['userid'] && $_REQUEST['del'] ) {
	$apx->lang->drop('guestbook');
	
	if ( $_POST['del'] ) {
		$db->query("DELETE FROM ".PRE."_user_guestbook WHERE id='".$_POST['del']."' AND owner='".$user->info['userid']."' LIMIT 1");
		$goto = mklink(
			'user.php?action=guestbook&amp;id='.$user->info['userid'],
			'user,guestbook,'.$user->info['userid'].',1.html'
		);
		message($apx->lang->get('MSG_DEL_OK'),$goto);
	}
	else {
		tmessage('delguestbook',array('ID'=>$_REQUEST['del']));
	}
	return;
}

////////////////////

$_REQUEST['id']=(int)$_REQUEST['id'];
if ( !$_REQUEST['id'] ) die('missing ID!');
$apx->lang->drop('guestbook');
headline($apx->lang->get('HEADLINE_GUESTBOOK'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_GUESTBOOK'));

//Benutzernamen auslesen
$profileInfo = $db->first("SELECT userid,username,pub_usegb,pub_profileforfriends FROM ".PRE."_user WHERE userid='".$_REQUEST['id']."' LIMIT 1");
list($userid,$username,$usegb,$friendonly) = $profileInfo;
$apx->tmpl->assign('USERID',$userid);
$apx->tmpl->assign('USERNAME',replace($username));

//Nur für Freunde
if ( $friendonly && !$user->is_buddy_of($userid) && $user->info['userid']!=$userid && $user->info['groupid']!=1 ) {
	message($apx->lang->get('MSG_FRIENDSONLY'));
	require('lib/_end.php');
}

//Gästebuch vom Benutzer deaktiviert
if ( !$usegb ) {
	message($apx->lang->get('MSG_DISABLED'));
	require('lib/_end.php');
}

//Gästebuch nur für Freunde
elseif ( $usegb==2 && $user->info['userid']!=$userid && $user->info['groupid']!=1 ) {
	if ( $user->info['userid'] ) list($isbuddy) = $db->first("SELECT friendid FROM ".PRE."_user_friends WHERE userid='".$_REQUEST['id']."' AND friendid='".$user->info['userid']."' LIMIT 1");
	if ( !$isbuddy ) {
		message($apx->lang->get('MSG_FRIENDSONLY'));
		require('lib/_end.php');
	}
}

//Neuer Eintrag
if ( $_REQUEST['add'] ) {
	
	//Auf Existenz prüfen
	if ( $_REQUEST['id']!=$user->info['userid'] ) {
		list($check) = $db->first("SELECT userid FROM ".PRE."_user WHERE userid='".$_REQUEST['id']."' LIMIT 1");
		if ( !$check ) die('invalid userid!');
	}
	
	if ( !$user->info['userid'] ) die('please log in!');
	elseif ( !$_POST['text'] || ( $set['user']['guestbook_req_title'] && !$_POST['title'] ) ) message('back');
	elseif ( $user->ignore($_REQUEST['id'],$reason) ) {
		if ( $reason ) message($apx->lang->get('MSG_IGNORED_REASON',array('REASON'=>$reason)),'javascript:history.back()');
		else message($apx->lang->get('MSG_IGNORED'),'javascript:history.back()');
	}
	elseif ( $set['user']['guestbook_entrymaxlen'] && strlen($_POST['text'])>$set['user']['guestbook_entrymaxlen'] ) message($apx->lang->get('MSG_TOOLONG'),'back');
	elseif ( ($spam+$set['user']['guestbook_spamprot']*60)>time() ) message($apx->lang->get('MSG_BLOCKSPAM',array('SEC'=>($spam+$set['user']['guestbook_spamprot']*60)-time())),'back');
	else {
		
		$_POST['owner']=$_REQUEST['id'];
		$_POST['userid']=$user->info['userid'];
		$_POST['time']=time();
		$_POST['ip']=get_remoteaddr();
		
		$db->dinsert(PRE.'_user_guestbook','owner,userid,username,title,text,time,ip');
		
		//eMail-Benachrichtigung an Gästebuch-Besitzer
		if ( $_REQUEST['id']!=$user->info['userid'] ) {
			list($sendmail,$email) = $db->first("SELECT pub_gbmail,email FROM ".PRE."_user WHERE userid='".$_REQUEST['id']."' LIMIT 1");
			if ( $sendmail ) {
				$input=array(
					'URL' => HTTP,
					'USERNAME' => $user->info['username'],
					'GOTO' => HTTP_HOST.mklink('user.php?action=guestbook&id='.$_REQUEST['id'],'user,guestbook,'.$_REQUEST['id'].',1.html')
				);
				sendmail($email,'SENDENTRY',$input);
			}
		}
		
		//Weiterleitung
		message($apx->lang->get('MSG_OK'),mklink(
			'user.php?action=guestbook&amp;id='.$_REQUEST['id'],
			'user,guestbook,'.$_REQUEST['id'].',1.html')
		);
	}
}

//Gästebuch zeigen
else {
	
	//Seitenzahlen
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_user_guestbook WHERE owner='".$_REQUEST['id']."'");
	pages(
		mklink(
			'user.php?action=guestbook&amp;id='.$_REQUEST['id'],
			'user,guestbook,'.$_REQUEST['id'].',{P}.html'
		),
		$count,
		$set['user']['guestbook_epp']
	);
	
	
	//Einträge auslesen
	$tabledata = array();
	$data=$db->fetch("SELECT * FROM ".PRE."_user_guestbook WHERE owner='".$_REQUEST['id']."' ORDER BY time DESC ".getlimit($set['user']['guestbook_epp']));
	$entrynumber=$count-($_REQUEST['p']-1)*$set['user']['guestbook_epp'];
	
	//Benutzer-Info
	$userids = get_ids($data,'userid');
	$userinfo = array();
	if ( count($userids) ) {
		$userinfo = $user->get_info_multi($userids,'username,email,pub_hidemail,homepage,avatar,avatar_title,signature,lastactive,pub_invisible');
	}
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Benutzer
			$tabledata[$i]['USERID']=$res['userid'];
			$tabledata[$i]['NAME']=replace($userinfo[$res['userid']]['username']);
			$tabledata[$i]['EMAIL']=replace(iif(!$userinfo[$res['userid']]['pub_hidemail'],$userinfo[$res['userid']]['email']));
			$tabledata[$i]['EMAIL_ENCRYPTED']=replace(iif(!$userinfo[$res['userid']]['pub_hidemail'],cryptMail($userinfo[$res['userid']]['email'])));
			$tabledata[$i]['HOMEPAGE']=replace($userinfo[$res['userid']]['homepage']);
			$tabledata[$i]['AVATAR']=$user->mkavatar($userinfo[$res['userid']]);
			$tabledata[$i]['AVATAR_TITLE']=$user->mkavtitle($userinfo[$res['userid']]);
			$tabledata[$i]['SIGNATURE']=$user->mksig($userinfo[$res['userid']]);
			$tabledata[$i]['ONLINE'] = iif(!$res['pub_invisible'] && ($res['lastactive']+$set['user']['timeout']*60)>=time(),1,0);
			$tabledata[$i]['ISONLINE'] = $tabledata[$i]['ONLINE'];
			
			//Text
			$text=$res['text'];
			if ( $set['user']['guestbook_breakline'] ) $text=wordwrap($text,$set['user']['guestbook_breakline'],"\n",1);
			if ( $set['user']['guestbook_badwords'] ) $text=badwords($text);
			$text=replace($text,1);
			if ( $set['user']['guestbook_allowsmilies'] ) $text=dbsmilies($text);
			if ( $set['user']['guestbook_allowcode'] ) $text=dbcodes($text);
			
			//Titel
			$title=$res['title'];
			if ( $set['user']['guestbook_breakline'] ) $title=wordwrap($title,$set['user']['guestbook_breakline'],"\n",1);
			if ( $set['user']['guestbook_badwords'] ) $title=badwords($title);
			$title=replace($title);
			
			$tabledata[$i]['TEXT']=$text;
			$tabledata[$i]['TITLE']=$title;
			$tabledata[$i]['TIME']=$res['time'];
			$tabledata[$i]['NUMBER']=$entrynumber--;
			
			//Admin-Links
			if ( $set['user']['guestbook_useradmin'] && $_REQUEST['id']==$user->info['userid'] ) {
				$tabledata[$i]['DELETELINK']=mklink(
					'user.php?action=guestbook&amp;del='.$res['id'],
					'user.php?action=guestbook&amp;del='.$res['id']
				);
			}
			if ( $_REQUEST['id']==$user->info['userid'] ) {
				$tabledata[$i]['IGNORELINK']=mklink(
					'user.php?action=ignorelist&amp;add=1&amp;username='.urlencode($userinfo[$res['userid']]['username']),
					'user,ignorelist.html?add=1&amp;username='.urlencode($userinfo[$res['userid']]['username'])
				);
			}
			
		}
	}
	
	//Formular senden an
	$postto=mklink(
		'user.php?action=guestbook&amp;id='.$_REQUEST['id'].'&amp;add=1',
		'user,guestbook,'.$_REQUEST['id'].',1.html?add=1'
	);
	
	//Links zu den Profil-Funktionen
	user_assign_profile_links($apx->tmpl, $profileInfo);
	
	$apx->tmpl->assign('POSTTO',$postto);
	$apx->tmpl->assign('ENTRY',$tabledata);
	$apx->tmpl->parse('guestbook');
}

?>