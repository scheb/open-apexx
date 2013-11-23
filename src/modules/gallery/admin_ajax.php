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



//Tags Autocomplete
function togglestate() {
	global $apx, $db, $set;
	
	$id = (int)$_REQUEST['id'];
	$status = (int)$_REQUEST['status'];
	if ( !$id ) terminate();
	
	$open = $apx->session->get('gallery_open');
	$open = array_map('intval', dash_unserialize($open));
	if ( !is_array($open) ) $open = array();
	
	if ( $status ) {
		if ( !in_array($id, $open) ) {
			$open[] = $id;
		}
	}
	else {
		$index = array_search($id, $open);
		if ( $index!==false ) {
			unset($open[$index]);
		}
	}
	
	$apx->session->set('gallery_open', dash_serialize($open));
}



//Tags Autocomplete
function nodemoved() {
	global $apx, $set, $db;
	if ( !checkToken() ) return;
	if ( !$apx->user->has_right('gallery.edit') || !$set['gallery']['subgals'] ) return;
	
	$id = (int)$_REQUEST['id'];
	$newparent = (int)$_REQUEST['parentid'];
	$beforeid = (int)$_REQUEST['before'];
	$afterid = (int)$_REQUEST['after'];
	if ( !$id ) return;
	
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$tree = new RecursiveTree(PRE.'_gallery', 'id');
	$update = array();
	
	$nodeInfo = $tree->getNode($_REQUEST['id']);
	$currentParentId = array_pop($nodeInfo['parents']);
	
	
	//Dieser Knoten wird ein Unter-Knoten
	//Übernehme secid vom neuen Parent, password löschen
	if ( $newparent ) {
		
		//Parent hat sich geändert => Daten übernehmen
		if ( $currentParentId!=$newparent ) {
			$rootNode = $tree->getNode($newparent, array('secid', 'password', 'restricted'));
			
			$update['secid'] = $rootNode['secid'];
			$update['password'] = '';
			$update['restricted'] = '';
			
			//Unter-Galerien des Knotens anpassen
			$childrenIds = $nodeInfo['children'];
			if ( $childrenIds ) {
				$db->query("
					UPDATE ".PRE."_gallery
					SET secid='".addslashes($update['secid'])."', password = '', restricted=0
					WHERE id IN (".implode(',', $childrenIds).")
				");
			}
		}
	}
	
	//Dieser Knoten ist ein Root-Knoten
	else {
		//Nix zu tun, der Knoten bleibt wie er ist und wird einfach ein Root-Knoten
	}
	
	//In einen Knoten verschieben
	if ( !$beforeid && !$afterid ) {
		$tree->moveNode($id, $newparent, $update);
	}
	
	//Vor einen Knoten verschieben
	elseif ( $beforeid ) {
		$tree->moveNodeBefore($id, $newparent, $beforeid, $update);
	}
	
	//Nach einen Knoten
	elseif ( $afterid ) {
		$tree->moveNodeAfter($id, $newparent, $afterid, $update);
	}
	
	//Gallery Updatetime
	setGalleryUpdatetime($currentParentId);
	setGalleryUpdatetime($newparent);
}



//Eintrag verschoben (Normale Liste)
function listmoved() {
	global $apx, $set;
	if ( !checkToken() ) return;
	if ( !$apx->user->has_right('gallery.edit') || $set['gallery']['subgals'] || $set['gallery']['ordergal']!=3 ) return;
	
	$id = (int)$_REQUEST['id'];
	$beforeid = (int)$_REQUEST['before'];
	$afterid = (int)$_REQUEST['after'];
	if ( !$id || ( !$beforeid && !$afterid ) ) return;
	
	require_once(BASEDIR.'lib/class.orderedlist.php');
	$list = new OrderedList(PRE.'_gallery', 'id');
	
	//Vor einen Knoten verschieben
	if ( $beforeid ) {
		$list->moveBefore($id, $beforeid);
	}
	
	//Nach einen Knoten
	elseif ( $afterid ) {
		$list->moveAfter($id, $afterid);
	}
}



//Updatetime einer Galerie setzen
function setGalleryUpdatetime($galId) {
	global $db;
	
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$tree = new RecursiveTree(PRE.'_gallery', 'id');
	
	$gallery = $tree->getNode($galId);
	if ( !$gallery ) {
		return;
	}
	
	$updateIds = array_merge($gallery['parents'], array($gallery['id']));
	foreach ( $updateIds AS $id ) {
		$gallery = $tree->getNode($id);
		$searchIds = array_merge($gallery['children'], array($gallery['id']));
		list($updatetime) = $db->first("
			SELECT max(addtime)
			FROM ".PRE."_gallery_pics
			WHERE galid IN (".implode(',', $searchIds).") AND active=1
		");
		$db->query("
			UPDATE ".PRE."_gallery
			SET lastupdate='".$updatetime."'
			WHERE id='".$id."'
			LIMIT 1
		");
	}
}


?>
