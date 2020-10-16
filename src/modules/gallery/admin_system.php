<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Galerie-Liste anzeigen
function gallery_list($selected=0) {
	echo '<select name="galid">'.get_gallery_list($selected).'</select>';
}



//Galerie-Liste erzeugen
function get_gallery_list($selected=0) {
	global $set,$db,$apx;
	$list='<option value=""></option>';
	
	if ( $set['gallery']['subgals'] ) {
		require_once(BASEDIR.'lib/class.recursivetree.php');
		$tree = new RecursiveTree(PRE.'_gallery', 'id');
		$data = $tree->getTree(array('title'), null, "'".time()."' BETWEEN starttime AND endtime ".section_filter(true, 'secid'));
		if ( !count($data) ) return '';
		
		foreach ( $data AS $res ) {
			$list.='<option value="'.$res['id'].'"'.iif($selected==$res['id'],' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;',($res['level']-1)).replace(strip_tags($res['title'])).'</option>';
		}
	}
	else {
		$data=$db->fetch("SELECT id,title FROM ".PRE."_gallery WHERE '".time()."' BETWEEN starttime AND endtime ".section_filter(true, 'secid')." ORDER BY title ASC");
		if ( !count($data) ) return '';
		
		foreach ( $data AS $res ) {
			$list.='<option value="'.$res['id'].'"'.iif($selected==$res['id'],' selected="selected"').'>'.replace(strip_tags($res['title'])).'</option>';
		}
	}
	return $list;
}


?>