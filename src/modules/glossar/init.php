<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 890,
    'id' => 'glossar',
    'dependence' => [],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.1',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
    'mediainput' => [
        1 => [
            'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERTTEXT}" title="{MM_INSERTTEXT}" style="vertical-align:middle;" />',
            'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'httpdir',
        ],
    ],
];

//Glossar                    S V O R
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

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen       F         V
$func['GLOSSAR_ALPHABETICAL'] = ['glossar_alphabetical', true];
$func['GLOSSAR_LAST'] = ['glossar_last', true];
$func['GLOSSAR_BEST_HITS'] = ['glossar_best_hits', true];
$func['GLOSSAR_BEST_RATING'] = ['glossar_best_rating', true];
$func['GLOSSAR_SIMILAR'] = ['glossar_similar', true];
$func['GLOSSAR_RANDOM'] = ['glossar_random', true];
$func['GLOSSAR_TAGCLOUD'] = ['glossar_tagcloud', true];
$func['GLOSSAR_STATS'] = ['glossar_stats', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
