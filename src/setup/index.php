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



define('APXRUN',true);
define('MODE','public');
define('BASEDIR',dirname(dirname(__file__)).'/');
$set=array(); //Variable sch�tzen

set_time_limit(600);

//Globale Module und Funktionen laden
require_once(BASEDIR.'lib/config.php');
require_once(BASEDIR.'lib/path.php');
require_once(BASEDIR.'lib/functions.php');
require_once(BASEDIR.'lib/functions.public.php');
require_once(BASEDIR.'setup/class.apexx.php');
require_once(BASEDIR.'lib/class.database.php');
require_once(BASEDIR.'lib/class.tengine.php');
require_once(BASEDIR.'lib/class.templates.public.php');
require_once(BASEDIR.'lib/class.language.php');

if ( !is_writeable(BASEDIR.getpath('cache')) ) die('cache-Ordner hat keine Schreibrechte!');

//Datenbank Verbindung aufbauen
define('PRE',$set['mysql_pre']);
$db = new database($set['mysql_server'], $set['mysql_user'], $set['mysql_pwd'], $set['mysql_db'], $set['mysql_utf8']);

//apexx-Klasse laden
$apx  = new apexx;

//Sprach-Klasse
$apx->lang = new language;
$apx->lang->langid($apx->language_default);
$apx->lang->init();

//Template Engine
$apx->tmpl = new templates();

//Pfade �berschreiben
$pathcfg['tmpl_base_public']    = 'setup/';

//Setup-Variablen
$_REQUEST['step']=(int)$_REQUEST['step'];
if ( !$_REQUEST['step'] ) $_REQUEST['step']=1;
$apx->tmpl->assign_static('STEP',$_REQUEST['step']);



///////////////////////////////////////////////////////////////////////////////////////////// ENDE

if ( $_REQUEST['finish'] ) {
	$apx->tmpl->overwrite('STEP',999);
	$apx->tmpl->parse('finish','/');
}



///////////////////////////////////////////////////////////////////////////////////////////// SCHRITT 3

elseif ( $_REQUEST['step']==4 ) {
	if ( $_POST['next'] ) {
		if ( !is_array($_POST['modules']) ) $_POST['modules']=array();
		$_POST['modules']=array_merge($_POST['modules']);
		define('SETUPMODE','install');
		
		foreach ( $_POST['modules'] AS $modulename ) {
			if ( !is_dir(BASEDIR.getmodulepath($modulename)) ) continue;
			
			//Modul-Info auslesen
			include(BASEDIR.getmodulepath($modulename).'init.php');
			$info=$module;
			$version=intval(str_replace('.','',$info['version']));
			unset($module);
			
			$db->query("UPDATE ".PRE."_modules SET installed='1',active='1',version='".$version."' WHERE module='".addslashes($modulename)."' LIMIT 1");
			
			//Modul-Setup durchf�hren
			if ( !file_exists(BASEDIR.getmodulepath($modulename).'setup.php') ) continue;
			include(BASEDIR.getmodulepath($modulename).'setup.php');
		}
		
		header("HTTP/1.1 301 Moved Permanently");
		header('location:index.php?finish=1');
		exit;
	}
	
	
	//Module in DB schreiben
	$handle=opendir(BASEDIR.getpath('moduledir'));
	while ( $file=readdir($handle) ) {
		if ( $file=='.' || $file=='..' ) continue;
		if ( !is_dir(BASEDIR.getpath('moduledir').$file) ) continue;
		if ( in_array($file,$apx->coremodules) ) continue; //Core-Module �berspringen
		$dirs[]=$file;
		$db->query("INSERT INTO ".PRE."_modules (module) VALUES ('".addslashes($file)."')");
	}
	closedir($handle);
	
	//Auswahlliste f�r Setup generieren
	foreach ( $dirs AS $module ) {
		if ( in_array($module,$apx->coremodules) ) continue;
		++$i;
		$tabledata[$i]['ID']=$module;
	}
	
	$apx->tmpl->assign('MODULE',$tabledata);
	$apx->tmpl->parse('step4','/');
}



///////////////////////////////////////////////////////////////////////////////////////////// SCHRITT 3

elseif ( $_REQUEST['step']==3 ) {
	if ( $_POST['next'] ) {
		if ( !$_POST['username_login'] || !$_POST['username'] || !$_POST['pwd1'] || !$_POST['pwd2'] ) message('back');
		elseif ( $_POST['pwd1']!=$_POST['pwd2'] ) message('Passwort und Passwort-Wiederholung stimmen nicht �berein!','back');
		else {
			$_POST['reg_time']=$_POST['lastonline']=$_POST['lastactive']=time();
			$_POST['reg_email']=$_POST['email'];
			$_POST['salt']=random_string();
			$_POST['password']=md5(md5($_POST['pwd1']).$_POST['salt']);
			$_POST['groupid']=1;
			$_POST['admin_editor']=1;
			$_POST['admin_lang']=$_POST['pub_lang']='de';
			$_POST['active']=1;
			
			$db->dinsert(PRE.'_user','username,username_login,password,salt,email,reg_time,reg_email,groupid,active,lastonline,lastactive,admin_editor,admin_lang,pub_lang');
			
			header("HTTP/1.1 301 Moved Permanently");
			header('location:index.php?step=4');
			exit;
		}
	}
	
	$apx->tmpl->assign('USERNAME',compatible_hsc($_POST['username']));
	$apx->tmpl->assign('USERNAME_LOGIN',compatible_hsc($_POST['username_login']));
	$apx->tmpl->assign('EMAIL',compatible_hsc($_POST['email']));
	
	$apx->tmpl->parse('step3','/');
}



///////////////////////////////////////////////////////////////////////////////////////////// SCHRITT 2

elseif ( $_REQUEST['step']==2 ) {
	if ( $_POST['next'] ) {
		header("HTTP/1.1 301 Moved Permanently");
		header('location:index.php?step=3');
		exit;
	}
	
	define('SETUPMODE','install');
	foreach ( $apx->coremodules AS $modulename ) {
		
		//Modul-Info auslesen
		require(BASEDIR.getmodulepath($modulename).'init.php');
		$info=$module;
		$version=intval(str_replace('.','',$info['version']));
		unset($module);
		
		//Setup ausf�hren
		require(BASEDIR.getmodulepath($modulename).'setup.php');
		
		$db->query("INSERT INTO ".PRE."_modules (module,installed,active,version) VALUES ('".addslashes($modulename)."','1','1','".$version."')");
	}
	
	$apx->tmpl->parse('step2','/');
}



///////////////////////////////////////////////////////////////////////////////////////////// SCHRITT 1

else {
	if ( $_POST['next'] ) {
		header("HTTP/1.1 301 Moved Permanently");
		header('location:index.php?step=2');
		exit;
	}
	
	if ( is_writeable(BASEDIR.getpath('uploads')) ) $apx->tmpl->assign('WRITEABLE_UPLOADS',1);
	$apx->tmpl->parse('step1','/');
}



////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////



//Ausgabe vorbereiten
$apx->tmpl->out();

//MySQL Verbindung schlie�en
$db->close();

//Cache l�schen wenn Setup abgeschlossen
if ( $_REQUEST['finish'] ) {
	$apx->tmpl->clear_cache();
}


?>
