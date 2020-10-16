<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Modul registrieren
$module = array(1,950,
'id' => 'content',
'dependence' => array('comments','ratings'),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.2',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de',
'mediainput' => array(
	1 =>	array(
				'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERTCONTENT}" title="{MM_INSERTCONTENT}" style="vertical-align:middle;" />',
				'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
				'filetype' => array('GIF','JPG','JPEG','JPE','PNG'),
				'urlrel' => 'httpdir'
				)
	)
);


//Aktionen registrieren     S V O R
$action['show']    =  array(0,1,1,0);
$action['add']     =  array(0,1,2,0);
$action['edit']    =  array(1,0,3,0);
$action['del']     =  array(1,0,4,0);
$action['enable']  =  array(1,0,5,0);
$action['disable'] =  array(1,0,6,0);
$action['group']   =  array(0,1,7,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen     F           V
//$func['']=array('',true);
$func['CONTENT_STATS']=array('content_stats',true);
$func['CONTENT_SHOW'] =array('content_show',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>