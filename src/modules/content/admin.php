<?php 

# CONTENT CLASS
# =============

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class action {


//Kategorien auflisten
function get_catlist($selected=null) {
	global $db, $set, $apx;
	$catlist = '<option value=""></option>';
	$data = $set['content']['groups'];
	if ( count($data) ) {
		foreach ( $data AS $id => $title ) {
			$catlist.='<option value="'.$id.'"'.iif($selected==$id,' selected="selected"').'>'.replace($title).'</option>';
		}
	}
	return $catlist;
}



//***************************** Inhalt zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	//Suche durchführen
	if ( ( $_REQUEST['item'] && ( $_REQUEST['title'] || $_REQUEST['text'] ) ) || $_REQUEST['secid'] || $_REQUEST['catid'] || $_REQUEST['userid'] ) {
		$where = '';
		$_REQUEST['secid'] = (int)$_REQUEST['secid'];
		$_REQUEST['catid'] = (int)$_REQUEST['catid'];
		$_REQUEST['userid'] = (int)$_REQUEST['userid'];
		
		//Suche wird ausgeführt...
		if ( $_REQUEST['title'] ) $sc[]="title LIKE '%".addslashes_like($_REQUEST['item'])."%'";
		if ( $_REQUEST['text'] ) $sc[]="text LIKE '%".addslashes_like($_REQUEST['item'])."%'";
		if ( is_array($sc) ) $where.=' AND ( '.implode(' OR ',$sc).' )';
		
		//Sektion
		if ( !$apx->session->get('section') && $_REQUEST['secid'] ) {
			$where.=" AND ( secid LIKE '%|".$_REQUEST['secid']."|%' OR secid='all' ) ";
		}
		
		//Kategorie
		if ( $_REQUEST['catid'] ) {
			$where.=" AND catid='".$_REQUEST['catid']."' ";
		}
		
		//Benutzer
		if ( $_REQUEST['userid'] ) {
			$where.=" AND userid='".$_REQUEST['userid']."' ";
		}
		
		$data=$db->fetch("SELECT id FROM ".PRE."_content WHERE 1 ".$where);
		$ids = get_ids($data, 'id');
		$ids[] = -1;
		$searchid = saveSearchResult('admin_content', $ids, array(
			'title' => $_REQUEST['title'],
			'text' => $_REQUEST['text'],
			'item' => $_REQUEST['item'],
			'catid' => $_REQUEST['catid'],
			'secid' => $_REQUEST['secid'],
			'userid' => $_REQUEST['userid']
		));
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: action.php?action=content.show&searchid='.$searchid);
		return;
	}
	
	
	//Vorgaben
	$_REQUEST['title'] = 1;
	$_REQUEST['text'] = 1;
	
	quicklink('content.add');
	
	$orderdef[0]='time';
	$orderdef['title']=array('a.title','ASC','COL_TITLE');
	$orderdef['user']=array('b.username','ASC','COL_USER');
	$orderdef['time']=array('a.time','DESC','COL_ADDTIME');
	$orderdef['lastchange']=array('a.lastchange','DESC','COL_LASTCHANGE');
	$orderdef['hits']=array('a.hits','DESC','COL_HITS');
	
	$col[]=array('',1,'align="center"');
	$col[]=array('COL_TITLE',50,'class="title"');
	$col[]=array('COL_USER',20,'align="center"');
	$col[]=array('COL_LASTCHANGE',20,'align="center"');
	$col[]=array('COL_HITS',10,'align="center"');
	
	
	//Suchergebnis?
	$resultFilter = '';
	if ( $_REQUEST['searchid'] ) {
		$searchRes = getSearchResult('admin_content', $_REQUEST['searchid']);
		if ( $searchRes ) {
			list($resultIds, $resultMeta) = $searchRes;
			$_REQUEST['item'] = $resultMeta['item'];
			$_REQUEST['title'] = $resultMeta['title'];
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
	$data = $set['content']['groups'];
	if ( count($data) ) {
		foreach ( $data AS $id => $title ) {
			$catlist.='<option value="'.$id.'"'.iif($_REQUEST['catid']==$id,' selected="selected"').'>'.replace($title).'</option>';
		}
	}
	
	//Benutzer auflisten
	$userlist = '';
	$data=$db->fetch("SELECT b.userid,b.username FROM ".PRE."_content AS a LEFT JOIN ".PRE."_user AS b USING (userid) WHERE a.userid!=0 GROUP BY userid ORDER BY username ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) $userlist.='<option value="'.$res['userid'].'"'.iif($_REQUEST['userid']==$res['userid'],' selected="selected"').'>'.replace($res['username']).'</option>';
	}
	
	$apx->tmpl->assign('ITEM',compatible_hsc($_REQUEST['item']));
	$apx->tmpl->assign('STITLE',(int)$_REQUEST['title']);
	$apx->tmpl->assign('STEXT',(int)$_REQUEST['text']);
	$apx->tmpl->assign('SECLIST',$seclist);
	$apx->tmpl->assign('CATLIST',$catlist);
	$apx->tmpl->assign('USERLIST',$userlist);
	$apx->tmpl->assign('EXTENDED',$searchRes);
	$apx->tmpl->parse('search');
	

	list($count)=$db->first("SELECT count(id) FROM ".PRE."_content AS a WHERE 1 ".$resultFilter.section_filter());
	pages('action.php?action=content.show&amp;sortby='.$_REQUEST['sortby'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']),$count);
	
	$data=$db->fetch("SELECT a.id,a.secid,a.title,a.lastchange,a.allowcoms,a.allowrating,a.active,a.hits,b.userid,b.username FROM ".PRE."_content AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE 1 ".$resultFilter.section_filter(true, 'a.secid')." ".getorder($orderdef).getlimit());
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['active'] ) $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			
			$title=$res['title'];
			$title=strip_tags($title);
			//$title=str_replace('=>','»',$title);
			$title=str_replace('->','»',$title);
			$title=shorttext($title,40);
			$title=replace($title);
			
			$temp=explode('->',$res['title']);
			$tmp=unserialize_section($res['secid']);
			$link=mklink(
				'content.php?id='.$res['id'],
				'content,'.$res['id'].urlformat(array_pop($temp)).'.html',
				iif($set['main']['forcesection'],iif(unserialize_section($res['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
			);
			
			$tabledata[$i]['COL2']='<a href="'.$link.'" target="_blank">'.$title.'</a>';
			$tabledata[$i]['COL3']=replace($res['username']);
			$tabledata[$i]['COL4']=mkdate($res['lastchange'],'<br />');
			$tabledata[$i]['COL5']=$res['hits'];
			
			//Optionen
			if ( $apx->user->has_right('content.edit') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('content.edit') ) ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'content.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			if ( $apx->user->has_right('content.del') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('content.del') ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'content.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			if ( $res['active'] && $apx->user->has_right('content.disable') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('content.disable') ) ) $tabledata[$i]['OPTIONS'].=optionHTML('disable.gif', 'content.disable', 'id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_DISABLE'));
			elseif ( !$res['active'] && $apx->user->has_right('content.enable') && ( $res['userid']==$apx->user->info['userid'] || $apx->user->has_spright('content.enable') ) ) $tabledata[$i]['OPTIONS'].=optionHTML('enable.gif', 'content.enable', 'id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_ENABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Kommentare + Bewertungen
			if ( $apx->is_module('comments') || $apx->is_module('ratings') ) $tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->is_module('comments') ) {
				list($comments)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='content' AND mid='".$res['id']."' )");
				if ( $comments && ( $apx->is_module('comments') && $set['content']['coms'] ) && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=content&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			if ( $apx->is_module('ratings') ) {
				list($ratings)=$db->first("SELECT count(id) FROM ".PRE."_ratings WHERE ( module='content' AND mid='".$res['id']."' )");
				if ( $ratings && ( $apx->is_module('ratings') && $set['content']['ratings'] ) && $res['allowrating'] && $apx->user->has_right('ratings.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('ratings.gif', 'ratings.show', 'module=content&mid='.$res['id'], $apx->lang->get('RATINGS').' ('.$ratings.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	
	orderstr($orderdef,'action.php?action=content.show'.iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']));
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Inhalt erstellen *****************************
function add() {
	global $set,$db,$apx;
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['catid'] || !$_POST['title'] || !$_POST['text'] ) infoNotComplete();
		else {
			$_POST['secid']=serialize_section($_POST['secid']);
			$_POST['time']=time();
			$_POST['lastchange']=time();
			$_POST['lastchange_userid']=$apx->user->userid['userid'];
			
			//Aktivierung
			if ( $apx->user->has_right('content.enable') && $_POST['pubnow'] ) $_POST['active']=1;
			
			//Autor erzwingen, wenn keine Sonderrechte
			if ( !$apx->user->has_spright('content.edit') ) $_POST['userid']=$apx->user->info['userid'];
			
			$db->dinsert(PRE.'_content','secid,catid,title,text,meta_description,userid,time,lastchange,lastchange_userid,searchable,allowcoms,allowrating,active');
			$nid=$db->insert_id();
			logit('CONTENT_ADD','ID #'.$nid);
			
			//Inlinescreens
			mediamanager_setinline($nid);
			
			printJSRedirect('action.php?action=content.show');
		}
	}
	else {
		$_POST['userid']=$apx->user->info['userid'];
		$_POST['searchable']=$_POST['allowcoms']=$_POST['allowrating']=1;
		
		mediamanager('content');
		
		$apx->tmpl->assign('CATLIST',$this->get_catlist($_POST['catid']));
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWRATING',(int)$_POST['allowrating']);
		$apx->tmpl->assign('PUBNOW',(int)$_POST['pubnow']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('ACTION','add');
		
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Inhalt bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['catid'] || !$_POST['title'] || !$_POST['text'] ) infoNotComplete();
		else {
			$_POST['secid']=serialize_section($_POST['secid']);
			$_POST['lastchange']=time();
			$_POST['lastchange_userid']=$apx->user->info['userid'];
			
			//Autor aktualisieren
			if ( $apx->user->has_spright('content.edit') && $_POST['userid'] ) {
				if ( $_POST['userid']=='send' ) $_POST['userid']=0;
				else $_POST['userid']=$_POST['userid'];
				$addfields.=',userid';
			}
			
			$db->dupdate(PRE.'_content','secid,catid,title,text,meta_description,lastchange,lastchange_userid,allowcoms,searchable,allowrating'.$addfields,"WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('content.edit')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			logit('CONTENT_EDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('content.show'));
		}
	}
	else {
		$res=$db->first("SELECT secid,catid,userid,title,text,meta_description,searchable,allowrating,allowcoms FROM ".PRE."_content WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('content.edit')," AND userid='".$apx->user->info['userid']."'")." )");
		foreach ( $res AS $key => $val ) $_POST[$key]=$val;
		$_POST['secid']=unserialize_section($_POST['secid']);
		
		mediamanager('content');
		
		$apx->tmpl->assign('CATLIST',$this->get_catlist($_POST['catid']));
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('USERID',$_POST['userid']);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('ALLOWRATING',(int)$_POST['allowrating']);
		$apx->tmpl->assign('PUBNOW',(int)$_POST['pubnow']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('ACTION','edit');
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Inhalt löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');

	if ( $_POST['send']==1 ) {
		if ( !checkToken() )  printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_content WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('content.del')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
			
			//Kommentare + Bewertungen löschen
			if ( $db->affected_rows() ) {
				if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='content' AND mid='".$_REQUEST['id']."' )");
				if ( $apx->is_module('ratings') ) $db->query("DELETE FROM ".PRE."_ratings WHERE ( module='content' AND mid='".$_REQUEST['id']."' )");
			}
			
			logit('CONTENT_DEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('content.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_content WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
	}
}



//***************************** Inhalt aktivieren *****************************
function enable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		$db->query("UPDATE ".PRE."_content SET active='1' WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('content.enable')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
		logit('CONTENT_ENABLE','ID #'.$_REQUEST['id']);
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.get_index('content.show'));
	}
}



//***************************** Inhalt deaktivieren *****************************
function disable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		$db->query("UPDATE ".PRE."_content SET active='0' WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('content.disable')," AND userid='".$apx->user->info['userid']."'")." ) LIMIT 1");
		logit('CONTENT_DISABLE','ID #'.$_REQUEST['id']);
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.get_index('content.show'));
	}
}



//***************************** Kategorien *****************************

function group() {
	global $set,$db,$apx,$html;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	$data=$set['content']['groups'];
	
	//Kategorie löschen
	if ( $_REQUEST['do']=='del' && isset($data[$_REQUEST['id']]) ) {
		list($count)=$db->first("SELECT count(*) FROM ".PRE."_content WHERE catid='".$_REQUEST['id']."'");
		if ( !$count ) {
			if ( isset($_POST['id']) ) {
				if ( !checkToken() ) infoInvalidToken();
				else {
					unset($data[$_REQUEST['id']]);
					$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='content' AND varname='groups' LIMIT 1");
					logit('CONTENT_CATDEL',$_REQUEST['id']);
					printJSReload();
				}
			}
			else {
				$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($data[$_REQUEST['id']]))));
				tmessageOverlay('catdel',array('ID'=>$_REQUEST['id']));
			}
		}
		return;
	}
	
	//Kategorie leeren
	if ( $_REQUEST['do']=='clean' && isset($data[$_REQUEST['id']]) ) {
		if ( $_POST['id'] && $_POST['moveto'] ) {
			if ( !checkToken() ) infoInvalidToken();
			else {
				$db->query("UPDATE ".PRE."_content SET catid='".intval($_POST['moveto'])."' WHERE catid='".$_REQUEST['id']."'");
				logit('CONTENT_CATCLEAN',"ID #".$_REQUEST['id']);
				
				//Kategorie löschen
				if ( $_POST['delcat'] ) {
					unset($data[$_REQUEST['id']]);
					$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='content' AND varname='groups' LIMIT 1");
					logit('CONTENT_CATDEL',$_REQUEST['id']);
				}
				
				printJSReload();
				return;
			}
		}
		else {
			
			//Kategorien auflisten
			$catlist = '';
			$data = $set['content']['groups'];
			if ( count($data) ) {
				foreach ( $data AS $id => $title ) {
					if ( $id==$_REQUEST['id'] ) continue;
					$catlist.='<option value="'.$id.'"'.iif($_REQUEST['catid']==$id,' selected="selected"').'>'.replace($title).'</option>';
				}
			}
			
			$apx->tmpl->assign('ID',$_REQUEST['id']);
			$apx->tmpl->assign('TITLE',compatible_hsc($data[$_REQUEST['id']]));
			$apx->tmpl->assign('DELCAT',(int)$_POST['delcat']);
			$apx->tmpl->assign('CATLIST',$catlist);
			
			tmessageOverlay('catclean');
		}
		return;
	}
	
	//Kategorie bearbeiten
	elseif ( $_REQUEST['do']=='edit' && isset($data[$_REQUEST['id']]) ) {
		if ( isset($_POST['title']) ) {
			if ( !checkToken() ) infoInvalidToken();
			elseif ( !$_POST['title'] ) info('back');
			else {
				$data[$_REQUEST['id']]=$_POST['title'];
				$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='content' AND varname='groups' LIMIT 1");
				logit('CONTENT_CATEDIT',$_REQUEST['id']);
				printJSRedirect('action.php?action=content.group');
				return;
			}
		}
		else {
			$_POST['title']=$data[$_REQUEST['id']];
			$apx->tmpl->assign('TITLE',$_POST['title']);
			$apx->tmpl->assign('ACTION','edit');
			$apx->tmpl->assign('ID',$_REQUEST['id']);
			$apx->tmpl->parse('catadd_catedit');
		}
	}
	
	//Kategorie erstellen
	elseif ( $_REQUEST['do']=='add' ) {
		if ( $_POST['send'] ) {
			if ( !checkToken() ) printInvalidToken();
			elseif ( !$_POST['title'] ) info('back');
			else {
				if ( !count($data) ) $data[1]=$_POST['title'];
				else $data[]=$_POST['title'];
				$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='content' AND varname='groups' LIMIT 1");
				logit('CONTENT_CATADD',array_key_max($data));
				printJSRedirect('action.php?action=content.group');
				return;
			}
		}
	}
	
	else {
		$apx->tmpl->assign('ACTION','add');
		$apx->tmpl->parse('catadd_catedit');
	}
	
	$col[]=array('ID',1,'align="center"');
	$col[]=array('COL_TITLE',80,'class="title"');
	$col[]=array('COL_CONTENTS',20,'align="center"');
	
	
	//AUSGABE
	asort($data);
	foreach ( $data AS $id => $res ) {
		++$i;
		list($count)=$db->first("SELECT count(*) FROM ".PRE."_content WHERE catid='".$id."'");
		$tabledata[$i]['COL1']=$id;
		$tabledata[$i]['COL2']=$res;
		$tabledata[$i]['COL3']=$count;
		$tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'content.group', 'do=edit&id='.$id, $apx->lang->get('CORE_EDIT'));
		if ( !$count ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'content.group', 'do=del&id='.$id, $apx->lang->get('CORE_DEL'));
		else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
		if ( $count ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('clean.gif', 'content.group', 'do=clean&id='.$id, $apx->lang->get('CLEAN'));
		else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
	}
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
}



} //END CLASS


?>
