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
define('BASEDIR',dirname(dirname(dirname(__file__))).'/');
define('BASEREL','../../');
define('DEBUG',false);
$set=array(); //Variable schützen

@set_time_limit(600);

ob_start();
if ( !$_REQUEST['hash'] ) die('missing HASH!');

//Setup suchen
if ( file_exists(BASEDIR.'setup/index.php') ) die('Bitte löschen Sie zuerst den Ordner "setup"!');


//Datenbank Verbindung aufbauen
require_once(BASEDIR.'lib/config.php');
require_once(BASEDIR.'lib/functions.php');
require_once(BASEDIR.'lib/class.database.php');
define('PRE',$set['mysql_pre']);
$db = new database($set['mysql_server'], $set['mysql_user'], $set['mysql_pwd'], $set['mysql_db'], $set['mysql_utf8']);


//Wenn kein Fehler aufgetreten ist, dann wird ein Leerpixel ausgegeben
function cron_finish() {
	if ( DEBUG!=true ) {
		ob_end_clean();
		header('Content-type: image/gif');
		readfile('cronimage.dat');
	}
	exit;
}


//Auf Cronjobs prüfen
$crons=$db->fetch("SELECT * FROM ".PRE."_cron WHERE hash='".addslashes($_REQUEST['hash'])."'");
if ( !count($crons) ) cron_finish();

$cronfuncs=array();
$loadmodules=array();

foreach ( $crons AS $res ) {
	$cronfuncs[]=array(
		$res['funcname'],
		$res['lastexec']
	);
	$loadmodule[]=$res['module'];
	$diff=floor((time()-$res['lastexec'])/$res['period'])*$res['period'];
	if ( DEBUG!=true ) $db->query("UPDATE ".PRE."_cron SET lastexec=lastexec+'".$diff."',hash='' WHERE funcname='".addslashes($res['funcname'])."' AND hash='".addslashes($_REQUEST['hash'])."' LIMIT 1");
}



///////////////////////////////////////////////////////////////////////////////////////////////////

//Weitere Dateien nachladen
require_once(BASEDIR.'lib/path.php');
require_once(BASEDIR.'lib/functions.php');
require_once(BASEDIR.'lib/functions.public.php');
require_once(BASEDIR.'lib/class.apexx.php');
require_once(BASEDIR.'lib/class.apexx.public.php');
require_once(BASEDIR.'lib/class.language.php');

//apexx-Klasse laden
$apx  = new apexx_public;

//Sprach-Klasse
$apx->lang = new language;
$apx->lang->langid($apx->language_default);
$apx->lang->init();


//Funktionen laden
foreach ( $loadmodule AS $module ) {
	if ( !file_exists(BASEDIR.getmodulepath($module).'cron.php') ) continue;
	include_once(BASEDIR.getmodulepath($module).'cron.php');
}

//Funktionen ausführen (Timestamp wird übergeben)
foreach ( $cronfuncs AS $info ) {
	$funcname='cron_'.$info[0];
	if ( function_exists($funcname) ) {
		$funcname($info[1]);
	}
}



///////////////////////////////////////////////////////////////////////////////////////////////////

//MySQL Verbindung schließen
$db->close();

cron_finish();

?>