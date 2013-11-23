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


//Aktuelle News auf Seite 1 herausfiltern
function news_recent() {
	global $set,$db,$apx,$user;
	static $recent;
	if ( isset($recent) ) return $recent;
	
	if ( !$set['news']['epp'] ) return array();
	
	$data=$db->fetch("SELECT id,IF(sticky>=".time().",1,0) AS sticky FROM ".PRE."_news WHERE ( ".time()." BETWEEN starttime AND endtime ".section_filter()." ) ORDER BY sticky DESC,starttime DESC LIMIT ".$set['news']['epp']);
	if ( !count($data) ) return array();
	
	$recent=array();
	foreach ( $data AS $res ) {
		$recent[]=$res['id'];
	}
	
	return $recent;
}


//Prüfen ob eine News auf Seite 1 ist
function news_is_recent($id) {
	$recent=news_recent();
	if ( in_array($id,$recent) ) return true;
	if ( !count($recent) ) return true;
	return false;
}



//Kategorien-Informationen
function news_catinfo($id=false) {
	global $set,$db,$apx,$user;
	
	//Eine Kategorie
	if ( is_int($id) || is_string($id) ) {
		$id=(int)$id;
		if ( isset($catinfo[$id]) ) return $catinfo[$id];
		$res=$db->first("SELECT id,title,icon,open FROM ".PRE."_news_cat WHERE ( id='".$id."' ) LIMIT 1",1);
		$catinfo[$id]=$res;
		$catinfo[$id]['link']=mklink(
			'news.php?catid='.$res['id'],
			'news,'.$res['id'].',1.html'
		);
		return $catinfo[$id];
	}
	
	//Mehrere Kategorien
	elseif ( is_array($id) ) {
		if ( !count($id) ) return array();
		$data=$db->fetch("SELECT id,title,icon,open FROM ".PRE."_news_cat WHERE id IN (".implode(',',$id).")");
		if ( !count($data) ) return array();
		foreach ( $data AS $res ) {
			$catinfo[$res['id']]=$res;
			$catinfo[$res['id']]['link']=mklink(
				'news.php?catid='.$res['id'],
				'news,'.$res['id'].',1.html'
			);
		}
		return $catinfo;
	}
	
	//Alle Kategorien;
	else {
		if ( $set['news']['subcats'] ) {
			require_once(BASEDIR.'lib/class.recursivetree.php');
			$tree=new RecursiveTree(PRE.'_news_cat', 'id');
			$data = $tree->getTree(array('*'));
		}
		else $data=$db->fetch("SELECT * FROM ".PRE."_news_cat ORDER BY title ASC");
		if ( !count($data) ) return array();
		foreach ( $data AS $res ) {
			$catinfo[$res['id']]=$res;
			$catinfo[$res['id']]['link']=mklink(
				'news.php?catid='.$res['id'],
				'news,'.$res['id'].',1.html'
			);
		}
		return $catinfo;
	}
}



//Kategorie-Baum holen
function news_tree($catid) {
	global $set,$db,$apx,$user;
	static $saved;
	$catid=(int)$catid;
	
	$catid=(int)$catid;
	if ( !$catid ) return array();
	if ( !$set['news']['subcats'] ) return array($catid);
	if ( isset($saved[$catid]) ) return $saved[$catid];
	
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$tree=new RecursiveTree(PRE.'_news_cat', 'id');
	$data = $tree->getTree(array('title', 'open'));
	$cattree = $tree->getChildrenIds($catid);
	$cattree[] = $catid; 
	
	$saved[$catid]=$cattree;
	return $cattree;
}



//Links generieren
function news_links($res) {
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



//News zu Tags suchen
function news_search_tags($tagids, $conn='or') {
	global $set,$db,$apx,$user;
	if ( !is_array($tagids) ) return array();
	$tagids = array_map('intval', $tagids);
	if ( !$tagids ) return array();
	if ( $conn=='or' ) {
		$data = $db->fetch("
			SELECT DISTINCT id
			FROM ".PRE."_news_tags
			WHERE tagid IN (".implode(', ', $tagids).")
		");
		$ids = get_ids($data, 'id');
	}
	else {
		$data = $db->fetch("
			SELECT id, tagid, count(id) AS hits
			FROM ".PRE."_news_tags
			WHERE tagid IN (".implode(', ', $tagids).")
			GROUP BY id
			HAVING hits=".count($tagids)."
		");
		$ids = get_ids($data, 'id');
	}
	return $ids;
}



//Nach Übereinstimmungen in den Tags suchen
function news_match_tags($items) {
	global $set,$db,$apx,$user;
	if ( !is_array($items) ) return array();
	$result = array();
	foreach ( $items AS $item ) {
		$data = $db->fetch("
			SELECT DISTINCT at.id
			FROM ".PRE."_news_tags AS at
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE t.tag LIKE '%".addslashes_like($item)."%'
		");
		$result[$item] = get_ids($data, 'id');
	}
	return $result;
}



//Tags zu einer News auslesen
function news_tags($id) {
	global $set,$db,$apx,$user;
	$tagdata = array();
	$tagids = array();
	$tags = array();
	$data = $db->fetch("
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM ".PRE."_news_tags AS nt
		LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN ".PRE."_news_tags AS nt2 ON nt.tagid=nt2.tagid
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



//Newspic generieren
function news_newspic($newspic) {
	global $set,$db,$apx,$user;
	if ( !$newspic ) return array();
	
	$picture=getpath('uploads').$newspic;
	$poppic=str_replace('-thumb.','.',$newspic);
	
	if ( $set['news']['newspic_popup'] && strpos($newspic,'-thumb.')!==false && file_exists(BASEDIR.getpath('uploads').$poppic) ) {
		$size=getimagesize(BASEDIR.getpath('uploads').$poppic);
		$picture_popup="javascript:popupwin('misc.php?action=picture&amp;pic=".$poppic."','".$size[0]."','".$size[1]."')";
	}
	else {
		$poppic = '';
	}
	
	return array($picture,$picture_popup,iif($poppic, HTTPDIR.getpath('uploads').$poppic));
}



//Kommentar-Seite
function news_showcomments($id) {
	global $set,$db,$apx,$user;
	$id=(int)$id;
	
	$res=$db->first("SELECT id,allowcoms FROM ".PRE."_news WHERE ( id='".$id."' ".section_filter()." ) LIMIT 1");
	if ( !$apx->is_module('comments') || !$set['news']['coms'] || !$res['allowcoms'] ) return;
	
	require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
	$coms=new comments('news',$id);
	$coms->assign_comments();
	if ( !news_is_recent($id) && !$set['news']['archcoms'] ) $apx->tmpl->assign('COMMENT_NOFORM',1);
	
	$apx->tmpl->parse('comments','comments');
	require('lib/_end.php');
}

?>