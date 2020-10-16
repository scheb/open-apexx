<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 450,
    'id' => 'gallery',
    'dependence' => ['comments', 'ratings'],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.2',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
];

//Aktionen registrieren      S V O R
//Galerien
$action['show'] = [0, 1, 1, 0];
$action['add'] = [0, 1, 2, 0];
$action['edit'] = [0, 0, 3, 0];
$action['del'] = [0, 0, 4, 0];
$action['enable'] = [0, 0, 5, 0];
$action['disable'] = [0, 0, 6, 0];
$action['move'] = [0, 0, 7, 0];

//Bilder
$action['pshow'] = [0, 0, 8, 0];
$action['padd'] = [0, 0, 9, 0];
$action['pedit'] = [0, 0, 10, 0];
$action['pmove'] = [0, 0, 11, 0];
$action['pdel'] = [0, 0, 12, 0];
$action['penable'] = [0, 0, 13, 0];
$action['pdisable'] = [0, 0, 14, 0];
$action['potw'] = [0, 0, 15, 0];
$action['preview'] = [0, 0, 16, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen              F           V
$func['CHOOSEGALLERY'] = ['gallery_choose', false];
$func['GALLERY'] = ['gallery_randompics', true];
$func['GALLERY_LAST'] = ['gallery_last', true];
$func['GALLERY_UPDATED'] = ['gallery_updated', true];
$func['GALLERY_LASTPICS'] = ['gallery_lastpics', true];
$func['GALLERY_BESTPICS_HITS'] = ['gallery_bestpics_hits', true];
$func['GALLERY_BESTPICS_RATING'] = ['gallery_bestpics_rating', true];
$func['GALLERY_POTW'] = ['gallery_potw', false];
$func['GALLERY_POTM'] = ['gallery_potm', true];
$func['GALLERY_SIMILAR'] = ['gallery_similar', true];
$func['GALLERY_RANDOM'] = ['gallery_random', true];
$func['PRODUCT_GALLERY'] = ['gallery_products', true];
$func['PRODUCT_GALLERYPICS'] = ['gallery_productspics', true];
$func['GALLERY_TAGCLOUD'] = ['gallery_tagcloud', true];
$func['GALLERY_STATS'] = ['gallery_stats', true];

//Admin-Template-Funktionen       F           V
$afunc['GALLERIES'] = ['gallery_list', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
