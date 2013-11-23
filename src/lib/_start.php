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

//////////////////////////////////////////////////////////////////////////////////////////// SYSTEMSTART

//BENCHMARK
$_BENCH=microtime();

define('MODE','public');
define('BASEDIR',dirname(dirname(__file__)).'/');
$set=array(); //Variable schützen

//Setup suchen
if ( file_exists(BASEDIR.'setup/index.php') ) die('Bitte löschen Sie zuerst den Ordner "setup"!');

//Globale Module und Funktionen laden
require_once(BASEDIR.'lib/config.php');
require_once(BASEDIR.'lib/path.php');
require_once(BASEDIR.'lib/functions.php');
require_once(BASEDIR.'lib/functions.public.php');
require_once(BASEDIR.'lib/class.apexx.php');
require_once(BASEDIR.'lib/class.apexx.public.php');
require_once(BASEDIR.'lib/class.database.php');
require_once(BASEDIR.'lib/class.tengine.php');
require_once(BASEDIR.'lib/class.templates.public.php');
require_once(BASEDIR.'lib/class.language.php');


//Datenbank Verbindung aufbauen
define('PRE',$set['mysql_pre']);
$db = new database($set['mysql_server'], $set['mysql_user'], $set['mysql_pwd'], $set['mysql_db'], $set['mysql_utf8']);

//apexx-Klasse laden
$apx = new apexx_public;

//Sprach-Klasse
$apx->lang = new language;
$apx->lang->langid($apx->language_default);

//Template Engine
$apx->tmpl = new templates();


//Modul-Funktionen laden
foreach ( $apx->modules AS $module => $info ) {
	include_once(BASEDIR.getmodulepath($module).'system.php');
}


//Sektionen initialisieren
$apx->init_section();

//Sprachpaket initialisieren
$apx->lang->init();


//Modul-Startup durchführen
foreach ( $apx->modules AS $module => $info ) {
	include_once(BASEDIR.getmodulepath($module).'startup.php');
}


?>