<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 965,
    'id' => 'forum',
    'dependence' => [],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.2.1',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
];

//Aktionen registrieren          S V O R
$action['searchuser'] = [0, 0, 0, 1];

$action['show'] = [0, 1, 1, 0];
$action['add'] = [0, 0, 2, 0];
$action['edit'] = [0, 0, 3, 0];
$action['del'] = [0, 0, 4, 0];
$action['clean'] = [0, 0, 5, 0];
//$action['move']         =  array(0,0,6,0);

$action['announce'] = [0, 1, 7, 0];
$action['ranks'] = [0, 1, 8, 0];
$action['icons'] = [0, 1, 9, 0];
$action['filetypes'] = [0, 1, 10, 0];
$action['reindex'] = [0, 1, 11, 0];
$action['resync'] = [0, 1, 12, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen       F           V
$func['THREADS_NEW'] = ['forum_threads_new', true];
$func['THREADS_UPDATED'] = ['forum_threads_updated', true];
$func['FORUM_STATS'] = ['forum_stats', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
