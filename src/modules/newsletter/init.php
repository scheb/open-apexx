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
$module = array(1,2000,
'id' => 'newsletter',
'dependence' => array(),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.1',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array(
	1 =>	array(
				'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_USEIMAGE}" title="{MM_USEIMAGE}" style="vertical-align:middle;" />',
				'function' => 'top.opener.CKEDITOR.tools.callFunction({FUNCNUM}, \'{PATH}\');',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'http'
				),
	2 =>	array(
				'icon' => '<img src="design/mm/insert_swf.gif" alt="{MM_USESWF}" title="{MM_USESWF}" style="vertical-align:middle;" />',
				'function' => 'top.opener.CKEDITOR.tools.callFunction({FUNCNUM}, \'{PATH}\');',
				'filetype' => array('SWF'),
				'urlrel' => 'http'
				)
	)
);


//Aktionen registrieren      S V O R
$action['show']      = array(0,1,1,0);
$action['add']       = array(0,1,2,0);
$action['addnews']   = array(0,0,3,0);
$action['edit']      = array(0,0,4,0);
$action['del']       = array(0,0,5,0);
$action['send']      = array(0,0,6,0);
$action['preview']   = array(0,0,7,0);

$action['eshow']     = array(0,1,8,0);
$action['eadd']      = array(0,0,9,0);
$action['eedit']     = array(0,0,10,0);
$action['edel']      = array(0,0,11,0);
$action['eenable']   = array(0,0,12,0);
$action['eimport']   = array(0,1,13,0);

$action['catshow']   = array(0,1,14,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen       F         V
$func['NEWSLETTER']=array('newsletter_form',false);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/

?>