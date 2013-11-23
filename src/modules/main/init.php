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


//Modul registrieren
$module = array(1,99999999,
'id' => 'main',
'dependence' => array(),
'requirement' => array(),
'version' => '1.2.2',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array(
	1 =>	array(
				'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERT}" title="{MM_INSERT}" style="vertical-align:middle;" />',
				'function' => 'top.opener.insert_image(\'code\',\'{PATH}\')',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'httpdir'
				)
	)
);


//Aktionen registrieren       S V O R
$action['index']      = array(0,0,1,1);

//Module
$action['mshow']      = array(0,1,5,0);
$action['minstall']   = array(0,0,6,0);
$action['muninstall'] = array(0,0,7,0);
$action['menable']    = array(0,0,8,0);
$action['mdisable']   = array(0,0,9,0);
$action['mupdate']    = array(0,0,10,0);
$action['mconfig']    = array(0,0,11,0);

$action['secshow']    = array(0,1,12,0);
$action['lshow']      = array(0,1,15,0);
$action['tshow']      = array(0,1,19,0);
$action['snippets']   = array(0,1,21,0);
$action['tags']       = array(0,1,22,0);
$action['sshow']      = array(0,1,23,0);
$action['cshow']      = array(0,1,27,0);
$action['bshow']      = array(0,1,31,0);

$action['env']        = array(0,1,950,0);
$action['searches']   = array(0,1,960,0);
$action['log']        = array(1,1,970,0);
$action['close']      = array(0,1,980,0);
$action['delcache']   = array(0,1,990,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen      F           V
$func['SEARCH']=array('main_searchbox',true);
$func['DATE']=array('main_mkdate',true);
$func['PRINT']=array('main_printlink',false);
$func['TELL']=array('main_telllink',false);
$func['SHORTTEXT']=array('main_shorttext',true);
$func['TITLEBAR']=array('main_set_titlebar',true);
$func['HEADLINE']=array('main_set_headline',true);
$func['DESIGN']=array('main_set_design',true);
$func['SNIPPET']=array('main_snippet',true);
$func['SECTIONS']=array('main_sections',true);

//Admin-Template-Funktionen    F         V
$afunc['TEXTAREA']=array('main_textbox',true);
$afunc['SECTIONS']=array('main_sections',true);


/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>