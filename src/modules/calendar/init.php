<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 875,
    'id' => 'calendar',
    'dependence' => [],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.3',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
    'mediainput' => [
        1 => [
            'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_INSERTPIC}" title="{MM_INSERTPIC}" style="vertical-align:middle;" />',
            'function' => 'top.opener.insert_replace(\'pic_copy\',\'{PATH}\')',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'uploads',
        ],
        2 => [
            'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERTTEXT}" title="{MM_INSERTTEXT}" style="vertical-align:middle;" />',
            'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'httpdir',
        ],
    ],
];

//Kalender                   S V O R
$action['show'] = [0, 1, 1, 0];
$action['add'] = [0, 1, 2, 0];
$action['edit'] = [0, 0, 3, 0];
$action['copy'] = [1, 0, 4, 0];
$action['del'] = [0, 0, 5, 0];
$action['enable'] = [0, 0, 6, 0];
$action['disable'] = [0, 0, 7, 0];

$action['catshow'] = [0, 1, 8, 0];
$action['catadd'] = [0, 0, 9, 0];
$action['catedit'] = [0, 0, 10, 0];
$action['catdel'] = [0, 0, 11, 0];
$action['catclean'] = [0, 0, 12, 0];
//$action['catmove']  =  array(0,0,13,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen       F         V
$func['LASTEVENTS'] = ['calendar_events_last', true];
$func['RECENTEVENTS'] = ['calendar_events_recent', true];
$func['NEXTDAYSEVENTS'] = ['calendar_events_nextdays', true];
$func['OLDEVENTS'] = ['calendar_events_old', true];
$func['LASTDAYSEVENTS'] = ['calendar_events_lastdays', true];
$func['PARTEVENTS'] = ['calendar_events_participate', true];
$func['EVENTS_SIMILAR'] = ['calendar_events_similar', true];
$func['EVENTS_RANDOM'] = ['calendar_events_random', true];
$func['EVENTS_CATEGORIES'] = ['calendar_events_categories', true];
$func['MINICALENDAR'] = ['calendar_mini', true];
$func['CALENDAR_TAGCLOUD'] = ['calendar_tagcloud', true];
$func['CALENDAR_STATS'] = ['calendar_stats', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
