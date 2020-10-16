<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Aktueller Poll
function poll_recent() {
	global $set,$db,$apx;
	list($id)=$db->first("SELECT id FROM ".PRE."_poll WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter()." ) ORDER BY starttime DESC LIMIT 1");
	return $id;
}



//Ergebnis formatieren + sortieren
function poll_format_result($info) {
	global $set;
	
	$result=array();
	for ( $i=1; $i<=20; $i++ ) {
		if ( $info['a'.$i] ) {
			$result[]=array(
				$info['a'.$i],
				$info['a'.$i.'_c'],
				$info['color'.$i]
			);
		}
	}
	
	if ( $set['poll']['maxfirst'] ) uasort($result,'poll_sortres');
	
	return $result;
}



//Funktion zum sortieren der Ergebnisse
function poll_sortres($a,$b) {
	if ($a[1]==$b[1]) return 0;
	return ($a[1]<$b[1]) ? 1 : -1;
}



//Umfragen zu Tags suchen
function poll_search_tags($tagids, $conn='or') {
	global $set,$db,$apx,$user;
	if ( !is_array($tagids) ) return array();
	$tagids = array_map('intval', $tagids);
	if ( !$tagids ) return array();
	if ( $conn=='or' ) {
		$data = $db->fetch("
			SELECT DISTINCT id
			FROM ".PRE."_poll_tags
			WHERE tagid IN (".implode(', ', $tagids).")
		");
		$ids = get_ids($data, 'id');
	}
	else {
		$data = $db->fetch("
			SELECT id, tagid, count(id) AS hits
			FROM ".PRE."_poll_tags
			WHERE tagid IN (".implode(', ', $tagids).")
			GROUP BY id
			HAVING hits=".count($tagids)."
		");
		$ids = get_ids($data, 'id');
	}
	return $ids;
}



//Nach Übereinstimmungen in den Tags suchen
function poll_match_tags($items) {
	global $set,$db,$apx,$user;
	if ( !is_array($items) ) return array();
	$result = array();
	foreach ( $items AS $item ) {
		$data = $db->fetch("
			SELECT DISTINCT at.id
			FROM ".PRE."_poll_tags AS at
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE t.tag LIKE '%".addslashes_like($item)."%'
		");
		$result[$item] = get_ids($data, 'id');
	}
	return $result;
}



//Tags zu einer Umfrage auslesen
function poll_tags($id) {
	global $set,$db,$apx,$user;
	$tagdata = array();
	$tagids = array();
	$tags = array();
	$data = $db->fetch("
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM ".PRE."_poll_tags AS nt
		LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN ".PRE."_poll_tags AS nt2 ON nt.tagid=nt2.tagid
		WHERE nt.id=".intval($id)."
		GROUP BY nt.tagid
		ORDER BY t.tag ASC
	");
	if ( count($data) ) {
		$maxweight = 1;
		foreach ( $data AS $res ) {
			if ( $res['weight']>$maxweight ) {
				$maxweight = $res['weight'];
			}
		}
		foreach ( $data AS $res ) {
			$tags[] = $res['tag'];
			$tagids[] = $res['tagid'];
			$tagdata[] = array(
				'ID' => $res['tagid'],
				'NAME' => replace($res['tag']),
				'WEIGHT' => $res['weight']/$maxweight
			);
		}
	}
	
	return array($tagdata, $tagids, implode(', ', $tags));
}



//Kommentar-Seite
function poll_showcomments($id) {
	global $set,$db,$apx,$user;
	$id=(int)$id;
	if ( !$id ) die('missing ID!');
	
	$recent=poll_recent();
	$res=$db->first("SELECT id,allowcoms FROM ".PRE."_poll WHERE ( id='".$id."' AND ( '".time()."' BETWEEN starttime AND endtime ) ".iif($apx->section_id()," ".section_filter()." ")." ) LIMIT 1");
	if ( !$apx->is_module('comments') || !$set['poll']['coms'] || !$res['allowcoms'] ) return;
	
	require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
	$coms=new comments('poll',$id);
	$coms->assign_comments();
	if ( $recent!=$res['id'] && !$set['poll']['archcoms'] ) $apx->tmpl->assign('COMMENT_NOFORM',1);
	
	$apx->tmpl->parse('comments','comments');
	require('lib/_end.php');
}

?>