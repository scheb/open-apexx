<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Modul registrieren
$module = array(1,99999,
'id' => 'comments',
'dependence' => array(),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.0',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de'
);


//Aktionen registrieren           S V O R
$action['show']          =  array(0,1,1,0);
$action['edit']          =  array(0,0,2,0);
$action['del']           =  array(0,0,3,0);
$action['enable']        =  array(0,0,4,0);
$action['disable']       =  array(0,0,5,0);
$action['blockip']       =  array(0,0,6,0);
$action['blockcontent']  =  array(0,0,8,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen                         F           V
$func['COMMENTS']=array('comments_usercount',true);
$func['COMMENTS_STATS']=array('comments_stats',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>