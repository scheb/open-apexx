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


//////////////////////////////////////////////////////////////////////////////////////////// FUNKTIONEN DEFINIEREN


//Statistik: Alte Einträge löschen => bessere Performance
function cron_stats_clean($lastexec) {
	global $set,$db,$apx;
	$db->query("DELETE FROM ".PRE."_stats_referer WHERE daystamp<'".date('Ymd',(time()-30*24*3600))."'");
	$db->query("DELETE FROM ".PRE."_stats_userenv WHERE daystamp<'".date('Ymd',(time()-30*24*3600))."'");
}

?>