<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2009, Christian Scheb            |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| THIS SOFTWARE IS NOT FREE! MAKE SURE YOU OWN A VALID LICENSE! |
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

//Modul registrieren
$module = array(1,999999,
'id' => 'user',
'dependence' => array(),
'requirement' => array('main' => '1.2.0'),
'version' => '1.2.3',
'author' => 'Christian Scheb',
'contact' => 'http://www.stylemotion.de'
);


//Aktionen registrieren       S V O R
$action['login']      = array(0,0,1,1);
$action['logout']     = array(0,0,2,1);
$action['autologout'] = array(0,0,3,1);

$action['show']       = array(0,1,4,0);
$action['add']        = array(0,1,5,0);
$action['edit']       = array(0,0,6,0);
$action['del']        = array(0,0,7,0);
$action['enable']     = array(0,0,8,0);

$action['gshow']      = array(0,1,12,0);
$action['gadd']       = array(0,0,13,0);
$action['gedit']      = array(0,0,14,0);
$action['gclean']     = array(0,0,15,0);
$action['gdel']       = array(0,0,16,0);

$action['profile']    = array(0,0,98,0);
$action['myprofile']  = array(0,1,99,1);

$action['guestbook']  = array(0,1,991,0);
$action['blog']       = array(0,1,992,0);
$action['gallery']    = array(0,1,993,0);

$action['sendmail']  = array(0,1,2000,0);
$action['sendpm']  = array(0,1,2001,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen         F         V
$func['USER_INFO']=array('user_info',true);
$func['USERONLINE']=array('user_online',false);
$func['NEWPMS']=array('user_newpms',false);
$func['NEWGBENTRIES']=array('user_newgbs',false);
$func['ONLINELIST']=array('user_onlinelist',true);
$func['LOGINBOX']=array('user_loginbox',false);
$func['BIRTHDAYS']=array('user_birthdays',true);
$func['BIRTHDAYS_TOMORROW']=array('user_birthdays_tomorrow',true);
$func['BIRTHDAYS_NEXTDAYS']=array('user_birthdays_nextdays',true);
$func['BUDDYLIST']=array('user_buddylist',true);
$func['NEWUSER']=array('user_new',true);
$func['RANDOMUSER']=array('user_random',true);
$func['PROFILE']=array('user_profile',true);
$func['BOOKMARK']=array('user_bookmarklink',false);
$func['SHOWBOOKMARKS']=array('user_bookmarks',true);
$func['ONLINERECORD']=array('user_onlinerecord',true);
$func['USERBLOGS']=array('user_blogs_last',true);
$func['USERGALLERY_LAST']=array('user_gallery_last',true);
$func['USERGALLERY_UPDATED']=array('user_gallery_updated',true);
$func['USERGALLERY_LASTPICS']=array('user_gallery_lastpics',true);
$func['USERGALLERY_POTM']=array('user_gallery_potm',true);
$func['USER_STATS']=array('user_stats',true);
$func['USERSTATUS']=array('user_status',true);

//Admin-Template-Funktionen       F         V
$afunc['USER']=array('user_team',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>