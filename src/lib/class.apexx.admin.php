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


class apexx_admin extends apexx {

var $active_action;

var $user;
var $tmpl;
var $lang;
var $session;


//STARTUP
function __construct() {
	parent::__construct();
	if ( !$_REQUEST['action'] ) $_REQUEST['action']='main.index';

	$loadmodule=explode('.',$_REQUEST['action']);
	$this->module($loadmodule[0]);
	$this->action($loadmodule[1]);

	if ( count($loadmodule)!=2 ) {
		die('WRONG SYNTAX OF ACTION PARAM!');
	}
}


////////////////////////////////////////////////////////////////////////////////// -> AKTION AUSFÜHREN



//Aktion ausführen
function execute_action() {
	if ( !file_exists(BASEDIR.getmodulepath($this->module()).'admin.php') ) message($this->lang->get('CORE_MISSADMIN'));
	elseif ( !isset($this->actions[$this->module()][$this->action()]) ) message($this->lang->get('CORE_NOTREG'));
	elseif ( !$this->user->has_right($_REQUEST['action']) ) {
		if ( $this->user->info['userid'] ) {
			message($this->lang->get('CORE_NORIGHT'));
		}
		else {
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: action.php?action=user.login');
			exit;
		}
	}
	else {
		$this->lang->dropaction(); //Action-Sprachpaket des Moduls laden

		require_once(BASEDIR.getmodulepath($this->module()).'admin.php');
		$adminclass = new action;

		$action=$this->action();
		if ( method_exists($adminclass,$action) ) $adminclass->$action();
		else message($this->lang->get('CORE_METHODFAIL'));
	}
}


/*
//Multifunktion ausführen
function execute_multifunc(&$class) {
	if ( !is_array($_POST['multi']) ) return;

	foreach ( $_POST['multi'] AS $key => $val ) {
		if ( $val=='1' ) continue;
		unset($_POST['multi'][$key]);
	}

	if ( !count($_POST['multi']) ) return;

	foreach ( $this->actions[$this->module()] AS $action => $trash ) {
		if ( !$_POST['multi_'.$action] ) continue;
		if ( !$this->user->has_right($this->module().'.'.$action) ) continue;

		$callfunc='multi_'.$action;
		return $class->$callfunc();
	}
}
*/


////////////////////////////////////////////////////////////////////////////////// -> INTERNE VARIABLEN SETZEN(AUSLESEN

//Aktives Module
//-> class apexx


//Aktive Aktion
function action($action=false) {
	if ( $action===false ) return $this->active_action;
	$this->active_action=$action;
}


} //END CLASS

?>
