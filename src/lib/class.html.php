<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|             (c) Copyright 2005, Christian Scheb               |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

class html {

var $tmpl;

//Formate
var $table;
var $tabletmpl;
var $layer;


function html() {
	global $apx;
	$this->tmpl=&$apx->tmpl;
}


////////////////////////////////////////////////////////////////////////////////// -> NAVIGATION

function navi() {
	global $apx, $db;
	if ( !$apx->user->info['userid'] ) return '';
	
	//Module
	foreach ( $apx->modules AS $nv_module => $module_info ) {
		if ( !$module_info[0] ) continue; //Modul nicht auflisten
		
  	$navilinks=array();
		$hidden=false;
		
		
		//Aktionen
		foreach ( $apx->actions[$nv_module] AS $nv_action => $action_info ) {
			if ( !$apx->user->has_right($nv_module.'.'.$nv_action) || !$action_info[1] ) continue;
			
			$navilinks[]=array(
				'TITLE' => $apx->lang->get('NAVI_'.strtoupper($nv_module).'_'.strtoupper($nv_action)),
				'LINK' => 'action.php?action='.$nv_module.'.'.$nv_action
			);
		}
		
		//Wenn Menüpunkte für dieses Module verfügbar Überschrift einfügen
		if ( count($navilinks) ) {
			if ( $_COOKIE['apx_open']['dd_'.$nv_module]!='1' ) $hidden=true;
			
			$naviCache[$nv_module]=array(
				'ID' => 'dd_'.$nv_module,
				'TITLE' => $apx->lang->get('MODULENAME_'.strtoupper($nv_module)),
				'HIDDEN' => $hidden,
				'ACTION' => $navilinks
			);
		
		}
	}
	
	
	//Anordnung anpassen
	if ( $apx->user->info['userid'] ) {
		$data = $db->fetch("SELECT module FROM ".PRE."_user_navord WHERE userid='".$apx->user->info['userid']."' ORDER BY ord ASC");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				if ( isset($naviCache[$res['module']]) ) {
					$navi[] = $naviCache[$res['module']];
					unset($naviCache[$res['module']]);
				}
			}
		}
	}
	foreach ( $naviCache AS $navitem ) {
		$navi[] = $navitem;
	}
	
	return $navi;
}


function mm_navi() {
	global $apx;
	if ( !$apx->user->info['userid'] ) return '';
	
	foreach ( $apx->actions['mediamanager'] AS $nv_action => $action_info ) {
		if ( !$apx->user->has_right('mediamanager'.'.'.$nv_action) || !$action_info[1] ) continue;
		
		++$i;
		
		$navi[$i]['TITLE']=$apx->lang->get('NAVI_MEDIAMANAGER_'.strtoupper($nv_action));
		if ( $nv_action=='search' ) $navi[$i]['LINK']='action.php?action=mediamanager.'.$nv_action.'&amp;dir='.$_REQUEST['dir'].'&amp;module='.$_REQUEST['module'];
		else $navi[$i]['LINK']='action.php?action=mediamanager.'.$nv_action.'&amp;module='.$_REQUEST['module'];
	}
	
	return $navi;
}



////////////////////////////////////////////////////////////////////////////////// -> LAYER

//Layer laden + Header ausgeben
function layer_header($info) {
	global $apx;
	if ( MODE!='admin' ) return;
	$tmpl=new tengine;
	
	foreach ( $info AS $bar ) {
		++$obj;
		$title=$apx->lang->get($bar[0]);
		if ( !$title ) $title=$bar[0];
		$layerdata[$obj]['NAME']=$title;
		$layerdata[$obj]['LINK']=$bar[1];
		$layerdata[$obj]['SELECTED']=iif($bar[2],'_sel','');
	}
	
	$tmpl->assign('LAYER',$layerdata);
	$tmpl->assign('SHOWHEADER',true);
	
	$tmpl->parse('layer','/');
}

//Layer Footer ausgeben
function layer_footer() {
	if ( MODE!='admin' ) return;
	$tmpl=new tengine;
	
	$tmpl->assign('SHOWFOOTER',true);
	$tmpl->parse('layer','/');
}


////////////////////////////////////////////////////////////////////////////////// -> AUTO-TABELLEN

//Tabellen-Inhalt
function table($cols,$footer_actions=array()) {
	global $apx;
	if ( MODE!='admin' ) return;
	
	//Colspan + Footer
	$colspan=count($cols)+1;
	if ( is_array($footer_actions) && count($footer_actions) ) $colspan+=1;
	
	//Header
	foreach ( $cols AS $col ) {
		++$i;
		
		//Titel aus dem Sprachpaket holen
		if ( isset($apx->lang->langpack[$col[0]]) ) $col[0]=$apx->lang->get($col[0]);
		
		$headerdef[]=array(
			'WIDTH' => $col[1],
			'TITLE' => iif($col[0],$col[0],'&nbsp;')
		);
		
		$this->tmpl->assign('COL'.$i.'_ACTIVE',true);
		$this->tmpl->assign('COL'.$i.'_ATTRIB',iif($col[2],' '.$col[2]));
	}
	
	//Checkboxes
	if ( is_array($footer_actions) && count($footer_actions) ) {
		$this->tmpl->assign('CHECKBOXES',true);
	}
	
	//Footer
	$this->assignfooter($footer_actions);
	
	//Assign
	$this->tmpl->assign('COL',$headerdef);
	$this->tmpl->assign('COLCOUNT',$colspan);
	$this->tmpl->assign('OPTIONS_WIDTH',$optwidth);
	
	$this->tmpl->parse('table','/');
}


//Footer holen
function assignfooter($footer_actions=array()) {
	global $apx;
	if ( MODE!='admin' ) return;
	if ( !is_array($footer_actions) || !count($footer_actions) ) return '';
	
	//Footer
	$multiaction = array();
	$footer = '';
	$i = 0;
	foreach ( $footer_actions AS $action ) {
		$multiaction[] = array(
			'URL' => $action[1],
			'OVERLAY' => !empty($action[2])
		);
		$footer .= '<input type="button" value="'.compatible_hsc($action[0]).'" class="button" onclick="tableMultiAction('.$i.');" /> ';
		++$i;
	}
	
	$apx->tmpl->assign('MULTIACTION', $multiaction);
	$apx->tmpl->assign('FOOTER', $footer);
}


} //END CLASS


?>