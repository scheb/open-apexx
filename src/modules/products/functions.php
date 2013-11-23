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


# GAMES CLASS
# ===========

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Release-Datum generieren
function products_format_release($info) {
	global $set,$db,$apx;
	if ( $info['day'] && $info['month'] && $info['year'] ) $releasedate = sprintf('%02d.%02d.%04d',$info['day'],$info['month'],$info['year']);
	elseif ( $info['month'] && $info['year'] ) $releasedate = getcalmonth($info['month']).' '.$info['year'];
	elseif ( $info['quater'] && $info['year'] ) $releasedate = $info['quater'].'. '.$apx->lang->get('CORE_QUATER').' '.$info['year'];
	else $releasedate = $info['year'];
	return $releasedate;
}



//Produktbild generieren
function products_pic($pic) {
	global $set,$db,$apx,$user;
	if ( !$pic ) return array();
	
	$picture=getpath('uploads').$pic;
	$poppic=str_replace('-thumb.','.',$pic);
	
	if ( strpos($pic,'-thumb.')!==false && file_exists(BASEDIR.getpath('uploads').$poppic) ) {
		$size=getimagesize(BASEDIR.getpath('uploads').$poppic);
		$picture_popup="javascript:popupwin('misc.php?action=picture&amp;pic=".$poppic."','".$size[0]."','".$size[1]."')";
	}
	else {
		$poppic = '';
	}
	
	return array($picture,$picture_popup,iif($poppic, HTTPDIR.getpath('uploads').$poppic));
}



//Produkte zu Tags suchen
function products_search_tags($tagids, $conn='or') {
	global $set,$db,$apx,$user;
	if ( !is_array($tagids) ) return array();
	$tagids = array_map('intval', $tagids);
	if ( !$tagids ) return array();
	if ( $conn=='or' ) {
		$data = $db->fetch("
			SELECT DISTINCT id
			FROM ".PRE."_products_tags
			WHERE tagid IN (".implode(', ', $tagids).")
		");
		$ids = get_ids($data, 'id');
	}
	else {
		$data = $db->fetch("
			SELECT id, tagid, count(id) AS hits
			FROM ".PRE."_products_tags
			WHERE tagid IN (".implode(', ', $tagids).")
			GROUP BY id
			HAVING hits=".count($tagids)."
		");
		$ids = get_ids($data, 'id');
	}
	return $ids;
}



//Nach Übereinstimmungen in den Tags suchen
function products_match_tags($items) {
	global $set,$db,$apx,$user;
	if ( !is_array($items) ) return array();
	$result = array();
	foreach ( $items AS $item ) {
		$data = $db->fetch("
			SELECT DISTINCT at.id
			FROM ".PRE."_products_tags AS at
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE t.tag LIKE '%".addslashes_like($item)."%'
		");
		$result[$item] = get_ids($data, 'id');
	}
	return $result;
}



//Tags zu einem Produkt auslesen
function products_tags($id) {
	global $set,$db,$apx,$user;
	$tagdata = array();
	$tagids = array();
	$tags = array();
	$data = $db->fetch("
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM ".PRE."_products_tags AS nt
		LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN ".PRE."_products_tags AS nt2 ON nt.tagid=nt2.tagid
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
function products_showcomments($id) {
	global $set,$db,$apx,$user;
	$id=(int)$id;
	
	$res=$db->first("SELECT id,allowcoms FROM ".PRE."_products WHERE id='".$id."' AND active='1' LIMIT 1");
	if ( !$apx->is_module('comments') || !$set['products']['coms'] || !$res['allowcoms'] ) return;
	
	require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
	$coms=new comments('products',$id);
	$coms->assign_comments();
	
	$apx->tmpl->parse('comments','comments');
	require('lib/_end.php');
}



//Befindet sich ein Produkt in der Sammlung des Benutzers?
function products_in_coll($prodid) {
	global $set,$db,$apx,$user;
	static $coll;
	
	if ( !$user->info['userid'] ) return false;
	
	if ( !isset($coll) ) {
		$data = $db->fetch("SELECT prodid FROM ".PRE."_products_coll WHERE userid='".$user->info['userid']."'");
		$coll = get_ids($data, 'prodid');
	}
	
	return in_array($prodid, $coll);
}

?>