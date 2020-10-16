<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Counter
function stats_counter() {
	global $set,$db,$apx;
	
	$apx->lang->drop('counter','stats');
	$tmpl=new tengine;
	
	list($count_record,$record_time)=$db->first("SELECT sum(uniques) AS count,time FROM ".PRE."_stats GROUP BY daystamp ORDER BY count DESC LIMIT 1");
	list($count_total,$hits_total)=$db->first("SELECT sum(uniques),sum(hits) FROM ".PRE."_stats");
	list($count_today,$hits_today)=$db->first("SELECT sum(uniques),sum(hits) FROM ".PRE."_stats WHERE daystamp='".date('Ymd',time()-TIMEDIFF)."'");
	list($count_yesterday,$hits_yesterday)=$db->first("SELECT sum(uniques),sum(hits) FROM ".PRE."_stats WHERE daystamp='".date('Ymd',time()-24*3600-TIMEDIFF)."'");
	$count_total+=$set['stats']['startcount'];
	
	$tmpl->assign('VISITS_RECORD',number_format($count_record,0,'','.'));
	$tmpl->assign('VISITS_RECORD_TIME',$record_time);
	$tmpl->assign('VISITS_TOTAL',number_format($count_total,0,'','.'));
	$tmpl->assign('VISITS_TODAY',number_format($count_today,0,'','.'));
	$tmpl->assign('VISITS_YESTERDAY',number_format($count_yesterday,0,'','.'));
	$tmpl->assign('HITS_TOTAL',number_format($hits_total,0,'','.'));
	$tmpl->assign('HITS_TODAY',number_format($hits_today,0,'','.'));
	$tmpl->assign('HITS_YESTERDAY',number_format($hits_yesterday,0,'','.'));
	
	$tmpl->parse('counter','stats');
}

?>