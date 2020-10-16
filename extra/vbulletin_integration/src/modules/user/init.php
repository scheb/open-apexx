<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 999999,
    'id' => 'user',
    'dependence' => [],
    'version' => '1.1.1',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
];

//Aktionen registrieren       S V O R
$action['login'] = [0, 0, 1, 1];
$action['logout'] = [0, 0, 2, 1];
$action['autologout'] = [0, 0, 3, 1];

$action['show'] = [0, 1, 4, 0];
$action['add'] = [0, 1, 5, 0];
$action['edit'] = [0, 0, 6, 0];
$action['del'] = [0, 0, 7, 0];
$action['enable'] = [0, 0, 8, 0];

$action['gshow'] = [0, 1, 12, 0];
$action['gadd'] = [0, 0, 13, 0];
$action['gedit'] = [0, 0, 14, 0];
$action['gdel'] = [0, 0, 15, 0];

$action['profile'] = [0, 0, 98, 0];
$action['myprofile'] = [0, 1, 99, 1];

$action['guestbook'] = [0, 1, 991, 0];
$action['blog'] = [0, 1, 992, 0];
$action['gallery'] = [0, 1, 993, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen         F         V
$func['USERONLINE'] = ['user_online', false];
$func['LOGINBOX'] = ['user_loginbox', false];
$func['PROFILE'] = ['user_profile', true];
$func['THREADS_NEW'] = ['vbthreads_newthreads', true];
$func['THREADS_UPDATED'] = ['vbthreads_updatedthreads', true];

//Admin-Template-Funktionen       F         V
$afunc['USER'] = ['user_team', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
