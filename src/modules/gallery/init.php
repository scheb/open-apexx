<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Modul registrieren
$module = array(1,450,
'id' => 'gallery',
'dependence' => array('comments','ratings'),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.2',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de'
);


//Aktionen registrieren      S V O R
//Galerien
$action['show']     =  array(0,1,1,0);
$action['add']      =  array(0,1,2,0);
$action['edit']     =  array(0,0,3,0);
$action['del']      =  array(0,0,4,0);
$action['enable']   =  array(0,0,5,0);
$action['disable']  =  array(0,0,6,0);
$action['move']     =  array(0,0,7,0);

//Bilder
$action['pshow']    =  array(0,0,8,0);
$action['padd']     =  array(0,0,9,0);
$action['pedit']    =  array(0,0,10,0);
$action['pmove']    =  array(0,0,11,0);
$action['pdel']     =  array(0,0,12,0);
$action['penable']  =  array(0,0,13,0);
$action['pdisable'] =  array(0,0,14,0);
$action['potw']     =  array(0,0,15,0);
$action['preview']  =  array(0,0,16,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen              F           V
$func['CHOOSEGALLERY']=array('gallery_choose',false);
$func['GALLERY']=array('gallery_randompics',true);
$func['GALLERY_LAST']=array('gallery_last',true);
$func['GALLERY_UPDATED']=array('gallery_updated',true);
$func['GALLERY_LASTPICS']=array('gallery_lastpics',true);
$func['GALLERY_BESTPICS_HITS']=array('gallery_bestpics_hits',true);
$func['GALLERY_BESTPICS_RATING']=array('gallery_bestpics_rating',true);
$func['GALLERY_POTW']=array('gallery_potw',false);
$func['GALLERY_POTM']=array('gallery_potm',true);
$func['GALLERY_SIMILAR']=array('gallery_similar',true);
$func['GALLERY_RANDOM']=array('gallery_random',true);
$func['PRODUCT_GALLERY']=array('gallery_products',true);
$func['PRODUCT_GALLERYPICS']=array('gallery_productspics',true);
$func['GALLERY_TAGCLOUD']=array('gallery_tagcloud',true);
$func['GALLERY_STATS']=array('gallery_stats',true);

//Admin-Template-Funktionen       F           V
$afunc['GALLERIES']=array('gallery_list',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>