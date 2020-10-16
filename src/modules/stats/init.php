<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 99999,
    'id' => 'stats',
    'dependence' => [],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.0',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
];

//Aktionen registrieren      S V O R
$action['visitors'] = [0, 1, 1, 0];
$action['agents'] = [0, 1, 2, 0];
$action['os'] = [0, 1, 3, 0];
$action['countries'] = [0, 1, 4, 0];
$action['referer'] = [1, 1, 5, 0];
$action['searched'] = [0, 1, 6, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen          F         V
$func['COUNTER'] = ['stats_counter', false];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
