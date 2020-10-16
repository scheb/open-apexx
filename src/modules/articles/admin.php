<?php 

# ARTICLE CLASS
# =============

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Funktionen laden
include(BASEDIR.getmodulepath('articles').'admin_extend.php');


class action extends articles_functions {

var $cat;

//Startup
function action() {
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$this->cat=new RecursiveTree(PRE.'_articles_cat', 'id');
}


////////////////////////////////////////////////////////////////////////////////////////// ARTIKEL

//***************************** Artikel zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	//Suche durchführen
	if ( ( $_REQUEST['item'] && ( $_REQUEST['title'] || $_REQUEST['subtitle'] || $_REQUEST['pages'] || $_REQUEST['teaser'] ) ) || $_REQUEST['secid'] || $_REQUEST['catid'] || $_REQUEST['userid'] ) {
		$where = '';
		$_REQUEST['secid']=(int)$_REQUEST['secid'];
		$_REQUEST['catid']=(int)$_REQUEST['catid'];
		$_REQUEST['userid']=(int)$_REQUEST['userid'];
		if ( !isset($_REQUEST['item']) ) {
			$_REQUEST['title']=1;
			$_REQUEST['subtitle']=1;
			$_REQUEST['teaser']=1;
			$_REQUEST['pages']=1;
		}
		
		//Suche wird ausgeführt...
		if ( $_REQUEST['item'] ) {
			if ( $_REQUEST['title'] ) $sc[]="a.title LIKE '%".addslashes_like($_REQUEST['item'])."%'";
			if ( $_REQUEST['subtitle'] ) $sc[]="a.subtitle LIKE '%".addslashes_like($_REQUEST['item'])."%'";
			if ( $_REQUEST['teaser'] ) $sc[]="a.teaser LIKE '%".addslashes_like($_REQUEST['item'])."%'";
			
			//Artikelseiten mit Treffern
			$data=$db->fetch("SELECT artid FROM ".PRE."_articles_pages WHERE title LIKE '%".addslashes_like($_REQUEST['item'])."%' OR text LIKE '%".addslashes_like($_REQUEST['item'])."%' GROUP BY artid");
			if ( count($data) ) {
				$pagelist=array();
				foreach ( $data AS $res ) $pagelist[]=$res['artid'];
				if ( count($pagelist) ) $sc[]="a.id IN (".implode(',',$pagelist).")";
			}
			
			if ( is_array($sc) ) $where.=' AND ( '.implode(' OR ',$sc).' )';
		}
		
		if ( !$apx->session->get('section') && $_REQUEST['secid'] ) {
			$where.=" AND ( a.secid LIKE '%|".$_REQUEST['secid']."|%' OR a.secid='all' )";
		}
		
		if ( $_REQUEST['catid'] ) {
			if ( $set['articles']['subcats'] ) {
				$tree = $this->cat->getChildrenIds($_REQUEST['catid']);
				$tree[] = $_REQUEST['catid'];
				if ( is_array($tree) ) $where.=' AND catid IN ('.implode(',',$tree).')';
			}
			else $where.=" AND a.catid='".$_REQUEST['catid']."' ";
		}
		
		if ( $_REQUEST['userid'] ) {
			$where.=" AND a.userid='".$_REQUEST['userid']."' ";
		}
		
		$data=$db->fetch("SELECT id FROM ".PRE."_articles AS a WHERE 1 ".$where);
		$ids = get_ids($data, 'id');
		$ids[] = -1;
		$searchid = saveSearchResult('admin_articles', $ids, array(
			'item' => $_REQUEST['item'],
			'title' => $_REQUEST['title'],
			'subtitle' => $_REQUEST['subtitle'],
			'teaser' => $_REQUEST['teaser'],
			'pages' => $_REQUEST['pages'],
			'catid' => $_REQUEST['catid'],
			'secid' => $_REQUEST['secid'],
			'userid' => $_REQUEST['userid']
		));
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: action.php?action=articles.show&what='.$_REQUEST['what'].'&type='.$_REQUEST['type'].'&searchid='.$searchid);
		return;
	}
	
	
	//Vorgaben
	$_REQUEST['title'] = 1;
	$_REQUEST['subtitle'] = 1;
	$_REQUEST['teaser'] = 1;
	$_REQUEST['pages'] = 1;
	
	quicklink('articles.add');
	
	$layerdef[]=array('LAYER_ALL','action.php?action=articles.show',!$_REQUEST['what']);
	$layerdef[]=array('NORMALS','action.php?action=articles.show&amp;what=type&amp;type=normal',$_REQUEST['what']=='type' && $_REQUEST['type']=='normal');
	$layerdef[]=array('PREVIEWS','action.php?action=articles.show&amp;what=type&amp;type=preview',$_REQUEST['what']=='type' && $_REQUEST['type']=='preview');
	$layerdef[]=array('REVIEWS','action.php?action=articles.show&amp;what=type&amp;type=review',$_REQUEST['what']=='type' && $_REQUEST['type']=='review');
	$layerdef[]=array('LAYER_SELF','action.php?action=articles.show&amp;what=self',$_REQUEST['what']=='self');

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
		$searchRes = getSearchResult('admin_articles', $_REQUEST['searchid']);
		if ( $searchRes ) {
			list($resultIds, $resultMeta) = $searchRes;
			$_REQUEST['item'] = $resultMeta['item'];
			$_REQUEST['title'] = $resultMeta['title'];
			$_REQUEST['subtitle'] = $resultMeta['subtitle'];
			$_REQUEST['teaser'] = $resultMeta['teaser'];
			$_REQUEST['pages'] = $resultMeta['pages'];
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
	if ( is_array($apx->sections) && count($apx->sections) && !$apx->session->get('section') ) {
		foreach ( $apx->sections AS $res ) $seclist.='<option value="'.$res['id'].'"'.iif($_REQUEST['secid']==$res['id'],' selected="selected"').'>'.replace($res['title']).'</option>';
	}
	
	//Kategorien auflisten
	if ( $set['articles']['subcats'] ) $data = $this->cat->getTree(array('title'));
	else $data=$db->fetch("SELECT * FROM ".PRE."_articles_cat ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',$res['level']-1);
			$catlist.='<option value="'.$res['id'].'"'.iif($_REQUEST['catid']==$res['id'],' selected="selected"').'>'.$space.replace($res['title']).'</option>';
		}
	}
	
	//Benutzer auflisten
	$data=$db->fetch("SELECT b.userid,b.username FROM ".PRE."_articles AS a LEFT JOIN ".PRE."_user AS b USING (userid) WHERE a.userid!=0 GROUP BY userid ORDER BY username ASC");
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
	$apx->tmpl->assign('SPAGES',(int)$_REQUEST['pages']);
	$apx->tmpl->assign('SET_TEASER',$set['articles']['teaser']);
	$apx->tmpl->assign('WHAT',$_REQUEST['what']);
	$apx->tmpl->assign('TYPE',$_REQUEST['type']);
	$apx->tmpl->assign('EXTENDED',$searchRes);
	$apx->tmpl->parse('search');
	
	
	//Filter
	$layerFilter = '';
	if ( $_REQUEST['what']=='type' ) {
		$layerFilter = " AND a.type='".addslashes($_REQUEST['type'])."' ";
	}
	if ( $_REQUEST['what']=='self' ) {
		$layerFilter = " AND a.userid='".$apx->user->info['userid']."' ";
	}
	elseif ( $_REQUEST['what']=='send' ) {
		$layerFilter = " AND a.send_ip!='' ";
	}
	
	
	list($count)=$db->first("SELECT count(userid) FROM ".PRE."_articles AS a WHERE 1 ".$resultFilter.$layerFilter.section_filter(true, 'secid'));
	pages('action.php?action=articles.show&amp;what='.$_REQUEST['what'].iif($_REQUEST['what']=='type', '&amp;type='.$_REQUEST['type']).iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']).'&amp;sortby='.$_REQUEST['sortby'],$count);
	$data=$db->fetch("SELECT a.id,a.secid,a.type,a.title,a.starttime,a.endtime,a.endtime,a.allowcoms,a.allowrating,IF(a.sticky>=".time().",1,0) AS sticky,a.hits,b.userid,b.username,c.title AS catname FROM ".PRE."_articles AS a LEFT JOIN ".PRE."_user AS b USING(userid) LEFT JOIN ".PRE."_articles_cat AS c ON a.catid=c.id WHERE 1 ".$resultFilter.$layerFilter.section_filter(true, 'a.secid')." ".getorder($orderdef,'sticky DESC',1).getlimit());
	$this->show_print($data);
	orderstr($orderdef,'action.php?action=articles.show&amp;what='.$_REQUEST['what'].iif($_REQUEST['what']=='type', '&amp;type='.$_REQUEST['type']).iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']));
	save_index($_SERVER['REQUEST_URI']);
	
	//Legende
	$apx->tmpl->parse('legend');
	
	//Layer-Footer ausgeben
	$html->layer_footer();
}



//Artikel auflisten
function show_print($data) {
	global $set,$db,$apx,$html;
	
	$col[]=array('',1,'align="center"');
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
			
			//Wohin verlinken?
			if ( $res['type']=='normal' ) $link2file='articles';
			else $link2file=$res['type'].'s';
			
			$tmp=unserialize_section($res['secid']);
			$title=shorttext(strip_tags($res['title']),40);
			$link=mklink(
				$link2file.'.php?id='.$res['id'],
				$link2file.',id'.$res['id'].',0'.urlformat($res['title']).'.html',
				iif($set['main']['forcesection'],iif(unserialize_section($res['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
			);
			
			$tabledata[$i]['COL2']='<img src="'.HTTPDIR.getmodulepath('articles').'images/'.$res['type'].'.gif" alt="'.$apx->lang->get(strtoupper($res['type'])).'" title="'.$apx->lang->get(strtoupper($res['type'])).'" />';
			$tabledata[$i]['COL3']='<a href="'.$link.'" target="_blank">'.$title.'</a>';
			$tabledata[$i]['COL4']=replace($res['catname']);
			$tabledata[$i]['COL6']=number_format($res['hits'],0,'','.');
			
			if ( $res['username'] ) $tabledata[$i]['COL3'].='<br /><small>'.$apx->lang->get('BY').' '.replace($res['username']).'</small>';
			else $tabledata[$i]['COL3'].='<br /><small>'.$apx->lang->get('BY').' '.$apx->lang->get('GUEST').': <i>'.replace($res['send_username']).'</i></small>';
			
			if ( $res['starttime'] ) $tabledata[$i]['COL5']=mkdate($res['starttime'],'<br />');
			else $tabledata[$i]['COL5']='&nbsp;';
			
			list($pages)=$db->first("SELECT count(id) FROM ".PRE."_articles_pages WHERE artid='".$res['id']."'");
			
			//Optionen
			if ( $apx->user->has_right('articles.edit') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('articles.edit') ) ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'articles.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('articles.copy') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('articles.copy') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('copy.gif', 'articles.copy', 'id='.$res['id'], $apx->lang->get('COPY'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('articles.del') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('articles.del') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'articles.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $pages && ( !$res['starttime'] || $res['endtime']<time() ) && $apx->user->has_right('articles.enable') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('articles.enable') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('enable.gif', 'articles.enable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
			elseif ( $res['starttime'] && $apx->user->has_right('articles.disable') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('articles.disable') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('disable.gif', 'articles.disable', 'id='.$res['id'], $apx->lang->get('CORE_DISABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			
			//Kommentare + Bewertungen
			if ( $apx->is_module('comments') || $apx->is_module('ratings') ) $tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->is_module('comments') ) {
				list($comments)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='articles' AND mid='".$res['id']."' )");
				if ( $comments && ( $apx->is_module('comments') && $set['articles']['coms'] ) && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=articles&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			if ( $apx->is_module('ratings') ) {
				list($ratings)=$db->first("SELECT count(id) FROM ".PRE."_ratings WHERE ( module='articles' AND mid='".$res['id']."' )");
				if ( $ratings && ( $apx->is_module('ratings') && $set['articles']['ratings'] ) && $res['allowrating'] && $apx->user->has_right('ratings.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('ratings.gif', 'ratings.show', 'module=articles&mid='.$res['id'], $apx->lang->get('RATINGS').' ('.$ratings.')');
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



//***************************** Neuer Artikel *****************************
function add() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !in_array($_REQUEST['pageid'],array('new','conclusion','pics')) ) $_REQUEST['pageid']=(int)$_REQUEST['pageid'];
	
	//Artikelseiten
	if ( $_REQUEST['pageid'] ) {
		$this->pagecontent();
		return;
	}
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['type'] || !$_POST['title'] || !$_POST['catid'] ) infoNotComplete();
		elseif ( $_POST['catid']!='newcat' && !$this->category_is_open($_POST['catid']) ) die('you are not allowed to add articles to this category!');
		elseif ( !$this->update_artpic() ) { /*DO NOTHING*/ }
		else {
			
			//Autor
			if ( !$apx->user->has_spright('article.edit') ) $_POST['userid']=$apx->user->info['userid'];
			
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
			
			$_POST['addtime']=time();
			$_POST['artpic']=$this->artpicpath;
			$_POST['teaser']=$_POST['text'];
			$_POST['secid']=serialize_section($_POST['secid']);
			$_POST['links']=serialize($links);
			
			//Sticky Ende
			if ( $_POST['sticky'] && ($stickyend = maketime(3))!=0 ) {
				$_POST['sticky']=$stickyend;
			}
			elseif ( $_POST['sticky'] ) {
				$_POST['sticky'] = 3000000000;
			}
			
			$db->dinsert(PRE.'_articles','type,secid,prodid,catid,userid,title,subtitle,artpic,teaser,meta_description,galid,links,addtime,top,sticky,searchable,restricted,allowcoms,allowrating');
			$nid=$db->insert_id();
			logit('ARTICLES_ADD','ID #'.$nid);
			
			//Previews und Reviews Eintrag erstellen
			if ( in_array($_POST['type'],array('preview','review')) ) {
				$db->query("INSERT INTO ".PRE."_articles_".$_POST['type']."s (artid) VALUES ('".$nid."')");
			}
			
			//Inlinescreens
			mediamanager_setinline($nid);
			
			//Tags
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_articles_tags VALUES('".$nid."', '".$tagid."')");
			}
			
			if ( $_POST['catid']=='newcat' && $apx->user->has_right('articles.catadd') ) printJSRedirect('action.php?action=articles.catadd&addid='.$nid.'&pubnow='.intval($_POST['pubnow']).'&from=add');
			else printJSRedirect('action.php?action=articles.add&id='.$nid.'&pageid=new&pubnow='.intval($_POST['pubnow']));
		}
	}
	else {
		$_POST['type']='normal';
		$_POST['link1_title']=$apx->lang->get('LLINK');
		$_POST['link1_popup']=1;
		$_POST['searchable']=1;
		$_POST['allowcoms']=1;
		$_POST['allowrating']=1;
		$_POST['userid']=$apx->user->info['userid'];
		
		mediamanager('articles');
		
		//Links
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
		
		//Links füllen
		while ( count($linklist)<20 ) {
			$linklist[]=array('TITLE'=>$apx->lang->get('LLINK'),'POPUP'=>1);
		}
		
		$apx->tmpl->assign('TYPE',$_POST['type']);
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('PRODID',$_POST['prodid']);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('GALID',$_POST['galid']);
		$apx->tmpl->assign('CATLIST',$this->get_catlist());
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('SUBTITLE',compatible_hsc($_POST['subtitle']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('PIC_COPY',compatible_hsc($_POST['pic_copy']));
		$apx->tmpl->assign('LINK',$linklist);
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



//***************************** Artikel bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !in_array($_REQUEST['pageid'],array('new','conclusion','pics')) ) $_REQUEST['pageid']=(int)$_REQUEST['pageid'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//Artikelseiten
	if ( $_REQUEST['pageid'] ) {
		$this->pagecontent();
		return;
	}
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	//Artikel aktualisieren
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['catid'] ) infoNotComplete();
		elseif ( $_POST['catid']!='newcat' && !$this->category_is_open($_POST['catid']) ) die('you are not allowed to add articles to this category!');
		elseif ( !$this->update_artpic() ) { /*DO NOTHING*/ }
		else {
			
			//Links
			for ( $i=1; $i<=20; $i++ ) {
				if ( !$_POST['link'.$i.'_title'] || !$_POST['link'.$i.'_text'] || !$_POST['link'.$i.'_url'] ) continue;
				$links[]=array(
					'title' => $_POST['link'.$i.'_title'],
					'text' => $_POST['link'.$i.'_text'],
					'url' => $_POST['link'.$i.'_url'],
					'popup' => intval($_POST['link'.$i.'_popup'])
				);
			}
			
			//Veröffentlichung
			if ( $apx->user->has_right('article.enable') && isset($_POST['t_day_1']) ) {
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
			if ( $apx->user->has_spright('article.edit') && $_POST['userid'] ) {
				$addfields.=',userid';
			}
			
			$_POST['secid']=serialize_section($_POST['secid']);
			$_POST['artpic']=$this->artpicpath;
			$_POST['teaser']=$_POST['text'];
			$_POST['links']=serialize($links);
			
			$db->dupdate(PRE.'_articles',$addfield.'secid,prodid,catid,title,subtitle,artpic,teaser,meta_description,galid,links,top,sticky,searchable,restricted,allowcoms,allowrating'.$addfields,"WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('articles.edit')," AND userid='".$apx->user->info['userid']."'")." )");
			logit('ARTICLES_EDIT','ID #'.$_REQUEST['id']);
			
			//Tags
			$db->query("DELETE FROM ".PRE."_articles_tags WHERE id='".$_REQUEST['id']."'");
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_articles_tags VALUES('".$_REQUEST['id']."', '".$tagid."')");
			}
			
			if ( $_POST['catid']=='newcat' && $apx->user->has_right('articles.catadd') ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('location:action.php?action=articles.catadd&addid='.$_REQUEST['id'].'&from=edit'.iif($_POST['submit_finish'],'&finish=1'));
			}
			elseif ( $_POST['submit_finish'] ) {
				$this->finish_article();
			}
			else {
				list($pageid)=$db->first("SELECT id FROM ".PRE."_articles_pages WHERE artid='".$_REQUEST['id']."' ORDER BY ord ASC LIMIT 1");
				if ( !$pageid ) $pageid='new';
				printJSRedirect('action.php?action=articles.edit&id='.$_REQUEST['id'].'&pageid='.$pageid);
			}
			return;
		}
	}
	else {
		$res=$_POST=$db->first("SELECT userid,secid,prodid,catid,artpic,title,subtitle,teaser,links,meta_description,galid,top,sticky,searchable,restricted,allowcoms,allowrating,starttime,endtime FROM ".PRE."_articles WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('articles.edit')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1",true);
		$_POST['text']=$_POST['teaser'];
		$_POST['secid']=unserialize_section($_POST['secid']);
		
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
		
		
		mediamanager('articles');
		
		//Links
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
		
		//Veröffentlichung
		if ( $apx->user->has_right('articles.enable') && isset($_POST['t_day_1']) ) {
			$apx->tmpl->assign('STARTTIME',choosetime(1,0,maketime(1)));
			$apx->tmpl->assign('ENDTIME',choosetime(2,1,maketime(2)));
		}
		
		//Artpicpic
		$teaserpic = '';
		if ( $_POST['artpic'] ) {
			$teaserpicpath = $_POST['artpic'];
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
			FROM ".PRE."_articles_tags AS n
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE n.id='".$_REQUEST['id']."'
			ORDER BY t.tag ASC
		");
		$tags = get_ids($tagdata, 'tag');
		$_POST['tags'] = implode(', ', $tags);
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('PRODID',$_POST['prodid']);
		$apx->tmpl->assign('GALID',$_POST['galid']);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('CATLIST',$this->get_catlist());
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('SUBTITLE',compatible_hsc($_POST['subtitle']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('ARTPIC',$teaserpic);
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



//***************************** Artikel löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');

	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$res = $db->first("SELECT artpic FROM ".PRE."_articles WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('articles.del')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			$db->query("DELETE FROM ".PRE."_articles WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('articles.del')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			if ( !$db->affected_rows() ) die('access denied!');
		
			//Seiten + Kommentare + Bewertungen löschen (nur wenn ein Eintrag gelöscht wurde -> User hat Recht dazu!)
			$db->query("DELETE FROM ".PRE."_articles_pages WHERE ( artid='".$_REQUEST['id']."' )");
			$db->query("DELETE FROM ".PRE."_articles_previews WHERE ( artid='".$_REQUEST['id']."' ) LIMIT 1");
			$db->query("DELETE FROM ".PRE."_articles_reviews WHERE ( artid='".$_REQUEST['id']."' ) LIMIT 1");
			if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='articles' AND mid='".$_REQUEST['id']."' )");
			if ( $apx->is_module('ratings') ) $db->query("DELETE FROM ".PRE."_ratings WHERE ( module='articles' AND mid='".$_REQUEST['id']."' )");
			
			//Bilder löschen
			require_once(BASEDIR.'lib/class.mediamanager.php');
			$mm = new mediamanager();
			$picture = $res['artpic'];
			$poppic=str_replace('-thumb.','.',$picture);
			if ( $picture && file_exists(BASEDIR.getpath('uploads').$picture) ) $mm->deletefile($picture);
			if ( $poppic && file_exists(BASEDIR.getpath('uploads').$poppic) ) $mm->deletefile($poppic);
			
			//Tags löschen
			$db->query("DELETE FROM ".PRE."_articles_tags WHERE id='".$_REQUEST['id']."'");
			
			logit('ARTICLES_DEL','ID #'.$_REQUEST['id']);	
			printJSRedirect(get_index('articles.show'));
		}
	}
	else {
		list($title, $subtitle) = $db->first("SELECT title, subtitle FROM ".PRE."_articles WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title.($subtitle ? ' - '.$subtitle : '')))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
	}
}



//***************************** Artikel kopieren *****************************
function copy() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$res=$db->first("SELECT type,secid,catid,userid,artpic,title,subtitle,teaser,galid,links,sticky,top,allowcoms,allowrating,searchable,restricted,pictures,pictures_nextid FROM ".PRE."_articles WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('articles.copy')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			foreach ( $res AS $key => $val ) $_POST[$key]=$val;
			$_POST['title']=$apx->lang->get('COPYOF').$_POST['title'];
			$_POST['addtime']=time();
			
			//Insert
			$db->dinsert(PRE.'_articles','type,secid,catid,userid,title,subtitle,teaser,galid,links,addtime,sticky,top,allowcoms,allowrating,searchable,restricted,pictures,pictures_nextid');
			$nid=$db->insert_id();
			$oldId = $_REQUEST['id'];
			$newId = $nid;
			
			//Bilder kopieren
			$newPicture = str_replace($oldId, $newId, $res['artpic']);
			copy_with_thumbnail($res['artpic'], $newPicture);
			
			//Angefügte Bilder kopieren
			$newAttPics = array();
			$i=0;
			$attPics = unserialize($res['pictures']);
			foreach ( $attPics AS $attPic ) {
				++$i;
				$newAttPic = str_replace('-'.$oldId.'-', '-'.$newId.'-', $attPic['thumbnail']);
				copy_with_thumbnail($attPic['thumbnail'], $newAttPic);
				$newAttPics[$i] = array(
					'picture' => str_replace('-'.$oldId.'-', '-'.$newId.'-', $attPic['picture']),
					'thumbnail' => str_replace('-'.$oldId.'-', '-'.$newId.'-', $attPic['thumbnail'])
				);
			}
			
			//Update Artikel
			$db->query("UPDATE ".PRE."_articles SET artpic='".addslashes($newPicture)."', pictures='".addslashes(serialize($newAttPics))."' WHERE id='".$newId."' LIMIT 1");
			
			//Preview kopieren
			if ( $res['type']=='preview' ) {
				$res=array();
				$_POST=array();
				$res=$db->first("SELECT * FROM ".PRE."_articles_previews WHERE artid='".$_REQUEST['id']."' LIMIT 1");
				foreach ( $res AS $key => $val ) $_POST[$key]=$val;
				$_POST['artid']=$nid;
				$db->dinsert(PRE.'_articles_previews','artid,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10,impression,conclusion');
			}
			
			//Review kopieren
			elseif ( $res['type']=='review' ) {
				$res=array();
				$_POST=array();
				$res=$db->first("SELECT * FROM ".PRE."_articles_reviews WHERE artid='".$_REQUEST['id']."' LIMIT 1");
				foreach ( $res AS $key => $val ) $_POST[$key]=$val;
				$_POST['artid']=$nid;
				$db->dinsert(PRE.'_articles_reviews','artid,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10,rate1,rate2,rate3,rate4,rate5,rate6,rate7,rate8,rate9,rate10,final_rate,positive,negative,conclusion,award');
			}
			
			//Pages
			$data=$db->fetch("SELECT title,text,addtime,ord FROM ".PRE."_articles_pages WHERE artid='".$_REQUEST['id']."'");
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					$db->query("INSERT INTO ".PRE."_articles_pages VALUES ('','".$nid."','".addslashes($res['title'])."','".addslashes($res['text'])."','".time()."','".$res['ord']."')");
				}
			}
			
			logit('ARTICLES_COPY','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('articles.show'));
		}
	}
	else {
		list($title, $subtitle) = $db->first("SELECT title, subtitle FROM ".PRE."_articles WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title.($subtitle ? ' - '.$subtitle : '')))));
		tmessageOverlay('copy',array('ID'=>$_REQUEST['id']));
	}
}



//***************************** Artikel aktivieren *****************************
function enable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			list($check)=$db->first("SELECT count(id) FROM ".PRE."_articles_pages WHERE artid='".$_REQUEST['id']."'");
			if ( !$check ) die('can not enable article! no pages created.');
			
			$starttime=maketime(1);
			$endtime=maketime(2);
			if ( !$endtime || $endtime<=$starttime ) $endtime=3000000000;
			
			$db->query("UPDATE ".PRE."_articles SET starttime='".$starttime."',endtime='".$endtime."' WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('articles.enable')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			logit('ARTICLES_ENABLE','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('articles.show'));
		}
	}
	else {
		list($title, $subtitle) = $db->first("SELECT title, subtitle FROM ".PRE."_articles WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('TITLE', compatible_hsc($title.($subtitle ? ' - '.$subtitle : '')));
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('STARTTIME',choosetime(1,0,time()));
		$apx->tmpl->assign('ENDTIME',choosetime(2,1));
		tmessageOverlay('enable');
	}
}



//***************************** Artikel widerrufen *****************************
function disable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');

	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("UPDATE ".PRE."_articles SET starttime=0,endtime=0 WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('articles.disable')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			logit('ARTICLES_DISABLE','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('articles.show'));
		}
	}
	else {
		list($title, $subtitle) = $db->first("SELECT title, subtitle FROM ".PRE."_articles WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title.($subtitle ? ' - '.$subtitle : '')))));
		tmessageOverlay('disable',array('ID'=>$_REQUEST['id']));
	}
}



////////////////////////////////////////////////////////////////////////////////////////// ARTIKEL MASTER

function pagecontent() {
	global $set,$db,$apx;
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( !$this->access_pages($_REQUEST['id']) ) die('you have no right to access this article!');
	
	//Artikel-Infos auslesen
	list($type,$title)=$db->first("SELECT type,title FROM ".PRE."_articles WHERE id='".$_REQUEST['id']."' LIMIT 1");
	$this->title=$title;
	$this->type=$type;
	
	///////////////////////////////////
	
	//PREVIEW / PREVIEW: Fazit
	if ( in_array($this->type,array('preview','review')) && $_REQUEST['pageid']=='conclusion' ) {
		$apx->lang->dropaction('articles','conclusion');
		if ( $this->type=='preview' ) $this->conclusion_preview();
		else $this->conclusion_review();
	}
	
	//Bilderserie
	elseif ( $_REQUEST['pageid']=='pics' ) {
		$apx->lang->dropaction('articles','pictures');
		$this->pictures();
	}
	
	//Artikel-Seiten bearbeiten
	else {
		$apx->lang->dropaction('articles','padd');
		$this->page_add_edit();
	}
}



////////////////////////////////////////////////////////////////////////////////////////// SEITEN

//***************************** Artikel-Seiten Index *****************************
function page_index() {
	global $set,$db,$apx,$html;
	
	echo'<h2>'.$apx->lang->get(iif($this->type=='normal','ARTICLE',strtoupper($this->type))).': '.$this->title.'</h2>';
	
	$col[]=array('',1,'align="center"');
	$col[]=array('COL_TITLE',100,'class="title"');
	
	//Seiten auflisten
	$data=$db->fetch("SELECT id,title FROM ".PRE."_articles_pages WHERE artid='".$_REQUEST['id']."' ORDER BY ord ASC");
	if ( !$pmax=count($data) ) return;
	foreach ( $data AS $res ) {
		++$i;
		
		if ( $this->type=='normal' ) $link2file='articles';
		else $link2file=$this->type.'s';
		
		$pagetitle=shorttext(strip_tags($res['title']),50);
		$link=mklink(
			$link2file.'.php?id='.$_REQUEST['id'].'&amp;page='.$i,
			$link2file.',id'.$_REQUEST['id'].','.$i.urlformat($this->title).'.html'
		);
		
		$tabledata[$i]['COL1']=$i.'.';
		$tabledata[$i]['COL2']='<a href="'.$link.'" target="_blank">'.$pagetitle.'</a>';
		
		//Optionen
		$tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', $_REQUEST['action'], 'id='.$_REQUEST['id'].'&pageid='.$res['id'].'&pubnow='.$_REQUEST['pubnow'], $apx->lang->get('CORE_EDIT'));
		$tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', $_REQUEST['action'], 'id='.$_REQUEST['id'].'&pageid='.$_REQUEST['pageid'].'&del='.$res['id'].'&pubnow='.$_REQUEST['pubnow'], $apx->lang->get('CORE_DEL'));
		 
		$tabledata[$i]['OPTIONS'].='&nbsp;';
		
		if ( $i!=1 ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', $_REQUEST['action'], 'id='.$_REQUEST['id'].'&pageid='.$_REQUEST['pageid'].'&move='.$res['id'].'&direction=up&pubnow='.$_REQUEST['pubnow'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
		else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
		
		if ( $i!=$pmax ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', $_REQUEST['action'], 'id='.$_REQUEST['id'].'&pageid='.$_REQUEST['pageid'].'&move='.$res['id'].'&direction=down&pubnow='.$_REQUEST['pubnow'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
		else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	echo'<br />&nbsp;';
}



//***************************** Artikel-Seiten bearbeiten *****************************
function page_add_edit() {
	global $set,$db,$apx;
	
	//Artikel-Seite löschen
	if ( $_REQUEST['del'] ) {
		$apx->lang->dropaction('articles','pdel');
		$this->page_del();
		return; 
	}
	
	//Artikel-Seiten anordnen
	elseif ( $_REQUEST['move'] && $_REQUEST['direction'] ) {
		$this->page_move();
		return; 
	}
	
	list($brother1,$brother2)=$this->get_brothers();
	list($pagecount)=$db->first("SELECT count(id) FROM ".PRE."_articles_pages WHERE artid='".$_REQUEST['id']."'");
	
	if ( $_POST['send']==1 ) {
		
		//Eine Seite zurück, wenn nichts ausgefüllt
		if ( $_POST['submit_prev'] && !$_POST['title'] && !$_POST['text'] ) {
			list($brother1,$brother2)=$this->get_brothers();
			printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid='.$brother1); 
		}
		
		//Artikel beenden, wenn nichts ausgefüllt
		elseif ( $pagecount && $_POST['submit_finish'] && !$_POST['title'] && !$_POST['text'] ) {
			$this->finish_article();
			return;
		}
		
		//Seiten beenden, wenn nichts ausgefüllt
		elseif ( $pagecount && $_POST['submit_walk'] && !$_POST['title'] && !$_POST['text']  ) {
			if ( $this->type=='normal' ) {
				printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid=pics');
				return;
			}
			else {
				printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid=conclusion');
				return;
			}
		}
		
		//Seite erstellen
		elseif ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['text'] ) infoNotComplete();
		else {
			
			//MYSQL einfügen: Neue Seite
			if ( $_REQUEST['pageid']=='new' ) {
				$_POST['artid']=$_REQUEST['id'];
				$_POST['addtime']=time();
				list($lastord)=$db->first("SELECT ord FROM ".PRE."_articles_pages WHERE artid='".$_REQUEST['id']."' ORDER BY ord DESC LIMIT 1");
				$_POST['ord']=$lastord+1;
				$db->dinsert(PRE.'_articles_pages','artid,title,text,ord');
				
				//Inlinescreens
				mediamanager_setinline($_REQUEST['id']);
			}
			
			//MYSQL einfügen: Aktualisieren
			else {
				$db->dupdate(PRE.'_articles_pages','title,text',"WHERE ( id='".$_REQUEST['pageid']."' AND artid='".$_REQUEST['id']."' ) LIMIT 1");
			}
			
			//WEITER: Vorherige Seite
			if ( $_POST['submit_prev'] ) {
				printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid='.$brother1); 
			}
			
			//WEITER: Neue Seite
			elseif ( $_POST['submit_next'] && !$brother2 ) {
				printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid=new');
			}
			
			//WEITER: Nächste Seite
			elseif ( $_POST['submit_next'] ) {
				printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid='.$brother2);
			}
			
			//ARTIKEL BEENDEN
			else {
				if ( $_POST['submit_finish'] ) {
					$this->finish_article();
					return;
				}
				elseif ( $this->type=='normal' ) {
					printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid=pics');
					return;
				}
				else {
					printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid=conclusion');
					return;
				}
			}
		}
	}
	else {
		if ( $_REQUEST['pageid']!='new' ) {
			list(
				$_POST['title'],
				$_POST['text']
			)=$db->first("SELECT title,text FROM ".PRE."_articles_pages WHERE ( id='".$_REQUEST['pageid']."' AND artid='".$_REQUEST['id']."' ) LIMIT 1");
		}
		
		$this->page_index();
		mediamanager('articles');
		
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('PAGEID',$_REQUEST['pageid']);
		$apx->tmpl->assign('SET_PREVIOUS',$brother1);
		$apx->tmpl->assign('SET_NEWPAGE',!$brother2);
		$apx->tmpl->assign('PUBNOW',(int)$_REQUEST['pubnow']);
		$apx->tmpl->assign('TYPE',$this->type);
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('ACTION',iif($_REQUEST['action']=='articles.add','add','edit'));
		$apx->tmpl->parse('padd_pedit');
	}
}



//***************************** Seite löschen *****************************
function page_del() {
	global $set,$db,$apx;
	$_REQUEST['del']=(int)$_REQUEST['del'];
	if ( !$_REQUEST['del'] ) die('missing delID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			//Wenn aktuelle Seite gelöscht wird neue ID bestimmen
			if ( $_REQUEST['pageid']==$_REQUEST['del'] ) {
				list($thisord)=$db->first("SELECT ord FROM ".PRE."_articles_pages WHERE ( artid='".$_REQUEST['id']."' AND id='".$_REQUEST['del']."' ) LIMIT 1");
				list($lowerbrother)=$db->first("SELECT id FROM ".PRE."_articles_pages WHERE ( artid='".$_REQUEST['id']."' AND ord<'".$thisord."' ) ORDER BY ord DESC LIMIT 1");
				if ( $lowerbrother ) $pageid=$lowerbrother;
				else $pageid='new';
			}
			else $pageid=$_REQUEST['pageid'];
			
			$db->query("DELETE FROM ".PRE."_articles_pages WHERE ( id='".$_REQUEST['del']."' AND artid='".$_REQUEST['id']."' ) LIMIT 1");
			printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pageid='.$pageid.'&pubnow='.$_REQUEST['pubnow']);
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_articles_pages WHERE id='".$_REQUEST['del']."' AND artid='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('pdel',array('ID'=>$_REQUEST['id'],'PAGEID'=>$_REQUEST['pageid'],'DEL'=>$_REQUEST['del'],'PUBNOW'=>$_REQUEST['pubnow']));
	}
}



//***************************** Seiten anordnen *****************************
function page_move() {
	global $set,$db,$apx;
	$_REQUEST['move']=(int)$_REQUEST['move'];
	if ( !checkToken() ) printInvalidToken();
	else {
		
		list($ord1)=$db->first("SELECT ord FROM ".PRE."_articles_pages WHERE ( artid='".$_REQUEST['id']."' AND id='".$_REQUEST['move']."' ) LIMIT 1");
		
		//Nach unten
		if ( $_REQUEST['direction']=='down' ) {
			list($brother,$ord2)=$db->first("SELECT id,ord FROM ".PRE."_articles_pages WHERE ( artid='".$_REQUEST['id']."' AND ord>'".$ord1."' ) ORDER BY ord ASC LIMIT 1");
			if ( !$brother ) die('no lower brother found!');
		}
		
		//Nach oben
		else {
			list($brother,$ord2)=$db->first("SELECT id,ord FROM ".PRE."_articles_pages WHERE ( artid='".$_REQUEST['id']."' AND ord<'".$ord1."' ) ORDER BY ord DESC LIMIT 1");
			if ( !$brother ) die('no upper brother found!');
		}
		
		$db->query("UPDATE ".PRE."_articles_pages SET ord=".($ord1+$ord2)."-ord WHERE ( artid='".$_REQUEST['id']."' AND id IN ('".$_REQUEST['move']."','".$brother."') ) ");
		
		header("HTTP/1.1 301 Moved Permanently");
		header('location:action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pageid='.$_REQUEST['pageid'].'&pubnow='.intval($_REQUEST['pubnow']));
	}
}



////////////////////////////////////////////////////////////////////////////////////////// FAZITS

//***************************** Fazit: Vorschau *****************************
function conclusion_preview() {
	global $set,$db,$apx;
	
	//Aktualisieren
	if ( $_POST['send'] ) {
		
		//Custom-Felder prüfen
		$custom_failed=false;
		/*for ( $i=1; $i<=10; $i++ ) {
			if ( !$set['articles']['custom_preview'][$i-1] ) continue;
			if ( !$_POST['custom'.$i] ) $custom_failed=true;
		}*/
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['impression'] || !$_POST['conclusion'] || $custom_failed ) infoNotComplete();
		else {
			$db->dupdate(PRE.'_articles_previews','custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10,impression,conclusion',"WHERE artid='".$_REQUEST['id']."' LIMIT 1");
			
			//Artikel beenden
			if ( $_POST['submit_finish'] ) {
				$this->finish_article();
				return;
			}
			
			//Weiter zur Bilderserie
			else {
				printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid=pics');
			}
		}
	}
	else {
		$res=$db->first("SELECT * FROM ".PRE."_articles_previews WHERE artid='".$_REQUEST['id']."' LIMIT 1",1);
		foreach ( $res AS $key => $value ) $_POST[$key]=$value;
		
		
		echo'<h2>'.$apx->lang->get(iif($this->type=='normal','ARTICLE',strtoupper($this->type))).': '.$this->title.'</h2>';
		
		//Custom-Felder
		for ( $i=1; $i<=10; $i++ ) {
			if ( !$set['articles']['custom_preview'][$i-1] ) continue;
			$apx->tmpl->assign('CUSTOM'.$i.'_TITLE',$set['articles']['custom_preview'][$i-1]);
			$apx->tmpl->assign('CUSTOM'.$i,compatible_hsc($_POST['custom'.$i]));
		}
		
		$apx->tmpl->assign('IMPRESSION',compatible_hsc($_POST['impression']));
		$apx->tmpl->assign('CONCLUSION',compatible_hsc($_POST['conclusion']));
		$apx->tmpl->assign('PUBNOW',(int)$_REQUEST['pubnow']);
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('ACTION',iif($_REQUEST['action']=='articles.add','add','edit'));
		
		$apx->tmpl->parse('conclusion_preview');
	}
}



//***************************** Fazit: Tests *****************************
function conclusion_review() {
	global $set,$db,$apx;
	
	//Aktualisieren
	if ( $_POST['send'] ) {
		
		//Custom-Felder prüfen
		$custom_failed=false;
		/*for ( $i=1; $i<=10; $i++ ) {
			if ( !$set['articles']['custom_review'][$i-1] ) continue;
			if ( !$_POST['custom'.$i] ) $custom_failed=true;
		}*/
		
		//Rating-Felder prüfen
		$rating_failed=false;
		/*for ( $i=1; $i<=10; $i++ ) {
			if ( !$set['articles']['ratefields'][$i-1] ) continue;
			if ( !$_POST['rate'.$i] ) $rating_failed=true;
		}*/
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['conclusion'] || !$_POST['final_rate'] || $custom_failed || $rating_failed ) infoNotComplete();
		else {
			$db->dupdate(PRE.'_articles_reviews','custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10,rate1,rate2,rate3,rate4,rate5,rate6,rate7,rate8,rate9,rate10,final_rate,positive,negative,award,conclusion',"WHERE artid='".$_REQUEST['id']."' LIMIT 1");
			
			//Artikel beenden
			if ( $_POST['submit_finish'] ) {
				$this->finish_article();
				return;
			}
			
			//Weiter zur Bilderserie
			else {
				printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid=pics');
			}
		}
	}
	else {
		$res=$db->first("SELECT * FROM ".PRE."_articles_reviews WHERE artid='".$_REQUEST['id']."' LIMIT 1",1);
		foreach ( $res AS $key => $value ) $_POST[$key]=$value;
		
		
		echo'<h2>'.$apx->lang->get(iif($this->type=='normal','ARTICLE',strtoupper($this->type))).': '.$this->title.'</h2>';
		
		//Custom-Felder
		for ( $i=1; $i<=10; $i++ ) {
			if ( !$set['articles']['custom_review'][$i-1] ) continue;
			$apx->tmpl->assign('CUSTOM'.$i.'_TITLE',$set['articles']['custom_review'][$i-1]);
			$apx->tmpl->assign('CUSTOM'.$i,compatible_hsc($_POST['custom'.$i]));
		}
		
		//Rating-Felder
		for ( $i=1; $i<=10; $i++ ) {
			if ( !$set['articles']['ratefields'][$i-1] ) continue;
			$apx->tmpl->assign('RATE'.$i.'_TITLE',$set['articles']['ratefields'][$i-1]);
			$apx->tmpl->assign('RATE'.$i,compatible_hsc($_POST['rate'.$i]));
		}
		
		//Awards auflisten
		if ( count($set['articles']['awards']) ) {
			foreach ( $set['articles']['awards'] AS $value ) {
				$awarddata[]['ID']=$value;
			}
		}
		
		$apx->tmpl->assign('FINAL_RATE',compatible_hsc($_POST['final_rate']));
		$apx->tmpl->assign('AV_AWARD',$awarddata);
		$apx->tmpl->assign('AWARD',compatible_hsc($_POST['award']));
		$apx->tmpl->assign('POSITIVE',compatible_hsc($_POST['positive']));
		$apx->tmpl->assign('NEGATIVE',compatible_hsc($_POST['negative']));
		$apx->tmpl->assign('CONCLUSION',compatible_hsc($_POST['conclusion']));
		$apx->tmpl->assign('PUBNOW',(int)$_REQUEST['pubnow']);
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('ACTION',iif($_REQUEST['action']=='articles.add','add','edit'));
		
		$apx->tmpl->parse('conclusion_review');
	}
}



////////////////////////////////////////////////////////////////////////////////////////// BILDERSERIE

//***************************** Bilderserie anfügen *****************************
function pictures() {
	global $set,$db,$apx;
	
	//Notwendig weil 1.7.0 beim Kopieren ein Bild mit ID 0 erzeugt hatte :/
	if ( isset($_REQUEST['delpic']) ) {
		$_REQUEST['delpic']=(int)$_REQUEST['delpic'];
	}
	else {
		$_REQUEST['delpic'] = null;
	}
	
	//Bilder auslesen
	list($pictures,$nextid)=$db->first("SELECT pictures,pictures_nextid FROM ".PRE."_articles WHERE id='".$_REQUEST['id']."'");
	$pictures=unserialize($pictures);
	if ( !is_array($pictures) ) $pictures=array();
	
	//Bild löschen
	if ( isset($_REQUEST['delpic']) && isset($pictures[$_REQUEST['delpic']]) ) {
		if ( $_POST['delpic'] ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				$picinfo=$pictures[$_REQUEST['delpic']];
				
				require(BASEDIR.'lib/class.mediamanager.php');
				$mm=new mediamanager;
				if ( $picinfo['thumbnail'] && file_exists(BASEDIR.getpath('uploads').$picinfo['thumbnail']) ) $mm->deletefile($picinfo['thumbnail']);
				if ( $picinfo['picture'] && file_exists(BASEDIR.getpath('uploads').$picinfo['picture']) ) $mm->deletefile($picinfo['picture']);
				
				unset($pictures[$_REQUEST['delpic']]);
				$db->query("UPDATE ".PRE."_articles SET pictures='".addslashes(serialize($pictures))."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
				printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid=pics');
			}
		}
		else {
			tmessageOverlay('picdel', array('ID' => $_REQUEST['id'], 'DELPIC' => $_REQUEST['delpic']));
		}
	}
	
	//Neue Bilder hinzufügen
	elseif ( $_POST['send'] ) {
		require(BASEDIR.'lib/class.mediamanager.php');
		$mm=new mediamanager;
		require_once(BASEDIR.'lib/class.image.php');
		$img=new image;
		
		//Bilder abarbeiten
		for ( $i=1; $i<=5; $i++ ) {
			if ( !$_FILES['upload'.$i]['tmp_name'] ) continue;
			
			$ext=strtolower($mm->getext($_FILES['upload'.$i]['name']));
			if ( $ext=='gif' ) $ext='jpg';
			
			$newname='pic'.'-'.$_POST['id'].'-'.$nextid.'.'.$ext;
			$newfile='articles/gallery/'.$newname;
			$thumbname='pic'.'-'.$_POST['id'].'-'.$nextid.'-thumb.'.$ext;
			$thumbfile='articles/gallery/'.$thumbname;
			
			//Erfolgreichen Upload prüfen
			if ( !$mm->uploadfile($_FILES['upload'.$i],'articles/gallery',$newname) ) continue;
			
			//Bild einlesen
			list($picture,$picturetype)=$img->getimage($newfile);
			
			
			//////// THUMBNAIL
			$thumbnail=$img->resize($picture,$set['articles']['thumbwidth'],$set['articles']['thumbheight'],$set['articles']['artpic_quality']);
			$img->saveimage($thumbnail,$picturetype,$thumbfile);
			
			
			//////// BILD
			
			//Bild skalieren
			if ( $picture!==false && !$_POST['noresize'.$i] && $set['articles']['picwidth'] && $set['articles']['picheight'] ) {
				$scaled=$img->resize(
					$picture,
					$set['articles']['picwidth'],
					$set['articles']['picheight'],
					$set['articles']['artpic_quality'],
					0
				);
				
				if ( $scaled!=$picture ) imagedestroy($picture);
				$picture=$scaled;
			}
			
			//Wasserzeichen einfügen
			if ( $picture!==false && $set['articles']['watermark'] && $_POST['watermark'.$i] ) {
				$watermarked=$img->watermark(
					$picture,
					$set['articles']['watermark'],
					$set['articles']['watermark_position'],
					$set['articles']['watermark_transp']
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
			
			$pictures[$nextid]=array(
				'picture' => $newfile,
				'thumbnail' => $thumbfile
			);
			
			++$nextid;
		}
		
		//Bilder eintragen
		$db->query("UPDATE ".PRE."_articles SET pictures='".addslashes(serialize($pictures))."',pictures_nextid='".intval($nextid)."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
		
		//Artikel beenden
		if ( $_POST['submit_finish'] ) {
			$this->finish_article();
			return;
		}
		
		//Weitere Bilder anfügen
		else {
			printJSRedirect('action.php?action='.$_REQUEST['action'].'&id='.$_REQUEST['id'].'&pubnow='.$_REQUEST['pubnow'].'&pageid=pics');
		}
	}
	else {
		
		echo'<h2>'.$apx->lang->get(iif($this->type=='normal','ARTICLE',strtoupper($this->type))).': '.$this->title.'</h2>';
		
		//Bilderserie auflisten
		foreach ( $pictures AS $id => $res ) {
			++$i;
			$picdata[$i]['ID']=$id;
			$picdata[$i]['IMAGE']=HTTPDIR.getpath('uploads').$res['thumbnail'];
			$picdata[$i]['LINK']=HTTPDIR.getpath('uploads').$res['picture'];
			$picdata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', $_REQUEST['action'], 'id='.$_REQUEST['id'].'&pageid=pics&delpic='.$id.'&pubnow='.$_REQUEST['pubnow'], $apx->lang->get('CORE_DEL'));
		}
		
		$apx->tmpl->assign('SET_WATERMARK',iif($set['articles']['watermark'],1,0));
		$apx->tmpl->assign('SET_NORESIZE',iif($set['articles']['picwidth'] && $set['articles']['picheight'],1,0));
		$apx->tmpl->assign('PIC',$picdata);
		$apx->tmpl->assign('PUBNOW',(int)$_REQUEST['pubnow']);
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('ACTION',iif($_REQUEST['action']=='articles.add','add','edit'));
		
		$apx->tmpl->parse('pictures');
	}
}



////////////////////////////////////////////////////////////////////////////////////////// ARTIKEL FERTIGSTELLEN

function finish_article() {
	global $set,$db,$apx;
	if ( $_REQUEST['action']=='articles.add' ) {
		
		//Veröffentlichung
		if ( $apx->user->has_right('articles.enable') && $_POST['pubnow'] ) {
			$db->query("UPDATE ".PRE."_articles SET starttime='".time()."',endtime='3000000000' WHERE id='".$_REQUEST['id']."' LIMIT 1");
		}
		
		printJSRedirect('action.php?action=articles.show');
	}
	else printJSRedirect(get_index('articles.show'));
}



////////////////////////////////////////////////////////////////////////////////////////// KATEGORIEN

//***************************** Kategorien zeigen *****************************
function catshow() {
	global $set,$db,$apx,$html;
	
	//Struktur reparieren
	if ( $_REQUEST['repair'] && $set['articles']['subcats'] ) {
		$this->cat->repair();
		echo 'Repair done!';
		return;
	}
	
	quicklink('articles.catadd');
	
	//DnD-Hinweis
	if ( $set['articles']['subcats'] && $apx->user->has_right('articles.edit') ) {
		echo '<p class="hint">'.$apx->lang->get('USEDND').'</p>';
	}
	
	$col[]=array('ID',0,'align="center"');
	$col[]=array('COL_CATNAME',75,'class="title"');
	$col[]=array('COL_ARTICLES',25,'align="center"');
	
	if ( $set['articles']['subcats'] ) {
		$data=$this->cat->getTree(array('title', 'open'));
	}
	else {
		$orderdef[0]='title';
		$orderdef['title']=array('title','ASC','COL_CATNAME');
		
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_articles_cat");
		pages('action.php?action=articles.catshow',$count);
		$data=$db->fetch("SELECT * FROM ".PRE."_articles_cat ".getorder($orderdef).getlimit());
	}
	
	if ( count($data) ) {
		
		//Ausgabe erfolgt
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['open'] ) {
				list($articles)=$db->first("SELECT count(id) FROM ".PRE."_articles WHERE catid='".$res['id']."'");
			}
			
			$tabledata[$i]['COL1']=$res['id'];
			$tabledata[$i]['COL2']=replace($res['title']);
			$tabledata[$i]['COL3']=iif(isset($articles),$articles,'&nbsp;');
			$tabledata[$i]['CLASS'] = 'l'.($res['level']-1).($res['children'] ? ' haschildren' : '').($res['level']>1 ? ' hidden' : '').($res['iscat'] ? ' dark' : '');
			$tabledata[$i]['ID'] = 'node:'.$res['id'];
			
			//Optionen
			if ( $apx->user->has_right('articles.catedit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'articles.catedit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('articles.catdel') && !$articles ) $tabledata[$i]['OPTIONS'].='<span class="ifhasnochildren">'.optionHTMLOverlay('del.gif', 'articles.catdel', 'id='.$res['id'], $apx->lang->get('CORE_DEL')).'</span><span class="ifhaschildren"><img alt="" src="design/ispace.gif"/></span>';
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('articles.catclean') && $articles ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('clean.gif', 'articles.catclean', 'id='.$res['id'], $apx->lang->get('CLEAN'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Anordnen nur bei Unterkategorien
			/*if ( $set['articles']['subcats'] ) {
				$tabledata[$i]['OPTIONS'].='&nbsp;';
				if ( $apx->user->has_right('articles.catmove') && $follow[$res['id']]['prev'] ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'articles.catmove', 'direction=up&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
				if ( $apx->user->has_right('articles.catmove') && $follow[$res['id']]['next'] ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'articles.catmove', 'direction=down&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
			}*/
			
			unset($articles);
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	
	//Mit Unter-Kategorien
	if ( $set['articles']['subcats'] ) {
		echo '<div class="treeview" id="tree">';
		$html->table($col);
		echo '</div>';
		
		$open = $apx->session->get('articles_cat_open');
		$open = dash_unserialize($open);
		$opendata = array();
		foreach ( $open AS $catid ) {
			$opendata[] = array(
				'ID' => $catid
			);
		}
		$apx->tmpl->assign('OPEN', $opendata);
		$apx->tmpl->assign('EDIT_ALLOWED', $apx->user->has_right('articles.edit'));
		$apx->tmpl->parse('catshow_js');
	}
	
	//Normale Kategorien
	else {
		$html->table($col);
		orderstr($orderdef,'action.php?action=articles.catshow');
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
				logit('ARTICLES_CATADD','ID #'.$nid);
				
				//Beitrag der Kategorie hinzufügen
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
				}
				else {
					printJSRedirect('action.php?action=articles.catshow');
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
				logit('ARTICLES_CATADD',"ID #".$nid);
				
				//Beitrag der Kategorie hinzufügen
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
				}
				else {
					printJSRedirect('action.php?action=articles.catshow');
				}
			}
		}
	}
	else {
		$_POST['open']=1;
		
		
		//Baum
		if ( $set['articles']['subcats'] ) {
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
		list($articles)=$db->first("SELECT count(id) FROM ".PRE."_articles WHERE catid='".$_REQUEST['id']."'");
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['id'] || !$_POST['parent'] || !$_POST['title'] ) infoNotComplete();
		elseif ( !$_POST['open'] && $articles ) info($apx->lang->get('INFO_CONTAINSARTICLES'));
		else {
			if ( $_POST['groupid'][0]=='all' ) $_POST['forgroup']='all';
			else $_POST['forgroup']=serialize($_POST['groupid']);
			
			$this->cat->moveNode($_REQUEST['id'], intval($_POST['parent']), array(
				'title' => $_POST['title'],
				'icon' => $_POST['icon'],
				'open' => $_POST['open'],
				'forgroup' => $_POST['forgroup']
			));
			logit('ARTICLES_CATEDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('articles.catshow'));
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
		if ( $set['articles']['subcats'] ) {
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
	
	list($articles)=$db->first("SELECT count(id) FROM ".PRE."_articles WHERE catid='".$_REQUEST['id']."'");
	if ( $articles ) die('category still contains articles!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$this->cat->deleteNode($_REQUEST['id']);
			logit('ARTICLES_CATDEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('articles.catshow'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_articles_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
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
			$db->query("UPDATE ".PRE."_articles SET catid='".intval($_POST['moveto'])."' WHERE catid='".$_REQUEST['id']."'");
			logit('ARTICLES_CATCLEAN',"ID #".$_REQUEST['id']);
			
			//Kategorie löschen
			if ( $_POST['delcat'] ) {
				$this->cat->deleteNode($_REQUEST['id']);
				logit('ARTICLES_CATDEL',"ID #".$_REQUEST['id']);
			}
			
			printJSRedirect(get_index('articles.catshow'));
			return;
		}
	}
	
	if ( $set['articles']['subcats'] ) $data=$this->cat->getTree(array('title', 'open'));
	else $data=$db->fetch("SELECT id,title,open FROM ".PRE."_articles_cat ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',($res['level']-1));
			if ( $res['id']!=$_REQUEST['id'] && $res['open'] ) $catlist.='<option value="'.$res['id'].'" '.iif($_POST['moveto']==$res['id'],' selected="selected"').' style="color:green;">'.$space.replace($res['title']).'</option>';
			else $catlist.='<option value="" disabled="disabled" style="color:grey;">'.$space.replace($res['title']).'</option>';
		}
	}
	
	list($title,$children)=$db->first("SELECT title,children FROM ".PRE."_articles_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
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
		header('Location: '.get_index('articles.catshow'));
	}
}*/


} //END CLASS

?>