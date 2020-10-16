<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



function search_news($items,$conn) {
	global $set,$db,$apx,$user;
	require_once(BASEDIR.getmodulepath('news').'functions.php');
	
	//Suchstring generieren
	$tagmatches = news_match_tags($items);
	foreach ( $items AS $item ) {
		$tagmatch = array_shift($tagmatches);
		$search[]=" ( ".iif($tagmatch, " id IN (".implode(',', $tagmatch).") OR ")." title LIKE '%".addslashes_like($item)."%' OR subtitle LIKE '%".addslashes_like($item)."%' OR teaser LIKE '%".addslashes_like($item)."%' OR text LIKE '%".addslashes_like($item)."%' ) ";
	}
	$searchstring=implode($conn,$search);
	
	//Ergebnisse
	$data=$db->fetch("SELECT id,title,subtitle FROM ".PRE."_news WHERE ( searchable='1' AND '".time()."' BETWEEN starttime AND endtime ".section_filter()." AND ( ".$searchstring." ) ) ORDER BY starttime DESC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			$link=mklink(
				'news.php?id='.$res['id'],
				'news,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			$result[$i]['TITLE']=strip_tags($res['title']).iif($res['subtitle'],' - '.strip_tags($res['subtitle']));
			$result[$i]['LINK']=$link;
		}
	}
	
	return $result;
}

?>