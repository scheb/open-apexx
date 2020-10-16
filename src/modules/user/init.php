<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 999999,
    'id' => 'user',
    'dependence' => [],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.2.3',
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
$action['gclean'] = [0, 0, 15, 0];
$action['gdel'] = [0, 0, 16, 0];

$action['profile'] = [0, 0, 98, 0];
$action['myprofile'] = [0, 1, 99, 1];

$action['guestbook'] = [0, 1, 991, 0];
$action['blog'] = [0, 1, 992, 0];
$action['gallery'] = [0, 1, 993, 0];

$action['sendmail'] = [0, 1, 2000, 0];
$action['sendpm'] = [0, 1, 2001, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen         F         V
$func['USER_INFO'] = ['user_info', true];
$func['USERONLINE'] = ['user_online', false];
$func['NEWPMS'] = ['user_newpms', false];
$func['NEWGBENTRIES'] = ['user_newgbs', false];
$func['ONLINELIST'] = ['user_onlinelist', true];
$func['LOGINBOX'] = ['user_loginbox', false];
$func['BIRTHDAYS'] = ['user_birthdays', true];
$func['BIRTHDAYS_TOMORROW'] = ['user_birthdays_tomorrow', true];
$func['BIRTHDAYS_NEXTDAYS'] = ['user_birthdays_nextdays', true];
$func['BUDDYLIST'] = ['user_buddylist', true];
$func['NEWUSER'] = ['user_new', true];
$func['RANDOMUSER'] = ['user_random', true];
$func['PROFILE'] = ['user_profile', true];
$func['BOOKMARK'] = ['user_bookmarklink', false];
$func['SHOWBOOKMARKS'] = ['user_bookmarks', true];
$func['ONLINERECORD'] = ['user_onlinerecord', true];
$func['USERBLOGS'] = ['user_blogs_last', true];
$func['USERGALLERY_LAST'] = ['user_gallery_last', true];
$func['USERGALLERY_UPDATED'] = ['user_gallery_updated', true];
$func['USERGALLERY_LASTPICS'] = ['user_gallery_lastpics', true];
$func['USERGALLERY_POTM'] = ['user_gallery_potm', true];
$func['USER_STATS'] = ['user_stats', true];
$func['USERSTATUS'] = ['user_status', true];

//Admin-Template-Funktionen       F         V
$afunc['USER'] = ['user_team', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
