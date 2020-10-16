<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Seiten + Weiter/Zurück generieren
function gallery_pages($id) {
	global $set,$db,$apx,$user;
	$id=(int)$id;
	
	//Order by
	if ( $set['gallery']['orderpics']==2 ) $sortby='id ASC';
	else $sortby='id DESC';
	
	$query="SELECT id,caption,thumbnail FROM ".PRE."_gallery_pics WHERE ( galid='".$id."' ";
	if ( !$user->is_team_member() ) $query.=" AND active='1' ";
	$query.=" ) ORDER BY ".$sortby;
	$data=$db->fetch($query);
	$pages=count($data);
	
	foreach ( $data AS $res ) {
		++$i;
		
		//Seitenzahlen
		$pagedata[$i]['NUMBER']=$i;
		$pagedata[$i]['LINK']=mklink(
			'gallery.php?pic='.$res['id'],
			'gallery,pic'.$res['id'].urlformat($res['caption']).'.html'
		);
		
		//Nächste Seite
		if ( $current['next']===false ) {
			$current['next']=array(
				'link' => mklink(
					'gallery.php?pic='.$res['id'],
					'gallery,pic'.$res['id'].urlformat($res['caption']).'.html'
				),
				'preview' => HTTPDIR.getpath('uploads').$res['thumbnail']
			);
		}
		
		//Vorherige Seite
		if ( $_REQUEST['pic']==$res['id'] ) {
			$selected=$i;
			$current['next']=false;
			
			if ( $last ) {
				$current['prev']=array(
					'link' => mklink(
						'gallery.php?pic='.$last['id'],
						'gallery,pic'.$last['id'].urlformat($last['caption']).'.html'
					),
					'preview' => HTTPDIR.getpath('uploads').$last['thumbnail']
				);
			}
		}
		
		//Erste Seite
		if ( $i==1 ) {
			$link_first=mklink(
				'gallery.php?pic='.$res['id'],
				'gallery,pic'.$res['id'].urlformat($res['caption']).'.html'
			);
		}
		
		//Letzte Seite
		if ( $i==$pages ) {
			$link_last=mklink(
				'gallery.php?pic='.$res['id'],
				'gallery,pic'.$res['id'].urlformat($res['caption']).'.html'
			);
		}
		
		$last=$res;
	}
	
	$apx->tmpl->assign('PICTURE',$pagedata);
	$apx->tmpl->assign('PICTURE_COUNT',$pages);
	$apx->tmpl->assign('PICTURE_SELECTED',$selected);
	
	//Vorherige Seite
	if ( $current['prev'] ) {
		$apx->tmpl->assign('PICTURE_PREVIOUS',$current['prev']['link']);
		$apx->tmpl->assign('PICTURE_PREVIOUS_PREVIEW',$current['prev']['preview']);
	} 
	
	//Nächste Seite
	if ( $current['next'] ) {
		$apx->tmpl->assign('PICTURE_NEXT',$current['next']['link']);
		$apx->tmpl->assign('PICTURE_NEXT_PREVIEW',$current['next']['preview']);
	}
	
	$apx->tmpl->assign('PICTURE_FIRST',$link_first);
	$apx->tmpl->assign('PICTURE_LAST',$link_last);
}



//Aktivierte Galerien holen
function gallery_active($secid=false,$prodid=false) {
	global $db;
	$secid=(int)$secid;
	$prodid=(int)$prodid;
	
	$saved=array();
	if ( $secid<0 ) $data=$db->fetch("SELECT id FROM ".PRE."_gallery WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) AND password='' ".iif($prodid," AND prodid='".$prodid."' ")." )");
	elseif ( $secid ) $data=$db->fetch("SELECT id FROM ".PRE."_gallery WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) AND password='' ".iif($prodid," AND prodid='".$prodid."' ")." AND ( secid LIKE '%|".$secid."|%' OR secid='all' ) )");
	else $data=$db->fetch("SELECT id FROM ".PRE."_gallery WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif($prodid," AND prodid='".$prodid."' ")." ".section_filter()." )");
	if ( !count($data) ) return array(-1);
	
	foreach ( $data AS $res ) {
		$saved[]=$res['id'];
	}
	
	return $saved;
}



//Pfad holen
function gallery_path($id) {
	global $set,$db,$apx,$user;
	$id=(int)$id;
	if ( !$id ) return '';
	
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$tree = new RecursiveTree(PRE.'_gallery', 'id');
	$data = $tree->getPathTo(array('title'), $id);
	if ( !count($data) ) return array();
	
	foreach ( $data AS $res ) {
		++$i;
		
		$pathdata[$i]['TITLE']=$res['title'];
		$pathdata[$i]['LINK']=mklink(
			'gallery.php?id='.$res['id'],
			'gallery,list'.$res['id'].',1'.urlformat($res['title']).'.html'
		);
	}
	
	return $pathdata;
}



//Galerien zu Tags suchen
function gallery_search_tags($tagids, $conn='or') {
	global $set,$db,$apx,$user;
	if ( !is_array($tagids) ) return array();
	$tagids = array_map('intval', $tagids);
	if ( !$tagids ) return array();
	if ( $conn=='or' ) {
		$data = $db->fetch("
			SELECT DISTINCT id
			FROM ".PRE."_gallery_tags
			WHERE tagid IN (".implode(', ', $tagids).")
		");
		$ids = get_ids($data, 'id');
	}
	else {
		$data = $db->fetch("
			SELECT id, tagid, count(id) AS hits
			FROM ".PRE."_gallery_tags
			WHERE tagid IN (".implode(', ', $tagids).")
			GROUP BY id
			HAVING hits=".count($tagids)."
		");
		$ids = get_ids($data, 'id');
	}
	return $ids;
}




//Nach Übereinstimmungen in den Tags suchen
function gallery_match_tags($items) {
	global $set,$db,$apx,$user;
	if ( !is_array($items) ) return array();
	$result = array();
	foreach ( $items AS $item ) {
		$data = $db->fetch("
			SELECT DISTINCT at.id
			FROM ".PRE."_gallery_tags AS at
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE t.tag LIKE '%".addslashes_like($item)."%'
		");
		$result[$item] = get_ids($data, 'id');
	}
	return $result;
}



//Tags zu einer Galerie auslesen
function gallery_tags($id) {
	global $set,$db,$apx,$user;
	$tagdata = array();
	$tagids = array();
	$tags = array();
	$data = $db->fetch("
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM ".PRE."_gallery_tags AS nt
		LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN ".PRE."_gallery_tags AS nt2 ON nt.tagid=nt2.tagid
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



//Kommentarseite (Bilder)
function gallery_showcomments($id) {
	global $set,$db,$apx,$user;
	$id=(int)$id;
	
	$res=$db->first("SELECT id,allowcoms FROM ".PRE."_gallery_pics WHERE ( id='".$id."' AND active='1' ) LIMIT 1");
	if ( !$apx->is_module('comments') || !$set['gallery']['coms'] || !$res['allowcoms'] ) return;
	
	require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
	$coms=new comments('gallery',$id);
	$coms->assign_comments();
	
	$apx->tmpl->parse('comments','comments');
	require('lib/_end.php');
}



//Kommentarseite (Galerie)
function galleryself_showcomments($id) {
	global $set,$db,$apx,$user;
	$id=(int)$id;
	
	$res=$db->first("SELECT id,allowcoms FROM ".PRE."_gallery WHERE ( id='".$id."' AND '".time()."' BETWEEN starttime AND endtime ) LIMIT 1");
	if ( !$apx->is_module('comments') || !$set['gallery']['coms'] || !$res['allowcoms'] ) return;
	
	require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
	$coms=new comments('galleryself',$id);
	$coms->assign_comments();
	
	$apx->tmpl->parse('comments','comments');
	require('lib/_end.php');
}

?>