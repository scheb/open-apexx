<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 99999,
    'id' => 'comments',
    'dependence' => [],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.0',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
];

//Aktionen registrieren           S V O R
$action['show'] = [0, 1, 1, 0];
$action['edit'] = [0, 0, 2, 0];
$action['del'] = [0, 0, 3, 0];
$action['enable'] = [0, 0, 4, 0];
$action['disable'] = [0, 0, 5, 0];
$action['blockip'] = [0, 0, 6, 0];
$action['blockcontent'] = [0, 0, 8, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen                         F           V
$func['COMMENTS'] = ['comments_usercount', true];
$func['COMMENTS_STATS'] = ['comments_stats', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
