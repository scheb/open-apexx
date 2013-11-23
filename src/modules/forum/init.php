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
$module = array(1,965,
'id' => 'forum',
'dependence' => array(),
'requirement' => array('main' => '1.2.0'),
'version' => '1.2.1',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de'
);


//Aktionen registrieren          S V O R
$action['searchuser']   =  array(0,0,0,1);

$action['show']         =  array(0,1,1,0);
$action['add']          =  array(0,0,2,0);
$action['edit']         =  array(0,0,3,0);
$action['del']          =  array(0,0,4,0);
$action['clean']        =  array(0,0,5,0);
//$action['move']         =  array(0,0,6,0);

$action['announce']     =  array(0,1,7,0);
$action['ranks']        =  array(0,1,8,0);
$action['icons']        =  array(0,1,9,0);
$action['filetypes']    =  array(0,1,10,0);
$action['reindex']      =  array(0,1,11,0);
$action['resync']       =  array(0,1,12,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen       F           V
$func['THREADS_NEW']=array('forum_threads_new',true);
$func['THREADS_UPDATED']=array('forum_threads_updated',true);
$func['FORUM_STATS']=array('forum_stats',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>