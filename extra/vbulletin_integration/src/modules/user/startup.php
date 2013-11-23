<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2007, Christian Scheb            |
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
if ( $user->info['userid'] ) {
	
	$apx->tmpl->assign_static('LOGGED_USERNAME',replace($user->info['username']));
	$apx->tmpl->assign_static('LOGGED_EMAIL',replace($user->info['email']));
	$apx->tmpl->assign_static('LOGGED_GROUPID',$user->info['groupid']);
	$apx->tmpl->assign_static('LOGGED_GROUPNAME',replace($user->info['name']));
	$apx->tmpl->assign_static('LOGGED_ISTEAM',$user->is_team_member());
	$apx->tmpl->assign_static('LOGGED_PROFILE',$user->mkprofile($user->info['userid'],$user->info['username']));
}


?>