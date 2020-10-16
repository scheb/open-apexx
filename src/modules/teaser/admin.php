<?php 

# TEASER CLASS
# ================

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class action {

//***************************** Teasers zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	quicklink('teaser.add');
	
	//Gruppen-Auswahl
	$_REQUEST['gid']=(int)$_REQUEST['gid'];
	$groupdata = array();
	foreach ( $set['teaser']['groups'] AS $id => $title ) {
		$groupdata[] = array(
			'ID' => $id,
			'TITLE' => compatible_hsc($title),
			'SELECTED' => $_REQUEST['gid']==$id
		);
	}
	$apx->tmpl->assign('GROUP', $groupdata);
	$apx->tmpl->parse('show_choose');
	
	//DnD-Hinweis
	if ( $set['teaser']['orderby']==1 && $apx->user->has_right('articles.edit') ) {
		echo '<p class="hint">'.$apx->lang->get('USEDND').'</p>';
	}
	
	if ( $set['teaser']['orderby']!=1 ) {
		$orderdef[0]='title';
		$orderdef['title']=array('title','ASC','COL_TITLE');
		$orderdef['hits']=array('hits','ASC','COL_HITS');
	}
	
	$col[]=array('&nbsp;',1,'');
	$col[]=array('COL_TITLE',50,'class="title"');
	$col[]=array('COL_IMAGE',40,'align="center"');
	$col[]=array('COL_HITS',10,'align="center"');
	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_teaser WHERE 1 ".iif($_REQUEST['gid'], "AND `group`=".$_REQUEST['gid']));
	pages('action.php?action=teaser.show&amp;sortby='.$_REQUEST['sortby'].iif($_REQUEST['gid'], '&amp;gid='.$_REQUEST['gid']),$count);
	
	$data=$db->fetch("SELECT id,title,link,image,hits,starttime,endtime FROM ".PRE."_teaser WHERE 1 ".iif($_REQUEST['gid'], "AND `group`=".$_REQUEST['gid']).section_filter()." ".iif(is_array($orderdef),getorder($orderdef)," ORDER BY ord ASC").getlimit() );
	$count=count($data);
	if ( $count ) {
		foreach ( $data AS $res ) {
			++$i;
			
			$size = @getimagesize(BASEDIR.getpath('uploads').$res['image']);
			if ( $size[0] && $size[0]>300 ) {
				$imageWidth = 300;
			}
			
			if ( !$res['starttime'] ) $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['endtime']<time() ) $tabledata[$i]['COL1']='<img src="design/greendotcross.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['starttime']>time() ) $tabledata[$i]['COL1']='<img src="design/greendotwait.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			
			$tabledata[$i]['COL2']='<a href="../misc.php?action=redirect&amp;url='.urlencode($res['link']).'" target="_blank">'.replace($res['title']).'</a>';
			$tabledata[$i]['COL3']=iif($res['image'],'<img src="../'.getpath('uploads').$res['image'].'" width="'.$imageWidth.'" alt="" />','&nbsp;');
			$tabledata[$i]['COL4']=number_format($res['hits'],0,'','.');
			$tabledata[$i]['COL5']=number_format($res['hits'],0,'','.');
			$tabledata[$i]['ID'] = 'node:'.$res['id'];
			
			//Optionen
			if ( $apx->user->has_right('teaser.edit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'teaser.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('teaser.del') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'teaser.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( !$res['starttime'] || $res['endtime']<time() ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('enable.gif', 'teaser.enable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
			elseif ( $res['starttime'] && $apx->user->has_right('teaser.disable') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('disable.gif', 'teaser.disable', 'id='.$res['id'], $apx->lang->get('CORE_DISABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	
	if ( $set['teaser']['orderby']==1 && $apx->user->has_right('articles.edit') ) {
		echo '<div class="listview" id="list">';
		$html->table($col);
		echo '</div>';
		$apx->tmpl->parse('show_js');
	}
	else {
		$html->table($col);
	}
	
	orderstr($orderdef,'action.php?action=teaser.show'.iif($_REQUEST['gid'], '&amp;gid='.$_REQUEST['gid']));
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Teaser hinzufügen *****************************
function add() {
	global $set,$db,$apx;
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send']==1 ) {
		require(BASEDIR.'lib/class.mediamanager.php');
		$mm=new mediamanager;
		$ext=$mm->getext($_FILES['image']['name']);
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] /*|| !$_POST['text']*/ || !$_POST['link'] ) infoNotComplete();
		elseif ( $_FILES['image']['tmp_name'] && !in_array($ext,array('GIF','JPG','JPE','JPEG','PNG')) ) info($apx->lang->get('INFO_NOIMAGE')); 
		else {
			
			//Image
			if ( $_FILES['image']['tmp_name'] ) {
				$tblinfo=$db->first("SHOW TABLE STATUS LIKE '".PRE."_teaser'");
				$newfile='teaser-'.$tblinfo['Auto_increment'].'.'.strtolower($ext);
				$mm->uploadfile($_FILES['image'],'teaser',$newfile);
				$_POST['image']='teaser/'.$newfile;
			}
			
			//Ord
			list($ord)=$db->first("SELECT max(ord) FROM ".PRE."_teaser");
			$_POST['ord']=$ord+1;
			$_POST['addtime'] = time();
			$_POST['secid']=serialize_section($_POST['secid']);
			
			//Veröffentlichung: JETZT
			$addfields = '';
			if ( $_POST['pubnow'] && $apx->user->has_right('teaser.enable') ) {
				$_POST['starttime']=time();
				$_POST['endtime']=3000000000;
				$addfields.=',starttime,endtime';
			}
			
			$db->dinsert(PRE.'_teaser','secid,group,title,text,image,link,ord,addtime'.$addfields);
			logit('TEASER_ADD','ID #'.$db->insert_id());
			printJSRedirect('action.php?action=teaser.show');
		}
	}
	else {
		
		//Teasergruppen auflisten
		$grouplist='';
		foreach ( $set['teaser']['groups'] AS $id => $title ) {
			$grouplist.='<option value="'.$id.'"'.iif($id==$_POST['group'],' selected="selected"').'>'.replace($title).'</option>';
		}
		
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('GROUPS',$grouplist);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('LINK',compatible_hsc($_POST['link']));
		$apx->tmpl->assign('PUBNOW',iif($apx->user->has_right('teaser.enable'),(int)$_POST['pubnow'],'off'));
		
		$apx->tmpl->parse('add');
	}
}



//***************************** Teaser bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	$info=$db->first("SELECT secid,`group`,title,text,image,link,starttime,endtime FROM ".PRE."_teaser WHERE id='".intval($_REQUEST['id'])."' LIMIT 1");
	
	if ( $_POST['send']==1 ) {
		require(BASEDIR.'lib/class.mediamanager.php');
		$mm=new mediamanager;
		$ext=$mm->getext($_FILES['image']['name']);
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] /*|| !$_POST['text']*/ || !$_POST['link'] ) infoNotComplete();
		elseif ( $_FILES['image']['tmp_name'] && !in_array($ext,array('GIF','JPG','JPE','JPEG','PNG')) ) info($apx->lang->get('INFO_NOIMAGE'));
		else {
			
			//Bild aktualisieren
			if ( $_FILES['image']['tmp_name'] ) {
				list($oldpic)=$db->first("SELECT image FROM ".PRE."_teaser WHERE id='".$_REQUEST['id']."'  LIMIT 1");
				if ( $oldpic ) $mm->deletefile($oldpic);
				
				$newfile='teaser-'.intval($_REQUEST['id']).'.'.strtolower($ext);
				$mm->uploadfile($_FILES['image'],'teaser',$newfile);
				$_POST['image']='teaser/'.$newfile;
			}
			elseif ( $_POST['delimage'] ) {
				list($oldpic)=$db->first("SELECT image FROM ".PRE."_teaser WHERE id='".$_REQUEST['id']."' LIMIT 1");
				$mm->deletefile($oldpic);
				$_POST['image']='';
			}
			
			$_POST['secid']=serialize_section($_POST['secid']);
			
			//Veröffentlichung
			$addfields = '';
			if ( $apx->user->has_right('teaser.enable') && isset($_POST['t_day_1']) ) {
				$_POST['starttime']=maketime(1);
				$_POST['endtime']=maketime(2);
				if ( $_POST['starttime'] ) {
					if ( !$_POST['endtime'] || $_POST['endtime']<=$_POST['starttime'] ) $_POST['endtime']=3000000000;
					$addfields=',starttime,endtime';
				}
			}
			
			$db->dupdate(PRE.'_teaser','secid,group,title,text,link'.iif(isset($_POST['image']),',image').$addfields,"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('TEASER_EDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('teaser.show'));
		}
	}
	else {
		$_POST['group']=$info['group'];
		$_POST['title']=$info['title'];
		$_POST['text']=$info['text'];
		$_POST['link']=$info['link'];
		$_POST['starttime']=$info['starttime'];
		$_POST['endtime']=$info['endtime'];
		$_POST['secid']=unserialize_section($info['secid']);
		
		$imageWidth = '';
		if ( $info['image'] ) {
			$size = @getimagesize(BASEDIR.getpath('uploads').$info['image']);
			if ( $size[0] && $size[0]>300 ) {
				$imageWidth = 300;
			}
		}
		
		//Bannergruppen auflisten
		$grouplist='';
		foreach ( $set['teaser']['groups'] AS $id => $title ) {
			$grouplist.='<option value="'.$id.'"'.iif($id==$_POST['group'],' selected="selected"').'>'.replace($title).'</option>';
		}
		
		//Veröffentlichung
		if ( $_POST['starttime'] ) {
			maketimepost(1,$_POST['starttime']);
			if ( $_POST['endtime']<2147483647 ) maketimepost(2,$_POST['endtime']);
		}
		
		//Veröffentlichung
		if ( $apx->user->has_right('teaser.enable') && isset($_POST['t_day_1']) ) {
			$apx->tmpl->assign('STARTTIME',choosetime(1,0,maketime(1)));
			$apx->tmpl->assign('ENDTIME',choosetime(2,1,maketime(2)));
		}
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('SECID',$_POST['secid']);
		$apx->tmpl->assign('GROUPS',$grouplist);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('LINK',compatible_hsc($_POST['link']));
		$apx->tmpl->assign('IMAGE',iif($info['image'],getpath('uploads').$info['image']));
		$apx->tmpl->assign('IMAGE_WIDTH',$imageWidth);
		$apx->tmpl->assign('DELIMAGE',(int)$_POST['delimage']);
		
		$apx->tmpl->parse('edit');
	}
}



//***************************** Teaser löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		else {
			list($image)=$db->first("SELECT image FROM ".PRE."_teaser WHERE id='".$_REQUEST['id']."' LIMIT 1");
			require(BASEDIR.'lib/class.mediamanager.php');
			$mm=new mediamanager;
			$mm->deletefile($image);
				
			$db->query("DELETE FROM ".PRE."_teaser WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('TEASER_DEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('teaser.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_teaser WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('del',array('ID'=>$_REQUEST['id']));
	}
}



//***************************** Teaser aktivieren *****************************
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
			
			$db->query("UPDATE ".PRE."_teaser SET starttime='".$starttime."',endtime='".$endtime."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('TEASER_ENABLE','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('teaser.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_teaser WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('TITLE', compatible_hsc($title));
		$apx->tmpl->assign('STARTTIME',choosetime(1,0,time()));
		$apx->tmpl->assign('ENDTIME',choosetime(2,1));
		tmessageOverlay('enable');
	}
}



//***************************** Teaser widerrufen *****************************
function disable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');

	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("UPDATE ".PRE."_teaser SET starttime=0,endtime=0 WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('TEASER_DISABLE','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('teaser.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_teaser WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('disable',array('ID'=>$_REQUEST['id']));
	}
}



//***************************** Teasergruppen *****************************

function group() {
	global $set,$db,$apx,$html;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	$data=$set['teaser']['groups'];
	
	//Kategorie löschen
	if ( $_REQUEST['do']=='del' && isset($data[$_REQUEST['id']]) ) {
		list($count)=$db->first("SELECT count(*) FROM ".PRE."_teaser WHERE ".PRE."_teaser.group='".$id."'");
		if ( !$count ) {
			if ( isset($_POST['id']) ) {
				if ( !checkToken() ) infoInvalidToken();
				else {
					unset($data[$_REQUEST['id']]);
					$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='teaser' AND varname='groups' LIMIT 1");
					logit('TEASER_CATDEL',$_REQUEST['id']);
					printJSReload();
				}
			}
			else {
				$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($data[$_REQUEST['id']]))));
				tmessageOverlay('catdel',array('ID'=>$_REQUEST['id']));
			}
			return;
		}
	}
	
	
	//Kategorie bearbeiten
	elseif ( $_REQUEST['do']=='edit' && isset($data[$_REQUEST['id']]) ) {
		if ( isset($_POST['title']) ) {
			if ( !checkToken() ) infoInvalidToken();
			elseif ( !$_POST['title'] ) info('back');
			else {
				$data[$_REQUEST['id']]=$_POST['title'];
				$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='teaser' AND varname='groups' LIMIT 1");
				logit('TEASER_CATEDIT',$_REQUEST['id']);
				printJSRedirect('action.php?action=teaser.group');
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
				$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='teaser' AND varname='groups' LIMIT 1");
				logit('TEASER_CATADD',array_key_max($data));
				printJSRedirect('action.php?action=teaser.group');
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
	$col[]=array('COL_TEASERS',20,'align="center"');
	
	
	//AUSGABE
	asort($data);
	foreach ( $data AS $id => $res ) {
		++$i;
		list($count)=$db->first("SELECT count(*) FROM ".PRE."_teaser WHERE ".PRE."_teaser.group='".$id."'");
		$tabledata[$i]['COL1']=$id;
		$tabledata[$i]['COL2']=$res;
		$tabledata[$i]['COL3']=$count;
		$tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'teaser.group', 'do=edit&id='.$id, $apx->lang->get('CORE_EDIT'));
		if ( !$count ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'teaser.group', 'do=del&id='.$id, $apx->lang->get('CORE_DEL'));
		else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
	}
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
}


} //END CLASS


?>