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
$module = array(1,875,
'id' => 'calendar',
'dependence' => array(),
'requirement' => array('main' => '1.2.0'),
'version' => '1.1.3',
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


//Kalender                   S V O R
$action['show']     =  array(0,1,1,0);
$action['add']      =  array(0,1,2,0);
$action['edit']     =  array(0,0,3,0);
$action['copy']     =  array(1,0,4,0);
$action['del']      =  array(0,0,5,0);
$action['enable']   =  array(0,0,6,0);
$action['disable']  =  array(0,0,7,0);

$action['catshow']  =  array(0,1,8,0);
$action['catadd']   =  array(0,0,9,0);
$action['catedit']  =  array(0,0,10,0);
$action['catdel']   =  array(0,0,11,0);
$action['catclean'] =  array(0,0,12,0);
//$action['catmove']  =  array(0,0,13,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen       F         V
$func['LASTEVENTS']=array('calendar_events_last',true);
$func['RECENTEVENTS']=array('calendar_events_recent',true);
$func['NEXTDAYSEVENTS']=array('calendar_events_nextdays',true);
$func['OLDEVENTS']=array('calendar_events_old',true);
$func['LASTDAYSEVENTS']=array('calendar_events_lastdays',true);
$func['PARTEVENTS']=array('calendar_events_participate',true);
$func['EVENTS_SIMILAR']=array('calendar_events_similar',true);
$func['EVENTS_RANDOM']=array('calendar_events_random',true);
$func['EVENTS_CATEGORIES']=array('calendar_events_categories',true);
$func['MINICALENDAR']=array('calendar_mini',true);
$func['CALENDAR_TAGCLOUD']=array('calendar_tagcloud',true);
$func['CALENDAR_STATS']=array('calendar_stats',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>