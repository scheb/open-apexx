<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Modul registrieren
$module = array(1,50,
'id' => 'products',
'dependence' => array(),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.8',
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
				'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERTTEXT}" title="{MM_INSERTTEXT}" style="vertical-align:middle;" />',
				'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'httpdir'
				)
	)
);


//Aktionen registrieren      S V O R
$action['show']      =  array(0,1,1,0);
$action['add']       =  array(0,1,2,0);
$action['edit']      =  array(0,0,3,0);
$action['del']       =  array(0,0,4,0);
$action['enable']    =  array(0,0,5,0);
$action['disable']   =  array(0,0,6,0);

$action['ushow']     =  array(0,1,5,0);
$action['uadd']      =  array(0,0,6,0);
$action['uedit']     =  array(0,0,7,0);
$action['udel']      =  array(0,0,8,0);

$action['media']     =  array(0,1,9,0);
$action['genre']     =  array(0,1,10,0);
$action['systems']   =  array(0,1,11,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen       F           V
$afunc['PRODUCTS']=array('products_list',true);

//Template-Funktionen       F           V
$func['LASTPRODUCTS']=array('products_last',true);
$func['TOPPRODUCTS']=array('products_top',true);
$func['NOTTOPPRODUCTS']=array('products_nottop',true);
$func['BESTPRODUCTS_HITS']=array('products_best_hits',true);
$func['BESTPRODUCTS_RATING']=array('products_best_rating',true);
$func['PRODUCTS_RELEASES']=array('products_releases',true);
$func['PRODUCTS_SIMILAR']=array('products_similar',true);
$func['PRODUCTS_RANDOM']=array('products_random',true);
$func['PRODUCTS_RELATED']=array('products_related',true);
$func['PRODUCTS_COLLECTION']=array('products_collection',true);
$func['PRODUCTS_COLLECTION_USER']=array('products_collection_user',true);
$func['PRODUCT_INFO']=array('products_info',true);
$func['MANUFACTURER_PRODUCTS']=array('products_form_manufacturer',true);
$func['PRODUCTS_TAGCLOUD']=array('products_tagcloud',true);
$func['PRODUCTS_STATS']=array('products_stats',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>