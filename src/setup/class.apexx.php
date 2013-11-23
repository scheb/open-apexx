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


# APEXX main class
# =====================

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class apexx {

var $modules=array();
var $actions=array();
var $functions=array();
var $sections=array();
var $languages=array();

var $active_module;
var $language_default;

var $section_default=0;
var $section=array('id'=>0);

var $coremodules=array('main','mediamanager','user');


////////////////////////////////////////////////////////////////////////////////// -> STARTUP

//System starten
function apexx() {
	global $set;
	
	error_reporting(E_ALL ^ E_NOTICE);
	
	$version=file(BASEDIR.'lib/version.info');
	define('VERSION',array_shift($version));
	define('HTTP_HOST',$this->get_http());
  define('HTTPDIR',$this->get_dir());
	define('HTTP',HTTP_HOST.HTTPDIR);
	
	//Variablen vorbereiten
	$this->prepare_vars();
	
	//Sprachpakete
	$this->languages=array('de'=>'Deutsch');
	$this->language_default='de';
	
	//Zeitzone
	define('TIMEDIFF',(date('Z')/3600-$set['main']['timezone']-date('I'))*3600);
}



//Übergebene Variable vorbereiten
function prepare_vars() {
	if ( isset($_REQUEST) && is_array($_REQUEST) ) $_REQUEST=$this->strpsl($_REQUEST);
	if ( isset($_POST) && is_array($_POST) ) $_POST=$this->strpsl($_POST);
	if ( isset($_GET) && is_array($_GET) ) $_GET=$this->strpsl($_GET);
	if ( isset($_COOKIE) && is_array($_COOKIE) ) $_COOKIE=$this->strpsl($_COOKIE);
	if ( isset($_SESSION) && is_array($_SESSION) ) $_COOKIE=$this->strpsl($_SESSION);
	
	@set_magic_quotes_runtime(0);
}



//Scripslashes von Variablen
function strpsl($array) {
	static $trimvars,$magicquotes;
	if ( !isset($trimvars) ) $trimvars=iif((int)$_REQUEST['apx_notrim'] && MODE=='admin',0,1);
	if ( !isset($magicquotes) ) $magicquotes=get_magic_quotes_gpc();
	
	foreach($array AS $key => $val) {
		if( is_array($val) ) {
			$array[$key]=$this->strpsl($val);
			continue;
		}
		
		if ( $trimvars ) $val=trim($val);
		if ( $magicquotes ) $val=stripslashes($val);
		$array[$key]=$val;
	}
	
	return $array;
}



//HTTP-URL
function get_http() {
  $port = iif($_SERVER['SERVER_PORT']!=80,':'.$_SERVER['SERVER_PORT']);
  $host = preg_replace('#:.*$#','',$_SERVER['HTTP_HOST']); //Port entfernen
  return 'http://'.$host.$port;
}



//Ordner
function get_dir() {
	$dir=dirname($_SERVER['PHP_SELF']).'/';
	
	//Relation zur Basis
	if ( defined('BASEREL') ) {
		$dir.=BASEREL;
	}
	
	$dir=str_replace('\\','/',$dir);
	$dir=preg_replace('#/{2,}#','/',$dir);
	while( preg_match('#/[A-Za-z0-9%_-]+/\.\.#im',$dir) ) {
		$dir=preg_replace('#/[A-Za-z0-9%_-]+/\.\.#im','',$dir);
	}
	$dir=str_replace('./','',$dir);
	
	return $dir;
}



///////////////////////////////////////////////// MODULE / AKTIONEN

//Modul-Informationen holen
function get_modules() {
  global $db;
  
  $data=$db->fetch("SELECT * FROM ".PRE."_modules WHERE active='1'");
  
	if ( count($data) ) {
	  foreach ( $data AS $res ) {
  		$module=$action=$modset=array();
  		list($modulename)=$res;
	  	
	  	if ( !is_dir(BASEDIR.getmodulepath($modulename)) ) continue;
  		
  		//Modul-INIT
	  	require(BASEDIR.getmodulepath($modulename).'init.php');
  		$this->register_module($modulename,$module);
	  	$this->register_actions($modulename,$action);
  		$this->register_functions($modulename,$func);
  		
	  	unset($module,$action,$func);
  	}
	}
}


//Ist ein Modul aktiv?
function is_module($modulename) {
	if ( isset($this->modules[$modulename]) ) return true;
	else return false;
}


//Modul registieren
function register_module($modulename,$info) {
	$this->modules[$modulename]=$info;
}


//Aktion registieren
function register_actions($modulename,$info) {
	$this->actions[$modulename]=$info;
}


//Funktion registieren
function register_functions($modulename,$info) {
	if ( !is_array($info) || !count($info) ) return;
	$this->functions[$modulename]=$info;
}


//Modul-Konfiguration auslesen
function get_config() {
	global $set,$db;
	
	$data=$db->fetch("SELECT * FROM ".PRE."_config");
	if ( !count($data) ) return;
	
	foreach ( $data AS $res ) {
		$modulename=$res['module'];
		$varname=$res['varname'];
		
		//Switch
		if ( $res['type']=='switch' ) {
			$thevalue=iif($res['value'],1,0);
		}
		
		//String
		elseif ( $res['type']=='string' ) {
			$thevalue=addslashes($res['value']);
		}
		
		//Arrays
		elseif ( $res['type']=='array' || $res['type']=='array_keys' ) {
			$thevalue=unserialize($res['value']);
			if ( !is_array($thevalue) ) $thevalue=array();
		}
		
		//Integer
		elseif ( $res['type']=='int' ) {
			$thevalue=(int)$res['value'];
		}
		
		//Float
		elseif ( $res['type']=='float' ) {
			$thevalue=(float)$res['value'];
		}
		
		//Select
		elseif ( $res['type']=='select' ) {
			$possible=unserialize($res['addnl']);
			
			foreach ( $possible AS $value => $descr ) {
				if ( $value==$res['value'] ) {
				$thevalue=$value;
				break;
				}
			}
		}
		
		if ( !isset($thevalue) ) continue;
		$set[$modulename][$varname]=$thevalue;
		unset($thevalue);
	}
	
	
}


//Module sortieren
function sort_modules() {
	uasort($this->modules,array($this,'do_sort_modules'));
}


//Actions sortieren
function sort_actions() {
	foreach ( $this->modules AS $module => $module_info ) {
		uasort($this->actions[$module],array($this,'do_sort_actions'));
	}
}


//Module sortieren (Navigation)
function do_sort_modules($a,$b) {
   if ($a[1]==$b[1]) return 0;
   return ($a[1]>$b[1]) ? 1 : -1;
}


//Aktionen sortieren (Navigation)
function do_sort_actions($a,$b) {
   if ($a[2]==$b[2]) return 0;
   return ($a[2]>$b[2]) ? 1 : -1;
}



////////////////////////////////////////////////////////////////////////////////// -> SPRACHPAKETE

//Sprachpakete registrieren
function get_languages() {
	global $set;
	
	$langinfo=&$set['main']['languages'];
	if ( !is_array($langinfo) || !count($langinfo) ) die('no langpack registered!');
	
	foreach ( $langinfo AS $dir => $res ) {
		if ( $res['default'] ) $this->language_default=$dir;
		$this->languages[$dir]=$res['title'];
	}
	
	if ( !isset($this->language_default) ) {
		reset($this->languages);
		list($key,$val)=each($this->languages);
		$this->language_default=$key;
	}
}



////////////////////////////////////////////////////////////////////////////////// -> SEKTIONEN

//Sektionen auslesen
function get_sections() {
	global $db;
	$data=$db->fetch("SELECT * FROM ".PRE."_sections ORDER BY title ASC",1);
	if ( !count($data) ) return;
	
	foreach ( $data AS $res ) {
		$this->sections[$res['id']]=$res;
		if ( $res['default'] ) $this->section_default=$res['id'];
	}
	
	if ( !$this->section_default ) {
		reset($this->sections);
		list($key,$val)=each($this->sections);
		$this->section_default=$key;
	}
}


//Aktuelle Sektion
function section_id($id=false) {
	if ( $id===false ) return $this->section['id'];
	
	$id=(int)$id;
	$this->section=$this->sections[$id];
}


//Sektion aktiviert?
function section_is_active($id) {
	$id=(int)$id;
	if ( $this->sections[$id]['active'] ) return true;
	return false;
}



////////////////////////////////////////////////////////////////////////////////// -> INTERNE VARIABLEN SETZEN(AUSLESEN

//Aktives Module
function module($module=false) {
	if ( $module===false ) return $this->active_module;
	if ( !$this->is_module($module) ) die('"'.$module.'" is not a valid/active module-ID!');
	$this->active_module=$module;
}


} //END CLASS

?>