<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Modul registrieren
$module = array(0,8900,
'id' => 'formmailer',
'dependence' => array(),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.0',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array()
);


//Aktionen registrieren     S V O R
//$action['']    =  array(0,1,1,0);
//NONE


/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte f�r Alle
*/


//Template-Funktionen     F           V
//$func['']=array('content_link',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>