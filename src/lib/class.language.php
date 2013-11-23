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


class language {

var $langpack=array();
var $cached=array();
var $dropped=array();
var $loaded=array();
var $insertcache=array();

var $langdir;
var $lang;
var $langid;


////////////////////////////////////////////////////////////////////////////////// -> STARTUP


function init() {
	if ( !$this->langid ) die('can not load langpack, no langid defined!');
	
	$this->core_load();     //Core Sprachpakete in den Cache laden und ablegen
	
	if ( MODE=='admin' ) {
		$this->dropall('modulename'); //Diesen Teil aller Modul-Sprachpakete ablegen (Modul-Name -> für Navi)
		$this->dropall('navi');       //Diesen Teil aller Modul-Sprachpakete ablegen (Navigation)
		$this->dropall('titles');     //Diesen Teil aller Modul-Sprachpakete ablegen (Titel)
	}
}


////////////////////////////////////////////////////////////////////////////////// -> ALLGEMEINE FUNKTIONEN


//Sprachpaket wählen
function langid($id=false) {
	global $apx;
	if ( $id===false ) return $this->langid;
	
	if ( isset($apx->languages[$id]) ) {
		$this->langid=$id;
	}
}


//Pfad holen
function langpath($module) {
	global $apx;
	
	if ( $module=='/' ) $this->langdir=getpath('lang_base',array('MODULE'=>$module,'LANGID'=>$this->langid()));
	else $this->langdir=getpath('lang_modules',array('MODULE'=>$module,'LANGID'=>$this->langid()));
}


//Daten von Sprachpaket holen
function getpack($pack) {
	global $apx,$user;
	
	$langdir=$this->langdir;
	
	$langSystem = array();
	$langUser = array();
	
	//System-Sprachpaket laden
	if ( file_exists(BASEDIR.$langdir.$pack.'.php') ) {
		$lang = array();
		include_once(BASEDIR.$langdir.$pack.'.php');
		if ( is_array($lang) ) {
			$langSystem = $lang;
		}
	}
	else error('Sprachpaket '.$langdir.$pack.'.php nicht vorhanden!');
	
	//User-Sprachpaket laden
	if ( file_exists(BASEDIR.$langdir.$pack.'_byuser.php') ) {
		$lang = array();
		include_once(BASEDIR.$langdir.$pack.'_byuser.php');
		if ( is_array($lang) ) {
			$langUser = $lang;
		}
	}
	
	//Merge
	$lang = array_merge_recursive($langSystem, $langUser);
	
	/*else {
		//Paket der Standard-Sprache laden
		if ( file_exists(BASEDIR.$langdir.$apx->language_default.'/'.$pack.'.php') ) {
			include_once(BASEDIR.$langdir.$apx->language_default.'/'.$pack.'.php');
		}
		else error('Sprachpaket '.$pack.'.php nicht vorhanden!');
	}*/
	
	$this->lang=$lang;
	return $this->lang;
}


//Sprachpaket speichern
function cache($module) {
	$this->langpath($module);
	$this->getpack(MODE);
	$this->cached[$module]=$this->lang;
}



////////////////////////////////////////////////////////////////////////////////// -> PAKETE LADEN

//Core Sprachpaket laden
function core_load() {
	$this->langpath('/');
	
	$this->getpack('global');
	$this->mergepack();
	
	$this->getpack(MODE);
	$this->mergepack();
}


//Sprachpakete aller Module in den Speicher laden
function module_load($modulename) {
	global $apx;
	
	if ( !$this->is_loaded($modulename) ) {
		$this->cache($modulename);
		$this->set_loaded($modulename);
	}
}


//Sprachpaket wurde geladen
function set_loaded($modulename) {
	$this->loaded[]=$modulename;
}


//Ist ein Sprachpaket geladen?
function is_loaded($modulename) {
	return in_array($modulename,$this->loaded);
}



////////////////////////////////////////////////////////////////////////////////// -> PAKET ABLEGEN

//Daten dem Sprachpaket hinzufügen
function mergepack($lang=false) {
	if ( $lang===false ) $lang=$this->lang;
	$this->langpack=array_merge($this->langpack,$lang);
}


//Von einem bestimmten Modul den Teil mit Namen $type ablegen
function drop($type,$module=false) {
	global $apx;
	
	if ( $module===false ) $module=$apx->module();
	$this->module_load($module);
	
	if ( !is_array($this->cached[$module][$type]) ) return;
	if ( $this->is_dropped($module.'-'.$type) ) return;
	
	$this->mergepack($this->cached[$module][$type]);
	$this->dropped($module.'-'.$type);
}


//Von allen Modulen den Teil mit Namen $type ablegen
function dropall($type) {
	global $apx;
	
	foreach ( $this->cached AS $module => $langpack ) {
		$this->drop($type,$module);
	}
	foreach ( $apx->modules AS $module => $trash ) {
		$this->drop($type,$module);
	}
}


//Von einem bestimmten Modul aus dem Teil "actions" das Sprachpaket der Aktion $action ablegen
function dropaction($module=false,$action=false) {
	global $apx;
	
	if ( $module===false ) $module=$apx->module();
	$this->module_load($module);
	
	if ( $action===false ) $action=$apx->action();
	if ( !is_array($this->cached[$module]['actions'][$action]) ) return;
	if ( $this->is_dropped($module.'-action-'.$action) ) return;
	
	$this->mergepack($this->cached[$module]['actions'][$action]);
	$this->dropped($module.'-action-'.$action);
}


//Als abgelegt markieren
function dropped($id) {
	$this->dropped[]=$id;
}


//Ist etwas abgelegt?
function is_dropped($id) {
	if ( in_array($id,$this->dropped) ) return true;
	return false;
}



////////////////////////////////////////////////////////////////////////////////// -> SPRACHE EINFÜGEN

//Platzhalter in den Sprach-Strings ersetzen
function insert($text,$input) {
	if ( !is_array($input) || !count($input) ) return $text;
	
	foreach ( $input AS $find => $replace ) {
		$text=str_replace('{'.$find.'}',$replace,$text);
	}
	
	return $text;
}


//Sprachpaket einfügen
function insertpack($text) {
	return $this->insert($text,$this->langpack);
}


//Sprach-Platzhalter
function get($id,$input=array()) {
	$lang=$this->langpack[$id];
	if ( !is_array($input) || !count($input) ) return $lang;
	
	$lang=$this->insert($lang,$input);
	return $lang;
}


//Langpack ausgeben
function get_langpack() {
	return $this->langpack;
}


} //END CLASS

?>