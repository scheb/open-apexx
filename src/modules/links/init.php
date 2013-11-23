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
$module = array(1,870,
'id' => 'links',
'dependence' => array('comments','ratings'),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.1',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array(
	1 =>	array(
				'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_INSERTLINKPIC}" title="{MM_INSERTLINKPIC}" style="vertical-align:middle;" />',
				'function' => 'top.opener.insert_replace(\'pic_copy\',\'{PATH}\')',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'uploads'
				),
	2 =>	array(
				'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERTTEXT}" title="{MM_INSERTTEXT}" style="vertical-align:middle;" />',
				'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'httpdir'
				)
		)
);


//Artikel auflisten          S V O R
$action['show']     =  array(0,1,1,0);
$action['add']      =  array(0,1,2,0);
$action['edit']     =  array(1,0,3,0);
$action['del']      =  array(1,0,4,0);
$action['enable']   =  array(1,0,5,0);
$action['disable']  =  array(1,0,6,0);

//Kategorien
$action['catshow']  =  array(0,1,14,0);
$action['catadd']   =  array(0,0,15,0);
$action['catedit']  =  array(0,0,16,0);
$action['catdel']   =  array(0,0,17,0);
$action['catclean'] =  array(0,0,18,0);
//$action['catmove']  =  array(0,0,19,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen              F           V
$func['LASTLINKS']=array('links_last',true);
$func['TOPLINKS']=array('links_top',true);
$func['NOTTOPLINKS']=array('links_nottop',true);
$func['BESTLINKS_HITS']=array('links_best_hits',true);
$func['BESTLINKS_RATING']=array('links_best_rating',true);
$func['LINKS_SIMILAR']=array('links_similar',true);
$func['LINKS_RANDOM']=array('links_random',true);
$func['LINKS_CATEGORIES']=array('links_categories',true);
$func['LINKS_TAGCLOUD']=array('links_tagcloud',true);
$func['LINKS_STATS']=array('links_stats',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>