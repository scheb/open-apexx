<?php 

# FAQ Class
# =========

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Statistik anzeigen
function faq_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'faq');
	
	$apx->lang->drop('func_stats', 'faq');
	
	if ( in_template(array('COUNT_ARTICLES', 'AVG_HITS'), $parse) ) {
		list($count, $hits) = $db->first("
			SELECT count(id), avg(hits) FROM ".PRE."_faq
			WHERE starttime!=0
		");
		$tmpl->assign('COUNT_FAQ', $count);
		$tmpl->assign('AVG_HITS', round($hits));
	}
	
	$tmpl->parse('functions/'.$template,'faq');
}


?>