<?php 

define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once(BASEDIR.getmodulepath('calendar').'functions.php');

$apx->module('calendar');
$apx->lang->drop('global');
$apx->lang->drop('calendar');

//Eingaben parsen
$todaystamp = (int)date('Ymd',time()-TIMEDIFF);
if ( $_REQUEST['today'] ) {
	$_REQUEST['day'] = date('d',time()-TIMEDIFF);
	$_REQUEST['month'] = date('m',time()-TIMEDIFF);
	$_REQUEST['year'] = date('Y',time()-TIMEDIFF);
}
$_REQUEST['week'] = (int)$_REQUEST['week'];
$_REQUEST['day'] = (int)$_REQUEST['day'];
$_REQUEST['month'] = (int)$_REQUEST['month'];
$_REQUEST['year'] = (int)$_REQUEST['year'];

//Keine Ansicht gewählt => Standard
if ( !$_REQUEST['year'] ) {
	if ( $set['calendar']['start']=='day' ) {
		$_REQUEST['day'] = date('d',time()-TIMEDIFF);
		$_REQUEST['month'] = date('m',time()-TIMEDIFF);
		$_REQUEST['year'] = date('Y',time()-TIMEDIFF);
	}
	elseif ( $set['calendar']['start']=='week' ) {
		$_REQUEST['week'] = date('W',time()-TIMEDIFF);
		$_REQUEST['year'] = date('Y',time()-TIMEDIFF);
	}
	elseif ( $set['calendar']['start']=='month' ) {
		$_REQUEST['month'] = date('m',time()-TIMEDIFF);
		$_REQUEST['year'] = date('Y',time()-TIMEDIFF);
	}
}

//Modus
if ( !in_array($_REQUEST['mode'],array('public','private')) || !$user->info['userid'] ) $_REQUEST['mode'] = 'public';
if ( $_REQUEST['mode']=='private' ) $modefilter = " AND private='1' AND userid='".$user->info['userid']."' ";
else $modefilter = " AND private='0' ";

//Headline
$callink = mklink('calendar.php','calendar.html');
if ( $_REQUEST['mode']=='private' ) $callink = mklink('calendar.php?mode=private','calendar,private.html');
headline($apx->lang->get('HEADLINE'),$callink);
titlebar($apx->lang->get('HEADLINE'));

//Beachte UNIX-Timestamp!
$minyear = 1980;
$maxyear = 2030;



/////////////////////////////////////////////////////////////////////////////////////// FUNKTIONEN

//Gibt den Montag der ersten Kalenderwoche eines Jahrs zurück
function first_calweek($year) {
	$firstday = mktime(0,0,0,1,1,$year)+TIMEDIFF;
	$dayofweek = date('w',$firstday-TIMEDIFF);
	
	//Donnerstag oder später => Woche gehört zu diesem Jahr
	if ($dayofweek <= 4) {
		$monday = mktime(0,0,0,1,1-($dayofweek-1),$year)+TIMEDIFF;
	}
	
	//Vor Donnerstag => gehört zum vorherigen Jahr, also zum nächsten Montag nach vorne rechnen
	else {
		$monday = mktime(0,0,0,1,1+(7-$dayofweek+1),$year)+TIMEDIFF;
	}
	return $monday;
}



//Montag einer bestimmten Kalenderwoche berechnen
function monday_of_week($week,$year) {
	$firstmonday = first_calweek($year); //Montag der ersten Kalenderwoche im Jahr
	$first_day = date('d',$firstmonday-TIMEDIFF);
	$first_month = date('m',$firstmonday-TIMEDIFF);
	$first_year = date('Y',$firstmonday-TIMEDIFF);
	$daydiff = ($week-1)*7;
	$stamp = mktime(0,0,0,$first_month,$first_day+$daydiff,$first_year)+TIMEDIFF;
	return $stamp+TIMEDIFF;
}



//Termine zu einem bestimmten Tag auslesen
function calendar_events_from_day($thisdaystamp,$parse,$parseprefix='DAY.') {
	global $set,$apx,$db,$user;
	static $eventcache, $catdata;
	if ( !isset($eventcache) ) $eventcache = array();
	
	//Termin-Kategorien auslesen
	if ( !isset($catdata) ) {
		$catdata = array();
		if ( in_template(array($parseprefix.'EVENT.CATTITLE',$parseprefix.'EVENT.CATICON'),$parse) ) {
			$catdata = $db->fetch_index("SELECT * FROM ".PRE."_calendar_cat",'id');
		}
	}
	
	//Modefilter
	if ( $_REQUEST['mode']=='private' ) $modefilter = " AND a.private='1' AND a.userid='".$user->info['userid']."' ";
	else $modefilter = " AND a.private='0' ";
	
	//Sortby
	if ( $set['calendar']['sortby']==2 ) $sortby = " title ASC ";
	else $sortby = " startday DESC, starttime ASC ";
	
	//Termine auslesen
	$edata = $db->fetch("SELECT a.*,b.username FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE '".$thisdaystamp."' BETWEEN startday AND endday AND a.active!=0 ".section_filter(true,'a.secid')." ".$modefilter." ORDER BY ".$sortby);
	$eventdata = array();
	if ( count($edata) ) {
		foreach ( $edata AS $res ) {
			++$i;
			
			//Termin wurde schon verarbeitet => Übernehmen und fertig
			if ( isset($eventcache[$res['id']]) ) {
				$eventdata[$i] = $eventcache[$res['id']];
				continue;
			}
			
			//Kategorie-Info
			$catinfo = $catdata[$res['catid']];
			
			//Link zum Termin
			$link = mklink(
				'events.php?id='.$res['id'],
				'events,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			//Aufmacher
			$picture = $picture_popup = $picture_popuppath = '';
			if ( in_template(array($parseprefix.'EVENT.PICTURE',$parseprefix.'EVENT.PICTURE_POPUP',$parseprefix.'EVENT.PICTURE_POPUPPATH'),$parse) ) {
				list($picture,$picture_popup,$picture_popuppath)=calendar_pic($res['picture']);
			}
			
			//Start berechnen
			$startday = $starttime = $endday = $endtime = 0;
			if ( in_template(array($parseprefix.'EVENT.STARTDAY',$parseprefix.'EVENT.STARTTIME'),$parse) ) {
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
			if ( in_template(array($parseprefix.'EVENT.ENDDAY',$parseprefix.'EVENT.ENDTIME'),$parse) ) {
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
			if ( in_array($parseprefix.'EVENT.TEXT',$parse) ) {
				$eventtext = mediamanager_inline($res['text']);
			}
			
			//Tags
			if ( in_array($parseprefix.'EVENT.TAG',$parse) || in_array($parseprefix.'EVENT.TAG_IDS',$parse) || in_array($parseprefix.'EVENT.KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = calendar_tags($res['id']);
			}
			
			$event = array();
			$event['ID'] = $res['id'];
			$event['TITLE'] = $res['title'];
			$event['TEXT'] = $eventtext;
			$event['LINK'] = $link;
			$event['PRIORITY'] = $res['priority'];
			$event['RESTRICTED'] = $res['restricted'];
			$event['LOCATION'] = compatible_hsc($res['location']);
			$event['LOCATION_LINK'] = compatible_hsc($res['location_link']);
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
			
			$eventcache[$res['id']] = $event;
			$eventdata[$i] = $event;
		}
	}
	
	return $eventdata;
}



//Geburtstage an einem Tag auslesen
function calendar_birthdays_from_day($timestamp,$parse) {
	global $set,$apx,$db;
	$birthdata = array();
	
	$bdata = $db->fetch("SELECT userid,username,birthday FROM ".PRE."_user WHERE ( birthday='".date('d-m',$timestamp-TIMEDIFF)."' OR birthday LIKE '".date('d-m-',$timestamp-TIMEDIFF)."%' ) ORDER BY username ASC");
	if ( count($bdata) ) {
		foreach ( $bdata AS $res ) {
			++$i;
			$bd=explode('-',$res['birthday']);
			$birthdata[$i]['USERID']=$res['userid'];
			$birthdata[$i]['USERNAME']=$res['username'];
			if ( $bd[2] ) $birthdata[$i]['AGE'] = date('Y',$timestamp-TIMEDIFF)-$bd[2];
		}		
	}
	
	return $birthdata;
}



/////////////////////////////////////////////////////////////////////////////////////// TAGESANSICHT

if ( $_REQUEST['day'] && $_REQUEST['month'] && $_REQUEST['year'] ) {
	
	//Tag, Monat, Jahr bestimmen und Grenzen einhalten
	$day = (int)$_REQUEST['day'];
	$month = (int)$_REQUEST['month'];
	$year = (int)$_REQUEST['year'];
	if ( $year>$maxyear ) $year = $maxyear;
	if ( $year<$minyear ) $year = $minyear;
	if ( $month>12 ) $month = 12;
	if ( $month<1 ) $month = 1;
	$firstdaystamp = mktime(0,0,0,$month,1,$year)+TIMEDIFF;
	$monthdays = (int)date('t',$firstdaystamp-TIMEDIFF);
	if ( $day<1 ) $day = 1;
	if ( $day>$monthdays ) $day = $monthdays;
	
	
	//Verwendete Variablen auslesen
	$parse = $apx->tmpl->used_vars('calendar_day');
	
	
	//Headline und Titlebar
	$timestamp = mktime(0,0,0,$month,$day,$year)+TIMEDIFF;
	$thisdaystamp = (int)date('Ymd',$timestamp-TIMEDIFF);
	$daytitle = date('j. ',$timestamp-TIMEDIFF).getcalmonth(date('F',$timestamp-TIMEDIFF)).date(' Y',$timestamp-TIMEDIFF);
	headline($daytitle,mklink(
		'calendar.php?day='.$day.'&amp;month='.$month.'&amp;year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),
		'calendar,'.$_REQUEST['mode'].',day'.sprintf('%02d%02d',$day,$month).$year.'.html'
	));
	titlebar($apx->lang->get('HEADLINE').': '.$daytitle);
	
	
	//Link: Neuer Termin
	$addlink = '';
	if ( $user->info['userid'] ) {
		$addlink = mklink(
			'events.php?add=1&amp;day='.date('dmY',$timestamp-TIMEDIFF),
			'events,add.html?day='.date('dmY',$timestamp-TIMEDIFF)
		).iif($_REQUEST['mode']=='private','&amp;private=1');
	}
	
	$apx->tmpl->assign('LINK',$link);
	$apx->tmpl->assign('LINK_ADDEVENT',$addlink);
	$apx->tmpl->assign('TODAY',iif($thisdaystamp==$todaystamp,1,0));
	
	
	//Events
	$eventdata = calendar_events_from_day($thisdaystamp,$parse,'');
	$apx->tmpl->assign('EVENT',$eventdata);
	$apx->tmpl->assign('EVENT_COUNT',count($eventdata));
	
	//Geburtstage
	$birthdata = calendar_birthdays_from_day($timestamp,$parse);
	$apx->tmpl->assign('BIRTHDAY',$birthdata);
	$apx->tmpl->assign('BIRTHDAY_COUNT',count($birthdata));
	
	
	//Blättern
	$prevdaystamp = mktime(0,0,0,$month,$day-1,$year)+TIMEDIFF;
	$nextdaystamp = mktime(0,0,0,$month,$day+1,$year)+TIMEDIFF;
	$link_previous = $link_next = '';
	if ( !( $day==1 && $month==1 && $year==$minyear ) ) {
		$link_previous = mklink(
			'calendar.php?day='.date('j',$prevdaystamp).'&amp;month='.date('n',$prevdaystamp).'&amp;year='.date('Y',$prevdaystamp).iif($_REQUEST['mode']=='private','&amp;mode=private'),
			'calendar,'.$_REQUEST['mode'].',day'.date('dmY',$prevdaystamp).'.html'
		);
	}
	if ( !( $day==31 && $month==12 && $year==$maxyear ) ) {
		$link_next = mklink(
			'calendar.php?day='.date('j',$nextdaystamp).'&amp;month='.date('n',$nextdaystamp).'&amp;year='.date('Y',$nextdaystamp).iif($_REQUEST['mode']=='private','&amp;mode=private'),
			'calendar,'.$_REQUEST['mode'].',day'.date('dmY',$nextdaystamp).'.html'
		);
	}
	$apx->tmpl->assign('LINK_PREVIOUSDAY',$link_previous);
	$apx->tmpl->assign('LINK_NEXTDAY',$link_next);
	$apx->tmpl->assign('TIME_PREVIOUSDAY',$prevdaystamp);
	$apx->tmpl->assign('TIME_NEXTDAY',$nextdaystamp);
	$apx->tmpl->assign('TIME_THISDAY',$timestamp);
	
	
	//Wochennummer berechnen
	$dayofweek = (date('w',$timestamp-TIMEDIFF)+6)%7; //Montag = 0, Sonntag = 6
	$daystodo = -1*($dayofweek-3); //Tage bis Donnerstag
	$weektimestamp = mktime(0,0,0,$month,$day+$daystodo,$year)+TIMEDIFF;
	$weeknumber = date('W',$weektimestamp-TIMEDIFF);
	$weekyear = date('Y',$weektimestamp-TIMEDIFF);
	
	//Ansichtswechsel
	$link_today = mklink('calendar.php?today=1'.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',today.html');
	$link_week = mklink('calendar.php?week='.$weeknumber.'&amp;year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',week'.sprintf('%02d',$weeknumber).$year.'.html');
	$link_month = mklink('calendar.php?month='.$month.'&amp;year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',month'.sprintf('%02d',$month).$year.'.html');
	$link_year = mklink('calendar.php?year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',year'.$year.'.html');
	$apx->tmpl->assign('LINK_SHOWTODAY',$link_today);
	$apx->tmpl->assign('LINK_SHOWWEEK',$link_week);
	$apx->tmpl->assign('LINK_SHOWMONTH',$link_month);
	$apx->tmpl->assign('LINK_SHOWYEAR',$link_year);
	
	
	//Moduswechsel
	$apx->tmpl->assign('LINK_SHOWPUBLIC',mklink(
		'calendar.php?day='.$day.'&amp;month='.$month.'&amp;year='.$year,
		'calendar,public,day'.sprintf('%02d%02d',$day,$month).$year.'.html'
	));
	$apx->tmpl->assign('LINK_SHOWPRIVATE',mklink(
		'calendar.php?day='.$day.'&amp;month='.$month.'&amp;year='.$year.'&amp;mode=private',
		'calendar,private,day'.sprintf('%02d%02d',$day,$month).$year.'.html'
	));
	
	$apx->tmpl->assign('SELECTED_DAY',$day);
	$apx->tmpl->assign('SELECTED_MONTH',$month);
	$apx->tmpl->assign('SELECTED_YEAR',$year);
	$apx->tmpl->assign('MODE',$_REQUEST['mode']);
	$apx->tmpl->parse('calendar_day');
	require('lib/_end.php');
}



/////////////////////////////////////////////////////////////////////////////////////// WOCHENANSICHT

if ( $_REQUEST['week'] && $_REQUEST['year'] ) {
	
	//Woche und Jahr bestimmen und Grenzen einhalten
	$week = (int)$_REQUEST['week'];
	$year = (int)$_REQUEST['year'];
	if ( $year>$maxyear ) $year = $maxyear;
	if ( $year<$minyear ) $year = $minyear;
	if ( $week>53 ) $week = 53;
	if ( $week<1 ) $week = 1;
	
	
	//Verwendete Variablen auslesen
	$parse = $apx->tmpl->used_vars('calendar_week');
	
	
	//Starttag berechnen
	$firrstday = monday_of_week($week,$year);
	$firrstday_day = date('d',$firrstday-TIMEDIFF);
	$firrstday_month = date('m',$firrstday-TIMEDIFF);
	$firrstday_year = date('Y',$firrstday-TIMEDIFF);
	$time_thisweek = mktime(0,0,0,$firrstday_month,$firrstday_day+3,$firrstday_year)+TIMEDIFF;
	$time_prevweek = mktime(0,0,0,$firrstday_month,$firrstday_day+3-7,$firrstday_year)+TIMEDIFF;
	$time_nextweek = mktime(0,0,0,$firrstday_month,$firrstday_day+3+7,$firrstday_year)+TIMEDIFF;
	
	
	//Headline und Titlebar
	headline($week.'. '.$apx->lang->get('CALWEEK').' '.$year,mklink(
		'calendar.php?week='.date('W',$time_thisweek-TIMEDIFF).'&amp;year='.date('Y',$time_thisweek).iif($_REQUEST['mode']=='private','&amp;mode=private'),
		'calendar,'.$_REQUEST['mode'].',week'.sprintf('%02d',date('W',$time_thisweek-TIMEDIFF)).date('Y',$time_thisweek-TIMEDIFF).'.html'
	));
	titlebar($apx->lang->get('HEADLINE').': '.$week.'. '.$apx->lang->get('CALWEEK').' '.$year);
	
	
	//Tage der Woche durchlaufen
	$weekdata = array();
	$lastmonth = 0;
	for ( $i=0; $i<7; $i++ ) {
		$timestamp = mktime(0,0,0,$firrstday_month,$firrstday_day+$i,$firrstday_year)+TIMEDIFF;
		$thisdaystamp = (int)date('Ymd',$timestamp-TIMEDIFF);
		
		//Link zum Tag
		$link = mklink(
			'calendar.php?day='.date('j',$timestamp-TIMEDIFF).'&amp;month='.date('n',$timestamp-TIMEDIFF).'&amp;year='.date('Y',$timestamp-TIMEDIFF).iif($_REQUEST['mode']=='private','&amp;mode=private'),
			'calendar,'.$_REQUEST['mode'].',day'.date('dmY',$timestamp-TIMEDIFF).'.html'
		);
		
		//Link: Neuer Termin
		$addlink = '';
		if ( $user->info['userid'] ) {
			$addlink = mklink(
				'events.php?add=1&amp;day='.date('dmY',$timestamp-TIMEDIFF),
				'events,add.html?day='.date('dmY',$timestamp-TIMEDIFF)
			).iif($_REQUEST['mode']=='private','&amp;private=1');
		}
		
		$weekdata[$i]['TIME'] = $timestamp;
		$weekdata[$i]['LINK'] = $link;
		$weekdata[$i]['TODAY'] = iif($thisdaystamp==$todaystamp,1,0);
		$weekdata[$i]['NEWMONTH'] = iif($lastmonth!=date('n',$timestamp-TIMEDIFF),1,0);
		$weekdata[$i]['LINK_ADDEVENT'] = $addlink;
		
		//Geburtstage
		if ( in_array('DAY.BIRTHDAY',$parse) ) {
			$birthdata = calendar_birthdays_from_day($timestamp,$parse);
			$weekdata[$i]['BIRTHDAY'] = $birthdata;
			$weekdata[$i]['BIRTHDAY_COUNT'] = count($birthdata);
		}
		elseif ( in_array('DAY.BIRTHDAY_COUNT',$parse) ) {
			list($bcount)=$db->first("SELECT count(*) FROM ".PRE."_user WHERE ( birthday='".date('d-m',$timestamp-TIMEDIFF)."' OR birthday LIKE '".date('d-m-',$timestamp-TIMEDIFF)."%' )");
			$weekdata[$i]['BIRTHDAY_COUNT'] = $bcount;
		}
		
		//Termine
		if ( in_array('DAY.EVENT',$parse) ) {
			$eventdata = calendar_events_from_day($thisdaystamp,$parse);
			$weekdata[$i]['EVENT'] = $eventdata;
			$weekdata[$i]['EVENT_COUNT'] = count($eventdata);
		}
		elseif ( in_array('DAY.EVENT_COUNT',$parse) ) {
			list($ecount)=$db->first("SELECT count(*) FROM ".PRE."_calendar_events WHERE '".$thisdaystamp."' BETWEEN startday AND endday AND active!=0 ".section_filter()." ".$modefilter);
			$weekdata[$i]['EVENT_COUNT'] = $ecount;
		}
		
		$lastmonth = date('n',$timestamp-TIMEDIFF);
	}
	
	
	//Blättern
	$link_previous = $link_next = '';
	if ( date('Y',$time_prevweek-TIMEDIFF)>=$minyear ) {
		$link_previous = mklink(
			'calendar.php?week='.date('W',$time_prevweek-TIMEDIFF).'&amp;year='.date('Y',$time_prevweek).iif($_REQUEST['mode']=='private','&amp;mode=private'),
			'calendar,'.$_REQUEST['mode'].',week'.sprintf('%02d',date('W',$time_prevweek-TIMEDIFF)).date('Y',$time_prevweek-TIMEDIFF).'.html'
		);
	}
	if ( date('Y',$time_nextweek-TIMEDIFF)<=$maxyear ) {
		$link_next = mklink(
			'calendar.php?week='.date('W',$time_nextweek-TIMEDIFF).'&amp;year='.date('Y',$time_nextweek).iif($_REQUEST['mode']=='private','&amp;mode=private'),
			'calendar,'.$_REQUEST['mode'].',week'.sprintf('%02d',date('W',$time_nextweek-TIMEDIFF)).date('Y',$time_nextweek-TIMEDIFF).'.html'
		);
	}
	$apx->tmpl->assign('LINK_PREVIOUSWEEK',$link_previous);
	$apx->tmpl->assign('LINK_NEXTWEEK',$link_next);
	$apx->tmpl->assign('TIME_PREVIOUSWEEK',$time_prevweek);
	$apx->tmpl->assign('TIME_NEXTWEEK',$time_nextweek);
	$apx->tmpl->assign('TIME_THISWEEK',$time_thisweek);
	
	
	//Ansichtswechsel
	$link_today = mklink('calendar.php?today=1'.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',today.html');
	$link_week = mklink('calendar.php?week='.$week.'&amp;year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',week'.sprintf('%02d',$week).$_REQUEST['year'].'.html');
	$link_month = mklink('calendar.php?month='.date('n',$firrstday-TIMEDIFF).'&amp;year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',month'.date('m',$firrstday-TIMEDIFF).$year.'.html');
	$link_year = mklink('calendar.php?year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',year'.$year.'.html');
	$apx->tmpl->assign('LINK_SHOWTODAY',$link_today);
	$apx->tmpl->assign('LINK_SHOWWEEK',$link_week);
	$apx->tmpl->assign('LINK_SHOWMONTH',$link_month);
	$apx->tmpl->assign('LINK_SHOWYEAR',$link_year);
	
	
	//Moduswechsel
	$apx->tmpl->assign('LINK_SHOWPUBLIC',mklink(
		'calendar.php?week='.date('W',$time_thisweek-TIMEDIFF).'&amp;year='.date('Y',$time_thisweek),
		'calendar,public,week'.sprintf('%02d',date('W',$time_thisweek-TIMEDIFF)).date('Y',$time_thisweek-TIMEDIFF).'.html'
	));
	$apx->tmpl->assign('LINK_SHOWPRIVATE',mklink(
		'calendar.php?week='.date('W',$time_thisweek-TIMEDIFF).'&amp;year='.date('Y',$time_thisweek).'&amp;mode=private',
		'calendar,private,week'.sprintf('%02d',date('W',$time_thisweek-TIMEDIFF)).date('Y',$time_thisweek-TIMEDIFF).'.html'
	));
	
	
	$apx->tmpl->assign('SELECTED_WEEK',$week);
	$apx->tmpl->assign('SELECTED_YEAR',$year);
	$apx->tmpl->assign('MODE',$_REQUEST['mode']);
	$apx->tmpl->assign('DAY',$weekdata);
	$apx->tmpl->parse('calendar_week');
	require('lib/_end.php');
}



/////////////////////////////////////////////////////////////////////////////////////// JAHRESANSICHT

if ( $_REQUEST['year'] && !$_REQUEST['month'] ) {
	
	//Jahr bestimmen und Grenzen einhalten
	$year = (int)$_REQUEST['year'];
	if ( $year>$maxyear ) $year = $maxyear;
	if ( $year<$minyear ) $year = $minyear;
	
	
	//Verwendete Variablen auslesen
	$parse = $apx->tmpl->used_vars('calendar_year');
	
	
	//Headline und Titlebar
	$firstdaystamp = mktime(0,0,0,1,1,$year)+TIMEDIFF;
	headline($year,mklink(
		'calendar.php?year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),
		'calendar,'.$_REQUEST['mode'].',year'.$year.'.html'
	));
	titlebar($apx->lang->get('HEADLINE').': '.$year);
	
	
	//Monate des gewählten Jahres durchlaufen
	$yeardata = array();
	for ( $month=1; $month<=12; $month++ ) {
		++$mi;
		$timestamp = mktime(0,0,0,$month,1,$year)+TIMEDIFF;
		
		//Link zum Monat
		$link = mklink(
			'calendar.php?month='.date('n',$timestamp-TIMEDIFF).'&amp;year='.date('Y',$timestamp-TIMEDIFF).iif($_REQUEST['mode']=='private','&amp;mode=private'),
			'calendar,'.$_REQUEST['mode'].',month'.date('mY',$timestamp-TIMEDIFF).'.html'
		);
		
		$yeardata[$mi]['LINK'] = $link;
		$yeardata[$mi]['TIME'] = $timestamp;
		
		//Tage des vorherigen Monats auffüllen
		$monthdata = array();
		$startday = 1;
		$subday = (date('w',$timestamp-TIMEDIFF)+6)%7;
		
		//Tage des nächsten Monats auffüllen
		$monthdays = (int)date('t',$timestamp-TIMEDIFF);
		$timestamp = mktime(0,0,0,$month,$monthdays,$year)+TIMEDIFF;
		$weekday = date('w',$timestamp-TIMEDIFF);
		$adddays = (7-$weekday)%7;
		
		//Tage des gewählten Monats durchlaufen
		$firstweekofmonth = '';
		for ( $day=1-$subday; $day<=$monthdays+$adddays; $day++ ) {
			++$i;
			
			$timestamp = mktime(0,0,0,$month,$day,$year)+TIMEDIFF;
			$thisdaystamp = (int)date('Ymd',$timestamp-TIMEDIFF);
			
			//Link: Woche anzeigen
			if ( !$weeklink || date('w',$timestamp-TIMEDIFF)==1 ) {
				$weektimestamp = $timestamp;
				$weektimestamp -= (date('w',$timestamp-TIMEDIFF)-4)*24*3600;
				$weeknumber = date('W',$weektimestamp-TIMEDIFF);
				$weekyear = date('Y',$weektimestamp-TIMEDIFF);
				$weeklink = mklink(
					'calendar.php?week='.date('W',$timestamp-TIMEDIFF).'&amp;year='.date('Y',$timestamp-TIMEDIFF).iif($_REQUEST['mode']=='private','&amp;mode=private'),
					'calendar,'.$_REQUEST['mode'].',week'.sprintf('%02d',$weeknumber).$weekyear.'.html'
				);
				if ( !$firstweekofmonth ) $firstweekofmonth = $weeklink;
			}
			
			//Geburtstage
			if ( in_array('MONTH.DAY.BIRTHDAY_COUNT',$parse) ) {
				list($bcount)=$db->first("SELECT count(*) FROM ".PRE."_user WHERE ( birthday='".date('d-m',$timestamp-TIMEDIFF)."' OR birthday LIKE '".date('d-m-',$timestamp-TIMEDIFF)."%' )");
				$monthdata[$i]['BIRTHDAY_COUNT'] = $bcount;
			}
			
			//Termine
			if ( in_array('MONTH.DAY.EVENT_COUNT',$parse) ) {
				list($ecount)=$db->first("SELECT count(*) FROM ".PRE."_calendar_events WHERE '".$thisdaystamp."' BETWEEN startday AND endday AND active!=0 ".section_filter()." ".$modefilter);
				$monthdata[$i]['EVENT_COUNT'] = $ecount;
			}
			
			//Link zum Tag
			$link = mklink(
				'calendar.php?day='.date('j',$timestamp-TIMEDIFF).'&amp;month='.date('n',$timestamp-TIMEDIFF).'&amp;year='.date('Y',$timestamp-TIMEDIFF).iif($_REQUEST['mode']=='private','&amp;mode=private'),
				'calendar,'.$_REQUEST['mode'].',day'.date('dmY',$timestamp-TIMEDIFF).'.html'
			);
			
			$monthdata[$i]['TIME'] = $timestamp;
			$monthdata[$i]['LINK'] = $link;
			$monthdata[$i]['LINK_SHOWWEEK'] = $weeklink;
			$monthdata[$i]['TODAY'] = iif($thisdaystamp==$todaystamp,1,0);
			
			//Tag gehört nicht zum Monat => keine weiteren Informationen
			if ( $day<1 || $day>$monthdays ) continue;
			
			$monthdata[$i]['INMONTH'] = 1;
		}
		$yeardata[$mi]['DAY'] = $monthdata;
	}
	
	
	//Blättern
	$link_previous = $link_next = '';
	if ( $year>=$minyear ) {
		$link_previous = mklink(
			'calendar.php?year='.($year-1).iif($_REQUEST['mode']=='private','&amp;mode=private'),
			'calendar,'.$_REQUEST['mode'].',year'.($year-1).'.html'
		);
	}
	if ( $year<=$maxyear ) {
		$link_next = mklink(
			'calendar.php?year='.($year+1).iif($_REQUEST['mode']=='private','&amp;mode=private'),
			'calendar,'.$_REQUEST['mode'].',year'.($year+1).'.html'
		);
	}
	$apx->tmpl->assign('LINK_PREVIOUSYEAR',$link_previous);
	$apx->tmpl->assign('LINK_NEXTYEAR',$link_next);
	$apx->tmpl->assign('TIME_PREVIOUSYEAR',mktime(0,0,0,1,1,$year-1)+TIMEDIFF);
	$apx->tmpl->assign('TIME_NEXTYEAR',mktime(0,0,0,1,1,$year+1)+TIMEDIFF);
	$apx->tmpl->assign('TIME_THISYEAR',$firstdaystamp);
	
	
	//Ansichtswechsel
	$link_today = mklink('calendar.php?today=1'.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',today.html');
	$link_week = mklink('calendar.php?week=1&amp;year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',week01'.$year.'.html');
	$link_month = mklink('calendar.php?month=1&amp;year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',month01'.$year.'.html');
	$link_year = mklink('calendar.php?year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',year'.$year.'.html');
	$apx->tmpl->assign('LINK_SHOWTODAY',$link_today);
	$apx->tmpl->assign('LINK_SHOWWEEK',$link_week);
	$apx->tmpl->assign('LINK_SHOWMONTH',$link_month);
	$apx->tmpl->assign('LINK_SHOWYEAR',$link_year);
	
	
	//Moduswechsel
	$apx->tmpl->assign('LINK_SHOWPUBLIC',mklink(
		'calendar.php?year='.$year,
		'calendar,public,year'.$year.'.html'
	));
	$apx->tmpl->assign('LINK_SHOWPRIVATE',mklink(
		'calendar.php?year='.$year.'&amp;mode=private',
		'calendar,private,year'.$year.'.html'
	));
	
	
	$apx->tmpl->assign('SELECTED_YEAR',$year);
	$apx->tmpl->assign('MODE',$_REQUEST['mode']);
	$apx->tmpl->assign('MONTH',$yeardata);
	$apx->tmpl->parse('calendar_year');
	require('lib/_end.php');
}



/////////////////////////////////////////////////////////////////////////////////////// MONATSANSICHT

//Monat bestimmen und Grenzen einhalten
if ( !$_REQUEST['month'] ) $_REQUEST['month'] = date('m',time()-TIMEDIFF);
if ( !$_REQUEST['year'] ) $_REQUEST['year'] = date('Y',time()-TIMEDIFF);
$month = (int)$_REQUEST['month'];
$year = (int)$_REQUEST['year'];
if ( $year>$maxyear ) $year = $maxyear;
if ( $year<$minyear ) $year = $minyear;
if ( $month>12 ) $month = 12;
if ( $month<1 ) $month = 1;


//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('calendar_month');


//Headline und Titlebar
$firstdaystamp = mktime(0,0,0,$month,1,$year)+TIMEDIFF;
$monthtitle = getcalmonth(date('F',$firstdaystamp-TIMEDIFF)).' '.date('Y',$firstdaystamp-TIMEDIFF);
headline($monthtitle,mklink(
	'calendar.php?month='.$month.'&amp;year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),
	'calendar,'.$_REQUEST['mode'].',month'.sprintf('%02d',$month).$year.'.html'
));
titlebar($apx->lang->get('HEADLINE').': '.$monthtitle);


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
$firstweekofmonth = '';
for ( $day=1-$subday; $day<=$monthdays+$adddays; $day++ ) {
	++$i;
	
	$timestamp = mktime(0,0,0,$month,$day,$year)+TIMEDIFF;
	$thisdaystamp = (int)date('Ymd',$timestamp-TIMEDIFF);
	
	//Link: Woche anzeigen
	if ( !$weeklink || date('w',$timestamp-TIMEDIFF)==1 ) {
		$weektimestamp = mktime(0,0,0,$month,$day+3,$year)+TIMEDIFF;
		$weeknumber = date('W',$weektimestamp-TIMEDIFF);
		$weekyear = date('Y',$weektimestamp-TIMEDIFF);
		$weeklink = mklink(
			'calendar.php?week='.$weeknumber.'&amp;year='.$weekyear.iif($_REQUEST['mode']=='private','&amp;mode=private'),
			'calendar,'.$_REQUEST['mode'].',week'.sprintf('%02d',$weeknumber).$weekyear.'.html'
		);
		if ( !$firstweekofmonth ) $firstweekofmonth = $weeklink;
	}
	
	//Link zum Tag
	$link = mklink(
		'calendar.php?day='.date('j',$timestamp-TIMEDIFF).'&amp;month='.date('n',$timestamp-TIMEDIFF).'&amp;year='.date('Y',$timestamp-TIMEDIFF).iif($_REQUEST['mode']=='private','&amp;mode=private'),
		'calendar,'.$_REQUEST['mode'].',day'.date('dmY',$timestamp-TIMEDIFF).'.html'
	);
	
	//Link: Neuer Termin
	$addlink = '';
	if ( $user->info['userid'] ) {
		$addlink = mklink(
			'events.php?add=1&amp;day='.date('dmY',$timestamp-TIMEDIFF),
			'events,add.html?day='.date('dmY',$timestamp-TIMEDIFF)
		).iif($_REQUEST['mode']=='private','&amp;private=1');
	}
	
	$monthdata[$i]['TIME'] = $timestamp;
	$monthdata[$i]['LINK'] = $link;
	$monthdata[$i]['LINK_ADDEVENT'] = $addlink;
	$monthdata[$i]['LINK_SHOWWEEK'] = $weeklink;
	$monthdata[$i]['TODAY'] = iif($thisdaystamp==$todaystamp,1,0);
	
	//Tag gehört nicht zum Monat => keine weiteren Informationen
	if ( $day<1 || $day>$monthdays ) continue;
	
	/////////////////////////////////
	
	$monthdata[$i]['INMONTH'] = 1;
	
	//Geburtstage
	if ( in_array('DAY.BIRTHDAY',$parse) ) {
		$birthdata = calendar_birthdays_from_day($timestamp,$parse);
		$monthdata[$i]['BIRTHDAY'] = $birthdata;
		$monthdata[$i]['BIRTHDAY_COUNT'] = count($birthdata);
	}
	elseif ( in_array('DAY.BIRTHDAY_COUNT',$parse) ) {
		list($bcount)=$db->first("SELECT count(*) FROM ".PRE."_user WHERE ( birthday='".date('d-m',$timestamp-TIMEDIFF)."' OR birthday LIKE '".date('d-m-',$timestamp-TIMEDIFF)."%' )");
		$monthdata[$i]['BIRTHDAY_COUNT'] = $bcount;
	}
	
	//Termine
	if ( in_array('DAY.EVENT',$parse) ) {
		$eventdata = calendar_events_from_day($thisdaystamp,$parse);
		$monthdata[$i]['EVENT'] = $eventdata;
		$monthdata[$i]['EVENT_COUNT'] = count($eventdata);
	}
	elseif ( in_array('DAY.EVENT_COUNT',$parse) ) {
		list($ecount)=$db->first("SELECT count(*) FROM ".PRE."_calendar_events WHERE '".$thisdaystamp."' BETWEEN startday AND endday AND active!=0 ".section_filter()." ".$modefilter);
		$monthdata[$i]['EVENT_COUNT'] = $ecount;
	}
	
}


//Blättern
$link_previous = $link_next = '';
if ( !( $month==1 && $year==$minyear ) ) {
	if ( $month==1 ) {
		$prevmonth = 12;
		$prevyear = $year-1;
	}
	else {
		$prevmonth = $month-1;
		$prevyear = $year;
	}
	$link_previous = mklink(
		'calendar.php?month='.$prevmonth.'&amp;year='.$prevyear.iif($_REQUEST['mode']=='private','&amp;mode=private'),
		'calendar,'.$_REQUEST['mode'].',month'.sprintf('%02d',$prevmonth).$prevyear.'.html'
	);
}
if ( !( $month==12 && $year==$maxyear ) ) {
	if ( $month==12 ) {
		$nextmonth = 1;
		$nextyear = $year+1;
	}
	else {
		$nextmonth = $month+1;
		$nextyear = $year;
	}
	$link_next = mklink(
		'calendar.php?month='.$nextmonth.'&amp;year='.$nextyear.iif($_REQUEST['mode']=='private','&amp;mode=private'),
		'calendar,'.$_REQUEST['mode'].',month'.sprintf('%02d',$nextmonth).$nextyear.'.html'
	);
}
$apx->tmpl->assign('LINK_PREVIOUSMONTH',$link_previous);
$apx->tmpl->assign('LINK_NEXTMONTH',$link_next);
$apx->tmpl->assign('TIME_PREVIOUSMONTH',mktime(0,0,0,$month-1,1,$year)+TIMEDIFF);
$apx->tmpl->assign('TIME_NEXTMONTH',mktime(0,0,0,$month+1,1,$year)+TIMEDIFF);
$apx->tmpl->assign('TIME_THISMONTH',$firstdaystamp);


//Ansichtswechsel
$link_today = mklink('calendar.php?today=1'.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',today.html');
$link_week = $firstweekofmonth;
$link_month = mklink('calendar.php?month='.$month.'&amp;year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',month'.sprintf('%02d',$month).$year.'.html');
$link_year = mklink('calendar.php?year='.$year.iif($_REQUEST['mode']=='private','&amp;mode=private'),'calendar,'.$_REQUEST['mode'].',year'.$year.'.html');
$apx->tmpl->assign('LINK_SHOWTODAY',$link_today);
$apx->tmpl->assign('LINK_SHOWWEEK',$link_week);
$apx->tmpl->assign('LINK_SHOWMONTH',$link_month);
$apx->tmpl->assign('LINK_SHOWYEAR',$link_year);


//Moduswechsel
$apx->tmpl->assign('LINK_SHOWPUBLIC',mklink(
	'calendar.php?month='.$month.'&amp;year='.$year,
	'calendar,public,month'.sprintf('%02d',$month).$year.'.html'
));
$apx->tmpl->assign('LINK_SHOWPRIVATE',mklink(
	'calendar.php?month='.$month.'&amp;year='.$year.'&amp;mode=private',
	'calendar,private,month'.sprintf('%02d',$month).$year.'.html'
));


$apx->tmpl->assign('SELECTED_MONTH',$month);
$apx->tmpl->assign('SELECTED_YEAR',$year);
$apx->tmpl->assign('MODE',$_REQUEST['mode']);
$apx->tmpl->assign('DAY',$monthdata);
$apx->tmpl->parse('calendar_month');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>