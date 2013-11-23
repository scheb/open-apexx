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

///////////////////////////////////////////////////////////////////////////////////////// SHUTDOWN

//Shutdown durchführen
foreach ( $apx->modules AS $module => $info ) {
	if ( !file_exists(BASEDIR.getmodulepath($module).'shutdown.php') ) continue;
	include_once(BASEDIR.getmodulepath($module).'shutdown.php');
}


///////////////////////////////////////////////////////////////////////////////////////// SCRIPT BEENDEN

//Ausgabe vorbereiten
$apx->tmpl->out();

//MySQL Verbindung schließen
$db->close();

//Renderzeit
if ( $set['rendertime'] ) {
	list($usec,$sec)=explode(' ',microtime()); 
	$b2=((float)$usec+(float)$sec);
	list($usec,$sec)=explode(' ',$_BENCH); 
	$b1=((float)$usec+(float)$sec);
	echo '<div style="font-size:11px;">Processing: '.($b2-$b1).' sec.</div>';
}


//Script beenden, nachfolgenden Code nicht ausführen!
//(falls _end.php erzwungen wird)
exit;

?>