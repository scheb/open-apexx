<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class apexx_public extends apexx {

var $lang;
var $tmpl;

var $active_section;


//STARTUP
function apexx_public() {
	parent::apexx();
}


//Sektion wählen
function init_section() {
	global $set;
	$_REQUEST['sec']=(int)$_REQUEST['sec'];
	
	//Sektion auswählen
	if ( $_REQUEST['sec'] && isset($this->sections[$_REQUEST['sec']]) && $this->sections[$_REQUEST['sec']]['active'] ) {
		$this->section_check($_REQUEST['sec']);
		$this->section_id($_REQUEST['sec']);
	}
	elseif ( $set['main']['forcesection'] ) {
		$this->section_id($this->section_default);
	}
	
	//Theme erzwingen
	if ( $this->section_id() && $this->section['theme'] ) {
		$this->tmpl->set_theme($this->section['theme']);
	}
	
	//Sprache erzwingen
	if ( $this->section_id() && $this->section['lang'] ) {
		$this->lang->langid($this->section['lang']);
	}
	
	$this->tmpl->assign_static('WEBSITE_NAME',$set['main']['websitename']); 
	$this->tmpl->assign_static('SECTION_ID',$this->section_id());
	$this->tmpl->assign_static('SECTION_TITLE',$this->section['title']);
	$this->tmpl->assign_static('SECTION_LANG',$this->section['lang']);
	$this->tmpl->assign_static('SECTION_VIRTUAL',$this->section['virtual']);
	$this->tmpl->assign_static('SECTION_THEME',$this->section['theme']);
}



//Darf man eine Sektion betreten?
function section_check($id) {
	global $user;
	
	if ( $user->info['section_access']=='all' ) $secacc='all'; 
	else {
		$secacc=unserialize($user->info['section_access']);
		if ( !is_array($secacc) ) $secacc=array();
	}
	
	//Beschränkung durch Benutzergruppe
	if ( $secacc!='all' && !in_array($id,$secacc) && $id!=$this->section_default ) {
		$this->lang->init(); //Sprachpaket ist noch nicht initialisiert!
		$indexpage=mklink('index.php','index.html',$this->section_default);
		message($this->sections[$id]['msg_noaccess'],$indexpage);
	}
}



} //END CLASS

?>
