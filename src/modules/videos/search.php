<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



function search_videos($items,$conn) {
	global $set,$db,$apx,$user;
	require_once(BASEDIR.getmodulepath('videos').'functions.php');
	
	//Suchstring generieren
	$tagmatches = videos_match_tags($items);
	foreach ( $items AS $item ) {
		$tagmatch = array_shift($tagmatches);
		$search[]=" ( ".iif($tagmatch, " id IN (".implode(',', $tagmatch).") OR ")." title LIKE '%".addslashes_like($item)."%' OR text LIKE '%".addslashes_like($item)."%' ) ";
	}
	$searchstring=implode($conn,$search);
	
	//Videos durchsuchen
	$data=$db->fetch("SELECT id,title FROM ".PRE."_videos WHERE ( searchable='1' AND '".time()."' BETWEEN starttime AND endtime ".section_filter()." AND ( ".$searchstring." ) ) ORDER BY addtime DESC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			$result[$i]['TITLE']=$res['title'];
			$result[$i]['LINK']=mklink(
				'videos.php?id='.$res['id'],
				'videos,id'.$res['id'].urlformat($res['title']).'.html'
			);
		}
	}
	
	return $result;
}

?>