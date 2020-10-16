<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Modul registrieren
$module = array(0,0,
'id' => 'mediamanager',
'dependence' => array(),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.0',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array(
	1 =>	array(
				'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_USEIMAGE}" title="{MM_USEIMAGE}" style="vertical-align:middle;" />',
				'function' => 'top.opener.CKEDITOR.tools.callFunction({FUNCNUM}, \'{PATH}\');',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'httpdir'
				),
	2 =>	array(
				'icon' => '<img src="design/mm/insert_swf.gif" alt="{MM_USESWF}" title="{MM_USESWF}" style="vertical-align:middle;" />',
				'function' => 'top.opener.CKEDITOR.tools.callFunction({FUNCNUM}, \'{PATH}\');',
				'filetype' => array('SWF'),
				'urlrel' => 'httpdir'
				)
	)
);


//Aktionen registrieren      S V O R
$action['inline']    = array(0,0,99,1);
$action['index']     = array(0,1,1,1);
$action['search']    = array(0,1,2,0);

$action['diradd']    = array(0,0,3,0);
$action['dirdel']    = array(0,0,4,0);
$action['dirrename'] = array(0,0,5,0);

$action['upload']    = array(0,0,6,0);
$action['sts']       = array(0,0,7,0);
$action['del']       = array(0,0,8,0);
$action['copy']      = array(0,0,9,0);
$action['move']      = array(0,0,10,0);
$action['rename']    = array(0,0,11,0);
$action['details']   = array(0,0,12,0);
$action['thumb']     = array(0,0,13,0);

$action['rules']     = array(0,1,14,0);
$action['radd']      = array(0,0,15,0);
$action['redit']     = array(0,0,16,0);
$action['rdel']      = array(0,0,17,0);


/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Admin-Template-Funktionen         F           V
$afunc['INLINE']=array('mediamanager_inline',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>