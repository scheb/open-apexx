<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



function search_faq($items,$conn) {
	global $set,$db,$apx,$user;
	
	//Suchstring generieren
	foreach ( $items AS $item ) {
		$search[]=" ( question LIKE '%".addslashes_like($item)."%' OR answer LIKE '%".addslashes_like($item)."%' ) ";
	}
	$searchstring=implode($conn,$search);
	
	//Ergebnisse
	$data=$db->fetch("SELECT id,question FROM ".PRE."_faq WHERE ( searchable='1' AND starttime!='0' AND ( ".$searchstring." ) ) ORDER BY starttime DESC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			$link=mklink(
				'faq.php?id='.$res['id'],
				'faq,'.$res['id'].urlformat($res['question']).'.html'
			);
			
			$result[$i]['TITLE']=strip_tags($res['question']);
			$result[$i]['LINK']=$link;
		}
	}
	
	return $result;
}


?>