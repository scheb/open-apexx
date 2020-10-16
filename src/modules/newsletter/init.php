<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 2000,
    'id' => 'newsletter',
    'dependence' => [],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.1',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
    'mediainput' => [
        1 => [
            'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_USEIMAGE}" title="{MM_USEIMAGE}" style="vertical-align:middle;" />',
            'function' => 'top.opener.CKEDITOR.tools.callFunction({FUNCNUM}, \'{PATH}\');',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'http',
        ],
        2 => [
            'icon' => '<img src="design/mm/insert_swf.gif" alt="{MM_USESWF}" title="{MM_USESWF}" style="vertical-align:middle;" />',
            'function' => 'top.opener.CKEDITOR.tools.callFunction({FUNCNUM}, \'{PATH}\');',
            'filetype' => ['SWF'],
            'urlrel' => 'http',
        ],
    ],
];

//Aktionen registrieren      S V O R
$action['show'] = [0, 1, 1, 0];
$action['add'] = [0, 1, 2, 0];
$action['addnews'] = [0, 0, 3, 0];
$action['edit'] = [0, 0, 4, 0];
$action['del'] = [0, 0, 5, 0];
$action['send'] = [0, 0, 6, 0];
$action['preview'] = [0, 0, 7, 0];

$action['eshow'] = [0, 1, 8, 0];
$action['eadd'] = [0, 0, 9, 0];
$action['eedit'] = [0, 0, 10, 0];
$action['edel'] = [0, 0, 11, 0];
$action['eenable'] = [0, 0, 12, 0];
$action['eimport'] = [0, 1, 13, 0];

$action['catshow'] = [0, 1, 14, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen       F         V
$func['NEWSLETTER'] = ['newsletter_form', false];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
