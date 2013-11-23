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


# VIDEOS CLASS
# ============

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Funktionen laden
include(BASEDIR.getmodulepath('videos').'admin_extend.php');


class action extends videos_functions {

var $cat;

//Startup
function action() {
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$this->cat=new RecursiveTree(PRE.'_videos_cat', 'id');
	
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$this->mm=new mediamanager;
}



////////////////////////////////////////////////////////////////////////////////////////// ARTICLE

//***************************** Videos zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	//Suche durchführen
	if ( ( $_REQUEST['item'] && ( $_REQUEST['title'] || $_REQUEST['text'] ) ) || $_REQUEST['secid'] || $_REQUEST['catid'] || $_REQUEST['userid'] ) {
		$where = '';
		$_REQUEST['secid'] = (int)$_REQUEST['secid'];
		$_REQUEST['catid'] = (int)$_REQUEST['catid'];
		$_REQUEST['userid'] = (int)$_REQUEST['userid'];
		
		//Suchbegriff
		if ( $_REQUEST['item'] ) {
			if ( $_REQUEST['title'] ) $sc[]="title LIKE '%".addslashes_like($_REQUEST['item'])."%'";
			if ( $_REQUEST['subtitle'] ) $sc[]="subtitle LIKE '%".addslashes_like($_REQUEST['item'])."%'";
			if ( $_REQUEST['teaser'] ) $sc[]="teaser LIKE '%".addslashes_like($_REQUEST['item'])."%'";
			if ( $_REQUEST['text'] ) $sc[]="text LIKE '%".addslashes_like($_REQUEST['item'])."%'";
			if ( is_array($sc) ) $where.=' AND ( '.implode(' OR ',$sc).' )';
		}
		
		//Sektion
		if ( !$apx->session->get('section') && $_REQUEST['secid'] ) {
			$where.=" AND ( secid LIKE '%|".$_REQUEST['secid']."|%' OR secid='all' ) ";
		}
		
		//Kategorie
		if ( $_REQUEST['catid'] ) {
			$tree = $this->cat->getChildrenIds($_REQUEST['catid']);
			$tree[] = $_REQUEST['catid'];
			if ( is_array($tree) ) $where.=" AND catid IN (".implode(',',$tree).") ";
		}
		
		//Benutzer
		if ( $_REQUEST['userid'] ) {
			$where.=" AND userid='".$_REQUEST['userid']."' ";
		}
		
		$data=$db->fetch("SELECT id FROM ".PRE."_videos WHERE 1 ".$where);
		$ids = get_ids($data, 'id');
		$ids[] = -1;
		$searchid = saveSearchResult('admin_videos', $ids, array(
			'item' => $_REQUEST['item'],
			'title' => $_REQUEST['title'],
			'text' => $_REQUEST['text'],
			'catid' => $_REQUEST['catid'],
			'secid' => $_REQUEST['secid'],
			'userid' => $_REQUEST['userid']
		));
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: action.php?action=videos.show&what='.$_REQUEST['what'].'&searchid='.$searchid);
		return;
	}
	
	
	//Unbroken setzen
	$_REQUEST['unbroken']=(int)$_REQUEST['unbroken'];
	if ( $_REQUEST['unbroken'] ) {
		$db->query("UPDATE ".PRE."_videos SET broken='' WHERE id='".$_REQUEST['unbroken']."' LIMIT 1");
	}
	
	
	//Vorgaben
	$_REQUEST['title'] = 1;
	$_REQUEST['text'] = 1;
	
	quicklink('videos.add');
	
	$layerdef[]=array('LAYER_ALL','action.php?action=videos.show',!$_REQUEST['what']);
	$layerdef[]=array('LAYER_BROKEN','action.php?action=videos.show&amp;what=broken',$_REQUEST['what']=='broken');
	if ( $set['videos']['ffmpeg'] && $set['videos']['flvtool2'] ) {
		$layerdef[]=array('LAYER_FAILED','action.php?action=videos.show&amp;what=failed',$_REQUEST['what']=='failed');
	}
	
	//Layer Header ausgeben
	$html->layer_header($layerdef);
	
	$orderdef[0]='creation';
	$orderdef['title']=array('a.title','ASC','COL_TITLE');
	$orderdef['user']=array('b.username','ASC','COL_AUTHOR');
	$orderdef['category']=array('c.title','ASC','COL_CATEGORY');
	$orderdef['creation']=array('a.addtime','DESC','SORT_ADDTIME');
	$orderdef['publication']=array('a.starttime','DESC','SORT_STARTTIME');
	$orderdef['hits']=array('a.hits','DESC','COL_HITS');
	$orderdef['downloads']=array('a.downloads','DESC','COL_DOWNLOADS');
	
	
	//Suchergebnis?
	$resultFilter = '';
	if ( $_REQUEST['searchid'] ) {
		$searchRes = getSearchResult('admin_videos', $_REQUEST['searchid']);
		if ( $searchRes ) {
			list($resultIds, $resultMeta) = $searchRes;
			$_REQUEST['item'] = $resultMeta['item'];
			$_REQUEST['title'] = $resultMeta['title'];
			$_REQUEST['subtitle'] = $resultMeta['subtitle'];
			$_REQUEST['teaser'] = $resultMeta['teaser'];
			$_REQUEST['text'] = $resultMeta['text'];
			$_REQUEST['catid'] = $resultMeta['catid'];
			$_REQUEST['secid'] = $resultMeta['secid'];
			$_REQUEST['userid'] = $resultMeta['userid'];
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
	
	//Kategorien auflisten
	$catlist = '';
	$data = $this->cat->getTree(array('title', 'open'));
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',$res['level']-1);
			$catlist.='<option value="'.$res['id'].'"'.iif($_REQUEST['catid']==$res['id'],' selected="selected"').'>'.$space.replace($res['title']).'</option>';
		}
	}
	
	//Benutzer auflisten
	$userlist = '';
	$data=$db->fetch("SELECT b.userid,b.username FROM ".PRE."_videos AS a LEFT JOIN ".PRE."_user AS b USING (userid) WHERE a.userid!=0 GROUP BY userid ORDER BY username ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) $userlist.='<option value="'.$res['userid'].'"'.iif($_REQUEST['userid']==$res['userid'],' selected="selected"').'>'.replace($res['username']).'</option>';
	}
	
	$apx->tmpl->assign('ITEM',compatible_hsc($_REQUEST['item']));
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
	if ( $_REQUEST['what']=='broken' ) {
		$layerFilter = " AND a.broken!=0 ";
	}
	elseif ( $_REQUEST['what']=='failed' ) {
		$layerFilter = " AND a.status='failed' ";
	}
	
	
	list($count)=$db->first("SELECT count(userid) FROM ".PRE."_videos AS a WHERE 1 ".$resultFilter.$layerFilter.section_filter(true, 'secid'));
	pages('action.php?action=videos.show&amp;what='.$_REQUEST['what'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']).'&amp;sortby='.$_REQUEST['sortby'],$count);
	$data=$db->fetch("SELECT a.id,a.secid,a.title,a.addtime,a.status,a.allowcoms,a.allowrating,a.starttime,a.endtime,a.broken,a.hits,a.downloads,b.userid,b.username,c.title AS catname FROM ".PRE."_videos AS a LEFT JOIN ".PRE."_user AS b USING(userid) LEFT JOIN ".PRE."_videos_cat AS c ON a.catid=c.id WHERE 1 ".$resultFilter.$layerFilter.section_filter(true, 'a.secid')." ".getorder($orderdef).getlimit());
	$this->show_print($data);
	orderstr($orderdef,'action.php?action=videos.show&amp;what='.$_REQUEST['what'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']));
	save_index($_SERVER['REQUEST_URI']);
	
	
	//Layer-Footer ausgeben
	$html->layer_footer();
}


//Videos auflisten
function show_print($data) {
	global $set,$db,$apx,$html;
	
	$col[]=array('',1,'align="center"');
	$col[]=array($apx->lang->get('COL_TITLE').' / '.$apx->lang->get('COL_AUTHOR'),35,'class="title"');
	$col[]=array('COL_CATEGORY',25,'align="center"');
	$col[]=array('COL_ADDTIME',20,'align="center"');
	$col[]=array('COL_HITS',10,'align="center"');
	$col[]=array('COL_DOWNLOADS',10,'align="center"');

	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Status
			if ( $res['status']=='new' ) $tabledata[$i]['COL1']='<img src="design/processing.gif" alt="'.$apx->lang->get('PROCESSING').'" title="'.$apx->lang->get('PROCESSING').'" />';
			elseif ( $res['status']=='converting' ) $tabledata[$i]['COL1']='<img src="design/processing.gif" alt="'.$apx->lang->get('PROCESSING').'" title="'.$apx->lang->get('PROCESSING').'" />';
			elseif ( $res['status']=='failed' ) $tabledata[$i]['COL1']='<img src="design/failed.gif" alt="'.$apx->lang->get('FAILED').'" title="'.$apx->lang->get('FAILED').'" />';
			elseif ( !$res['starttime'] ) $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['endtime']<time() ) $tabledata[$i]['COL1']='<img src="design/greendotcross.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['starttime']>time() ) $tabledata[$i]['COL1']='<img src="design/greendotwait.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			
			$tmp=unserialize_section($res['secid']);
			$title=shorttext(strip_tags($res['title']),40);
			$link=mklink(
				'videos.php?id='.$res['id'],
				'videos,id'.$res['id'].urlformat($res['title']).'.html',
				iif($set['main']['forcesection'],iif(unserialize_section($res['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
			);
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['COL2'].='<a href="'.$link.'" target="_blank">'.$title.'</a>';
			$tabledata[$i]['COL3']=replace($res['catname']);
			$tabledata[$i]['COL4']=iif($res['starttime'],mkdate($res['starttime'],'<br />'),'&nbsp;');
			$tabledata[$i]['COL5']=$res['hits'];
			$tabledata[$i]['COL6']=$res['downloads'];
			
			$tabledata[$i]['COL2'].='<br /><small>'.$apx->lang->get('BY').' '.replace($res['username']).'</small>';
			if ( $res['broken'] ) {
				$tabledata[$i]['COL2']='<a href="action.php?action=videos.show&amp;what='.$_REQUEST['what'].'&amp;p='.$_REQUEST['p'].'&amp;item='.$_REQUEST['item'].'&amp;sortby='.$_REQUEST['sortby'].'&amp;title='.$_REQUEST['title'].'&amp;text='.$_REQUEST['text'].'&amp;catid='.$_REQUEST['catid'].'&amp;unbroken='.$res['id'].'"><img src="../'.getmodulepath('videos').'images/broken.gif" alt="'.$apx->lang->get('BROKEN').': '.mkdate($res['broken']).'" title="'.$apx->lang->get('BROKEN').': '.mkdate($res['broken']).'" align="right" /></a>'.$tabledata[$i]['COL2'];
			}
			
			//Optionen
			if ( $apx->user->has_right('videos.edit') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('videos.edit') ) ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'videos.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('videos.del') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('videos.del') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'videos.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( ( !$res['starttime'] || $res['endtime']<time() ) && $apx->user->has_right('videos.enable') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('videos.enable') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('enable.gif', 'videos.enable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
			elseif ( $res['starttime'] && $apx->user->has_right('videos.disable') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('videos.disable') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('disable.gif', 'videos.disable', 'id='.$res['id'], $apx->lang->get('CORE_DISABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			$tabledata[$i]['OPTIONS'].='&nbsp;';
			
			if ( $apx->user->has_right('videos.pshow') ) $tabledata[$i]['OPTIONS'].=optionHTML('pic.gif', 'videos.pshow', 'id='.$res['id'], $apx->lang->get('PICS'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Kommentare + Bewertungen
			if ( $apx->is_module('comments') || $apx->is_module('ratings') ) $tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->is_module('comments') ) {
				list($comments)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='videos' AND mid='".$res['id']."' )");
				if ( $comments && ( $apx->is_module('comments') && $set['videos']['coms'] ) && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=videos&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			if ( $apx->is_module('ratings') ) {
				list($ratings)=$db->first("SELECT count(id) FROM ".PRE."_ratings WHERE ( module='videos' AND mid='".$res['id']."' )");
				if ( $ratings && ( $apx->is_module('ratings') && $set['videos']['ratings'] ) && $res['allowrating'] && $apx->user->has_right('ratings.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('ratings.gif', 'ratings.show', 'module=videos&mid='.$res['id'], $apx->lang->get('RATINGS').' ('.$ratings.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$multiactions = array();
	if ( $apx->user->has_right('videos.del') ) $multiactions[] = array($apx->lang->get('CORE_DEL'), 'action.php?action=videos.del', false);
	if ( $apx->user->has_right('videos.enable') ) $multiactions[] = array($apx->lang->get('CORE_ENABLE'), 'action.php?action=videos.enable', false);
	if ( $apx->user->has_right('videos.disable') ) $multiactions[] = array($apx->lang->get('CORE_DISABLE'), 'action.php?action=videos.disable', false);
	$html->table($col, $multiactions);
}



//***************************** Neuer Video *****************************
function add() {
	global $set,$db,$apx;
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send']==1 ) {
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['catid'] || !$_POST['text'] ) infoNotComplete();
		elseif ( $_POST['source']=='embed' && !$_POST['embed_url'] ) infoNotComplete();
		elseif ( $_POST['source']=='select' && !$_POST['select_flv'] ) infoNotComplete();
		elseif ( $_POST['source']=='external' && ( !$_POST['external_flv'] || ( $_POST['external_file'] && !$_POST['external_filesize'] ) ) ) infoNotComplete();
		elseif ( $_POST['source']=='convert' && !$_POST['convert_file'] ) infoNotComplete();
		elseif ( $_POST['source']=='embed' && !($extInfo = $this->getEmbedVideo($_POST['embed_url'])) ) info($apx->lang->get('INFO_EMBED_NOTFOUND'));
		elseif ( $_POST['source']=='select' && !file_exists(BASEDIR.getpath('uploads').$_POST['select_flv']) ) info($apx->lang->get('INFO_NOTEXISTS', array('FILE' => $_POST['select_flv'])));
		elseif ( $_POST['source']=='select' && $_POST['select_file'] && !file_exists(BASEDIR.getpath('uploads').$_POST['select_file']) ) info($apx->lang->get('INFO_NOTEXISTS', array('FILE' => $_POST['select_file'])));
		elseif ( $_POST['source']=='convert' && !file_exists(BASEDIR.getpath('uploads').$_POST['convert_file']) ) info($apx->lang->get('INFO_NOTEXISTS', array('FILE' => $_POST['convert_file'])));
		elseif ( !$this->category_is_open($_POST['catid']) ) die('you are not allowed to add videos to this category!');
		elseif ( !$this->update_teaserpic() ) { /*DO NOTHING*/ }
		else {
			$vSource = $_POST['source'];
			
			//Veröffentlichung
			if ( $apx->user->has_right('videos.enable') && $_POST['pubnow'] ) {
				$addfield=',starttime,endtime';
				$_POST['starttime']=time();
				$_POST['endtime']='3000000000';
			}
			
			$_POST['secid']=serialize_section($_POST['secid']);
			$_POST['addtime']=time();
			$_POST['teaserpic']=$this->teaserpicpath;
			$_POST['regonly'] = 0;
			$_POST['limit'] = 0;
			$_POST['filesize'] = 0;
			$_POST['password'] = '';
			
			//Autor
			if ( !$apx->user->has_spright('videos.edit') ) $_POST['userid']=$apx->user->info['userid'];
			
			//Auswahl
			if ( $vSource=='select' ) {
				$_POST['source'] = 'apexx';
				$_POST['flvfile'] = $_POST['select_flv'];;
				$_POST['file'] = $_POST['select_file'];
				$_POST['status'] = 'finished';
				
				//Download-Optionen
				if ( $_POST['select_file'] ) {
					$_POST['regonly'] = (int)$_POST['select_regonly'];
					$_POST['limit'] = (int)$_POST['select_limit'];
					$_POST['password'] = $_POST['select_password'];
				}
			}
			
			//Externe Auswahl
			elseif ( $vSource=='external' ) {
				$_POST['source'] = 'external';
				$_POST['flvfile'] = $_POST['external_flv'];;
				$_POST['file'] = $_POST['external_file'];
				$_POST['status'] = 'finished';
				
				//Download-Optionen
				if ( $_POST['external_file'] ) {
					$_POST['regonly'] = (int)$_POST['external_regonly'];
					$_POST['limit'] = (int)$_POST['external_limit'];
					$_POST['password'] = $_POST['external_password'];
					
					if ( $_POST['external_filesize_format']=='kb' ) $_POST['filesize']=(int)1024*(float)str_replace(',','.',$_POST['external_filesize']);
					elseif ( $_POST['external_filesize_format']=='mb' ) $_POST['filesize']=(int)1024*1024*(float)str_replace(',','.',$_POST['external_filesize']);
					elseif ( $_POST['external_filesize_format']=='gb' ) $_POST['filesize']=(int)1024*1024*1024*(float)str_replace(',','.',$_POST['external_filesize']);
					else $_POST['filesize'] = $_POST['external_filesize'];
				}
			}
			
			//Konvertierung
			elseif ( $vSource=='convert' ) {
				$_POST['source'] = 'apexx';
				$_POST['flvfile'] = $_POST['convert_file'];
				$_POST['file'] = '';
				$_POST['status'] = 'new';
				
				//Download-Optionen
				if ( $_POST['convert_download'] ) {
					$_POST['file'] = $_POST['convert_file'];
					$_POST['regonly'] = (int)$_POST['convert_regonly'];
					$_POST['limit'] = (int)$_POST['convert_limit'];
					$_POST['password'] = $_POST['convert_password'];
				}
			}
			
			//Embed Service
			else {
				$_POST['source'] = $extInfo['source'];
				$_POST['flvfile'] = $extInfo['identifier'];
				$_POST['file'] = '';
				$_POST['status'] = 'finished';
			}
			
			$db->dinsert(PRE.'_videos','secid,prodid,catid,userid,file,flvfile,filesize,status,source,title,text,meta_description,teaserpic,addtime,password,limit,top,regonly,searchable,allowcoms,allowrating,restricted'.$addfield);
			$nid=$db->insert_id();
			logit('VIDEOS_ADD','ID #'.$nid);
			
			//Tags
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_videos_tags VALUES('".$nid."', '".$tagid."')");
			}
			
			//Konverter starten
			if ( $vSource=='convert' ) {
				$path = 'admin/action.php?action=videos.convert&id='.$nid.'&screens='.intval($_POST['convert_screens']).'&password='.$set['main']['crypt'];
				$fp = fsockopen($_SERVER['HTTP_HOST'], $_SERVER['SERVER_PORT']);
				$send = "GET ".HTTPDIR.$path." HTTP/1.1\r\n";
				$send .= "Host: ".$_SERVER['HTTP_HOST']."\r\n";
    		$send .= "Connection: Close\r\n\r\n";
    		fwrite($fp, $send);
				stream_set_timeout($fp, 2);
  	  	fread($fp, 1024);
  	  	fclose($fp);
			}
			
			//Screenshots erzeugen
			elseif ( $vSource=='select' && $_POST['select_screens'] ) {
				$this->makeScreenshots($_POST['flvfile'], $nid);
			}
			
			printJSRedirect('action.php?action=videos.show');
		}
	}
	else {
		$_POST['searchable']=1;
		$_POST['allowcoms']=1;
		$_POST['allowrating']=1;
		$_POST['userid']=$apx->user->info['userid'];
		
		
		mediamanager('videos');
		
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('PRODID',$_POST['prodid']);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('CATLIST',$this->get_catlist());
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('PASSWORD',compatible_hsc($_POST['password']));
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWRATING',(int)$_POST['allowrating']);
		$apx->tmpl->assign('TOP',(int)$_POST['top']);
		$apx->tmpl->assign('REGONLY',(int)$_POST['regonly']);
		$apx->tmpl->assign('PUBNOW',(int)$_POST['pubnow']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('RESTRICTED',(int)$_POST['restricted']);
		$apx->tmpl->assign('CAN_CONVERT',$set['videos']['ffmpeg'] && $set['videos']['flvtool2']);
		
		$apx->tmpl->assign('ACTION','add');
		
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Video bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send']==1 ) {
		
		list($source) = $db->first("SELECT source FROM ".PRE."_videos WHERE id='".$_REQUEST['id']."' LIMIT 1");
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['catid'] || !$_POST['text'] ) infoNotComplete();
		elseif ( $source=='apexx' && !$_POST['select_flv'] ) infoNotComplete();
		elseif ( $source!='apexx' && $_POST['embed_url'] && !($extInfo = $this->getEmbedVideo($_POST['embed_url'])) ) info($apx->lang->get('INFO_EMBED_NOTFOUND'));
		elseif ( $source=='apexx' && !file_exists(BASEDIR.getpath('uploads').$_POST['select_flv']) ) info($apx->lang->get('INFO_NOTEXISTS', array('FILE' => $_POST['select_flv'])));
		elseif ( $source=='apexx' && $_POST['select_file'] && !file_exists(BASEDIR.getpath('uploads').$_POST['select_file']) ) info($apx->lang->get('INFO_NOTEXISTS', array('FILE' => $_POST['select_file'])));
		elseif ( $source=='external' && ( !$_POST['external_flv'] || ( $_POST['external_file'] && !$_POST['external_filesize'] ) ) ) infoNotComplete();
		elseif ( !$this->category_is_open($_POST['catid']) ) die('you are not allowed to add videos to this category!');
		elseif ( !$this->update_teaserpic() ) { /*DO NOTHING*/ }
		else {
			$addfield = '';
			
			//Veröffentlichung
			if ( $apx->user->has_right('videos.enable') && isset($_POST['t_day_1']) ) {
				$_POST['starttime']=maketime(1);
				$_POST['endtime']=maketime(2);
				if ( $_POST['starttime'] ) {
					if ( !$_POST['endtime'] || $_POST['endtime']<=$_POST['starttime'] ) $_POST['endtime']=3000000000;
					$addfield.=',starttime,endtime';
				}
			}
			
			$_POST['secid']=serialize_section($_POST['secid']);
			$_POST['teaserpic']=$this->teaserpicpath;
			$_POST['regonly'] = 0;
			$_POST['limit'] = 0;
			$_POST['filesize'] = 0;
			$_POST['password'] = '';
			
			//Autor
			if ( $apx->user->has_spright('videos.edit') && $_POST['userid'] ) {
				$_POST['userid']=$_POST['userid'];
				$addfield .= ',userid';
			}
			
			//Auswahl
			if ( $source=='apexx' ) {
				$_POST['source'] = 'apexx';
				$_POST['flvfile'] = $_POST['select_flv'];;
				$_POST['file'] = $_POST['select_file'];
				$addfield .= ',file,flvfile,source';
				
				//Download-Optionen
				if ( $_POST['select_file'] ) {
					$_POST['regonly'] = (int)$_POST['select_regonly'];
					$_POST['limit'] = (int)$_POST['select_limit'];
					$_POST['password'] = $_POST['select_password'];
				}
			}
			
			//Externe Auswahl
			elseif ( $source=='external' ) {
				$_POST['source'] = 'external';
				$_POST['flvfile'] = $_POST['external_flv'];;
				$_POST['file'] = $_POST['external_file'];
				$_POST['status'] = 'finished';
				
				//Download-Optionen
				if ( $_POST['external_file'] ) {
					$_POST['regonly'] = (int)$_POST['external_regonly'];
					$_POST['limit'] = (int)$_POST['external_limit'];
					$_POST['password'] = $_POST['external_password'];
					
					if ( $_POST['external_filesize_format']=='kb' ) $_POST['filesize']=(int)1024*(float)str_replace(',','.',$_POST['external_filesize']);
					elseif ( $_POST['external_filesize_format']=='mb' ) $_POST['filesize']=(int)1024*1024*(float)str_replace(',','.',$_POST['external_filesize']);
					elseif ( $_POST['external_filesize_format']=='gb' ) $_POST['filesize']=(int)1024*1024*1024*(float)str_replace(',','.',$_POST['external_filesize']);
					else $_POST['filesize'] = $_POST['external_filesize'];
				}
				
				$addfield .= ',file,flvfile,source';
			}
			
			//Externer Service
			elseif ( $_POST['embed_url'] ) {
				$_POST['source'] = $extInfo['source'];
				$_POST['flvfile'] = $extInfo['identifier'];
				$_POST['file'] = '';
				$addfield .= ',file,flvfile,source';
			}
			
			$db->dupdate(PRE.'_videos','secid,prodid,catid,title,text,meta_description,teaserpic,filesize,password,limit,top,regonly,searchable,allowcoms,allowrating,restricted'.$addfield, "WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('VIDEOS_EDIT','ID #'.$_REQUEST['id']);
			
			//Tags
			$db->query("DELETE FROM ".PRE."_videos_tags WHERE id='".$_REQUEST['id']."'");
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_videos_tags VALUES('".$_REQUEST['id']."', '".$tagid."')");
			}
			
			printJSRedirect(get_index('videos.show'));
		}
	}
	else {
		require(BASEDIR.getmodulepath('videos').'plattforms.php');
		
		$res=$db->first("SELECT * FROM ".PRE."_videos WHERE id='".$_REQUEST['id']."' LIMIT 1",1);
		foreach ( $res AS $key => $val ) $_POST[$key]=$val;
		$_POST['secid']=unserialize_section($_POST['secid']);
		
		//Autor
		if ( !$res['userid'] ) $_POST['userid']='send';
		
		//Veröffentlichung
		if ( $res['starttime'] ) {
			maketimepost(1,$res['starttime']);
			if ( $res['endtime']<2147483647 ) maketimepost(2,$res['endtime']);
		}
		
		mediamanager('videos');
		
		//Autor
		if ( $apx->user->has_spright('videos.edit') ) {
			$apx->tmpl->assign('USERLIST',$this->get_userlist());
		}
		
		//Teaserpic
		$teaserpic = '';
		if ( $_POST['teaserpic'] ) {
			$teaserpicpath = $_POST['teaserpic'];
			$poppicpath = str_replace('-thumb.', '.', $teaserpicpath);
			if ( file_exists(BASEDIR.getpath('uploads').$poppicpath) ) {
				$teaserpic = '../'.getpath('uploads').$poppicpath;
			}
			else {
				$teaserpic = '../'.getpath('uploads').$teaserpicpath;
			}
		}
		
		//Veröffentlichung
		if ( $apx->user->has_right('videos.enable') && isset($_POST['t_day_1']) ) {
			$apx->tmpl->assign('STARTTIME',choosetime(1,0,maketime(1)));
			$apx->tmpl->assign('ENDTIME',choosetime(2,1,maketime(2)));
		}
		
		//Tags
		$tags = array();
		$tagdata = $db->fetch("
			SELECT t.tag
			FROM ".PRE."_videos_tags AS n
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE n.id='".$_REQUEST['id']."'
			ORDER BY t.tag ASC
		");
		$tags = get_ids($tagdata, 'tag');
		$_POST['tags'] = implode(', ', $tags);
		
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('PRODID',$_POST['prodid']);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('CATLIST',$this->get_catlist());
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('TEASERPIC',$teaserpic);
		$apx->tmpl->assign('PIC_COPY',compatible_hsc($_POST['pic_copy']));
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWRATING',(int)$_POST['allowrating']);
		$apx->tmpl->assign('TOP',(int)$_POST['top']);
		$apx->tmpl->assign('PUBNOW',(int)$_POST['pubnow']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('RESTRICTED',(int)$_POST['restricted']);
		
		$apx->tmpl->assign('CAN_CONVERT',$set['videos']['ffmpeg'] && $set['videos']['flvtool2']);
		$apx->tmpl->assign('SOURCE',compatible_hsc($_POST['source']));
		$apx->tmpl->assign('SOURCE_NAME',compatible_hsc($plattforms[$_POST['source']][4]));
		$apx->tmpl->assign('FLVFILE',compatible_hsc($_POST['flvfile']));
		$apx->tmpl->assign('FILE',compatible_hsc($_POST['file']));
		if ( $_POST['source']=='apexx' && $_POST['file'] ) {
			$apx->tmpl->assign('PASSWORD',compatible_hsc($_POST['password']));
			$apx->tmpl->assign('REGONLY', $_POST['regonly']);
			$apx->tmpl->assign('LIMIT', $_POST['limit']);
		}
		elseif ( $_POST['source']=='external' && $_POST['file'] ) {
			$apx->tmpl->assign('PASSWORD',compatible_hsc($_POST['password']));
			$apx->tmpl->assign('REGONLY', $_POST['regonly']);
			$apx->tmpl->assign('LIMIT', $_POST['limit']);
			$apx->tmpl->assign('FILESIZE',compatible_hsc($_POST['filesize']));
			$apx->tmpl->assign('FILESIZE_FORMAT',$_POST['filesize_format']);
		}
		
		$apx->tmpl->assign('ACTION','edit');
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Video löschen *****************************
function del() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('videos.show'));
				return;
			}
			
			//Dateien löschen
			$data=$db->first("SELECT id, title, teaserpic, source, file, flvfile FROM ".PRE."_videos WHERE ( id IN ( ".implode(',',$cache)." ) ".iif(!$apx->user->has_spright('videos.del')," AND userid='".$apx->user->info['userid']."'")." )");
			$db->query("DELETE FROM ".PRE."_videos WHERE ( id IN ( ".implode(',',$cache)." ) ".iif(!$apx->user->has_spright('videos.del')," AND userid='".$apx->user->info['userid']."'")." )");
			$cache=array();
			if ( !is_array($data) || !count($data) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('videos.show'));
				return;
			}
			
			foreach ( $data AS $res ) {
				list($id, $title, $picture, $source, $file1, $file2) = $res;
				
				//Teaserbilder
				$poppic=str_replace('-thumb.','.',$picture);
				if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $this->mm->deletefile($picture);
				if ( $poppic && file_exists(BASEDIR.getpath('uploads').$poppic) ) $this->mm->deletefile($poppic);
				
				//Dateien löschen
				if ( $source=='apexx' && $_POST['delfile'] ) {
					if ( $file1 && file_exists(BASEDIR.getpath('uploads').$file1) ) $this->mm->deletefile($file1);
					if ( $file2 && file_exists(BASEDIR.getpath('uploads').$file2) ) $this->mm->deletefile($file2);
				}
				
				//Screenshots
				$picdata = $db->fetch("SELECT picture, thumbnail FROM ".PRE."_videos_screens WHERE videoid='".$res['id']."'");
				if ( $picdata ) {
					foreach ( $picdata AS $pic ) {
						if ( $pic['picture'] && file_exists(BASEDIR.getpath('uploads').$pic['picture']) ) $this->mm->deletefile($pic['picture']);
						if ( $pic['thumbnail'] && file_exists(BASEDIR.getpath('uploads').$pic['thumbnail']) ) $this->mm->deletefile($pic['thumbnail']);
					}
				}
				
				//Kommentare und Bewertungen
				if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='videos' AND mid='".$res['id']."' )");
				if ( $apx->is_module('ratings') ) $db->query("DELETE FROM ".PRE."_ratings WHERE ( module='videos' AND mid='".$res['id']."' )");
				
				//Tags löschen
				$db->query("DELETE FROM ".PRE."_videos_tags WHERE id='".$res['id']."'");
				$cache[]=$res['id'];
				
				foreach ( $cache AS $id ) logit('VIDEOS_PDEL',"ID #".$id);
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('videos.show'));
		}
	}
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				
				//Dateien löschen
				list($title, $picture, $source, $file1, $file2) = $db->first("SELECT title, teaserpic, source, file, flvfile FROM ".PRE."_videos WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('videos.del')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
				$db->query("DELETE FROM ".PRE."_videos WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('videos.del')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
				if ( !$db->affected_rows() ) die('access denied!');
				
				//Teaserbilder
				$poppic=str_replace('-thumb.','.',$picture);
				if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $this->mm->deletefile($picture);
				if ( $poppic && file_exists(BASEDIR.getpath('uploads').$poppic) ) $this->mm->deletefile($poppic);
				
				//Dateien löschen
				if ( $source=='apexx' && $_POST['delfile'] ) {
					if ( $file1 && file_exists(BASEDIR.getpath('uploads').$file1) ) $this->mm->deletefile($file1);
					if ( $file2 && file_exists(BASEDIR.getpath('uploads').$file2) ) $this->mm->deletefile($file2);
				}
				
				//Screenshots
				$picdata = $db->fetch("SELECT picture, thumbnail FROM ".PRE."_videos_screens WHERE videoid='".$_REQUEST['id']."'");
				if ( $picdata ) {
					foreach ( $picdata AS $pic ) {
						if ( $pic['picture'] && file_exists(BASEDIR.getpath('uploads').$pic['picture']) ) $this->mm->deletefile($pic['picture']);
						if ( $pic['thumbnail'] && file_exists(BASEDIR.getpath('uploads').$pic['thumbnail']) ) $this->mm->deletefile($pic['thumbnail']);
					}
				}
				
				//Kommentare und Bewertungen
				if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='videos' AND mid='".$_REQUEST['id']."' )");
				if ( $apx->is_module('ratings') ) $db->query("DELETE FROM ".PRE."_ratings WHERE ( module='videos' AND mid='".$_REQUEST['id']."' )");
				
				//Tags löschen
				$db->query("DELETE FROM ".PRE."_videos_tags WHERE id='".$_REQUEST['id']."'");
				
				logit('VIDEOS_DEL','ID #'.$_REQUEST['id']);
				printJSRedirect(get_index('videos.show'));
			}
		}
		else {
			list($title, $source, $file1, $file2) = $db->first("SELECT title, source, file, flvfile FROM ".PRE."_videos WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
			if ( $source=='apexx' ) {
				$files = array(basename($file2));
				if ( $file1 ) {
					$files[] = basename($file1);
				}
				$apx->tmpl->assign('FILENAME', compatible_hsc(implode(', ', $files)));
			}
			tmessageOverlay('del',array('ID'=>$_REQUEST['id']));
		}
	}
}



//***************************** Video aktivieren *****************************
function enable() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('videos.show'));
				return;
			}
			
			$data=$db->fetch("SELECT id FROM ".PRE."_videos WHERE ( id IN (".implode(',',$cache).") ".iif(!$apx->user->has_spright('videos.enable')," AND userid='".$apx->user->info['userid']."'")." )");
			$cache=array();
			if ( !is_array($data) || !count($data) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('videos.show'));
				return;
			}
			
			foreach ( $data AS $res ) {
				$cache[]=$res['id'];
			}
			
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('videos.show'));
				return;
			}
			
			$db->query("UPDATE ".PRE."_videos SET starttime='".time()."',endtime='3000000000' WHERE id IN (".implode(',',$cache).")");
			foreach ( $cache AS $id ) logit('VIDEOS_ENABLE','ID #'.$id);
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('videos.show'));
		}
	}
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		
		if ( $_POST['send']==1 ) {
			$starttime=maketime(1);
			$endtime=maketime(2);
			if ( !$endtime || $endtime<=$starttime ) $endtime=3000000000;
			
			$db->query("UPDATE ".PRE."_videos SET starttime='".$starttime."',endtime='".$endtime."' WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('videos.enable')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			logit('VIDEOS_ENABLE','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('videos.show'));
		}
		else {
			list($title) = $db->first("SELECT title FROM ".PRE."_videos WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$apx->tmpl->assign('TITLE', compatible_hsc($title));
			$apx->tmpl->assign('ID',$_REQUEST['id']);
			$apx->tmpl->assign('STARTTIME',choosetime(1,0,time()));
			$apx->tmpl->assign('ENDTIME',choosetime(2,1));
			tmessageOverlay('enable');
		}
	}
}



//***************************** Video widerrufen *****************************
function disable() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('videos.show'));
				return;
			}
			
			$data=$db->fetch("SELECT id FROM ".PRE."_videos WHERE ( id IN (".implode(',',$cache).") ".iif(!$apx->user->has_spright('videos.disable')," AND userid='".$apx->user->info['userid']."'")." )");
			$cache=array();
			if ( !is_array($data) || !count($data) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('videos.show'));
				return;
			}
			
			foreach ( $data AS $res ) {
				$cache[]=$res['id'];
			}
			
			if ( !count($cache) ) return;
			
			$db->query("UPDATE ".PRE."_videos SET starttime='0',endtime='0' WHERE id IN (".implode(',',$cache).")");
			foreach ( $cache AS $id ) logit('VIDEOS_DISABLE','ID #'.$id);
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('videos.show'));
		}
	}
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
	
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				$db->query("UPDATE ".PRE."_videos SET starttime=0,endtime=0 WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('videos.disable')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
				logit('VIDEOS_DISABLE','ID #'.$_REQUEST['id']);
				printJSRedirect(get_index('videos.show'));
			}
		}
		else {
			list($title) = $db->first("SELECT title FROM ".PRE."_videos WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
			tmessageOverlay('disable',array('ID'=>$_REQUEST['id']));
		}
	}
}



//Konverter laufen lassen
function convert() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( $_REQUEST['password']!=$set['main']['crypt'] ) die('invalid password!');
	$apx->tmpl->loaddesign('blank');
	set_time_limit(300);
	
	$res = $db->first("SELECT flvfile, source, status FROM ".PRE."_videos WHERE id='".$_REQUEST['id']."' LIMIT 1");
	if ( $res['source']=='apexx' && ( $res['status']=='new' || $res['status']=='failed' ) ) {
		$db->query("UPDATE ".PRE."_videos SET status='converting' WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$db->close();
		
		//Konvertierung durchführen
		$sourceFile = $res['flvfile'];
		$flvFile = 'videos/flv/video-'.$_REQUEST['id'].'.flv';
		$success = $this->runConverter($_REQUEST['id'], BASEDIR.getpath('uploads').$sourceFile, BASEDIR.getpath('uploads').$flvFile, (int)$_REQUEST['screens']);
		
		//Konvertierung erfolgreich
		if ( $success ) {
			$db->query("UPDATE ".PRE."_videos SET status='finished', flvfile='".addslashes($flvFile)."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
		}
		
		//Konvertierung fehlgeschlagen
		else {
			$db->query("UPDATE ".PRE."_videos SET status='failed' WHERE id='".$_REQUEST['id']."' LIMIT 1");
		}
	}
}



////////////////////////////////////////////////////////////////////////////////////////// BILDER

//***************************** Bilder zeigen *****************************
function pshow() {
	global $set,$db,$apx,$html;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $this->access_pics($_REQUEST['id'],'videos.padd') ) quicklink('videos.padd','action.php','id='.$_REQUEST['id']); 
	
	$col[]=array('COL_THUMBNAIL',100,'align="center"');
	
	list($title)=$db->first("SELECT title FROM ".PRE."_videos WHERE id='".$_REQUEST['id']."' LIMIT 1");
	echo'<h2>'.$apx->lang->get('VIDEO').': '.$title.'</h2>';
	
	$pictures = $db->fetch("SELECT pictureid, thumbnail, picture FROM ".PRE."_videos_screens WHERE videoid='".$_REQUEST['id']."'");
	if ( is_array($pictures) && count($pictures) ) {
		foreach ( $pictures AS $res ) {
			++$i;
			
			$tabledata[$i]['ID']=$res['pictureid'];
			$tabledata[$i]['COL1']='<a href="../'.getpath('uploads').$res['picture'].'" target="_blank"><img src="../'.getpath('uploads').$res['thumbnail'].'" alt="thumbnail" /></a>';
			
			//Optionen
			if ( $apx->user->has_right('videos.pdel') && $this->access_pics($_REQUEST['id'],'videos.pdel') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'videos.pdel', 'id='.$res['pictureid'].'&dlid='.$_REQUEST['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$multiactions = array();
	if ( $this->access_pics($_REQUEST['id'], 'videos.pdel') ) $multiactions[] = array($apx->lang->get('CORE_DEL'), 'action.php?action=videos.pdel&id='.$_REQUEST['id']);
	$html->table($col, $multiactions);
}



//***************************** Bilder anfügen *****************************
function padd() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( !$this->access_pics($_REQUEST['id'],'videos.padd') ) die('you have no right to access this video!');

	if ( $_POST['send']==1 ) {
		require_once(BASEDIR.'lib/class.image.php');
		$img=new image;
		
		$tblinfo=$db->first("SHOW TABLE STATUS LIKE '".PRE."_videos_screens'");
		$nextid = $tblinfo['Auto_increment'];
		
		//Bilder prüfen
		$error = array();
		for ( $i=1; $i<=$set['videos']['addpics']; $i++ ) {
			if ( !$_FILES['upload'.$i]['tmp_name'] ) continue;
			$imginfo = getimagesize($_FILES['upload'.$i]['tmp_name']);
			if ( !in_array($imginfo[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)) ) {
				$error[] = $apx->lang->get('MSG_NOIMAGE', array('NAME' => $_FILES['upload'.$i]['name']));
			}
			if ( $error ) {
				info(implode('<br />', $error));
				return;
			}
		}
		
		//Bilder abarbeiten
		for ( $i=1; $i<=$set['videos']['addpics']; $i++ ) {
			if ( !$_FILES['upload'.$i]['tmp_name'] ) continue;
			
			$ext=strtolower($this->mm->getext($_FILES['upload'.$i]['name']));
			if ( $ext=='gif' ) $ext='jpg';
			
			$newname='pic'.'-'.$_POST['id'].'-'.$nextid.'.'.$ext;
			$newfile='videos/screens/'.$newname;
			$thumbname='pic'.'-'.$_POST['id'].'-'.$nextid.'-thumb.'.$ext;
			$thumbfile='videos/screens/'.$thumbname;
			
			//Erfolgreichen Upload prüfen
			if ( !$this->mm->uploadfile($_FILES['upload'.$i],'videos/screens',$newname) ) continue;
			
			//Bild einlesen
			list($picture,$picturetype)=$img->getimage($newfile);
			
			
			//////// THUMBNAIL
			$thumbnail=$img->resize($picture,$set['videos']['thumbwidth'],$set['videos']['thumbheight'],$set['videos']['quality_resize'],$set['videos']['thumb_fit']);
			$img->saveimage($thumbnail,$picturetype,$thumbfile);
			
			
			//////// BILD
			
			//Bild skalieren
			if ( $picture!==false && !$_POST['noresize'.$i] && $set['videos']['picwidth'] && $set['videos']['picheight'] ) {
				$scaled=$img->resize(
					$picture,
					$set['videos']['picwidth'],
					$set['videos']['picheight'],
					$set['videos']['quality_resize'],
					0
				);
				
				if ( $scaled!=$picture ) imagedestroy($picture);
				$picture=$scaled;
			}
			
			//Wasserzeichen einfügen
			if ( $picture!==false && $set['videos']['watermark'] && $_POST['watermark'.$i] ) {
				$watermarked=$img->watermark(
					$picture,
					$set['videos']['watermark'],
					$set['videos']['watermark_position'],
					$set['videos']['watermark_transp']
				);
				
				if ( $watermarked!=$picture ) imagedestroy($picture);
				$picture=$watermarked;
			}
			
			//Bild erstellen
			$img->saveimage($picture,$picturetype,$newfile);
			
			//Cleanup
			imagedestroy($picture);
			imagedestroy($thumbnail);
			unset($picture,$thumbnail);
			
			//In DB eintragen
			$db->query("INSERT INTO ".PRE."_videos_screens (videoid, thumbnail, picture) VALUES ('".$_REQUEST['id']."', '".addslashes($thumbfile)."', '".addslashes($newfile)."')");
			
			logit('VIDEOS_PADD','ID #'.$nextid);
			++$nextid;
		}
		
		printJSRedirect('action.php?action=videos.pshow&id='.$_REQUEST['id']);
		return;
	}
	else {
		for ( $i=1; $i<=$set['videos']['addpics']; $i++ ) {
			$_POST['watermark'.$i]=1;
			$_POST['allowcoms'.$i]=1;
			$_POST['allowrating'.$i]=1;
		}
	}
	
	//Felder ausgeben
	for ( $i=1; $i<=$set['videos']['addpics']; $i++ ) {
		$upload[$i]['CAPTION']=compatible_hsc($_POST['caption'.$i]);
		$upload[$i]['WATERMARK']=1;
		$upload[$i]['NORESIZE']=0;
		$upload[$i]['ALLOWCOMS']=1;
		$upload[$i]['ALLOWRATING']=1;
	}
	
	$apx->tmpl->assign('ID',$_REQUEST['id']);
	$apx->tmpl->assign('UPLOAD',$upload);
	
	$apx->tmpl->assign('SET_WATERMARK',iif($set['videos']['watermark'],1,0));
	$apx->tmpl->assign('SET_NORESIZE',iif($set['videos']['picwidth'] && $set['videos']['picheight'],1,0));
	
	$apx->tmpl->parse('padd');
}


//***************************** Bild löschen *****************************
function pdel() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$_REQUEST['id']=(int)$_REQUEST['id'];
			if ( !$_REQUEST['id'] ) die('missing ID!');
			if ( !$this->access_pics($_REQUEST['id'], 'videos.pdel') ) die('you have no right to access this video!');
			
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: action.php?action=videos.pshow&id='.$_REQUEST['id']);
				return;
			}
			
			if ( count($cache) ) {
				$pictures = $db->fetch("SELECT pictureid, image, thumbnail FROM ".PRE."_videos_screens WHERE videoid='".$_REQUEST['id']."' AND pictureid IN (".implode(',', $cache).")");
				if ( count($pictures) ) {
					foreach ( $pictures AS $pic ) {
						$thumbnail=$pic['thumbnail'];
						$picture=$pic['picture'];
						if ( $thumbnail && file_exists(BASEDIR.getpath('uploads').$thumbnail) ) $this->mm->deletefile($thumbnail);
						if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $this->mm->deletefile($picture);
						logit('VIDEOS_PDEL','ID #'.$pic['pictureid']);
					}
				}
				
				$db->query("DELETE FROM ".PRE."_videos_screens WHERE videoid='".$_REQUEST['id']."' AND pictureid IN (".implode(',', $cache).")");
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: action.php?action=videos.pshow&id='.$_REQUEST['id']);
		}
	}
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		$_REQUEST['dlid']=(int)$_REQUEST['dlid'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		if ( !$_REQUEST['dlid'] ) die('missing video ID!');
		if ( !$this->access_pics($_REQUEST['dlid'],'videos.pdel') ) die('you have no right to access this video!');
		
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				$id=$_REQUEST['id'];
				
				$pic = $db->first("SELECT thumbnail, picture FROM ".PRE."_videos_screens WHERE videoid='".$_REQUEST['dlid']."' AND pictureid='".$id."' LIMIT 1");
				$thumbnail=$pic['thumbnail'];
				$picture=$pic['picture'];
				if ( $thumbnail && file_exists(BASEDIR.getpath('uploads').$thumbnail) ) $this->mm->deletefile($thumbnail);
				if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $this->mm->deletefile($picture);
				
				//Eintrag löschen
				$db->query("DELETE FROM ".PRE."_videos_screens WHERE videoid='".$_REQUEST['dlid']."' AND pictureid='".$id."' LIMIT 1");
				
				logit('VIDEOS_PDEL','ID #'.$_REQUEST['id']);
				printJSRedirect('action.php?action=videos.pshow&id='.$_REQUEST['dlid']);
			}
		}
		else {
			tmessageOverlay('pdel',array('ID'=>$_REQUEST['id'],'DLID'=>$_REQUEST['dlid']));
		}
	}
}



////////////////////////////////////////////////////////////////////////////////////////// KATEGORIEN

//***************************** Kategorien zeigen *****************************
function catshow() {
	global $set,$db,$apx,$html;
	
	//Struktur reparieren
	if ( $_REQUEST['repair'] ) {
		$this->cat->repair();
		echo 'Repair done!';
		return;
	}
	
	quicklink('videos.catadd');
	
	//DnD-Hinweis
	if ( $apx->user->has_right('videos.edit') ) {
		echo '<p class="hint">'.$apx->lang->get('USEDND').'</p>';
	}
	
	$col[]=array('ID',0,'align="center"');
	$col[]=array('COL_CATNAME',75,'class="title"');
	$col[]=array('COL_VIDEOS',25,'align="center"');
	
	$data=$this->cat->getTree(array('title', 'open'));
	if ( count($data) ) {
		
		//Ausgabe erfolgt
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['open'] ) {
				list($videos)=$db->first("SELECT count(id) FROM ".PRE."_videos WHERE catid='".$res['id']."'");
			}
			
			$tabledata[$i]['COL1']=$res['id'];
			$tabledata[$i]['COL2']=replace($res['title']);
			$tabledata[$i]['COL3']=iif(isset($videos),$videos,'&nbsp;');
			$tabledata[$i]['CLASS'] = 'l'.($res['level']-1).($res['children'] ? ' haschildren' : '').($res['level']>1 ? ' hidden' : '').($res['iscat'] ? ' dark' : '');
			$tabledata[$i]['ID'] = 'node:'.$res['id'];
			
			//Optionen
			if ( $apx->user->has_right('videos.catedit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'videos.catedit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('videos.catdel') && !$videos ) $tabledata[$i]['OPTIONS'].='<span class="ifhasnochildren">'.optionHTMLOverlay('del.gif', 'videos.catdel', 'id='.$res['id'], $apx->lang->get('CORE_DEL')).'</span><span class="ifhaschildren"><img alt="" src="design/ispace.gif"/></span>';
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('videos.catclean') && $videos ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('clean.gif', 'videos.catclean', 'id='.$res['id'], $apx->lang->get('CLEAN'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Anordnen nur bei Unterkategorien
			/*$tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->user->has_right('videos.catmove') && $follow[$res['id']]['prev'] ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'videos.catmove', 'direction=up&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
			if ( $apx->user->has_right('videos.catmove') && $follow[$res['id']]['next'] ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'videos.catmove', 'direction=down&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
			*/
			
			unset($videos);
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	
	//Mit Unter-Kategorien
	echo '<div class="treeview" id="tree">';
	$html->table($col);
	echo '</div>';
	
	$open = $apx->session->get('videos_cat_open');
	$open = dash_unserialize($open);
	$opendata = array();
	foreach ( $open AS $catid ) {
		$opendata[] = array(
			'ID' => $catid
		);
	}
	$apx->tmpl->assign('OPEN', $opendata);
	$apx->tmpl->assign('EDIT_ALLOWED', $apx->user->has_Right('videos.edit'));
	$apx->tmpl->parse('catshow_js');
	
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Neue Kategorie *****************************
function catadd() {
	global $set,$db,$apx;
	
	if ( $_REQUEST['updateparent'] ) $_POST['open']=1;
	if ( !count($_POST['groupid']) || $_POST['groupid'][0]=='all' ) $_POST['groupid']=array('all');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['parent'] ) infoNotComplete();
		else {
			
			//WENN ROOT
			if ( $_POST['parent']=='root' ) {
				if ( $_POST['groupid'][0]=='all' ) $_POST['forgroup']='all';
				else $_POST['forgroup']=serialize($_POST['groupid']);
				
				$nid = $this->cat->createNode(0, array(
					'title' => $_POST['title'],
					'text' => $_POST['text'],
					'icon' => $_POST['icon'],
					'open' => $_POST['open'],
					'forgroup' => $_POST['forgroup']
				));
				logit('VIDEOS_CATADD','ID #'.$nid);
				
				//Beitrag der Kategorie hinzufügen
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
				}
				else {
					printJSRedirect('action.php?action=videos.catshow');
				}
			}
			
			//WENN NODE
			else {
				if ( $_POST['groupid'][0]=='all' ) $_POST['forgroup']='all';
				else $_POST['forgroup']=serialize($_POST['groupid']);
				
				$nid = $this->cat->createNode(intval($_POST['parent']), array(
					'title' => $_POST['title'],
					'text' => $_POST['text'],
					'icon' => $_POST['icon'],
					'open' => $_POST['open'],
					'forgroup' => $_POST['forgroup']
				));
				logit('VIDEOS_CATADD',"ID #".$nid);
				
				//Beitrag der Kategorie hinzufügen
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
				}
				else {
					printJSRedirect('action.php?action=videos.catshow');
				}
			}
		}
	}
	else {
		$_POST['open']=1;
		
		
		//Baum
		$catlist='<option value="root" style="font-weight:bold;">'.$apx->lang->get('ROOT').'</option>';
		$data=$this->cat->getTree(array('title'));
		if ( count($data) ) {
			$catlist.='<option value=""></option>';
			foreach ( $data AS $res ) {
				$catlist.='<option value="'.$res['id'].'"'.iif($_POST['parent']==$res['id'],' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;',($res['level']-1)).replace($res['title']).'</option>';
			}
		}
		
		//Benutzergruppen
		$data=$db->fetch("SELECT groupid,name FROM ".PRE."_user_groups WHERE ( gtype='admin' OR gtype='indiv' ) ORDER BY name ASC");
		$grouplist.='<option value="all"'.iif(!isset($_POST['groupid']) || $_POST['groupid'][0]=='all',' selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('ALL').'</option>';
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$grouplist.='<option value="'.$res['groupid'].'"'.iif(isset($_POST['groupid']) && in_array($res['groupid'],$_POST['groupid']),' selected="selected"').'>'.replace($res['name']).'</option>';
			}
		}
		
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('ICON',compatible_hsc($_POST['icon']));
		$apx->tmpl->assign('OPEN',(int)$_POST['open']);
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('USERGROUPS',$grouplist);
		$apx->tmpl->assign('ACTION','add');
		$apx->tmpl->assign('UPDATEPARENT',(int)$_REQUEST['updateparent']);
		
		$apx->tmpl->parse('catadd_catedit');
	}
}



//***************************** Kategorie bearbeiten *****************************
function catedit() {
global $set,$apx,$tmpl,$db,$user;
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( !count($_POST['groupid']) || $_POST['groupid'][0]=='all' ) $_POST['groupid']=array('all');
	
	if ( $_POST['send']==1 ) {
		list($videos)=$db->first("SELECT count(id) FROM ".PRE."_videos WHERE catid='".$_REQUEST['id']."'");
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['id'] || !$_POST['parent'] || !$_POST['title'] ) infoNotComplete();
		elseif ( !$_POST['open'] && $videos ) info($apx->lang->get('INFO_CONTAINSVIDEOS'));
		else {
			if ( $_POST['groupid'][0]=='all' ) $_POST['forgroup']='all';
			else $_POST['forgroup']=serialize($_POST['groupid']);
			
			$this->cat->moveNode($_REQUEST['id'], intval($_POST['parent']), array(
				'title' => $_POST['title'],
				'text' => $_POST['text'],
				'icon' => $_POST['icon'],
				'open' => $_POST['open'],
				'forgroup' => $_POST['forgroup']
			));
			logit('VIDEOS_CATEDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('videos.catshow'));
		}
	}
	else {
		$res = $this->cat->getNode($_REQUEST['id'], array('title', 'text', 'icon', 'open', 'forgroup'));
		$_POST['title'] = $res['title'];
		$_POST['text'] = $res['text'];
		$_POST['icon'] = $res['icon'];
		$_POST['open'] = $res['open'];
		if ( $res['forgroup']=='all' ) $_POST['groupid'][0]='all';
		else $_POST['groupid']=unserialize($res['forgroup']);
		if ( !$res['parents'] ) $_POST['parent'] = 'root';
		else $_POST['parent'] = array_pop($res['parents']);
		
		//Baum
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
		
		//Benutzergruppen
		$data=$db->fetch("SELECT groupid,name FROM ".PRE."_user_groups WHERE ( gtype='admin' OR gtype='indiv' ) ORDER BY name ASC");
		$grouplist.='<option value="all"'.iif(!isset($_POST['groupid']) || $_POST['groupid'][0]=='all',' selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('ALL').'</option>';
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$grouplist.='<option value="'.$res['groupid'].'"'.iif(isset($_POST['groupid']) && in_array($res['groupid'],$_POST['groupid']),' selected="selected"').'>'.replace($res['name']).'</option>';
			}
		}
		
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('ICON',compatible_hsc($_POST['icon']));
		$apx->tmpl->assign('OPEN',(int)$_POST['open']);
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('USERGROUPS',$grouplist);
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
	
	list($videos)=$db->first("SELECT count(id) FROM ".PRE."_videos WHERE catid='".$_REQUEST['id']."'");
	if ( $videos ) die('category still contains videos!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$this->cat->deleteNode($_REQUEST['id']);
			logit('VIDEOS_CATDEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('videos.catshow'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_videos_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
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
			$db->query("UPDATE ".PRE."_videos SET catid='".intval($_POST['moveto'])."' WHERE catid='".$_REQUEST['id']."'");
			logit('VIDEOS_CATCLEAN',"ID #".$_REQUEST['id']);
			
			//Kategorie löschen
			if ( $_POST['delcat'] ) {
				$this->cat->deleteNode($_REQUEST['id']);
				logit('VIDEOS_CATDEL',"ID #".$_REQUEST['id']);
			}
			
			printJSRedirect(get_index('videos.catshow'));
			return;
		}
	}
	
	$data=$this->cat->getTree(array('title', 'open'));
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',($res['level']-1));
			if ( $res['id']!=$_REQUEST['id'] && $res['open'] ) $catlist.='<option value="'.$res['id'].'" '.iif($_POST['moveto']==$res['id'],' selected="selected"').' style="color:green;">'.$space.replace($res['title']).'</option>';
			else $catlist.='<option value="" disabled="disabled" style="color:grey;">'.$space.replace($res['title']).'</option>';
		}
	}
	
	list($title,$children)=$db->first("SELECT title,children FROM ".PRE."_videos_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
	$children = dash_unserialize($children);
	
	$apx->tmpl->assign('ID',$_REQUEST['id']);
	$apx->tmpl->assign('TITLE',compatible_hsc($title));
	$apx->tmpl->assign('DELCAT',(int)$_POST['delcat']);
	$apx->tmpl->assign('DELETEABLE', !$children);
	$apx->tmpl->assign('CATLIST',$catlist);
	
	tmessageOverlay('catclean');
}



////////////////////////////////////////////////////////////////////////////////////////// STATS

function stats() {
	global $set,$db,$apx;
	$datestamp=date('Ymd',time()-TIMEDIFF);
	
	list($count_files)=$db->first("SELECT count(id) FROM ".PRE."_videos WHERE starttime!='0'");
	$count_dlsperday=$this->stats_dlsperday();
	$count_sizeperday=$this->stats_sizeperday();
	
	list($count_all,$size[0])=$db->first("SELECT sum(hits),sum(bytes*hits) FROM ".PRE."_videos_stats");
	list($count_week,$size[1])=$db->first("SELECT sum(hits),sum(bytes*hits) FROM ".PRE."_videos_stats WHERE daystamp BETWEEN '".date('Ymd',time()-6*24*3600-TIMEDIFF)."' AND '".date('Ymd',time()-TIMEDIFF)."'");
	list($count_today,$size[2])=$db->first("SELECT sum(hits),sum(bytes*hits) FROM ".PRE."_videos_stats WHERE daystamp='".date('Ymd',time()-TIMEDIFF)."'");
	
	$apx->tmpl->assign('FILES',$count_files);
	$apx->tmpl->assign('DLS_PERDAY',$count_dlsperday);
	$apx->tmpl->assign('SIZE_PERDAY',$count_sizeperday);
	$apx->tmpl->assign('DLS_ALL',(int)$count_all);
	$apx->tmpl->assign('DLS_WEEK',(int)$count_week);
	$apx->tmpl->assign('DLS_TODAY',(int)$count_today);
	$apx->tmpl->assign('SIZE_ALL',$this->format_size($size[0]));
	$apx->tmpl->assign('SIZE_WEEK',$this->format_size($size[1]));
	$apx->tmpl->assign('SIZE_TODAY',$this->format_size($size[2]));
	
	//Die letzten 50 Tage
	if ( $_REQUEST['show']=='size' ) {
		$data=$db->fetch("SELECT sum(bytes*hits) AS count,daystamp,time FROM ".PRE."_videos_stats WHERE daystamp>='".date('Ymd',time()-50*24*3600-TIMEDIFF)."' GROUP BY daystamp ORDER BY daystamp ASC");
		$apx->tmpl->assign('GRAPH_HEADLINE',$apx->lang->get('TRAFFIC'));
	}
	else {
		$data=$db->fetch("SELECT sum(hits) AS count,daystamp,time FROM ".PRE."_videos_stats WHERE daystamp>='".date('Ymd',time()-50*24*3600-TIMEDIFF)."' GROUP BY daystamp ORDER BY daystamp ASC");
		$apx->tmpl->assign('GRAPH_HEADLINE',$apx->lang->get('DOWNLOADS'));
	}
	
	if ( count($data) ) {
	
		//Maximum holen
		foreach ( $data AS $res ) {
			if ( $res['count']>$max ) $max=$res['count'];
		}
		
		//Base generieren
		if ( $_REQUEST['show']=='size' ) {
			if ( strlen($max)>3 ) {
				$pot=floor(strlen($max)/3);
				$multi=floor(strlen($max)%3);
				if ( $multi==0 ) {
					--$pot;
					$multi=3;
				}
			
				for ( $i=1; $i<=10; $i++ ) {
					$base=pow(1024,$pot)*pow(10,$multi-1)*$i;
					if ( $base>=$max ) break;
				}
			}
			else {
				for ( $i=1; $i<=10; $i++ ) {
					$base=pow(10,strlen($max)-1)*$i;
					if ( $base>=$max ) break;
				}
			}
		
			$apx->tmpl->assign('SCALE1',$this->format_size(round($base/4),0));
			$apx->tmpl->assign('SCALE2',$this->format_size(round($base/4*2),0));
			$apx->tmpl->assign('SCALE3',$this->format_size(round($base/4*3),0));
			$apx->tmpl->assign('SCALE4',$this->format_size(round($base),0));
		}
		else {
			if ( strlen($max)>1 ) {
				for ( $i=1; $i<=10; $i++ ) {
					$base=pow(10,(strlen($max)-1))*$i;
					if ( $base>=$max ) break;
				}
			}
			else $base=$max;
			
			$apx->tmpl->assign('SCALE1',round($base/4));
			$apx->tmpl->assign('SCALE2',round($base/4*2));
			$apx->tmpl->assign('SCALE3',round($base/4*3));
			$apx->tmpl->assign('SCALE4',$base);
		}
		
		//Statistik generieren
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $_REQUEST['show']=='size' ) $info=$this->format_size($res['count']);
			else $info=$res['count'].' '.$apx->lang->get('HITS');
			
			$statdata[$i]['DATE']=apxdate($res['time']);
			$statdata[$i]['INFO']=$info;
			$statdata[$i]['COUNT']=$res['count'];
			$statdata[$i]['HEIGHT']=round((($res['count']/$base)*299));
		}
	}
	
	//Beliebteste Videos
	$data=$db->fetch("SELECT sum(a.hits) AS count,b.id,b.title FROM ".PRE."_videos_stats AS a LEFT JOIN ".PRE."_videos AS b ON a.dlid=b.id WHERE time BETWEEN '".(time()-7*24*3600)."' AND '".time()."' GROUP BY dlid ORDER BY count DESC LIMIT 20");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$popdata[$i]['COUNT']=$res['count'];
			$popdata[$i]['TITLE']=strip_tags($res['title']);
			$popdata[$i]['LINK']=mklink(
				'videos.php?id='.$res['id'],
				'videos,id'.$res['id'].urlformat($res['title']).'.html',
				iif($set['main']['forcesection'],iif(unserialize_section($res['secid'])==array('all'),$apx->section_default,array_shift($tmp=unserialize_section($res['secid']))),0)
			);
		}
	}
	
	$apx->tmpl->assign('STAT',$statdata);
	$apx->tmpl->assign('POP',$popdata);
	
	$apx->tmpl->parse('stats');
}



////////////////////////////////////////////////////////////////////////////////////////// KONVERTER

//***************************** Konverter-Konfiguration *****************************
function cfg() {
	global $set,$db,$apx;
	
	if ( $_POST['send']==1 ) {
		
		$invalid = array();
		if ( $_POST['ffmpeg'] && !$this->validateExecPath('ffmpeg', $_POST['ffmpeg']) ) $invalid[] = 'FFmpeg';
		if ( $_POST['flvtool2'] && !$this->validateExecPath('flvtool2', $_POST['flvtool2']) ) $invalid[] = 'FLVTool2';
		if ( $_POST['mencoder'] && !$this->validateExecPath('mencoder', $_POST['mencoder']) ) $invalid[] = 'MEncoder';
		
		if ( !$_POST['ffmpeg'] || !$_POST['flvtool2'] ) info($apx->lang->get('CORE_BACK'));
		elseif ( $invalid ) info($apx->lang->get('INFO_INVALID').implode(', ', $invalid));
		else {
			
			$db->query("UPDATE ".PRE."_config SET value='".addslashes($_POST['ffmpeg'])."' WHERE module='videos' AND varname='ffmpeg' LIMIT 1");
			$db->query("UPDATE ".PRE."_config SET value='".addslashes($_POST['flvtool2'])."' WHERE module='videos' AND varname='flvtool2' LIMIT 1");
			$db->query("UPDATE ".PRE."_config SET value='".addslashes($_POST['mencoder'])."' WHERE module='videos' AND varname='mencoder' LIMIT 1");
			
			logit('VIDEOS_CFG');
			printJSRedirect('action.php?action=videos.cfg'); 
		}
	}
	else {
		if ( !function_exists('exec') ) {
			message($apx->lang->get('MSG_EXEC_DISABLED'));
			return;
		}
		
		$apx->tmpl->assign('FFMPEG', compatible_hsc($set['videos']['ffmpeg']));
		$apx->tmpl->assign('FLVTOOL2', compatible_hsc($set['videos']['flvtool2']));
		$apx->tmpl->assign('MENCODER', compatible_hsc($set['videos']['mencoder']));
		
		$apx->tmpl->parse('cfg');
	}
}


} //END CLASS


?>
