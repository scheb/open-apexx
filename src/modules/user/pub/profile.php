<?php

$_REQUEST['id']=(int)$_REQUEST['id'];
if ( !$_REQUEST['id'] ) die('missing ID!');
$apx->lang->drop('profile');
headline($apx->lang->get('HEADLINE_PROFILE'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_PROFILE'));

//Nur für Registrierte
if ( $set['user']['profile_regonly'] && !$user->info['userid'] ) {
	tmessage('profileregonly',array(),false,false);
	require('lib/_end.php');
}

//Userinfo auslesen
$res=$db->first("SELECT * FROM ".PRE."_user WHERE userid='".$_REQUEST['id']."' LIMIT 1");
$userid = $res['userid'];
if ( !$res['userid'] ) filenotfound();

//Nur für Freunde
if ( $res['pub_profileforfriends'] && !$user->is_buddy_of($res['userid']) && $user->info['userid']!=$res['userid'] && $user->info['groupid']!=1 ) {
	message($apx->lang->get('MSG_FRIENDSONLY'));
	require('lib/_end.php');
}

//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('profile');

//Besucher aufzeichnen und ausgeben
if ( in_array('VISITOR',$parse) ) {
	if ( $_REQUEST['id']!=$user->info['userid'] ) {
		user_count_visit('profile',$_REQUEST['id']);
	}
	if ( !$set['user']['visitorself'] || $_REQUEST['id']==$user->info['userid'] ) {
		user_assign_visitors('profile',$_REQUEST['id'],$apx->tmpl,$parse);
	}
}

//Gruppennamen auslesen
list($groupname)=$db->first("SELECT name FROM ".PRE."_user_groups WHERE groupid='".$res['groupid']."' LIMIT 1");

$age = 0;
if ( $res['birthday'] ) {
	$bd=explode('-',$res['birthday']);
	$birthday=intval($bd[0]).'. '.getcalmonth($bd[1]).iif($bd[2],' '.$bd[2]);
	if ( $bd[2] ) {
		$age = date('Y')-$bd[2];
		if ( intval(sprintf('%02d%02d', $bd[1], $bd[0]))>intval(date('md')) ) {
			$age -= 1;
		}
	}
}

//Status-Smiley
$statusSmileyPath = '';
foreach ( $set['main']['smilies'] AS $smiley ) {
	if ( $smiley['code']==$res['status_smiley'] ) {
		$statusSmileyPath = $smiley['file'];
		break;
	}
}

$apx->tmpl->assign('USERID',$res['userid']);
$apx->tmpl->assign('USERNAME',replace($res['username']));
$apx->tmpl->assign('GROUP',replace($groupname));
$apx->tmpl->assign('REGDATE',$res['reg_time']);
$apx->tmpl->assign('REGDAYS',floor((time()-$res['reg_time'])/(24*3600)));
$apx->tmpl->assign('LASTACTIVE',(int)$res['lastactive']);
$apx->tmpl->assign('IS_ONLINE',iif(!$res['pub_invisible'] && ($res['lastactive']+$set['user']['timeout']*60)>=time(),1,0));
$apx->tmpl->assign('EMAIL',replace($res['email']));
$apx->tmpl->assign('EMAIL_ENCRYPTED',cryptMail($res['email']));
$apx->tmpl->assign('HIDEMAIL',$res['pub_hidemail']);
$apx->tmpl->assign('STATUS',replace($res['status']));
$apx->tmpl->assign('STATUS_SMILEY',compatible_hsc($statusSmileyPath));
$apx->tmpl->assign('STATUS_SMILEY_CODE',replace($res['status_smiley']));
$apx->tmpl->assign('HOMEPAGE',replace($res['homepage']));
$apx->tmpl->assign('ICQ',replace($res['icq']));
$apx->tmpl->assign('AIM',replace($res['aim']));
$apx->tmpl->assign('YIM',replace($res['yim']));
$apx->tmpl->assign('MSN',replace($res['msn']));
$apx->tmpl->assign('SKYPE',replace($res['skype']));
$apx->tmpl->assign('REALNAME',replace($res['realname']));
$apx->tmpl->assign('CITY',replace($res['city']));
$apx->tmpl->assign('PLZ',replace($res['plz']));
$apx->tmpl->assign('COUNTRY',replace($res['country']));
$apx->tmpl->assign('INTERESTS',replace($res['interests']));
$apx->tmpl->assign('WORK',replace($res['work']));
$apx->tmpl->assign('GENDER',(int)$res['gender']);
$apx->tmpl->assign('BIRTHDAY',$birthday);
$apx->tmpl->assign('AGE',$age);
$apx->tmpl->assign('SIGNATURE',$user->mksig($res,1));
$apx->tmpl->assign('AVATAR',$user->mkavatar($res));
$apx->tmpl->assign('AVATAR_TITLE',$user->mkavtitle($res));

//Custom-Felder
for ( $i=1; $i<=10; $i++ ) {
	$apx->tmpl->assign('CUSTOM'.$i.'_NAME',replace($set['user']['cusfield_names'][($i-1)]));
	$apx->tmpl->assign('CUSTOM'.$i,replace($res['custom'.$i]));
}

//Forum-Variablen
if ( $apx->is_module('forum') ) {
	if ( $res['forum_lastactive']==0 ) $res['forum_lastactive']=$res['lastactive'];
	$apx->tmpl->assign('FORUM_LASTACTIVE',(int)$res['forum_lastactive']);
	$apx->tmpl->assign('FORUM_POSTS',(int)$res['forum_posts']);
	$apx->tmpl->assign('FORUM_FINDPOSTS',HTTPDIR.$set['forum']['directory'].'/search.php?send=1&author='.urlencode($res['username']));
}

//Kommentare
if ( $apx->is_module('comments') ) {
	$apx->tmpl->assign('COMMENTS',comments_count($res['userid']));
}

//Interaktionen
$link_buddy=iif($user->info['userid'] && $user->info['userid']!=$_REQUEST['id'] && !$user->is_buddy($res['userid']), mklink(
	'user.php?action=addbuddy&amp;id='.$res['userid'],
	'user,addbuddy,'.$res['userid'].'.html'
));
$link_sendpm=iif($user->info['userid'] && $user->info['userid']!=$_REQUEST['id'], mklink(
	'user.php?action=newpm&amp;touser='.$res['userid'],
	'user,newpm,'.$res['userid'].'.html'
));
$link_sendmail=iif(($user->info['userid'] || $set['user']['sendmail_guests']) && $user->info['userid']!=$_REQUEST['id'], mklink(
	'user.php?action=newmail&amp;touser='.$res['userid'],
	'user,newmail,'.$res['userid'].'.html'
));
$link_ignore=iif($user->info['userid'] && $user->info['userid']!=$_REQUEST['id'] && !$user->ignore($res['userid'],$reason),mklink(
	'user.php?action=ignorelist&amp;add=1&amp;username='.urlencode($res['username']),
	'user,ignorelist.html?add=1&amp;username='.urlencode($res['username'])
));
$apx->tmpl->assign('LINK_BUDDY',$link_buddy);
$apx->tmpl->assign('LINK_SENDPM',$link_sendpm);
$apx->tmpl->assign('LINK_SENDEMAIL',$link_sendmail);
$apx->tmpl->assign('LINK_IGNORE',$link_ignore);

//Links zu anderen Funktionen
user_assign_profile_links($apx->tmpl, $res);

//Inhalt melden
$link_report = "javascript:popupwin('user.php?action=report&amp;contentid=profile:".$_REQUEST['id']."',500,300);";
$apx->tmpl->assign('LINK_REPORT',$link_report);

//Buddyliste
$userdata = array();
if ( $res['pub_showbuddies'] && in_array('BUDDY', $parse) ) {
	$data = $db->fetch("SELECT friendid FROM ".PRE."_user_friends WHERE userid='".$res['userid']."'");
	$buddies = get_ids($data,'friendid');
	if ( count($buddies) ) {
		$data = $db->fetch("SELECT userid,username,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail FROM ".PRE."_user WHERE userid IN (".implode(',',$buddies).") ORDER BY username ASC");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				++$i;
				
				$age = 0;
				if ( $res['birthday'] ) {
					$bd=explode('-',$res['birthday']);
					$birthday=intval($bd[0]).'. '.getcalmonth($bd[1]).iif($bd[2],' '.$bd[2]);
					if ( $bd[2] ) {
						$age = date('Y')-$bd[2];
						if ( intval(sprintf('%02d%02d', $bd[1], $bd[0]))>intval(date('md')) ) {
							$age -= 1;
						}
					}
				}
				
				$tabledata[$i]['ID'] = $res['userid'];
				$tabledata[$i]['USERID'] = $res['userid'];
				$tabledata[$i]['NAME'] = replace($res['username']);
				$tabledata[$i]['USERNAME'] = replace($res['username']);
				$tabledata[$i]['GROUPID'] = $res['groupid'];
				$tabledata[$i]['EMAIL'] = replace(iif(!$res['pub_hidemail'],$res['email']));
				$tabledata[$i]['EMAIL_ENCRYPTED'] = replace(iif(!$res['pub_hidemail'],cryptMail($res['email'])));
				$tabledata[$i]['ONLINE'] = iif(!$res['pub_invisible'] && ($res['lastactive']+$set['user']['timeout']*60)>=time(),1,0);
				$tabledata[$i]['ISONLINE'] = $tabledata[$i]['ONLINE'];
				$tabledata[$i]['REALNAME'] = replace($res['realname']);
				$tabledata[$i]['GENDER'] = $res['gender'];
				$tabledata[$i]['CITY'] = replace($res['city']);
				$tabledata[$i]['PLZ'] = replace($res['plz']);
				$tabledata[$i]['COUNTRY'] = $res['country'];
				$tabledata[$i]['REGTIME']=$res['reg_time'];
				$tabledata[$i]['REGDAYS']=floor((time()-$res['reg_time'])/(24*3600));
				$tabledata[$i]['LASTACTIVE'] = $res['lastactive'];
				$tabledata[$i]['AVATAR'] = $user->mkavatar($res);
				$tabledata[$i]['AVATAR_TITLE'] = $user->mkavtitle($res);
				$tabledata[$i]['BIRTHDAY'] = $birthday;
				$tabledata[$i]['AGE'] = $age;
				if ( in_array('BUDDY.ISBUDDY', $parse) ) {
					$tabledata[$i]['ISBUDDY'] = $user->is_buddy($res['userid']);
				}
				
				//Interaktions-Links
				if ( $user->info['userid'] ) {
					$tabledata[$i]['LINK_SENDPM']=mklink(
						'user.php?action=newpm&amp;touser='.$res['userid'],
						'user,newpm,'.$res['userid'].'.html'
					);
					
					$tabledata[$i]['LINK_SENDEMAIL']=mklink(
						'user.php?action=newmail&amp;touser='.$res['userid'],
						'user,newmail,'.$res['userid'].'.html'
					);
					
					if ( in_array('BUDDY.LINK_BUDDY', $parse) && $userid!=$user->info['userid'] && !$user->is_buddy($res['userid']) ) {
						$tabledata[$i]['LINK_BUDDY']=mklink(
							'user.php?action=addbuddy&amp;id='.$res['userid'],
							'user,addbuddy,'.$res['userid'].'.html'
						);
					}
				}
				
			}
		}
	}
}
$apx->tmpl->assign('BUDDY',$tabledata);

$apx->tmpl->parse('profile');

?>