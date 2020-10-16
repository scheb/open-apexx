<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


////////////////////////////////////////////////////////////////////////////////////////////


//Statische Variablen setzen
$apx->tmpl->assign_static('LOGGED_ID',$user->info['userid']);
if ( $user->info['userid'] ) {
	
	$apx->tmpl->assign_static('LOGGED_USERNAME',replace($user->info['username']));
	$apx->tmpl->assign_static('LOGGED_EMAIL',replace($user->info['email']));
	$apx->tmpl->assign_static('LOGGED_GROUPID',$user->info['groupid']);
	$apx->tmpl->assign_static('LOGGED_GROUPNAME',replace($user->info['name']));
	$apx->tmpl->assign_static('LOGGED_ISTEAM',$user->is_team_member());
	$apx->tmpl->assign_static('LOGGED_PROFILE',$user->mkprofile($user->info['userid'],$user->info['username']));
}


?>