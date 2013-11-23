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


# NEWS
# ====

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Funktionen laden
include(BASEDIR.getmodulepath('news').'admin_extend.php');


class action extends news_functions {

var $cat;

//Startup
function action() {
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$this->cat=new RecursiveTree(PRE.'_news_cat', 'id');
}



//***************************** News zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	//Suche durchführen
	if ( ( $_REQUEST['item'] && ( $_REQUEST['title'] || $_REQUEST['subtitle'] || $_REQUEST['text'] || $_REQUEST['teaser'] ) ) || $_REQUEST['secid'] || $_REQUEST['catid'] || $_REQUEST['userid'] ) {
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
			if ( $set['news']['subcats'] ) {
				$tree=$this->cat->getChildrenIds($_REQUEST['catid']);
				$tree[] = $_REQUEST['catid'];
				if ( is_array($tree) ) $where.=" AND catid IN (".implode(',',$tree).") ";
			}
			else $where.=" AND catid='".$_REQUEST['catid']."' ";
		}
		
		//Benutzer
		if ( $_REQUEST['userid'] ) {
			$where.=" AND a.userid='".$_REQUEST['userid']."' ";
		}
		
		$data=$db->fetch("SELECT id FROM ".PRE."_news WHERE 1 ".$where);
		$ids = get_ids($data, 'id');
		$ids[] = -1;
		$searchid = saveSearchResult('admin_news', $ids, array(
			'item' => $_REQUEST['item'],
			'title' => $_REQUEST['title'],
			'subtitle' => $_REQUEST['subtitle'],
			'teaser' => $_REQUEST['teaser'],
			'text' => $_REQUEST['text'],
			'catid' => $_REQUEST['catid'],
			'secid' => $_REQUEST['secid'],
			'userid' => $_REQUEST['userid']
		));
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: action.php?action=news.show&what='.$_REQUEST['what'].'&searchid='.$searchid);
		return;
	}
	
	
	//Vorgaben
	$_REQUEST['title'] = 1;
	$_REQUEST['subtitle'] = 1;
	$_REQUEST['teaser'] = 1;
	$_REQUEST['text'] = 1;
	
	quicklink('news.add');
	
	$layerdef[]=array('LAYER_ALL','action.php?action=news.show',!$_REQUEST['what']);
	$layerdef[]=array('LAYER_SELF','action.php?action=news.show&amp;what=self',$_REQUEST['what']=='self');
	$layerdef[]=array('LAYER_SEND','action.php?action=news.show&amp;what=send',$_REQUEST['what']=='send');
	
	//Layer Header ausgeben
	$html->layer_header($layerdef);
	
	$orderdef[0]='creation';
	$orderdef['title']=array('a.title','ASC','COL_TITLE');
	$orderdef['user']=array('b.username','ASC','COL_USER');
	$orderdef['category']=array('catname','ASC','COL_CATEGORY');
	$orderdef['creation']=array('a.addtime','DESC','SORT_ADDTIME');
	$orderdef['publication']=array('a.starttime','DESC','SORT_STARTTIME');
	$orderdef['hits']=array('a.hits','DESC','COL_HITS');
	
	
	//Suchergebnis?
	$resultFilter = '';
	if ( $_REQUEST['searchid'] ) {
		$searchRes = getSearchResult('admin_news', $_REQUEST['searchid']);
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
	if ( $set['news']['subcats'] ) $data = $this->cat->getTree(array('title'));
	else $data=$db->fetch("SELECT * FROM ".PRE."_news_cat ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',$res['level']-1);
			$catlist.='<option value="'.$res['id'].'"'.iif($_REQUEST['catid']==$res['id'],' selected="selected"').'>'.$space.replace($res['title']).'</option>';
		}
	}
	
	//Benutzer auflisten
	$userlist = '';
	$data=$db->fetch("SELECT b.userid,b.username FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING (userid) WHERE a.userid!=0 GROUP BY userid ORDER BY username ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) $userlist.='<option value="'.$res['userid'].'"'.iif($_REQUEST['userid']==$res['userid'],' selected="selected"').'>'.replace($res['username']).'</option>';
	}
	
	$apx->tmpl->assign('ITEM',compatible_hsc($_REQUEST['item']));
	$apx->tmpl->assign('SECLIST',$seclist);
	$apx->tmpl->assign('CATLIST',$catlist);
	$apx->tmpl->assign('USERLIST',$userlist);
	$apx->tmpl->assign('STITLE',(int)$_REQUEST['title']);
	$apx->tmpl->assign('SSUBTITLE',(int)$_REQUEST['subtitle']);
	$apx->tmpl->assign('STEASER',(int)$_REQUEST['teaser']);
	$apx->tmpl->assign('STEXT',(int)$_REQUEST['text']);
	$apx->tmpl->assign('SET_TEASER',$set['news']['teaser']);
	$apx->tmpl->assign('WHAT',$_REQUEST['what']);
	$apx->tmpl->assign('EXTENDED',$searchRes);
	$apx->tmpl->parse('search');
	
	
	//Filter
	$layerFilter = '';
	if ( $_REQUEST['what']=='self' ) {
		$layerFilter = " AND a.userid='".$apx->user->info['userid']."' ";
	}
	elseif ( $_REQUEST['what']=='send' ) {
		$layerFilter = " AND a.send_ip!='' ";
	}
	
	
	list($count)=$db->first("SELECT count(userid) FROM ".PRE."_news AS a WHERE 1 ".$resultFilter.$layerFilter.section_filter(true, 'a.secid'));
	pages('action.php?action=news.show&amp;what='.$_REQUEST['what'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']).'&amp;sortby='.$_REQUEST['sortby'],$count);
	$data=$db->fetch("SELECT a.id,a.secid,a.send_username,a.title,a.starttime,a.endtime,a.endtime,a.allowcoms,a.allowrating,IF(a.sticky>=".time().",1,0) AS sticky,a.hits,b.userid,b.username,c.title AS catname FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) LEFT JOIN ".PRE."_news_cat AS c ON a.catid=c.id WHERE 1 ".$resultFilter.$layerFilter.section_filter(true, 'a.secid')." ".getorder($orderdef,'sticky DESC',1).getlimit());
	$this->show_print($data);
	orderstr($orderdef,'action.php?action=news.show&amp;what='.$_REQUEST['what'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']));
	save_index($_SERVER['REQUEST_URI']);
	
	//Layer-Footer ausgeben
	$html->layer_footer();
}



//News auflisten
function show_print($data) {
	global $set,$db,$apx,$html;
	
	$col[]=array('',1,'align="center"');
	$col[]=array($apx->lang->get('COL_TITLE').' / '.$apx->lang->get('COL_USER'),50,'class="title"');
	$col[]=array('COL_CATEGORY',22,'align="center"');
	$col[]=array('COL_PUBDATE',18,'align="center"');
	$col[]=array('COL_HITS',10,'align="center"');
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( !$res['starttime'] ) $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['endtime']<time() ) $tabledata[$i]['COL1']='<img src="design/greendotcross.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['starttime']>time() ) $tabledata[$i]['COL1']='<img src="design/greendotwait.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			
			$tmp=unserialize_section($res['secid']);
			$link=mklink(
				'news.php?id='.$res['id'],
				'news,id'.$res['id'].urlformat($res['title']).'.html',
				iif($set['main']['forcesection'],iif(unserialize_section($res['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
			);
			
			$tabledata[$i]['COL2']='<a href="'.$link.'" target="_blank">'.shorttext(strip_tags($res['title']),40).'</a>';
			$tabledata[$i]['COL3']=replace($res['catname']);
			$tabledata[$i]['COL5']=number_format($res['hits'],0,'','.');
			
			if ( $res['username'] ) $tabledata[$i]['COL2'].='<br /><small>'.$apx->lang->get('BY').' '.replace($res['username']).'</small>';
			else $tabledata[$i]['COL2'].='<br /><small>'.$apx->lang->get('BY').' '.$apx->lang->get('GUEST').': <i>'.replace($res['send_username']).'</i></small>';
			
			if ( $res['starttime'] ) $tabledata[$i]['COL4']=mkdate($res['starttime'],'<br />');
			else $tabledata[$i]['COL4']='&nbsp;';
			
			//Optionen
			if ( $apx->user->has_right('news.edit') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('news.edit') ) ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'news.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('news.copy') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('news.copy') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('copy.gif', 'news.copy', 'id='.$res['id'], $apx->lang->get('COPY'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('news.del') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('news.del') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'news.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( ( !$res['starttime'] || $res['endtime']<time() ) && $apx->user->has_right('news.enable') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('news.enable') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('enable.gif', 'news.enable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
			elseif ( $res['starttime'] && $apx->user->has_right('news.disable') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('news.disable') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('disable.gif', 'news.disable', 'id='.$res['id'], $apx->lang->get('CORE_DISABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Kommentare + Bewertungen
			if ( $apx->is_module('comments') || $apx->is_module('ratings') ) $tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->is_module('comments') ) {
				list($comments)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='news' AND mid='".$res['id']."' )");
				if ( $comments && ( $apx->is_module('comments') && $set['news']['coms'] ) && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=news&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			if ( $apx->is_module('ratings') ) {
				list($ratings)=$db->first("SELECT count(id) FROM ".PRE."_ratings WHERE ( module='news' AND mid='".$res['id']."' )");
				if ( $ratings && ( $apx->is_module('ratings') && $set['news']['ratings'] ) && $res['allowrating'] && $apx->user->has_right('ratings.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('ratings.gif', 'ratings.show', 'module=news&mid='.$res['id'], $apx->lang->get('RATINGS').' ('.$ratings.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			
			//Spacer
			if ( isset($laststicky) && (int)$res['sticky']!=$laststicky ) $tabledata[$i]['SPACER']=true;
			$laststicky=(int)$res['sticky'];
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
}



//***************************** Neue News *****************************
function add() {
	global $set,$db,$apx;
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['catid'] || !$_POST['text'] ) infoNotComplete();
		elseif ( $_POST['catid']!='newcat' && !$this->category_is_open($_POST['catid']) ) die('you are not allowed to add news to this category!');
		elseif ( !$this->update_newspic() ) { /*DO NOTHING*/ }
		else {
			$links=array();
			
			//Sources
			$sources=$this->get_sources();
			for ( $i=1; $i<=20; $i++ ) {
				if ( !$_POST['source'.$i.'_title'] || !$_POST['source'.$i.'_id'] ) continue;
				$sourceid=(int)$_POST['source'.$i.'_id'];
				$links[]=array(
					'title' => $_POST['source'.$i.'_title'],
					'text' => $sources[$sourceid]['TITLE'],
					'url' => $sources[$sourceid]['LINK'],
					'popup' => (int)$_POST['source'.$i.'_popup']
				);
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
			
			//Veröffentlichung: JETZT
			if ( $_POST['pubnow'] && $apx->user->has_right('news.enable') ) {
				$_POST['starttime']=time();
				$_POST['endtime']=3000000000;
				$addfields.=',starttime,endtime';
			}
			
			//Sticky Ende
			if ( $_POST['sticky'] && ($stickyend = maketime(3))!=0 ) {
				$_POST['sticky']=$stickyend;
			}
			elseif ( $_POST['sticky'] ) {
				$_POST['sticky'] = 3000000000;
			}
			
			//Autor erzwingen, wenn keine Sonderechte
			if ( !$apx->user->has_spright('news.edit') ) $_POST['userid']=$apx->user->info['userid'];
			
			$_POST['secid']=serialize_section($_POST['secid']);
			$_POST['addtime']=time();
			$_POST['links']=serialize($links);
			$_POST['newspic']=$this->newspicpath;
			
			$db->dinsert(PRE.'_news','secid,prodid,catid,userid,title,subtitle,newspic,teaser,text,meta_description,galid,links,addtime,top,sticky,searchable,restricted,allowcoms,allowrating'.$addfields);
			$nid=$db->insert_id();
			logit('NEWS_ADD','ID #'.$nid);
			
			//Inlinescreens
			mediamanager_setinline($nid);
			
			//Tags
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_news_tags VALUES('".$nid."', '".$tagid."')");
			}
			
			if ( $_POST['catid']=='newcat' && $apx->user->has_right('news.catadd') ) printJSRedirect('action.php?action=news.catadd&addid='.$nid);
			else printJSRedirect('action.php?action=news.show');
		}
	}
	else {
		$_POST['link1_title']=$apx->lang->get('LLINK');
		$_POST['link1_popup']=1;
		$_POST['source1_title']=$apx->lang->get('LSOURCE');
		$_POST['source1_popup']=1;
		$_POST['allowcoms']=1;
		$_POST['allowrating']=1;
		$_POST['searchable']=1;
		$_POST['userid']=$apx->user->info['userid'];
		
		
		mediamanager('news');
		
		//Quellen auslesen
		$source_optionlist=$this->get_sources();
		
		//Quellen
		for ( $i=1; $i<=20; $i++ ) {
			if ( $i>1 && ( !$_POST['source'.$i.'_title'] || $_POST['source'.$i.'_title']==$apx->lang->get('LSOURCE') ) && !$_POST['source'.$i.'_id'] ) continue;
			$sourcelist[]=array(
				'TITLE' => compatible_hsc($_POST['source'.$i.'_title']),
				'SELECTED' => (int)$_POST['source'.$i.'_id'],
				'POPUP' => (int)$_POST['source'.$i.'_popup'],
				'DISPLAY' => 1
			);
		}
		
		//Normale Links
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
		
		//Links + Sources füllen
		while ( count($sourcelist)<20 ) {
			$sourcelist[]=array('TITLE'=>$apx->lang->get('LSOURCE'),'POPUP'=>1);
		}
		while ( count($linklist)<20 ) {
			$linklist[]=array('TITLE'=>$apx->lang->get('LLINK'),'POPUP'=>1);
		}
		
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('PRODID',$_POST['prodid']);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('GALID',$_POST['galid']);
		$apx->tmpl->assign('CATLIST',$this->get_catlist());
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('SUBTITLE',compatible_hsc($_POST['subtitle']));
		$apx->tmpl->assign('TEASER',compatible_hsc($_POST['teaser']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('PIC_COPY',compatible_hsc($_POST['pic_copy']));
		$apx->tmpl->assign('LINK',$linklist);
		$apx->tmpl->assign('SOURCE',$sourcelist);
		$apx->tmpl->assign('SOURCE_OPTIONS',$source_optionlist);
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWRATING',(int)$_POST['allowrating']);
		$apx->tmpl->assign('TOP',(int)$_POST['top']);
		$apx->tmpl->assign('STICKY',(int)$_POST['sticky']);
		$apx->tmpl->assign('STICKYTIME',choosetime(3,1,maketime(3)));
		$apx->tmpl->assign('PUBNOW',(int)$_POST['pubnow']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('RESTRICTED',(int)$_POST['restricted']);
		
		$apx->tmpl->parse('add');
	}
}



//***************************** News bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	//News aktualisieren
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['catid'] || !$_POST['text'] ) infoNotComplete();
		elseif ( $_POST['catid']!='newcat' && !$this->category_is_open($_POST['catid']) ) die('you are not allowed to add news to this category!');
		elseif ( !$this->update_newspic() ) { /*DO NOTHING*/ }
		else {
			
			//Links
			for ( $i=1; $i<=40; $i++ ) {
				if ( !$_POST['link'.$i.'_title'] || !$_POST['link'.$i.'_text'] || !$_POST['link'.$i.'_url'] ) continue;
				$links[]=array(
					'title' => $_POST['link'.$i.'_title'],
					'text' => $_POST['link'.$i.'_text'],
					'url' => $_POST['link'.$i.'_url'],
					'popup' => intval($_POST['link'.$i.'_popup'])
				);
			}
	
			//Veröffentlichung
			if ( $apx->user->has_right('news.enable') && isset($_POST['t_day_1']) ) {
				$_POST['starttime']=maketime(1);
				$_POST['endtime']=maketime(2);
				if ( $_POST['starttime'] ) {
					if ( !$_POST['endtime'] || $_POST['endtime']<=$_POST['starttime'] ) $_POST['endtime']=3000000000;
					$addfields=',starttime,endtime';
				}
			}
			
			//Sticky Ende
			if ( $_POST['sticky'] && ($stickyend = maketime(3))!=0 ) {
				$_POST['sticky']=$stickyend;
			}
			elseif ( $_POST['sticky'] ) {
				$_POST['sticky'] = 3000000000;
			}
			
			//Autor
			if ( $apx->user->has_spright('news.edit') && $_POST['userid'] ) {
				if ( $_POST['userid']=='send' ) $_POST['userid']=0;
				else $_POST['userid']=$_POST['userid'];
				$addfields.=',userid';
			}
			
			$_POST['secid']=serialize_section($_POST['secid']);
			$_POST['links']=serialize($links);
			$_POST['newspic']=$this->newspicpath;
			
			$db->dupdate(PRE.'_news','secid,prodid,catid,title,subtitle,newspic,teaser,text,meta_description,galid,links,top,sticky,searchable,restricted,allowcoms,allowrating'.$addfields,"WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('news.edit')," AND userid='".$apx->user->info['userid']."'")." )");
			logit('NEWS_EDIT',"ID #".$_REQUEST['id']);
			
			//Tags
			$db->query("DELETE FROM ".PRE."_news_tags WHERE id='".$_REQUEST['id']."'");
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_news_tags VALUES('".$_REQUEST['id']."', '".$tagid."')");
			}
			
			if ( $_POST['catid']=='newcat' && $apx->user->has_right('news.catadd') ) printJSRedirect('action.php?action=news.catadd&addid='.$_REQUEST['id']);
			else printJSRedirect(get_index('news.show'));
		}
	}
	else {
		$res=$db->first("SELECT secid,prodid,userid,send_username,send_email,catid,newspic,title,subtitle,teaser,text,meta_description,galid,links,top,sticky,searchable,restricted,allowcoms,allowrating,starttime,endtime FROM ".PRE."_news WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('news.edit')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1",1);
		
		//Umsetzung zu POST
		foreach ( $res AS $key => $val ) {
			if ( $key=='links' ) continue;
			$_POST[$key]=$val;
		}
		
		//Keine Benutzer-ID gesetzt => Eingesendete News
		if ( !$res['userid'] ) $_POST['userid']='send';
		
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
		
		//Veröffentlichung
		if ( $res['starttime'] ) {
			maketimepost(1,$res['starttime']);
			if ( $res['endtime']<2147483647 ) maketimepost(2,$res['endtime']);
		}
		
		//Sticky Ende
		if ( $res['sticky']<2147483647 ) {
			maketimepost(3,$res['sticky']);
		}
		
		$_POST['secid']=unserialize_section($_POST['secid']);
		
		
		mediamanager('news');
		
		//Normale Links
		if ( !$_POST['link1_title'] ) $_POST['link1_title']=$apx->lang->get('LLINK');
		for ( $i=1; $i<=40; $i++ ) {
			if ( $i>1 && ( !$_POST['link'.$i.'_title'] || $_POST['link'.$i.'_title']==$apx->lang->get('LLINK') ) && !$_POST['link'.$i.'_text'] && !$_POST['link'.$i.'_url'] ) continue;
			$linklist[]=array(
				'TITLE' => compatible_hsc($_POST['link'.$i.'_title']),
				'TEXT' => compatible_hsc($_POST['link'.$i.'_text']),
				'URL' => compatible_hsc($_POST['link'.$i.'_url']),
				'POPUP' => (int)$_POST['link'.$i.'_popup'],
				'DISPLAY' => 1
			);
		}
		
		while ( count($linklist)<40 ) {
			$linklist[]=array('TITLE'=>$apx->lang->get('LLINK'),'POPUP'=>1);
		}
		
		//Veröffentlichung
		if ( $apx->user->has_right('news.enable') && isset($_POST['t_day_1']) ) {
			$apx->tmpl->assign('STARTTIME',choosetime(1,0,maketime(1)));
			$apx->tmpl->assign('ENDTIME',choosetime(2,1,maketime(2)));
		}
		
		//Einsende-User beachten
		$send=$db->first("SELECT send_username,send_email FROM ".PRE."_news WHERE id='".$_REQUEST['id']."' LIMIT 1");
		if ( $send['send_username'] ) {
			$usersend='<option value="send"'.iif($_POST['userid']=='send',' selected="selected"').'>'.$apx->lang->get('GUEST').': '.$send['send_username'].iif($send['send_email'],' ('.$send['send_email'].')').'</option>';
		}
		
		//Newspic
		$teaserpic = '';
		if ( $_POST['newspic'] ) {
			$teaserpicpath = $_POST['newspic'];
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
			FROM ".PRE."_news_tags AS n
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE n.id='".$_REQUEST['id']."'
			ORDER BY t.tag ASC
		");
		$tags = get_ids($tagdata, 'tag');
		$_POST['tags'] = implode(', ', $tags);
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('PRODID',$_POST['prodid']);
		$apx->tmpl->assign('USER_SEND',$usersend);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('GALID',$_POST['galid']);
		$apx->tmpl->assign('CATLIST',$this->get_catlist());
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('SUBTITLE',compatible_hsc($_POST['subtitle']));
		$apx->tmpl->assign('TEASER',compatible_hsc($_POST['teaser']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('NEWSPIC',$teaserpic);
		$apx->tmpl->assign('PIC_COPY',compatible_hsc($_POST['pic_copy']));
		$apx->tmpl->assign('LINK',$linklist);
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWRATING',(int)$_POST['allowrating']);
		$apx->tmpl->assign('TOP',(int)$_POST['top']);
		$apx->tmpl->assign('STICKY',(int)$_POST['sticky']);
		$apx->tmpl->assign('STICKYTIME',choosetime(3,1,maketime(3)));
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('RESTRICTED',(int)$_POST['restricted']);
		
		$apx->tmpl->parse('edit');
	}
}



//***************************** News löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$res = $db->first("SELECT newspic FROM ".PRE."_news WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('news.del')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			$db->query("DELETE FROM ".PRE."_news WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('news.del')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			if ( !$db->affected_rows() ) die('access denied!');
			
			//Kommentare + Bewertungen löschen (nur wenn ein Eintrag gelöscht wurde -> User hat Recht dazu!)
			if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='news' AND mid='".$_REQUEST['id']."' )");
			if ( $apx->is_module('ratings') ) $db->query("DELETE FROM ".PRE."_ratings WHERE ( module='news' AND mid='".$_REQUEST['id']."' )");
			
			//Bilder löschen
			require_once(BASEDIR.'lib/class.mediamanager.php');
			$mm = new mediamanager();
			$picture = $res['newspic'];
			$poppic=str_replace('-thumb.','.',$newspic);
			if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $mm->deletefile($picture);
			if ( $poppic && file_exists(BASEDIR.getpath('uploads').$poppic) ) $mm->deletefile($poppic);
			
			//Tags löschen
			$db->query("DELETE FROM ".PRE."_news_tags WHERE id='".$_REQUEST['id']."'");
			
			logit('NEWS_DEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('news.show'));
		}
	}
	else {
		list($title, $subtitle) = $db->first("SELECT title, subtitle FROM ".PRE."_news WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title.($subtitle ? ' - '.$subtitle : '')))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
	}
}



//***************************** News kopieren *****************************
function copy() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$res=$db->first("SELECT secid,catid,userid,send_username,send_email,newspic,title,subtitle,teaser,text,galid,links,top,sticky,allowcoms,allowrating,searchable,restricted FROM ".PRE."_news WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('news.copy')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			
			foreach ( $res AS $key => $val ) $_POST[$key]=$val;
			$_POST['title']=$apx->lang->get('COPYOF').$_POST['title'];
			$_POST['addtime']=time();
			
			$db->dinsert(PRE.'_news','secid,catid,userid,send_username,send_email,newspic,title,subtitle,teaser,text,galid,links,addtime,top,sticky,allowcoms,allowrating,searchable,restricted');
			$nid = $db->insert_id();
			$oldId = $_REQUEST['id'];
			$newId = $nid;
			
			//Bilder kopieren
			$newPicture = str_replace($oldId, $newId, $res['newspic']);
			copy_with_thumbnail($res['newspic'], $newPicture);
			
			//Bilder update
			$db->query("UPDATE ".PRE."_news SET newspic='".addslashes($newPicture)."' WHERE id='".$newId."' LIMIT 1");
			
			logit('NEWS_COPY','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('news.show'));
		}
	}
	else {
		list($title, $subtitle) = $db->first("SELECT title, subtitle FROM ".PRE."_news WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title.($subtitle ? ' - '.$subtitle : '')))));
		tmessageOverlay('copy',array('ID'=>$_REQUEST['id']));
	}
}



//***************************** News aktivieren *****************************
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
			
			$db->query("UPDATE ".PRE."_news SET starttime='".$starttime."',endtime='".$endtime."' WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('news.enable')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			logit('NEWS_ENABLE','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('news.show'));
		}
	}
	else {
		list($title, $subtitle) = $db->first("SELECT title, subtitle FROM ".PRE."_news WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('TITLE', compatible_hsc($title.($subtitle ? ' - '.$subtitle : '')));
		$apx->tmpl->assign('STARTTIME',choosetime(1,0,time()));
		$apx->tmpl->assign('ENDTIME',choosetime(2,1));
		tmessageOverlay('enable');
	}
}



//***************************** News widerrufen *****************************
function disable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');

	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("UPDATE ".PRE."_news SET starttime=0,endtime=0 WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('news.disable')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			logit('NEWS_DISABLE','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('news.show'));
		}
	}
	else {
		list($title, $subtitle) = $db->first("SELECT title, subtitle FROM ".PRE."_news WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title.($subtitle ? ' - '.$subtitle : '')))));
		tmessageOverlay('disable',array('ID'=>$_REQUEST['id']));
	}
}



////////////////////////////////////////////////////////////////////////////////////////// KATEGORIEN

//***************************** Kategorien zeigen *****************************
function catshow() {
	global $set,$db,$apx,$html;
	
	//Struktur reparieren
	if ( $_REQUEST['repair'] && $set['news']['subcats'] ) {
		$this->cat->repair();
		echo 'Repair done!';
		return;
	}
	
	quicklink('news.catadd');
	
	//DnD-Hinweis
	if ( $set['news']['subcats'] && $apx->user->has_right('news.edit') ) {
		echo '<p class="hint">'.$apx->lang->get('USEDND').'</p>';
	}
	
	$col[]=array('ID',0,'align="center"');
	$col[]=array('COL_CATNAME',75,'class="title"');
	$col[]=array('COL_NEWS',25,'align="center"');
	
	if ( $set['news']['subcats'] ) {
		$data=$this->cat->getTree(array('title', 'open'));
	}
	else {
		$orderdef[0]='title';
		$orderdef['title']=array('title','ASC','COL_CATNAME');
		
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_news_cat");
		pages('action.php?action=news.catshow',$count);
		$data=$db->fetch("SELECT * FROM ".PRE."_news_cat ".getorder($orderdef).getlimit());
	}
	
	if ( count($data) ) {
		
		//Ausgabe erfolgt
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['open'] ) {
				list($news)=$db->first("SELECT count(id) FROM ".PRE."_news WHERE catid='".$res['id']."'");
			}
			
			$tabledata[$i]['COL1']=$res['id'];
			$tabledata[$i]['COL2']=replace($res['title']);
			$tabledata[$i]['COL3']=iif(isset($news),$news,'&nbsp;');
			$tabledata[$i]['CLASS'] = 'l'.($res['level']-1).($res['children'] ? ' haschildren' : '').($res['level']>1 ? ' hidden' : '').($res['iscat'] ? ' dark' : '');
			$tabledata[$i]['ID'] = 'node:'.$res['id'];
			
			//Optionen
			if ( $apx->user->has_right('news.catedit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'news.catedit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('news.catdel') && !$news ) $tabledata[$i]['OPTIONS'].='<span class="ifhasnochildren">'.optionHTMLOverlay('del.gif', 'news.catdel', 'id='.$res['id'], $apx->lang->get('CORE_DEL')).'</span><span class="ifhaschildren"><img alt="" src="design/ispace.gif"/></span>';
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('news.catclean') && $news ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('clean.gif', 'news.catclean', 'id='.$res['id'], $apx->lang->get('CLEAN'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Anordnen nur bei Unterkategorien
			/*if ( $set['news']['subcats'] ) {
				$tabledata[$i]['OPTIONS'].='&nbsp;';
				if ( $apx->user->has_right('news.catmove') && $follow[$res['id']]['prev'] ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'news.catmove', 'direction=up&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
				if ( $apx->user->has_right('news.catmove') && $follow[$res['id']]['next'] ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'news.catmove', 'direction=down&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
			}*/
			
			unset($news);
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	
	//Mit Unter-Kategorien
	if ( $set['news']['subcats'] ) {
		echo '<div class="treeview" id="tree">';
		$html->table($col);
		echo '</div>';
		
		$open = $apx->session->get('news_cat_open');
		$open = dash_unserialize($open);
		$opendata = array();
		foreach ( $open AS $catid ) {
			$opendata[] = array(
				'ID' => $catid
			);
		}
		$apx->tmpl->assign('OPEN', $opendata);
		$apx->tmpl->assign('EDIT_ALLOWED', $apx->user->has_Right('news.edit'));
		$apx->tmpl->parse('catshow_js');
	}
	
	//Normale Kategorien
	else {
		$html->table($col);
		orderstr($orderdef,'action.php?action=news.catshow');
	}
	
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
					'icon' => $_POST['icon'],
					'open' => $_POST['open'],
					'forgroup' => $_POST['forgroup']
				));
				logit('NEWS_CATADD','ID #'.$nid);
				
				//Beitrag der Kategorie hinzufügen
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
				}
				else {
					printJSRedirect('action.php?action=news.catshow');
				}
			}
			
			//WENN NODE
			else {
				if ( $_POST['groupid'][0]=='all' ) $_POST['forgroup']='all';
				else $_POST['forgroup']=serialize($_POST['groupid']);
				
				$nid = $this->cat->createNode(intval($_POST['parent']), array(
					'title' => $_POST['title'],
					'icon' => $_POST['icon'],
					'open' => $_POST['open'],
					'forgroup' => $_POST['forgroup']
				));
				logit('NEWS_CATADD',"ID #".$nid);
				
				//Beitrag der Kategorie hinzufügen
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
				}
				else {
					printJSRedirect('action.php?action=news.catshow');
				}
			}
		}
	}
	else {
		$_POST['open']=1;
		
		
		//Baum
		if ( $set['news']['subcats'] ) {
			$catlist='<option value="root" style="font-weight:bold;">'.$apx->lang->get('ROOT').'</option>';
			$data=$this->cat->getTree(array('title'));
			if ( count($data) ) {
				$catlist.='<option value=""></option>';
				foreach ( $data AS $res ) {
					$catlist.='<option value="'.$res['id'].'"'.iif($_POST['parent']==$res['id'],' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;',($res['level']-1)).replace($res['title']).'</option>';
				}
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
		list($news)=$db->first("SELECT count(id) FROM ".PRE."_news WHERE catid='".$_REQUEST['id']."'");
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['id'] || !$_POST['parent'] || !$_POST['title'] ) infoNotComplete();
		elseif ( !$_POST['open'] && $news ) info($apx->lang->get('INFO_CONTAINSNEWS'));
		else {
			if ( $_POST['groupid'][0]=='all' ) $_POST['forgroup']='all';
			else $_POST['forgroup']=serialize($_POST['groupid']);
			
			$this->cat->moveNode($_REQUEST['id'], intval($_POST['parent']), array(
				'title' => $_POST['title'],
				'icon' => $_POST['icon'],
				'open' => $_POST['open'],
				'forgroup' => $_POST['forgroup']
			));
			logit('NEWS_CATEDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('news.catshow'));
		}
	}
	else {
		$res = $this->cat->getNode($_REQUEST['id'], array('title','icon','open','forgroup'));
		$_POST['title'] = $res['title'];
		$_POST['icon'] = $res['icon'];
		$_POST['open'] = $res['open'];
		if ( $res['forgroup']=='all' ) $_POST['groupid'][0]='all';
		else $_POST['groupid']=unserialize($res['forgroup']);
		if ( !$res['parents'] ) $_POST['parent'] = 'root';
		else $_POST['parent'] = array_pop($res['parents']);
		
		//Baum
		if ( $set['news']['subcats'] ) {
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
		
		//Benutzergruppen
		$data=$db->fetch("SELECT groupid,name FROM ".PRE."_user_groups WHERE ( gtype='admin' OR gtype='indiv' ) ORDER BY name ASC");
		$grouplist.='<option value="all"'.iif(!isset($_POST['groupid']) || $_POST['groupid'][0]=='all',' selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('ALL').'</option>';
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$grouplist.='<option value="'.$res['groupid'].'"'.iif(isset($_POST['groupid']) && in_array($res['groupid'],$_POST['groupid']),' selected="selected"').'>'.replace($res['name']).'</option>';
			}
		}
		
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
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
	
	list($news)=$db->first("SELECT count(id) FROM ".PRE."_news WHERE catid='".$_REQUEST['id']."'");
	if ( $news ) die('category still contains news!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$this->cat->deleteNode($_REQUEST['id']);
			logit('NEWS_CATDEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('news.catshow'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_news_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
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
			$db->query("UPDATE ".PRE."_news SET catid='".intval($_POST['moveto'])."' WHERE catid='".$_REQUEST['id']."'");
			logit('NEWS_CATCLEAN',"ID #".$_REQUEST['id']);
			
			//Kategorie löschen
			if ( $_POST['delcat'] ) {
				$this->cat->deleteNode($_REQUEST['id']);
				logit('NEWS_CATDEL',"ID #".$_REQUEST['id']);
			}
			
			printJSRedirect(get_index('news.catshow'));
			return;
		}
	}
	
	if ( $set['news']['subcats'] ) $data=$this->cat->getTree(array('title', 'open'));
	else $data=$db->fetch("SELECT id,title,open FROM ".PRE."_news_cat ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',($res['level']-1));
			if ( $res['id']!=$_REQUEST['id'] && $res['open'] ) $catlist.='<option value="'.$res['id'].'" '.iif($_POST['moveto']==$res['id'],' selected="selected"').' style="color:green;">'.$space.replace($res['title']).'</option>';
			else $catlist.='<option value="" disabled="disabled" style="color:grey;">'.$space.replace($res['title']).'</option>';
		}
	}
	
	list($title,$children)=$db->first("SELECT title,children FROM ".PRE."_news_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
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
		header('Location: '.get_index('news.catshow'));
	}
}*/



////////////////////////////////////////////////////////////////////////////////////////// QUELLEN

//***************************** Quelle zeigen *****************************
function sshow() {
	global $set,$db,$apx,$html;
	
	quicklink('news.sadd');
	
	$col[]=array('COL_TITLE',50,'');
	$col[]=array('COL_LINK',50,'');
	
	$orderdef[0]='title';
	$orderdef['title']=array('title','ASC','SORT_TITLE');

	$data=$db->fetch("SELECT * FROM ".PRE."_news_sources".getorder($orderdef));
	$imax=count($data);
	
	if ( $imax ) {
		foreach ( $data AS $res ) {
			++$i;
			
			$tabledata[$i]['COL1']=replace($res['title']);
			$tabledata[$i]['COL2']='<a href="../misc.php?action=redirect&amp;url='.urlencode($res['link']).'" target="_blank">'.shorttext($res['link'],40).'</a>';
			
			//Optionen
			if ( $apx->user->has_right('news.sedit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'news.sedit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			if ( $apx->user->has_right('news.sdel') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'news.sdel', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	
	orderstr($orderdef,'action.php?action=news.sshow');
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Quelle hinzufügen *****************************
function sadd() {
	global $set,$db,$apx;
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['link'] ) infoNotComplete();
		else {
			$db->dinsert(PRE.'_news_sources','title,link');
			logit('NEWS_SADD','ID #'.$db->insert_id());
			printJSRedirect('action.php?action=news.sshow');
		}
	}
	else {
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('LINK',compatible_hsc($_POST['link']));
		$apx->tmpl->assign('ACTION','sadd');
		
		$apx->tmpl->parse('sadd_sedit');
	}
}



//***************************** Quelle bearbeiten *****************************
function sedit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['link'] ) infoNotComplete();
		else {
			$db->dupdate(PRE.'_news_sources','title,link',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('NEWS_SEDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('news.sshow'));
		}
	}
	else {
		list($_POST['title'],$_POST['link'])=$db->first("SELECT title,link FROM ".PRE."_news_sources WHERE id='".intval($_REQUEST['id'])."' LIMIT 1");
		
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('LINK',compatible_hsc($_POST['link']));
		$apx->tmpl->assign('ACTION','sedit');
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		
		$apx->tmpl->parse('sadd_sedit');
	}
}



//***************************** Quelle löschen *****************************
function sdel() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_news_sources WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('NEWS_SDEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('news.sshow'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_news_sources WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
	}
}


} //END CLASS


?>