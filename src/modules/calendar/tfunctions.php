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



//Neuste Termine
function calendar_events_last($count=5,$start=0,$catid=false,$mode=false,$template='last') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( in_array($type,array('public','private')) ) $type='';
	
	//Filter
	$catfilter = '';
	$modefilter = '';
	if ( $catid ) {
		$cattree = calendar_tree($catid);
		if ( count($cattree) ) $catfilter = " AND a.catid IN (".implode(',',$cattree).") ";
	}
	if ( $mode==='private' ) $modefilter = " AND a.private='1' AND a.userid='".$user->info['userid']."' ";
	elseif ( $mode==='public' ) $modefilter = " AND a.private='0' ";
	else $modefilter = " AND ( a.private='0' OR ( a.private='1' AND a.userid='".$user->info['userid']."' ) ) ";
	
	$data=$db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.active!=0 ".section_filter(true,'a.secid')." ".$catfilter.$modefilter." ORDER BY addtime DESC LIMIT ".$start.','.$count);
	calendar_print($data,'functions/'.$template);
}



//Zufällige Termine
function calendar_events_random($count=5,$start=0,$catid=false,$mode=false,$template='random') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( in_array($type,array('public','private')) ) $type='';
	
	//Filter
	$catfilter = '';
	$modefilter = '';
	if ( $catid ) {
		$cattree = calendar_tree($catid);
		if ( count($cattree) ) $catfilter = " AND a.catid IN (".implode(',',$cattree).") ";
	}
	if ( $mode==='private' ) $modefilter = " AND a.private='1' AND a.userid='".$user->info['userid']."' ";
	elseif ( $mode==='public' ) $modefilter = " AND a.private='0' ";
	else $modefilter = " AND ( a.private='0' OR ( a.private='1' AND a.userid='".$user->info['userid']."' ) ) ";
	
	$data=$db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.active!=0 ".section_filter(true,'a.secid')." ".$catfilter.$modefilter." ORDER BY RAND() LIMIT ".$start.','.$count);
	calendar_print($data,'functions/'.$template);
}



//Aktuelle Termine
function calendar_events_recent($count=5,$start=0,$catid=false,$mode=false,$template='recent') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( in_array($type,array('public','private')) ) $type='';
	
	//Filter
	$catfilter = '';
	$modefilter = '';
	if ( $catid ) {
		$cattree = calendar_tree($catid);
		if ( count($cattree) ) $catfilter = " AND a.catid IN (".implode(',',$cattree).") ";
	}
	if ( $mode==='private' ) $modefilter = " AND a.private='1' AND a.userid='".$user->info['userid']."' ";
	elseif ( $mode==='public' ) $modefilter = " AND a.private='0' ";
	else $modefilter = " AND ( a.private='0' OR ( a.private='1' AND a.userid='".$user->info['userid']."' ) ) ";
	$todaystamp = date('Ymd',time()-TIMEDIFF);
	$todaytime = date('Hi',time()-TIMEDIFF);
	
	$data = $db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.active!=0 AND ( '".$todaystamp."'<endday OR ( '".$todaystamp."'=endday AND ( endtime=-1 OR '".$todaytime."'<=endtime ) ) ) ".section_filter(true,'a.secid')." ".$catfilter.$modefilter." ORDER BY startday ASC, starttime ASC, title ASC LIMIT ".$start.','.$count);
	calendar_print($data,'functions/'.$template);
}



//Termine der nächsten X Tage
function calendar_events_nextdays($days=6,$catid=false,$mode=false,$template='nextdays') {
	global $set,$db,$apx,$user;
	$days=(int)$days;
	$catid=(int)$catid;
	if ( in_array($type,array('public','private')) ) $type='';
	
	//Filter
	$catfilter = '';
	$modefilter = '';
	if ( $catid ) {
		$cattree = calendar_tree($catid);
		if ( count($cattree) ) $catfilter = " AND a.catid IN (".implode(',',$cattree).") ";
	}
	if ( $mode==='private' ) $modefilter = " AND a.private='1' AND a.userid='".$user->info['userid']."' ";
	elseif ( $mode==='public' ) $modefilter = " AND a.private='0' ";
	else $modefilter = " AND ( a.private='0' OR ( a.private='1' AND a.userid='".$user->info['userid']."' ) ) ";
	$todaystamp = date('Ymd',time()-TIMEDIFF);
	$maxstamp = date('Ymd',time()-TIMEDIFF+$days*24*3600);
	
	$data = $db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.active!=0 AND '".$todaystamp."'<=endday AND '".$maxstamp."'>=startday ".section_filter(true,'a.secid')." ".$catfilter.$modefilter." ORDER BY startday ASC, starttime ASC, title ASC");
	calendar_print($data,'functions/'.$template);
}



//Aktuelle Termine
function calendar_events_old($count=5,$start=0,$catid=false,$mode=false,$template='old') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( in_array($type,array('public','private')) ) $type='';
	
	//Filter
	$catfilter = '';
	$modefilter = '';
	if ( $catid ) {
		$cattree = calendar_tree($catid);
		if ( count($cattree) ) $catfilter = " AND a.catid IN (".implode(',',$cattree).") ";
	}
	if ( $mode==='private' ) $modefilter = " AND a.private='1' AND a.userid='".$user->info['userid']."' ";
	elseif ( $mode==='public' ) $modefilter = " AND a.private='0' ";
	else $modefilter = " AND ( a.private='0' OR ( a.private='1' AND a.userid='".$user->info['userid']."' ) ) ";
	$todaystamp = date('Ymd',time()-TIMEDIFF);
	$todaytime = date('Hi',time()-TIMEDIFF);
	
	$data = $db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.active!=0 AND NOT ( '".$todaystamp."'<endday OR ( '".$todaystamp."'=endday AND ( endtime=-1 OR '".$todaytime."'<=endtime ) ) ) ".section_filter(true,'a.secid')." ".$catfilter.$modefilter." ORDER BY startday DESC, starttime DESC, title ASC LIMIT ".$start.','.$count);
	calendar_print($data,'functions/'.$template);
}



//Termine der letzten X Tage
function calendar_events_lastdays($days=6,$catid=false,$mode=false,$template='lastdays') {
	global $set,$db,$apx,$user;
	$days=(int)$days;
	$catid=(int)$catid;
	if ( in_array($type,array('public','private')) ) $type='';
	
	//Filter
	$catfilter = '';
	$modefilter = '';
	if ( $catid ) {
		$cattree = calendar_tree($catid);
		if ( count($cattree) ) $catfilter = " AND a.catid IN (".implode(',',$cattree).") ";
	}
	if ( $mode==='private' ) $modefilter = " AND a.private='1' AND a.userid='".$user->info['userid']."' ";
	elseif ( $mode==='public' ) $modefilter = " AND a.private='0' ";
	else $modefilter = " AND ( a.private='0' OR ( a.private='1' AND a.userid='".$user->info['userid']."' ) ) ";
	$todaystamp = date('Ymd',time()-TIMEDIFF-$days*24*3600); //Nicht wirklich heute
	$maxstamp = date('Ymd',time()-TIMEDIFF-24*3600); //Das ist gestern
	
	$data = $db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.active!=0 AND '".$todaystamp."'<=endday AND '".$maxstamp."'>=startday ".section_filter(true,'a.secid')." ".$catfilter.$modefilter." ORDER BY startday ASC, starttime ASC, title ASC");
	calendar_print($data,'functions/'.$template);
}



//Aktuelle Termine mit eigenem Teilnahmevermerk
function calendar_events_participate($userid=0,$count=5,$start=0,$catid=false,$template='participate') {
	global $set,$db,$apx,$user;
	$userid=(int)$userid;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( !$userid ) return;
	
	//Filter
	$catfilter = '';
	if ( $catid ) $catfilter = " AND a.catid='".$catid."' ";
	$todaystamp = date('Ymd',time()-TIMEDIFF);
	
	$data = $db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) LEFT JOIN ".PRE."_calendar_parts AS c ON c.eventid=a.id WHERE a.active!=0 AND c.userid='".$userid."' AND endday>='".$todaystamp."' ".section_filter(true,'a.secid')." ".$catfilter." ORDER BY startday ASC,starttime ASC, title ASC LIMIT ".$start.','.$count);
	calendar_print($data,'functions/'.$template);
}



//Ähnliche Termine
function calendar_events_similar($tagids=array(),$count=5,$start=0,$catid=false,$mode=false,$template='similar') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( in_array($type,array('public','private')) ) $type='';
	
	if ( !is_array($tagids) ) {
		$tagids = getTagIds(strval($tagids));
	}
	$ids = calendar_search_tags($tagids);
	$ids[] = -1;
	$tagfilter = " AND a.id IN (".implode(', ', $ids).") ";
	
	//Filter
	$catfilter = '';
	$modefilter = '';
	if ( $catid ) {
		$cattree = calendar_tree($catid);
		if ( count($cattree) ) $catfilter = " AND a.catid IN (".implode(',',$cattree).") ";
	}
	if ( $mode==='private' ) $modefilter = " AND a.private='1' AND a.userid='".$user->info['userid']."' ";
	elseif ( $mode==='public' ) $modefilter = " AND a.private='0' ";
	else $modefilter = " AND ( a.private='0' OR ( a.private='1' AND a.userid='".$user->info['userid']."' ) ) ";
	$todaystamp = date('Ymd',time()-TIMEDIFF);
	
	$data = $db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.active!=0 AND endday>='".$todaystamp."' ".$tagfilter." ".section_filter(true,'a.secid')." ".$catfilter.$modefilter." ORDER BY startday ASC,starttime ASC, title ASC LIMIT ".$start.','.$count);
	calendar_print($data,'functions/'.$template);
}



//AUSGABE
function calendar_print($data,$template) {
	global $set,$db,$apx,$user; 
	$tmpl=new tengine;
	
	$parse = $tmpl->used_vars($template,'calendar');
	
	//Kategorie-Info
	$catids = get_ids($data,'catid');
	$catdata = array();
	if ( count($catids) ) {
		$catdata = $db->fetch_index("SELECT * FROM ".PRE."_calendar_cat WHERE id IN (".implode(',',$catids).")",'id');
	}
	
	//Termine auflisten
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
			
			//Aufmacher
			$picture = $picture_popup = '';
			if ( in_template(array('EVENT.PICTURE','EVENT.PICTURE_POPUP','EVENT.PICTURE_POPUPPATH'),$parse) ) {
				list($picture,$picture_popup,$picture_popuppath)=calendar_pic($res['picture']);
			}
			
			//Start berechnen
			$startday = $starttime = $endday = $endtime = 0;
			if ( in_template(array('EVENT.STARTDAY','EVENT.STARTTIME'),$parse) ) {
				$startday = calendar_stamp2time($res['startday']);
				if ( $res['starttime']!=-1 ) {
					$time_comp = calendar_explode_stamp($res['startday']);
					$tmpstamp = sprintf('%04d',$res['starttime']);
					$time_comp['hour'] = substr($tmpstamp,0,2);
					$time_comp['minute'] = substr($tmpstamp,2,2);
					$starttime = mktime($time_comp['hour'],$time_comp['minute'],0,$time_comp['month'],$time_comp['day'],$time_comp['year'])+TIMEDIFF;
				}
			}
			
			//Ende berechnen (falls nötig)
			if ( in_template(array('EVENT.ENDDAY','EVENT.ENDTIME'),$parse) ) {
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
			}
			
			//Username + eMail
			if ( $res['userid'] ) {
				$username=$res['username'];
				$email=iif(!$res['pub_hidemail'],$res['email']);
			}
			else {
				$username=$res['send_username'];
				$email=$res['send_email'];
			}
			
			//Text
			$eventtext = '';
			if ( in_array('EVENT.TEXT',$parse) ) {
				$eventtext = mediamanager_inline($res['text']);
				if ( $apx->is_module('glossar') ) $eventtext = glossar_highlight($eventtext);
			}
			
			//Datehead
			if ( $laststamp!=$res['startday'] ) {
				$event['DATEHEAD'] = $startday;
			}
			
			//Tags
			if ( in_array('EVENT.TAG',$parse) || in_array('EVENT.TAG_IDS',$parse) || in_array('EVENT.KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = calendar_tags($res['id']);
			}
			
			$event['ID'] = $res['id'];
			$event['TITLE'] = $res['title'];
			$event['TEXT'] = $eventtext;
			$event['LINK'] = $link;
			$event['LOCATION'] = compatible_hsc($res['location']);
			$event['LOCATION_LINK'] = compatible_hsc($res['location_link']);
			$event['PRIORITY'] = $res['priority'];
			$event['RESTRICTED'] = $res['restricted'];
			$event['PRIVATE'] = $res['private'];
			$event['HITS'] = $res['hits'];
			$event['RELATED'] = calendar_links($res['links']);
			$event['PICTURE'] = $picture;
			$event['PICTURE_POPUP'] = $picture_popup;
			$event['PICTURE_POPUPPATH'] = $picture_popuppath;
			$event['STARTDAY'] = $startday;
			$event['STARTTIME'] = $starttime;
			$event['ENDDAY'] = $endday;
			$event['ENDTIME'] = $endtime;
			$event['USERID'] = $res['userid'];
			$event['USERNAME'] = replace($username);
			$event['EMAIL'] = replace($email);
			$event['EMAIL_ENCRYPTED'] = replace(cryptMail($email));
			$event['CATID'] = $res['catid'];
			$event['CATTITLE'] = $catinfo['title'];
			$event['CATICON'] = $catinfo['icon'];
			
			//Tags
			$event['TAG']=$tagdata;
			$event['TAG_IDS']=$tagids;
			$event['KEYWORDS']=$keywords;
			
			//Galerie
			if ( $apx->is_module('gallery') && $res['galid'] && !$res['private'] && in_template(array('EVENT.GALLERY_ID','EVENT.GALLERY_TITLE','EVENT.GALLERY_LINK'),$parse) ) {
				$galinfo=gallery_info($res['galid']);
				$event['GALLERY_ID']=$galinfo['id'];
				$event['GALLERY_TITLE']=$galinfo['title'];
				$event['GALLERY_LINK']=mklink(
					'gallery.php?id='.$galinfo['id'],
					'gallery,list'.$galinfo['id'].',1'.urlformat($galinfo['title']).'.html'
				);
			}
			
			//Kommentare
			if ( $apx->is_module('comments') && $set['calendar']['coms'] && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('calendar',$res['id']);
				else $coms->mid=$res['id'];
				
				$link=mklink(
					'events.php?id='.$res['id'],
					'events,id'.$res['id'].urlformat($res['title']).'.html'
				);
				
				$event['COMMENT_COUNT']=$coms->count();
				$event['COMMENT_LINK']=$coms->link($link);
				$event['DISPLAY_COMMENTS']=1;
				if ( in_template(array('EVENT.COMMENT_LAST_USERID','EVENT.COMMENT_LAST_NAME','EVENT.COMMENT_LAST_TIME'),$parse) ) {
					$event['COMMENT_LAST_USERID']=$coms->last_userid();
					$event['COMMENT_LAST_NAME']=$coms->last_name();
					$event['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
			
			$eventdata[$i] = $event;
			$laststamp = $res['startday'];
		}
	}
	
	$tmpl->assign('EVENT',$eventdata);
	$tmpl->parse($template,'calendar');
}



//Kategorien auflisten
function calendar_events_categories($catid=false,$template='categories') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$catid=(int)$catid;
	
	if ( $set['calendar']['subcats'] ) {
		if ( $catid ) {
			require_once(BASEDIR.'lib/class.recursivetree.php');
			$tree = new RecursiveTree(PRE.'_calendar_cat', 'id');
			$data = $tree->getTree(array('title'), $catid);
		}
		else {
			require_once(BASEDIR.'lib/class.recursivetree.php');
			$tree = new RecursiveTree(PRE.'_calendar_cat', 'id');
			$data = $tree->getTree(array('title'));
		}
	}
	else $data = $db->fetch("SELECT * FROM ".PRE."_calendar_cat ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $cat ) {
			++$i;
			
			//Kategorie-Link
			if ( isset($cat['link']) ) $link=$cat['link'];
			else $link=mklink(
				'events.php?catid='.$res['id'],
				'events.html?catid='.$res['id']
			);
			
			$catdata[$i]['ID']=$cat['id'];
			$catdata[$i]['TITLE']=$cat['title'];
			$catdata[$i]['LEVEL']=$cat['level'];
			$catdata[$i]['ICON']=$cat['icon'];
			$catdata[$i]['LINK']=$link;
		}
	}
	
	$tmpl->assign('CATEGORY',$catdata);
	$tmpl->parse('functions/'.$template,'calendar');
}



//Kategorien auflisten
function calendar_mini($month = 0, $year = 0, $template='minicalendar') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$todaystamp = (int)date('Ymd',time()-TIMEDIFF);
	
	$minyear = 1980;
	$maxyear = 2030;
	
	$month = (int)$month;
	$year = (int)$year;
	if ( !$month ) $month = intval(date('n', time()-TIMEDIFF));
	if ( !$year ) $year = intval(date('Y', time()-TIMEDIFF));
	if ( $year>$maxyear ) $year = $maxyear;
	if ( $year<$minyear ) $year = $minyear;
	if ( $month>12 ) $month = 12;
	if ( $month<1 ) $month = 1;
	$firstdaystamp = mktime(0,0,0,$month,1,$year)+TIMEDIFF;
	$monthdays = (int)date('t',$firstdaystamp-TIMEDIFF);
	
	
	//Verwendete Variablen auslesen
	$parse = $apx->tmpl->used_vars('functions/'.$template, 'calendar');
	
	
	//Tage des vorherigen Monats auffüllen
	$startday = 1;
	$timestamp = mktime(0,0,0,$month,1,$year)+TIMEDIFF;
	$subday = (date('w',$timestamp-TIMEDIFF)+6)%7;
	
	//Tage des nächsten Monats auffüllen
	$monthdays = (int)date('t',$timestamp-TIMEDIFF);
	$timestamp = mktime(0,0,0,$month,$monthdays,$year)+TIMEDIFF;
	$weekday = date('w',$timestamp-TIMEDIFF);
	$adddays = (7-$weekday)%7;
	
	
	//Tage des gewählten Monats durchlaufen
	$monthdata = array();
	for ( $cday=1-$subday; $cday<=$monthdays+$adddays; $cday++ ) {
		++$i;
		
		$timestamp = mktime(0,0,0,$month,$cday,$year)+TIMEDIFF;
		$thisdaystamp = (int)date('Ymd',$timestamp-TIMEDIFF);
		
		//Tag auswählen
		$link = mklink(
			'events.php?day='.date('j',$timestamp-TIMEDIFF).'&amp;month='.date('n',$timestamp-TIMEDIFF).'&amp;year='.date('Y',$timestamp-TIMEDIFF),
			'events,public,day'.date('dmY',$timestamp-TIMEDIFF).'.html'
		);
		
		$monthdata[$i]['TODAY'] = iif($thisdaystamp==$todaystamp,1,0);
		$monthdata[$i]['TIME'] = $timestamp;
		$monthdata[$i]['LINK_SELECT'] = $link;
		
		//Tag gehört nicht zum Monat => keine weiteren Informationen
		if (  $cday>=1 && $cday<=$monthdays ) {
			$monthdata[$i]['INMONTH'] = 1;
			
			//Anzahl Termine
			if ( in_array('DAY.EVENT_COUNT',$parse) ) {
				list($ecount)=$db->first("SELECT count(*) FROM ".PRE."_calendar_events AS a WHERE '".$thisdaystamp."' BETWEEN startday AND endday AND active!=0 ".section_filter()." ".$modefilter);
				$monthdata[$i]['EVENT_COUNT'] = $ecount;
			}
		}
	}
	
	$tmpl->assign('DAY',$monthdata);
	$tmpl->parse('functions/'.$template,'calendar');
}



//Tags auflisten
function calendar_tagcloud($count=10, $random=false, $template='tagcloud') {
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
			FROM ".PRE."_calendar_tags AS nt
			LEFT JOIN ".PRE."_calendar_events AS n ON nt.id=n.id
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_calendar_tags AS nt2 ON nt.tagid=nt2.tagid
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
			FROM ".PRE."_calendar_tags AS nt
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_calendar_tags AS nt2 ON nt.tagid=nt2.tagid
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
	$tmpl->parse('functions/'.$template,'calendar');
}



//Statistik anzeigen
function calendar_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'calendar');
	$apx->lang->drop('func_stats', 'calendar');
	
	if ( in_array('COUNT_CATGEORIES', $parse) ) {
		list($count) = $db->first("SELECT count(id) FROM ".PRE."_calendar_cat");
		$tmpl->assign('COUNT_CATGEORIES', $count);
	}
	if ( in_template(array('COUNT_EVENTS', 'AVG_HITS'), $parse) ) {
		list($count, $hits) = $db->first("
			SELECT count(id), avg(hits) FROM ".PRE."_calendar_events
			WHERE active!=0
		");
		$tmpl->assign('COUNT_EVENTS', $count);
		$tmpl->assign('AVG_HITS', round($hits));
	}
	
	$tmpl->parse('functions/'.$template,'calendar');
}


?>