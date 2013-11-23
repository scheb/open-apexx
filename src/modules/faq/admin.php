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


# FAQ 
# ===

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class action {

var $cat;

//Startup
function action() {
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$this->cat=new RecursiveTree(PRE.'_faq', 'id');
}


//Sind die Eltern-Knoten alle aktiviert?
function areParentsEnabled($id) {
	$path = $this->cat->getPathTo(array('starttime'), $id);
	foreach ( $path AS $res ) {
		if ( $res['id']==$id ) break;
		if ( !$res['starttime'] ) {
			return false;
		}
	}
	return true;
}


////////////////////////////////////////////////////////////////////////////////////////// FAQ

//***************************** Fragen zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	//Struktur reparieren
	if ( $_REQUEST['repair'] ) {
		$this->cat->repair();
		echo 'Repair done!';
		return;
	}
	
	quicklink('faq.add');
	
	//DnD-Hinweis
	if ( $apx->user->has_right('faq.edit') ) {
		echo '<p class="hint">'.$apx->lang->get('USEDND').'</p>';
	}
	
	$col[]=array('',1,'');
	$col[]=array('COL_QUESTION',80,'class="title"');
	$col[]=array('COL_HITS',20,'align="center"');
	
	$prefix=array();
	$data = $this->cat->getTree(array('*'));
	if ( count($data) ) {
		
		//Ausgabe erfolgt
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['starttime'] ) $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			
			$link = mklink(
				'faq.php?id='.$res['id'],
				'faq,'.$res['id'].urlformat($res['question']).'.html'
			);
			
			$tabledata[$i]['COL2']='<a href="'.$link.'" target="_blank">'.replace($res['question']).'</a>';
			$tabledata[$i]['COL3']=number_format($res['hits'],0,'','.');
			$tabledata[$i]['CLASS'] = 'l'.($res['level']-1).($res['children'] ? ' haschildren' : '').($res['level']>1 ? ' hidden' : '');
			$tabledata[$i]['ID'] = 'node:'.$res['id'];
			
			//Optionen
			if ( $apx->user->has_right('faq.edit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'faq.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('faq.del') ) $tabledata[$i]['OPTIONS'].='<span class="ifhasnochildren">'.optionHTMLOverlay('del.gif', 'faq.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL')).'</span><span class="ifhaschildren"><img alt="" src="design/ispace.gif"/></span>';
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('faq.enable') && !$res['starttime'] ) $tabledata[$i]['OPTIONS'].=optionHTML('enable.gif', 'faq.enable', 'id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_ENABLE'));
			elseif ( $apx->user->has_right('faq.disable') && $res['starttime'] ) $tabledata[$i]['OPTIONS'].=optionHTML('disable.gif', 'faq.disable', 'id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_DISABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			/*$tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->user->has_right('faq.move') && $follow[$res['id']]['prev'] ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'faq.move', 'direction=up&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" />';
			
			if ( $apx->user->has_right('faq.move') && $follow[$res['id']]['next'] ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'faq.move', 'direction=down&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" />';*/
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	
	echo '<div class="treeview" id="tree">';
	$html->table($col);
	echo '</div>';
	
	$open = $apx->session->get('faq_open');
	$open = dash_unserialize($open);
	$opendata = array();
	foreach ( $open AS $catid ) {
		$opendata[] = array(
			'ID' => $catid
		);
	}
	$apx->tmpl->assign('OPEN', $opendata);
	$apx->tmpl->assign('EDIT_ALLOWED', $apx->user->has_Right('faq.edit'));
	$apx->tmpl->parse('show_js');
	
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Neue Frage *****************************
function add() {
	global $set,$db,$apx;
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['parent'] || !$_POST['question'] ) infoNotComplete();
		else {
			
			$insert = array(
				'question' => $_POST['question'],
				'answer' => $_POST['answer'],
				'meta_description' => $_POST['meta_description'],
				'searchable' => $_POST['searchable'],
				'addtime' => time()
			);
			
			//EINTRAG FREISCHALTEN
			if ( $apx->user->has_right('faq.enable') && $_POST['pubnow'] ) {
				
				//Prüfen, ob der Elternknoten deaktiviert ist => falls ja den Knoten deaktivieren
				if ( $_POST['parent']=='root' ) {
					$insert['starttime'] = time();
				}
				else {
					list($parentEnabled) = $db->first("SELECT starttime FROM ".PRE."_faq WHERE id='".intval($_POST['parent'])."' LIMIT 1");
					if ( $parentEnabled ) {
						$insert['starttime'] = time();
					}
				}
			}
			
			//WENN ROOT
			if ( $_POST['parent']=='root' ) {
				$nid = $this->cat->createNode(0, $insert);
				logit('FAQ_ADD','ID #'.$nid);
			}
			
			//WENN NODE
			else {
				$nid = $this->cat->createNode(intval($_POST['parent']), $insert);
				logit('FAQ_ADD',"ID #".$nid);
			}
			
			//Inlinescreens
			mediamanager_setinline($this->cat->lastid);
			
			//Message ausgeben oder neuer Eintrag
			if ( $_POST['submit_next'] ) {
				printJSRedirect('action.php?action=faq.add&parent='.$_REQUEST['parent']);
			}
			else {
				printJSRedirect('action.php?action=faq.show');
			}
		}
	}
	else {
		$_POST['searchable']=1;
		$_POST['parent'] = $_GET['parent'];
		
		//Baum
		$catlist='<option value="root" style="font-weight:bold;"'.iif($_POST['parent']=='root',' selected="selected"').'>'.$apx->lang->get('ROOT').'</option><option value=""></option>';
		$data = $this->cat->getTree(array('question'));
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$catlist.='<option value="'.$res['id'].'"'.iif($_POST['parent']==$res['id'],' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;',($res['level']-1)).replace(shorttext($res['question'],80)).'</option>';
			}
		}
		
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('QUESTION',compatible_hsc($_POST['question']));
		$apx->tmpl->assign('ANSWER',compatible_hsc($_POST['answer']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('PUBNOW',(int)$_POST['pubnow']);
		$apx->tmpl->assign('ACTION','add');
		
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Frage bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['id'] || !$_POST['parent'] || !$_POST['question'] ) infoNotComplete();
		else {
			
			$update = array(
				'question' => $_POST['question'],
				'answer' => $_POST['answer'],
				'meta_description' => $_POST['meta_description'],
				'searchable' => $_POST['searchable']
			);
			
			//Prüfen, ob der neue Elternknoten deaktiviert ist => falls ja den Knoten deaktivieren
			if ( intval($_POST['parent']) ) {
				list($parentEnabled) = $db->first("SELECT starttime FROM ".PRE."_faq WHERE id='".intval($_POST['parent'])."' LIMIT 1");
				if ( !$parentEnabled ) {
					$update['starttime'] = 0;
				}
			}
			
			$this->cat->moveNode($_REQUEST['id'],$_POST['parent'],$update);
			logit('FAQ_EDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('faq.show'));
		}
	}
	else {
		$res = $this->cat->getNode($_REQUEST['id'], array('question', 'meta_description', 'answer', 'searchable'));
		if ( !$res['parents'] ) $_POST['parent'] = 'root';
		else $_POST['parent'] = array_pop($res['parents']);
		$_POST['question'] = $res['question'];
		$_POST['answer'] = $res['answer'];
		$_POST['meta_description'] = $res['meta_description'];
		$_POST['searchable'] = $res['searchable'];
		
		//Baum
		$catlist='<option value="root" style="font-weight:bold;"'.iif($_POST['parent']=='root',' selected="selected"').'>'.$apx->lang->get('ROOT').'</option>';
		$data = $this->cat->getTree(array('question'));
		if ( count($data) ) {
			$catlist.='<option value=""></option>';
			foreach ( $data AS $res ) {
				if ( $jumplevel && $res['level']>$jumplevel ) continue;
				else $jumplevel=0;
				if ( $_REQUEST['id']==$res['id'] ) { $jumplevel=$res['level']; continue; }
				$catlist.='<option value="'.$res['id'].'"'.iif($_POST['parent']===$res['id'],' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;',($res['level']-1)).replace($res['question']).'</option>';
			}
		}
		
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('QUESTION',compatible_hsc($_POST['question']));
		$apx->tmpl->assign('ANSWER',compatible_hsc($_POST['answer']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('ACTION','edit');
		
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Frage löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$this->cat->deleteNode($_REQUEST['id']);
			logit('FAQ_DEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('faq.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT question FROM ".PRE."_faq WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
	}
}



//***************************** Frage aktivieren *****************************
function enable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		
		//Elternknoten ebenfalls aktivieren
		$path = $this->cat->getPathTo(array('starttime'), $_REQUEST['id']);
		foreach ( $path AS $res ) {
			if ( !$res['starttime'] ) {
				$db->query("UPDATE ".PRE."_faq SET starttime='".time()."' WHERE id='".$res['id']."' LIMIT 1");
				logit('FAQ_ENABLE','ID #'.$res['id']);
			}
		}
		
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.get_index('faq.show'));
	}
}



//***************************** Frage deaktivieren *****************************
function disable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		
		//Kindknoten ebenfalls deaktivieren
		$cattree = $this->cat->getChildrenIds($_REQUEST['id']);
		$cattree[] = $_REQUEST['id'];
		
		$db->query("UPDATE ".PRE."_faq SET starttime='0' WHERE id IN (".implode(', ', $cattree).")");
		foreach ( $cattree AS $catid ) {
			logit('FAQ_DISABLE','ID #'.$catid);
		}
		
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.get_index('faq.show'));
	}
}



//***************************** Frage verschieben *****************************
/*function move() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		$this->cat->move($_REQUEST['id'],$_REQUEST['direction']);
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.get_index('faq.show'));
	}
}*/

} //END CLASS


?>