<?php 

define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->module('user');
$apx->lang->drop('all');
headline($apx->lang->get('HEADLINE'),mklink('user.php','user.html'));
titlebar($apx->lang->get('HEADLINE'));

//Alte PNs der User löschen
$db->query("DELETE FROM ".PRE."_user_pms WHERE ( del_to='1' AND del_from='1' )");

//Funktionen laden
include(BASEDIR.getmodulepath('user').'citymatch.php');
include(BASEDIR.getmodulepath('user').'functions.php');
include(BASEDIR.getmodulepath('comments').'functions.php');


////////////////////////////////////////////////////////////////////////////////////////// LOGOUT

$publicFunc = array(
	'logout',
	'profile',
	'newmail',
	'guestbook',
	'blog',
	'gallery',
	'collection',
	'report',
	'list',
	'search',
	'online',
	'usermap'
);

$userFunc = array(
	'myprofile',
	'setstatus',
	'signature',
	'avatar',
	'pms',
	'newpm',
	'readpm',
	'delpm',
	'ignorelist',
	'friends',
	'addbuddy',
	'delbuddy',
	'addbookmark',
	'delbookmark',
	'myblog',
	'mygallery',
	'subscriptions',
	'subscribe'
);

$guestFunc = array(
	'register',
	'activate',
	'getregkey',
	'getpwd'
);


////////////////////////////////////////////////////////////////////////////////////////// ÖFFENTLICHE FUNKTIONEN

if ( in_array($_REQUEST['action'], $publicFunc) ) {
	require(BASEDIR.getmodulepath('user').'pub/'.$_REQUEST['action'].'.php');
}



////////////////////////////////////////////////////////////////////////////////////////// USER-FUNKTIONEN

elseif ( $user->info['userid'] ) {
	if ( in_array($_REQUEST['action'], $userFunc) ) {
		require(BASEDIR.getmodulepath('user').'pub/'.$_REQUEST['action'].'.php');
	}
	else {
		require(BASEDIR.getmodulepath('user').'pub/index.php');
	}
	
}


////////////////////////////////////////////////////////////////////////////////////////// GAST-FUNKTIONEN

elseif ( !$user->info['userid'] ) {
	if ( in_array($_REQUEST['action'], $guestFunc) ) {
		require(BASEDIR.getmodulepath('user').'pub/'.$_REQUEST['action'].'.php');
	}
	else {
		require(BASEDIR.getmodulepath('user').'pub/login.php');
	}
}


////////////////////////////////////////////////////////////////////////////////////////// 404

else {
	filenotfound();
}


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>