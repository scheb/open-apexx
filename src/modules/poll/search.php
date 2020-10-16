<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



function search_poll($items,$conn) {
	global $set,$db,$apx,$user;
	require_once(BASEDIR.getmodulepath('poll').'functions.php');
	
	$tagmatches = poll_match_tags($items);
	foreach ( $items AS $item ) {
		$tagmatch = array_shift($tagmatches);
		$query=" ( ".iif($tagmatch, " id IN (".implode(',', $tagmatch).") OR ")." question LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a1 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a2 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a3 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a4 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a5 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a6 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a7 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a8 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a9 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a10 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a11 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a12 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a13 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a14 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a15 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a16 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a17 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a18 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a19 LIKE '%".addslashes_like($item)."%'";
		$query.=" OR a20 LIKE '%".addslashes_like($item)."%' )";
		$search[]=$query;
	}
	$searchstring=implode($conn,$search);
	
	//Aktuelle Umfrage
	require_once(BASEDIR.getmodulepath('poll').'functions.php');
	$recent=poll_recent();
	
	//Ergebnisse
	$data=$db->fetch("SELECT id,question FROM ".PRE."_poll WHERE ( searchable='1' AND '".time()."' BETWEEN starttime AND endtime ".section_filter()." AND ( ".$searchstring." ) ) ORDER BY starttime DESC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['id']==$recent ) {
				$link=mklink(
					'poll.php?recent=1',
					'poll,recent.html'
				);
			}
			else {
				$link=mklink(
					'poll.php?id='.$res['id'],
					'poll,'.$res['id'].'.html'
				);
			}
			
			$result[$i]['TITLE']=strip_tags($res['question']);
			$result[$i]['LINK']=$link;
		}
	}
	
	return $result;
}

?>