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


class templates extends tengine {

var $designid='default';
var $titlebar='';
var $headline=array();

////////////////////////////////////////////////////////////////////////////////// -> STARTUP


function templates() {
	global $apx,$set;
	
	$this->assign_static('CHARSET',$set['main']['charset']);
	$this->assign_static('ACTIVE_MODULE',''); //Alt, Abwärtskompatiblität
	$this->assign_static('APEXX_MODULE','');
	
	//Basis
	if ( defined('BASEREL') ) {
		$this->assign_static('BASEREL',BASEREL);
	}
	
	//Zeiger
	$this->parsevars['APEXX_MODULE']=&$apx->active_module;
	$this->parsevars['ACTIVE_MODULE']=&$apx->active_module;
	
	//Set-Variablen
	foreach ( $set AS $module => $settings ) {
		if ( !is_array($settings) ) continue;
		foreach ( $settings AS $key => $value ) {
			$this->assign_static('SET_'.strtoupper($module).'_'.strtoupper($key),$value);
		}
	}
	
	//Installierte Module
	foreach ( $apx->modules AS $module => $trash ) {
		$this->assign_static('MODULE_'.strtoupper($module),1);
	}
	
	ob_start();
	parent::tengine(true);
}



////////////////////////////////////////////////////////////////////////////////// -> HEADLINES

//Headline
function headline($text,$url='') {
	$this->headline[]=array(
		'TEXT' => $text,
		'LINK' => $url
	);
}


//Titelleiste
function titlebar($text) {
	$this->titlebar=$text;
}



////////////////////////////////////////////////////////////////////////////////// -> AUSGABE


//Design laden
function loaddesign($prefix='default') {
	$this->designid=$prefix;
}



//Ausgabe vorbereiten
function out() {
	global $apx,$set;
	
	//Output holen und löschen
	$this->cache=ob_get_contents();
	ob_end_clean();
	
	if ( $this->designid!='blank' ) {
		$addcode='';
		$extendcode='';
		
		//Autoload Javascript
		$addcode='<script type="text/javascript" src="'.HTTPDIR.'lib/yui/yahoo-dom-event/yahoo-dom-event.js"></script>';
		$addcode.='<script language="JavaScript" type="text/javascript" src="'.HTTPDIR.'lib/javascript/global.js"></script>'."\n";
		$addcode.='<script language="JavaScript" type="text/javascript" src="'.HTTPDIR.'lib/javascript/public_popups.js"></script>'."\n";
		$addcode.='<script language="JavaScript" type="text/javascript" src="'.HTTPDIR.'lib/javascript/tooltip.js"></script>'."\n";
		
		//Cronjobs ausführen
		//Prüfen, ob das Script bereits installiert ist!
		if ( isset($set['main']) && $cronhash=cronexec() ) {
			$extendcode.='<img src="'.HTTPDIR.'lib/cronjob/cron.php?hash='.$cronhash.'" width="1" height="1" alt="" />';
		}
		
		$this->cache=$addcode.$this->cache.$extendcode;
	}
	
	//Headlines
	$this->assign('HEADLINE',$this->headline);
	$this->assign('TITLEBAR',$this->titlebar);
	
	//Assign Content
	$this->assign('CONTENT',$this->cache);
	
	//Ausgabe erfolgt
	$this->final_flush();
}



//CACHE-AUSGABE
function final_flush() {

	//Leeres Design -> Nur Cache ausgeben
	if ( $this->designid=='blank' ) {
		echo $this->cache;
		return;
	}
	
	//Design + Cache ausgeben
	$this->parse('design_'.$this->designid,'/');
	
	//Errorreport
	$this->show_errorreport();
}



//Error-Report
function show_errorreport() {
	if ( !$this->errorreport ) return;
	echo '<div class="error">'.$this->errorreport.'</div>';
}


}

?>