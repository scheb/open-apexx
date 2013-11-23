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



function search_articles($items,$conn) {
	global $set,$db,$apx,$user;
	require_once(BASEDIR.getmodulepath('articles').'functions.php');
	
	//Suchstrings generieren
	$tagmatches = articles_match_tags($items);
	foreach ( $items AS $item ) {
		$tagmatch = array_shift($tagmatches);
		$search1[]="( title LIKE '%".addslashes_like($item)."%' OR text LIKE '%".addslashes_like($item)."%' ) ";
		$search2[]="( ".iif($tagmatch, " id IN (".implode(',', $tagmatch).") OR ")." title LIKE '%".addslashes_like($item)."%' OR subtitle LIKE '%".addslashes_like($item)."%' OR teaser LIKE '%".addslashes_like($item)."%' ) ";
	}
	$searchstring1="( ".implode($conn,$search1)." )";
	$searchstring2="( ".implode($conn,$search2)." )";
	
	//Seiten durchsuchen
	$data=$db->fetch("SELECT artid FROM ".PRE."_articles_pages WHERE ( ".$searchstring1." ) GROUP BY artid");
	$artids=get_ids($data,'artid');
	if ( count($artids) ) $pageres="id IN (".@implode(',',$artids).") OR";
	
	//Artikel durchsuchen
	$data=$db->fetch("SELECT id,type,title,subtitle FROM ".PRE."_articles WHERE ( searchable='1' AND '".time()."' BETWEEN starttime AND endtime ".section_filter()." AND ( ".$pageres." ".$searchstring2." ) ) ORDER BY starttime DESC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Wohin soll verlinkt werden?
			if ( $res['type']=='normal' ) $link2file='articles';
			else $link2file=$res['type'].'s';
			
			$link=mklink(
				$link2file.'.php?id='.$res['id'],
				$link2file.',id'.$res['id'].',0'.urlformat($res['title']).'.html'
			);
			
			$result[$i]['TITLE']=strip_tags($res['title']).iif($res['subtitle'],' - '.strip_tags($res['subtitle']));
			$result[$i]['LINK']=$link;
		}
	}
	
	return $result;
}


?>