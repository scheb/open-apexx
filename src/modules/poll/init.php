<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 855,
    'id' => 'poll',
    'dependence' => ['comments'],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.1',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
];

//Umfragen
$action['show'] = [0, 1, 1, 0];
$action['add'] = [0, 1, 2, 0];
$action['edit'] = [0, 0, 3, 0];
$action['del'] = [0, 0, 4, 0];
$action['enable'] = [0, 0, 5, 0];
$action['disable'] = [0, 0, 6, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen      F        V
$func['POLL'] = ['poll_small', true];
$func['POLL_RANDOM'] = ['poll_random', true];
$func['POLL_SIMILAR'] = ['poll_similar', true];
$func['POLL_TAGCLOUD'] = ['poll_tagcloud', true];
$func['POLL_STATS'] = ['poll_stats', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
