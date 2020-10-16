<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 100,
    'id' => 'news',
    'dependence' => ['comments', 'ratings', 'gallery'],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.1',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
    'mediainput' => [
        1 => [
            'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_INSERTNEWSPIC}" title="{MM_INSERTNEWSPIC}" style="vertical-align:middle;" />',
            'function' => 'top.opener.insert_replace(\'pic_copy\',\'{PATH}\')',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'uploads',
        ],
        2 => [
            'icon' => '<img src="design/mm/insert_text1.gif" alt="{MM_INSERTTEASER}" title="{MM_INSERTTEASER}" style="vertical-align:middle;" />',
            'function' => 'top.opener.insert_image(\'teaser\',\'{PATH}\')',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'httpdir',
        ],
        3 => [
            'icon' => '<img src="design/mm/insert_text2.gif" alt="{MM_INSERTTEXT}" title="{MM_INSERTTEXT}" style="vertical-align:middle;" />',
            'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'httpdir',
        ],
    ],
];

//Newsmanagement             S V O R
$action['show'] = [0, 1, 1, 0];
$action['add'] = [0, 1, 2, 0];
$action['edit'] = [1, 0, 3, 0];
$action['copy'] = [1, 0, 4, 0];
$action['del'] = [1, 0, 5, 0];
$action['enable'] = [1, 0, 6, 0];
$action['disable'] = [1, 0, 7, 0];

//Kategorien
$action['catshow'] = [0, 1, 8, 0];
$action['catadd'] = [0, 0, 9, 0];
$action['catedit'] = [0, 0, 10, 0];
$action['catdel'] = [0, 0, 11, 0];
$action['catclean'] = [0, 0, 12, 0];
//$action['catmove']  =  array(0,0,13,0);

//News-Quellen
$action['sshow'] = [0, 1, 19, 0];
$action['sadd'] = [0, 0, 20, 0];
$action['sedit'] = [0, 0, 21, 0];
$action['sdel'] = [0, 0, 22, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen       F         V
$func['LASTNEWS'] = ['news_last', true];
$func['TOPNEWS'] = ['news_top', true];
$func['NOTTOPNEWS'] = ['news_nottop', true];
$func['BESTNEWS_HITS'] = ['news_best_hits', true];
$func['BESTNEWS_RATING'] = ['news_best_rating', true];
$func['NEWS_CATEGORIES'] = ['news_categories', true];
$func['NEWS_SIMILAR'] = ['news_similar', true];
$func['NEWS_RANDOM'] = ['news_random', true];
$func['PRODUCT_NEWS'] = ['news_product', true];
$func['NEWS_TAGCLOUD'] = ['news_tagcloud', true];
$func['NEWS_STATS'] = ['news_stats', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
