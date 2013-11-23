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



function search_gallery($items,$conn) {
	global $set,$db,$apx,$user;
	require_once(BASEDIR.getmodulepath('gallery').'functions.php');
	
	//Suchstring generieren
	$tagmatches = gallery_match_tags($items);
	foreach ( $items AS $item ) {
		$tagmatch = array_shift($tagmatches);
		$search1[]="caption LIKE '".addslashes_like($item)."'";
		$search2[]=" ( ".iif($tagmatch, " id IN (".implode(',', $tagmatch).") OR ")." title LIKE '%".addslashes_like($item)."%' OR description LIKE '%".addslashes_like($item)."%' ) ";
	}
	$searchstring1=implode($conn,$search1);
	$searchstring2=implode($conn,$search2);
	
	//Bilder durchsuchen
	$data=$db->fetch("SELECT galid FROM ".PRE."_gallery_pics WHERE ( active='1' AND ( ".$searchstring1." ) ) GROUP BY galid");
	$galids=get_ids($data,'galid');
	if ( count($galids) ) $picres=" id IN (".@implode(',',$galids).") OR ";
	
	//Ergebnisse
	$data=$db->fetch("SELECT id,title FROM ".PRE."_gallery WHERE ( searchable='1' AND '".time()."' BETWEEN starttime AND endtime ".section_filter()." AND ( ".$picres." ( ".$searchstring2." ) ) ) ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$result[$i]['TITLE']=strip_tags($res['title']);
			$result[$i]['LINK']=mklink(
				'gallery.php?id='.$res['id'],
				'gallery,list'.$res['id'].',1'.urlformat($res['title']).'.html'
			);
		}
	}
	
	return $result;
}

?>