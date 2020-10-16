<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 230,
    'id' => 'articles',
    'dependence' => ['comments', 'ratings', 'gallery'],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.2.1',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
    'mediainput' => [
        1 => [
            'icon' => '<img src="design/mm/insert_img.gif" alt="{MM_INSERTARTPIC}" title="{MM_INSERTARTPIC}" style="vertical-align:middle;" />',
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
$action['copy'] = [1, 0, 4, 0];
$action['del'] = [1, 0, 5, 0];
$action['enable'] = [1, 0, 6, 0];
$action['disable'] = [1, 0, 7, 0];

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

//Template-Funktionen                F           V
$func['CHOOSEARTICLE'] = ['articles_choose', true];
$func['LASTARTICLES'] = ['articles_last', true];
$func['TOPARTICLES'] = ['articles_top', true];
$func['NOTTOPARTICLES'] = ['articles_nottop', true];
$func['BESTARTICLES_HITS'] = ['articles_best_hits', true];
$func['BESTARTICLES_RATING'] = ['articles_best_rating', true];
$func['ARTICLES_BESTREVIEWS'] = ['articles_reviews', true];
$func['ARTICLES_CATEGORIES'] = ['articles_categories', true];
$func['ARTICLES_SIMILAR'] = ['articles_similar', true];
$func['ARTICLES_RANDOM'] = ['articles_random', true];
$func['PRODUCT_ARTICLES'] = ['articles_product', true];
$func['ARTICLES_TAGCLOUD'] = ['articles_tagcloud', true];
$func['ARTICLES_STATS'] = ['articles_stats', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
