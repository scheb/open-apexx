<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Tags Autocomplete
function suggesttag() {
	global $apx, $db, $set;
	$max = 5;
	
	$taglist = array();
	$data = $db->fetch("
		SELECT DISTINCT tagid, tag
		FROM ".PRE."_tags
		WHERE tag LIKE '".addslashes_like(utf8_decode($_REQUEST['query']))."%'
		ORDER BY tag ASC
		LIMIT ".$max."
	");
	$rows = $query->num_rows;
	$ids = array(-1);
	foreach ( $data AS $res ) {
		echo utf8_encode($res['tag'])."\n";
		$ids[] = $res['tagid'];
	}
	
	
	//Ergebnisliste erweitern
	if ( $rows<$max ) {
		$data = $db->fetch("
			SELECT DISTINCT tagid, tag
			FROM ".PRE."_tags
			WHERE tag LIKE '%".addslashes_like($_REQUEST['query'])."%' AND tagid NOT IN (".implode(',', $ids).")
			ORDER BY tag ASC
			LIMIT ".($max-$rows)."
		");
		foreach ( $data AS $res ) {
			echo utf8_encode($res['tag'])."\n";
		}
	}
}



//Anordnung der Navi speichern
function savenaviorder() {
	global $apx, $db, $set;
	if ( !$apx->user->info['userid'] ) return; //Benutzer ist nicht angemeldet
	
	$db->query("
		DELETE FROM ".PRE."_user_navord
		WHERE userid='".$apx->user->info['userid']."'
	");
	$i = 0;
	foreach ( $_POST['order'] AS $moduleid ) {
		$db->query("
			INSERT INTO ".PRE."_user_navord
			(userid, module, ord) VALUES
			('".$apx->user->info['userid']."', '".addslashes($moduleid)."', '".$i."')
		");
		$i++;
	}
}


?>
