<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 99999999,
    'id' => 'main',
    'dependence' => [],
    'requirement' => [],
    'version' => '1.2.2',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
    'mediainput' => [
        1 => [
            'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERT}" title="{MM_INSERT}" style="vertical-align:middle;" />',
            'function' => 'top.opener.insert_image(\'code\',\'{PATH}\')',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'httpdir',
        ],
    ],
];

//Aktionen registrieren       S V O R
$action['index'] = [0, 0, 1, 1];

//Module
$action['mshow'] = [0, 1, 5, 0];
$action['minstall'] = [0, 0, 6, 0];
$action['muninstall'] = [0, 0, 7, 0];
$action['menable'] = [0, 0, 8, 0];
$action['mdisable'] = [0, 0, 9, 0];
$action['mupdate'] = [0, 0, 10, 0];
$action['mconfig'] = [0, 0, 11, 0];

$action['secshow'] = [0, 1, 12, 0];
$action['lshow'] = [0, 1, 15, 0];
$action['tshow'] = [0, 1, 19, 0];
$action['snippets'] = [0, 1, 21, 0];
$action['tags'] = [0, 1, 22, 0];
$action['sshow'] = [0, 1, 23, 0];
$action['cshow'] = [0, 1, 27, 0];
$action['bshow'] = [0, 1, 31, 0];

$action['env'] = [0, 1, 950, 0];
$action['searches'] = [0, 1, 960, 0];
$action['log'] = [1, 1, 970, 0];
$action['close'] = [0, 1, 980, 0];
$action['delcache'] = [0, 1, 990, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen      F           V
$func['SEARCH'] = ['main_searchbox', true];
$func['DATE'] = ['main_mkdate', true];
$func['PRINT'] = ['main_printlink', false];
$func['TELL'] = ['main_telllink', false];
$func['SHORTTEXT'] = ['main_shorttext', true];
$func['TITLEBAR'] = ['main_set_titlebar', true];
$func['HEADLINE'] = ['main_set_headline', true];
$func['DESIGN'] = ['main_set_design', true];
$func['SNIPPET'] = ['main_snippet', true];
$func['SECTIONS'] = ['main_sections', true];

//Admin-Template-Funktionen    F         V
$afunc['TEXTAREA'] = ['main_textbox', true];
$afunc['SECTIONS'] = ['main_sections', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
