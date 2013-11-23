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


//Kategorie-Baum holen
function calendar_tree($catid) {
	global $set,$db,$apx,$user;
	static $saved;
	$catid=(int)$catid;
	
	$catid=(int)$catid;
	if ( !$catid ) return array();
	if ( !$set['calendar']['subcats'] ) return array($catid);
	if ( isset($saved[$catid]) ) return $saved[$catid];
	
	$cattree=array();
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$tree = new RecursiveTree(PRE.'_calendar_cat', 'id');
	$cattree = $tree->getChildrenIds($catid);
	$cattree[] = $catid;
	
	$saved[$catid]=$cattree;
	return $cattree;
}



//Links generieren
function calendar_links($res) {
	$res=unserialize($res);
	if ( !is_array($res) || !count($res) ) return array();
	
	foreach ( $res AS $link ) {
		++$i;
		$linkdata[$i]['TITLE']=$link['title'];
		$linkdata[$i]['TEXT']=$link['text'];
		$linkdata[$i]['URL']=$link['url'];
		$linkdata[$i]['POPUP']=$link['popup'];
	}
	
	return $linkdata;
}



//Events zu Tags suchen
function calendar_search_tags($tagids, $conn='or') {
	global $set,$db,$apx,$user;
	if ( !is_array($tagids) ) return array();
	$tagids = array_map('intval', $tagids);
	if ( !$tagids ) return array();
	if ( $conn=='or' ) {
		$data = $db->fetch("
			SELECT DISTINCT id
			FROM ".PRE."_calendar_tags
			WHERE tagid IN (".implode(', ', $tagids).")
		");
		$ids = get_ids($data, 'id');
	}
	else {
		$data = $db->fetch("
			SELECT id, tagid, count(id) AS hits
			FROM ".PRE."_calendar_tags
			WHERE tagid IN (".implode(', ', $tagids).")
			GROUP BY id
			HAVING hits=".count($tagids)."
		");
		$ids = get_ids($data, 'id');
	}
	return $ids;
}



//Nach Übereinstimmungen in den Tags suchen
function calendar_match_tags($items) {
	global $set,$db,$apx,$user;
	if ( !is_array($items) ) return array();
	$result = array();
	foreach ( $items AS $item ) {
		$data = $db->fetch("
			SELECT DISTINCT at.id
			FROM ".PRE."_calendar_tags AS at
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE t.tag LIKE '%".addslashes_like($item)."%'
		");
		$result[$item] = get_ids($data, 'id');
	}
	return $result;
}



//Tags zu einem Event auslesen
function calendar_tags($id) {
	global $set,$db,$apx,$user;
	$tagdata = array();
	$tagids = array();
	$tags = array();
	$data = $db->fetch("
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM ".PRE."_calendar_tags AS nt
		LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN ".PRE."_calendar_tags AS nt2 ON nt.tagid=nt2.tagid
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



//Daystamp erzeugen
function calendar_generate_stamp( $day, $month, $year ) {
	return sprintf('%04d%02d%02d',$year,$month,$day);
}



//Aus einem Daystamp einen Timestamp machen
function calendar_stamp2time($stamp) {
	$info = calendar_explode_stamp($stamp);
	return mktime(0,0,0,$info['month'],$info['day'],$info['year'])+TIMEDIFF;
}



//Einen Daystamp in seine Bestandteile zerlegen
function calendar_explode_stamp($stamp) {
	$stamp=sprintf('%08d',$stamp);
	$info=array(
		'day' => (int)substr($stamp,6,2),
		'month' => (int)substr($stamp,4,2),
		'year' => (int)substr($stamp,0,4)
	);
	return $info;
}



//Aufmacher-Bild generieren
function calendar_pic($pic) {
	global $set,$db,$apx,$user;
	if ( !$pic ) return array();
	
	$picture=getpath('uploads').$pic;
	$poppic=str_replace('-thumb.','.',$pic);
	
	if ( $set['calendar']['pic_popup'] && strpos($pic,'-thumb.')!==false && file_exists(BASEDIR.getpath('uploads').$poppic) ) {
		$size=getimagesize(BASEDIR.getpath('uploads').$poppic);
		$picture_popup="javascript:popupwin('misc.php?action=picture&amp;pic=".$poppic."','".$size[0]."','".$size[1]."')";
	}
	else {
		$poppic = '';
	}
	
	return array($picture,$picture_popup,iif($poppic, HTTPDIR.getpath('uploads').$poppic));
}



//Kommentar-Seite
function calendar_showcomments($id) {
	global $set,$db,$apx,$user;
	$id=(int)$id;
	
	$res=$db->first("SELECT id,allowcoms FROM ".PRE."_calendar_events WHERE ( id='".$id."' AND active!=0 AND private=0 ".section_filter()." ) LIMIT 1");
	if ( !$apx->is_module('comments') || !$set['calendar']['coms'] || !$res['allowcoms'] ) return;
	
	require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
	$coms=new comments('calendar',$id);
	$coms->assign_comments();
	
	$apx->tmpl->parse('comments','comments');
	require('lib/_end.php');
}


?>