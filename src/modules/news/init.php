<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Modul registrieren
$module = array(1,100,
'id' => 'news',
'dependence' => array('comments','ratings','gallery'),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.1',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array(
	1 =>	array(
				'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_INSERTNEWSPIC}" title="{MM_INSERTNEWSPIC}" style="vertical-align:middle;" />',
				'function' => 'top.opener.insert_replace(\'pic_copy\',\'{PATH}\')',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'uploads'
				),
	2 =>	array(
				'icon' => '<img src="design/mm/insert_text1.gif" alt="{MM_INSERTTEASER}" title="{MM_INSERTTEASER}" style="vertical-align:middle;" />',
				'function' => 'top.opener.insert_image(\'teaser\',\'{PATH}\')',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'httpdir'
				),
	3 =>	array(
				'icon' => '<img src="design/mm/insert_text2.gif" alt="{MM_INSERTTEXT}" title="{MM_INSERTTEXT}" style="vertical-align:middle;" />',
				'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'httpdir'
				)
	)
);


//Newsmanagement             S V O R
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
//$action['catmove']  =  array(0,0,13,0);

//News-Quellen
$action['sshow']    =  array(0,1,19,0);
$action['sadd']     =  array(0,0,20,0);
$action['sedit']    =  array(0,0,21,0);
$action['sdel']     =  array(0,0,22,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen       F         V
$func['LASTNEWS']=array('news_last',true);
$func['TOPNEWS']=array('news_top',true);
$func['NOTTOPNEWS']=array('news_nottop',true);
$func['BESTNEWS_HITS']=array('news_best_hits',true);
$func['BESTNEWS_RATING']=array('news_best_rating',true);
$func['NEWS_CATEGORIES']=array('news_categories',true);
$func['NEWS_SIMILAR']=array('news_similar',true);
$func['NEWS_RANDOM']=array('news_random',true);
$func['PRODUCT_NEWS']=array('news_product',true);
$func['NEWS_TAGCLOUD']=array('news_tagcloud',true);
$func['NEWS_STATS']=array('news_stats',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>