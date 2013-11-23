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


define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once(BASEDIR.getmodulepath('calendar').'functions.php');

$apx->module('calendar');
$apx->lang->drop('global');
$apx->lang->drop('events');

//Eingaben parsen
$_REQUEST['id'] = (int)$_REQUEST['id'];
$_REQUEST['edit'] = (int)$_REQUEST['edit'];
$_REQUEST['del'] = (int)$_REQUEST['del'];

//Modus
if ( !in_array($_REQUEST['mode'],array('public','private')) || !$user->info['userid'] ) $_REQUEST['mode'] = 'public';
if ( $_REQUEST['mode']=='private' ) $modefilter = " AND a.private='1' AND a.userid='".$user->info['userid']."' ";
else $modefilter = " AND a.private='0' ";

//Headline
$eventslink = mklink('events.php','events.html');
if ( $_REQUEST['mode']=='private' ) $eventslink = mklink('events.php?mode=private','events,private.html');

//Beachte UNIX-Timestamp!
$minyear = 1980;
$maxyear = 2030;



////////////////////////////////////////////////////////////////////////////////////// TERMIN HINZUFÜGEN

if ( $_REQUEST['add'] ) {
	if ( !$user->info['userid'] ) require('lib/_end.php');
	$apx->lang->drop('manageevents');
	headline($apx->lang->get('ADDEVENT'));
	titlebar($apx->lang->get('ADDEVENT'));
	
	//Termin eintragen
	if ( $_POST['send'] ) {
		if ( !$_POST['title'] || !$_POST['text'] || !$_POST['start_day'] || !$_POST['start_month'] || !$_POST['start_year'] ) message('back');
		else {
			$_POST['userid']=$user->info['userid'];
			$_POST['addtime']=time();
			$_POST['active'] = time();
			$_POST['private'] = 1;
			$_POST['secid'] = 'all';
			$_POST['startday']=calendar_generate_stamp($_POST['start_day'],$_POST['start_month'],$_POST['start_year']);
			
			//Startzeit
			$_POST['starttime']=-1;
			if ( $_POST['start_hour']!=='' && $_POST['start_minute']!=='' ) {
				$_POST['starttime']=sprintf('%02d%02d',$_POST['start_hour'],$_POST['start_minute']);
			}
			
			//Termin Ende
			$_POST['endday']=0;
			if ( $_POST['end_day']!=='' && $_POST['end_month']!=='' && $_POST['end_year']!=='' ) {
				$_POST['endday']=calendar_generate_stamp($_POST['end_day'],$_POST['end_month'],$_POST['end_year']);
				$_POST['endtime'] = -1;
				if ( $_POST['end_hour']!=='' && $_POST['end_minute']!=='' ) {
					$_POST['endtime']=sprintf('%02d%02d',$_POST['end_hour'],$_POST['end_minute']);
				}
			}
			else {
				$_POST['endday'] = $_POST['startday'];
				$_POST['endtime'] = -1;
			}
			
			$db->dinsert(PRE.'_calendar_events','secid,userid,title,text,location,location_link,priority,addtime,startday,starttime,endday,endtime,active,private');
			$nid = $db->insert_id();
			
			$goto = mklink(
				'events.php?id='.$nid,
				'events,id'.$nid.urlformat($_POST['title']).'.html'
			);
			
			message($apx->lang->get('MSG_OK_ADD'),$goto);
		}
	}
	
	//Formular zeigen
	else {
		
		$postto = mklink(
			'events.php?add=1',
			'events,add.html'
		);
		
		//Starttag
		$start_day = $start_month = $start_year = 0;
		if ( $_REQUEST['day'] ) {
			$_REQUEST['day'] = sprintf('%08d',$_REQUEST['day']);
			$start_day = (int)substr($_REQUEST['day'],0,2);
			$start_month = (int)substr($_REQUEST['day'],2,2);
			$start_year = (int)substr($_REQUEST['day'],4,4);
		}
		
		$apx->tmpl->assign('START_DAY',$start_day);
		$apx->tmpl->assign('START_MONTH',$start_month);
		$apx->tmpl->assign('START_YEAR',$start_year);
		$apx->tmpl->assign('POSTTO',$postto);
		$apx->tmpl->parse('event_add');
	}
	
	require('lib/_end.php');
}



////////////////////////////////////////////////////////////////////////////////////// TERMIN BEARBEITEN

if ( $_REQUEST['edit'] ) {
	if ( !$_REQUEST['edit'] ) die('missing id!');
	if ( !$user->info['userid'] ) require('lib/_end.php');
	$apx->lang->drop('manageevents');
	headline($apx->lang->get('EDITEVENT'));
	titlebar($apx->lang->get('EDITEVENT'));
	
	//Termin aktualisieren
	if ( $_POST['send'] ) {
		if ( !$_POST['title'] || !$_POST['text'] || !$_POST['start_day'] || !$_POST['start_month'] || !$_POST['start_year'] ) message('back');
		else {
			$_POST['startday']=calendar_generate_stamp($_POST['start_day'],$_POST['start_month'],$_POST['start_year']);
			
			//Startzeit
			$_POST['starttime']=-1;
			if ( $_POST['start_hour']!=='' && $_POST['start_minute']!=='' ) {
				$_POST['starttime']=sprintf('%02d%02d',$_POST['start_hour'],$_POST['start_minute']);
			}
			
			//Termin Ende
			$_POST['endday']=0;
			if ( $_POST['end_day']!=='' && $_POST['end_month']!=='' && $_POST['end_year']!=='' ) {
				$_POST['endday']=calendar_generate_stamp($_POST['end_day'],$_POST['end_month'],$_POST['end_year']);
				$_POST['endtime'] = -1;
				if ( $_POST['end_hour']!=='' && $_POST['end_minute']!=='' ) {
					$_POST['endtime']=sprintf('%02d%02d',$_POST['end_hour'],$_POST['end_minute']);
				}
			}
			else {
				$_POST['endday'] = $_POST['startday'];
				$_POST['endtime'] = -1;
			}
			
			$db->dupdate(PRE.'_calendar_events','title,text,location,location_link,priority,startday,starttime,endday,endtime',"WHERE id='".$_REQUEST['edit']."' AND userid='".$user->info['userid']."' AND private='1' LIMIT 1");
			
			$goto = mklink(
				'events.php?id='.$_REQUEST['edit'],
				'events,id'.$_REQUEST['edit'].urlformat($_POST['title']).'.html'
			);
			
			message($apx->lang->get('MSG_OK_EDIT'),$goto);
		}
	}
	
	//Formular zeigen
	else {
		
		$postto = mklink(
			'events.php?edit='.$_REQUEST['edit'],
			'events,edit'.$_REQUEST['edit'].'.html'
		);
		
		$res = $db->first("SELECT id,title,text,location,location_link,priority,startday,starttime,endday,endtime FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['edit']."' AND userid='".$user->info['userid']."' AND private='1' LIMIT 1");
		foreach ( $res AS $key => $value ) {
			$_POST[$key] = $value;
		}
		
		//Start
		$start = calendar_explode_stamp($res['startday']);
		$_POST['start_day'] = $start['day'];
		$_POST['start_month'] = $start['month'];
		$_POST['start_year'] = $start['year'];
		if ( $res['starttime']!=-1 ) {
			$starttime = sprintf('%04d',$res['starttime']);
			$_POST['start_hour'] = substr($starttime,0,2);
			$_POST['start_minute'] = substr($starttime,2,2);
		}
		
		//Ende
		if ( $res['endday']!=$res['startday'] || $res['endtime']!=-1 ) {
			$end = calendar_explode_stamp($res['endday']);
			$_POST['end_day'] = $end['day'];
			$_POST['end_month'] = $end['month'];
			$_POST['end_year'] = $end['year'];
			if ( $res['endtime']!=-1 ) {
				$endtime = sprintf('%04d',$res['endtime']);
				$_POST['end_hour'] = substr($endtime,0,2);
				$_POST['end_minute'] = substr($endtime,2,2);
			}
		}
		
		$apx->tmpl->assign('POSTTO',$postto);
		$apx->tmpl->assign('EDIT',$_REQUEST['edit']);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('LOCATION',compatible_hsc($_POST['location']));
		$apx->tmpl->assign('LOCATION_LINK',compatible_hsc($_POST['location_link']));
		$apx->tmpl->assign('START_DAY',(int)$_POST['start_day']);
		$apx->tmpl->assign('START_MONTH',(int)$_POST['start_month']);
		$apx->tmpl->assign('START_YEAR',(int)$_POST['start_year']);
		$apx->tmpl->assign('START_HOUR',$_POST['start_hour']);
		$apx->tmpl->assign('START_MINUTE',$_POST['start_minute']);
		$apx->tmpl->assign('END_DAY',(int)$_POST['end_day']);
		$apx->tmpl->assign('END_MONTH',(int)$_POST['end_month']);
		$apx->tmpl->assign('END_YEAR',(int)$_POST['end_year']);
		$apx->tmpl->assign('END_HOUR',$_POST['end_hour']);
		$apx->tmpl->assign('END_MINUTE',$_POST['end_minute']);
		$apx->tmpl->assign('PRIORITY',(int)$_POST['priority']);
		$apx->tmpl->assign('ID',$_REQUEST['edit']);
		
		$apx->tmpl->parse('event_edit');
	}
	
	require('lib/_end.php');
}



////////////////////////////////////////////////////////////////////////////////////// TERMIN LÖSCHEN

if ( $_REQUEST['del'] ) {
	if ( !$_REQUEST['del'] ) die('missing id!');
	if ( !$user->info['userid'] ) require('lib/_end.php');
	$apx->lang->drop('manageevents');
	
	//Termin eintragen
	if ( $_POST['send'] ) {
		$db->query("DELETE FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['del']."' AND userid='".$user->info['userid']."' AND private='1' LIMIT 1");
		$goto = mklink('events.php?mode=private','events,private,day'.date('dmY',time()-TIMEDIFF).'.html');
		message($apx->lang->get('MSG_OK_DEL'),$goto);
	}
	
	//Formular zeigen
	else {
		tmessage('delevent',array('ID'=>$_REQUEST['del']));
	}
	
	require('lib/_end.php');
}



////////////////////////////////////////////////////////////////////////////////////// SUCHE

if ( $_REQUEST['search'] ) {
	$apx->lang->drop('search');
	headline($apx->lang->get('HEADLINE_SEARCH'));
	titlebar($apx->lang->get('HEADLINE_SEARCH'));
	
	//Suche durchführen
	if ( $_SERVER['REQUEST_METHOD']=='POST' || $_REQUEST['tag'] ) {
		if ( 
			!$_POST['item'] && !$_POST['location'] && 
			( ( $_POST['start_day'] || $_POST['start_month'] || $_POST['start_year'] ) && ( !$_POST['start_day'] || !$_POST['start_month'] || !$_POST['start_year'] ) ) ||
			( ( $_POST['end_day'] || $_POST['end_month'] || $_POST['end_year'] ) && ( !$_POST['end_day'] || !$_POST['end_month'] || !$_POST['end_year'] ) )
		) message($apx->lang->get('CORE_BACK'));
		else {
			$where = '';
			$_POST['tag'] = $_REQUEST['tag'];
			
			//Stichwortsuche
			if ( $_POST['item'] ) {
				$items = explode(' ',$_POST['item']);
				$items = array_map('trim',$items);
				$tagmatches = calendar_match_tags($items);
				$itemsearchfields = array(
					'title',
					'text'
				);
				foreach ( $items AS $item ) {
					$itemsearch .= ' AND ( ';
					$tagmatch = array_shift($tagmatches);
					if ( $tagmatch ) {
						$elementsearch = " id IN (".implode(',', $tagmatch).") ";
					}
					else {
						$elementsearch = '';
					}
					foreach ( $itemsearchfields AS $fieldname ) {
						if ( $elementsearch ) $elementsearch .= ' OR ';
						$elementsearch .= ' '.$fieldname." LIKE '%".addslashes_like($item)."%' ";
					}
					$itemsearch .= $elementsearch.' ) ';
				}
				$where .= $itemsearch;
			}
			
			//Ortssuche
			if ( $_POST['location'] ) {
				$items = explode(' ',$_POST['location']);
				$items = array_map('trim',$items);
				$itemsearchfields = array(
					'location',
					'location_link'
				);
				foreach ( $items AS $item ) {
					$itemsearch .= ' AND ( ';
					$elementsearch = '';
					foreach ( $itemsearchfields AS $fieldname ) {
						if ( $elementsearch ) $elementsearch .= ' OR ';
						$elementsearch .= ' '.$fieldname." LIKE '%".addslashes_like($item)."%' ";
					}
					$itemsearch .= $elementsearch.' ) ';
				}
				$where .= $itemsearch;
			}
			
			//Nach Tag suchen
			if ( $_REQUEST['tag'] ) {
				$tagid = getTagId($_REQUEST['tag']);
				if ( $tagid ) {
					$data = $db->fetch("SELECT id FROM ".PRE."_calendar_tags WHERE tagid='".$tagid."'");
					$ids = get_ids($data, 'id');
					if ( $ids ) {
						$where.=' AND id IN ('.implode(',', $ids).') ';
					}
					else {
						$where.=' AND 0 ';
					}
				}
				else {
					$where.=' AND 0 ';
				}
			}
			
			//Kategorie
			if ( $_REQUEST['catid'] ) {
				$cattree=calendar_tree($_REQUEST['catid']);
				if ( count($cattree) ) {
					$where.=' AND catid IN ('.@implode(',',$cattree).')';
				}
			}
			
			//Zeitraum-Suche
			if ( $_POST['start_day'] ) {
				if ( !$_POST['end_day'] ) {
					$_POST['end_day'] = $_POST['start_day'];
					$_POST['end_month'] = $_POST['start_month'];
					$_POST['end_year'] = $_POST['start_year'];
				}
				$minstamp = sprintf('%04d%02d%02d', $_POST['start_year'], $_POST['start_month'], $_POST['start_day']);
				$maxstamp = sprintf('%04d%02d%02d', $_POST['end_year'], $_POST['end_month'], $_POST['end_day']);
				$where .= " AND '".$minstamp."'<=endday AND '".$maxstamp."'>=startday ";
			}
			
			$data = $db->fetch("SELECT id FROM ".PRE."_calendar_events WHERE active!=0 ".section_filter()." AND ( private='0' OR ( private='1' AND userid='".$user->info['userid']."' ) ) ".$where);
			$result = get_ids($data, 'id');
			
			//Kein Ergebnis
			if ( !count($result) ) {
				message($apx->lang->get('MSG_NORESULT'),'back');
				require('lib/_end.php');
			}
			
			//Suche speichern und weiterleiten
			$searchid = md5(uniqid('search').microtime());
			$db->query("INSERT INTO ".PRE."_search VALUES ('".addslashes($searchid)."','eventsearch','".addslashes(serialize($result))."','".addslashes(serialize($_POST))."','".time()."')");
			$redirect = str_replace('&amp;', '&', mklink(
				'events.php?search=1&searchid='.$searchid,
				'events,search.html?searchid='.$searchid
			));
			header("HTTP/1.1 301 Moved Permanently");
			header('location:'.$redirect);
			exit;
		}
		require('lib/_end.php');
	}
	
	//Suchergebnis
	if ( $_REQUEST['searchid'] ) {
		list($results,$options) = $db->first("SELECT results,options FROM ".PRE."_search WHERE object='eventsearch' AND searchid='".addslashes($_REQUEST['searchid'])."' ORDER BY time DESC");
		$results = unserialize($results);
		$_POST = unserialize($options);
		if ( !is_array($results) ) {
			filenotfound();
			require('lib/_end.php');
		}
		
		//Verwendete Variablen auslesen
		$parse = $apx->tmpl->used_vars('search');
		
		//Seitenzahlen
		list($count)=$db->first("SELECT count(userid) FROM ".PRE."_calendar_events WHERE active!=0 AND id IN (".implode(',', $results).")");
		$pagelink=mklink(
			'events.php?search=1&amp;searchid='.$_REQUEST['searchid'],
			'events,search.html?searchid='.$_REQUEST['searchid'].iif($_REQUEST['sortby'],'&amp;sortby='.$_REQUEST['sortby'])
		);
		pages($pagelink,$count,$set['calendar']['searchepp']);
		
		//Sortby
		if ( $set['calendar']['sortby']==2 ) $sortby = " title ASC ";
		else $sortby = " startday DESC, starttime ASC ";
		
		//Termine auslesen
		$data = $db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.active!=0 AND id IN (".implode(',', $results).") ORDER BY ".$sortby.getlimit($set['calendar']['searchepp']));
		
		//Kategorien auslesen
		$catdata = array();
		if ( in_template(array('EVENT.CATTITLE','EVENT.CATICON'),$parse) ) {
			$catdata = $db->fetch_index("SELECT * FROM ".PRE."_calendar_cat",'id');
		}
		
		//Termine auflisten
		$eventdata = array();
		foreach ( $data AS $res ) {
			
			//Kategorie-Info
			$catinfo = $catdata[$res['catid']];
			
			//Link zum Termin
			$link = mklink(
				'events.php?id='.$res['id'],
				'events,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			//Aufmacher
			$picture = $picture_popup = $picture_popuppath = '';
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
			
			//Tags
			if ( in_array('EVENT.TAG',$parse) || in_array('EVENT.TAG_IDS',$parse) || in_array('EVENT.KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = calendar_tags($res['id']);
			}
			
			$event = array();
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
			if ( $apx->is_module('comments') && $set['calendar']['coms'] && $res['allowcoms'] && !$res['private'] ) {
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
			
			$eventdata[] = $event;
		}
		$apx->tmpl->assign('EVENT', $eventdata);
	}
	
	//Formular erzeugen
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$tree = new RecursiveTree(PRE.'_calendar_cat', 'id');
	$catdata = array();
	$data = $tree->getTree(array('title'));
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$catdata[$i]['ID']=$res['id'];
			$catdata[$i]['TITLE']=$res['title'];
			$catdata[$i]['LEVEL']=$res['level'];
			$catdata[$i]['SELECTED']=$res['id']==$_POST['catid'];
		}
	}
	$apx->tmpl->assign('CATEGORY',$catdata);
	$apx->tmpl->assign('TAG',compatible_hsc($_POST['tag']));
	$apx->tmpl->assign('ITEM',compatible_hsc($_POST['item']));
	$apx->tmpl->assign('LOCATION',compatible_hsc($_POST['location']));
	$apx->tmpl->assign('START_DAY',intval($_POST['start_day']));
	$apx->tmpl->assign('START_MONTH',intval($_POST['start_month']));
	$apx->tmpl->assign('START_YEAR',intval($_POST['start_year']));
	$apx->tmpl->assign('END_DAY',intval($_POST['end_day']));
	$apx->tmpl->assign('END_MONTH',intval($_POST['end_month']));
	$apx->tmpl->assign('END_YEAR',intval($_POST['end_year']));
	$postto = mklink(
		'events.php?search=1',
		'events,search.html'
	);
	$apx->tmpl->assign('POSTTO',$postto);
	$apx->tmpl->parse('search');
	require('lib/_end.php');
}



////////////////////////////////////////////////////////////////////////////////////// NUR KOMMENTARE

if ( $_REQUEST['id'] && $_REQUEST['comments'] ) {
	
	//Eventinfos auslesen
	$res=$db->first("SELECT title FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' AND active!=0 AND private='0' ".section_filter()." LIMIT 1");
	titlebar($apx->lang->get('HEADLINE').': '.$res['title']);
	calendar_showcomments($_REQUEST['id']);
	require('lib/_end.php');
}



////////////////////////////////////////////////////////////////////////////////////// TEILNAHMEVERMERK

if ( $_REQUEST['id'] && $_REQUEST['participate'] && $user->info['userid'] ) {
	$apx->lang->drop('participate');
	
	//Eventinfos auslesen
	$res=$db->first("SELECT id,title,allownote FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' AND active!=0 AND private='0' ".section_filter()." LIMIT 1");
	if ( !$res['id'] || !$res['allownote'] ) require('lib/_end.php');
	
	$link = mklink(
		'events.php?id='.$res['id'],
		'events,id'.$res['id'].urlformat($res['title']).'.html'
	);
	
	if ( $_REQUEST['participate']=='add' ) {
		$db->query("INSERT INTO ".PRE."_calendar_parts VALUES ('".$_REQUEST['id']."','".$user->info['userid']."')");
		message($apx->lang->get('MSG_ADD_OK'),$link);
	}
	else {
		$db->query("DELETE FROM ".PRE."_calendar_parts WHERE eventid='".$_REQUEST['id']."' AND userid='".$user->info['userid']."'");	
		message($apx->lang->get('MSG_REMOVE_OK'),$link);
	}
	
	require('lib/_end.php');
}



////////////////////////////////////////////////////////////////////////////////////// DETAILANSICHT

headline($apx->lang->get('HEADLINE'),$eventslink);
titlebar($apx->lang->get('HEADLINE'));

if ( $_REQUEST['id'] ) {
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars('event_detail');
	
	//Daten auslesen
	$simplemodefilter = " AND ( ( a.private='1' AND a.userid='".$user->info['userid']."' ) OR a.private='0' ) ";
	$res=$db->first("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE id='".$_REQUEST['id']."' ".iif(!$user->is_team_member()," AND a.active!=0 ")." ".section_filter(true,'a.secid')." ".$simplemodefilter." LIMIT 1");
	if ( !$res['id'] ) filenotfound();
	
	//Altersabfrage
	if ( $res['restricted'] ) {
		checkage();
	}
	
	//Counter
	$simplemodefilter = " AND ( ( private='1' AND userid='".$user->info['userid']."' ) OR private='0' ) ";
	$db->query("UPDATE ".PRE."_calendar_events SET hits=hits+1 WHERE id='".$_REQUEST['id']."' AND active!=0 ".section_filter()." ".$simplemodefilter);
	
	//Headline + Titlebar
	titlebar($apx->lang->get('HEADLINE').': '.$res['title']);
	
	//Kategorie-Info
	$catinfo = array();
	if ( in_array('CATTITLE',$parse) || in_array('CATICON',$parse) || in_array('CATLINK',$parse) ) {
		$catinfo = $db->first("SELECT * FROM ".PRE."_calendar_cat WHERE id='".$res['catid']."' LIMIT 1");
	}
	
	//Link zum Termin
	$link = mklink(
		'events.php?id='.$res['id'],
		'events,id'.$res['id'].urlformat($res['title']).'.html'
	);
	
	//Aufmacher
	$picture = $picture_popup = $picture_popuppath = '';
	if ( in_template(array('PICTURE','PICTURE_POPUP','PICTURE_POPUPPATH'),$parse) ) {
		list($picture,$picture_popup,$picture_popuppath)=calendar_pic($res['picture']);
	}
	
	//Start berechnen
	$startday = $starttime = $endday = $endtime = 0;
	if ( in_template(array('STARTDAY','STARTTIME'),$parse) ) {
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
	if ( in_template(array('ENDDAY','ENDTIME'),$parse) ) {
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
	
	//Teilnahme
	$takepart = false;
	if ( in_template(array('LINK_PART_ADD','LINK_PART_REMOVE'),$parse) && $user->info['userid'] && floatval($res['endday'].sprintf('%04d',iif($res['endtime']!=-1,$res['endtime'],9999)))>=floatval(date('YmdHi',time()-TIMEDIFF)) ) {
		if ( $user->info['userid'] ) list($takepart) = $db->first("SELECT userid FROM ".PRE."_calendar_parts WHERE userid='".$user->info['userid']."' AND eventid='".$res['id']."' LIMIT 1");
		if ( $takepart ) {
			$apx->tmpl->assign('LINK_PART_REMOVE',mklink(
				'events.php?id='.$res['id'].'&amp;participate=remove',
				'events,id'.$res['id'].urlformat($res['title']).'.html?participate=remove'
			));
		}
		else {
			$apx->tmpl->assign('LINK_PART_ADD',mklink(
				'events.php?id='.$res['id'].'&amp;participate=add',
				'events,id'.$res['id'].urlformat($res['title']).'.html?participate=add'
			));
		}
	}
	if ( in_array('PARTICIPANT',$parse) ) {
		$data = $db->fetch("SELECT b.userid,b.username FROM ".PRE."_calendar_parts AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE eventid='".$res['id']."' ORDER BY b.username ASC");
		$partdata = array();
		if ( count($data) ) {
			foreach ( $data AS $part ) {
				++$i;
				$partdata[$i]['USERID'] = $part['userid'];
				$partdata[$i]['USERNAME'] = replace($part['username']);
			}
		}
		$apx->tmpl->assign('PARTICIPANT',$partdata);
		$apx->tmpl->assign('PARTICIPANT_COUNT',count($partdata));
	}
	
	//Verwaltung bei privaten Terminen
	if ( $res['private'] && $res['userid']==$user->info['userid'] ) {
		$apx->tmpl->assign('LINK_EDITEVENT',mklink('events.php?edit='.$res['id'],'events,edit'.$res['id'].'.html'));
		$apx->tmpl->assign('LINK_DELEVENT',mklink('events.php?del='.$res['id'],'events,del'.$res['id'].'.html'));
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
	$text = mediamanager_inline($res['text']);
	if ( $apx->is_module('glossar') ) $text = glossar_highlight($text);
	
	//Tags
	if ( in_array('TAG',$parse) || in_array('TAG_IDS',$parse) || in_array('KEYWORDS',$parse) ) {
		list($tagdata, $tagids, $keywords) = calendar_tags($res['id']);
	}
	
	$apx->tmpl->assign('ID',$res['id']);
	$apx->tmpl->assign('TITLE',$res['title']);
	$apx->tmpl->assign('TEXT',$text);
	$apx->tmpl->assign_static('META_DESCRIPTION',replace($res['meta_description']));
	$apx->tmpl->assign('LINK',$link);
	$apx->tmpl->assign('LOCATION',compatible_hsc($res['location']));
	$apx->tmpl->assign('LOCATION_LINK',compatible_hsc($res['location_link']));
	$apx->tmpl->assign('PRIORITY',$res['priority']);
	$apx->tmpl->assign('RESTRICTED',$res['restricted']);
	$apx->tmpl->assign('PRIVATE',$res['private']);
	$apx->tmpl->assign('HITS',number_format($res['hits']+1,0,'','.'));
	$apx->tmpl->assign('RELATED',calendar_links($res['links']));
	$apx->tmpl->assign('PICTURE',$picture);
	$apx->tmpl->assign('PICTURE_POPUP',$picture_popup);
	$apx->tmpl->assign('PICTURE_POPUPPATH',$picture_popuppath);
	$apx->tmpl->assign('STARTDAY',$startday);
	$apx->tmpl->assign('STARTTIME',$starttime);
	$apx->tmpl->assign('ENDDAY',$endday);
	$apx->tmpl->assign('ENDTIME',$endtime);
	$apx->tmpl->assign('USERID',$res['userid']);
	$apx->tmpl->assign('USERNAME',replace($username));
	$apx->tmpl->assign('EMAIL',replace($email));
	$apx->tmpl->assign('EMAIL_ENCRYPTED',replace(cryptMail($email)));
	$apx->tmpl->assign('CATID',$res['catid']);
	$apx->tmpl->assign('CATTITLE',$catinfo['title']);
	$apx->tmpl->assign('CATICON',$catinfo['icon']);
	$apx->tmpl->assign('DISPLAY_PART',$res['allownote']);
	
	//Tags
	$apx->tmpl->assign('TAG_IDS', $tagids);
	$apx->tmpl->assign('TAG', $tagdata);
	$apx->tmpl->assign('KEYWORDS', $keywords);
	
	//Galerie
	if ( $apx->is_module('gallery') && $res['galid'] && !$res['private'] ) {
		$galinfo=gallery_info($res['galid']);
		$gallink=mklink(
			'gallery.php?id='.$galinfo['id'],
			'gallery,list'.$galinfo['id'].',1'.urlformat($galinfo['title']).'.html'
		);
		$apx->tmpl->assign('GALLERY_ID',$galinfo['id']);
		$apx->tmpl->assign('GALLERY_TITLE',$galinfo['title']);
		$apx->tmpl->assign('GALLERY_LINK',$gallink);
	}
	
	//Kommentare
	if ( $apx->is_module('comments') && $set['calendar']['coms'] && $res['allowcoms'] && !$res['private'] ) {
		require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
		$coms=new comments('calendar',$res['id']);
		$coms->assign_comments($parse);
	}
	
	$apx->tmpl->parse('event_detail');
	require('lib/_end.php');
}



////////////////////////////////////////////////////////////////////////////////////// AKTUELLE TERMINE

//Tag, Monat, Jahr bestimmen und Grenzen einhalten
if ( !$_REQUEST['day'] ) $_REQUEST['day'] = date('d',time()-TIMEDIFF);
if ( !$_REQUEST['month'] ) $_REQUEST['month'] = date('m',time()-TIMEDIFF);
if ( !$_REQUEST['year'] ) $_REQUEST['year'] = date('Y',time()-TIMEDIFF);
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
$selectdaystamp = sprintf('%04d%02d%02d',$year,$month,$day);


//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('events');


//Tagesauswahl erzeugen
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
		'events.php?day='.date('j',$timestamp-TIMEDIFF).'&amp;month='.date('n',$timestamp-TIMEDIFF).'&amp;year='.date('Y',$timestamp-TIMEDIFF).iif($_REQUEST['mode']=='private','&amp;mode=private'),
		'events,'.$_REQUEST['mode'].',day'.date('dmY',$timestamp-TIMEDIFF).'.html'
	);
	
	$monthdata[$i]['TIME'] = $timestamp;
	$monthdata[$i]['LINK_SELECT'] = $link;
	$monthdata[$i]['SELECTED'] = iif($thisdaystamp==$selectdaystamp,1,0);
	
	//Tag gehört nicht zum Monat => keine weiteren Informationen
	if (  $cday>=1 && $cday<=$monthdays ) {
		$monthdata[$i]['INMONTH'] = 1;
		
		//Anzahl Termine
		if ( in_array('CALENDAR.EVENT_COUNT',$parse) ) {
			list($ecount)=$db->first("SELECT count(*) FROM ".PRE."_calendar_events AS a WHERE '".$thisdaystamp."' BETWEEN startday AND endday AND active!=0 ".section_filter()." ".$modefilter);
			$monthdata[$i]['EVENT_COUNT'] = $ecount;
		}
	}
}


//Grenzen bestimmen
$start = mktime(0,0,0,$month,$day,$year)+TIMEDIFF;
$end = mktime(0,0,0,$month,$day+$set['calendar']['eventdays']-1,$year)+TIMEDIFF;
$startstamp = date('Ymd',$start-TIMEDIFF);
$endstamp = date('Ymd',$end-TIMEDIFF);

//Kategorie-Filter
$catfilter = '';
if ( $_REQUEST['catid'] ) {
	$catids = calendar_tree($_REQUEST['catid']);
	$catfilter = " AND a.catid IN (".implode(',',$catids).") ";
}


//Kategorien auslesen
$catdata = array();
if ( in_template(array('DAY.EVENT.CATTITLE','DAY.EVENT.CATICON'),$parse) ) {
	$catdata = $db->fetch_index("SELECT * FROM ".PRE."_calendar_cat",'id');
}

//Tage durchlaufen
$eventcache = array();
for ( $addday=0; $addday<$set['calendar']['eventdays']; $addday++ ) {
	$timestamp = mktime(0,0,0,$month,$day+$addday,$year)+TIMEDIFF;
	$thisdaystamp = (int)date('Ymd',$timestamp-TIMEDIFF);
	$eventdata = array();
	
	//Sortby
	if ( $set['calendar']['sortby']==2 ) $sortby = " title ASC, priority ASC ";
	else $sortby = " startday DESC, starttime ASC, priority ASC ";
	
	//Termine auflisten
	$data = $db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE '".$thisdaystamp."' BETWEEN startday AND endday AND a.active!=0 ".section_filter(true,'a.secid')." ".$catfilter.$modefilter." ORDER BY ".$sortby);
	if ( count($data) ) {
		foreach ( $data AS $res ) {
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
			if ( in_template(array('DAY.EVENT.PICTURE','DAY.EVENT.PICTURE_POPUP','DAY.EVENT.PICTURE_POPUPPATH'),$parse) ) {
				list($picture,$picture_popup,$picture_popuppath)=calendar_pic($res['picture']);
			}
			
			//Start berechnen
			$startday = $starttime = $endday = $endtime = 0;
			if ( in_template(array('DAY.EVENT.STARTDAY','DAY.EVENT.STARTTIME'),$parse) ) {
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
			if ( in_template(array('DAY.EVENT.ENDDAY','DAY.EVENT.ENDTIME'),$parse) ) {
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
			if ( in_array('DAY.EVENT.TEXT',$parse) ) {
				$eventtext = mediamanager_inline($res['text']);
				if ( $apx->is_module('glossar') ) $eventtext = glossar_highlight($eventtext);
			}
			
			//Tags
			if ( in_array('DAY.EVENT.TAG',$parse) || in_array('DAY.EVENT.TAG_IDS',$parse) || in_array('DAY.EVENT.KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = calendar_tags($res['id']);
			}
			
			$event = array();
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
			if ( $apx->is_module('gallery') && $res['galid'] && !$res['private'] && in_template(array('DAY.EVENT.GALLERY_ID','DAY.EVENT.GALLERY_TITLE','DAY.EVENT.GALLERY_LINK'),$parse) ) {
				$galinfo=gallery_info($res['galid']);
				$event['GALLERY_ID']=$galinfo['id'];
				$event['GALLERY_TITLE']=$galinfo['title'];
				$event['GALLERY_LINK']=mklink(
					'gallery.php?id='.$galinfo['id'],
					'gallery,list'.$galinfo['id'].',1'.urlformat($galinfo['title']).'.html'
				);
			}
			
			//Kommentare
			if ( $apx->is_module('comments') && $set['calendar']['coms'] && $res['allowcoms'] && !$res['private'] ) {
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
				if ( in_template(array('DAY.EVENT.COMMENT_LAST_USERID','DAY.EVENT.COMMENT_LAST_NAME','DAY.EVENT.COMMENT_LAST_TIME'),$parse) ) {
					$event['COMMENT_LAST_USERID']=$coms->last_userid();
					$event['COMMENT_LAST_NAME']=$coms->last_name();
					$event['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
			
			$eventcache[$res['id']] = $event;
			$eventdata[$i] = $event;
		}
	}
	
	$daydata[$addday]['TIME'] = $timestamp;
	$daydata[$addday]['EVENT'] = $eventdata;
}


//Blättern
$time_prevweek = mktime(0,0,0,$month,$day-7,$year)+TIMEDIFF;
$time_nextweek = mktime(0,0,0,$month,$day+7,$year)+TIMEDIFF;
$time_prevday = mktime(0,0,0,$month,$day-1,$year)+TIMEDIFF;
$time_nextday = mktime(0,0,0,$month,$day+1,$year)+TIMEDIFF;

$link_previousweek = $link_nextweek = $link_previousday = $link_nextday = '';
if ( date('Y',$time_prevweek-TIMEDIFF)>=$minyear ) {
	$link_previousweek = mklink(
		'events.php?day='.date('j',$time_prevweek-TIMEDIFF).'&amp;month='.date('m',$time_prevweek-TIMEDIFF).'&amp;year='.date('Y',$time_prevweek-TIMEDIFF),
		'events,public,day'.date('dmY',$time_prevweek-TIMEDIFF).'.html'
	);
}
if ( date('Y',$time_nextweek-TIMEDIFF)<=$maxyear ) {
	$link_nextweek = mklink(
		'events.php?day='.date('j',$time_nextweek-TIMEDIFF).'&amp;month='.date('m',$time_nextweek-TIMEDIFF).'&amp;year='.date('Y',$time_nextweek-TIMEDIFF),
		'events,public,day'.date('dmY',$time_nextweek-TIMEDIFF).'.html'
	);
}
if ( date('Y',$time_prevday-TIMEDIFF)>=$minyear ) {
	$link_previousday = mklink(
		'events.php?day='.date('j',$time_prevday-TIMEDIFF).'&amp;month='.date('m',$time_prevday-TIMEDIFF).'&amp;year='.date('Y',$time_prevday-TIMEDIFF),
		'events,public,day'.date('dmY',$time_prevday-TIMEDIFF).'.html'
	);
}
if ( date('Y',$time_nextday-TIMEDIFF)<=$maxyear ) {
	$link_nextday = mklink(
		'events.php?day='.date('j',$time_nextday-TIMEDIFF).'&amp;month='.date('m',$time_nextday-TIMEDIFF).'&amp;year='.date('Y',$time_nextday-TIMEDIFF),
		'events,public,day'.date('dmY',$time_nextday-TIMEDIFF).'.html'
	);
}

$apx->tmpl->assign('LINK_PREVIOUSWEEK',$link_previousweek);
$apx->tmpl->assign('LINK_NEXTWEEK',$link_nextweek);
$apx->tmpl->assign('LINK_PREVIOUSDAY',$link_previousday);
$apx->tmpl->assign('LINK_NEXTDAY',$link_nextday);
$apx->tmpl->assign('TIME_PREVIOUSWEEK',$time_prevweek);
$apx->tmpl->assign('TIME_NEXTWEEK',$time_nextweek);
$apx->tmpl->assign('TIME_PREVIOUSDAY',$time_prevday);
$apx->tmpl->assign('TIME_NEXTDAY',$time_nextday);


//Moduswechsel
$apx->tmpl->assign('LINK_SHOWPUBLIC',mklink(
	'events.php?day='.$day.'&amp;month='.$month.'&amp;year='.$year,
	'events,public,day'.sprintf('%02d%02d%04d',$day,$month,$year).'.html'
));
$apx->tmpl->assign('LINK_SHOWPRIVATE',mklink(
	'events.php?day='.$day.'&amp;month='.$month.'&amp;year='.$year.'&amp;mode=private',
	'events,private,day'.sprintf('%02d%02d%04d',$day,$month,$year).'.html'
));


if ( $user->info['userid'] ) $apx->tmpl->assign('LINK_ADDEVENT',mklink('events.php?add=1','events,add.html'));
$apx->tmpl->assign('LINK_SEARCH',mklink('events.php?search=1','events,search.html'));
$apx->tmpl->assign('SELECTED_DAY',$day);
$apx->tmpl->assign('SELECTED_MONTH',$month);
$apx->tmpl->assign('SELECTED_YEAR',$year);
$apx->tmpl->assign('MODE',$_REQUEST['mode']);
$apx->tmpl->assign('CALENDAR',$monthdata);
$apx->tmpl->assign('DAY',$daydata);
$apx->tmpl->parse('events');



////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>