<?php 

# AFFILIATES CLASS
# ================

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class action {

//***************************** Affiliates zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	quicklink('affiliates.add');
	
	//DnD-Hinweis
	if ( $set['affiliates']['orderby']==1 && $apx->user->has_right('articles.edit') ) {
		echo '<p class="hint">'.$apx->lang->get('USEDND').'</p>';
	}
	
	if ( $set['affiliates']['orderby']!=1 ) {
		$orderdef[0]='title';
		$orderdef['title']=array('title','ASC','COL_TITLE');
		$orderdef['hits']=array('hits','ASC','COL_HITS');
	}
	
	$col[]=array('&nbsp;',1,'');
	$col[]=array('COL_TITLE',50,'class="title"');
	$col[]=array('COL_IMAGE',30,'align="center"');
	$col[]=array('COL_HITS',20,'align="center"');
	
	$data=$db->fetch("SELECT id,title,link,image,hits,active FROM ".PRE."_affiliates ".iif(is_array($orderdef),getorder($orderdef)," ORDER BY ord ASC") );
	$count=count($data);
	if ( $count ) {
		foreach ( $data AS $res ) {
			++$i;
			
			$size = @getimagesize(BASEDIR.getpath('uploads').$res['image']);
			if ( $size[0] && $size[0]>300 ) {
				$imageWidth = 300;
			}
			
			if ( $res['active'] ) $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			
			$tabledata[$i]['COL2']='<a href="../misc.php?action=redirect&amp;url='.urlencode($res['link']).'" target="_blank">'.replace($res['title']).'</a>';
			$tabledata[$i]['COL3']=iif($res['image'],'<img src="../'.getpath('uploads').$res['image'].'" width="'.$imageWidth.'" alt="" />','&nbsp;');
			$tabledata[$i]['COL4']=number_format($res['hits'],0,'','.');
			$tabledata[$i]['ID'] = 'node:'.$res['id'];
			
			//Optionen
			if ( $apx->user->has_right('affiliates.edit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'affiliates.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('affiliates.del') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'affiliates.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $res['active'] && $apx->user->has_right('affiliates.disable') ) $tabledata[$i]['OPTIONS'].=optionHTML('disable.gif', 'affiliates.disable', 'id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_ENABLE'));
			elseif ( !$res['active'] && $apx->user->has_right('affiliates.enable') ) $tabledata[$i]['OPTIONS'].=optionHTML('enable.gif', 'affiliates.enable', 'id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_DISABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	
	if ( $set['affiliates']['orderby']==1 && $apx->user->has_right('articles.edit') ) {
		echo '<div class="listview" id="list">';
		$html->table($col);
		echo '</div>';
		$apx->tmpl->parse('show_js');
	}
	else {
		$html->table($col);
	}
	
	orderstr($orderdef,'action.php?action=affiliates.show');
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Affiliate hinzufügen *****************************
function add() {
	global $set,$db,$apx;
	
	if ( $_POST['send']==1 ) {
		require(BASEDIR.'lib/class.mediamanager.php');
		$mm=new mediamanager;
		$ext=$mm->getext($_FILES['image']['name']);
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['link'] ) infoNotComplete();
		elseif ( $_FILES['image']['tmp_name'] && !in_array($ext,array('GIF','JPG','JPE','JPEG','PNG')) ) info($apx->lang->get('INFO_NOIMAGE')); 
		else {
			
			//Image
			if ( $_FILES['image']['tmp_name'] ) {
				$tblinfo=$db->first("SHOW TABLE STATUS LIKE '".PRE."_affiliates'");
				$newfile='affiliate-'.$tblinfo['Auto_increment'].'.'.strtolower($ext);
				$mm->uploadfile($_FILES['image'],'affiliates',$newfile);
				$_POST['image']='affiliates/'.$newfile;
			}
			
			//Ord
			list($ord)=$db->first("SELECT max(ord) FROM ".PRE."_affiliates");
			$_POST['ord']=$ord+1;
			
			//Active
			if ( $apx->user->has_right('affiliates.enable') && $_POST['pubnow'] ) $_POST['active']=1;
			$db->dinsert(PRE.'_affiliates','title,image,link,ord,active');
			logit('AFFILIATES_ADD','ID #'.$db->insert_id());
			printJSRedirect('action.php?action=affiliates.show');
		}
	}
	else {
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('LINK',compatible_hsc($_POST['link']));
		$apx->tmpl->assign('PUBNOW',iif($apx->user->has_right('affiliates.enable'),(int)$_POST['pubnow'],'off'));
		
		$apx->tmpl->parse('add');
	}
}



//***************************** Affiliate bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	$info=$db->first("SELECT title,image,link FROM ".PRE."_affiliates WHERE id='".intval($_REQUEST['id'])."' LIMIT 1");
	
	if ( $_POST['send']==1 ) {
		require(BASEDIR.'lib/class.mediamanager.php');
		$mm=new mediamanager;
		$ext=$mm->getext($_FILES['image']['name']);
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['title'] || !$_POST['link'] ) infoNotComplete();
		elseif ( $_FILES['image']['tmp_name'] && !in_array($ext,array('GIF','JPG','JPE','JPEG','PNG')) ) info($apx->lang->get('INFO_NOIMAGE'));
		else {
			
			//Bild aktualisieren
			if ( $_FILES['image']['tmp_name'] ) {
				list($oldpic)=$db->first("SELECT image FROM ".PRE."_affiliates WHERE id='".$_REQUEST['id']."'  LIMIT 1");
				if ( $oldpic ) $mm->deletefile($oldpic);
				
				$newfile='affiliate-'.intval($_REQUEST['id']).'.'.strtolower($ext);
				$mm->uploadfile($_FILES['image'],'affiliates',$newfile);
				$_POST['image']='affiliates/'.$newfile;
			}
			elseif ( $_POST['delimage'] ) {
				list($oldpic)=$db->first("SELECT image FROM ".PRE."_affiliates WHERE id='".$_REQUEST['id']."' LIMIT 1");
				$mm->deletefile($oldpic);
				$_POST['image']='';
			}
			
			$db->dupdate(PRE.'_affiliates','title,link'.iif(isset($_POST['image']),',image'),"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('AFFILIATES_EDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('affiliates.show'));
		}
	}
	else {
		$_POST['title']=$info['title'];
		$_POST['link']=$info['link'];
		
		$imageWidth = '';
		if ( $info['image'] ) {
			$size = @getimagesize(BASEDIR.getpath('uploads').$info['image']);
			if ( $size[0] && $size[0]>300 ) {
				$imageWidth = 300;
			}
		}
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('LINK',compatible_hsc($_POST['link']));
		$apx->tmpl->assign('IMAGE',iif($info['image'],getpath('uploads').$info['image']));
		$apx->tmpl->assign('IMAGE_WIDTH',$imageWidth);
		$apx->tmpl->assign('DELIMAGE',(int)$_POST['delimage']);
		
		$apx->tmpl->parse('edit');
	}
}



//***************************** Affiliate löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		else {
			list($image)=$db->first("SELECT image FROM ".PRE."_affiliates WHERE id='".$_REQUEST['id']."' LIMIT 1");
			require(BASEDIR.'lib/class.mediamanager.php');
			$mm=new mediamanager;
			$mm->deletefile($image);
				
			$db->query("DELETE FROM ".PRE."_affiliates WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('AFFILIATES_DEL','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('affiliates.show'));
		}
	}
	else {
		list($title) = $db->first("SELECT title FROM ".PRE."_affiliates WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('del',array('ID'=>$_REQUEST['id']));
	}
}



//***************************** Affiliate aktivieren *****************************
function enable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		$db->query("UPDATE ".PRE."_affiliates SET active='1' WHERE id='".$_REQUEST['id']."' LIMIT 1");
		logit('AFFILIATES_ENABLE','ID #'.$_REQUEST['id']);
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: '.get_index('affiliates.show'));
	}
}



//***************************** Affiliate deaktivieren *****************************
function disable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		$db->query("UPDATE ".PRE."_affiliates SET active='0' WHERE id='".$_REQUEST['id']."' LIMIT 1");
		logit('AFFILIATES_DISABLE','ID #'.$_REQUEST['id']);
		header('Location: '.get_index('affiliates.show'));
	}
}



//***************************** Affiliate anordnen *****************************
/*function move() {
	global $set,$db,$apx;
	if ( $set['affiliates']['sortby']!=1 ) 'moving affiliates is disabled!';
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( !checkToken() ) printInvalidToken();
	else {
		list($ord1)=$db->first("SELECT ord FROM ".PRE."_affiliates WHERE id='".$_REQUEST['id']."' LIMIT 1");
		
		//Nach unten
		if ( $_REQUEST['direction']=='down' ) {
			list($brother,$ord2)=$db->first("SELECT id,ord FROM ".PRE."_affiliates WHERE ord>'".$ord1."' ORDER BY ord ASC LIMIT 1");
			if ( !$brother ) die('no lower brother found!');
		}
		
		//Nach oben
		else {
			list($brother,$ord2)=$db->first("SELECT id,ord FROM ".PRE."_affiliates WHERE ord<'".$ord1."' ORDER BY ord DESC LIMIT 1");
			if ( !$brother ) die('no upper brother found!');
		}
		
		$db->query("UPDATE ".PRE."_affiliates SET ord=".($ord1+$ord2)."-ord WHERE id IN ('".$_REQUEST['id']."','".$brother."')");
		header('Location: '.get_index('affiliates.show'));
	}
}*/


} //END CLASS


?>