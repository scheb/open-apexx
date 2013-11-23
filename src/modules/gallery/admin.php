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


# GALLERY 
# =======

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class action {

var $cat;

//Startup
function action() {
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$this->cat=new RecursiveTree(PRE.'_gallery', 'id');
}


//Updatetime einer Galerie setzen
function setGalleryUpdatetime($galId) {
	global $db;
	$gallery = $this->cat->getNode($galId);
	if ( !$gallery ) {
		return;
	}
	
	$updateIds = array_merge($gallery['parents'], array($gallery['id']));
	foreach ( $updateIds AS $id ) {
		$gallery = $this->cat->getNode($id);
		$searchIds = array_merge($gallery['children'], array($gallery['id']));
		list($updatetime) = $db->first("
			SELECT max(addtime)
			FROM ".PRE."_gallery_pics
			WHERE galid IN (".implode(',', $searchIds).") AND active=1
		");
		$db->query("
			UPDATE ".PRE."_gallery
			SET lastupdate='".$updatetime."'
			WHERE id='".$id."'
			LIMIT 1
		");
	}
}


////////////////////////////////////////////////////////////////////////////////////////// GALERIEN

//***************************** Galerien zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	//Struktur reparieren
	if ( $_REQUEST['repair'] && $set['gallery']['subgals'] ) {
		$this->cat->repair();
		echo 'Repair done!';
		return;
	}
	
	//Suche durchführen
	if ( !$set['gallery']['subgals'] && $_REQUEST['item'] ) {
		$where = '';
		
		//Suchbegriff
		if ( $_REQUEST['item'] ) {
			$where .= " AND title LIKE '%".addslashes_like($_REQUEST['item'])."%'";
		}
		
		$data=$db->fetch("SELECT id FROM ".PRE."_gallery WHERE 1 ".$where);
		$ids = get_ids($data, 'id');
		$ids[] = -1;
		$searchid = saveSearchResult('admin_gallery', $ids, array(
			'item' => $_REQUEST['item']
		));
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: action.php?action=gallery.show&what='.$_REQUEST['what'].'&searchid='.$searchid);
		return;
	}
	
	
	quicklink('gallery.add');
	
	//DnD-Hinweis
	if ( $apx->user->has_right('gallery.edit') && ( $set['gallery']['subgals'] || $set['gallery']['ordergal']==3 ) ) {
		echo '<p class="hint">'.$apx->lang->get('USEDND').'</p>';
	}
	
	$orderdef[0]='title';
	$orderdef['title']=array('title','ASC','COL_TITLE');
	$orderdef['addtime']=array('addtime','DESC','SORT_ADDTIME');
	$orderdef['starttime']=array('starttime','DESC','COL_STARTTIME');
	
	
	//Suchergebnis?
	$resultFilter = '';
	if ( !$set['gallery']['subgals'] && $_REQUEST['searchid'] ) {
		$searchRes = getSearchResult('admin_gallery', $_REQUEST['searchid']);
		if ( $searchRes ) {
			list($resultIds, $resultMeta) = $searchRes;
			$_REQUEST['item'] = $resultMeta['item'];
			$resultFilter = " AND a.id IN (".implode(', ', $resultIds).")";
		}
		else {
			$_REQUEST['searchid'] = '';
		}
	}
	
	$col[]=array('&nbsp;',0,'');
	$col[]=array('ID',0,'align="center"');
	$col[]=array('COL_TITLE',60,'class="title"');
	$col[]=array('COL_STARTTIME',25,'align="center"');
	$col[]=array('COL_COUNT',15,'align="center"');
	
	if ( !$set['gallery']['subgals'] ) {
		$apx->tmpl->assign('ITEM',compatible_hsc($_REQUEST['item']));
		$apx->tmpl->parse('search');
		
		letters('action.php?action=gallery.show'.iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']));
		
		if ( $_REQUEST['letter']=='spchar' ) $where=" AND title NOT REGEXP(\"^[a-zA-Z]\") ";
		elseif ( $_REQUEST['letter'] )  $where=" AND title LIKE '".addslashes($_REQUEST['letter'])."%' ";
		
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_gallery AS a WHERE 1 ".$resultFilter.$where.section_filter(true, 'secid'));
		pages('action.php?action=gallery.show'.iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']).'&amp;sortby='.$_REQUEST['sortby'].'&amp;letter='.$_REQUEST['letter'],$count);
		
		//Orderby
		if ( $set['gallery']['ordergal']==3 ) { $sortby=' ORDER BY ord ASC '; $orderdef=array(); }
		else $sortby=getorder($orderdef);
		
		$data=$db->fetch("SELECT id,secid,title,starttime,endtime FROM ".PRE."_gallery AS a WHERE 1 ".$resultFilter.$where.section_filter(true, 'secid').$sortby.getlimit());
	}
	else {
		$data = $this->cat->getTree(array('*'), null, section_filter(false, 'secid'));
	}
	
	if ( count($data) ) {
		
		//Untergalerien?
		if ( $set['gallery']['subgals'] ) {
			list($space,$follow)=parse_tree($data);
			$isactive[0]=true; //Root ist immer aktiv ;)
		}
		
		$i=($_REQUEST['p']-1)*$set['admin_epp'];
		
		foreach ( $data AS $res ) {
			++$i;
			if ( $res['level']==1 ) ++$tree;
			$isactive[$res['level']]=$res['starttime'];
			
			if ( !$res['starttime'] ) $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['endtime']<time() ) $tabledata[$i]['COL1']='<img src="design/greendotcross.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['starttime']>time() ) $tabledata[$i]['COL1']='<img src="design/greendotwait.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			
			$tmp=unserialize_section($res['secid']);
			$title=replace(strip_tags($res['title']));
			$link=mklink(
				'gallery.php?id='.$res['id'],
				'gallery,list'.$res['id'].',1'.urlformat($res['title']).'.html',
				iif($set['main']['forcesection'],iif(unserialize_section($res['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
			);
			
			list($pics)=$db->first("SELECT count(id) FROM ".PRE."_gallery_pics WHERE galid='".$res['id']."'");
			list($activepics)=$db->first("SELECT count(id) FROM ".PRE."_gallery_pics WHERE ( galid='".$res['id']."' AND active='1' )");
			
			$tabledata[$i]['COL2']=$res['id'];
			$tabledata[$i]['COL3']='<a href="'.$link.'" target="_blank">'.$title.'</a>';
			$tabledata[$i]['COL4']=iif($res['starttime'],mkdate($res['starttime'],'<br />'),'&nbsp;');
			$tabledata[$i]['COL5']=number_format($pics,0,'','.');
			$tabledata[$i]['CLASS'] = 'l'.($res['level']-1).($res['children'] ? ' haschildren' : '').($res['level']>1 ? ' hidden' : '');
			$tabledata[$i]['ID'] = 'node:'.$res['id'];
			
			//Optionen
			if ( $apx->user->has_right('gallery.edit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'gallery.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('gallery.del') ) $tabledata[$i]['OPTIONS'].='<span class="ifhasnochildren">'.optionHTMLOverlay('del.gif', 'gallery.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL')).'</span><span class="ifhaschildren"><img alt="" src="design/ispace.gif"/></span>';
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( ( !$res['starttime'] || $res['endtime']<time() ) && $apx->user->has_right('gallery.enable') && ( !$set['gallery']['subgals'] || $isactive[$res['level']-1] ) ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('enable.gif', 'gallery.enable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
			elseif ( $res['starttime'] && $apx->user->has_right('gallery.disable') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('disable.gif', 'gallery.disable', 'id='.$res['id'], $apx->lang->get('CORE_DISABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			$tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->user->has_right('gallery.pshow') ) $tabledata[$i]['OPTIONS'].=optionHTML('pic.gif', 'gallery.pshow', 'id='.$res['id'], $apx->lang->get('SHOWPICS'));
			if ( $apx->user->has_right('gallery.padd') ) $tabledata[$i]['OPTIONS'].=optionHTML('picadd.gif', 'gallery.padd', 'id='.$res['id'], $apx->lang->get('ADDPICS'));
			
			//Kommentare + Bewertungen
			if ( $apx->is_module('comments') ) {
				$tabledata[$i]['OPTIONS'].='&nbsp;';
				list($comments)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='galleryself' AND mid='".$res['id']."' )");
				if ( $comments && $set['gallery']['galcoms'] && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=galleryself&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			
			//Anordnen: Untergalerien
			/*if ( $set['gallery']['subgals'] ) {
				$tabledata[$i]['OPTIONS'].='&nbsp;';
				if ( $apx->user->has_right('gallery.move') && $follow[$res['id']]['prev'] ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'gallery.move', 'direction=up&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
				if ( $apx->user->has_right('gallery.move') && $follow[$res['id']]['next'] ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'gallery.move', 'direction=down&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
			}
			
			//Anordnen: Einfach
			elseif ( !$set['gallery']['subgals'] && $set['gallery']['ordergal']==3 ) {
				$tabledata[$i]['OPTIONS'].='&nbsp;';
				if ( $apx->user->has_right('gallery.move') && $i!=1 ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'gallery.move', 'direction=up&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
				if ( $apx->user->has_right('gallery.move') && $i!=$count ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'gallery.move', 'direction=down&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
			}*/
			
			if ( $res['level']==1 ) ++$pdone[0];
			else ++$pdone[$tree][$res['level']];
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	
	if ( $set['gallery']['subgals'] ) {
		echo '<div class="treeview" id="tree">';
		$html->table($col);
		echo '</div>';
		
		$open = $apx->session->get('gallery_open');
		$open = dash_unserialize($open);
		$opendata = array();
		foreach ( $open AS $catid ) {
			$opendata[] = array(
				'ID' => $catid
			);
		}
		$apx->tmpl->assign('OPEN', $opendata);
		$apx->tmpl->assign('EDIT_ALLOWED', $apx->user->has_Right('gallery.edit'));
		$apx->tmpl->parse('show_js');
	}
	elseif ( $set['gallery']['ordergal']==3 ) {
		echo '<div class="listview" id="list">';
		$html->table($col);
		echo '</div>';
		$apx->tmpl->parse('show_listjs');
	}
	else {
		$html->table($col);
		orderstr($orderdef,'action.php?action=gallery.show'.iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']).'&amp;letter='.$_REQUEST['letter']);
	}
	
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Galerie erstellen *****************************
function add() {
	global $set,$db,$apx;
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] ) infoNotComplete();
		else {
			$insert = array(
				'secid' => serialize_section($_POST['secid']),
				'prodid' => $_POST['prodid'],
				'title' => $_POST['title'],
				'description' => $_POST['description'],
				'meta_description' => $_POST['meta_description'],
				'password' => $_POST['password'],
				'addtime' => time(),
				'searchable' => $_POST['searchable'],
				'restricted' => $_POST['restricted'],
				'allowcoms' => $_POST['allowcoms']
			);
			
			//GALERIE FREISCHALTEN
			if ( $apx->user->has_right('gallery.enable') && $_POST['pubnow'] ) {
				
				//Prüfen, ob der Elternknoten deaktiviert ist => falls ja den Knoten deaktivieren
				if ( $_POST['parent']=='root' ) {
					$insert['starttime'] = time();
					$insert['endtime'] = 3000000000;
				}
				else {
					list($parentEnabled) = $db->first("SELECT starttime FROM ".PRE."_gallery WHERE id='".intval($_POST['parent'])."' LIMIT 1");
					if ( $parentEnabled ) {
						$insert['starttime'] = time();
						$insert['endtime'] = 3000000000;
					}
				}
			}
			
			//WENN NODE
			if ( $set['gallery']['subgals'] && $_POST['parent']!='root' ) {
				list($secid) = $db->first("SELECT secid FROM ".PRE."_gallery WHERE id='".intval($_POST['parent'])."' LIMIT 1");
				$insert['secid'] = $secid;
				unset($insert['password'], $insert['restricted']);
				$nid = $this->cat->createNode(intval($_POST['parent']), $insert);
			}
			
			//WENN ROOT
			else {
				$nid = $this->cat->createNode(0, $insert);
			}
			
			logit('GALLERY_ADD','ID #'.$nid);
			
			//Tags
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_gallery_tags VALUES('".$nid."', '".$tagid."')");
			}
			
			require(BASEDIR.'lib/class.mediamanager.php');
			$mm=new mediamanager;
			$mm->createdir($nid,'gallery');
			
			if ( $_POST['submit2'] ) {
				if ( $_REQUEST['updateparent'] ) {
					printJSRedirect('action.php?action=gallery.padd&id='.$nid.'&updateparent='.$_REQUEST['updateparent']);
				}
				else {
					printJSRedirect('action.php?action=gallery.padd&id='.$nid);
				}
			}
			else {
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], get_gallery_list($nid));
				}
				else {
					printJSRedirect(get_index('gallery.show'));
				}
			}
		}
	}
	else {
		$_POST['searchable']=1;
		$_POST['allowcoms']=1;
		
		
		//Mutterelement
		if ( $set['gallery']['subgals'] ) {
			$data = $this->cat->getTree(array('title'));
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					$gallist.='<option value="'.$res['id'].'"'.iif($_POST['parent']==$res['id'],' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;',($res['level']-1)).replace($res['title']).'</option>';
				}
			}
		}
		
		$apx->tmpl->assign('PARENT',$gallist);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('PRODID',$_POST['prodid']);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('DESCRIPTION',compatible_hsc($_POST['description']));
		$apx->tmpl->assign('PASSWORD',compatible_hsc($_POST['password']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('PUBNOW',(int)$_POST['pubnow']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('RESTRICTED',(int)$_POST['restricted']);
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('UPDATEPARENT',(int)$_REQUEST['updateparent']);
		
		$apx->tmpl->parse('add');
	}
}



//***************************** Galerie bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] ) infoNotComplete();
		else {
			$update = array(
				'prodid' => $_POST['prodid'],
				'title' => $_POST['title'],
				'description' => $_POST['description'],
				'meta_description' => $_POST['meta_description'],
				'searchable' => $_POST['searchable'],
				'restricted' => $_POST['restricted'],
				'allowcoms' => $_POST['allowcoms']
			);
			
			//Veröffentlichung
			if ( $apx->user->has_right('gallery.enable') && isset($_POST['t_day_1']) ) {
				$update['starttime']=maketime(1);
				$update['endtime']=maketime(2);
				if ( $update['starttime'] ) {
					if ( !$update['endtime'] || $update['endtime']<=$_POST['starttime'] ) $update['endtime']=3000000000;
				}
			}
			
			//Prüfen, ob der neue Elternknoten deaktiviert ist => falls ja den Knoten deaktivieren
			if ( intval($_POST['parent']) ) {
				list($parentEnabled) = $db->first("SELECT starttime FROM ".PRE."_gallery WHERE id='".intval($_POST['parent'])."' LIMIT 1");
				if ( !$parentEnabled ) {
					$update['starttime'] = 0;
					$update['endtime'] = 0;
				}
			}
			
			//Unter-Galerien werden verwendet
			if ( $set['gallery']['subgals'] ) {
				$nodeInfo = $this->cat->getNode($_REQUEST['id']);
				$currentParentId = array_pop($nodeInfo['parents']);
				
				//Dieser Knoten wird ein Unter-Knoten
				//Übernehme secid vom neuen Parent, password löschen
				if ( intval($_POST['parent']) ) {
					$_POST['parent'] = intval($_POST['parent']);
					
					//Parent hat sich geändert => Daten übernehmen
					if ( $currentParentId!=$_POST['parent'] ) {
						$rootNode = $this->cat->getNode($_POST['parent'], array('secid', 'password', 'restricted'));
						
						$update['secid'] = $rootNode['secid'];
						$update['password'] = '';
						$update['restricted'] = '';
						
						//Unter-Galerien des Knotens anpassen
						$childrenIds = $nodeInfo['children'];
						if ( $childrenIds ) {
							$db->query("
								UPDATE ".PRE."_gallery
								SET secid='".addslashes($update['secid'])."', password = '', restricted=0
								WHERE id IN (".implode(',', $childrenIds).")
							");
						}
					}
				}
				
				//Dieser Knoten ist ein Root-Knoten
				else {
					$update['secid'] = serialize_section($_POST['secid']);
					$update['password'] = $_POST['password'];
					$update['restricted'] = $_POST['restricted'];
					
					//Unter-Galerien des Knotens anpassen
					$childrenIds = $nodeInfo['children'];
					if ( $childrenIds ) {
						$db->query("
							UPDATE ".PRE."_gallery
							SET secid='".addslashes($update['secid'])."', password = '', restricted=0
							WHERE id IN (".implode(',', $childrenIds).")
						");
					}
				}
			}
			
			//Keine Unter-Galerien
			else {
				$update['secid'] = serialize_section($_POST['secid']);
				$update['password'] = $_POST['password'];
				$update['restricted'] = $_POST['restricted'];
			}
			
			$this->cat->moveNode($_REQUEST['id'], intval($_POST['parent']), $update);
			logit('GALLERY_EDIT','ID #'.$_REQUEST['id']);
			
			//Tags
			$db->query("DELETE FROM ".PRE."_gallery_tags WHERE id='".$_REQUEST['id']."'");
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_gallery_tags VALUES('".$_REQUEST['id']."', '".$tagid."')");
			}
			
			printJSRedirect(get_index('gallery.show'));
		}
	}
	else {
		$res = $this->cat->getNode($_REQUEST['id'], array('secid', 'prodid', 'title', 'description', 'meta_description', 'password', 'starttime', 'endtime', 'searchable', 'restricted', 'allowcoms'));
		$_POST['secid']=unserialize_section($res['secid']);
		$_POST['prodid'] = $res['prodid'];
		$_POST['title'] = $res['title'];
		$_POST['description'] = $res['description'];
		$_POST['meta_description'] = $res['meta_description'];
		$_POST['password'] = $res['password'];
		$_POST['searchable'] = $res['searchable'];
		$_POST['restricted'] = $res['restricted'];
		$_POST['allowcoms'] = $res['allowcoms'];
		if ( !$res['parents'] ) $_POST['parent'] = 'root';
		else $_POST['parent'] = array_pop($res['parents']);
		
		//Veröffentlichung
		if ( $res['starttime'] ) {
			maketimepost(1,$res['starttime']);
			if ( $res['endtime']<2147483647 ) maketimepost(2,$res['endtime']);
		}
		
		//Baum
		if ( $set['gallery']['subgals'] ) {
			$gallist='<option value="root" style="font-weight:bold;"'.iif($_POST['parent']=='root',' selected="selected"').'>'.$apx->lang->get('ROOT').'</option>';
			$data = $this->cat->getTree(array('title'));
			if ( count($data) ) {
				$gallist.='<option value=""></option>';
				foreach ( $data AS $res ) {
					if ( $jumplevel && $res['level']>$jumplevel ) continue;
					else $jumplevel=0;
					if ( $_REQUEST['id']==$res['id'] ) { $jumplevel=$res['level']; continue; }
					$gallist.='<option value="'.$res['id'].'"'.iif($_POST['parent']===$res['id'],' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;',($res['level']-1)).replace($res['title']).'</option>';
				}
			}
		}
		
		//Veröffentlichung
		if ( $apx->user->has_right('gallery.enable') && isset($_POST['t_day_1']) ) {
			$apx->tmpl->assign('STARTTIME',choosetime(1,0,maketime(1)));
			$apx->tmpl->assign('ENDTIME',choosetime(2,1,maketime(2)));
		}
		
		//Tags
		$tags = array();
		$tagdata = $db->fetch("
			SELECT t.tag
			FROM ".PRE."_gallery_tags AS n
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE n.id='".$_REQUEST['id']."'
			ORDER BY t.tag ASC
		");
		$tags = get_ids($tagdata, 'tag');
		$_POST['tags'] = implode(', ', $tags);
		
		$apx->tmpl->assign('PARENT',$gallist);
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('PRODID',$_POST['prodid']);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('DESCRIPTION',compatible_hsc($_POST['description']));
		$apx->tmpl->assign('PASSWORD',compatible_hsc($_POST['password']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		$apx->tmpl->assign('RESTRICTED',(int)$_POST['restricted']);
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		
		$apx->tmpl->parse('edit');
	}
}



//***************************** Galerie löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			
			//MYSQL löschen
			$this->cat->deleteNode($_REQUEST['id']);
			$data=$db->fetch("SELECT id,thumbnail,picture FROM ".PRE."_gallery_pics WHERE galid='".$_REQUEST['id']."'");
			$db->query("DELETE FROM ".PRE."_gallery_pics WHERE galid='".$_REQUEST['id']."'");
			
			//Dateien löschen
			if ( count($data) ) {
				require(BASEDIR.'lib/class.mediamanager.php');
				$mm=new mediamanager;
				
				foreach ( $data AS $res ) {
					$ccache[]=$res['id'];
					$mm->deletefile($res['thumbnail']);
					$mm->deletefile($res['picture']);
				}
				
				//Ordner löschen
				$mm->deletedir('gallery/'.$_REQUEST['id']);
				
				//Kommentare und Bewertungen löschen
				if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='gallery' AND mid IN ( ".implode(',',$ccache)." ) )");
				if ( $apx->is_module('ratings') ) $db->query("DELETE FROM ".PRE."_ratings WHERE ( module='gallery' AND mid IN ( ".implode(',',$ccache)." ) )");
				
				//Tags löschen
				$db->query("DELETE FROM ".PRE."_gallery_tags WHERE id='".$_REQUEST['id']."'");
			}
			
			logit('GALLERY_DEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('gallery.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_gallery WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
	}
}



//***************************** Galerie aktivieren *****************************
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
			
			//Elternknoten ebenfalls aktivieren
			$path = $this->cat->getPathTo(array('starttime'), $_REQUEST['id']);
			foreach ( $path AS $res ) {
				if ( !$res['starttime'] ) {
					$db->query("UPDATE ".PRE."_gallery SET starttime='".$starttime."',endtime='".$endtime."' WHERE id='".$res['id']."' LIMIT 1");
					logit('GALLERY_ENABLE','ID #'.$res['id']);
				}
			}
			
			printJSRedirect(get_index('gallery.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_gallery WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('TITLE', compatible_hsc($title));
		$apx->tmpl->assign('STARTTIME',choosetime(1,0,time()));
		$apx->tmpl->assign('ENDTIME',choosetime(2,1));
		tmessageOverlay('enable');
	}
}



//***************************** Galerie deaktivieren *****************************
function disable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			
			//Kindknoten ebenfalls deaktivieren
			$cattree = $this->cat->getChildrenIds($_REQUEST['id']);
			$cattree[] = $_REQUEST['id'];
			
			$db->query("UPDATE ".PRE."_gallery SET starttime='0',endtime='0' WHERE id IN (".implode(',',$cattree).")");
			foreach ( $cattree AS $catid ) {
				logit('GALLERY_DISABLE','ID #'.$catid);
			}
			
			printJSRedirect(get_index('gallery.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_gallery WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('disable',array('ID'=>$_REQUEST['id']));
	}
}



//***************************** Galerien anordnen *****************************
function move() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( !$set['gallery']['subgals'] && $set['gallery']['ordergal']!=3 ) die('moving of galleries is disabled!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		$this->cat->swapNode($_REQUEST['id'], $_REQUEST['direction']);
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.get_index('gallery.show'));
	}
}



////////////////////////////////////////////////////////////////////////////////////////// BILDER

//***************************** Bilder zeigen *****************************
function pshow() {
	global $set,$db,$apx,$html;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	quicklink('gallery.padd','action.php','id='.$_REQUEST['id']);
	
	$orderdef[0]='time';
	$orderdef['time']=array('id','DESC','SORT_ADDTIME');
	$orderdef['caption']=array('caption','ASC','COL_CAPTION');
	$orderdef['hits']=array('hits','DESC','COL_HITS');
	
	$col[]=array('&nbsp;',1,'');
	$col[]=array('&nbsp;',1,'');
	$col[]=array('&nbsp;',1,'');
	$col[]=array('COL_THUMBNAIL',20,'align="center"');
	$col[]=array('COL_CAPTION',70,'class="title"');
	$col[]=array('COL_HITS',10,'align="center"');

	list($title)=$db->first("SELECT title FROM ".PRE."_gallery WHERE ( id='".$_REQUEST['id']."' ) LIMIT 1");
	echo'<h2>'.$apx->lang->get('GALLERY').': '.$title.'</h2>';
	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_gallery_pics WHERE ( galid='".$_REQUEST['id']."')");
	pages('action.php?action=gallery.pshow&amp;id='.$_REQUEST['id'].'&amp;sortby='.$_REQUEST['sortby'],$count);
	
	//Preview-Bild
	list($previewpic)=$db->first("SELECT preview FROM ".PRE."_gallery WHERE ( id='".$_REQUEST['id']."') LIMIT 1");
	
	$data=$db->fetch("SELECT * FROM ".PRE."_gallery_pics WHERE galid='".$_REQUEST['id']."' ".getorder($orderdef).getlimit());
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Aktiv-Anzeige
			if ( $res['active'] ) $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			
			//Vorschau-Bild
			if ( $previewpic==$res['thumbnail'] ) $tabledata[$i]['COL2']='<img src="design/previewicon.gif" alt="'.$apx->lang->get('IS_PREVIEW').'" title="'.$apx->lang->get('IS_PREVIEW').'" />';
			else $tabledata[$i]['COL2']='&nbsp;';
			
			//POTW
			if ( $res['potw'] ) $tabledata[$i]['COL3']='<img src="design/default.gif" alt="'.$apx->lang->get('IS_POTW').'" title="'.$apx->lang->get('IS_POTW').'" />';
			else $tabledata[$i]['COL3']='&nbsp;';
			
			$caption=shorttext(strip_tags($res['caption']),50);
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['COL4']='<a href="../'.getpath('uploads').$res['picture'].'" target="_blank"><img src="../'.getpath('uploads').$res['thumbnail'].'" alt="thumbnail" /></a>';
			$tabledata[$i]['COL5']=iif($caption,$caption,'&nbsp;');
			$tabledata[$i]['COL6']=number_format($res['hits'],0,'','.');
			
			//Optionen
			if ( $apx->user->has_right('gallery.pedit') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('edit.gif', 'gallery.pedit', 'id='.$res['id'].'&gid='.intval($_REQUEST['id']), $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('gallery.pmove') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('move.gif', 'gallery.pmove', 'id='.$res['id'].'&gid='.intval($_REQUEST['id']), $apx->lang->get('MOVE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('gallery.pdel') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'gallery.pdel', 'id='.$res['id'].'&gid='.intval($_REQUEST['id']), $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $res['active'] && $apx->user->has_right('gallery.pdisable') && !$res['potw'] && !$res['preview'] ) $tabledata[$i]['OPTIONS'].=optionHTML('disable.gif', 'gallery.pdisable', 'id='.$res['id'].'&gid='.intval($_REQUEST['id']).'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_DISABLE'));
			elseif ( !$res['active'] && $apx->user->has_right('gallery.penable') ) $tabledata[$i]['OPTIONS'].=optionHTML('enable.gif', 'gallery.penable', 'id='.$res['id'].'&gid='.intval($_REQUEST['id']).'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_ENABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			$tabledata[$i]['OPTIONS'].='<br />';
			
			if ( $res['active'] && !$res['potw'] && $apx->user->has_right('gallery.potw') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('potw.gif', 'gallery.potw', 'id='.$res['id'].'&gid='.intval($_REQUEST['id']), $apx->lang->get('POTW'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $res['active'] && $previewpic!=$res['thumbnail'] && $apx->user->has_right('gallery.preview') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('previewpic.gif', 'gallery.preview', 'id='.$res['id'].'&gid='.intval($_REQUEST['id']), $apx->lang->get('PREVIEW'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Kommentare + Bewertungen
			if ( $apx->is_module('comments') ) {
				list($comments)=$db->first("SELECT count(id) FROM ".PRE."_comments WHERE ( module='gallery' AND mid='".$res['id']."' )");
				if ( $comments && ( $apx->is_module('comments') && $set['gallery']['coms'] ) && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=gallery&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			if ( $apx->is_module('ratings') ) {
				list($ratings)=$db->first("SELECT count(id) FROM ".PRE."_ratings WHERE ( module='gallery' AND mid='".$res['id']."' )");
				if ( $ratings && ( $apx->is_module('ratings') && $set['gallery']['ratings'] ) && $res['allowrating'] && $apx->user->has_right('ratings.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('ratings.gif', 'ratings.show', 'module=gallery&mid='.$res['id'], $apx->lang->get('RATINGS').' ('.$ratings.')');
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
		
		}
	}
	
	$multiactions = array();
	if ( $apx->user->has_right('gallery.pmove') ) $multiactions[] = array($apx->lang->get('MOVE'), 'action.php?action=gallery.pmove&gid='.intval($_REQUEST['id']), true);
	if ( $apx->user->has_right('gallery.pdel') ) $multiactions[] = array($apx->lang->get('CORE_DEL'), 'action.php?action=gallery.pdel&gid='.intval($_REQUEST['id']), false);
	if ( $apx->user->has_right('gallery.penable') ) $multiactions[] = array($apx->lang->get('CORE_ENABLE'), 'action.php?action=gallery.penable&gid='.intval($_REQUEST['id']), false);
	if ( $apx->user->has_right('gallery.pdisable') ) $multiactions[] = array($apx->lang->get('CORE_DISABLE'), 'action.php?action=gallery.pdisable&gid='.intval($_REQUEST['id']), false);
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col, $multiactions);
	
	orderstr($orderdef,'action.php?action=gallery.pshow&amp;id='.$_REQUEST['id']);
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Bilder anfügen *****************************
function padd() {
	global $set,$db,$apx,$html;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	@set_time_limit(600);
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		else {
			$files=array();
			require_once(BASEDIR.'lib/class.mediamanager.php');
			$mm=new mediamanager;
			
			//ZIP
			if ( $_REQUEST['what']=='zip' && $_FILES['zip']['tmp_name'] ) {
				$mm->uploadfile($_FILES['zip'],'gallery/uploads',$mm->getfile($_FILES['zip']['tmp_name']));
				$zipfile=zip_open(BASEDIR.getpath('uploads').'gallery/uploads/'.$mm->getfile($_FILES['zip']['tmp_name']));
				
				while ( $zipentry=zip_read($zipfile) ) {
					if ( zip_entry_open($zipfile,$zipentry,'r') ) {
						if ( substr(zip_entry_name($zipentry),-1)=='/' ) continue;
						
						$content=zip_entry_read($zipentry,zip_entry_filesize($zipentry));
						$zipname=str_replace('/','%1%',zip_entry_name($zipentry));
						$outfilepath='gallery/uploads/'.$zipname;
						zip_entry_close($zipentry);
						
						//Datei schreiben
						$outfile=fopen(BASEDIR.getpath('uploads').$outfilepath,'w');
						fwrite($outfile,$content);
						fclose($outfile);
						
						$ext=strtolower($mm->getext($outfilepath));
						if ( $ext=='gif' ) $ext='jpg';
						
						$files[]=array(
							'ext' => $ext,
							'source' => $outfilepath,
							'watermark' => $_POST['watermark'],
							'noresize' => $_POST['noresize'],
							'allowcoms' => $_POST['allowcoms'],
							'allowrating' => $_POST['allowrating'],
							'caption' => $_POST['caption']
						);
					}
				}
				
				zip_close($zipfile);
				$mm->deletefile('gallery/uploads/'.$mm->getfile($_FILES['zip']['tmp_name']));
			}
			
			//UPLOAD-ORDNER
			elseif ( $_REQUEST['what']=='ftp' ) {
				if ( !is_array($_POST['ftp']) ) $_POST['ftp']=array();
				
				require_once(BASEDIR.'lib/class.mediamanager.php');
				$mm=new mediamanager;
				
				foreach ( $_POST['ftp'] AS $key => $file ) {
					$file=$mm->securefile($file);
					$ext=strtolower($mm->getext($file));
					if ( $ext=='gif' ) $ext='jpg';
					
					$files[]=array(
						'ext' => $ext,
						'source' => 'gallery/uploads/'.$file,
						'watermark' => $_POST['watermark'.$key],
						'noresize' => $_POST['noresize'.$key],
						'allowcoms' => $_POST['allowcoms'.$key],
						'allowrating' => $_POST['allowrating'.$key],
						'caption' => $_POST['caption'.$key]
					);
				}
			}
			
			//EINZELNE BILDER
			else {
				for ( $i=1; $i<=$set['gallery']['addpics']; $i++ ) {
					if ( !$_FILES['upload'.$i]['tmp_name'] ) continue;
					
					//Erfolgreichen Upload prüfen
					if ( !$mm->uploadfile($_FILES['upload'.$i],'gallery/uploads',$mm->getfile($_FILES['upload'.$i]['tmp_name'])) ) continue;
					
					$ext=strtolower($mm->getext($_FILES['upload'.$i]['name']));
					if ( $ext=='gif' ) $ext='jpg';
					
					$files[]=array(
						'ext' => $ext,
						'source' => 'gallery/uploads/'.$mm->getfile($_FILES['upload'.$i]['tmp_name']),
						'watermark' => $_POST['watermark'.$i],
						'noresize' => $_POST['noresize'.$i],
						'allowcoms' => $_POST['allowcoms'.$i],
						'allowrating' => $_POST['allowrating'.$i],
						'caption' => $_POST['caption'.$i]
					);
				}
			}
			
			$this->process_files($files);
			
			//Gallery Updatetime
			$this->setGalleryUpdatetime($_REQUEST['id']);
			
			//Weitere Bilder anfügen
			if ( $_POST['addnext'] ) {
				printJSRedirect('action.php?action=gallery.padd&id='.$_REQUEST['id'].'&updateparent='.$_REQUEST['updateparent']);
			}
			else {
				if ( $_REQUEST['updateparent'] ) {
					printJSUpdateObject($_REQUEST['updateparent'], get_gallery_list($_REQUEST['id']));
				}
				else {
					printJSRedirect('action.php?action=gallery.pshow&id='.$_REQUEST['id']);
				}
			}
		}
	}
	else {
		
		//Layer
		$layerdef[]=array('LAYER_UPLOAD','action.php?action=gallery.padd&amp;id='.$_REQUEST['id'].'&amp;updateparent='.$_REQUEST['updateparent'],!$_REQUEST['what']);
		$layerdef[]=array('LAYER_ZIP','action.php?action=gallery.padd&amp;id='.$_REQUEST['id'].'&amp;what=zip&amp;updateparent='.$_REQUEST['updateparent'],$_REQUEST['what']=='zip');
		$layerdef[]=array('LAYER_FTP','action.php?action=gallery.padd&amp;id='.$_REQUEST['id'].'&amp;what=ftp&amp;updateparent='.$_REQUEST['updateparent'],$_REQUEST['what']=='ftp');
		if ( !function_exists('zip_open') ) unset($layerdef[1]);
		
		$html->layer_header($layerdef);
		
		//ZIP
		if ( $_REQUEST['what']=='zip' ) {
			$apx->tmpl->assign('ID',$_REQUEST['id']);
			$apx->tmpl->assign('SET_OPTIONS',( $apx->is_module('comments') && $set['gallery']['coms'] ) || ( $apx->is_module('ratings') && $set['gallery']['ratings'] ) || $set['gallery']['watermark'] || ( $set['gallery']['picwidth'] && $set['gallery']['picheight'] ));
			$apx->tmpl->assign('SET_COMS',( $apx->is_module('comments') && $set['gallery']['coms'] ));
			$apx->tmpl->assign('SET_RATING',( $apx->is_module('ratings') && $set['gallery']['ratings'] ));
			$apx->tmpl->assign('SET_WATERMARK',iif($set['gallery']['watermark'],1,0));
			$apx->tmpl->assign('SET_NORESIZE',iif($set['gallery']['picwidth'] && $set['gallery']['picheight'],1,0));
			$apx->tmpl->assign('UPDATEPARENT',(int)$_REQUEST['updateparent']);
			$apx->tmpl->parse('padd_zip');
		}
		
		//UPLOAD-ORDNER
		elseif ( $_REQUEST['what']=='ftp' ) {
			require_once(BASEDIR.'lib/class.mediamanager.php');
			$mm=new mediamanager;
			$extensions=array('jpg','jpeg','jpe','png','gif');
			
			$files=array();
			$dirs=array();
			if ( is_dir(BASEDIR.getpath('uploads').'gallery/uploads/'.iif($_REQUEST['dir'],$_REQUEST['dir'].'/')) ) {
				$dir=opendir(BASEDIR.getpath('uploads').'gallery/uploads/'.iif($_REQUEST['dir'],$_REQUEST['dir'].'/'));
				while ( $file=readdir($dir) ) {
					if ( $file=='.' || $file=='..' ) continue;
					
					//Ordner
					if ( is_dir(BASEDIR.getpath('uploads').'gallery/uploads/'.iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file) ) {
						$dirs[]=$file;
						continue;
					}
				
					//Datei
					if ( !in_array(strtolower($mm->getext($file)),$extensions) ) continue;
					$files[]=$file;
				}
				closedir($dir);
			}
			
			sort($files);
			sort($dirs);
			
			//Ordner auflisten
			foreach ( $dirs AS $dir ) {
				++$i;
				$subdir[$i]['NAME']=$dir;
				$subdir[$i]['LINK']='action.php?action=gallery.padd&amp;id='.$_REQUEST['id'].'&amp;what=ftp&amp;dir='.iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$dir;
			}
			
			//Dateien auflisten
			foreach ( $files AS $file ) {
				++$i;
				$upload[$i]['FILE']=$file;
				$upload[$i]['FILEID']=iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file;
				$upload[$i]['LINK']=HTTPDIR.getpath('uploads').'gallery/uploads/'.iif($_REQUEST['dir'],$_REQUEST['dir'].'/').$file;
			}
			
			//Pfad erstellen
			$pp=explode('/',$_REQUEST['dir']);
			if ( $_REQUEST['dir'] && count($pp) ) {
				foreach ( $pp AS $dirname ) {
					++$i;
					$path.=iif($path,'/').$dirname;
					$pathdata[$i]['NAME']=$dirname;
					$pathdata[$i]['LINK']='action.php?action=gallery.padd&amp;id='.$_REQUEST['id'].'&amp;what=ftp&amp;dir='.$path;
				}
			}
			
			$apx->tmpl->assign('ID',$_REQUEST['id']);
			$apx->tmpl->assign('FTP',$upload);
			$apx->tmpl->assign('DIR',$subdir);
			$apx->tmpl->assign('PATH',$pathdata);
			
			$apx->tmpl->assign('SET_OPTIONS',( $apx->is_module('comments') && $set['gallery']['coms'] ) || ( $apx->is_module('ratings') && $set['gallery']['ratings'] ) || $set['gallery']['watermark'] || ( $set['gallery']['picwidth'] && $set['gallery']['picheight'] ));
			$apx->tmpl->assign('SET_COMS',( $apx->is_module('comments') && $set['gallery']['coms'] ));
			$apx->tmpl->assign('SET_RATING',( $apx->is_module('ratings') && $set['gallery']['ratings'] ));
			$apx->tmpl->assign('SET_WATERMARK',iif($set['gallery']['watermark'],1,0));
			$apx->tmpl->assign('SET_NORESIZE',iif($set['gallery']['picwidth'] && $set['gallery']['picheight'],1,0));
			
			$apx->tmpl->assign('UPDATEPARENT',(int)$_REQUEST['updateparent']);
			$apx->tmpl->parse('padd_ftp');
		}
		
		//EINZELNE BILDER
		else {
			for ( $i=1; $i<=$set['gallery']['addpics']; $i++ ) {
				$upload[$i]['ASD']=1;
			}
			
			$apx->tmpl->assign('ID',$_REQUEST['id']);
			$apx->tmpl->assign('UPLOAD',$upload);
			
			$apx->tmpl->assign('SET_COMS',( $apx->is_module('comments') && $set['gallery']['coms'] ));
			$apx->tmpl->assign('SET_RATING',( $apx->is_module('ratings') && $set['gallery']['ratings'] ));
			$apx->tmpl->assign('SET_WATERMARK',iif($set['gallery']['watermark'],1,0));
			$apx->tmpl->assign('SET_NORESIZE',iif($set['gallery']['picwidth'] && $set['gallery']['picheight'],1,0));
			
			$apx->tmpl->assign('UPDATEPARENT',(int)$_REQUEST['updateparent']);
			$apx->tmpl->parse('padd_upload');
		}
		
		//Layer Ende
		$html->layer_footer();
	}
}


//Bilder verarbeiten
function process_files($files) {
	global $set,$db,$apx;
	
	$tblinfo=$db->first("SHOW TABLE STATUS LIKE '".PRE."_gallery_pics'");
	$now=time(); //Einheitliche Upload-Zeit, auch wenns nicht stimmt
	
	require_once(BASEDIR.'lib/class.image.php');
	$img=new image;
	$mm=new mediamanager;
	
	foreach ( $files AS $file ) {
		++$i;
		
		$newname='pic'.'-'.($tblinfo['Auto_increment']+$i-1).'.'.$file['ext'];
		$newfile='gallery/'.$_REQUEST['id'].'/'.$newname;
		$thumbname='pic'.'-'.($tblinfo['Auto_increment']+$i-1).'-thumb.'.$file['ext'];
		$thumbfile='gallery/'.$_REQUEST['id'].'/'.$thumbname;
		
		//Bild einlesen
		list($picture,$picturetype)=$img->getimage($file['source']);
		
		//////// THUMBNAIL
		$thumbnail=$img->resize($picture,$set['gallery']['thumbwidth'],$set['gallery']['thumbheight'],$set['gallery']['quality_resize'],$set['gallery']['thumb_fit']);
		$img->saveimage($thumbnail,$picturetype,$thumbfile);
		
		
		//////// BILD
		
		//Skalieren
		if ( $picture!==false && !$file['noresize'] && $set['gallery']['picwidth'] && $set['gallery']['picheight'] ) {
			$scaled=$img->resize(
				$picture,
				$set['gallery']['picwidth'],
				$set['gallery']['picheight'],
				$set['gallery']['quality_resize'],
				0
			);
			
			if ( $scaled!=$picture ) imagedestroy($picture);
			$picture=$scaled;
		}
		
		//Wasserzeichen einfügen
		if ( $picture!==false && $set['gallery']['watermark'] && $file['watermark'] ) {
			$watermarked=$img->watermark(
				$picture,
				$set['gallery']['watermark'],
				$set['gallery']['watermark_position'],
				$set['gallery']['watermark_transp']
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
		if ( $_POST['what']!='ftp' || $_POST['delpics'] ) $mm->deletefile($file['source']);
		
		$db->query("INSERT INTO ".PRE."_gallery_pics VALUES (0,'".$_REQUEST['id']."','".$thumbfile."','".$newfile."','".addslashes($file['caption'])."',0,'".$now."','".(int)$file['allowcoms']."','".(int)$file['allowrating']."','1','0')");
		logit('GALLERY_PADD','ID #'.$db->insert_id());
	}
	
	$this->files=$i;
}



//***************************** Bild bearbeiten *****************************
function pedit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	$_REQUEST['gid']=(int)$_REQUEST['gid'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( !$_REQUEST['gid'] ) die('missing gallery ID!');

	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->dupdate(PRE.'_gallery_pics','caption,allowcoms,allowrating',"WHERE ( id='".$_REQUEST['id']."' AND galid='".$_REQUEST['gid']."' )  LIMIT 1");
			logit('GALLERY_PEDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('gallery.pshow'));
		}
	}
	else {
		$res=$db->first("SELECT caption,allowcoms,allowrating FROM ".PRE."_gallery_pics WHERE ( id='".$_REQUEST['id']."' AND galid='".$_REQUEST['gid']."' )  LIMIT 1");
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('GID',$_REQUEST['gid']);
		$apx->tmpl->assign('CAPTION',compatible_hsc($res['caption']));
		$apx->tmpl->assign('ALLOWCOMS',$res['allowcoms']);
		$apx->tmpl->assign('ALLOWRATING',$res['allowrating']);
		tmessageOverlay('pedit');
	}
}


//***************************** Bild verschieben *****************************
function pmove() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		$_REQUEST['gid']=(int)$_REQUEST['gid'];
		if ( !$_REQUEST['gid'] ) die('missing gallery ID!');
		
		$ids = array_map('intval', $_REQUEST['multiid']);
		if ( !count($ids) ) {
			printJSRedirect(get_index('gallery.pshow'));
			return;
		}
	
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			elseif ( $ids ) {
				require(BASEDIR.'lib/class.mediamanager.php');
				$mm=new mediamanager;
				
				$data=$db->fetch("SELECT id,thumbnail,picture FROM ".PRE."_gallery_pics WHERE ( id IN (".implode(',',$ids).") AND galid='".$_REQUEST['gid']."' )");
				if ( count($data) ) {
					foreach ( $data AS $res ) {
						list($theid,$thumbnail,$picture)=$res;
						
						$new_thumbnail='gallery/'.intval($_POST['newgal']).'/'.$mm->getfile($thumbnail);
						$new_picture='gallery/'.intval($_POST['newgal']).'/'.$mm->getfile($picture);
						$mm->movefile($thumbnail,$new_thumbnail);
						$mm->movefile($picture,$new_picture);
						
						$db->query("UPDATE ".PRE."_gallery_pics SET galid='".intval($_POST['newgal'])."',thumbnail='".addslashes($new_thumbnail)."',picture='".addslashes($new_picture)."' WHERE ( id='".$theid."' AND galid='".$_REQUEST['gid']."' ) LIMIT 1");
						logit('GALLERY_PMOVE','ID #'.$theid);
						
						//Gallery Updatetime
						$this->setGalleryUpdatetime($_REQUEST['gid']);
						$this->setGalleryUpdatetime($_POST['newgal']);
					}
				}
			}
			
			printJSRedirect(get_index('gallery.pshow'));
			return;
		}
		
		//Galerien auflisten
		if ( $set['gallery']['subgals'] ) $data = $this->cat->getTree(array('*'), null, section_filter(false, 'secid'));
		else $data=$db->fetch("SELECT id,title FROM ".PRE."_gallery ORDER BY title ASC");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$space='';
				if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',$res['level']-1);
				$gallist.='<option value="'.$res['id'].'"'.iif($res['id']==$_REQUEST['gid'],' selected="selected"').'>'.$space.replace($res['title']).'</option>';
			}
		}
		
		$idsdata = array();
		foreach ( $ids AS $id ) {
			$idsdata[] = array(
				'ID' => $id
			);
		}
		
		$apx->tmpl->assign('MULTIID',$idsdata);
		$apx->tmpl->assign('GID',$_REQUEST['gid']);
		$apx->tmpl->assign('GALLIST',$gallist);
		tmessageOverlay('multi_pmove');
	}
	
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		$_REQUEST['gid']=(int)$_REQUEST['gid'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		if ( !$_REQUEST['gid'] ) die('missing gallery ID!');
		
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				
				//Bild verschieben
				if ( $_POST['newgal']!=$_REQUEST['gid'] ) {
					list($thumbnail,$picture)=$db->first("SELECT thumbnail,picture FROM ".PRE."_gallery_pics WHERE ( id='".$_REQUEST['id']."' AND galid='".$_REQUEST['gid']."' ) LIMIT 1");
					
					require(BASEDIR.'lib/class.mediamanager.php');
					$mm=new mediamanager;
					
					$new_thumbnail='gallery/'.intval($_POST['newgal']).'/'.$mm->getfile($thumbnail);
					$new_picture='gallery/'.intval($_POST['newgal']).'/'.$mm->getfile($picture);
					$mm->movefile($thumbnail,$new_thumbnail);
					$mm->movefile($picture,$new_picture);
					
					//Preview-Bild-Zuweisung löschen (falls vorhanden)
					$db->query("UPDATE ".PRE."_gallery SET preview='' WHERE preview='".addslashes($thumbnail)."'");
					
					$db->query("UPDATE ".PRE."_gallery_pics SET galid='".intval($_POST['newgal'])."',thumbnail='".addslashes($new_thumbnail)."',picture='".addslashes($new_picture)."' WHERE ( id='".$_REQUEST['id']."' AND galid='".$_REQUEST['gid']."' ) LIMIT 1");
					logit('GALLERY_PMOVE','ID #'.$_REQUEST['id']);
					
					//Gallery Updatetime
					$this->setGalleryUpdatetime($_REQUEST['gid']);
					$this->setGalleryUpdatetime($_POST['newgal']);
				}
				
				printJSRedirect('action.php?action=gallery.pshow&id='.$_POST['newgal']);
			}
		}
		else {
			//Galerien auflisten
			if ( $set['gallery']['subgals'] ) $data = $this->cat->getTree(array('*'), null, section_filter(false, 'secid'));
			else $data=$db->fetch("SELECT id,title FROM ".PRE."_gallery ORDER BY title ASC");
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					$space='';
					if ( $res['level'] ) $space=str_repeat('&nbsp;&nbsp;',$res['level']-1);
					$gallist.='<option value="'.$res['id'].'"'.iif($res['id']==$_REQUEST['gid'],' selected="selected"').'>'.$space.replace($res['title']).'</option>';
				}
			}
			
			$apx->tmpl->assign('ID',$_REQUEST['id']);
			$apx->tmpl->assign('GID',$_REQUEST['gid']);
			$apx->tmpl->assign('GALLIST',$gallist);
			tmessageOverlay('pmove');
		}
	}
}



//***************************** Bild löschen *****************************
function pdel() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('gallery.pshow'));
				return;
			}
			
			if ( count($cache) ) {
				$data=$db->fetch("SELECT galid,thumbnail,picture FROM ".PRE."_gallery_pics WHERE ( id IN ( ".implode(',',$cache).") )");
				$db->query("DELETE FROM ".PRE."_gallery_pics WHERE ( id IN ( ".implode(',',$cache).") )");
				
				require(BASEDIR.'lib/class.mediamanager.php');
				$mm=new mediamanager;
				$galid = null;
				
				//Bilder löschen
				if ( count($data) ) {
					foreach ( $data AS $res ) {
						$galid = $res['galid'];
						$mm->deletefile($res['thumbnail']);
						$mm->deletefile($res['picture']);
					}
				}
				
				//Kommentare und Bewertungen löschen
				if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='gallery' AND mid IN ( ".implode(',',$cache)." ) )");
				if ( $apx->is_module('ratings') ) $db->query("DELETE FROM ".PRE."_ratings WHERE ( module='gallery' AND mid IN ( ".implode(',',$cache)." ) )");
				
				foreach ( $cache AS $id ) logit('GALLERY_PDEL','ID #'.$id);
				
				//Galerie-Updatetime
				if ( $galid ) {
					$this->setGalleryUpdatetime($galid);
				}
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('gallery.pshow'));
		}
	}
	
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		$_REQUEST['gid']=(int)$_REQUEST['gid'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		if ( !$_REQUEST['gid'] ) die('missing gallery ID!');
	
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				$res=$db->first("SELECT galid,thumbnail,picture FROM ".PRE."_gallery_pics WHERE ( id='".$_REQUEST['id']."' AND galid='".$_REQUEST['gid']."' ) LIMIT 1");
				$db->query("DELETE FROM ".PRE."_gallery_pics WHERE ( id='".$_REQUEST['id']."' AND galid='".$_REQUEST['gid']."' ) LIMIT 1");
				
				//Nur löschen, wenn der Muttereintrag wirklich gelöscht wurde!
				if ( $db->affected_rows() ) {
					//Bilder löschen
					require(BASEDIR.'lib/class.mediamanager.php');
					$mm=new mediamanager;
					$mm->deletefile($res['thumbnail']);
					$mm->deletefile($res['picture']);
					
					//Kommentare und Bewertungen löschen
					if ( $apx->is_module('comments') ) $db->query("DELETE FROM ".PRE."_comments WHERE ( module='gallery' AND mid='".$_REQUEST['id']."' )");
					if ( $apx->is_module('ratings') ) $db->query("DELETE FROM ".PRE."_ratings WHERE ( module='gallery' AND mid='".$_REQUEST['id']."' )");
					
					//Preview-Bild-Zuweisung löschen (falls vorhanden)
					$db->query("UPDATE ".PRE."_gallery SET preview='' WHERE preview='".addslashes($res['thumbnail'])."'");
					
					//Galerie-Updatetime
					$this->setGalleryUpdatetime($_REQUEST['gid']);
				}
				
				logit('GALLERY_PDEL','ID #'.$_REQUEST['id']);
				printJSRedirect(get_index('gallery.pshow'));
			}
		}
		else {
			$input['ID']=$_REQUEST['id'];
			$input['GID']=$_REQUEST['gid'];
			tmessageOverlay('pdel',$input);
		}
	}
}



//***************************** Bild aktivieren *****************************
function penable() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('gallery.pshow'));
				return;
			}
			
			if ( count($cache) ) {
				$db->query("UPDATE ".PRE."_gallery_pics SET active='1' WHERE ( id IN (".implode(",",$cache).") )");
				foreach ( $cache AS $id ) logit('GALLERY_PENABLE','ID #'.$id);
				
				//Galerie-Updatetime
				list($galid) = $db->first("SELECT galid FROM ".PRE."_gallery_pics WHERE id IN (".implode(",",$cache).") LIMIT 1");
				if ( $galid ) {
					$this->setGalleryUpdatetime($galid);
				}
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('gallery.pshow'));
		}
	}
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		$_REQUEST['gid']=(int)$_REQUEST['gid'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		if ( !$_REQUEST['gid'] ) die('missing gallery ID!');
		
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("UPDATE ".PRE."_gallery_pics SET active='1' WHERE ( id='".$_REQUEST['id']."' AND galid='".$_REQUEST['gid']."' ) LIMIT 1");
			logit('GALLERY_PENABLE','ID #'.$_REQUEST['id']);
			
			//Galerie-Updatetime
			$this->setGalleryUpdatetime($_REQUEST['gid']);
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('gallery.pshow'));
		}
	}
}



//***************************** Bild deaktivieren *****************************
function pdisable() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('gallery.pshow'));
				return;
			}
			
			if ( count($cache) ) {
				$db->query("UPDATE ".PRE."_gallery_pics SET active='0' WHERE ( id IN (".implode(",",$cache).") )");
				foreach ( $cache AS $id ) logit('GALLERY_PDISABLE','ID #'.$id);
				
				//Galerie-Updatetime
				list($galid) = $db->first("SELECT galid FROM ".PRE."_gallery_pics WHERE id IN (".implode(",",$cache).") LIMIT 1");
				if ( $galid ) {
					$this->setGalleryUpdatetime($galid);
				}
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('gallery.pshow'));
		}
	}
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		$_REQUEST['gid']=(int)$_REQUEST['gid'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		if ( !$_REQUEST['gid'] ) die('missing gallery ID!');
		
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("UPDATE ".PRE."_gallery_pics SET active='0' WHERE ( id='".$_REQUEST['id']."' AND galid='".$_REQUEST['gid']."' ) LIMIT 1");
			logit('GALLERY_PDISABLE','ID #'.$_REQUEST['id']);
			
			//Galerie-Updatetime
			$this->setGalleryUpdatetime($_REQUEST['gid']);
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('gallery.pshow'));
		}
	}
}



//***************************** POTW setzten *****************************
function potw() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	$_REQUEST['gid']=(int)$_REQUEST['gid'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( !$_REQUEST['gid'] ) die('missing gallery ID!');
	
	//AKTIV-CHECK
	list($galid,$active1)=$db->first("SELECT galid,active FROM ".PRE."_gallery_pics WHERE ( id='".$_REQUEST['id']."' AND galid='".$_REQUEST['gid']."' ) LIMIT 1");
	list($active2)=$db->first("SELECT starttime FROM ".PRE."_gallery WHERE ( id='".$galid."' ) LIMIT 1");
	if ( !$active1 || !$active2 ) {
		messageOverlay($apx->lang->get('MSG_NOTACTIVE'));
		return;
	}
	
	if ( $_POST['send'] ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("UPDATE ".PRE."_gallery_pics SET potw='0'");
			$db->query("UPDATE ".PRE."_gallery_pics SET potw='1' WHERE ( id='".$_REQUEST['id']."' AND galid='".$_REQUEST['gid']."' ) LIMIT 1");
			logit('GALLERY_POTW','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('gallery.pshow'));
		}
	}
	else {
		$input['ID']=$_REQUEST['id'];
		$input['GID']=$_REQUEST['gid'];
		tmessageOverlay('potw', $input);
	}
}



//***************************** Vorschau setzten *****************************
function preview() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	$_REQUEST['gid']=(int)$_REQUEST['gid'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( !$_REQUEST['gid'] ) die('missing gallery ID!');
	
	//AKTIV-CHECK
	list($thumb,$active)=$db->first("SELECT thumbnail,active FROM ".PRE."_gallery_pics WHERE ( id='".$_REQUEST['id']."' AND galid='".$_REQUEST['gid']."' ) LIMIT 1");
	if ( !$active ) {
		message($apx->lang->get('MSG_NOTACTIVE'));
		return;
	}
	
	if ( $_POST['send'] ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("UPDATE ".PRE."_gallery SET preview='".$thumb."' WHERE id='".$_REQUEST['gid']."' LIMIT 1");
			logit('GALLERY_PREVIEW','ID #'.$_REQUEST['galid'].' -&gt; '.$_REQUEST['id']);
			printJSRedirect(get_index('gallery.pshow'));
		}
	}
	else {
		$input['ID']=$_REQUEST['id'];
		$input['GID']=$_REQUEST['gid'];
		tmessageOverlay('preview', $input);
	}
}


} //END CLASS


?>