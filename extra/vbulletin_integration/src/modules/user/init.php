<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2007, Christian Scheb            |
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
'version' => '1.1.1',
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
$action['gdel']       = array(0,0,15,0);

$action['profile']    = array(0,0,98,0);
$action['myprofile']  = array(0,1,99,1);

$action['guestbook']  = array(0,1,991,0);
$action['blog']       = array(0,1,992,0);
$action['gallery']    = array(0,1,993,0);

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/


//Template-Funktionen         F         V
$func['USERONLINE']=array('user_online',false);
$func['LOGINBOX']=array('user_loginbox',false);
$func['PROFILE']=array('user_profile',true);
$func['THREADS_NEW']=array('vbthreads_newthreads',true);
$func['THREADS_UPDATED']=array('vbthreads_updatedthreads',true);

//Admin-Template-Funktionen       F         V
$afunc['USER']=array('user_team',true);

/*
F = Funktions-Name
V = Variablen akzeptieren
*/


?>