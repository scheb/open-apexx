<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

////////////////////////////////////////////////////////////////////////////////////////////

//Zeit synchronisieren
$statsnow=time();

//Check IP
$db->query("DELETE FROM ".PRE."_stats_iplog WHERE time<'".($statsnow-$set['stats']['blockip']*3600)."'");
list($check)=$db->first("SELECT time FROM ".PRE."_stats_iplog WHERE ip='".ip2integer(get_remoteaddr())."' LIMIT 1");

//Stamps
$daystamp=date('Ymd',$statsnow-TIMEDIFF);
$hourstamp=date('G',$statsnow-TIMEDIFF);
$weekday = (date('w')+6)%7;

//URLs
$_SERVER['HTTP_REFERER']=$_SERVER['HTTP_REFERER'];
$_SERVER['REQUEST_URI']=$_SERVER['REQUEST_URI'];



///////////////////////////// Counter starten /////////////////////////////

if ( !defined('NOSTATS') && !$_COOKIE[$set['main']['cookie_pre'].'_stats_count'] && !$check ) {
	require_once(BASEDIR.getmodulepath('stats').'functions.php');
	$weekstamp=stats_weekstamp($statsnow);
	
	//User-Info
	$browser=stats_browser($_SERVER['HTTP_USER_AGENT']);
	$os=stats_os($_SERVER['HTTP_USER_AGENT']);
	$hostname = @gethostbyaddr(get_remoteaddr());
	$country=stats_country(substr(strrchr($hostname,'.'),1));
	
	//Besucher-Umgebung
	$data=$db->fetch("SELECT type FROM ".PRE."_stats_userenv WHERE ( daystamp='".$daystamp."' AND ( ( type='browser' AND value='".addslashes($browser)."' ) OR ( type='os' AND value='".addslashes($os)."' ) OR ( type='country' AND value='".addslashes($country)."' ) ) )");
	$exists=array();
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$exists[$res['type']]=true;
		}
	}
	
	if ( $exists['browser'] ) $db->query("UPDATE ".PRE."_stats_userenv SET hits=hits+1 WHERE ( daystamp='".$daystamp."' AND type='browser' AND value='".addslashes($browser)."' ) LIMIT 1");
	else $db->query("INSERT INTO ".PRE."_stats_userenv VALUES ('".$daystamp."','browser','".addslashes($browser)."',1)");
	if ( $exists['os'] ) $db->query("UPDATE ".PRE."_stats_userenv SET hits=hits+1 WHERE ( daystamp='".$daystamp."' AND type='os' AND value='".addslashes($os)."' ) LIMIT 1");
	else $db->query("INSERT INTO ".PRE."_stats_userenv VALUES ('".$daystamp."','os','".addslashes($os)."',1)");
	if ( $exists['country'] ) $db->query("UPDATE ".PRE."_stats_userenv SET hits=hits+1 WHERE ( daystamp='".$daystamp."' AND type='country' AND value='".addslashes($country)."' ) LIMIT 1");
	else $db->query("INSERT INTO ".PRE."_stats_userenv VALUES ('".$daystamp."','country','".addslashes($country)."',1)");
	
	//Besucher zählen
	if ( $browser!='SEARCHENGINE' || $set['stats']['countsearchengine'] ) {
		list($stats_exists)=$db->first("SELECT daystamp FROM ".PRE."_stats WHERE daystamp='".$daystamp."' LIMIT 1");
		if ( $stats_exists ) $db->query("UPDATE ".PRE."_stats SET uniques=uniques+1,uniques_".$hourstamp."h=uniques_".$hourstamp."h+1,hits=hits+1 WHERE daystamp='".$daystamp."' LIMIT 1");
		else {
			$db->quieterror=true;
			$db->query("INSERT INTO ".PRE."_stats (daystamp,weekstamp,weekday,time,uniques,uniques_".$hourstamp."h,hits) VALUES ('".$daystamp."','".$weekstamp."','".$weekday."','".time()."',1,1,1)");
			$db->quieterror=false;
		}
	}
	
	//IP-Sperre und Cookie setzen
	$db->query("INSERT INTO ".PRE."_stats_iplog VALUES ('".ip2integer(get_remoteaddr())."','".time()."')");
	if ( $set['stats']['cookie'] ) setcookie($set['main']['cookie_pre'].'_stats_count',1,(time()+$set['stats']['blockip']*3600),'/');
}


///////////////////////////// Hits /////////////////////////////
elseif ( !( strpos($_SERVER['PHP_SELF'],'misc.php')!==false && $_REQUEST['action']=='counter' ) && $_SERVER['REQUEST_URI'] ) {
	require_once(BASEDIR.getmodulepath('stats').'functions.php');
	$weekstamp=stats_weekstamp($statsnow);
	
	//User-Info
	$browser=stats_browser($_SERVER['HTTP_USER_AGENT']);
	
	if ( $browser!='SEARCHENGINE' || $set['stats']['countsearchengine'] ) {
		if ( !isset($stats_exists) ) list($stats_exists)=$db->first("SELECT daystamp FROM ".PRE."_stats WHERE daystamp='".$daystamp."' LIMIT 1");
		if ( $stats_exists ) $db->query("UPDATE ".PRE."_stats SET hits=hits+1 WHERE daystamp='".$daystamp."' LIMIT 1");
		else {
			$db->quieterror=true;
			$db->query("INSERT INTO ".PRE."_stats (daystamp,weekstamp,weekday,time,hits) VALUES ('".$daystamp."','".$weekstamp."','".$weekday."','".time()."',1)");
			$db->quieterror=false;
		}
	}
}



///////////////////////////// Referer /////////////////////////////
if (
	$_REQUEST['action']!='counter' //Zählpixel filtern
	&& $_SERVER['HTTP_REFERER']
	&& strtolower(substr($_SERVER['HTTP_REFERER'],0,7))=='http://' 
	&& ( $set['stats']['ownreferer'] || strpos(strtolower($_SERVER['HTTP_REFERER']),'http://'.strtolower($_SERVER['HTTP_HOST']))===false )
)	{
	require_once(BASEDIR.getmodulepath('stats').'functions.php');
	
	//Host + Searchstring
	$host=stats_host($_SERVER['HTTP_REFERER']);
	$searchstring=stats_searchstring($_SERVER['HTTP_REFERER']);
	
	list($referer_exists)=$db->first("SELECT daystamp FROM ".PRE."_stats_referer WHERE ( daystamp='".$daystamp."' AND hash='".addslashes(md5($_SERVER['HTTP_REFERER']))."' AND url='".addslashes($_SERVER['HTTP_REFERER'])."' ) LIMIT 1");
	if ( $referer_exists ) $db->query("UPDATE ".PRE."_stats_referer SET hits=hits+1 WHERE ( daystamp='".$daystamp."' AND hash='".addslashes(md5($_SERVER['HTTP_REFERER']))."' AND url='".addslashes($_SERVER['HTTP_REFERER'])."' ) LIMIT 1");
	else $db->query("INSERT INTO ".PRE."_stats_referer VALUES ('".$daystamp."','".$statsnow."','".addslashes($host)."','".addslashes($_SERVER['HTTP_REFERER'])."','".addslashes(md5($_SERVER['HTTP_REFERER']))."','".$searchstring."',1)");
}

?>