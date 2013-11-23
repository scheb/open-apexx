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



//Session speichern
$apx->session->save();

//Ausgabe findet nun statt
$apx->tmpl->out();

//MySQL Verbindung schließen
$db->close();

//Benchmark-Zeit
if ( $set['rendertime'] ) {
	list($usec,$sec)=explode(' ',microtime()); 
	$b2=((float)$usec+(float)$sec);
	list($usec,$sec)=explode(' ',$_BENCH); 
	$b1=((float)$usec+(float)$sec);
	echo '<div style="font-size:11px;">Processing: '.($b2-$b1).' sec.</div>';
}

?>