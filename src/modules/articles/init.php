<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Modul registrieren
$module = array(1,230,
'id' => 'articles',
'dependence' => array('comments','ratings','gallery'),
'requirement' => array('main' => '1.2.0'),
'version' => '1.2.1',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array(
	1 =>	array(
			'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_INSERTARTPIC}" title="{MM_INSERTARTPIC}" style="vertical-align:middle;" />',
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
$action['copy']     =  array(1,0,4,0);
$action['del']      =  array(1,0,5,0);
$action['enable']   =  array(1,0,6,0);
$action['disable']  =  array(1,0,7,0);

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


//Template-Funktionen                F           V
$func['CHOOSEARTICLE']=array('articles_choose',true);
$func['LASTARTICLES']=array('articles_last',true);
$func['TOPARTICLES']=array('articles_top',true);
$func['NOTTOPARTICLES']=array('articles_nottop',true);
$func['BESTARTICLES_HITS']=array('articles_best_hits',true);
$func['BESTARTICLES_RATING']=array('articles_best_rating',true);
$func['ARTICLES_BESTREVIEWS']=array('articles_reviews',true);
$func['ARTICLES_CATEGORIES']=array('articles_categories',true);
$func['ARTICLES_SIMILAR']=array('articles_similar',true);
$func['ARTICLES_RANDOM']=array('articles_random',true);
$func['PRODUCT_ARTICLES']=array('articles_product',true);
$func['ARTICLES_TAGCLOUD']=array('articles_tagcloud',true);
$func['ARTICLES_STATS']=array('articles_stats',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>