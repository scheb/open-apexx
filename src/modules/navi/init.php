<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 99980,
    'id' => 'navi',
    'dependence' => [],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.1',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
    'mediainput' => [],
];

//Navigation              S V O R
$action['show'] = [0, 1, 1, 0];
$action['add'] = [0, 0, 2, 0];
$action['edit'] = [0, 0, 3, 0];
$action['del'] = [0, 0, 4, 0];
//$action['move']  =  array(0,0,5,0);
$action['group'] = [0, 1, 7, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen       F         V
$func['NAVI_TREE'] = ['navi_tree', true];
$func['NAVI_LEVEL'] = ['navi_level', true];
$func['NAVI_NODE'] = ['navi_node', true];
$func['NAVI_BREADCRUMB'] = ['navi_breadcrumb', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
