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


//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


////////////////////////////////////////////////////////////////////////////////////////////


//Statische Variablen setzen
$apx->tmpl->assign_static('LOGGED_ID',$user->info['userid']);
$apx->tmpl->assign_static('LOGGED_GROUPID',$user->info['groupid']);
$apx->tmpl->assign_static('LOGGED_GROUPNAME',replace($user->info['name']));
if ( $user->info['userid'] ) {
	
	$apx->tmpl->assign_static('LOGGED_USERNAME',replace($user->info['username']));
	$apx->tmpl->assign_static('LOGGED_EMAIL',replace($user->info['email']));
	$apx->tmpl->assign_static('LOGGED_EMAIL_ENCRYPTED',replace(cryptMail($user->info['email'])));
	$apx->tmpl->assign_static('LOGGED_ISTEAM',$user->is_team_member());
	$apx->tmpl->assign_static('LOGGED_PROFILE',$user->mkprofile($user->info['userid'],$user->info['username']));
	
	//Noch mehr davon
	$apx->tmpl->assign_static('LOGGED_ICQ',replace($user->info['icq']));
	$apx->tmpl->assign_static('LOGGED_AIM',replace($user->info['aim']));
	$apx->tmpl->assign_static('LOGGED_YIM',replace($user->info['yim']));
	$apx->tmpl->assign_static('LOGGED_MSN',replace($user->info['msn']));
	$apx->tmpl->assign_static('LOGGED_SKYPE',$user->info['skype']);
	$apx->tmpl->assign_static('LOGGED_HOMEPAGE',replace($user->info['homepage']));
	$apx->tmpl->assign_static('LOGGED_REALNAME',replace($user->info['realname']));
	$apx->tmpl->assign_static('LOGGED_GENDER',$user->info['gender']);
	$apx->tmpl->assign_static('LOGGED_CITY',replace($user->info['city']));
	$apx->tmpl->assign_static('LOGGED_PLZ',replace($user->info['plz']));
	$apx->tmpl->assign_static('LOGGED_COUNTRY',$user->info['country']);
	$apx->tmpl->assign_static('LOGGED_INTERESTS',replace($user->info['interests']));
	$apx->tmpl->assign_static('LOGGED_WORK',replace($user->info['work']));
	$apx->tmpl->assign_static('LOGGED_LASTVISIT',$user->info['lastonline']);
	$apx->tmpl->assign_static('LOGGED_SIGNATURE',$user->mksig($user->info));
	$apx->tmpl->assign_static('LOGGED_AVATAR',$user->mkavatar($user->info));
	$apx->tmpl->assign_static('LOGGED_AVATAR_TITLE',$user->mkavtitle($user->info));
}


//Theme erzwingen
if ( $user->info['pub_theme'] ) {
	$apx->tmpl->set_theme($user->info['pub_theme']);
}


?>