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
$module = array(1,320,
'id' => 'videos',
'dependence' => array('comments','ratings'),
'requirement' => array('main' => '1.2.0'),
'version' => '1.0.3',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array(
	1 =>	array(
				'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_INSERTPIC}" title="{MM_INSERTPIC}" style="vertical-align:middle;" />',
				'function' => 'top.opener.insert_replace(\'pic_copy\',\'{PATH}\')',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'uploads'
				),
	2 =>	array(
				'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERTTEXT}" title="{MM_INSERTTEXT}" align="middle" />',
				'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'httpdir'
				),
	3 =>	array(
				'icon' => '<img src="design/mm/insert_file.gif" alt="{MM_INSERTFILE}" title="{MM_INSERTFILE}" align="middle" />',
				'function' => 'top.opener.insert_file(\'{PATH}\')',
				'filetype' => array('WMV','MPG','MPEG','MP4','AVI','MOV','FLV'),
				'urlrel' => 'uploads'
				),
	4 =>	array(
				'icon' => '<img src="design/mm/insert_flv.gif" alt="{MM_INSERTFLV}" title="{MM_INSERTFLV}" align="middle" />',
				'function' => 'top.opener.insert_replace(\'select_flv\',\'{PATH}\')',
				'filetype' => array('FLV','F4V'),
				'urlrel' => 'uploads'
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
$action['convert']  =  array(0,0,7,1);

$action['pshow']    =  array(0,0,8,0);
$action['padd']     =  array(1,0,9,0);
$action['pdel']     =  array(1,0,10,0);

//Kategorien
$action['catshow']  =  array(0,1,14,0);
$action['catadd']   =  array(0,0,15,0);
$action['catedit']  =  array(0,0,16,0);
$action['catdel']   =  array(0,0,17,0);
$action['catclean'] =  array(0,0,18,0);
//$action['catmove']  =  array(0,0,19,0);

//Stats
$action['stats']    =  array(0,1,20,0);

//Konfig
$action['cfg']      =  array(0,1,99,0);


/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen              F           V
$func['LASTVIDEOS']=array('videos_last',true);
$func['TOPVIDEOS']=array('videos_top',true);
$func['NOTTOPVIDEOS']=array('videos_nottop',true);
$func['BESTVIDEOS_HITS']=array('videos_best_hits',true);
$func['BESTVIDEOS_RATING']=array('videos_best_rating',true);
$func['VIDEOS_CATEGORIES']=array('videos_categories',true);
$func['VIDEOS_SIMILAR']=array('videos_similar',true);
$func['VIDEOS_RANDOM']=array('videos_random',true);
$func['PRODUCT_VIDEOS']=array('videos_product',true);
$func['VIDEOS_TAGCLOUD']=array('videos_tagcloud',true);
$func['VIDEOS_STATS']=array('videos_stats',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>