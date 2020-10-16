<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 320,
    'id' => 'downloads',
    'dependence' => ['comments', 'ratings'],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.3',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
    'mediainput' => [
        1 => [
            'icon' => '<img src="design/mm/insert_file.gif" alt="{MM_INSERTFILE}" title="{MM_INSERTFILE}" align="middle" />',
            'function' => 'top.opener.insert_replace(\'file\',\'{PATH}\')',
            'filetype' => [],
            'urlrel' => 'uploads',
        ],
        2 => [
            'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERTTEXT}" title="{MM_INSERTTEXT}" align="middle" />',
            'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'httpdir',
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

$action['pshow'] = [0, 0, 7, 0];
$action['padd'] = [1, 0, 8, 0];
$action['pdel'] = [1, 0, 9, 0];

//Kategorien
$action['catshow'] = [0, 1, 14, 0];
$action['catadd'] = [0, 0, 15, 0];
$action['catedit'] = [0, 0, 16, 0];
$action['catdel'] = [0, 0, 17, 0];
$action['catclean'] = [0, 0, 18, 0];
//$action['catmove']  =  array(0,0,19,0);

//Stats
$action['stats'] = [0, 1, 20, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen              F           V
$func['LASTDOWNLOADS'] = ['downloads_last', true];
$func['TOPDOWNLOADS'] = ['downloads_top', true];
$func['NOTTOPDOWNLOADS'] = ['downloads_nottop', true];
$func['BESTDOWNLOADS_HITS'] = ['downloads_best_hits', true];
$func['BESTDOWNLOADS_RATING'] = ['downloads_best_rating', true];
$func['DOWNLOADS_CATEGORIES'] = ['downloads_categories', true];
$func['DOWNLOADS_SIMILAR'] = ['downloads_similar', true];
$func['DOWNLOADS_RANDOM'] = ['downloads_random', true];
$func['PRODUCT_DOWNLOADS'] = ['downloads_product', true];
$func['DOWNLOADS_TAGCLOUD'] = ['downloads_tagcloud', true];
$func['DOWNLOADS_STATS'] = ['downloads_stats', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
