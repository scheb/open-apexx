<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Modul registrieren
$module = array(1,320,
'id' => 'downloads',
'dependence' => array('comments','ratings'),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.3',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array(
	1 =>	array(
				'icon' => '<img src="design/mm/insert_file.gif" alt="{MM_INSERTFILE}" title="{MM_INSERTFILE}" align="middle" />',
				'function' => 'top.opener.insert_replace(\'file\',\'{PATH}\')',
				'filetype' => array(),
				'urlrel' => 'uploads'
				),
	2 =>	array(
				'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERTTEXT}" title="{MM_INSERTTEXT}" align="middle" />',
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

$action['pshow']    =  array(0,0,7,0);
$action['padd']     =  array(1,0,8,0);
$action['pdel']     =  array(1,0,9,0);

//Kategorien
$action['catshow']  =  array(0,1,14,0);
$action['catadd']   =  array(0,0,15,0);
$action['catedit']  =  array(0,0,16,0);
$action['catdel']   =  array(0,0,17,0);
$action['catclean'] =  array(0,0,18,0);
//$action['catmove']  =  array(0,0,19,0);

//Stats
$action['stats']    =  array(0,1,20,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen              F           V
$func['LASTDOWNLOADS']=array('downloads_last',true);
$func['TOPDOWNLOADS']=array('downloads_top',true);
$func['NOTTOPDOWNLOADS']=array('downloads_nottop',true);
$func['BESTDOWNLOADS_HITS']=array('downloads_best_hits',true);
$func['BESTDOWNLOADS_RATING']=array('downloads_best_rating',true);
$func['DOWNLOADS_CATEGORIES']=array('downloads_categories',true);
$func['DOWNLOADS_SIMILAR']=array('downloads_similar',true);
$func['DOWNLOADS_RANDOM']=array('downloads_random',true);
$func['PRODUCT_DOWNLOADS']=array('downloads_product',true);
$func['DOWNLOADS_TAGCLOUD']=array('downloads_tagcloud',true);
$func['DOWNLOADS_STATS']=array('downloads_stats',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>