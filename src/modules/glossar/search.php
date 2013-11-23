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


function search_glossar($items,$conn) {
	global $set,$db,$apx,$user;
	require_once(BASEDIR.getmodulepath('glossar').'functions.php');
	
	//Suchstring generieren
	$tagmatches = glossar_match_tags($items);
	foreach ( $items AS $item ) {
		$tagmatch = array_shift($tagmatches);
		$search[]=" ( ".iif($tagmatch, " id IN (".implode(',', $tagmatch).") OR ")." title LIKE '%".addslashes_like($item)."%' OR text LIKE '%".addslashes_like($item)."%' ) ";
	}
	$searchstring=implode($conn,$search);
	
	//Ergebnisse
	$data=$db->fetch("SELECT id,title FROM ".PRE."_glossar WHERE ( searchable='1' AND starttime!='0' AND ( ".$searchstring." ) ) ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$result[$i]['TITLE']=strip_tags($res['title']);
			$result[$i]['LINK']=mklink(
				'glossar.php?id='.$res['id'],
				'glossar,id'.$res['id'].urlformat($res['title']).'.html'
			);
		}
	}
	
	return $result;
}

?>