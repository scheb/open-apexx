<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Modul registrieren
$module = array(1,890,
'id' => 'glossar',
'dependence' => array(),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.1',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array(
	1 =>	array(
				'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERTTEXT}" title="{MM_INSERTTEXT}" style="vertical-align:middle;" />',
				'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'httpdir'
				)
	)
);


//Glossar                    S V O R
$action['show']     =  array(0,1,1,0);
$action['add']      =  array(0,1,2,0);
$action['edit']     =  array(1,0,3,0);
$action['copy']     =  array(1,0,4,0);
$action['del']      =  array(1,0,5,0);
$action['enable']   =  array(1,0,6,0);
$action['disable']  =  array(1,0,7,0);

//Kategorien
$action['catshow']  =  array(0,1,8,0);
$action['catadd']   =  array(0,0,9,0);
$action['catedit']  =  array(0,0,10,0);
$action['catdel']   =  array(0,0,11,0);
$action['catclean'] =  array(0,0,12,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen       F         V
$func['GLOSSAR_ALPHABETICAL']=array('glossar_alphabetical',true);
$func['GLOSSAR_LAST']=array('glossar_last',true);
$func['GLOSSAR_BEST_HITS']=array('glossar_best_hits',true);
$func['GLOSSAR_BEST_RATING']=array('glossar_best_rating',true);
$func['GLOSSAR_SIMILAR']=array('glossar_similar',true);
$func['GLOSSAR_RANDOM']=array('glossar_random',true);
$func['GLOSSAR_TAGCLOUD']=array('glossar_tagcloud',true);
$func['GLOSSAR_STATS']=array('glossar_stats',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>