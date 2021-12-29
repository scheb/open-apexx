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


# KALENDER
# ========

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Funktionen laden
include(BASEDIR.getmodulepath('calendar').'admin_extend.php');


class action extends calendar_functions {

//Startup
function __construct() {
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$this->cat=new RecursiveTree(PRE.'_calendar_cat', 'id');
}



//***************************** Termine zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	$todaystamp = date('Ymd',time()-TIMEDIFF);
	
	
	//Suche durchführen
	if ( ( $_REQUEST['item'] && ( $_REQUEST['title'] || $_REQUEST['text'] ) ) || $_REQUEST['secid'] || $_REQUEST['catid'] || $_REQUEST['userid'] || ( $_REQUEST['start_day'] && $_REQUEST['start_month'] && $_REQUEST['start_year'] ) || ( $_REQUEST['end_day'] && $_REQUEST['end_month'] && $_REQUEST['end_year'] ) ) {
		$where = '';
		$_REQUEST['catid']=(int)$_REQUEST['catid'];
		$_REQUEST['secid']=(int)$_REQUEST['secid'];
		$_REQUEST['userid']=(int)$_REQUEST['userid'];
		$_REQUEST['start_day']=(int)$_REQUEST['start_day'];
		$_REQUEST['start_month']=(int)$_REQUEST['start_month'];
		$_REQUEST['start_year']=(int)$_REQUEST['start_year'];
		$_REQUEST['end_day']=(int)$_REQUEST['end_day'];
		$_REQUEST['end_month']=(int)$_REQUEST['end_month'];
		$_REQUEST['end_year']=(int)$_REQUEST['end_year'];
		if ( !( $_REQUEST['start_day'] && $_REQUEST['start_month'] && $_REQUEST['start_year'] ) ) {
			unset($_REQUEST['start_day'], $_REQUEST['start_month'], $_REQUEST['start_year']);
		}
		if ( !( $_REQUEST['end_day'] && $_REQUEST['end_month'] && $_REQUEST['end_year'] ) ) {
			unset($_REQUEST['end_day'], $_REQUEST['end_month'], $_REQUEST['end_year']);
		}
		
		//Suchbegriff
		if ( $_REQUEST['item'] ) {
			if ( $_REQUEST['title'] ) $sc[]="a.title LIKE '%".addslashes_like($_REQUEST['item'])."%'";
			if ( $_REQUEST['text'] ) $sc[]="a.text LIKE '%".addslashes_like($_REQUEST['item'])."%'";
			if ( is_array($sc) ) $where .= ' AND ( '.implode(' OR ',$sc).' )';
		}
		
		//Zeitraum
		if ( $_REQUEST['start_day'] && $_REQUEST['start_month'] && $_REQUEST['start_year'] && $_REQUEST['end_day'] && $_REQUEST['end_month'] && $_REQUEST['end_year'] ) {
			$startstamp = sprintf('%04d%02d%02d', $_REQUEST['start_year'], $_REQUEST['start_month'], $_REQUEST['start_day']);
			$endstamp = sprintf('%04d%02d%02d', $_REQUEST['end_year'], $_REQUEST['end_month'], $_REQUEST['end_day']);
			$where .= " AND '".$startstamp."'<=endday AND '".$endstamp."'>=startday ";
		}
		elseif ( $_REQUEST['start_day'] && $_REQUEST['start_month'] && $_REQUEST['start_year'] ) {
			$startstamp = sprintf('%04d%02d%02d', $_REQUEST['start_year'], $_REQUEST['start_month'], $_REQUEST['start_day']);
			$where .= " AND startday>=".$startstamp." ";
		}
		elseif ( $_REQUEST['end_day'] && $_REQUEST['end_month'] && $_REQUEST['end_year'] ) {
			$endstamp = sprintf('%04d%02d%02d', $_REQUEST['end_year'], $_REQUEST['end_month'], $_REQUEST['end_day']);
			$where .= " AND endday<=".$endstamp." ";
		}
		
		//Sektion
		if ( !$apx->session->get('section') && $_REQUEST['secid'] ) {
			$where.=" AND ( secid LIKE '%|".$_REQUEST['secid']."|%' OR secid='all' ) ";
		}
		
		//Kategorie
		if ( $_REQUEST['catid'] ) {
			if ( $set['gallery']['subcats'] ) {
				$tree = $this->cat->getChildrenIds($_REQUEST['catid']);
				$tree[] = $_REQUEST['catid'];
				if ( is_array($tree) ) $where.=" AND catid IN (".implode(',',$tree).") ";
			}
			else $where.=" AND catid='".$_REQUEST['catid']."' ";
		}
		
		//Benutzer
		if ( $_REQUEST['userid'] ) {
			$where.=" AND userid='".$_REQUEST['userid']."' ";
		}
		
		$data=$db->fetch("SELECT id FROM ".PRE."_calendar_events AS a WHERE 1 ".$where);
		$ids = get_ids($data, 'id');
		$ids[] = -1;
		$searchid = saveSearchResult('admin_calendar', $ids, array(
			'item' => $_REQUEST['item'],
			'title' => $_REQUEST['title'],
			'text' => $_REQUEST['text'],
			'catid' => $_REQUEST['catid'],
			'secid' => $_REQUEST['secid'],
			'userid' => $_REQUEST['userid'],
			'start_day' => $_REQUEST['start_day'],
			'start_month' => $_REQUEST['start_month'],
			'start_year' => $_REQUEST['start_year'],
			'end_day' => $_REQUEST['end_day'],
			'end_month' => $_REQUEST['end_month'],
			'end_year' => $_REQUEST['end_year']
		));
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: action.php?action=calendar.show&what='.$_REQUEST['what'].'&searchid='.$searchid);
		return;
	}
	
	
	//Voreinstellungen
	$_REQUEST['title']=1;
	$_REQUEST['text']=1;
	
	quicklink('calendar.add');
	
	$layerdef[]=array('LAYER_RECENT','action.php?action=calendar.show',!$_REQUEST['what']);
	$layerdef[]=array('LAYER_SEND','action.php?action=calendar.show&amp;what=send',$_REQUEST['what']=='send');
	$layerdef[]=array('LAYER_ARCHIVE','action.php?action=calendar.show&amp;what=archive',$_REQUEST['what']=='archive');
	
	//Layer Header ausgeben
	$html->layer_header($layerdef);
	
	$orderdef[0]='addtime';
	$orderdef['title']=array('a.title','ASC','COL_TITLE');
	$orderdef['cat']=array('catname','ASC','COL_CATEGORY');
	$orderdef['addtime']=array('a.addtime','DESC','SORT_ADDTIME');
	$orderdef['startday']=array('a.startday','ASC','SORT_STARTDAY');
	$orderdef['endday']=array('a.endday','ASC','SORT_ENDDAY');
	$orderdef['hits']=array('a.hits','DESC','COL_HITS');
	
	
	//Suchergebnis?
	$resultFilter = '';
	if ( $_REQUEST['searchid'] ) {
		$searchRes = getSearchResult('admin_calendar', $_REQUEST['searchid']);
		if ( $searchRes ) {
			list($resultIds, $resultMeta) = $searchRes;
			$_REQUEST['item'] = $resultMeta['item'];
			$_REQUEST['title'] = $resultMeta['title'];
			$_REQUEST['text'] = $resultMeta['text'];
			$_REQUEST['catid'] = $resultMeta['catid'];
			$_REQUEST['secid'] = $resultMeta['secid'];
			$_REQUEST['userid'] = $resultMeta['userid'];
			$_REQUEST['start_day'] = $resultMeta['start_day'];
			$_REQUEST['start_month'] = $resultMeta['start_month'];
			$_REQUEST['start_year'] = $resultMeta['start_year'];
			$_REQUEST['end_day'] = $resultMeta['end_day'];
			$_REQUEST['end_month'] = $resultMeta['end_month'];
			$_REQUEST['end_year'] = $resultMeta['end_year'];
			$resultFilter = " AND a.id IN (".implode(', ', $resultIds).")";
		}
		else {
			$_REQUEST['searchid'] = '';
		}
	}
	
	
	//Sektionen auflisten
	$seclist = '';
	if ( is_array($apx->sections) && count($apx->sections) ) {
		foreach ( $apx->sections AS $res ) {
			$seclist.='<option value="'.$res['id'].'"'.iif($_REQUEST['secid']==$res['id'],' selected="selected"').'>'.replace($res['title']).'</option>';
		}
	}
	
	//Kategorien
	$catlist='';
	if ( $set['calendar']['subcats'] ) $data = $this->cat->getTree(array('title'));
	else $data=$db->fetch("SELECT id,title FROM ".PRE."_calendar_cat ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',$res['level']-1);
			$catlist.='<option value="'.$res['id'].'"'.iif($_REQUEST['catid']==$res['id'],'selected="selected"').'>'.$space.replace($res['title']).'</option>';
		}
	}
	
	//Benutzer auflisten
	$userlist = '';
	$data=$db->fetch("SELECT b.userid,b.username FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING (userid) WHERE a.userid!=0 AND a.private='0' GROUP BY userid ORDER BY username ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) $userlist.='<option value="'.$res['userid'].'"'.iif($_REQUEST['userid']==$res['userid'],' selected="selected"').'>'.replace($res['username']).'</option>';
	}
	
	$apx->tmpl->assign('ITEM',compatible_hsc($_REQUEST['item']));
	$apx->tmpl->assign('START_DAY',$_REQUEST['start_day']);
	$apx->tmpl->assign('START_MONTH',$_REQUEST['start_month']);
	$apx->tmpl->assign('START_YEAR',$_REQUEST['start_year']);
	$apx->tmpl->assign('END_DAY',$_REQUEST['end_day']);
	$apx->tmpl->assign('END_MONTH',$_REQUEST['end_month']);
	$apx->tmpl->assign('END_YEAR',$_REQUEST['end_year']);
	$apx->tmpl->assign('SECLIST',$seclist);
	$apx->tmpl->assign('CATLIST',$catlist);
	$apx->tmpl->assign('USERLIST',$userlist);
	$apx->tmpl->assign('STITLE',(int)$_REQUEST['title']);
	$apx->tmpl->assign('STEXT',(int)$_REQUEST['text']);
	$apx->tmpl->assign('WHAT',$_REQUEST['what']);
	$apx->tmpl->assign('EXTENDED',$searchRes);
	$apx->tmpl->parse('search');
	
	
	//Filter
	$layerFilter = '';
	if ( $_REQUEST['what']=='archive' ) {
		$layerFilter = " AND a.endday<'".$todaystamp."' ";
	}
	elseif ( $_REQUEST['what']=='send' ) {
		$layerFilter = " AND a.send_ip!='' ";
	}
	else {
		$layerFilter = " AND a.endday>='".$todaystamp."' ";
	}
	
	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_calendar_events AS a WHERE private='0' ".$resultFilter.$layerFilter.section_filter(true, 'secid'));
	pages('action.php?action=calendar.show&amp;what='.$_REQUEST['what'].'&amp;sortby='.$_REQUEST['sortby'],$count);
	$data=$db->fetch("SELECT a.id,a.secid,a.send_username,a.title,a.addtime,a.startday,a.endday,a.hits,a.active,a.allowcoms,b.username,c.title AS catname FROM ".PRE."_calendar_events AS a LEFT JOIN ".PRE."_user AS b USING(userid) LEFT JOIN ".PRE."_calendar_cat AS c ON a.catid=c.id WHERE a.private=0 ".$resultFilter.$layerFilter.section_filter(true, 'a.secid')." ".getorder($orderdef).getlimit());
	$this->show_print($data);
	orderstr($orderdef,'action.php?action=calendar.show&amp;what='.$_REQUEST['what']);
	save_index($_SERVER['REQUEST_URI']);
	
	
	//Layer-Footer ausgeben
	$html->layer_footer();
}



//Termine auflisten
function show_print($data) {
	global $set,$db,$apx,$html;
	
	$col[]=array('',1,'align="center"');
	$col[]=array($apx->lang->get('COL_TITLE').' / '.$apx->lang->get('COL_USER'),55,'class="title"');
	$col[]=array('COL_CATEGORY',20,'align="center"');
	$col[]=array('COL_STARTEND',15,'align="center"');
	$col[]=array('COL_HITS',10,'align="center"');
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['active'] ) $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			
			$tmp=unserialize_section($res['secid']);
			$link=mklink(
				'events.php?id='.$res['id'],
				'events,id'.$res['id'].urlformat($res['title']).'.html',
				iif($set['main']['forcesection'],iif(unserialize_section($res['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
			);
			
			$tabledata[$i]['COL2']='<a href="'.$link.'" target="_blank">'.shorttext(strip_tags($res['title']),40).'</a>';
			if ( $res['username'] ) $tabledata[$i]['COL2'].='<br /><small>'.$apx->lang->get('BY').' '.replace($res['username']).'</small>';
			else $tabledata[$i]['COL2'].='<br /><small>'.$apx->lang->get('BY').' '.$apx->lang->get('GUEST').': <i>'.replace($res['send_username']).'</i></small>';
			
			$col3 = apxdate($this->stamp2time($res['startday']));
			if ( $res['endday']!=$res['startday'] ) $col3.='<br />'.apxdate($this->stamp2time($res['endday']));
			$tabledata[$i]['COL3']=replace($res['catname']);
			$tabledata[$i]['COL4']=$col3;
			$tabledata[$i]['COL5']=number_format($res['hits'],0,'','.');
			
			//Optionen
			if ( $apx->user->has_right('calendar.edit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'calendar.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('calendar.copy') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('calendar.copy') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('copy.gif', 'calendar.copy', 'id='.$res['id'], $apx->lang->get('COPY'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('calendar.del') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'calendar.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('calendar.enable') && !$res['active'] ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('enable.gif', 'calendar.enable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
			elseif ( $apx->user->has_right('calendar.disable') && $res['active'] ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('disable.gif', 'calendar.disable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			
			//Kommentare
			if ( $apx->is_module('comments') ) {
				$tabledata[$i]['OPTIONS'].='&nbsp;';
				list($comments)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='calendar' AND mid='".$res['id']."' )");
				if ( $comments && ( $apx->is_module('comments') && $set['calendar']['coms'] ) && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=calendar&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
}



//***************************** Termin erstellen *****************************
function add() {
	global $set,$db,$apx;
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	//Absenden
	if ( $_POST['send'] ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['catid'] || !$_POST['title'] || !$_POST['text'] || !$_POST['start_day'] || !$_POST['start_month'] || !$_POST['start_year'] ) infoNotComplete();
		elseif ( !$this->update_pic() ) { /*DO NOTHING*/ }
		else {
			$_POST['addtime']=time();
			$_POST['picture']=$this->picpath;
			$_POST['startday']=$this->generate_stamp($_POST['start_day'],$_POST['start_month'],$_POST['start_year']);
			
			//Startzeit
			$_POST['starttime']=-1;
			if ( $_POST['start_hour']!=='' && $_POST['start_minute']!=='' ) {
				$_POST['starttime']=sprintf('%02d%02d',$_POST['start_hour'],$_POST['start_minute']);
			}
			
			//Termin Ende
			$_POST['endday']=0;
			if ( $_POST['end_day']!=='' && $_POST['end_month']!=='' && $_POST['end_year']!=='' ) {
				$_POST['endday']=$this->generate_stamp($_POST['end_day'],$_POST['end_month'],$_POST['end_year']);
				$_POST['endtime'] = -1;
				if ( $_POST['end_hour']!=='' && $_POST['end_minute']!=='' ) {
					$_POST['endtime']=sprintf('%02d%02d',$_POST['end_hour'],$_POST['end_minute']);
				}
			}
			else {
				$_POST['endday'] = $_POST['startday'];
				$_POST['endtime'] = -1;
			}
			
			//Links
			for ( $i=1; $i<=20; $i++ ) {
				if ( !$_POST['link'.$i.'_title'] || !$_POST['link'.$i.'_text'] || !$_POST['link'.$i.'_url'] ) continue;
				$links[]=array(
					'title' => $_POST['link'.$i.'_title'],
					'text' => $_POST['link'.$i.'_text'],
					'url' => $_POST['link'.$i.'_url'],
					'popup' => (int)$_POST['link'.$i.'_popup']
				);
			}
			$_POST['links']=serialize($links);
			
			//Freischalten
			$_POST['active'] = 0;
			if ( $apx->user->has_right('calendar.enable') && $_POST['pubnow'] ) {
				$_POST['active'] = time();
			}
			
			//Sektion
			$_POST['secid']=serialize_section($_POST['secid']);
			
			$db->dinsert(PRE.'_calendar_events','secid,catid,userid,title,text,location,location_link,picture,priority,meta_description,galid,links,addtime,startday,starttime,endday,endtime,searchable,restricted,allowcoms,allownote,active');
			$nid=$db->insert_id();
			logit('CALENDAR_ADD','ID #'.$nid);
			
			//Inlinescreens
			mediamanager_setinline($nid);
			
			//Tags
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_calendar_tags VALUES('".$nid."', '".$tagid."')");
			}
			
			printJSRedirect('action.php?action=calendar.show');
			return;	
		}
	}
	else {
		$_POST['link1_title']=$apx->lang->get('LLINK');
		$_POST['link1_popup']=1;
		$_POST['priority'] = 2;
		$_POST['searchable'] = 1;
		$_POST['allowcoms'] = 1;
		$_POST['allownote'] = 1;
		$_POST['pubnow'] = 1;
		$_POST['start_day']=date('d',time()-TIMEDIFF);
		$_POST['start_month']=date('m',time()-TIMEDIFF);
		$_POST['start_year']=date('Y',time()-TIMEDIFF);
		$_POST['userid']=$apx->user->info['userid'];
		
		
		//Normale Links
		if ( !$_POST['link1_title'] ) $_POST['link1_title']=$apx->lang->get('LLINK');
		for ( $i=1; $i<=20; $i++ ) {
			if ( $i>1 && ( !$_POST['link'.$i.'_title'] || $_POST['link'.$i.'_title']==$apx->lang->get('LLINK') ) && !$_POST['link'.$i.'_text'] && !$_POST['link'.$i.'_url'] ) continue;
			$linklist[]=array(
				'TITLE' => compatible_hsc($_POST['link'.$i.'_title']),
				'TEXT' => compatible_hsc($_POST['link'.$i.'_text']),
				'URL' => compatible_hsc($_POST['link'.$i.'_url']),
				'POPUP' => (int)$_POST['link'.$i.'_popup'],
				'DISPLAY' => 1
			);
		}
		while ( count($linklist)<20 ) {
			$linklist[]=array('TITLE'=>$apx->lang->get('LLINK'),'POPUP'=>1);
		}
		
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('CATLIST',$this->get_catlist());
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
		$apx->tmpl->assign('GALID',(int)$_POST['galid']);
		$apx->tmpl->assign('PRIORITY',(int)$_POST['priority']);
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('LINK',$linklist);
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWNOTE',(int)$_POST['allownote']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('RESTRICTED',(int)$_POST['restricted']);
		$apx->tmpl->assign('PUBNOW',(int)$_POST['pubnow']);
		
		$apx->tmpl->assign('ACTION','add');
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Termin bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	//Aktualisieren
	if ( $_POST['send'] ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['catid'] || !$_POST['title'] || !$_POST['text'] || !$_POST['start_day'] || !$_POST['start_month'] || !$_POST['start_year'] ) infoNotComplete();
		elseif ( !$this->update_pic() ) { /*DO NOTHING*/ }
		else {
			$_POST['picture']=$this->picpath;
			$_POST['startday']=$this->generate_stamp($_POST['start_day'],$_POST['start_month'],$_POST['start_year']);
			
			//Startzeit
			$_POST['starttime']=-1;
			if ( $_POST['start_hour']!=='' && $_POST['start_minute']!=='' ) {
				$_POST['starttime']=sprintf('%02d%02d',$_POST['start_hour'],$_POST['start_minute']);
			}
			
			//Termin Ende
			$_POST['endday']=0;
			if ( $_POST['end_day']!=='' && $_POST['end_month']!=='' && $_POST['end_year']!=='' ) {
				$_POST['endday']=$this->generate_stamp($_POST['end_day'],$_POST['end_month'],$_POST['end_year']);
				$_POST['endtime'] = -1;
				if ( $_POST['end_hour']!=='' && $_POST['end_minute']!=='' ) {
					$_POST['endtime']=sprintf('%02d%02d',$_POST['end_hour'],$_POST['end_minute']);
				}
			}
			else {
				$_POST['endday'] = $_POST['startday'];
				$_POST['endtime'] = -1;
			}
			
			//Links
			for ( $i=1; $i<=20; $i++ ) {
				if ( !$_POST['link'.$i.'_title'] || !$_POST['link'.$i.'_text'] || !$_POST['link'.$i.'_url'] ) continue;
				$links[]=array(
					'title' => $_POST['link'.$i.'_title'],
					'text' => $_POST['link'.$i.'_text'],
					'url' => $_POST['link'.$i.'_url'],
					'popup' => (int)$_POST['link'.$i.'_popup']
				);
			}
			$_POST['links']=serialize($links);
			
			//Autor
			if ( $_POST['userid']=='send' ) $_POST['userid']=0;
			else $_POST['userid']=$_POST['userid'];
			
			//Sektion
			$_POST['secid']=serialize_section($_POST['secid']);
			
			$db->dupdate(PRE.'_calendar_events','secid,catid,userid,title,text,location,location_link,picture,priority,meta_description,galid,links,startday,starttime,endday,endtime,searchable,restricted,allowcoms,allownote',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$nid=$db->insert_id();
			logit('CALENDAR_EDIT','ID #'.$_REQUEST['id']);
			
			//Tags
			$db->query("DELETE FROM ".PRE."_calendar_tags WHERE id='".$_REQUEST['id']."'");
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_calendar_tags VALUES('".$_REQUEST['id']."', '".$tagid."')");
			}
			
			printJSRedirect(get_index('calendar.show'));
			return;	
		}
	}
	else {
		$res = $db->first("SELECT * FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' LIMIT 1");
		foreach ( $res AS $key => $value ) {
			$_POST[$key] = $value;
		}
		
		//Start
		$start = $this->explode_stamp($res['startday']);
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
			$end = $this->explode_stamp($res['endday']);
			$_POST['end_day'] = $end['day'];
			$_POST['end_month'] = $end['month'];
			$_POST['end_year'] = $end['year'];
			if ( $res['endtime']!=-1 ) {
				$endtime = sprintf('%04d',$res['endtime']);
				$_POST['end_hour'] = substr($endtime,0,2);
				$_POST['end_minute'] = substr($endtime,2,2);
			}
		}
		
		//Links umformen
		$_POST['link1_popup']=1;
		$links=unserialize($res['links']);
		if ( is_array($links) && count($links) ) {
			foreach ( $links AS $link ) {
				++$i;
				$_POST['link'.$i.'_title']=$link['title'];
				$_POST['link'.$i.'_text']=$link['text'];
				$_POST['link'.$i.'_url']=$link['url'];
				$_POST['link'.$i.'_popup']=$link['popup'];
			}
		}
		
		//Sektionen
		$_POST['secid']=unserialize_section($_POST['secid']);
		
		
		//Aktuelles Bild
		list($picture) = $db->first("SELECT picture FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$teaserpic = '';
		if ( $picture ) {
			$teaserpicpath = $picture;
			$poppicpath = str_replace('-thumb.', '.', $teaserpicpath);
			if ( file_exists(BASEDIR.getpath('uploads').$poppicpath) ) {
				$teaserpic = '../'.getpath('uploads').$poppicpath;
			}
			else {
				$teaserpic = '../'.getpath('uploads').$teaserpicpath;
			}
		}
		
		
		//Einsende-User beachten
		$send=$db->first("SELECT send_username,send_email FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' LIMIT 1");
		if ( $send['send_username'] ) {
			$usersend='<option value="send"'.iif($_POST['userid']=='send',' selected="selected"').'>'.$apx->lang->get('GUEST').': '.$send['send_username'].iif($send['send_email'],' ('.$send['send_email'].')').'</option>';
		}
		
		//Normale Links
		if ( !$_POST['link1_title'] ) $_POST['link1_title']=$apx->lang->get('LLINK');
		for ( $i=1; $i<=20; $i++ ) {
			if ( $i>1 && ( !$_POST['link'.$i.'_title'] || $_POST['link'.$i.'_title']==$apx->lang->get('LLINK') ) && !$_POST['link'.$i.'_text'] && !$_POST['link'.$i.'_url'] ) continue;
			$linklist[]=array(
				'TITLE' => compatible_hsc($_POST['link'.$i.'_title']),
				'TEXT' => compatible_hsc($_POST['link'.$i.'_text']),
				'URL' => compatible_hsc($_POST['link'.$i.'_url']),
				'POPUP' => (int)$_POST['link'.$i.'_popup'],
				'DISPLAY' => 1
			);
		}
		while ( count($linklist)<20 ) {
			$linklist[]=array('TITLE'=>$apx->lang->get('LLINK'),'POPUP'=>1);
		}
		
		//Tags
		$tags = array();
		$tagdata = $db->fetch("
			SELECT t.tag
			FROM ".PRE."_calendar_tags AS n
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE n.id='".$_REQUEST['id']."'
			ORDER BY t.tag ASC
		");
		$tags = get_ids($tagdata, 'tag');
		$_POST['tags'] = implode(', ', $tags);
		
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('USER_SEND',$usersend);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('CATLIST',$this->get_catlist());
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('PICTURE',$teaserpic);
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
		$apx->tmpl->assign('GALID',(int)$_POST['galid']);
		$apx->tmpl->assign('PRIORITY',(int)$_POST['priority']);
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('LINK',$linklist);
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWNOTE',(int)$_POST['allownote']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('RESTRICTED',(int)$_POST['restricted']);
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('ACTION','edit');
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Termin kopieren *****************************
function copy() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$res=$db->first("SELECT secid,catid,userid,send_username,send_email,send_ip,picture,title,text,location,location_link,picture,priority,galid,links,startday,starttime,endday,endtime,searchable,allowcoms,allownote,restricted,private FROM ".PRE."_calendar_events WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('calendar.copy')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			
			foreach ( $res AS $key => $val ) $_POST[$key]=$val;
			$_POST['title']=$apx->lang->get('COPYOF').$_POST['title'];
			$_POST['addtime']=time();
			
			$db->dinsert(PRE.'_calendar_events','secid,catid,userid,send_username,send_email,send_ip,picture,title,text,location,location_link,picture,priority,galid,links,startday,addtime,starttime,endday,endtime,searchable,allowcoms,allownote,restricted,private');
			$nid = $db->insert_id();
			$oldId = $_REQUEST['id'];
			$newId = $nid;
			
			//Bilder kopieren
			$newPicture = str_replace($oldId, $newId, $res['picture']);
			copy_with_thumbnail($res['picture'], $newPicture);
			
			//Bilder update
			$db->query("UPDATE ".PRE."_calendar_events SET picture='".addslashes($newPicture)."' WHERE id='".$newId."' LIMIT 1");
			
			logit('CALENDAR_COPY','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('calendar.show'));
		}
	}
	else {
		list($title, $subtitle) = $db->first("SELECT title FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('copy',array('ID'=>$_REQUEST['id']));
	}
}



//***************************** Termin löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$res = $db->first("SELECT picture FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$db->query("DELETE FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' LIMIT 1");
			
			//Kommentare löschen
			if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='anzeigenmarkt' AND mid='".$_REQUEST['id']."' )");
			
			//Bilder löschen
			require_once(BASEDIR.'lib/class.mediamanager.php');
			$mm = new mediamanager();
			$picture = $res['picture'];
			$poppic = str_replace('-thumb.','.',$picture);
			if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $mm->deletefile($picture);
			if ( $poppic && file_exists(BASEDIR.getpath('uploads').$poppic) ) $mm->deletefile($poppic);
			
			//Tags löschen
			$db->query("DELETE FROM ".PRE."_calendar_tags WHERE id='".$_REQUEST['id']."'");
			
			logit('CALENDAR_DEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('calendar.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
	}
}



//***************************** Termin aktivieren *****************************
function enable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$starttime=maketime(1);
			$endtime=maketime(2);
			if ( !$endtime || $endtime<=$starttime ) $endtime=3000000000;
			
			$db->query("UPDATE ".PRE."_calendar_events SET active='".time()."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('CALENDAR_ENABLE','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('calendar.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('enable',array('ID'=>$_REQUEST['id']));
	}
}



//***************************** Termin widerrufen *****************************
function disable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');

	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("UPDATE ".PRE."_calendar_events SET active='0' WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('CALENDAR_DISABLE','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('calendar.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_calendar_events WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('disable',array('ID'=>$_REQUEST['id']));
	}
}



////////////////////////////////////////////////////////////////////////////////////////// KATEGORIEN

//***************************** Kategorien zeigen *****************************
function catshow() {
	global $set,$db,$apx,$html;
	
	//Struktur reparieren
	if ( $_REQUEST['repair'] && $set['calendar']['subcats'] ) {
		$this->cat->repair();
		echo 'Repair done!';
		return;
	}
	
	quicklink('calendar.catadd');
	
	//DnD-Hinweis
	if ( $set['calendar']['subcats'] && $apx->user->has_right('calendar.edit') ) {
		echo '<p class="hint">'.$apx->lang->get('USEDND').'</p>';
	}
	
	$col[]=array('ID',0,'align="center"');
	$col[]=array('COL_CATNAME',75,'class="title"');
	$col[]=array('COL_EVENTS',25,'align="center"');
	
	if ( $set['calendar']['subcats'] ) {
		$data=$this->cat->getTree(array('title'));
	}
	else {
		$orderdef[0]='title';
		$orderdef['title']=array('title','ASC','COL_CATNAME');
		
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_calendar_cat");
		pages('action.php?action=calendar.catshow',$count);
		$data=$db->fetch("SELECT * FROM ".PRE."_calendar_cat ".getorder($orderdef).getlimit());
	}
	
	if ( count($data) ) {
		
		//Ausgabe erfolgt
		foreach ( $data AS $res ) {
			++$i;
			
			list($events)=$db->first("SELECT count(id) FROM ".PRE."_calendar_events WHERE catid='".$res['id']."'");
			
			$tabledata[$i]['COL1']=$res['id'];
			$tabledata[$i]['COL2']=replace($res['title']);
			$tabledata[$i]['COL3']=iif(isset($events),$events,'&nbsp;');
			$tabledata[$i]['CLASS'] = 'l'.($res['level']-1).($res['children'] ? ' haschildren' : '').($res['level']>1 ? ' hidden' : '').($res['iscat'] ? ' dark' : '');
			$tabledata[$i]['ID'] = 'node:'.$res['id'];
			
			//Optionen
			if ( $apx->user->has_right('calendar.catedit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'calendar.catedit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('calendar.catdel') && !$events ) $tabledata[$i]['OPTIONS'].='<span class="ifhasnochildren">'.optionHTMLOverlay('del.gif', 'calendar.catdel', 'id='.$res['id'], $apx->lang->get('CORE_DEL')).'</span><span class="ifhaschildren"><img alt="" src="design/ispace.gif"/></span>';
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('calendar.catclean') && $events ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('clean.gif', 'calendar.catclean', 'id='.$res['id'], $apx->lang->get('CLEAN'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Anordnen nur bei Unterkategorien
			/*if ( $set['calendar']['subcats'] ) {
				$tabledata[$i]['OPTIONS'].='&nbsp;';
				if ( $apx->user->has_right('calendar.catmove') && $follow[$res['id']]['prev'] ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'calendar.catmove', 'direction=up&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
				if ( $apx->user->has_right('calendar.catmove') && $follow[$res['id']]['next'] ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'calendar.catmove', 'direction=down&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
			}*/
			
			unset($events);
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	
	//Mit Unter-Kategorien
	if ( $set['calendar']['subcats'] ) {
		echo '<div class="treeview" id="tree">';
		$html->table($col);
		echo '</div>';
		
		$open = $apx->session->get('calendar_cat_open');
		$open = dash_unserialize($open);
		$opendata = array();
		foreach ( $open AS $catid ) {
			$opendata[] = array(
				'ID' => $catid
			);
		}
		$apx->tmpl->assign('OPEN', $opendata);
		$apx->tmpl->assign('EDIT_ALLOWED', $apx->user->has_Right('calendar.edit'));
		$apx->tmpl->parse('catshow_js');
	}
	
	//Normale Kategorien
	else {
		$html->table($col);
		orderstr($orderdef,'action.php?action=calendar.catshow');
	}
	
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Neue Kategorie *****************************
function catadd() {
	global $set,$db,$apx;
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['parent'] ) infoNotComplete();
		else {
			
			//WENN ROOT
			if ( $_POST['parent']=='root' ) {
				$nid = $this->cat->createNode(0, array(
					'title' => $_POST['title'],
					'icon' => $_POST['icon']
				));
				logit('CALENDAR_CATADD','ID #'.$nid);
				
				//Beitrag der Kategorie hinzufügen
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
				}
				else {
					printJSRedirect('action.php?action=calendar.catshow');
				}
			}
			
			//WENN NODE
			else {
				$nid = $this->cat->createNode(intval($_POST['parent']), array(
					'title' => $_POST['title'],
					'icon' => $_POST['icon']
				));
				logit('CALENDAR_CATADD',"ID #".$nid);
				
				//Beitrag der Kategorie hinzufügen
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
				}
				else {
					printJSRedirect('action.php?action=calendar.catshow');
				}
			}
		}
	}
	else {
		
		//Baum
		if ( $set['calendar']['subcats'] ) {
			$catlist='<option value="root" style="font-weight:bold;">'.$apx->lang->get('ROOT').'</option>';
			$data=$this->cat->getTree(array('title'));
			if ( count($data) ) {
				$catlist.='<option value=""></option>';
				foreach ( $data AS $res ) {
					$catlist.='<option value="'.$res['id'].'"'.iif($_POST['parent']==$res['id'],' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;',($res['level']-1)).replace($res['title']).'</option>';
				}
			}
		}
		
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('ICON',compatible_hsc($_POST['icon']));
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('ACTION','add');
		$apx->tmpl->assign('UPDATEPARENT',(int)$_REQUEST['updateparent']);
		
		$apx->tmpl->parse('catadd_catedit');
	}
}



//***************************** Kategorie bearbeiten *****************************
function catedit() {
global $set,$apx,$tmpl,$db,$user;
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		list($events)=$db->first("SELECT count(id) FROM ".PRE."_calendar_events WHERE catid='".$_REQUEST['id']."'");
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['id'] || !$_POST['parent'] || !$_POST['title'] ) infoNotComplete();
		else {
			$this->cat->moveNode($_REQUEST['id'], intval($_POST['parent']), array(
				'title' => $_POST['title'],
				'icon' => $_POST['icon']
			));
			logit('CALENDAR_CATEDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('calendar.catshow'));
		}
	}
	else {
		$res = $this->cat->getNode($_REQUEST['id'], array('title','icon'));
		$_POST['title'] = $res['title'];
		$_POST['icon'] = $res['icon'];
		if ( !$res['parents'] ) $_POST['parent'] = 'root';
		else $_POST['parent'] = array_pop($res['parents']);
		
		//Baum
		if ( $set['calendar']['subcats'] ) {
			$catlist='<option value="root" style="font-weight:bold;">'.$apx->lang->get('ROOT').'</option>';
			$data=$this->cat->getTree(array('title'));
			if ( count($data) ) {
				$catlist.='<option value=""></option>';
				foreach ( $data AS $res ) {
					if ( $jumplevel && $res['level']>$jumplevel ) continue;
					else $jumplevel=0;
					if ( $_REQUEST['id']==$res['id'] ) { $jumplevel=$res['level']; continue; }
					$catlist.='<option value="'.$res['id'].'"'.iif($_POST['parent']===$res['id'],' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;',($res['level']-1)).replace($res['title']).'</option>';
				}
			}
		}
		
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('ICON',compatible_hsc($_POST['icon']));
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('ACTION','edit');
		
		$apx->tmpl->parse('catadd_catedit');
	}
}



//***************************** Kategorie löschen *****************************
function catdel() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	list($events)=$db->first("SELECT count(id) FROM ".PRE."_calendar_events WHERE catid='".$_REQUEST['id']."'");
	if ( $events ) die('category still contains calendar!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$this->cat->deleteNode($_REQUEST['id']);
			logit('CALENDAR_CATDEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('calendar.catshow'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_calendar_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
	}
}



//***************************** Kategorie leeren + löschen *****************************
function catclean() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( $_POST['delcat'] ) {
			$nodeInfo = $this->cat->getNode($_REQUEST['id']);
			if ( $nodeInfo['children'] ) {
				$_POST['delcat'] = 0;
			}
		}
		
		if ( !checkToken() ) printInvalidToken();
		elseif ( $_POST['id'] && $_POST['moveto'] ) {
			$db->query("UPDATE ".PRE."_calendar_events SET catid='".intval($_POST['moveto'])."' WHERE catid='".$_REQUEST['id']."'");
			logit('CALENDAR_CATCLEAN',"ID #".$_REQUEST['id']);
			
			//Kategorie löschen
			if ( $_POST['delcat'] ) {
				$this->cat->deleteNode($_REQUEST['id']);
				logit('CALENDAR_CATDEL',"ID #".$_REQUEST['id']);
			}
			
			printJSRedirect(get_index('calendar.catshow'));
			return;
		}
	}
	
	if ( $set['calendar']['subcats'] ) $data=$this->cat->getTree(array('title'));
	else $data=$db->fetch("SELECT id,title FROM ".PRE."_calendar_cat ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',($res['level']-1));
			if ( $res['id']!=$_REQUEST['id'] ) $catlist.='<option value="'.$res['id'].'" '.iif($_POST['moveto']==$res['id'],' selected="selected"').' style="color:green;">'.$space.replace($res['title']).'</option>';
			else $catlist.='<option value="" disabled="disabled" style="color:grey;">'.$space.replace($res['title']).'</option>';
		}
	}
	
	list($title,$children)=$db->first("SELECT title,children FROM ".PRE."_calendar_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
	$children = dash_unserialize($children);
	
	$apx->tmpl->assign('ID',$_REQUEST['id']);
	$apx->tmpl->assign('TITLE',compatible_hsc($title));
	$apx->tmpl->assign('DELCAT',(int)$_POST['delcat']);
	$apx->tmpl->assign('DELETEABLE', !$children);
	$apx->tmpl->assign('CATLIST',$catlist);
	
	tmessageOverlay('catclean');
}



//***************************** Kategorie verschieben *****************************
/*function catmove() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		$this->cat->move($_REQUEST['id'],$_REQUEST['direction']);
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.get_index('calendar.catshow'));
	}
}*/


} //END CLASS


?>
