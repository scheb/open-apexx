<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 320,
    'id' => 'videos',
    'dependence' => ['comments', 'ratings'],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.0.3',
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
            'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERTTEXT}" title="{MM_INSERTTEXT}" align="middle" />',
            'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'httpdir',
        ],
        3 => [
            'icon' => '<img src="design/mm/insert_file.gif" alt="{MM_INSERTFILE}" title="{MM_INSERTFILE}" align="middle" />',
            'function' => 'top.opener.insert_file(\'{PATH}\')',
            'filetype' => ['WMV', 'MPG', 'MPEG', 'MP4', 'AVI', 'MOV', 'FLV'],
            'urlrel' => 'uploads',
        ],
        4 => [
            'icon' => '<img src="design/mm/insert_flv.gif" alt="{MM_INSERTFLV}" title="{MM_INSERTFLV}" align="middle" />',
            'function' => 'top.opener.insert_replace(\'select_flv\',\'{PATH}\')',
            'filetype' => ['FLV', 'F4V'],
            'urlrel' => 'uploads',
        ],
    ],
];

//Artikel auflisten          S V O R
$action['show'] = [0, 1, 1, 0];
$action['add'] = [0, 1, 2, 0];
$action['edit'] = [1, 0, 3, 0];
$action['del'] = [1, 0, 4, 0];
$action['enable'] = [1, 0, 5, 0];
$action['disable'] = [1, 0, 6, 0];
$action['convert'] = [0, 0, 7, 1];

$action['pshow'] = [0, 0, 8, 0];
$action['padd'] = [1, 0, 9, 0];
$action['pdel'] = [1, 0, 10, 0];

//Kategorien
$action['catshow'] = [0, 1, 14, 0];
$action['catadd'] = [0, 0, 15, 0];
$action['catedit'] = [0, 0, 16, 0];
$action['catdel'] = [0, 0, 17, 0];
$action['catclean'] = [0, 0, 18, 0];
//$action['catmove']  =  array(0,0,19,0);

//Stats
$action['stats'] = [0, 1, 20, 0];

//Konfig
$action['cfg'] = [0, 1, 99, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen              F           V
$func['LASTVIDEOS'] = ['videos_last', true];
$func['TOPVIDEOS'] = ['videos_top', true];
$func['NOTTOPVIDEOS'] = ['videos_nottop', true];
$func['BESTVIDEOS_HITS'] = ['videos_best_hits', true];
$func['BESTVIDEOS_RATING'] = ['videos_best_rating', true];
$func['VIDEOS_CATEGORIES'] = ['videos_categories', true];
$func['VIDEOS_SIMILAR'] = ['videos_similar', true];
$func['VIDEOS_RANDOM'] = ['videos_random', true];
$func['PRODUCT_VIDEOS'] = ['videos_product', true];
$func['VIDEOS_TAGCLOUD'] = ['videos_tagcloud', true];
$func['VIDEOS_STATS'] = ['videos_stats', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
