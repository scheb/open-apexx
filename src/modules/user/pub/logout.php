<?php

$apx->lang->drop('logout');
setcookie($set['main']['cookie_pre'].'_userid','',time()-100*24*3600,'/');
setcookie($set['main']['cookie_pre'].'_password','',time()-100*24*3600,'/');

//Auth-Cookies löschen
foreach ( $_COOKIE AS $name => $value ) {
	if ( substr($name,0,16)=='usergallery_pwd_' ) {
		setcookie($name,'',time()-99999);
	}
}

//Weiterleitung zur zuletzt besuchten Seite
$filter=array(
	'user,login.html',
	'user.php?action=login'
);
$refforward=true;
foreach ( $filter AS $url ) {
	if ( strpos($_SERVER['HTTP_REFERER'],$url)!==false ) {
		$refforward=false;
		break;
	}
}
if ( $refforward && $_SERVER['HTTP_REFERER'] ) $goto=$_SERVER['HTTP_REFERER'];
else $goto=mklink('user.php','user.html');

message($apx->lang->get('MSG_OK'),$goto);

?>