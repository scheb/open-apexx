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



//BENCHMARK
$_BENCH=microtime();

define('MODE','admin');
define('BASEDIR',dirname(dirname(dirname(__file__))).'/');
define('BASEREL','../');
$set=array(); //Variable schützen

//Setup suchen
if ( file_exists(BASEDIR.'setup/index.php') ) die('Bitte löschen Sie zuerst den Ordner "setup"!');

//Globale Module und Funktionen laden
require_once(BASEDIR.'lib/config.php');
require_once(BASEDIR.'lib/path.php');
require_once(BASEDIR.'lib/functions.php');
require_once(BASEDIR.'lib/functions.admin.php');
require_once(BASEDIR.'lib/class.apexx.php');
require_once(BASEDIR.'lib/class.apexx.admin.php');
require_once(BASEDIR.'lib/class.database.php');
require_once(BASEDIR.'lib/class.tengine.php');
require_once(BASEDIR.'lib/class.templates.admin.php');
require_once(BASEDIR.'lib/class.language.php');
require_once(BASEDIR.'lib/class.session.php');
require_once(BASEDIR.'lib/class.html.php');


//Datenbank Verbindung aufbauen
define('PRE',$set['mysql_pre']);
$db = new database($set['mysql_server'], $set['mysql_user'], $set['mysql_pwd'], $set['mysql_db'], $set['mysql_utf8']);

//Apexx-Klasse initialisieren
$apx  = new apexx_admin;
$apx->lang = new language;   //Sprache
$apx->lang->langid($apx->language_default); //Standard-Sprachpaket

//Session starten
$apx->session = new session('sid');
$token = $apx->session->get('sectoken');
if ( !$token ) {
	$apx->session->set('sectoken', md5(microtime().rand()));
}

//Sektionswähler
if ( isset($_GET['selectsection']) ) {
	if ( isset($apx->sections[$_GET['selectsection']]) ) {
		$apx->session->set('section', $_GET['selectsection']);
	}
	else {
		$apx->session->set('section', 0);
	}
}


//Modul-Funktionen laden
foreach ( $apx->modules AS $module => $info ) {
	if ( !file_exists(BASEDIR.getmodulepath($module).'admin_system.php') ) continue;
	include_once(BASEDIR.getmodulepath($module).'admin_system.php');
}


$apx->lang->init();          //Sprachpakete initialisieren
$apx->tmpl = new templates;  //Templates
$html = new html;            //HTML Klasse für Admin

?>