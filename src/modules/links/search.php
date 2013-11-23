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



function search_links($items,$conn) {
	global $set,$db,$apx,$user;
	require_once(BASEDIR.getmodulepath('links').'functions.php');
	
	//Suchstring generieren
	$tagmatches = links_match_tags($items);
	foreach ( $items AS $item ) {
		$tagmatch = array_shift($tagmatches);
		$search[]=" ( ".iif($tagmatch, " id IN (".implode(',', $tagmatch).") OR ")." title LIKE '%".addslashes_like($item)."%' OR text LIKE '%".addslashes_like($item)."%' OR url LIKE '%".addslashes_like($item)."%' ) ";
	}
	$searchstring=implode($conn,$search);
	
	//Links durchsuchen
	$data=$db->fetch("SELECT id,title FROM ".PRE."_links WHERE ( searchable='1' AND '".time()."' BETWEEN starttime AND endtime ".section_filter()." AND ( ".$searchstring." ) ) ORDER BY addtime DESC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			$result[$i]['TITLE']=$res['title'];
			$result[$i]['LINK']=mklink(
				'links.php?id='.$res['id'],
				'links,id'.$res['id'].urlformat($res['title']).'.html'
			);
		}
	}
	
	return $result;
}

?>