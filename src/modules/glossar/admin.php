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


# GLOSSAR
# =======

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class action {


//Kategorien auflisten
function get_catlist($selected=null) {
	global $db;
	$data=$db->fetch("SELECT id,title FROM ".PRE."_glossar_cat ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$catlist.='<option value="'.$res['id'].'"'.iif($res['id']==$selected,' selected="selected"').'>'.replace($res['title']).'</option>';
		}
	}
	return $catlist;
}



//***************************** Einträge zeigen *****************************
function show() {
	global $set,$db,$apx,$html;	
	
	//Suche durchführen
	if ( $_REQUEST['item'] && ( $_REQUEST['title'] || $_REQUEST['text'] ) ) {
		$where = '';
		
		//Suche wird ausgeführt...
		if ( $_REQUEST['title'] ) $sc[]="title LIKE '%".addslashes_like($_REQUEST['item'])."%'";
		if ( $_REQUEST['text'] ) $sc[]="text LIKE '%".addslashes_like($_REQUEST['item'])."%'";
		if ( is_array($sc) ) $where.=' AND ( '.implode(' OR ',$sc).' )';
		
		$data=$db->fetch("SELECT id FROM ".PRE."_glossar WHERE 1 ".$where);
		$ids = get_ids($data, 'id');
		$ids[] = -1;
		$searchid = saveSearchResult('admin_glossar', $ids, array(
			'title' => $_REQUEST['title'],
			'text' => $_REQUEST['text'],
			'item' => $_REQUEST['item']
		));
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: action.php?action=glossar.show&what='.$_REQUEST['what'].'&searchid='.$searchid);
		return;
	}
	
	
	//Vorgaben
	$_REQUEST['title'] = 1;
	$_REQUEST['text'] = 1;
	
	
	quicklink('glossar.add', 'action.php', 'catid='.$_REQUEST['what']);
	
	$orderdef[0]='creation';
	$orderdef['title']=array('a.title','ASC','COL_TITLE');
	$orderdef['category']=array('catname','ASC','COL_CATEGORY');
	$orderdef['creation']=array('a.addtime','DESC','SORT_ADDTIME');
	$orderdef['publication']=array('a.starttime','DESC','SORT_STARTTIME');
	$orderdef['hits']=array('a.hits','DESC','COL_HITS');
	
	//Layer
	$layerdef[]=array('ALL','action.php?action=glossar.show',!$_REQUEST['what']);
	$data=$db->fetch("SELECT * FROM ".PRE."_glossar_cat ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$layerdef[]=array(compatible_hsc($res['title']),'action.php?action=glossar.show&amp;what='.$res['id'],$_REQUEST['what']==$res['id']);
		}
	}
	$html->layer_header($layerdef);
	$layerFilter = '';
	if ( intval($_REQUEST['what']) ) {
		$layerFilter = " AND a.catid='".intval($_REQUEST['what'])."' ";
	}
	
	//Suchergebnis?
	$resultFilter = '';
	if ( $_REQUEST['searchid'] ) {
		$searchRes = getSearchResult('admin_glossar', $_REQUEST['searchid']);
		if ( $searchRes ) {
			list($resultIds, $resultMeta) = $searchRes;
			$_REQUEST['item'] = $resultMeta['item'];
			$_REQUEST['title'] = $resultMeta['title'];
			$_REQUEST['text'] = $resultMeta['text'];
			$resultFilter = " AND a.id IN (".implode(', ', $resultIds).")";
		}
		else {
			$_REQUEST['searchid'] = '';
		}
	}
	
	$apx->tmpl->assign('ITEM',compatible_hsc($_REQUEST['item']));
	$apx->tmpl->assign('STITLE',(int)$_REQUEST['title']);
	$apx->tmpl->assign('STEXT',(int)$_REQUEST['text']);
	$apx->tmpl->assign('WHAT',$_REQUEST['what']);
	$apx->tmpl->parse('search');
	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_glossar AS a WHERE 1 ".$layerFilter.$resultFilter);
	pages('action.php?action=glossar.show&amp;what='.$_REQUEST['what'].'&amp;sortby='.$_REQUEST['sortby'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']),$count);
	$data=$db->fetch("SELECT a.id,a.title,a.starttime,a.allowcoms,a.allowrating,a.hits,b.title AS catname FROM ".PRE."_glossar AS a LEFT JOIN ".PRE."_glossar_cat AS b ON a.catid=b.id WHERE 1 ".$layerFilter.$resultFilter." ".getorder($orderdef).getlimit());
	$this->show_print($data);
	orderstr($orderdef,'action.php?action=glossar.show&amp;what='.$_REQUEST['what'].''.iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']));
	save_index($_SERVER['REQUEST_URI']);
}



//Einträge auflisten
function show_print($data) {
	global $set,$db,$apx,$html;
	
	$col[]=array('',1,'align="center"');
	$col[]=array('COL_TITLE',50,'class="title"');
	$col[]=array('COL_CATEGORY',22,'align="center"');
	$col[]=array('COL_PUBDATE',18,'align="center"');
	$col[]=array('COL_HITS',10,'align="center"');
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['starttime'] ) $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			
			$link=mklink(
				'glossar.php?id='.$res['id'],
				'glossar,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			$tabledata[$i]['COL2']='<a href="'.$link.'" target="_blank">'.shorttext(strip_tags($res['title']),40).'</a>';
			$tabledata[$i]['COL3']=replace($res['catname']);
			$tabledata[$i]['COL5']=number_format($res['hits'],0,'','.');
			
			if ( $res['starttime'] ) $tabledata[$i]['COL4']=mkdate($res['starttime'],'<br />');
			else $tabledata[$i]['COL4']='&nbsp;';
			
			//Optionen
			if ( $apx->user->has_right('glossar.edit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'glossar.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('glossar.del') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'glossar.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( !$res['starttime'] && $apx->user->has_right('glossar.enable') ) $tabledata[$i]['OPTIONS'].=optionHTML('enable.gif', 'glossar.enable', 'id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_ENABLE'));
			elseif ( $res['starttime'] && $apx->user->has_right('glossar.disable') ) $tabledata[$i]['OPTIONS'].=optionHTML('disable.gif', 'glossar.disable', 'id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_DISABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Kommentare + Bewertungen
			if ( $apx->is_module('comments') || $apx->is_module('ratings') ) $tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->is_module('comments') ) {
				list($comments)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='glossar' AND mid='".$res['id']."' )");
				if ( $comments && ( $apx->is_module('comments') && $set['glossar']['coms'] ) && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=glossar&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			if ( $apx->is_module('ratings') ) {
				list($ratings)=$db->first("SELECT count(id) FROM ".PRE."_ratings WHERE ( module='glossar' AND mid='".$res['id']."' )");
				if ( $ratings && ( $apx->is_module('ratings') && $set['glossar']['ratings'] ) && $res['allowrating'] && $apx->user->has_right('ratings.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('ratings.gif', 'ratings.show', 'module=glossar&mid='.$res['id'], $apx->lang->get('RATINGS').' ('.$ratings.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
}



//***************************** Neuer Begriff *****************************
function add() {
	global $set,$db,$apx;
	
	//Absenden
	if ( $_POST['send']==1 ) {
		
		//Begriff bereits vorhanden?
		$duplicate = false;
		if ( $_POST['send']==1 && !$_POST['ignore'] ) {
			list($duplicate) = $db->first("SELECT id FROM ".PRE."_glossar WHERE catid='".intval($_POST['catid'])."' AND title LIKE '".addslashes($_POST['title'])."' LIMIT 1");
		}
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['catid'] || !$_POST['text'] ) infoNotComplete();
		elseif ( $duplicate ) {
			info($apx->lang->get('MSG_DUPLICATE'));
			echo '<script type="text/javascript"> parent.document.forms[\'textform\'].ignore.value = 1; </script>';
		}
		else {
			
			//Veröffentlichung: JETZT
			if ( $_POST['pubnow'] && $apx->user->has_right('glossar.enable') ) {
				$_POST['starttime']=time();
				$addfields.=',starttime';
			}
			
			$_POST['addtime']=time();
			
			$db->dinsert(PRE.'_glossar','catid,title,spelling,text,meta_description,addtime,searchable,allowcoms,allowrating'.$addfields);
			$nid=$db->insert_id();
			logit('GLOSSAR_ADD','ID #'.$nid);
			
			//Inlinescreens
			mediamanager_setinline($nid);
			
			//Tags
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_glossar_tags VALUES('".$nid."', '".$tagid."')");
			}
			
			printJSRedirect('action.php?action=glossar.show');
		}
	}
	else {
		$_POST['allowcoms']=1;
		$_POST['allowrating']=1;
		$_POST['searchable']=1;
		$_POST['pubnow']=1;
		$_POST['catid']=$_GET['catid'];
		
		mediamanager('glossar');
		
		$apx->tmpl->assign('CATLIST',$this->get_catlist($_POST['catid']));
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('SPELLING',compatible_hsc($_POST['spelling']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWRATING',(int)$_POST['allowrating']);
		$apx->tmpl->assign('PUBNOW',(int)$_POST['pubnow']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		
		$apx->tmpl->assign('ACTION','add');
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Begriff bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['catid'] || !$_POST['text'] ) infoNotComplete();
		else {
			$db->dupdate(PRE.'_glossar','catid,title,spelling,text,meta_description,searchable,allowcoms,allowrating',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$nid=$db->insert_id();
			logit('GLOSSAR_ADD','ID #'.$nid);
			
			//Inlinescreens
			mediamanager_setinline($nid);
			
			//Tags
			$db->query("DELETE FROM ".PRE."_glossar_tags WHERE id='".$_REQUEST['id']."'");
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_glossar_tags VALUES('".$_REQUEST['id']."', '".$tagid."')");
			}
			
			printJSRedirect(get_index('glossar.show'));
		}
	}
	else {
		$res=$db->first("SELECT * FROM ".PRE."_glossar WHERE id='".$_REQUEST['id']."' LIMIT 1",1);
		foreach ( $res AS $key => $value ) $_POST[$key]=$value;
		
		mediamanager('glossar');
		
		//Tags
		$tags = array();
		$tagdata = $db->fetch("
			SELECT t.tag
			FROM ".PRE."_glossar_tags AS n
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE n.id='".$_REQUEST['id']."'
			ORDER BY t.tag ASC
		");
		$tags = get_ids($tagdata, 'tag');
		$_POST['tags'] = implode(', ', $tags);
		
		$apx->tmpl->assign('CATLIST',$this->get_catlist($_POST['catid']));
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('SPELLING',compatible_hsc($_POST['spelling']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWRATING',(int)$_POST['allowrating']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('ACTION','edit');
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Begriff löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_glossar WHERE id='".$_REQUEST['id']."' LIMIT 1");
			
			//Kommentare + Bewertungen löschen (nur wenn ein Eintrag gelöscht wurde -> User hat Recht dazu!)
			if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='glossar' AND mid='".$_REQUEST['id']."' )");
			if ( $apx->is_module('ratings') ) $db->query("DELETE FROM ".PRE."_ratings WHERE ( module='glossar' AND mid='".$_REQUEST['id']."' )");
			
			//Tags löschen
			$db->query("DELETE FROM ".PRE."_glossar_tags WHERE id='".$_REQUEST['id']."'");
			
			logit('GLOSSAR_DEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('glossar.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_glossar WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
	}
}



//***************************** Begriff aktivieren *****************************
function enable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		$db->query("UPDATE ".PRE."_glossar SET starttime='".time()."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
		logit('GLOSSAR_ENABLE','ID #'.$_REQUEST['id']);
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.get_index('glossar.show'));
	}
}



//***************************** Begriff widerrufen *****************************
function disable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');

	if ( !checkToken() ) printInvalidToken();
	else {
		$db->query("UPDATE ".PRE."_glossar SET starttime='0' WHERE id='".$_REQUEST['id']."' LIMIT 1");
		logit('GLOSSAR_DISABLE','ID #'.$_REQUEST['id']);
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.get_index('glossar.show'));
	}
}



////////////////////////////////////////////////////////////////////////////////////////// KATEGORIEN

//***************************** Kategorien zeigen *****************************
function catshow() {
	global $set,$db,$apx,$html;
	
	quicklink('glossar.catadd');
	
	$col[]=array('ID',3,'align="center"');
	$col[]=array('COL_CATNAME',72,'class="title"');
	$col[]=array('COL_ENTRIES',25,'align="center"');
	
	$orderdef[0]='title';
	$orderdef['title']=array('title','ASC','COL_CATNAME');
	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_glossar_cat");
	pages('action.php?action=glossar.catshow',$count);
	$data=$db->fetch("SELECT * FROM ".PRE."_glossar_cat ".getorder($orderdef).getlimit());
	
	if ( count($data) ) {
		
		//Ausgabe erfolgt
		foreach ( $data AS $res ) {
			++$i;
			
			list($entries)=$db->first("SELECT count(id) FROM ".PRE."_glossar WHERE catid='".$res['id']."'");
			
			$tabledata[$i]['COL1']=$res['id'];
			$tabledata[$i]['COL2']=$space[$res['id']].' '.replace($res['title']);
			$tabledata[$i]['COL3']=$entries;
			
			//Optionen
			if ( $apx->user->has_right('glossar.catedit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'glossar.catedit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('glossar.catdel') && !$entries && !$res['children'] ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'glossar.catdel', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('glossar.catclean') && $entries ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('clean.gif', 'glossar.catclean', 'id='.$res['id'], $apx->lang->get('CLEAN'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			unset($entries);
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	
	orderstr($orderdef,'action.php?action=glossar.catshow');
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Neue Kategorie *****************************
function catadd() {
	global $set,$db,$apx;
	
	//ABSENDEN
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] ) infoNotComplete();
		else {
			$db->dinsert(PRE.'_glossar_cat','title,icon,text');
			$nid = $db->insert_id();
			logit('GLOSSAR_CATADD','ID #'.$nid);
			
			//Beitrag der Kategorie hinzufügen
			if ( $_REQUEST['updateparent'] ) {
				printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
			}
			else {
				printJSRedirect('action.php?action=glossar.catshow');
			}
		}
	}
	else {
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('ICON',compatible_hsc($_POST['icon']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('ACTION','add');
		$apx->tmpl->assign('UPDATEPARENT',(int)$_REQUEST['updateparent']);
		
		$apx->tmpl->parse('catadd_catedit');
	}
}



//***************************** Kategorie bearbeiten *****************************
function catedit() {
	global $set,$apx,$db;
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//ABSENDEN
	if ( $_POST['send']==1 ) {		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['id'] || !$_POST['title'] ) infoNotComplete();
		else {
			$db->dupdate(PRE.'_glossar_cat','title,icon,text',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('GLOSSAR_CATEDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('glossar.catshow'));
		}
	}
	else {
		list(
			$_POST['title'],
			$_POST['icon'],
			$_POST['text']
		)=$db->first("SELECT title,icon,text FROM ".PRE."_glossar_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
		
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('ICON',compatible_hsc($_POST['icon']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
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
	
	list($entries)=$db->first("SELECT count(id) FROM ".PRE."_glossar WHERE catid='".$_REQUEST['id']."'");
	if ( $entries ) die('category still contains entries!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_glossar_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('GLOSSAR_CATDEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('glossar.catshow'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_glossar_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
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
		if ( !checkToken() ) printInvalidToken();
		elseif ( $_POST['id'] && $_POST['moveto'] ) {
			$db->query("UPDATE ".PRE."_glossar SET catid='".intval($_POST['moveto'])."' WHERE catid='".$_REQUEST['id']."'");
			logit('GLOSSAR_CATCLEAN',"ID #".$_REQUEST['id']);
			
			//Kategorie löschen
			if ( $_POST['delcat'] ) {
				$db->query("DELETE FROM ".PRE."_glossar_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
				logit('GLOSSAR_CATDEL',"ID #".$_REQUEST['id']);
			}
			
			printJSRedirect(get_index('glossar.catshow'));
			return;
		}
	}
	
	//Andere Kategorien auflisten
	$data=$db->fetch("SELECT id,title FROM ".PRE."_glossar_cat WHERE id!='".$_REQUEST['id']."' ORDER BY title ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$catlist.='<option value="'.$res['id'].'" '.iif($_POST['moveto']==$res['id'],' selected="selected"').'>'.replace($res['title']).'</option>';
		}
	}
	
	list($title) = $db->first("SELECT title FROM ".PRE."_glossar_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
	$apx->tmpl->assign('TITLE', compatible_hsc($title));
	$apx->tmpl->assign('ID',$_REQUEST['id']);
	$apx->tmpl->assign('DELCAT',(int)$_POST['delcat']);
	$apx->tmpl->assign('CATLIST',$catlist);
	tmessageOverlay('catclean');
}


} //END CLASS


?>