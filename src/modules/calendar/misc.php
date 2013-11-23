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



require_once(BASEDIR.getmodulepath('calendar').'functions.php');



//Kommentar-Popup
function misc_calendar_comments() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->tmpl->loaddesign('blank');
	calendar_showcomments($_REQUEST['id']);
}



//Eventfeed ausgeben
function misc_eventsfeed() {
	global $set,$db,$apx;
	$apx->tmpl->loaddesign('blank');
	header('Content-type: application/rss+xml');
	
	//Baum holen, wenn nur eine bestimmte Kategorie gezeigt werden soll
	$cattree=calendar_tree($_REQUEST['catid']);
	
	if ( $catid ) {
		$cattree = calendar_tree($catid);
		if ( count($cattree) ) $catfilter = " AND a.catid IN (".implode(',',$cattree).") ";
	}
	$todaystamp = date('Ymd',time()-TIMEDIFF);
	$data = $db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.active!=0 AND a.private='0' AND '".$todaystamp."'<=endday ".section_filter(true,'a.secid')." ".$catfilter.$modefilter." ORDER BY startday ASC, starttime ASC, title ASC LIMIT 20");
	
	//Kategorie-Info
	$catids = get_ids($data,'catid');
	$catdata = array();
	if ( count($catids) ) {
		$catdata = $db->fetch_index("SELECT * FROM ".PRE."_calendar_cat WHERE id IN (".implode(',',$catids).")",'id');
	}
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$event = array();
			
			//Kategorie-Info
			$catinfo = $catdata[$res['catid']];
			
			//Link zum Termin
			$link = mklink(
				'events.php?id='.$res['id'],
				'events,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			//Start berechnen
			$startday = $starttime = $endday = $endtime = 0;
			$startday = calendar_stamp2time($res['startday']);
			if ( $res['starttime']!=-1 ) {
				$time_comp = calendar_explode_stamp($res['startday']);
				$tmpstamp = sprintf('%04d',$res['starttime']);
				$time_comp['hour'] = substr($tmpstamp,0,2);
				$time_comp['minute'] = substr($tmpstamp,2,2);
				$starttime = mktime($time_comp['hour'],$time_comp['minute'],0,$time_comp['month'],$time_comp['day'],$time_comp['year'])+TIMEDIFF;
			}
			
			//Ende berechnen (falls nötig)
			if ( $res['endday']!=$res['startday'] || $res['endtime']!=-1 ) {
				$endday = calendar_stamp2time($res['endday']);
				if ( $res['endtime']!=-1 ) {
					$time_comp = calendar_explode_stamp($res['endday']);
					$tmpstamp = sprintf('%04d',$res['endtime']);
					$time_comp['hour'] = substr($tmpstamp,0,2);
					$time_comp['minute'] = substr($tmpstamp,2,2);
					$endtime = mktime($time_comp['hour'],$time_comp['minute'],0,$time_comp['month'],$time_comp['day'],$time_comp['year'])+TIMEDIFF;
				}
			}
			
			$event['ID'] = $res['id'];
			$event['TITLE'] = rss_replace($res['title']);
			$event['TEXT'] = rss_replace($res['text']);
			$event['LINK'] = HTTP_HOST.$link;
			$event['LOCATION'] = $res['location'];
			$event['LOCATION_LINK'] = $res['location_link'];
			$event['STARTDAY'] = $startday;
			$event['STARTTIME'] = $starttime;
			$event['ENDDAY'] = $endday;
			$event['ENDTIME'] = $endtime;
			$event['ADDTIME'] = $res['addtime'];
			$event['TIME'] = date('r',$res['addtime']);
			$event['CATID'] = $res['catid'];
			$event['CATTITLE'] = $catinfo['title'];
			$event['CATICON'] = $catinfo['icon'];
			
			$tabledata[] = $event;
		}
	}
	
	$apx->tmpl->assign('WEBSITENAME',$set['main']['websitename']);
	$apx->tmpl->assign('EVENT',$tabledata);
	$apx->tmpl->parse('rss','calendar');
}


?>