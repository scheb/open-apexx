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


//Ortsnamen vergleichen
function user_city_match($city1,$city2) {
	$replace = array(
		'-' => ' ',
		'/' => ' ',
		'(' => ' ',
		')' => ' '
	);
	$city1 = preg_replace('#[ ]{2,}#',' ',strtr(strtolower($city1),$replace));
	$city2 = preg_replace('#[ ]{2,}#',' ',strtr(strtolower($city2),$replace));
	if ( $city1==$city2 ) {
		return true;
	}
	return false;
}



//Stadtnamen für MySQL-LIKE anpassen
function user_city_mysql_match($text) {
	$replace = array(
		'%' => '\\%',
		'_' => '\\_',
		'-' => '_',
		'/' => '_',
		'(' => '_',
		')' => '_'
	);
	return strtr($text,$replace);
}



//Location-ID bestimmen
function user_get_location($plz,$city,$country) {
	global $apx,$db,$set;
	
	//PLZ und Land bekannt
	if ( $plz && in_array($country,array('DE','AT','CH')) ) {
		$plzstamp = sprintf('%05d',intval($plz));
		$stamp = $country.'-'.$plzstamp;
		$data = $db->fetch("
			SELECT l.id,l.name
			FROM ".PRE."_user_locations_plz AS p
			LEFT JOIN ".PRE."_user_locations AS l ON p.locid=l.id
			WHERE p.stamp='".addslashes($stamp)."'
		");
	}
	
	//Nur PLZ bekannt
	elseif ( $plz ) {
		$plzstamp = sprintf('%05d',intval($plz));
		$data = $db->fetch("
			SELECT l.id,l.name
			FROM ".PRE."_user_locations_plz AS p
			LEFT JOIN ".PRE."_user_locations AS l ON p.locid=l.id
			WHERE p.plz='".addslashes($plzstamp)."'
		");
	}
	
	//Suche nach Ortsnamen
	elseif ( $city ) {
		$name = user_city_mysql_match($city);
		$data = $db->fetch("
			SELECT id,name
			FROM ".PRE."_user_locations
			WHERE name LIKE '".addslashes($name)."'
		");
		$citysearch = true;
	}
	
	//Passenden Ort suchen
	if ( is_countable($data) && count($data)==1 ) {
		foreach ( $data AS $res ) {
			return $res['id'];
		}
	}
	elseif ( is_countable($data) && count($data)>1 && !isset($citysearch) && $city ) {
		foreach ( $data AS $res ) {
			if ( user_city_match($res['name'],$city) ) {
				return $res['id'];
			}
		}
	}
	
	return 0;
}

?>
