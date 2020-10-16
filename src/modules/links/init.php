<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 870,
    'id' => 'links',
    'dependence' => ['comments', 'ratings'],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.1',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
    'mediainput' => [
        1 => [
            'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_INSERTLINKPIC}" title="{MM_INSERTLINKPIC}" style="vertical-align:middle;" />',
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

//Artikel auflisten          S V O R
$action['show'] = [0, 1, 1, 0];
$action['add'] = [0, 1, 2, 0];
$action['edit'] = [1, 0, 3, 0];
$action['del'] = [1, 0, 4, 0];
$action['enable'] = [1, 0, 5, 0];
$action['disable'] = [1, 0, 6, 0];

//Kategorien
$action['catshow'] = [0, 1, 14, 0];
$action['catadd'] = [0, 0, 15, 0];
$action['catedit'] = [0, 0, 16, 0];
$action['catdel'] = [0, 0, 17, 0];
$action['catclean'] = [0, 0, 18, 0];
//$action['catmove']  =  array(0,0,19,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen              F           V
$func['LASTLINKS'] = ['links_last', true];
$func['TOPLINKS'] = ['links_top', true];
$func['NOTTOPLINKS'] = ['links_nottop', true];
$func['BESTLINKS_HITS'] = ['links_best_hits', true];
$func['BESTLINKS_RATING'] = ['links_best_rating', true];
$func['LINKS_SIMILAR'] = ['links_similar', true];
$func['LINKS_RANDOM'] = ['links_random', true];
$func['LINKS_CATEGORIES'] = ['links_categories', true];
$func['LINKS_TAGCLOUD'] = ['links_tagcloud', true];
$func['LINKS_STATS'] = ['links_stats', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
