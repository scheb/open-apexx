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


# GAMES CLASS
# ===========

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



function search_products($items,$conn) {
	global $set,$db,$apx,$user;
	require_once(BASEDIR.getmodulepath('products').'functions.php');
	
	//Suchstring generieren
	$tagmatches = products_match_tags($items);
	foreach ( $items AS $item ) {
		$tagmatch = array_shift($tagmatches);
		$search[]=" ( ".iif($tagmatch, " id IN (".implode(',', $tagmatch).") OR ")." title LIKE '%".addslashes_like($item)."%' OR text LIKE '%".addslashes_like($item)."%' ) ";
	}
	$searchstring=implode($conn,$search);
	
	//Ergebnisse
	$data=$db->fetch("SELECT id,title FROM ".PRE."_products WHERE ( active='1' AND searchable='1' AND ( ".$searchstring." ) ) ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			$link=mklink(
				'products.php?id='.$res['id'],
				'products,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			$result[$i]['TITLE']=strip_tags($res['title']);
			$result[$i]['LINK']=$link;
		}
	}
	
	return $result;
}

?>