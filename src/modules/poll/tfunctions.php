<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



require_once(BASEDIR.getmodulepath('poll').'functions.php');



//Kleine Umfrage
function poll_small($id=false,$template='poll') {
	global $set,$db,$apx,$user;
	$id=(int)$id;
	
	$tmpl=new tengine;
	$apx->lang->drop('poll','poll');
	$recent=poll_recent();
	if ( !$id ) $id=$recent;
	
	//Verwendete Variablen auslesen
	$parse=$tmpl->used_vars('functions/'.$template,'poll');
	
	$pollinfo=$db->first("SELECT *,a1_c+a2_c+a3_c+a4_c+a5_c+a6_c+a7_c+a8_c+a9_c+a10_c+a11_c+a12_c+a13_c+a14_c+a15_c+a16_c+a17_c+a18_c+a19_c+a20_c AS total FROM ".PRE."_poll WHERE ( id='".$id."' ".section_filter()." ) LIMIT 1");
	if ( !$pollinfo['id'] ) return;
	if ( $user->info['userid'] ) list($ipblock)=$db->first("SELECT ip FROM ".PRE."_poll_iplog WHERE ( id='".$id."' AND userid='".$user->info['userid']."' AND time>".(time()-24*3600)." ) LIMIT 1");
	else list($ipblock)=$db->first("SELECT ip FROM ".PRE."_poll_iplog WHERE ( id='".$id."' AND ip='".ip2integer(get_remoteaddr())."' AND time>".(time()-24*3600)." ) LIMIT 1");
	
	//Ergebnisse zeigen
	if (
		( $pollinfo['id']!=$recent && !$set['poll']['archvote'] )
		|| $_COOKIE[$set['main']['cookie_pre'].'_voted'][$pollinfo['id']]=='1'
		|| $ipblock
		|| ($pollinfo['starttime']+$pollinfo['days']*24*3600)<=time()
	) {
		$result=poll_format_result($pollinfo);
		
		foreach ( $result AS $element ) {
			++$ri;
			
			$percent=round($element[1]/iif($pollinfo['total'],$pollinfo['total'],1)*100,$set['poll']['percentdigits']);
			$width=round($percent).'%';
			
			$resdata[$ri]['ANSWER']=$element[0];
			$resdata[$ri]['VOTES']=$element[1];
			$resdata[$ri]['COLOR']=$element[2];
			$resdata[$ri]['PERCENT']=$percent.'%';
			$resdata[$ri]['WIDTH']=$width;
		}
		
		if ( ($pollinfo['starttime']+$pollinfo['days']*24*3600)<=time() ) $set_end=1;
		if ( $_COOKIE[$set['main']['cookie_pre'].'_voted'][$pollinfo['id']]=='1' || $ipblock ) $set_voted=1;
		
		$tmpl->assign('TOTALVOTES',$pollinfo['total']);
		$tmpl->assign('RESULT',$resdata);
		$tmpl->assign('SET_END',$set_end);
		$tmpl->assign('SET_VOTED',$set_voted);
	}
	
	//Optionen zeigen
	else {
		for ( $i=1; $i<=20; $i++ ) {
			if ( !$pollinfo['a'.$i] ) continue;
			
			if ( $pollinfo['multiple'] ) $box='<input type="checkbox" name="vote['.$i.']" value="1" />'; 
			else $box='<input type="radio" name="vote" value="'.$i.'" />';
				
			$optdata[$i]['ANSWER']=$pollinfo['a'.$i];
			$optdata[$i]['COLOR']=$pollinfo['color'.$i];
			$optdata[$i]['BOX']=$box;
		}
		
		$postto=mklink(
			'poll.php?id='.$pollinfo['id'],
			'poll,'.$pollinfo['id'].urlformat($pollinfo['question']).'.html'
		);
		
		$tmpl->assign('POSTTO',$postto);
		$tmpl->assign('OPTION',$optdata);
	}
	
	//Link: Ergebnis zeigen
	if ( $pollinfo['id']==$recent ) {
		$resultlink=mklink(
			'poll.php?recent=1&amp;result=1',
			'poll,recent.html?result=1'
		);
	}	
	else {
		$resultlink=mklink(
			'poll.php?id='.$pollinfo['id'].'&amp;result=1',
			'poll,'.$pollinfo['id'].urlformat($pollinfo['question']).'.html?result=1'
		);
	}
	
	//Tags
	if ( in_array('TAG',$parse) || in_array('TAG_IDS',$parse) || in_array('KEYWORDS',$parse) ) {
		list($tagdata, $tagids, $keywords) = poll_tags($res['id']);
	}
	
	//Kommentare
	if ( $set['poll']['coms'] && $pollinfo['allowcoms'] && $apx->is_module('comments') ) {
		require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
		$coms=new comments('poll',$id);
		
		$tmpl->assign('COMMENT_LINK',$coms->link($resultlink));
		$tmpl->assign('COMMENT_COUNT',$coms->count());
		$tmpl->assign('DISPLAY_COMMENTS',1);
	}
	
	//Link zum Poll
	$pollink = mklink(
		'poll.php?id='.$pollinfo['id'],
		'poll,'.$pollinfo['id'].urlformat($pollinfo['question']).'.html'
	);
		
	//Ausgabe
	$tmpl->assign('LINK',$pollink);
	$tmpl->assign('LINK_RESULT',$resultlink);
	$tmpl->assign('ID',$pollinfo['id']);
	$tmpl->assign('QUESTION',$pollinfo['question']);
	$tmpl->assign('STARTTIME',$pollinfo['starttime']);
	$tmpl->assign('ENDTIME',$pollinfo['starttime']+$pollinfo['days']*24*3600);
	
	//Tags
	$tmpl->assign('TAG_IDS', $tagids);
	$tmpl->assign('TAG', $tagdata);
	$tmpl->assign('KEYWORDS', $keywords);
	
	$tmpl->parse('functions/'.$template,'poll');
}



//Zufällige Umfrage anzeigen
function poll_random($template='random') {
	global $set,$db,$apx,$user;
	list($id2go)=$db->first("SELECT id FROM ".PRE."_poll WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) AND (starttime+(days*24*3600))>".time()." ".section_filter().") ORDER BY RAND() LIMIT 1");
	poll_small($id2go,$template);
}



//Ähnliche Umfragen
function poll_similar($tagids=array(),$count=5,$start=0,$template='similar') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$tmpl=new tengine;
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars('functions/'.$template,'poll');
	
	if ( !is_array($tagids) ) {
		$tagids = getTagIds(strval($tagids));
	}
	$ids = poll_search_tags($tagids);
	$ids[] = -1;
	$tagfilter = " AND id IN (".implode(', ', $ids).") ";
	
	$data=$db->fetch("SELECT id,question,addtime,starttime,days,allowcoms FROM ".PRE."_poll WHERE ( '".time()."' BETWEEN starttime AND endtime ".$tagfilter." ".section_filter()." ) ORDER BY starttime DESC LIMIT ".$count);
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Tags
			if ( in_array('POLL.TAG',$parse) || in_array('POLL.TAG_IDS',$parse) || in_array('POLL.KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = poll_tags($res['id']);
			}
			
			$tabledata[$i]['QUESTION']=$res['question'];
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['STARTTIME']=$res['starttime'];
			$tabledata[$i]['ENDTIME']=($res['starttime']+$res['days']*24*3600);
			
			//Tags
			$tabledata[$i]['TAG']=$tagdata;
			$tabledata[$i]['TAG_IDS']=$tagids;
			$tabledata[$i]['KEYWORDS']=$keywords;
			
			$tabledata[$i]['LINK']=mklink(
				'poll.php?id='.$res['id'],
				'poll,'.$res['id'].urlformat($res['question']).'.html'
			);
			
			//Kommentare
			if ( $apx->is_module('comments') && $set['poll']['coms'] && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('poll',$res['id']);
				else $coms->mid=$res['id'];
				
				$link=mklink(
					'poll.php?id='.$res['id'],
					'poll,'.$res['id'].urlformat($res['question']).'.html'
				);
				
				$tabledata[$i]['COMMENT_COUNT']=$coms->count();
				$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
				$tabledata[$i]['DISPLAY_COMMENTS']=1;
				if ( in_template(array('POLL.COMMENT_LAST_USERID','POLL.COMMENT_LAST_NAME','POLL.COMMENT_LAST_TIME'),$parse) ) {
					$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
					$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
					$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
		}
	}
	
	$tmpl->assign('POLL',$tabledata);
	$tmpl->parse('functions/'.$template,'poll');
}



//Tags auflisten
function poll_tagcloud($count=10, $random=false, $template='tagcloud') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$catid=(int)$catid;
	
	if ( $random ) {
		$orderby = "RAND()";
	}
	else {
		$orderby = "weight DESC";
	}
	
	//Sektion gewählt
	if ( $apx->section_id() ) {
		$data = $db->fetch("
			SELECT t.tagid, t.tag, count(nt.id) AS weight
			FROM ".PRE."_poll_tags AS nt
			LEFT JOIN ".PRE."_poll AS n ON nt.id=n.id
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_poll_tags AS nt2 ON nt.tagid=nt2.tagid
			WHERE 1 ".section_filter(true, 'n.secid')."
			GROUP BY nt.tagid
			ORDER BY ".$orderby."
			LIMIT ".$count."
		");
	}
	
	//Keine Sektion gewählt
	else {
		$data = $db->fetch("
			SELECT t.tagid, t.tag, count(nt.id) AS weight
			FROM ".PRE."_poll_tags AS nt
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_poll_tags AS nt2 ON nt.tagid=nt2.tagid
			GROUP BY nt.tagid
			ORDER BY ".$orderby."
			LIMIT ".$count."
		");
	}
	
	if ( count($data) ) {
		$maxweight = 1;
		foreach ( $data AS $res ) {
			if ( $res['weight']>$maxweight ) {
				$maxweight = $res['weight'];
			}
		}
		if ( !$random ) {
			shuffle($data);
		}
		foreach ( $data AS $res ) {
			$tagdata[] = array(
				'ID' => $res['tagid'],
				'NAME' => replace($res['tag']),
				'WEIGHT' => $res['weight']/$maxweight
			);
		}
	}
	
	$tmpl->assign('TAG',$tagdata);
	$tmpl->parse('functions/'.$template,'poll');
}



//Statistik anzeigen
function poll_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'poll');
	
	$apx->lang->drop('func_stats', 'poll');
	
	if ( in_template(array('COUNT_POLLS', 'AVG_VOTES'), $parse) ) {
		list($count, $hits) = $db->first("
			SELECT count(id), avg(a1_c+a2_c+a3_c+a4_c+a5_c+a6_c+a7_c+a8_c+a9_c+a10_c+a11_c+a12_c+a13_c+a14_c+a15_c+a16_c+a17_c+a18_c+a19_c+a20_c) FROM ".PRE."_poll
			WHERE ".time()." BETWEEN starttime AND endtime
		");
		$tmpl->assign('COUNT_POLLS', $count);
		$tmpl->assign('AVG_VOTES', round($hits));
	}
	
	$tmpl->parse('functions/'.$template,'poll');
}

?>