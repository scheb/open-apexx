<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [0, 0,
    'id' => 'mediamanager',
    'dependence' => [],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.0',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
    'mediainput' => [
        1 => [
            'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_USEIMAGE}" title="{MM_USEIMAGE}" style="vertical-align:middle;" />',
            'function' => 'top.opener.CKEDITOR.tools.callFunction({FUNCNUM}, \'{PATH}\');',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'httpdir',
        ],
        2 => [
            'icon' => '<img src="design/mm/insert_swf.gif" alt="{MM_USESWF}" title="{MM_USESWF}" style="vertical-align:middle;" />',
            'function' => 'top.opener.CKEDITOR.tools.callFunction({FUNCNUM}, \'{PATH}\');',
            'filetype' => ['SWF'],
            'urlrel' => 'httpdir',
        ],
    ],
];

//Aktionen registrieren      S V O R
$action['inline'] = [0, 0, 99, 1];
$action['index'] = [0, 1, 1, 1];
$action['search'] = [0, 1, 2, 0];

$action['diradd'] = [0, 0, 3, 0];
$action['dirdel'] = [0, 0, 4, 0];
$action['dirrename'] = [0, 0, 5, 0];

$action['upload'] = [0, 0, 6, 0];
$action['sts'] = [0, 0, 7, 0];
$action['del'] = [0, 0, 8, 0];
$action['copy'] = [0, 0, 9, 0];
$action['move'] = [0, 0, 10, 0];
$action['rename'] = [0, 0, 11, 0];
$action['details'] = [0, 0, 12, 0];
$action['thumb'] = [0, 0, 13, 0];

$action['rules'] = [0, 1, 14, 0];
$action['radd'] = [0, 0, 15, 0];
$action['redit'] = [0, 0, 16, 0];
$action['rdel'] = [0, 0, 17, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Admin-Template-Funktionen         F           V
$afunc['INLINE'] = ['mediamanager_inline', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
