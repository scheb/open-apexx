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


# FORUM
# =====

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Funktionen laden
include(BASEDIR.getmodulepath('forum').'admin_extend.php');


class action extends forum_functions {

var $cat;

//Startup
function __construct() {
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$this->cat=new RecursiveTree(PRE.'_forums', 'forumid');
	
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$this->mm=new mediamanager;
}


//Definiert die Rechte-Felder und deren Standardeinstellung
//Welche Benutzergruppen bekommen standardmäßig welche Rechte?
var $rightfields=array(
	'right_visible' => array('all'),
	'right_read' => array('all'),
	'right_open' => array('public','indiv'),
	'right_announce' => array('none'),
	'right_post' => array('public','indiv'),
	'right_editpost' => array('public','indiv'),
	'right_delpost' => array('public','indiv'),
	'right_delthread' => array('none'),
	'right_addattachment' => array('public','indiv'),
	'right_readattachment' => array('all')
);



///////////////////////////////////////////////////////////////////////////////////// BENUTZER SUCHEN

function searchuser() {
	global $set,$db,$apx;
	$apx->tmpl->loaddesign('blank');
	
	if ( $_POST['send'] ) {
		$data=$db->fetch("SELECT userid,username_login FROM ".PRE."_user WHERE username_login LIKE '%".addslashes_like($_POST['item'])."%' OR username LIKE '%".addslashes_like($_POST['item'])."%' ORDER BY username_login ASC");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				++$i;
				$tabledata[$i]['ID']=$res['userid'];
				$tabledata[$i]['NAME']=$res['username_login'];
				$tabledata[$i]['INSERT']=addslashes($res['username_login']);
			}
		}
	}
	
	$apx->tmpl->assign('RESULT',$tabledata);
	$apx->tmpl->assign('ITEM',compatible_hsc($_POST['item']));
	$apx->tmpl->assign('INSERTFUNC',$_REQUEST['insertfunc']);
	
	$apx->tmpl->parse('searchuser');
}



///////////////////////////////////////////////////////////////////////////////////// FOREN

//***************************** Foren zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	//Struktur reparieren
	if ( $_REQUEST['repair'] ) {
		$this->cat->repair();
		echo 'Repair done!';
		return;
	}
	
	$col[]=array('ID',0,'align="center"');
	$col[]=array('COL_TITLE',70,'class="title"');
	$col[]=array('COL_THREADS',15,'align="center"');
	$col[]=array('COL_POSTS',15,'align="center"');
	
	//Foren auslesen
	$data=$this->cat->getTree(array('iscat', 'title', 'posts', 'threads'));
	
	//Forum erstellen
	if ( $apx->user->has_right('forum.add') ) {
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$space = str_repeat('&nbsp;&nbsp;', $res['level']-1);
				if ( $res['iscat'] ) $style=' style="background:#EAEAEA"';
				else $style='';
				$forumlist.='<option value="'.$res['forumid'].'"'.$style.'>'.$space.replace($res['title']).'</option>';
			}
		}
		$apx->tmpl->assign('FORUMLIST',$forumlist);
	}
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$res['children'] = dash_unserialize($res['children']);
			
			$link = mklink(
				$set['forum']['directory'].'/forum.php?id='.$res['forumid'],
				$set['forum']['directory'].'/forum,'.$res['forumid'].',1'.urlformat($res['title']).'.html'
			);
			
			$tabledata[$i]['COL1']=$res['forumid'];
			$tabledata[$i]['COL2']='<a href="'.$link.'" target="_blank">'.replace($res['title']).'</a>';
			$tabledata[$i]['COL3']=number_format($res['threads'],0,'','.');
			$tabledata[$i]['COL4']=number_format($res['posts'],0,'','.');
			$tabledata[$i]['CLASS'] = 'l'.($res['level']-1).($res['children'] ? ' haschildren' : '').($res['level']>1 ? ' hidden' : '').($res['iscat'] ? ' dark' : '');
			$tabledata[$i]['ID'] = 'node:'.$res['forumid'];
			
			//Optionen
			if ( $apx->user->has_right('forum.edit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'forum.edit', 'id='.$res['forumid'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			if ( $apx->user->has_right('forum.del') && !$res['posts'] ) $tabledata[$i]['OPTIONS'].='<span class="ifhasnochildren">'.optionHTMLOverlay('del.gif', 'forum.del', 'id='.$res['forumid'], $apx->lang->get('CORE_DEL')).'</span><span class="ifhaschildren"><img alt="" src="design/ispace.gif"/></span>';
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			if ( $apx->user->has_right('forum.clean') && $res['posts'] ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('clean.gif', 'forum.clean', 'id='.$res['forumid'], $apx->lang->get('CLEAN'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Anordnen nur bei Unterkategorien
			/*
			$tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->user->has_right('forum.move') && $follow[$res['forumid']]['prev'] ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'forum.move', 'direction=up&id='.$res['forumid'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" />';
			if ( $apx->user->has_right('forum.move') && $follow[$res['forumid']]['next'] ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'forum.move', 'direction=down&id='.$res['forumid'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" />';
			*/
		}
	}
	
	$apx->tmpl->parse('show_add');
	$apx->tmpl->assign('TABLE',$tabledata);
	echo '<div class="treeview" id="tree">';
	$html->table($col);
	echo '</div>';
	
	$open = $apx->session->get('forum_open');
	$open = dash_unserialize($open);
	$opendata = array();
	foreach ( $open AS $catid ) {
		$opendata[] = array(
			'ID' => $catid
		);
	}
	$apx->tmpl->assign('OPEN', $opendata);
	$apx->tmpl->assign('EDIT_ALLOWED', $apx->user->has_Right('forum.edit'));
	$apx->tmpl->parse('show_js');
}



//***************************** Forum hinzufügen *****************************
function add() {
	global $set,$db,$apx;
	$_REQUEST['parentid']=(int)$_REQUEST['parentid'];
	
	if ( isset($_REQUEST['create']['cat']) ) {
		$this->add_forum('cat');
	}
	else {
		$this->add_forum('forum');
	}
}


//Forum erstellen
function add_forum($ftype,$parentinfo=array()) {
	global $set,$db,$apx;
	
	//Rechte-Variablen säubern
	foreach ( $this->rightfields AS $index => $info ) {
		if ( !is_array($_POST[$index]) ) $_POST[$index]=array();
		elseif ( $_POST[$index][0]=='all' ) $_POST[$index]=array('all');
		elseif ( $_POST[$index][0]=='none' ) $_POST[$index]=array('none');
	}
	
	//Moderator-Variable zu Array
	if ( !is_array($_POST['moderator']) ) $_POST['moderator']=array();
	
	//Erstellen
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] ) infoNotComplete();
		else {
			
			//Rechte abspeichern wenn das Forum nicht erbt
			if ( !$_POST['inherit'] ) {
				foreach ( $this->rightfields AS $index => $trash ) {
					$rightfields.=','.$index;
					if ( $_POST[$index][0]=='all' ) $_POST[$index]='all';
					elseif ( $_POST[$index][0]=='none' ) $_POST[$index]='';
					else $_POST[$index]=forum_serialize($_POST[$index]);
				}
			}
			
			//Echtes Forum erstellen
			if ( $ftype=='forum' ) {
				
				//Moderator hinzufügen
				if ( $_POST['moderator_add'] ) {
					list($userid)=$db->first("SELECT userid FROM ".PRE."_user WHERE LOWER(username_login)='".addslashes(strtolower($_POST['moderator_add']))."' LIMIT 1");
					if ( $userid ) $_POST['moderator'][]=$userid;
				}
				
				if ( !$_POST['link'] ) {
					$_POST['moderator']=forum_serialize($_POST['moderator']);
				}
				else {
					unset($_POST['stylesheet'],$_POST['open'],$_POST['password'],$_POST['password']);
				}
				
				$sqlFields = explode(',', 'title,description,meta_description,stylesheet,link,moderator,open,searchable,countposts,inherit,password'.$rightfields);
				$sqlData = array();
				foreach ( $sqlFields AS $field ) {
					$sqlData[$field] = $_POST[$field];
				}
				
				$nid=$this->cat->createNode(intval($_POST['parentid']), $sqlData);
				
				//Prefixes speichern
				if ( !$_POST['link'] ) {
					foreach ( $_POST['prefix'] AS $prefix ) {
						if ( $prefix['title'] && $prefix['code'] ) {
							$db->query("
								INSERT INTO ".PRE."_forum_prefixes
								(forumid,title,code) VALUES
								('".$nid."', '".addslashes($prefix['title'])."', '".addslashes($prefix['code'])."')
							");
						}
					}
				}
			}
			
			//Kategorie erstellen
			else {
				$_POST['open']=1;
				$_POST['iscat']=1;
				
				$sqlFields = explode(',', 'title,description,meta_description,stylesheet,inherit,password,iscat,open'.$rightfields);
				$sqlData = array();
				foreach ( $sqlFields AS $field ) {
					$sqlData[$field] = $_POST[$field];
				}
				
				$nid=$this->cat->createNode(intval($_POST['parentid']), $sqlData);
			}
			
			logit('FORUM_ADD','ID #'.$nid);
			printJSRedirect('action.php?action=forum.show');
		}
	}
	else {
		if ( $_REQUEST['parentid'] ) $_POST['inherit']=1;
		
		//Standardrechte setzen
		$data=$db->fetch("SELECT groupid,gtype FROM ".PRE."_user_groups WHERE groupid!=1 ORDER BY name ASC");
		if ( count($data) ) {
			foreach ( $this->rightfields AS $index => $info ) {
				foreach ( $data AS $res ) {
					if ( in_array('all',$info) ) $_POST[$index]=array('all');
					elseif ( in_array('none',$info) ) $_POST[$index]=array('none');
					elseif ( in_array($res['gtype'],$info) ) $_POST[$index][]=$res['groupid'];
				}
			}
		}
		
		//Zusätzliche Felder bei echten Foren
		if ( $ftype=='forum' ) {
			$_POST['open']=1;
			$_POST['searchable']=1;
			$_POST['countposts']=1;
		}
		
		
		//Keine + Alle
		foreach ( $this->rightfields AS $index => $info ) {
			${$index}='<option value="all"'.iif(in_array('all',$_POST[$index]),' selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('ALL').'</option>';
			${$index}.='<option value="none"'.iif(in_array('none',$_POST[$index]),' selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('NOBODY').'</option>';
		}
		
		//Benutzergruppen ohne Admin
		$data=$db->fetch("SELECT groupid,name FROM ".PRE."_user_groups WHERE groupid!=1 ORDER BY name ASC");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				foreach ( $this->rightfields AS $index => $info ) {
					${$index}.='<option value="'.$res['groupid'].'"'.iif(in_array($res['groupid'],$_POST[$index]),' selected="selected"').'">'.replace($res['name']).'</option>';
				}
			}
		}
		
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('DESCRIPTION',compatible_hsc($_POST['description']));
		$apx->tmpl->assign('STYLESHEET','');
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('INHERIT',(int)$_POST['inherit']);
		$apx->tmpl->assign('PASSWORD',compatible_hsc($_POST['password']));
		
		//Rechtefelder
		foreach ( $this->rightfields AS $index => $info ) {
			$apx->tmpl->assign(strtoupper($index),${$index});
		}
		
		//Zusätzliche Felder bei echten Foren
		if ( $ftype=='forum' ) {
			
			//Moderatoren
			$modids=$modlist=array();
			if ( count($_POST['moderator']) ) {
				foreach ( $_POST['moderator'] AS $id ) {
					$id=(int)$id;
					if ( !$id ) continue;
					$modids[]=$id;
				}
				
				if ( count($modids) ) {
					$moddata=$db->fetch("SELECT userid,username_login FROM ".PRE."_user WHERE userid IN (".implode(',',$modids).") ORDER BY username_login ASC");
					if ( count($moddata) ) {
						foreach ( $moddata AS $res ) {
							++$i;
							$modlist[$i]['USERID']=$res['userid'];
							$modlist[$i]['USERNAME']=replace($res['username_login']);
						}
					}
				}
			}
			
			$apx->tmpl->assign('MODLIST',$modlist);
			$apx->tmpl->assign('MODERATOR_ADD',compatible_hsc($_POST['moderator_add']));
			$apx->tmpl->assign('LINK',compatible_hsc($_POST['link']));
			$apx->tmpl->assign('OPEN',intval($_POST['open']));
			$apx->tmpl->assign('SEARCHABLE',intval($_POST['searchable']));
			$apx->tmpl->assign('COUNTPOSTS',intval($_POST['countposts']));
		}
		
		//Template ausgeben
		$apx->tmpl->assign('FTYPE',$ftype);
		$apx->tmpl->assign('PARENTID',$_REQUEST['parentid']);
		$apx->tmpl->assign('ACTION','add');
		if ( $ftype=='forum' ) $apx->tmpl->parse('add_edit_forum');
		else $apx->tmpl->parse('add_edit_category');
	}
}



//***************************** Forum bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//Alle Felder nur beim ersten Mal auslesen
	foreach ( $this->rightfields AS $index => $trash ) $rightfields.=','.$index;
	if ( $_POST['id'] ) $fields='iscat';
	else $fields='iscat,title,description,meta_description,stylesheet,link,moderator,password,open,searchable,countposts,inherit'.$rightfields;
	
	$info = $this->cat->getNode($_REQUEST['id'], explode(',', $fields));
	if ( $info['iscat'] ) $this->edit_forum('cat',$info);
	else $this->edit_forum('forum',$info);
}


//Forum bearbeiten
function edit_forum($ftype,$foruminfo=array()) {
	global $set,$db,$apx;
	
	//Rechte-Variablen säubern
	foreach ( $this->rightfields AS $index => $info ) {
		if ( !is_array($_POST[$index]) ) $_POST[$index]=array();
		elseif ( $_POST[$index][0]=='all' ) $_POST[$index]=array('all');
		elseif ( $_POST[$index][0]=='none' ) $_POST[$index]=array('none');
	}
	
	//Moderator-Variable zu Array
	if ( !is_array($_POST['moderator']) ) $_POST['moderator']=array();
	
	//Erstellen
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] ) infoNotComplete(); 
		else {
			
			//Rechte abspeichern wenn das Forum nicht erbt
			if ( !$_POST['inherit'] ) {
				foreach ( $this->rightfields AS $index => $trash ) {
					$rightfields.=','.$index;
					if ( $_POST[$index][0]=='all' ) $_POST[$index]='all';
					elseif ( $_POST[$index][0]=='none' ) $_POST[$index]='';
					else $_POST[$index]=forum_serialize($_POST[$index]);
				}
			}
			
			//Rechte erben
			else {
				foreach ( $this->rightfields AS $index => $trash ) {
					$rightfields.=','.$index;
					unset($_POST[$index]);
				}
			}
			
			//Echtes Forum aktualisieren
			if ( $ftype=='forum' ) {
				
				//Moderator hinzufügen
				if ( $_POST['moderator_add'] ) {
					list($userid)=$db->first("SELECT userid FROM ".PRE."_user WHERE LOWER(username_login)='".addslashes(strtolower($_POST['moderator_add']))."' LIMIT 1");
					if ( $userid ) $_POST['moderator'][]=$userid;
				}
				
				if ( !$_POST['link'] ) {
					$_POST['moderator']=forum_serialize($_POST['moderator']);
				}
				else {
					unset($_POST['stylesheet'],$_POST['open'],$_POST['password'],$_POST['password']);
				}
				
				$db->dupdate(PRE.'_forums','title,description,meta_description,stylesheet,link,moderator,open,searchable,countposts,inherit,password'.$rightfields,"WHERE forumid='".$_REQUEST['id']."' LIMIT 1");
				
				//Prefixes speichern
				$prefixids = array();
				if ( !$_POST['link'] ) {
					foreach ( $_POST['prefix'] AS $prefix ) {
						if ( $prefix['title'] && $prefix['code'] ) {
							
							//Aktualisieren
							if ( intval($prefix['id']) ) {
								$db->query("
									UPDATE ".PRE."_forum_prefixes
									SET title='".addslashes($prefix['title'])."', code='".addslashes($prefix['code'])."'
									WHERE prefixid='".intval($prefix['id'])."' AND forumid='".$_REQUEST['id']."'
									LIMIT 1
								");
								$prefixids[] = intval($prefix['id']);
							}
							
							//Erstellen
							else {
								$db->query("
									INSERT INTO ".PRE."_forum_prefixes
									(forumid,title,code) VALUES
									('".$_REQUEST['id']."', '".addslashes($prefix['title'])."', '".addslashes($prefix['code'])."')
								");
								$prefixids[] = $db->insert_id();
							}
						}
					}
					
					//Restliche Prefixes löschen
					$db->query("
						DELETE FROM ".PRE."_forum_prefixes
						WHERE ".($prefixids ? "prefixid NOT IN (".implode(',',$prefixids).") AND" : '')." forumid='".$_REQUEST['id']."'
					");
				}
			}
			
			//Kategorie aktualisieren
			else {
				$db->dupdate(PRE.'_forums','title,description,meta_description,stylesheet,inherit,password,open'.$rightfields,"WHERE forumid='".$_REQUEST['id']."' LIMIT 1");
			}
			
			logit('FORUM_EDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('forum.show'));
		}
	}
	else {
		foreach ( $foruminfo AS $key => $value ) $_POST[$key]=$value; 
		$_POST['moderator']=forum_unserialize($foruminfo['moderator']);
		
		//Rechte
		foreach ( $this->rightfields AS $index => $info ) {
			if ( $foruminfo[$index]=='all' ) $_POST[$index]=array('all');
			elseif ( !$foruminfo[$index] ) $_POST[$index]=array('none');
			else $_POST[$index]=forum_unserialize($foruminfo[$index]);
		}
		
		
		//Keine + Alle
		foreach ( $this->rightfields AS $index => $info ) {
			${$index}='<option value="all"'.iif(in_array('all',$_POST[$index]),' selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('ALL').'</option>';
			${$index}.='<option value="none"'.iif(in_array('none',$_POST[$index]),' selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('NOBODY').'</option>';
		}
		
		//Benutzergruppen ohne Admin
		$data=$db->fetch("SELECT groupid,name FROM ".PRE."_user_groups WHERE groupid!=1 ORDER BY name ASC");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				foreach ( $this->rightfields AS $index => $info ) {
					${$index}.='<option value="'.$res['groupid'].'"'.iif(in_array($res['groupid'],$_POST[$index]),' selected="selected"').'">'.replace($res['name']).'</option>';
				}
			}
		}
		
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('DESCRIPTION',compatible_hsc($_POST['description']));
		$apx->tmpl->assign('STYLESHEET',compatible_hsc($_POST['stylesheet']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('INHERIT',(int)$_POST['inherit']);
		$apx->tmpl->assign('PASSWORD',compatible_hsc($_POST['password']));
		
		//Rechtefelder
		foreach ( $this->rightfields AS $index => $info ) {
			$apx->tmpl->assign(strtoupper($index),${$index});
		}
		
		//Zusätzliche Felder bei echten Foren
		if ( $ftype=='forum' ) {
			
			//Moderatoren
			$modids=$modlist=array();
			if ( count($_POST['moderator']) ) {
				foreach ( $_POST['moderator'] AS $id ) {
					$id=(int)$id;
					if ( !$id ) continue;
					$modids[]=$id;
				}
				
				if ( count($modids) ) {
					$moddata=$db->fetch("SELECT userid,username_login FROM ".PRE."_user WHERE userid IN (".implode(',',$modids).") ORDER BY username_login ASC");
					if ( count($moddata) ) {
						foreach ( $moddata AS $res ) {
							++$i;
							$modlist[$i]['USERID']=$res['userid'];
							$modlist[$i]['USERNAME']=replace($res['username_login']);
						}
					}
				}
			}
			
			//Präfixe
			$data = $db->fetch("
				SELECT prefixid, title, code
				FROM ".PRE."_forum_prefixes
				WHERE forumid='".$_REQUEST['id']."'
			");
			$prefixdata = array();
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					$prefixdata[] = array(
						'ID' => $res['prefixid'],
						'TITLE' => compatible_hsc($res['title']),
						'CODE' => compatible_hsc($res['code'])
					);
				}
			}
			
			$apx->tmpl->assign('PREFIX',$prefixdata);
			$apx->tmpl->assign('MODLIST',$modlist);
			$apx->tmpl->assign('MODERATOR_ADD',compatible_hsc($_POST['moderator_add']));
			$apx->tmpl->assign('LINK',compatible_hsc($_POST['link']));
			$apx->tmpl->assign('OPEN',intval($_POST['open']));
			$apx->tmpl->assign('SEARCHABLE',intval($_POST['searchable']));
			$apx->tmpl->assign('COUNTPOSTS',intval($_POST['countposts']));
		}
		
		//Template ausgeben
		$apx->tmpl->assign('ACTION','edit');
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		
		if ( $ftype=='forum' ) $apx->tmpl->parse('add_edit_forum');
		else $apx->tmpl->parse('add_edit_category');
	}
}



//***************************** Forum leeren *****************************
function clean() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$_POST['moveto']=(int)$_POST['moveto'];
	
	$finfo=$this->cat->getNode($_REQUEST['id'], explode(',', 'threads,posts,lastposter,lastposter_userid,lastposttime'));
	
	if ( $_POST['send']==1 && $_POST['moveto']>=0 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			
			//Nur was machen, wenn das Zielforum ein anderes ist
			if ( intval($_POST['moveto']) && $_POST['moveto']!=$_REQUEST['id'] ) {
				
				//Lastpost überschreiben oder nicht?
				$setvalues="threads=threads+".$finfo['threads'].",posts=posts+".$finfo['posts'].",";
				$setvalues.="lastposter=IF(lastposttime<'".$finfo['lastposttime']."','".$finfo['lastposter']."',lastposter),lastposter_userid=IF(lastposttime<'".$finfo['lastposttime']."','".$finfo['lastposter_userid']."',lastposter_userid),lastposttime=IF(lastposttime<'".$finfo['lastposttime']."','".$finfo['lastposttime']."',lastposttime)";
				
				//Postings und Threads aktualisieren
				$db->query("UPDATE ".PRE."_forums SET ".$setvalues." WHERE forumid='".$_POST['moveto']."' LIMIT 1");
				$db->query("UPDATE ".PRE."_forums SET threads=0,posts=0,lastposter='',lastposter_userid='',lastposttime=0 WHERE forumid='".$_REQUEST['id']."' LIMIT 1");
				$db->query("UPDATE ".PRE."_forum_threads SET forumid='".$_POST['moveto']."' WHERE forumid='".$_REQUEST['id']."'");
				logit('FORUM_CLEAN','ID #'.$_REQUEST['id']);
				
				//Forum löschen
				if ( $_POST['delforum'] && !$finfo['children'] ) {
					$this->cat->deleteNode($_REQUEST['id']);
					logit('FORUM_DEL',"ID #".$_REQUEST['id']);
				}
				
			}
			
			//Alle Beiträge löschen
			elseif ( !intval($_POST['moveto']) ) {
				
				//Themen und unwiderruflich Beiträge löschen
				$threaddata = $db->fetch("SELECT threadid FROM ".PRE."_forum_threads WHERE forumid='".$_REQUEST['id']."'");
				$threadIds = get_ids($threaddata, 'threadid');
				if ( $threadIds ) {
					
					//Anhänge löschen
					$data = $db->fetch("
						SELECT a.id, a.file
						FROM ".PRE."_forum_attachments AS a
						LEFT JOIN ".PRE."_forum_posts AS p USING(postid)
						WHERE p.threadid IN (".implode(',', $threadIds).")
					");
					$attIds = get_ids($data, 'id');
					if ( count($data) ) {
						foreach ( $data AS $res ) {
							if ( file_exists(BASEDIR.getpath('uploads').$res['file']) ) {
								@unlink(BASEDIR.getpath('uploads').$res['file']);
							}
						}
						$db->query("DELETE FROM ".PRE."_forum_attachments WHERE id IN (".implode(',', $attIds).")");
					}
					
					$db->query("DELETE FROM ".PRE."_forum_threads WHERE threadid IN (".implode(',', $threadIds).")");
					$db->query("DELETE FROM ".PRE."_forum_posts WHERE threadid IN (".implode(',', $threadIds).")");
					$db->query("DELETE FROM ".PRE."_forum_index WHERE threadid IN (".implode(',', $threadIds).")");
				}
				
				
				//Forum aktualisieren
				$db->query("UPDATE ".PRE."_forums SET threads=0,posts=0,lastposter='',lastposter_userid='',lastposttime=0 WHERE forumid='".$_REQUEST['id']."' LIMIT 1");
				
				//Forum löschen
				if ( $_POST['delforum'] ) {
					$this->cat->deleteNode($_REQUEST['id']);
					logit('FORUM_DEL',"ID #".$_REQUEST['id']);
				}
			}
			
			logit('FORUM_CLEAN',"ID #".$_REQUEST['id']);
			printJSRedirect(get_index('forum.show'));
		}
	}
	else {
		
		//Foren auflisten
		$data=$this->cat->getTree(array('title','iscat','link'));
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				if ( $res['level']>1 ) $space=str_repeat('&nbsp;&nbsp;',$res['level']-1);
				else $space='';
				if ( $res['iscat'] ) $style=' style="background:#EAEAEA;color:#2B2B2B;" disabled="disabled"';
				else $style='';
				$forumlist.='<option value="'.iif(!$res['iscat'] && !$res['link'],$res['forumid'], '-1').'"'.$style.''.iif($_POST['moveto']==$res['forumid'],' selected="selected"').'>'.$space.replace($res['title']).'</option>';
			}
		}
		
		list($title) = $db->first("SELECT title FROM ".PRE."_forums WHERE forumid='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('TITLE', compatible_hsc($title));
		$apx->tmpl->assign('DELFORUM',(int)$_POST['delforum']);
		$apx->tmpl->assign('DELABLE',$finfo['children']=='|');
		$apx->tmpl->assign('FORUMLIST',$forumlist);
		tmessageOverlay('clean');
	}
}



//***************************** Forum löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	$finfo=$this->cat->getNode($_REQUEST['id'], array('posts', 'parents', 'children'));
	if ( $finfo['posts'] ) die('forum still contains postings!');
	if ( $finfo['children'] ) die('forum still has children!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken ) printInvalidToken();
		else {
			
			//Abonnements löschen
			$db->query("DELETE FROM ".PRE."_forum_subscriptions WHERE type='forum' AND source='".$_REQUEST['id']."'");
			
			//Präfixe löschen
			$db->query("DELETE FROM ".PRE."_forum_prefixes WHERE forumid='".$_REQUEST['id']."'");
			
			$this->cat->deleteNode($_REQUEST['id']);
			logit('FORUM_DEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('forum.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_forums WHERE forumid='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
	}
}



//***************************** Foren anordnen *****************************
/*function move() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken ) printInvalidToken();
	else {
		list($ord1,$parents)=$db->first("SELECT ord,parents FROM ".PRE."_forums WHERE forumid='".$_REQUEST['id']."' LIMIT 1");
		
		//Nach unten
		if ( $_REQUEST['direction']=='down' ) {
			list($brother,$ord2)=$db->first("SELECT forumid,ord FROM ".PRE."_forums WHERE parents='".addslashes($parents)."' AND ord>'".$ord1."' ORDER BY ord ASC LIMIT 1");
			if ( !$brother ) die('no lower brother found!');
		}
		
		//Nach oben
		else {
			list($brother,$ord2)=$db->first("SELECT forumid,ord FROM ".PRE."_forums WHERE parents='".addslashes($parents)."' AND ord<'".$ord1."' ORDER BY ord DESC LIMIT 1");
			if ( !$brother ) die('no upper brother found!');
		}
		
		$db->query("UPDATE ".PRE."_forums SET ord=".($ord1+$ord2)."-ord WHERE forumid IN ('".$_REQUEST['id']."','".$brother."')");
		header("HTTP/1.1 301 Moved Permanently");
		header('location:action.php?action=forum.show');
	}
}*/



///////////////////////////////////////////////////////////////////////////////////// ANKÜNDIGUNGEN

//***************************** Akündigungen zeigen *****************************
function announce() {
	global $set,$apx,$db,$html;
	
	//Aktionen
	if ( $_REQUEST['do']=='add' ) return $this->announce_add();
	if ( $_REQUEST['do']=='edit' ) return $this->announce_edit();
	if ( $_REQUEST['do']=='del' ) return $this->announce_del();
	
	//Voreinstellung
	if ( !$_REQUEST['what'] ) {
		$_REQUEST['what'] = 'posts';
	}
	
	echo'<p class="slink">&raquo; <a href="action.php?action=forum.announce&amp;do=add&amp;criteria='.$_REQUEST['what'].'">'.$apx->lang->get('ADDANNOUNCE').'</a></p>';
	
	$col[]=array('',0,'');
	$col[]=array('COL_TITLE',50,'class="title"');
	$col[]=array('COL_USER',30,'align="center"');
	$col[]=array('COL_PUBDATE',20,'align="center"');
	
	$orderdef[0]='addtime';
	$orderdef['title']=array('a.title','ASC','COL_TITLE');
	$orderdef['username']=array('b.username','ASC','COL_USER');
	$orderdef['addtime']=array('a.starttime','DESC','SORT_ADDTIME');
	$orderdef['publication']=array('a.starttime','DESC','COL_PUBDATE');
	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_forum_announcements WHERE userid!=''");
	pages('action.php?action=forum.announce&amp;sortby='.$_REQUEST['sortby'],$count);
	
	$data=$db->fetch("SELECT a.id,a.title,a.userid,a.starttime,a.endtime,b.username FROM ".PRE."_forum_announcements AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.userid!='' ".getorder($orderdef).getlimit());
	if ( count($data) ) {
		
		foreach ( $data AS $res ) {
			++$i;
			if ( !$res['starttime'] ) $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['endtime']<time() ) $tabledata[$i]['COL1']='<img src="design/greendotcross.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['starttime']>time() ) $tabledata[$i]['COL1']='<img src="design/greendotwait.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			
			$link = mklink(
				$set['forum']['directory'].'/announcement.php?id='.$res['id'],
				$set['forum']['directory'].'/announcement,'.$res['id'].urlformat($res['title']).'.html'
			);
			
			$tabledata[$i]['COL2']='<a href="'.$link.'" target="_blank">'.replace($res['title']).'</a>';
			$tabledata[$i]['COL3']=$res['username'];
			if ( $res['starttime'] ) $tabledata[$i]['COL4']=mkdate($res['starttime'],'<br />');
			else $tabledata[$i]['COL4']='&nbsp;';
			
			//Optionen
			$tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'forum.announce', 'do=edit&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			$tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'forum.announce', 'do=del&id='.$res['id'], $apx->lang->get('CORE_DEL'));
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	orderstr($orderdef,'action.php?action=forum.announce');
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Akündigung hinzufügen *****************************
function announce_add() {
	global $set,$apx,$db;
	$apx->lang->dropaction('forum','announce_add');
	
	//Forum-Liste
	if ( !is_array($_POST['forumid']) || $_POST['forumid'][0]=='all' ) $_POST['forumid']=array('all');
	
	//Absenden
	if ( $_POST['send'] ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !count($_POST['forumid']) || !$_POST['title'] || !$_POST['text'] ) infoNotComplete();
		else {
			
			//Veröffentlichung
			if ( $_POST['pubnow'] ) {
				$_POST['starttime']=time();
				$_POST['endtime']=3000000000;
			}
			else {
				$_POST['starttime'] = maketime(1);
				if ( $_POST['starttime'] ) {
					$_POST['endtime'] = maketime(2);
					if ( !$_POST['endtime'] || $_POST['endtime']<$_POST['starttime'] ) {
						$_POST['endtime']=3000000000;
					}
				}
			}
			
			$_POST['addtime'] = time();
			$_POST['userid'] = $apx->user->info['userid'];
			
			//Ankündigung erstellen
			$db->dinsert(PRE.'_forum_announcements','userid,title,text,addtime,starttime,endtime');
			$nid = $db->insert_id();
			
			//Foren eintragen
			if ( $_POST['forumid'][0]=='all' ) {
				$db->query("
					INSERT IGNORE INTO ".PRE."_forum_anndisplay
					VALUES ('".$nid."', '0')
				");
			}
			else {
				foreach ( $_POST['forumid'] AS $fid ) {
					$fid = (int)$fid;
					if ( !$fid ) continue;
					$db->query("
						INSERT IGNORE INTO ".PRE."_forum_anndisplay
						VALUES ('".$nid."', '".$fid."')
					");
				}
			}
			
			logit('FORUM_ANNOUNCEADD','ID #'.$nid);
			printJSRedirect('action.php?action=forum.announce');
		}
	}
	else {
		maketimepost(1,time());
		maketimepost(2,'');
		$_POST['forumid'] = array('all');
		
		//Foren auslesen
		$data=$this->cat->getTree(array('iscat', 'title', 'posts', 'threads'));
		$forumlist = '<option value="all" style="font-weight:bold;"'.iif(in_array('all',$_POST['forumid']),' selected="selected"').'>'.$apx->lang->get('ALLFORUMS').'</option>';
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$space = str_repeat('&nbsp;&nbsp;', $res['level']-1);
				if ( $res['iscat'] ) $style=' style="background:#EAEAEA"';
				else $style='';
				$forumlist.='<option value="'.$res['forumid'].'"'.$style.'>'.$space.replace($res['title']).'</option>';
			}
		}
		
		$apx->tmpl->assign('FORUMLIST',$forumlist);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('STARTTIME',choosetime(1,1,maketime(1)));
		$apx->tmpl->assign('ENDTIME',choosetime(2,1,maketime(2)));
		$apx->tmpl->assign('ACTION','add');
		
		$apx->tmpl->parse('announceadd_announceedit');
	}
}



//***************************** Akündigung bearbeiten *****************************
function announce_edit() {
	global $set,$apx,$db;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->lang->dropaction('forum','announce_edit');
	
	//Forum-Liste
	if ( !is_array($_POST['forumid']) || $_POST['forumid'][0]=='all' ) $_POST['forumid']=array('all');
	
	//Absenden
	if ( $_POST['send'] ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !count($_POST['forumid']) || !$_POST['title'] || !$_POST['text'] ) infoNotComplete();
		else {
			
			//Veröffentlichung
			$_POST['starttime'] = maketime(1);
			if ( $_POST['starttime'] ) {
				$_POST['endtime'] = maketime(2);
				if ( !$_POST['endtime'] || $_POST['endtime']<$_POST['starttime'] ) {
					$_POST['endtime']=3000000000;
				}
			}
			
			//Ankündigung erstellen
			$db->dupdate(PRE.'_forum_announcements','title,text,starttime,endtime',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			
			//Foren eintragen
			$db->query("DELETE FROM ".PRE."_forum_anndisplay WHERE id='".$_REQUEST['id']."'");
			if ( $_POST['forumid'][0]=='all' ) {
				$db->query("
					INSERT IGNORE INTO ".PRE."_forum_anndisplay
					VALUES ('".$_REQUEST['id']."', '0')
				");
			}
			else {
				foreach ( $_POST['forumid'] AS $fid ) {
					$fid = (int)$fid;
					if ( !$fid ) continue;
					$db->query("
						INSERT IGNORE INTO ".PRE."_forum_anndisplay
						VALUES ('".$_REQUEST['id']."', '".$fid."')
					");
				}
			}
			
			logit('FORUM_ANNOUNCEADD','ID #'.$nid);
			printJSRedirect('action.php?action=forum.announce');
		}
	}
	else {
		$_POST = $db->first("SELECT title,text,starttime,endtime FROM ".PRE."_forum_announcements WHERE id='".$_REQUEST['id']."' LIMIT 1");
		
		//Forum-Ids auslesen
		$data = $db->fetch("SELECT forumid FROM ".PRE."_forum_anndisplay WHERE id='".$_REQUEST['id']."'");
		$_POST['forumid']=array();
		foreach ( $data AS $res ) {
			if ( $res['forumid']==0 ) {
				$_POST['forumid']=array('all');
				break;
			}
			else {
				$_POST['forumid'][] = $res['forumid'];
			}
		}
		
		//Veröffentlichung
		if ( $_POST['starttime'] ) {
			maketimepost(1,$_POST['starttime']);
			if ( $_POST['endtime']<2147483647 ) maketimepost(2,$_POST['endtime']);
		}
		
		//Foren auslesen
		$data=$this->cat->getTree(array('iscat', 'title', 'posts', 'threads'));
		$forumlist = '<option value="all" style="font-weight:bold;"'.iif(in_array('all',$_POST['forumid']),' selected="selected"').'>'.$apx->lang->get('ALLFORUMS').'</option>';
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$space = str_repeat('&nbsp;&nbsp;', $res['level']-1);
				if ( $res['iscat'] ) $style=' style="background:#EAEAEA"';
				else $style='';
				$forumlist.='<option value="'.$res['forumid'].'"'.$style.''.iif(in_array($res['forumid'],$_POST['forumid']),' selected="selected"').'>'.$space.replace($res['title']).'</option>';
			}
		}
		
		$apx->tmpl->assign('FORUMLIST',$forumlist);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('STARTTIME',choosetime(1,1,maketime(1)));
		$apx->tmpl->assign('ENDTIME',choosetime(2,1,maketime(2)));
		$apx->tmpl->assign('ACTION','edit');
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		
		$apx->tmpl->parse('announceadd_announceedit');
	}
}



//***************************** Akündigung löschen *****************************
function announce_del() {
	global $set,$apx,$db;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->lang->dropaction('forum','announce_del');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_forum_announcements WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$db->query("DELETE FROM ".PRE."_forum_anndisplay WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('FORUM_ANNOUNCEDEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('forum.announce'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_forum_announcements WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('announcedel',array('ID'=>$_REQUEST['id']));
	}
}



///////////////////////////////////////////////////////////////////////////////////// RÄNGE

//***************************** Ränge zeigen *****************************
function ranks() {
	global $set,$apx,$db,$html;
	
	//Aktionen
	if ( $_REQUEST['do']=='add' ) return $this->ranks_add();
	if ( $_REQUEST['do']=='edit' ) return $this->ranks_edit();
	if ( $_REQUEST['do']=='del' ) return $this->ranks_del();
	
	//Voreinstellung
	if ( !$_REQUEST['what'] ) {
		$_REQUEST['what'] = 'posts';
	}
	
	echo'<p class="slink">&raquo; <a href="action.php?action=forum.ranks&amp;do=add&amp;criteria='.$_REQUEST['what'].'">'.$apx->lang->get('ADDRANK').'</a></p>';
	
	//Layer
	$layerdef[]=array('LAYER_POST','action.php?action=forum.ranks&amp;what=posts',$_REQUEST['what']=='posts');
	$layerdef[]=array('LAYER_USER','action.php?action=forum.ranks&amp;what=user',$_REQUEST['what']=='user');
	$layerdef[]=array('LAYER_USERGROUPS','action.php?action=forum.ranks&amp;what=groups',$_REQUEST['what']=='groups');
	
	//Layer Header ausgeben
	$html->layer_header($layerdef);
	
	///////////////////// Benutzer-Ränge
	if ( $_REQUEST['what']=='user' ) {
		$col[]=array('COL_RANK',50,'class="title"');
		$col[]=array('COL_USER',50,'align="center"');
		
		$orderdef[0]='title';
		$orderdef['title']=array('a.title','ASC','COL_RANK');
		$orderdef['username']=array('b.username','ASC','COL_USER');
		
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_forum_ranks WHERE userid!=''");
		pages('action.php?action=forum.ranks&amp;what='.$_REQUEST['what'].'&amp;sortby='.$_REQUEST['sortby'],$count);
		
		$data=$db->fetch("SELECT a.id,a.title,a.userid,b.username FROM ".PRE."_forum_ranks AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.userid!='' ".getorder($orderdef).getlimit());
		if ( count($data) ) {
			
			foreach ( $data AS $res ) {
				++$i;		
				$tabledata[$i]['COL1']=$res['title'];
				$tabledata[$i]['COL2']=$res['username'];
				
				//Optionen
				$tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'forum.ranks', 'do=edit&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
				$tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'forum.ranks', 'do=del&id='.$res['id'], $apx->lang->get('CORE_DEL'));
			}
		}
	}
	
	
	///////////////////// Benutzergruppen-Ränge
	elseif ( $_REQUEST['what']=='groups' ) {
		$col[]=array('COL_RANK',70,'class="title"');
		$col[]=array('COL_USERGROUP',30,'align="center"');
		
		$orderdef[0]='title';
		$orderdef['title']=array('a.title','ASC','COL_RANK');
		$orderdef['group']=array('b.name','ASC','COL_USERGROUP');
		
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_forum_ranks WHERE groupid!=0");
		pages('action.php?action=forum.ranks&amp;what='.$_REQUEST['what'].'&amp;sortby='.$_REQUEST['sortby'],$count);
		
		$data=$db->fetch("SELECT a.id,a.title,b.name FROM ".PRE."_forum_ranks AS a LEFT JOIN ".PRE."_user_groups AS b USING(groupid) WHERE a.groupid!=0 ".getorder($orderdef).getlimit());
		if ( count($data) ) {
			
			foreach ( $data AS $res ) {
				++$i;		
				$tabledata[$i]['COL1']=$res['title'];
				$tabledata[$i]['COL2']=$res['name'];
				
				//Optionen
				$tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'forum.ranks', 'do=edit&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
				$tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'forum.ranks', 'do=del&id='.$res['id'], $apx->lang->get('CORE_DEL'));
			}
		}
	}
	
	
	///////////////////// Posting-Ränge
	else {
		$col[]=array('COL_RANK',70,'class="title"');
		$col[]=array('COL_POSTS',30,'align="center"');
		
		$orderdef[0]='posts';
		$orderdef['title']=array('title','ASC','COL_RANK');
		$orderdef['posts']=array('minposts','ASC','COL_POSTS');
		
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_forum_ranks WHERE minposts!=-1");
		pages('action.php?action=forum.ranks&amp;sortby='.$_REQUEST['sortby'],$count);
		
		$data=$db->fetch("SELECT id,title,minposts FROM ".PRE."_forum_ranks WHERE minposts!=-1 ".getorder($orderdef).getlimit());
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				++$i;		
				$tabledata[$i]['COL1']=$res['title'];
				$tabledata[$i]['COL2']=$res['minposts'];
				
				//Optionen
				$tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'forum.ranks', 'do=edit&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
				$tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'forum.ranks', 'do=del&id='.$res['id'], $apx->lang->get('CORE_DEL'));
			}
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	orderstr($orderdef,'action.php?action=forum.ranks&amp;what='.$_REQUEST['what']);
	
	save_index($_SERVER['REQUEST_URI']);
	
	//Layer-Footer ausgeben
	$html->layer_footer();
}



//***************************** Rang hinzufügen *****************************
function ranks_add() {
	global $set,$apx,$db;
	$apx->lang->dropaction('forum','ranks_add');
	
	//Absenden
	if ( $_POST['send'] ) {
		
		//Benutzer-ID bestimmen
		$userid=0;
		if ( $_POST['criteria']=='user' ) {
			list($userid)=$db->first("SELECT userid FROM ".PRE."_user WHERE LOWER(username_login)='".strtolower(addslashes($_POST['username']))."' LIMIT 1");
		}
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || ( $_POST['criteria']=='posts' && ( $_POST['minposts']==='' || intval($_POST['minposts'])<0 ) ) || ( $_POST['criteria']=='user' && !$_POST['username'] ) || ( $_POST['criteria']=='groups' && !$_POST['groupid'] ) ) infoNotComplete();
		elseif ( $_POST['criteria']=='user' && !$userid ) info($apx->lang->get('INFO_USERFAILED'));
		else {
			if ( $_POST['criteria']=='posts' ) unset($_POST['username'],$_POST['groupid']);
			if ( $_POST['criteria']=='groups' ) {
				unset($_POST['minposts'],$_POST['username']);
				$_POST['minposts']=-1;
			}
			if ( $_POST['criteria']=='user' ) {
				unset($_POST['minposts'],$_POST['username'],$_POST['groupid']);
				$_POST['userid']=$userid;
				$_POST['minposts']=-1;
			}
			
			$db->dinsert(PRE.'_forum_ranks','title,color,image,minposts,userid,groupid');
			logit('FORUM_RANKADD','ID #'.$db->insert_id());
			printJSRedirect('action.php?action=forum.ranks&what='.$_POST['criteria']);
		}
	}
	else {
		$_POST['minposts']=-1;
		$_POST['criteria'] = 'posts';
		if ( $_GET['criteria'] ) {
			$_POST['criteria']=$_GET['criteria'];
		}
		
		
		//Benutzergruppen
		$data=$db->fetch("SELECT groupid,name FROM ".PRE."_user_groups ORDER BY name ASC");
		foreach ( $data AS $res ) {
			$grouplist.='<option value="'.$res['groupid'].'"'.iif($res['groupid']==$_POST['groupid'],' selected="selected"').'>'.replace($res['name']).'</option>';
		}
		
		$apx->tmpl->assign('CRITERIA',$_POST['criteria']);
		$apx->tmpl->assign('MINPOSTS',intval($_POST['minposts']));
		$apx->tmpl->assign('USERNAME',compatible_hsc($_POST['username']));
		$apx->tmpl->assign('GROUPLIST',$grouplist);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('COLOR',compatible_hsc($_POST['color']));
		$apx->tmpl->assign('IMAGE',compatible_hsc($_POST['image']));
		$apx->tmpl->assign('ACTION','add');
		
		$apx->tmpl->parse('rankadd_rankedit');
	}
}



//***************************** Rang bearbeiten *****************************
function ranks_edit() {
	global $set,$apx,$db;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->lang->dropaction('forum','ranks_edit');
	
	//Absenden
	if ( $_POST['send'] ) {
		
		//Benutzer-ID bestimmen
		$userid=0;
		if ( $_POST['criteria']=='user' ) {
			list($userid)=$db->first("SELECT userid FROM ".PRE."_user WHERE LOWER(username_login)='".strtolower(addslashes($_POST['username']))."' LIMIT 1");
		}
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || ( $_POST['criteria']=='posts' && ( $_POST['minposts']==='' || intval($_POST['minposts'])<0 ) ) || ( $_POST['criteria']=='user' && !$_POST['username'] ) || ( $_POST['criteria']=='groups' && !$_POST['groupid'] ) ) infoNotComplete();
		elseif ( $_POST['criteria']=='user' && !$userid ) info($apx->lang->get('INFO_USERFAILED'));
		else {
			if ( $_POST['criteria']=='posts' ) unset($_POST['username'],$_POST['groupid']);
			if ( $_POST['criteria']=='groups' ) {
				unset($_POST['minposts'],$_POST['username']);
				$_POST['minposts']=-1;
			}
			if ( $_POST['criteria']=='user' ) {
				unset($_POST['minposts'],$_POST['username'],$_POST['groupid']);
				$_POST['userid']=$userid;
				$_POST['minposts']=-1;
			}
			
			$db->dupdate(PRE.'_forum_ranks','title,color,image,minposts,userid,groupid',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('FORUM_RANKEDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('forum.ranks'));
		}
	}
	else {
		$res=$db->first("SELECT * FROM ".PRE."_forum_ranks WHERE id='".$_REQUEST['id']."' LIMIT 1");
		foreach ( $res AS $key => $value ) $_POST[$key]=$value;
		if ( $res['userid'] ) {
			list($_POST['username'])=$db->first("SELECT username_login FROM ".PRE."_user WHERE userid='".$res['userid']."' LIMIT 1");
			$_POST['criteria']='user';
		}
		elseif ( $res['groupid'] ) $_POST['criteria']='groups';
		else $_POST['criteria']='posts';
		
		
		//Benutzergruppen
		$data=$db->fetch("SELECT groupid,name FROM ".PRE."_user_groups ORDER BY name ASC");
		foreach ( $data AS $res ) {
			$grouplist.='<option value="'.$res['groupid'].'"'.iif($res['groupid']==$_POST['groupid'],' selected="selected"').'>'.replace($res['name']).'</option>';
		}
		
		$apx->tmpl->assign('CRITERIA',$_POST['criteria']);
		$apx->tmpl->assign('MINPOSTS',intval($_POST['minposts']));
		$apx->tmpl->assign('USERNAME',compatible_hsc($_POST['username']));
		$apx->tmpl->assign('GROUPLIST',$grouplist);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('COLOR',compatible_hsc($_POST['color']));
		$apx->tmpl->assign('IMAGE',compatible_hsc($_POST['image']));
		$apx->tmpl->assign('ACTION','edit');
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		
		$apx->tmpl->parse('rankadd_rankedit');
	}
}



//***************************** Rang löschen *****************************
function ranks_del() {
	global $set,$apx,$db;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->lang->dropaction('forum','ranks_del');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_forum_ranks WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('FORUM_RANKDEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('forum.ranks'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_forum_ranks WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('rankdel',array('ID'=>$_REQUEST['id']));
	}
}



///////////////////////////////////////////////////////////////////////////////////// DATEITYPEN

//***************************** Dateitypen zeigen *****************************
function filetypes() {
	global $set,$apx,$db,$html;
	
	//Aktionen
	if ( $_REQUEST['do']=='add' ) return $this->filetypes_add();
	if ( $_REQUEST['do']=='del' ) return $this->filetypes_del();
	
	if ( $_REQUEST['do']=='edit' ) {
		$this->filetypes_edit();
	}
	else {
		$apx->tmpl->parse('addfiletypes');
	}
	
	$col[]=array('ICON',10,'align="center"');
	$col[]=array('EXT',45,'align="center"');
	$col[]=array('MAXSIZE',45,'align="center"');
	
	$data=$db->fetch("SELECT * FROM ".PRE."_forum_filetypes ORDER BY ext ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$tabledata[$i]['COL1']='<img src="'.iif(substr($res['icon'],0,1)=='/',$res['icon'],'../forum/'.$res['icon']).'" alt="" />';
			$tabledata[$i]['COL2']=$res['ext'];
			$tabledata[$i]['COL3']=$res['size'].' kB';
			$tabledata[$i]['OPTIONS']=optionHTML('edit.gif', 'forum.filetypes', 'do=edit&id='.$res['ext'], $apx->lang->get('CORE_EDIT'));
			$tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'forum.filetypes', 'do=del&id='.$res['ext'], $apx->lang->get('CORE_DEL'));
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
}



//***************************** Dateitypen hinzufügen *****************************

function filetypes_add() {
	global $set,$apx,$db;
	
	list($exists) = $db->first("SELECT ext FROM ".PRE."_forum_filetypes WHERE ext='".addslashes(strtolower($_POST['ext']))."' LIMIT 1");
	
	if ( !checkToken() ) infoInvalidToken();
	elseif ( !$_POST['ext'] || !$_POST['icon'] || !$_POST['size'] ) infoNotComplete();
	elseif ( $exists ) info($apx->lang->get('MSG_EXISTS'));
	else {
		$_POST['ext']=strtolower($_POST['ext']);
		$db->dinsert(PRE.'_forum_filetypes','ext,icon,size');
		logit('FORUM_FILETYPEDEL',$_POST['ext']);
		printJSRedirect('action.php?action=forum.filetypes');
	}
}



//***************************** Dateitypen bearbeiten *****************************

function filetypes_edit() {
	global $set,$apx,$db;
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send'] ) {
		$exists = false;
		if ( $_POST['ext']!=$_REQUEST['id'] ) {
			list($exists) = $db->first("SELECT ext FROM ".PRE."_forum_filetypes WHERE ext='".addslashes(strtolower($_POST['ext']))."' LIMIT 1");
		}
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['ext'] || !$_POST['icon'] || !$_POST['size'] ) infoNotComplete();
		elseif ( $exists ) info($apx->lang->get('MSG_EXISTS'));
		else {
			$_POST['ext']=strtolower($_POST['ext']);
			$db->dupdate(PRE.'_forum_filetypes','ext,icon,size', "WHERE ext='".addslashes(strtolower($_REQUEST['id']))."' LIMIT 1");
			logit('FORUM_FILETYPEDEL',$_POST['ext']);
			printJSRedirect('action.php?action=forum.filetypes');
		}
	}
	else {
		$res = $db->first("SELECT * FROM ".PRE."_forum_filetypes WHERE ext='".addslashes(strtolower($_REQUEST['id']))."' LIMIT 1");
		
		$apx->tmpl->assign('ID', compatible_hsc($_REQUEST['id']));
		$apx->tmpl->assign('EXT', compatible_hsc($res['ext']));
		$apx->tmpl->assign('ICON', compatible_hsc($res['icon']));
		$apx->tmpl->assign('SIZE', compatible_hsc($res['size']));
		
		$apx->tmpl->parse('addfiletypes');
	}
}



//***************************** Dateitypen zeigen *****************************

function filetypes_del() {
	global $set,$apx,$db;
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send'] ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_forum_filetypes WHERE ext='".addslashes(strtolower($_REQUEST['id']))."'");
			logit('FORUM_FILETYPEDEL',$_REQUEST['id']);
			printJSRedirect('action.php?action=forum.filetypes');
		}
	}
	else {
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($_REQUEST['id']))));
		tmessageOverlay('filetypedel', array('ID' => $_REQUEST['id']));
	}
}



///////////////////////////////////////////////////////////////////////////////////// ICONS

//***************************** Icons zeigen *****************************
function icons() {
	global $set,$apx,$db,$html;
	
	//Aktionen
	if ( $_REQUEST['do']=='add' ) return $this->icons_add();
	if ( $_REQUEST['do']=='del' ) return $this->icons_del();
	
	$apx->tmpl->parse('addicons');
	
	$col[]=array('ICON',100);
	
	$icons=$set['forum']['icons'];
	$icons=array_sort($icons,'ord','ASC');
	$count=count($icons);
	
	foreach ( $icons AS $key => $info ) {
		++$i;
		$tabledata[$i]['COL1']='<img src="'.iif(substr($info['file'],0,1)=='/',$info['file'],'../forum/'.$info['file']).'" alt="" />';
		$tabledata[$i]['ID'] = 'node:'.$key;
		
		$tabledata[$i]['OPTIONS']=optionHTMLOverlay('del.gif', 'forum.icons', 'do=del&id='.$key, $apx->lang->get('CORE_DEL'));
		/*if ( $i!=1 ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'forum.icons', 'do=move&direction=up&id='.$key.'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
		else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" />';
		if ( $i!=$count ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'forum.icons', 'do=move&direction=down&id='.$key.'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
		else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" />';*/
	}
	
	echo '<div class="listview" id="list">';
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	echo '</div>';
	
	$apx->tmpl->parse('icons_js');
}


//***************************** Icons hinzufügen *****************************

function icons_add() {
	global $set,$apx,$db;
	
	if ( !checkToken() ) infoInvalidToken();
	else {
		$max=array_key_max($set['forum']['icons']);
		if ( !$max ) $max=-1;
		
		for ( $i=1; $i<=5; $i++ ) {
			if ( !$_POST['file'.$i] ) continue;
			if ( !count($set['forum']['icons']) ) {
				$set['forum']['icons'][1]=array(
					'file' => $_POST['file'.$i],
					'ord' => ++$max
				);
			}
			else {
				$set['forum']['icons'][]=array(
					'file' => $_POST['file'.$i],
					'ord' => ++$max
				);
			}
			logit('FORUM_ICONSADD',$_POST['file'.$i]);
		}
		
		$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($set['forum']['icons']))."' WHERE module='forum' AND varname='icons' LIMIT 1");
		printJSRedirect('action.php?action=forum.icons');
	}
}
	


//***************************** Icons zeigen *****************************

function icons_del() {
	global $set,$apx,$db;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	
	if ( $_POST['send'] ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			unset($set['forum']['icons'][$_REQUEST['id']]);
			$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($set['forum']['icons']))."' WHERE module='forum' AND varname='icons' LIMIT 1");
			logit('FORUM_ICONSDEL',$_REQUEST['id']);
			printJSRedirect('action.php?action=forum.icons');
		}
	}
	else {
		tmessageOverlay('icondel', array('ID' => $_REQUEST['id']));
	}
}



/*//***************************** Icons anordnen *****************************

function icons_move() {
	global $set,$apx,$db;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	
	if ( !checkToken() ) printInvalidToken();
	else {
		$set['forum']['icons']=array_sort($set['forum']['icons'],'ord','ASC');
		$ord1=$set['forum']['icons'][$_REQUEST['id']];
		$brother=false;
		
		//Nach unten
		if ( $_REQUEST['direction']=='down' ) {
			foreach ( $set['forum']['icons'] AS $key => $value ) {
				if ( $prev==$_REQUEST['id'] ) {
					$brother=$key;
					break;
				}
				$prev=$key;
			}
			if ( $brother===false ) die('no lower brother found!');
		}
		
		//Nach oben
		else {
			foreach ( $set['forum']['icons'] AS $key => $value ) {
				if ( $key==$_REQUEST['id'] ) break;
				$brother=$key; 
			}
			if ( $brother===false ) die('no upper brother found!');
		}
		
		$sum=$set['forum']['icons'][$_REQUEST['id']]['ord']+$set['forum']['icons'][$brother]['ord'];
		$set['forum']['icons'][$_REQUEST['id']]['ord']=$sum-$set['forum']['icons'][$_REQUEST['id']]['ord'];
		$set['forum']['icons'][$brother]['ord']=$sum-$set['forum']['icons'][$brother]['ord'];
		
		ksort($set['forum']['icons']);
		$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($set['forum']['icons']))."' WHERE module='forum' AND varname='icons' LIMIT 1");
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: action.php?action=forum.icons');
	}
}*/



//***************************** Index neu erzeugen *****************************

function reindex() {
	global $set,$apx,$db;
	
	if ( $_REQUEST['send'] ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$step=100;
			$_REQUEST['limit']=(int)$_REQUEST['limit'];
			
			//Index löschen
			if ( $_POST['send'] ) {
				$db->query("DELETE FROM ".PRE."_forum_index");
			}
			
			//Index neu erstellen
			$data=$db->fetch("
				SELECT p.postid,p.threadid,p.title,p.text
				FROM ".PRE."_forum_posts AS p
				LEFT JOIN ".PRE."_forum_threads AS t USING(threadid)
				WHERE p.del=0 AND t.del=0
				LIMIT ".$_REQUEST['limit'].",".$step
			);
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					$this->update_index($res['title'],$res['threadid'],$res['postid'],1);
					$this->update_index($res['text'],$res['threadid'],$res['postid'],1);
				}
				
				tmessage('reindex_forwarder',array('FORWARDER'=>'action.php?action=forum.reindex&amp;send=1&amp;limit='.($_REQUEST['limit']+$step).'&sectoken='.$apx->session->get('sectoken')));
			}
			
			//Vorgang beendet
			else {
				logit('FORUM_REINDEX');
				message($apx->lang->get('MSG_OK'));
			}
		}
	}
	else {
		tmessage('reindex');
	}
}


function update_index($text,$threadid,$postid,$title=false) {
	global $set,$db,$apx;
	$threadid=(int)$threadid;
	$postid=(int)$postid;
	$title=(int)$title;
	if ( !$threadid ) return false;
	if ( !$postid ) return false;
	
	//Codes entfernen
	while ( preg_match('#\[([a-z0-9]+)(=.*?)?\](.*?)\[/\\1\]#si',$text) ) {
		$text=preg_replace('#\[([a-z0-9]+)(=.*?)?\](.*?)\[/\\1\]#si','\\3',$text);
	}
	
	//Wörter trennen
	$text = strtolower($text);
	$words = extract_words($text);
	$words = array_unique($words);
	
	//SQL erzeugen
	include('../forum/lib/stopwords.php');
	$values='';
	foreach ( $words AS $word ) {
		$word=trim($word);
		if ( !$word ) continue; //Leere Wörter überspringen
		if ( strlen($word)<3 ) continue; //Wörter kürzer als 3 Zeichen überspringen
		if ( strlen($word)>50 ) continue; //Wörter länger als 50 Zeichen überspringen
		if ( in_array($word,$stopwords) ) continue; //Stopwörter überspringen
		$values.=iif($values,',')."('".addslashes(strtolower($word))."','".$threadid."','".$postid."','".$title."')";
	}
	
	//In die Datenbank eintragen
	if ( $values ) {
		$db->query("INSERT INTO ".PRE."_forum_index (word,threadid,postid,istitle) VALUES ".$values);
	}
	
	return true;
}



//***************************** Synchronsieren *****************************

function resync() {
	global $set,$apx,$db;
	
	if ( $_REQUEST['send'] ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			@set_time_limit(600);
			
			//Thread- und Beitragszahlen berichtigen
			$data = $db->fetch("
				SELECT forumid
				FROM ".PRE."_forums
			");
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					$forumid = $res['forumid'];
					$forumThreads = 0;
					$forumPosts = 0;
					$forumLastpost = array();
					$forumLastthread = array();
					
					//Threads auslesen
					$threaddata = $db->fetch("
						SELECT threadid, prefix, title, icon, del
						FROM ".PRE."_forum_threads
						WHERE del=0 AND moved=0 AND forumid='".$forumid."'
					");
					if ( count($threaddata) ) {
						foreach ( $threaddata AS $tres ) {
							$threadid = $tres['threadid'];
							list($threadPosts) = $db->first("
								SELECT count(postid)
								FROM ".PRE."_forum_posts
								WHERE del=0 AND threadid='".$threadid."'
							");
							$threadLastpost = $db->first("
								SELECT postid, userid, username, time
								FROM ".PRE."_forum_posts
								WHERE del=0 AND threadid='".$threadid."'
								ORDER BY time DESC
								LIMIT 1
							");
							$db->query("
								UPDATE ".PRE."_forum_threads
								SET
									posts='".$threadPosts."',
									lastpost='".$threadLastpost['postid']."',
									lastposter='".addslashes($threadLastpost['username'])."',
									lastposter_userid='".$threadLastpost['userid']."',
									lastposttime='".$threadLastpost['time']."'
								WHERE threadid='".$threadid."'
							");
							
							//Themen/Beiträge im Forum
							if ( !$tres['del'] ) {
								++$forumThreads;
							}
							$forumPosts += $threadPosts;
							
							//Lastpost im Forum
							if ( !$forumLastpost || $forumLastpost['time']<$threadLastpost['time'] ) {
								$forumLastthread = $tres;
								$forumLastpost = $threadLastpost;
							}
						}
					}
					
					//Forum aktualisieren
					$db->query("
						UPDATE ".PRE."_forums
						SET
							threads='".$forumThreads."',
							posts='".$forumPosts."',
							lastpost='".$forumLastpost['postid']."',
							lastposter='".addslashes($forumLastpost['username'])."',
							lastposter_userid='".$forumLastpost['userid']."',
							lastposttime='".$forumLastpost['time']."',
							lastthread='".$forumLastthread['threadid']."',
							lastthread_title='".addslashes($forumLastthread['title'])."',
							lastthread_icon='".addslashes($forumLastthread['icon'])."',
							lastthread_prefix='".addslashes($forumLastthread['prefix'])."'
						WHERE forumid='".$forumid."'
						LIMIT 1");
				}
			}
			
			logit('FORUM_RESYNC');
			message($apx->lang->get('MSG_OK'));
		}
	}
	else {
		tmessage('resync');
	}
}



} //END CLASS


?>
