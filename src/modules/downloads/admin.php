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


# DOWNLOADS CLASS
# ===============

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Funktionen laden
include(BASEDIR.getmodulepath('downloads').'admin_extend.php');


class action extends downloads_functions {

var $cat;

//Startup
function action() {
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$this->cat=new RecursiveTree(PRE.'_downloads_cat', 'id');
	
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$this->mm=new mediamanager;
}



////////////////////////////////////////////////////////////////////////////////////////// ARTICLE

//***************************** Downloads zeigen *****************************
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
		
		$data=$db->fetch("SELECT id FROM ".PRE."_downloads WHERE 1 ".$where);
		$ids = get_ids($data, 'id');
		$ids[] = -1;
		$searchid = saveSearchResult('admin_downloads', $ids, array(
			'item' => $_REQUEST['item'],
			'title' => $_REQUEST['title'],
			'text' => $_REQUEST['text'],
			'catid' => $_REQUEST['catid'],
			'secid' => $_REQUEST['secid'],
			'userid' => $_REQUEST['userid']
		));
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: action.php?action=downloads.show&what='.$_REQUEST['what'].'&searchid='.$searchid);
		return;
	}
	
	
	//Unbroken setzen
	$_REQUEST['unbroken']=(int)$_REQUEST['unbroken'];
	if ( $_REQUEST['unbroken'] ) {
		$db->query("UPDATE ".PRE."_downloads SET broken='' WHERE id='".$_REQUEST['unbroken']."' LIMIT 1");
	}
	
	
	//Vorgaben
	$_REQUEST['title'] = 1;
	$_REQUEST['text'] = 1;
	
	quicklink('downloads.add');
	
	$layerdef[]=array('LAYER_ALL','action.php?action=downloads.show',!$_REQUEST['what']);
	$layerdef[]=array('LAYER_SEND','action.php?action=downloads.show&amp;what=send',$_REQUEST['what']=='send');
	$layerdef[]=array('LAYER_BROKEN','action.php?action=downloads.show&amp;what=broken',$_REQUEST['what']=='broken');
	
	//Layer Header ausgeben
	$html->layer_header($layerdef);
	
	$orderdef[0]='creation';
	$orderdef['title']=array('a.title','ASC','COL_TITLE');
	$orderdef['user']=array('b.username','ASC','COL_UPLOADER');
	$orderdef['category']=array('c.title','ASC','COL_CATEGORY');
	$orderdef['creation']=array('a.addtime','DESC','SORT_ADDTIME');
	$orderdef['publication']=array('a.starttime','DESC','SORT_STARTTIME');
	$orderdef['hits']=array('a.hits','DESC','COL_HITS');
	
	
	//Suchergebnis?
	$resultFilter = '';
	if ( $_REQUEST['searchid'] ) {
		$searchRes = getSearchResult('admin_downloads', $_REQUEST['searchid']);
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
	$data=$db->fetch("SELECT b.userid,b.username FROM ".PRE."_downloads AS a LEFT JOIN ".PRE."_user AS b USING (userid) WHERE a.userid!=0 GROUP BY userid ORDER BY username ASC");
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
	elseif ( $_REQUEST['what']=='send' ) {
		$layerFilter = " AND a.send_ip!='' ";
	}
	else {
		$layerFilter = " AND tempfile='' ";
	}
	
	
	list($count)=$db->first("SELECT count(userid) FROM ".PRE."_downloads AS a WHERE 1 ".$resultFilter.$layerFilter.section_filter(true, 'secid'));
	pages('action.php?action=downloads.show&amp;what='.$_REQUEST['what'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']).'&amp;sortby='.$_REQUEST['sortby'],$count);
	$data=$db->fetch("SELECT a.id,a.secid,a.send_username,a.title,a.file,a.tempfile,a.addtime,a.allowcoms,a.allowrating,a.starttime,a.endtime,a.broken,a.hits,b.userid,b.username,c.title AS catname FROM ".PRE."_downloads AS a LEFT JOIN ".PRE."_user AS b USING(userid) LEFT JOIN ".PRE."_downloads_cat AS c ON a.catid=c.id WHERE 1 ".$resultFilter.$layerFilter.section_filter(true, 'a.secid')." ".getorder($orderdef).getlimit());
	$this->show_print($data);
	orderstr($orderdef,'action.php?action=downloads.show&amp;what='.$_REQUEST['what'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']));
	save_index($_SERVER['REQUEST_URI']);
	
	
	//Layer-Footer ausgeben
	$html->layer_footer();
}


//Downloads auflisten
function show_print($data) {
	global $set,$db,$apx,$html;
	
	$col[]=array('',1,'align="center"');
	$col[]=array($apx->lang->get('COL_TITLE').' / '.$apx->lang->get('COL_UPLOADER'),45,'class="title"');
	$col[]=array('COL_CATEGORY',25,'align="center"');
	$col[]=array('COL_ADDTIME',20,'align="center"');
	$col[]=array('COL_DOWNLOADS',10,'align="center"');

	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( !$res['starttime'] ) $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['endtime']<time() ) $tabledata[$i]['COL1']='<img src="design/greendotcross.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['starttime']>time() ) $tabledata[$i]['COL1']='<img src="design/greendotwait.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			
			$tmp=unserialize_section($res['secid']);
			$title=shorttext(strip_tags($res['title']),40);
			$link=mklink(
				'downloads.php?id='.$res['id'],
				'downloads,id'.$res['id'].urlformat($res['title']).'.html',
				iif($set['main']['forcesection'],iif(unserialize_section($res['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
			);
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['COL2'].='<a href="'.$link.'" target="_blank">'.$title.'</a>';
			$tabledata[$i]['COL3']=replace($res['catname']);
			$tabledata[$i]['COL4']=iif($res['starttime'],mkdate($res['starttime'],'<br />'),'&nbsp;');
			$tabledata[$i]['COL5']=$res['hits'];
			
			if ( $res['username'] ) $tabledata[$i]['COL2'].='<br /><small>'.$apx->lang->get('BY').' '.replace($res['username']).'</small>';
			else  $tabledata[$i]['COL2'].='<br /><small>'.$apx->lang->get('BY').' '.$apx->lang->get('GUEST').': <i>'.replace($res['send_username']).'</i></small>';
			if ( $res['broken'] ) {
				$tabledata[$i]['COL2']='<a href="action.php?action=downloads.show&amp;what='.$_REQUEST['what'].'&amp;p='.$_REQUEST['p'].'&amp;item='.$_REQUEST['item'].'&amp;sortby='.$_REQUEST['sortby'].'&amp;title='.$_REQUEST['title'].'&amp;text='.$_REQUEST['text'].'&amp;catid='.$_REQUEST['catid'].'&amp;unbroken='.$res['id'].'"><img src="../'.getmodulepath('downloads').'images/broken.gif" alt="'.$apx->lang->get('BROKEN').': '.mkdate($res['broken']).'" title="'.$apx->lang->get('BROKEN').': '.mkdate($res['broken']).'" align="right" /></a>'.$tabledata[$i]['COL2'];
			}
			
			//Optionen
			if ( $apx->user->has_right('downloads.edit') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('downloads.edit') ) ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'downloads.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('downloads.del') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('downloads.del') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'downloads.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( ( !$res['starttime'] || $res['endtime']<time() ) && $apx->user->has_right('downloads.enable') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('downloads.enable') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('enable.gif', 'downloads.enable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
			elseif ( $res['starttime'] && $apx->user->has_right('downloads.disable') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('downloads.disable') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('disable.gif', 'downloads.disable', 'id='.$res['id'], $apx->lang->get('CORE_DISABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			$tabledata[$i]['OPTIONS'].='&nbsp;';
			
			if ( $apx->user->has_right('downloads.pshow') ) $tabledata[$i]['OPTIONS'].=optionHTML('pic.gif', 'downloads.pshow', 'id='.$res['id'], $apx->lang->get('PICS'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Kommentare + Bewertungen
			if ( $apx->is_module('comments') || $apx->is_module('ratings') ) $tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->is_module('comments') ) {
				list($comments)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='downloads' AND mid='".$res['id']."' )");
				if ( $comments && ( $apx->is_module('comments') && $set['downloads']['coms'] ) && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=downloads&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			if ( $apx->is_module('ratings') ) {
				list($ratings)=$db->first("SELECT count(id) FROM ".PRE."_ratings WHERE ( module='downloads' AND mid='".$res['id']."' )");
				if ( $ratings && ( $apx->is_module('ratings') && $set['downloads']['ratings'] ) && $res['allowrating'] && $apx->user->has_right('ratings.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('ratings.gif', 'ratings.show', 'module=downloads&mid='.$res['id'], $apx->lang->get('RATINGS').' ('.$ratings.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$multiactions = array();
	if ( $apx->user->has_right('downloads.del') ) $multiactions[] = array($apx->lang->get('CORE_DEL'), 'action.php?action=downloads.del', false);
	if ( $apx->user->has_right('downloads.enable') ) $multiactions[] = array($apx->lang->get('CORE_ENABLE'), 'action.php?action=downloads.enable', false);
	if ( $apx->user->has_right('downloads.disable') ) $multiactions[] = array($apx->lang->get('CORE_DISABLE'), 'action.php?action=downloads.disable', false);
	$html->table($col, $multiactions);
}



//***************************** Neuer Download *****************************
function add() {
	global $set,$db,$apx;
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( $_FILES['file_upload']['error']==1 ) info($apx->lang->get('INFO_TOOBIG'));
		elseif ( !$_POST['title'] || !$_POST['catid'] || !$_POST['text'] || ( !$_POST['file'] && !$_FILES['file_upload']['tmp_name'] ) || ( !$_POST['local'] && !$_POST['filesize'] ) ) infoNotComplete();
		elseif ( !$this->category_is_open($_POST['catid']) ) die('you are not allowed to add downloads to this category!');
		elseif ( !$this->update_file() ) { /*do nothing*/ }
		elseif ( !$this->update_teaserpic() ) { /*DO NOTHING*/ }
		else {
			//Dateigröße
			if ( $_POST['local'] ) $_POST['filesize']=0;
			else {
				if ( $_POST['filesize_format']=='kb' ) $_POST['filesize']=(int)1024*(float)str_replace(',','.',$_POST['filesize']);
				elseif ( $_POST['filesize_format']=='mb' ) $_POST['filesize']=(int)1024*1024*(float)str_replace(',','.',$_POST['filesize']);
				elseif ( $_POST['filesize_format']=='gb' ) $_POST['filesize']=(int)1024*1024*1024*(float)str_replace(',','.',$_POST['filesize']);
			}
			
			//Mirrors
			$mirrorlist=array();
			for ( $i=1; $i<=20; $i++ ) {
				if ( !$_POST['mirror'.$i.'_title'] || !$_POST['mirror'.$i.'_url'] ) continue;
				$mirrorlist[]=array(
					'title' => $_POST['mirror'.$i.'_title'],
					'url' => $_POST['mirror'.$i.'_url']
				);
			}
			
			//Veröffentlichung
			if ( $apx->user->has_right('downloads.enable') && $_POST['pubnow'] ) {
				$addfield=',starttime,endtime';
				$_POST['starttime']=time();
				$_POST['endtime']='3000000000';
			}
			
			$_POST['secid']=serialize_section($_POST['secid']);
			$_POST['addtime']=time();
			$_POST['mirrors']=serialize($mirrorlist);
			$_POST['file']=$this->filepath;
			$_POST['teaserpic']=$this->teaserpicpath;
			
			//Autor
			if ( !$apx->user->has_spright('downloads.edit') ) $_POST['userid']=$apx->user->info['userid'];
			
			$db->dinsert(PRE.'_downloads','secid,prodid,catid,userid,file,filesize,format,local,title,text,teaserpic,meta_description,galid,author,author_link,mirrors,password,limit,addtime,searchable,restricted,allowcoms,allowrating,top,regonly'.$addfield);
			$nid=$db->insert_id();
			logit('DOWNLOADS_ADD','ID #'.$nid);
			
			//Tags
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_downloads_tags VALUES('".$nid."', '".$tagid."')");
			}
			
			if ( $_POST['submit_padd'] ) printJSRedirect('action.php?action=downloads.padd&id='.$nid);
			else printJSRedirect('action.php?action=downloads.show');
		}
	}
	else {
		$_POST['local']=1;
		$_POST['searchable']=1;
		$_POST['allowcoms']=1;
		$_POST['allowrating']=1;
		$_POST['userid']=$apx->user->info['userid'];
		
		
		mediamanager('downloads');
		
		//Mirrors
		$mirrorlist=array();
		for ( $i=1; $i<=20; $i++ ) {
			if ( $i>1 && ( !$_POST['mirror'.$i.'_title'] || !$_POST['mirror'.$i.'_url'] ) ) continue;
			$mirrorlist[]=array(
				'TITLE' => compatible_hsc($_POST['mirror'.$i.'_title']),
				'URL' => compatible_hsc($_POST['mirror'.$i.'_url']),
				'DISPLAY' => 1
			);
		}
		
		while ( count($mirrorlist)<20 ) {
			$mirrorlist[]=array('TITLE' => '','URL' => '');
		}
		
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('PRODID',$_POST['prodid']);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('GALID',$_POST['galid']);
		$apx->tmpl->assign('CATLIST',$this->get_catlist());
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('AUTHOR',compatible_hsc($_POST['author']));
		$apx->tmpl->assign('AUTHOR_LINK',compatible_hsc($_POST['author_link']));
		$apx->tmpl->assign('FILE',compatible_hsc($_POST['file']));
		$apx->tmpl->assign('FILESIZE',compatible_hsc($_POST['filesize']));
		$apx->tmpl->assign('FILESIZE_FORMAT',$_POST['filesize_format']);
		$apx->tmpl->assign('FORMAT',compatible_hsc($_POST['format']));
		$apx->tmpl->assign('MIRROR',$mirrorlist);
		$apx->tmpl->assign('LOCAL',(int)$_POST['local']);
		$apx->tmpl->assign('PASSWORD',compatible_hsc($_POST['password']));
		$apx->tmpl->assign('LIMIT',(int)$_POST['limit']);
		
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWRATING',(int)$_POST['allowrating']);
		$apx->tmpl->assign('TOP',(int)$_POST['top']);
		$apx->tmpl->assign('REGONLY',(int)$_POST['regonly']);
		$apx->tmpl->assign('PUBNOW',(int)$_POST['pubnow']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('RESTRICTED',(int)$_POST['restricted']);
		
		$apx->tmpl->assign('ACTION','add');
		
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Download bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	//Sendfile
	list($tempfile,$filename)=$db->first("SELECT tempfile,file FROM ".PRE."_downloads WHERE id='".intval($_REQUEST['id'])."' LIMIT 1");
	
	//DATEI AKTIVIEREN
	if ( $_POST['enablefile'] && $tempfile ) {
		if ( !checkToken() ) infoInvalidToken();
		else {
			$this->edit_enable($tempfile,$filename);
			printJSRedirect('action.php?action=downloads.edit&id='.$_REQUEST['id']);
		}
	}
	
	//AKTUALISIEREN
	elseif ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( $_FILES['file_upload']['error']==1 ) info($apx->lang->get('INFO_TOOBIG'));
		elseif ( !$_POST['title'] || !$_POST['catid'] || !$_POST['text'] || ( !$_POST['file'] && !$_FILES['file_upload']['tmp_name'] ) || ( !$_POST['local'] && !$_POST['filesize'] ) ) infoNotComplete();
		elseif ( !$this->category_is_open($_POST['catid']) ) die('you are not allowed to add downloads to this category!');
		elseif ( !$this->update_file() ) { /*do nothing*/ }
		elseif ( !$this->update_teaserpic() ) { /*DO NOTHING*/ }
		else {
			//Dateigröße
			if ( $_POST['local'] ) $_POST['filesize']=0;
			else {
				if ( $_POST['filesize_format']=='kb' ) $_POST['filesize']=(int)1024*(float)str_replace(',','.',$_POST['filesize']);
				elseif ( $_POST['filesize_format']=='mb' ) $_POST['filesize']=(int)1024*1024*(float)str_replace(',','.',$_POST['filesize']);
				elseif ( $_POST['filesize_format']=='gb' ) $_POST['filesize']=(int)1024*1024*1024*(float)str_replace(',','.',$_POST['filesize']);
			}
			
			//Mirrors
			$mirrorlist=array();
			for ( $i=1; $i<=20; $i++ ) {
				if ( !$_POST['mirror'.$i.'_title'] || !$_POST['mirror'.$i.'_url'] ) continue;
				$mirrorlist[]=array(
					'title' => $_POST['mirror'.$i.'_title'],
					'url' => $_POST['mirror'.$i.'_url']
				);
			}
			
			//Autor
			if ( $apx->user->has_spright('downloads.edit') && $_POST['userid'] ) {
				if ( $_POST['userid']=='send' ) $_POST['userid']=0;
				else $_POST['userid']=$_POST['userid'];
				$addfields.=',userid';
			}
			
			//Veröffentlichung
			if ( $apx->user->has_right('downloads.enable') && isset($_POST['t_day_1']) ) {
				$_POST['starttime']=maketime(1);
				$_POST['endtime']=maketime(2);
				if ( $_POST['starttime'] ) {
				if ( !$_POST['endtime'] || $_POST['endtime']<=$_POST['starttime'] ) $_POST['endtime']=3000000000;
				$addfields.=',starttime,endtime';
				}
			}
			
			$_POST['secid']=serialize_section($_POST['secid']);
			$_POST['mirrors']=serialize($mirrorlist);
			$_POST['file']=$this->filepath;
			$_POST['tempfile']=$this->tempfile;
			$_POST['teaserpic']=$this->teaserpicpath;
			
			$db->dupdate(PRE.'_downloads','secid,prodid,catid,file,tempfile,filesize,format,local,title,text,teaserpic,meta_description,galid,author,author_link,mirrors,allowcoms,allowrating,top,regonly,searchable,restricted,limit,password'.$addfields,"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('DOWNLOADS_EDIT','ID #'.$_REQUEST['id']);
			
			//Tags
			$db->query("DELETE FROM ".PRE."_downloads_tags WHERE id='".$_REQUEST['id']."'");
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_downloads_tags VALUES('".$_REQUEST['id']."', '".$tagid."')");
			}
			
			printJSRedirect(get_index('downloads.show'));
		}
	}
	else {
		$res=$db->first("SELECT * FROM ".PRE."_downloads WHERE id='".$_REQUEST['id']."' LIMIT 1",1);
		foreach ( $res AS $key => $val ) $_POST[$key]=$val;
		$_POST['secid']=unserialize_section($_POST['secid']);
		
		//Autor
		if ( !$res['userid'] ) $_POST['userid']='send';
		
		//Veröffentlichung
		if ( $res['starttime'] ) {
			maketimepost(1,$res['starttime']);
			if ( $res['endtime']<2147483647 ) maketimepost(2,$res['endtime']);
		}
		
		//Mirrors
		$mirrors=unserialize($res['mirrors']);
		if ( is_array($mirrors) && count($mirrors) ) {
			foreach ( $mirrors AS $res ) {
				++$i;
				$_POST['mirror'.$i.'_title']=$res['title'];
				$_POST['mirror'.$i.'_url']=$res['url'];
			}
		}
		
		
		mediamanager('downloads');
		
		//Mirrors
		$mirrorlist=array();
		for ( $i=1; $i<=20; $i++ ) {
			if ( $i>1 && ( !$_POST['mirror'.$i.'_title'] || !$_POST['mirror'.$i.'_url'] ) ) continue;
			$mirrorlist[]=array(
				'TITLE' => compatible_hsc($_POST['mirror'.$i.'_title']),
				'URL' => compatible_hsc($_POST['mirror'.$i.'_url']),
				'DISPLAY' => 1
			);
		}
		
		while ( count($mirrorlist)<20 ) {
			$mirrorlist[]=array('TITLE' => '','URL' => '');
		}
		
		//Autor
		if ( $apx->user->has_spright('downloads.edit') ) {
			$apx->tmpl->assign('USERLIST',$this->get_userlist());
		}
		
		//Veröffentlichung
		if ( $apx->user->has_right('downloads.enable') && isset($_POST['t_day_1']) ) {
			$apx->tmpl->assign('STARTTIME',choosetime(1,0,maketime(1)));
			$apx->tmpl->assign('ENDTIME',choosetime(2,1,maketime(2)));
		}
		
		//Einsende-User beachten
		$send=$db->first("SELECT send_username,send_email FROM ".PRE."_downloads WHERE id='".$_REQUEST['id']."' LIMIT 1");
		if ( $send['send_username'] ) {
			$usersend='<option value="send"'.iif($_POST['userid']=='send',' selected="selected"').'>'.$apx->lang->get('GUEST').': '.$send['send_username'].iif($send['send_email'],' ('.$send['send_email'].')').'</option>';
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
		
		//Tags
		$tags = array();
		$tagdata = $db->fetch("
			SELECT t.tag
			FROM ".PRE."_downloads_tags AS n
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE n.id='".$_REQUEST['id']."'
			ORDER BY t.tag ASC
		");
		$tags = get_ids($tagdata, 'tag');
		$_POST['tags'] = implode(', ', $tags);
		
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('PRODID',$_POST['prodid']);
		$apx->tmpl->assign('USER_SEND',$usersend);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('GALID',$_POST['galid']);
		$apx->tmpl->assign('CATLIST',$this->get_catlist());
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('TEASERPIC',$teaserpic);
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('AUTHOR',compatible_hsc($_POST['author']));
		$apx->tmpl->assign('AUTHOR_LINK',compatible_hsc($_POST['author_link']));
		$apx->tmpl->assign('FILE',compatible_hsc($_POST['file']));
		$apx->tmpl->assign('TEMPFILE',$tempfile);
		$apx->tmpl->assign('TEMPFILE_URL','../'.getpath('uploads').$tempfile);
		$apx->tmpl->assign('FILESIZE',compatible_hsc($_POST['filesize']));
		$apx->tmpl->assign('FILESIZE_FORMAT',$_POST['filesize_format']);
		$apx->tmpl->assign('FORMAT',compatible_hsc($_POST['format']));
		$apx->tmpl->assign('MIRROR',$mirrorlist);
		$apx->tmpl->assign('LOCAL',(int)$_POST['local']);
		$apx->tmpl->assign('PASSWORD',compatible_hsc($_POST['password']));
		$apx->tmpl->assign('LIMIT',(int)$_POST['limit']);
		
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWRATING',(int)$_POST['allowrating']);
		$apx->tmpl->assign('TOP',(int)$_POST['top']);
		$apx->tmpl->assign('REGONLY',(int)$_POST['regonly']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('RESTRICTED',(int)$_POST['restricted']);
		
		$apx->tmpl->assign('ACTION','edit');
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		
		$apx->tmpl->parse('add_edit');
	}
}


function edit_enable($tempfile,$filename) {
	global $set,$db,$apx;
	
	$filename=$this->get_uploaded_name($filename);
	$this->mm->movefile($tempfile,'downloads/'.$filename);
	$db->query("UPDATE ".PRE."_downloads SET tempfile='',file='downloads/".addslashes($filename)."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
	
	$_POST['file']='downloads/'.$filename;
}



//***************************** Download löschen *****************************
function del() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('downloads.show'));
				return;
			}
			
			//Dateien löschen
			$data=$db->first("SELECT id,file,tempfile,teaserpic,pictures FROM ".PRE."_downloads WHERE ( id IN ( ".implode(',',$cache)." ) ".iif(!$apx->user->has_spright('downloads.del')," AND userid='".$apx->user->info['userid']."'")." )");
			$db->query("DELETE FROM ".PRE."_downloads WHERE ( id IN ( ".implode(',',$cache)." ) ".iif(!$apx->user->has_spright('downloads.del')," AND userid='".$apx->user->info['userid']."'")." )");
			$cache=array();
			if ( !is_array($data) || !count($data) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('downloads.show'));
				return;
			}
			
			$picture = $data['teaserpic'];
			$poppic=str_replace('-thumb.','.',$articlespic);
			if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $mm->deletefile($picture);
			if ( $poppic && file_exists(BASEDIR.getpath('uploads').$poppic) ) $mm->deletefile($poppic);
			
			foreach ( $data AS $res ) {
				$pictures=unserialize($pictures);
				if ( is_array($pictures) && count($pictures) ) {
					foreach ( $pictures AS $res ) {
						$this->mm->deletefile($res['picture']);
						$this->mm->deletefile($res['thumbnail']);
					}
				}
				
				if ( $res['file'] && file_exists(BASEDIR.getpath('uploads').$res['file']) ) $this->mm->deletefile($res['file']);
				if ( $res['tempfile'] && file_exists(BASEDIR.getpath('uploads').$res['tempfile']) ) $this->mm->deletefile($res['tempfile']);
				if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='downloads' AND mid='".$res['id']."' )");
				if ( $apx->is_module('ratings') ) $db->query("DELETE FROM ".PRE."_ratings WHERE ( module='downloads' AND mid='".$res['id']."' )");
				
				//Tags löschen
				$db->query("DELETE FROM ".PRE."_downloads_tags WHERE id='".$res['id']."'");
				
				$cache[]=$res['id'];
				
				foreach ( $cache AS $id ) logit('DOWNLOADS_PDEL',"ID #".$id);
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('downloads.show'));
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
				list($file,$tempfile,$pictures)=$db->first("SELECT file,tempfile,pictures FROM ".PRE."_downloads WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('downloads.del')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
				$db->query("DELETE FROM ".PRE."_downloads WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('downloads.del')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
				if ( !$db->affected_rows() ) die('access denied!');
				
				//Dateien löschen
				if ( $_POST['delfile'] ) {
					if ( $file && file_exists(BASEDIR.getpath('uploads').$file) ) $this->mm->deletefile($file);
					if ( $tempfile && file_exists(BASEDIR.getpath('uploads').$tempfile) ) $this->mm->deletefile($tempfile);
					$pictures=unserialize($pictures);
					if ( is_array($pictures) && count($pictures) ) {
						foreach ( $pictures AS $res ) {
							$this->mm->deletefile($res['picture']);
							$this->mm->deletefile($res['thumbnail']);
						}
					}
				}
				
				if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='downloads' AND mid='".$_REQUEST['id']."' )");
				if ( $apx->is_module('ratings') ) $db->query("DELETE FROM ".PRE."_ratings WHERE ( module='downloads' AND mid='".$_REQUEST['id']."' )");
				
				//Tags löschen
				$db->query("DELETE FROM ".PRE."_downloads_tags WHERE id='".$_REQUEST['id']."'");
				
				logit('DOWNLOADS_DEL','ID #'.$_REQUEST['id']);
				printJSRedirect(get_index('downloads.show'));
			}
		}
		else {
			list($title, $local, $filename) = $db->first("SELECT title, local, file FROM ".PRE."_downloads WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
			if ( $local ) {
				$filename = basename($filename);
				$apx->tmpl->assign('FILENAME', compatible_hsc($filename));
			}
			tmessageOverlay('del',array('ID'=>$_REQUEST['id']));
		}
	}
}



//***************************** Download aktivieren *****************************
function enable() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('downloads.show'));
				return;
			}
			
			$data=$db->fetch("SELECT id,file FROM ".PRE."_downloads WHERE ( id IN (".implode(',',$cache).") ".iif(!$apx->user->has_spright('downloads.enable')," AND userid='".$apx->user->info['userid']."'")." )");
			$cache=array();
			if ( !is_array($data) || !count($data) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('downloads.show'));
				return;
			}
			
			foreach ( $data AS $res ) {
				if ( !$res['file'] ) continue;
				$cache[]=$res['id'];
			}
			
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('downloads.show'));
				return;
			}
			
			$db->query("UPDATE ".PRE."_downloads SET starttime='".time()."',endtime='3000000000' WHERE id IN (".implode(',',$cache).") AND tempfile=''");
			foreach ( $cache AS $id ) logit('DOWNLOADS_ENABLE','ID #'.$id);
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('downloads.show'));
		}
	}
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		
		list($check)=$db->first("SELECT tempfile FROM ".PRE."_downloads WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('downloads.enable')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
		if ( $check ) die('can not enable download! missing file!');
		
		if ( $_POST['send']==1 ) {
			$starttime=maketime(1);
			$endtime=maketime(2);
			if ( !$endtime || $endtime<=$starttime ) $endtime=3000000000;
			
			$db->query("UPDATE ".PRE."_downloads SET starttime='".$starttime."',endtime='".$endtime."' WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('downloads.enable')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			logit('DOWNLOADS_ENABLE','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('downloads.show'));
		}
		else {
			list($title) = $db->first("SELECT title FROM ".PRE."_downloads WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$apx->tmpl->assign('TITLE', compatible_hsc($title));
			$apx->tmpl->assign('ID',$_REQUEST['id']);
			$apx->tmpl->assign('STARTTIME',choosetime(1,0,time()));
			$apx->tmpl->assign('ENDTIME',choosetime(2,1));
			tmessageOverlay('enable');
		}
	}
}



//***************************** Download widerrufen *****************************
function disable() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('downloads.show'));
				return;
			}
			
			$data=$db->fetch("SELECT id FROM ".PRE."_downloads WHERE ( id IN (".implode(',',$cache).") ".iif(!$apx->user->has_spright('downloads.disable')," AND userid='".$apx->user->info['userid']."'")." )");
			$cache=array();
			if ( !is_array($data) || !count($data) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('downloads.show'));
				return;
			}
			
			foreach ( $data AS $res ) {
				$cache[]=$res['id'];
			}
			
			if ( !count($cache) ) return;
			
			$db->query("UPDATE ".PRE."_downloads SET starttime='0',endtime='0' WHERE id IN (".implode(',',$cache).")");
			foreach ( $cache AS $id ) logit('DOWNLOADS_DISABLE','ID #'.$id);
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('downloads.show'));
		}
	}
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
	
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				$db->query("UPDATE ".PRE."_downloads SET starttime=0,endtime=0 WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('downloads.disable')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
				logit('DOWNLOADS_DISABLE','ID #'.$_REQUEST['id']);
				printJSRedirect(get_index('downloads.show'));
			}
		}
		else {
			list($title) = $db->first("SELECT title FROM ".PRE."_downloads WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
			tmessageOverlay('disable',array('ID'=>$_REQUEST['id']));
		}
	}
}



////////////////////////////////////////////////////////////////////////////////////////// BILDER

//***************************** Bilder zeigen *****************************
function pshow() {
	global $set,$db,$apx,$html;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $this->access_pics($_REQUEST['id'],'downloads.padd') ) quicklink('downloads.padd','action.php','id='.$_REQUEST['id']); 
	
	$col[]=array('COL_THUMBNAIL',100,'align="center"');
	
	list($title)=$db->first("SELECT title FROM ".PRE."_downloads WHERE id='".$_REQUEST['id']."' LIMIt 1");
	echo'<h2>'.$apx->lang->get('DOWNLOAD').': '.$title.'</h2>';
	
	$res=$db->first("SELECT pictures FROM ".PRE."_downloads WHERE id='".$_REQUEST['id']."'");
	$pictures=unserialize($res['pictures']);
	
	if ( is_array($pictures) && count($pictures) ) {
		foreach ( $pictures AS $key => $res ) {
			++$i;
			
			$tabledata[$i]['ID']=$key;
			$tabledata[$i]['COL1']='<a href="../'.getpath('uploads').$res['picture'].'" target="_blank"><img src="../'.getpath('uploads').$res['thumbnail'].'" alt="thumbnail" /></a>';
			
			//Optionen
			if ( $apx->user->has_right('downloads.pdel') && $this->access_pics($_REQUEST['id'],'downloads.pdel') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'downloads.pdel', 'id='.$key.'&dlid='.$_REQUEST['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$multiactions = array();
	if ( $this->access_pics($_REQUEST['id'], 'downloads.pdel') ) $multiactions[] = array($apx->lang->get('CORE_DEL'), 'action.php?action=downloads.pdel&id='.$_REQUEST['id']);
	$html->table($col, $multiactions);
}



//***************************** Bilder anfügen *****************************
function padd() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( !$this->access_pics($_REQUEST['id'],'downloads.padd') ) die('you have no right to access this download!');

	if ( $_POST['send']==1 ) {
		require_once(BASEDIR.'lib/class.image.php');
		$img=new image;
		
		list($picturelist,$nextid)=$db->first("SELECT pictures,pictures_nextid FROM ".PRE."_downloads WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$picturelist=unserialize($picturelist);
		if ( !is_array($picturelist) ) $picturelist=array();
		
		//Bilder prüfen
		$error = array();
		for ( $i=1; $i<=$set['downloads']['addpics']; $i++ ) {
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
		for ( $i=1; $i<=$set['downloads']['addpics']; $i++ ) {
			if ( !$_FILES['upload'.$i]['tmp_name'] ) continue;
			
			$ext=strtolower($this->mm->getext($_FILES['upload'.$i]['name']));
			if ( $ext=='gif' ) $ext='jpg';
			
			$newname='pic'.'-'.$_POST['id'].'-'.$nextid.'.'.$ext;
			$newfile='downloads/pics/'.$newname;
			$thumbname='pic'.'-'.$_POST['id'].'-'.$nextid.'-thumb.'.$ext;
			$thumbfile='downloads/pics/'.$thumbname;
			
			//Erfolgreichen Upload prüfen
			if ( !$this->mm->uploadfile($_FILES['upload'.$i],'downloads/pics',$newname) ) continue;
			
			//Bild einlesen
			list($picture,$picturetype)=$img->getimage($newfile);
			
			
			//////// THUMBNAIL
			$thumbnail=$img->resize($picture,$set['downloads']['thumbwidth'],$set['downloads']['thumbheight'],$set['downloads']['quality_resize'],$set['downloads']['thumb_fit']);
			$img->saveimage($thumbnail,$picturetype,$thumbfile);
			
			
			//////// BILD
			
			//Bild skalieren
			if ( $picture!==false && !$_POST['noresize'.$i] && $set['downloads']['picwidth'] && $set['downloads']['picheight'] ) {
				$scaled=$img->resize(
					$picture,
					$set['downloads']['picwidth'],
					$set['downloads']['picheight'],
					$set['downloads']['quality_resize'],
					0
				);
				
				if ( $scaled!=$picture ) imagedestroy($picture);
				$picture=$scaled;
			}
			
			//Wasserzeichen einfügen
			if ( $picture!==false && $set['downloads']['watermark'] && $_POST['watermark'.$i] ) {
				$watermarked=$img->watermark(
					$picture,
					$set['downloads']['watermark'],
					$set['downloads']['watermark_position'],
					$set['downloads']['watermark_transp']
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
			
			$picturelist[$nextid]=array(
				'picture' => $newfile,
				'thumbnail' => $thumbfile
			);
			
			logit('DOWNLOADS_PADD','ID #'.$nextid);
			++$nextid;
		}
		
		$db->query("UPDATE ".PRE."_downloads SET pictures='".addslashes(serialize($picturelist))."',pictures_nextid='".intval($nextid)."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
		printJSRedirect('action.php?action=downloads.pshow&id='.$_REQUEST['id']);
		return;
	}
	else {
		for ( $i=1; $i<=$set['downloads']['addpics']; $i++ ) {
			$_POST['watermark'.$i]=1;
			$_POST['allowcoms'.$i]=1;
			$_POST['allowrating'.$i]=1;
		}
	}
	
	//Felder ausgeben
	for ( $i=1; $i<=$set['downloads']['addpics']; $i++ ) {
		$upload[$i]['CAPTION']=compatible_hsc($_POST['caption'.$i]);
		$upload[$i]['WATERMARK']=1;
		$upload[$i]['NORESIZE']=0;
		$upload[$i]['ALLOWCOMS']=1;
		$upload[$i]['ALLOWRATING']=1;
	}
	
	$apx->tmpl->assign('ID',$_REQUEST['id']);
	$apx->tmpl->assign('UPLOAD',$upload);
	
	$apx->tmpl->assign('SET_WATERMARK',iif($set['downloads']['watermark'],1,0));
	$apx->tmpl->assign('SET_NORESIZE',iif($set['downloads']['picwidth'] && $set['downloads']['picheight'],1,0));
	
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
			if ( !$this->access_pics($_REQUEST['id'], 'downloads.pdel') ) die('you have no right to access this download!');
			
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: action.php?action=downloads.pshow&id='.$_REQUEST['id']);
				return;
			}
			
			if ( count($cache) ) {
				list($picturelist)=$db->first("SELECT pictures FROM ".PRE."_downloads WHERE id='".$_REQUEST['id']."' LIMIT 1");
				$picturelist=unserialize($picturelist);
				if ( !is_array($picturelist) ) die('picturelist is not an array!');
				
				foreach ( $cache AS $id ) {
					$thumbnail=$picturelist[$id]['thumbnail'];
					$picture=$picturelist[$id]['picture'];
					if ( $thumbnail && file_exists(BASEDIR.getpath('uploads').$thumbnail) ) $this->mm->deletefile($thumbnail);
					if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $this->mm->deletefile($picture);
					
					unset($picturelist[$id]);
					logit('DOWNLOADS_PDEL','ID #'.$id);
				}
				
				$db->query("UPDATE ".PRE."_downloads SET pictures='".addslashes(serialize($picturelist))."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: action.php?action=downloads.pshow&id='.$_REQUEST['id']);
		}
	}
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		$_REQUEST['dlid']=(int)$_REQUEST['dlid'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		if ( !$_REQUEST['dlid'] ) die('missing download ID!');
		if ( !$this->access_pics($_REQUEST['dlid'],'downloads.pdel') ) die('you have no right to access this download!');
		
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				$id=$_REQUEST['id'];
				
				list($picturelist)=$db->first("SELECT pictures FROM ".PRE."_downloads WHERE id='".$_REQUEST['dlid']."' LIMIT 1");
				$picturelist=unserialize($picturelist);
				if ( !is_array($picturelist) ) die('picturelist is not an array!');
				
				$thumbnail=$picturelist[$id]['thumbnail'];
				$picture=$picturelist[$id]['picture'];
				
				//Eintrag löschen
				unset($picturelist[$id]);
				$db->query("UPDATE ".PRE."_downloads SET pictures='".addslashes(serialize($picturelist))."' WHERE id='".$_REQUEST['dlid']."' LIMIT 1");
				
				if ( $thumbnail && file_exists(BASEDIR.getpath('uploads').$thumbnail) ) $this->mm->deletefile($thumbnail);
				if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $this->mm->deletefile($picture);
				
				logit('DOWNLOADS_PDEL','ID #'.$_REQUEST['id']);
				printJSRedirect('action.php?action=downloads.pshow&id='.$_REQUEST['dlid']);
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
	
	quicklink('downloads.catadd');
	
	//DnD-Hinweis
	if ( $apx->user->has_right('downloads.edit') ) {
		echo '<p class="hint">'.$apx->lang->get('USEDND').'</p>';
	}
	
	$col[]=array('ID',0,'align="center"');
	$col[]=array('COL_CATNAME',75,'class="title"');
	$col[]=array('COL_DOWNLOADS',25,'align="center"');
	
	$data=$this->cat->getTree(array('title', 'open'));
	if ( count($data) ) {
		
		//Ausgabe erfolgt
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['open'] ) {
				list($downloads)=$db->first("SELECT count(id) FROM ".PRE."_downloads WHERE catid='".$res['id']."'");
			}
			
			$tabledata[$i]['COL1']=$res['id'];
			$tabledata[$i]['COL2']=replace($res['title']);
			$tabledata[$i]['COL3']=iif(isset($downloads),$downloads,'&nbsp;');
			$tabledata[$i]['CLASS'] = 'l'.($res['level']-1).($res['children'] ? ' haschildren' : '').($res['level']>1 ? ' hidden' : '').($res['iscat'] ? ' dark' : '');
			$tabledata[$i]['ID'] = 'node:'.$res['id'];
			
			//Optionen
			if ( $apx->user->has_right('downloads.catedit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'downloads.catedit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('downloads.catdel') && !$downloads ) $tabledata[$i]['OPTIONS'].='<span class="ifhasnochildren">'.optionHTMLOverlay('del.gif', 'downloads.catdel', 'id='.$res['id'], $apx->lang->get('CORE_DEL')).'</span><span class="ifhaschildren"><img alt="" src="design/ispace.gif"/></span>';
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('downloads.catclean') && $downloads ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('clean.gif', 'downloads.catclean', 'id='.$res['id'], $apx->lang->get('CLEAN'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Anordnen nur bei Unterkategorien
			/*$tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->user->has_right('downloads.catmove') && $follow[$res['id']]['prev'] ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'downloads.catmove', 'direction=up&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
			if ( $apx->user->has_right('downloads.catmove') && $follow[$res['id']]['next'] ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'downloads.catmove', 'direction=down&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
			*/
			
			unset($downloads);
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	
	//Mit Unter-Kategorien
	echo '<div class="treeview" id="tree">';
	$html->table($col);
	echo '</div>';
	
	$open = $apx->session->get('downloads_cat_open');
	$open = dash_unserialize($open);
	$opendata = array();
	foreach ( $open AS $catid ) {
		$opendata[] = array(
			'ID' => $catid
		);
	}
	$apx->tmpl->assign('OPEN', $opendata);
	$apx->tmpl->assign('EDIT_ALLOWED', $apx->user->has_Right('downloads.edit'));
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
				logit('DOWNLOADS_CATADD','ID #'.$nid);
				
				//Beitrag der Kategorie hinzufügen
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
				}
				else {
					printJSRedirect('action.php?action=downloads.catshow');
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
				logit('DOWNLOADS_CATADD',"ID #".$nid);
				
				//Beitrag der Kategorie hinzufügen
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
				}
				else {
					printJSRedirect('action.php?action=downloads.catshow');
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
		list($downloads)=$db->first("SELECT count(id) FROM ".PRE."_downloads WHERE catid='".$_REQUEST['id']."'");
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['id'] || !$_POST['parent'] || !$_POST['title'] ) infoNotComplete();
		elseif ( !$_POST['open'] && $downloads ) info($apx->lang->get('INFO_CONTAINSDOWNLOADS'));
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
			logit('DOWNLOADS_CATEDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('downloads.catshow'));
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
	
	list($downloads)=$db->first("SELECT count(id) FROM ".PRE."_downloads WHERE catid='".$_REQUEST['id']."'");
	if ( $downloads ) die('category still contains downloads!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$this->cat->deleteNode($_REQUEST['id']);
			logit('DOWNLOADS_CATDEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('downloads.catshow'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_downloads_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
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
			$db->query("UPDATE ".PRE."_downloads SET catid='".intval($_POST['moveto'])."' WHERE catid='".$_REQUEST['id']."'");
			logit('DOWNLOADS_CATCLEAN',"ID #".$_REQUEST['id']);
			
			//Kategorie löschen
			if ( $_POST['delcat'] ) {
				$this->cat->deleteNode($_REQUEST['id']);
				logit('DOWNLOADS_CATDEL',"ID #".$_REQUEST['id']);
			}
			
			printJSRedirect(get_index('downloads.catshow'));
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
	
	list($title,$children)=$db->first("SELECT title,children FROM ".PRE."_downloads_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
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
		header('Location: '.get_index('downloads.catshow'));
	}
}*/



////////////////////////////////////////////////////////////////////////////////////////// STATS

function stats() {
	global $set,$db,$apx;
	$datestamp=date('Ymd',time()-TIMEDIFF);
	
	list($count_files)=$db->first("SELECT count(id) FROM ".PRE."_downloads WHERE starttime!='0'");
	$count_dlsperday=$this->stats_dlsperday();
	$count_sizeperday=$this->stats_sizeperday();
	
	list($count_all,$size[0])=$db->first("SELECT sum(hits),sum(bytes*hits) FROM ".PRE."_downloads_stats");
	list($count_week,$size[1])=$db->first("SELECT sum(hits),sum(bytes*hits) FROM ".PRE."_downloads_stats WHERE daystamp BETWEEN '".date('Ymd',time()-6*24*3600-TIMEDIFF)."' AND '".date('Ymd',time()-TIMEDIFF)."'");
	list($count_today,$size[2])=$db->first("SELECT sum(hits),sum(bytes*hits) FROM ".PRE."_downloads_stats WHERE daystamp='".date('Ymd',time()-TIMEDIFF)."'");
	
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
		$data=$db->fetch("SELECT sum(bytes*hits) AS count,daystamp,time FROM ".PRE."_downloads_stats WHERE daystamp>='".date('Ymd',time()-50*24*3600-TIMEDIFF)."' GROUP BY daystamp ORDER BY daystamp ASC");
		$apx->tmpl->assign('GRAPH_HEADLINE',$apx->lang->get('TRAFFIC'));
	}
	else {
		$data=$db->fetch("SELECT sum(hits) AS count,daystamp,time FROM ".PRE."_downloads_stats WHERE daystamp>='".date('Ymd',time()-50*24*3600-TIMEDIFF)."' GROUP BY daystamp ORDER BY daystamp ASC");
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
	
	//Beliebteste Downloads
	$data=$db->fetch("SELECT sum(a.hits) AS count,b.id,b.title FROM ".PRE."_downloads_stats AS a LEFT JOIN ".PRE."_downloads AS b ON a.dlid=b.id WHERE time BETWEEN '".(time()-7*24*3600)."' AND '".time()."' GROUP BY dlid ORDER BY count DESC LIMIT 20");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$popdata[$i]['COUNT']=$res['count'];
			$popdata[$i]['TITLE']=strip_tags($res['title']);
			$popdata[$i]['LINK']=mklink(
				'downloads.php?id='.$res['id'],
				'downloads,id'.$res['id'].urlformat($res['title']).'.html',
				iif($set['main']['forcesection'],iif(unserialize_section($res['secid'])==array('all'),$apx->section_default,array_shift($tmp=unserialize_section($res['secid']))),0)
			);
		}
	}
	
	$apx->tmpl->assign('STAT',$statdata);
	$apx->tmpl->assign('POP',$popdata);
	
	$apx->tmpl->parse('stats');
}


} //END CLASS


?>
