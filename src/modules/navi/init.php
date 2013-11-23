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
$module = array(1,99980,
'id' => 'navi',
'dependence' => array(),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.1',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array()
);


//Navigation              S V O R
$action['show']  =  array(0,1,1,0);
$action['add']   =  array(0,0,2,0);
$action['edit']  =  array(0,0,3,0);
$action['del']   =  array(0,0,4,0);
//$action['move']  =  array(0,0,5,0);
$action['group']   =  array(0,1,7,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen       F         V
$func['NAVI_TREE']=array('navi_tree',true);
$func['NAVI_LEVEL']=array('navi_level',true);
$func['NAVI_NODE']=array('navi_node',true);
$func['NAVI_BREADCRUMB']=array('navi_breadcrumb',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>