<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 50,
    'id' => 'products',
    'dependence' => [],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.8',
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

//Aktionen registrieren      S V O R
$action['show'] = [0, 1, 1, 0];
$action['add'] = [0, 1, 2, 0];
$action['edit'] = [0, 0, 3, 0];
$action['del'] = [0, 0, 4, 0];
$action['enable'] = [0, 0, 5, 0];
$action['disable'] = [0, 0, 6, 0];

$action['ushow'] = [0, 1, 5, 0];
$action['uadd'] = [0, 0, 6, 0];
$action['uedit'] = [0, 0, 7, 0];
$action['udel'] = [0, 0, 8, 0];

$action['media'] = [0, 1, 9, 0];
$action['genre'] = [0, 1, 10, 0];
$action['systems'] = [0, 1, 11, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen       F           V
$afunc['PRODUCTS'] = ['products_list', true];

//Template-Funktionen       F           V
$func['LASTPRODUCTS'] = ['products_last', true];
$func['TOPPRODUCTS'] = ['products_top', true];
$func['NOTTOPPRODUCTS'] = ['products_nottop', true];
$func['BESTPRODUCTS_HITS'] = ['products_best_hits', true];
$func['BESTPRODUCTS_RATING'] = ['products_best_rating', true];
$func['PRODUCTS_RELEASES'] = ['products_releases', true];
$func['PRODUCTS_SIMILAR'] = ['products_similar', true];
$func['PRODUCTS_RANDOM'] = ['products_random', true];
$func['PRODUCTS_RELATED'] = ['products_related', true];
$func['PRODUCTS_COLLECTION'] = ['products_collection', true];
$func['PRODUCTS_COLLECTION_USER'] = ['products_collection_user', true];
$func['PRODUCT_INFO'] = ['products_info', true];
$func['MANUFACTURER_PRODUCTS'] = ['products_form_manufacturer', true];
$func['PRODUCTS_TAGCLOUD'] = ['products_tagcloud', true];
$func['PRODUCTS_STATS'] = ['products_stats', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
