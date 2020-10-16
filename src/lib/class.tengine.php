<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class tengine {

var $staticvars=array();
var $parsevars=array();
var $staticparsevars=array();
var $used_vars=array();
var $used_includes=array();

var $cachepath_replace=array('/'=>'%1%','.'=>'%2%');
var $mode='parse';
var $whois=false;
var $theme='default';


function tengine($firsttime=false) {
	global $set,$apx;
	
	//Einstellungen
	$this->cachepath=getpath('cache');
	$this->templatepath='';
	$this->cache_expire=1*24*3600; //1 Tag
	//$this->cache_expire=7*24*3600; //7 Tage
	//$this->cache_expire=0; //Immer
	//$this->cache_expire=false; //Nie
	
	//Erstes mal initalisieren der Klasse
	if ( $firsttime ) {
		
		//Server-Variablen
		$this->assign_static('SERVER_HTTPHOST',HTTP_HOST);
		$this->assign_static('SERVER_HTTPDIR',HTTP);
		$this->assign_static('SERVER_DIR',HTTPDIR);
		$this->assign_static('SERVER_SCRIPT',$_SERVER['SCRIPT_NAME']);
		$this->assign_static('SERVER_QUERY',$_SERVER['QUERY_STRING']);
		$this->assign_static('SERVER_URL',$_SERVER['REQUEST_URI']);
		$this->assign_static('APEXX_VERSION',VERSION);
		$this->assign_static('APEXX_TIME',time());
		
		//REQUEST-Variablen
		foreach ( $_REQUEST AS $key => $value ) {
			$this->assign_static('SERVER_REQUEST_'.strtoupper($key),$value);
		}
	}
	
	//Statische Variablen importieren
	else {
		$this->parsevars=$apx->tmpl->export_statics();
		$this->theme=$apx->tmpl->theme;
	}
	
	//WHOIS-Abfrage
	$this->whois=$set['tmplwhois'];
}


/*********************** PFADE ***********************/

//Theme setzen
function set_theme($theme) {
	if ( !is_dir(BASEDIR.getpath('tmpldir').$theme) ) $this->theme='default';
	else $this->theme=$theme;
}



/*********************** PFADE ***********************/

//Pfad zum angeforderten Template
function get_templatepath($module=false) {
	global $apx;
	if ( !$module ) $module=$apx->module();
	
	if ( $module=='/' ) {
		if ( MODE=='admin' ) return getpath('tmpl_base_admin',array('MODULE'=>$module));
		else return $this->tmpldir=getpath('tmpl_base_public',array('MODULE'=>$module,'THEME'=>$this->theme));
	}
	else {
		if ( MODE=='admin' ) return $this->tmpldir=getpath('tmpl_modules_admin',array('MODULE'=>$module));
		else return $this->tmpldir=getpath('tmpl_modules_public',array('MODULE'=>$module,'THEME'=>$this->theme));
	}

	return '';
}



/*********************** VAR INPUT ***********************/
/*** Existiert eine Variable? ***/
function var_exists($var) {
	if ( array_key_exists($var,$this->parsevars) ) return true;
	return false;
}


/*** Variablen speichern ***/
function assign($varname,$value,$ref=false) {
	if ( $this->var_exists($varname) ) {
		$this->error('<b>error:</b> "'.$varname.'" is already defined!');
		return;
	}
	
	if ( $ref ) $this->parsevars[$varname]=&$value;
	else $this->parsevars[$varname]=$value;
}


/*** Variable überschreiben ***/
function overwrite($varname,$value,$ref=false) {
	if ( $ref ) $this->parsevars[$varname]=&$value;
	else $this->parsevars[$varname]=$value;
}


/*** Variable erweitern ***/
function extend($varname,$value) {
	$this->parsevars[$varname].=$value;
}


/*** Statische Variable speichern ***/
function assign_static($varname,$value,$ref=false) {
	if ( $this->var_exists($varname) ) {
		return $this->error('<b>error:</b> "'.$varname.'" is already defined!');
	}
	
	if ( $ref ) $this->parsevars[$varname]=&$value;
	else $this->parsevars[$varname]=$value;
	$this->set_static($varname);
}


/*** Variable als statisch festlegen ***/
function set_static($varname) {
	$this->staticvars[]=$varname;
	$this->staticparsevars[$varname]=$this->parsevars[$varname];
}


/*** Prüfen ob eine Variable statisch ist ***/
function is_static($var) {
	if ( in_array($var,$this->staticvars) ) return true;
	return false;
}


/*** Statische Variablen exportieren ***/
function export_statics() {
	/*$static=array();
	
	foreach ( $this->staticvars AS $key ) {
		if ( !isset($this->parsevars[$key]) ) continue;
		$static[$key]=$this->parsevars[$key];
	}*/
	
	return $this->staticparsevars;
}


/*** Parse-Variablen leeren ***/
function clearvars() {
	foreach ( $this->parsevars AS $key => $value ) {
		if ( $this->is_static($key) ) continue;
		unset($this->parsevars[$key]);
	}
}



/*********************** DATEI PARSEN ***********************/
function parse($filename,$override_module=false) {
	$this->failed=false;
	$this->mode='parse';
	
	//Parsing starten
	$filepath=$this->get_templatepath($override_module).$filename.'.html';
	$compiled_path=$this->get_compiled_file($filepath);
	if ( $this->failed ) return;
	
	//Whois START
	$this->whois_start($filepath);
	
	//Template ausführen
	include(BASEDIR.$compiled_path);
	$this->clearvars();
	
	//Whois ENDE
	$this->whois_end($filepath);
}



/*********************** DATEI INCLUDEN ***********************/
function include_file($filename) {
	if ( !preg_match('#([A-Z0-9_-]+/)*[A-Z0-9_-]+.html#si',$filename) ) {
		$this->error('<b>error:</b> "'.$filename.'" is not a valid include!',false);
		return;
	}
	
	$compiled_path=$this->get_compiled_file($filename);
	if ( $this->failed ) return;
	
	//Whois START
	$this->whois_start($filename);
	
	include(BASEDIR.$compiled_path);
	
	//Whois ENDE
	$this->whois_end($filename);
}



/*********************** SPRACHPLATZHALTER ***********************/

function get_langvar($id) {
	global $apx;
	return $apx->lang->get($id);
}



/*********************** CACHING ***********************/

/*** Prüfen ob eine komplilierte Datei vorhanden, ansonsten kompilieren ***/
function get_compiled_file($filepath) {
	$compiled_path=$this->get_cached_filepath($filepath);
	
	//Datei kompilieren, wenn nicht vorhanden
	if ( !file_exists(BASEDIR.$compiled_path) ) {
		$this->cache_file($filepath,$compiled_path);
	}
	
	//Datei neu komplilieren, wenn abgelaufen
	elseif ( $this->cache_expire!==false ) {
		$cachedate=$this->get_file_lastchange($compiled_path);
		if ( $cachedate+$this->cache_expire<time() ) $this->cache_file($filepath,$compiled_path);
	}
	
	if ( $this->failed ) {
		$this->error('<b>fatal error:</b> failed caching "'.$filepath.'"');
		return false;
	}

	return $compiled_path;
}


/*** Cache-Pfad einer Datei generieren ***/
function get_cached_filepath($filepath) {	
	$lastchange=$this->get_file_lastchange($filepath);
	
  $cachedfile=$filepath.$lastchange;
  $cachedfile=$this->cachepath.$this->encode_path($cachedfile).'.php';
	
	return $cachedfile;
}


/*** Cache-Pfad codieren ***/
function encode_path($file) {
	$file=strtr($file,$this->cachepath_replace);
	return $file;
}



/*********************** CACHE LEEREN ***********************/

function clear_cache($filepath=false) {
	
	//Bestimmte Datei
	if ( $filepath!==false && is_string($filepath) ) {
		$handler=opendir(BASEDIR.getpath('cache'));
		while( $file=readdir($handler) ){
			if ( $file=='.' || $file=='..' || $file=='index.html' ) continue;
			if ( !preg_match('#^'.$this->encode_path($filepath).'[0-9]+\.php$#',$file) ) continue;
			unlink(BASEDIR.getpath('cache').$file);
		}
		closedir($handler);
		return;
	}
	
	//Gesamter Cache
	$handler=opendir(BASEDIR.getpath('cache'));
	while( $file=readdir($handler) ){
		if ( $file=='.' || $file=='..' || $file=='index.html' ) continue;
		unlink(BASEDIR.getpath('cache').$file);
	}
	closedir($handler);
}




/*********************** DATEN AUSLESEN ***********************/

/*** Inhalt einer Datei auslesen ***/
function read_file($filepath) {
	static $cache;
	$abs_filepath=BASEDIR.$filepath;
	
	if ( isset($cache[$filepath]) ) return $cache[$filepath];
	if ( !file_exists($abs_filepath) ) return $this->error('<b>error:</b> file "'.$filepath.'" not found for reading!');
	
	$file=implode('',file($abs_filepath));
	$file=str_replace("\r",'',$file);
	//$cache[$filepath]=$file; // ---> Kein Caching bei Dateiinhalten!
	
	return $file;
}


/*** Letzte Änderung einer Datei ***/
function get_file_lastchange($filepath) {
	static $cache;
	$abs_filepath=BASEDIR.$filepath;
	
	if ( isset($cache[$filepath]) ) return $cache[$filepath];
	if ( !file_exists($abs_filepath) ) return $this->error('<b>error:</b> file "'.$filepath.'" not found for stat!');
	
	$lastchange=filemtime($abs_filepath);
	$cache[$filepath]=$lastchange;
	
	return $lastchange;
}



/*********************** CACHE ***********************/

/*** Datei in den Cache laden ***/
function cache_file($filepath,$cachedpath) {
	
	//Datei auslesen
	$content=$this->read_file($filepath);
	if ( $this->failed ) return;
	
	//Compiler starten
	$compiler = $this->create_compiler($filepath);
	$compiled_content=$compiler->get_compiled_content($content);
	unset($compiler); //Speicher frei machen
	
	//Auf erfolgreiches komplilieren prüfen
	if ( $compiled_content===false ) {
		return $this->error('<b>error:</b> failed compiling "'.$filepath.'"!');
	}
	
	//$this->clear_cache($filepath); //Wird nun von Cronjob erledigt
	$this->save_file($cachedpath,$compiled_content);
}



/*** Compiler erzeugen ***/
function create_compiler($filepath) {
	require_once(BASEDIR.'lib/class.tengine.compiler.php');
	$compiler=new tengine_compiler($filepath);
	return $compiler;
}



/*** Cache speichern ***/
function save_file($cachedpath,$compiled_content) {
	if ( !is_dir(BASEDIR.$this->cachepath) ) {
		die('<b>fatal error:</b> "'.$this->cachepath.'" is not a directory!');
	}
	
	if(!is_writable(BASEDIR.$this->cachepath)) {
		die('<b>fatal error:</b> "'.$this->cachepath.'" is not writeable!');
	}
	
	$tmpfile=tempnam(BASEDIR.$this->cachepath,'compwrite');
	$fstream=fopen($tmpfile,'w');
	flock($fstream,LOCK_EX);
	fwrite($fstream,$compiled_content);
	flock($fstream,LOCK_UN);
	fclose($fstream);
	
	//Datei löschen falls vorhanden
	if ( file_exists(BASEDIR.$cachedpath) ) {
		unlink(BASEDIR.$cachedpath);
	}
	
	rename($tmpfile,BASEDIR.$cachedpath);
	@chmod(BASEDIR.$cachedpath,0777);
}



/*********************** VERWENDETE VARIABLEN UND INCLUDES ***********************/

/*** Verwendete Variablen zurückgeben ***/
function used_vars($filename,$override_module=false) {
	$this->failed=false;
	$filepath=$this->get_templatepath($override_module).$filename.'.html';
	$varlist=$this->used_vars_readout($filepath);
	return array_unique($varlist);
}


/*** Verwendete Variablen aus einer Datei auslesen ***/
function used_vars_readout($filepath) {
	$this->mode='getvars';
	
	$cached_path=$this->get_compiled_file($filepath);
	if ( !$cached_path ) {
		$this->mode='parse';
		return array();
	}
	include(BASEDIR.$cached_path);
	
	$varlist=$this->used_vars[$filepath];
	$includes=$this->used_includes[$filepath];
	
	//Variablen der Includes einlesen
	if ( is_array($includes) && count($includes) ) {
		foreach ( $includes AS $include ) {
			$addlist=$this->used_vars_readout($include);
			$varlist=array_merge($varlist,$addlist);
		}
	}
	
	$this->mode='parse';
	return $varlist;
}



/*********************** FEHLERMELDUNG AUSGEBEN ***********************/

//WHOIS Anfang ausgeben
function whois_start($filepath) {
	if ( MODE=='admin' ) return; //Nicht im Adminbereich anzeigen
	$colors=array('red','darkred','blue','darkblue','green','black','purple','indigo','sienna','burlywood');
	
	//Kommentare
	if ( $this->whois==1 ) {
		echo '<!-- START: '.$filepath.' -->';
	}
	
	//Tabellen
	elseif ( $this->whois==2 ) {
		$colorid=array_rand($colors);
		$color=$colors[$colorid];
		echo '<table width="100%"><tr><td style="font-family:courier,monospace;font-size:10px;color:white;background:'.$color.';border:2px solid '.$color.';">'.$filepath.'</td></tr><tr><td style="border:2px solid '.$color.';">';
	}
}


//WHOIS Ende ausgeben
function whois_end($filepath) {
	if ( MODE=='admin' ) return; //Nicht im Adminbereich anzeigen
	
	//Kommentare
	if ( $this->whois==1 ) {
		echo '<!-- END: '.$filepath.' -->';
	}
	
	//Tabellen
	elseif ( $this->whois==2 ) {
		echo '</td></tr></table>';
	}
}


/*********************** FEHLERMELDUNG AUSGEBEN ***********************/

function error($text,$send_failed=true) {
	error($text);
	
	if ( $send_failed ) {
		$this->failed=true;
		//$this->clearvars();
	}
}


} //END CLASS


?>