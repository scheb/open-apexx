<?php 

# POLL
# ==========

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class action {

var $colors=array(
	'white',
	'silver',
	'black',
	'skyblue',
	'royalblue',
	'blue',
	'darkblue',
	'indigo',
	'purple',
	'deeppink',
	'darkred',
	'sienna',
	'firebrick',
	'crimson',
	'red',
	'orangered',
	'chocolate',
	'orange',
	'yellow',
	'limegreen',
	'green',
	'seagreen',
	'teal'
);



//***************************** Umfragen zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	quicklink('poll.add');
	
	$orderdef[0]='addtime';
	$orderdef['question']=array('question','ASC','COL_QUESTION');
	$orderdef['addtime']=array('addtime','DESC','COL_ADDTIME');
	$orderdef['starttime']=array('starttime','DESC','COL_STARTTIME');
	$orderdef['endtime']=array('endtime','DESC','COL_ENDTIME');
	
	$col[]=array('',1,'align="center"');
	$col[]=array('COL_QUESTION',60,'class="title"');
	$col[]=array('COL_STARTTIME',20,'align="center"');
	$col[]=array('COL_ENDTIME',20,'align="center"');
	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_poll");
	pages('action.php?action=poll.show&amp;sortby='.$_REQUEST['sortby'],$count);
	
	$data=$db->fetch("SELECT id,secid,question,addtime,starttime,endtime,days,allowcoms FROM ".PRE."_poll ".getorder($orderdef).getlimit());
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( !$res['starttime'] ) $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['endtime']<time() ) $tabledata[$i]['COL1']='<img src="design/greendotcross.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $res['starttime']>time() ) $tabledata[$i]['COL1']='<img src="design/greendotwait.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			
			$tmp=unserialize_section($res['secid']);
			$question=shorttext(strip_tags($res['question']),60);
			$link=mklink(
				'poll.php?id='.$res['id'],
				'poll,'.$res['id'].urlformat($res['question']).'.html',
				iif($set['main']['forcesection'],iif(unserialize_section($res['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
			);
			
			$tabledata[$i]['COL2']='<a href="'.$link.'" target="_blank">'.$question.'</a>';
			if ( $res['starttime'] ) $tabledata[$i]['COL3']=mkdate($res['starttime'],'<br />');
			if ( $res['starttime'] ) $tabledata[$i]['COL4']=mkdate(($res['starttime']+$res['days']*24*3600),'<br />');
			
			//Optionen
			if ( $apx->user->has_right('poll.edit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'poll.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('poll.del') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'poll.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( ( !$res['starttime'] || $res['endtime']<time() ) && $apx->user->has_right('poll.enable') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('enable.gif', 'poll.enable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
			elseif ( $res['starttime'] && $apx->user->has_right('poll.disable') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('disable.gif', 'poll.disable', 'id='.$res['id'], $apx->lang->get('CORE_DISABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Kommentare
			if ( $apx->is_module('comments') ) {
				$tabledata[$i]['OPTIONS'].='&nbsp;';
				list($comments)=$db->first("SELECT count(mid) FROM ".PRE."_comments WHERE ( module='poll' AND mid='".$res['id']."' )");
				if ( $comments && ( $apx->is_module('comments') && $set['poll']['coms'] ) && $res['allowcoms'] && $apx->user->has_right('comments.show') ) $tabledata[$i]['OPTIONS'].=optionHTML('comments.gif', 'comments.show', 'module=poll&mid='.$res['id'], $apx->lang->get('COMMENTS'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	
	orderstr($orderdef,'action.php?action=poll.show');
	
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Umfrage hinzufügen *****************************
function add() {
	global $set,$db,$apx;
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send'] ) {
		
		//Mindestens zwei Felder belegt?
		for ( $i=1; $i<=20; $i++ ) {
			if ( $_POST['a'.$i] ) ++$ac;
			if ( $ac==2 ) break;
		}
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['question'] || !$_POST['days'] || $ac<2 ) infoNotComplete();
		else {
			for ( $i=1; $i<=20; $i++ ) if ( $_POST['a'.$i] && $_POST['color'.$i] ) $qcache[]=array($_POST['a'.$i],$_POST['color'.$i]);
			for ( $i=1; $i<=20; $i++ ) {
				$_POST['a'.$i]=$qcache[($i-1)][0];
				$_POST['color'.$i]=$qcache[($i-1)][1];
			}
			
			$_POST['secid']=serialize_section($_POST['secid']);
			$_POST['addtime']=time();
			
			//Veröffentlichen
			if ( $apx->user->has_right('poll.enable') && $_POST['pubnow'] ) {
				$_POST['starttime']=time();
				$_POST['endtime']='3000000000';
				$addfields=',starttime,endtime';
			}
			
			$db->dinsert(PRE."_poll","secid,question,meta_description,a1,a2,a3,a4,a5,a6,a7,a8,a9,a10,a11,a12,a13,a14,a15,a16,a17,a18,a19,a20,color1,color2,color3,color4,color5,color6,color7,color8,color9,color10,color11,color12,color13,color14,color15,color16,color17,color18,color19,color20,days,multiple,searchable,allowcoms,addtime".$addfields);
			$nid = $db->insert_id();
			logit('POLL_ADD','ID #'.$nid);
			
			//Tags
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_poll_tags VALUES('".$nid."', '".$tagid."')");
			}
			
			printJSRedirect('action.php?action=poll.show');
		}
	}
	else {
		$_POST['allowcoms']=1;
		$_POST['searchable']=1;
		$_POST['days']=14;
		
		//Sektionen auflisten
		if ( is_array($apx->sections) && count($apx->sections) ) {
			$seclist='<option value="all" style="font-weight:bold;"'.iif(in_array('all',$_POST['secid']),' selected="selected"').'>'.$apx->lang->get('ALLSEC').'</option>';
			foreach ( $apx->sections AS $id => $info ) {
				$seclist.='<option value="'.$id.'"'.iif(in_array($id,$_POST['secid']),' selected="selected"').'>'.replace($info['title']).'</option>';
			}
		}
		
		//Antwortmöglichkeiten
		for ( $i=1; $i<=20; $i++ ) {
			if ( $i>1 && !$_POST['a'.$i] ) continue;
			$answerdata[$i]['TEXT']=compatible_hsc($_POST['a'.$i]);
			$answerdata[$i]['COLOR']=iif($_POST['color'.$i],$_POST['color'.$i],$this->colors[0]);
			$answerdata[$i]['DISPLAY']=1;
		}
		
		//Felder auffüllen
		while ( count($answerdata)<20 ) {
			$answerdata[]=array('COLOR'=>$this->colors[0]);
		}
		
		//Farben
		foreach ( $this->colors AS $color ) {
			$colordata[]['ID']=$color;
		}
		
		$apx->tmpl->assign('COLOR',$colordata);
		$apx->tmpl->assign('ANSWER',$answerdata);
		$apx->tmpl->assign('SECLIST',$seclist);
		$apx->tmpl->assign('QUESTION',compatible_hsc($_POST['question']));
		$apx->tmpl->assign('DAYS',intval($_POST['days']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('MULTIPLE',(int)$_POST['multiple']);
		$apx->tmpl->assign('PUBNOW',(int)$_POST['pubnow']);
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		
		$apx->tmpl->parse('add');
	}
}


//***************************** Umfrage bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	//Sektions-Liste
	if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
	
	if ( $_POST['send'] ) {
		
		//Mindestens zwei Felder belegt?
		for ( $i=1; $i<=20; $i++ ) {
			if ( $_POST['a'.$i] ) ++$ac;
			if ( $ac==2 ) break;
		}
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['question'] || !$_POST['days'] || $ac<2 ) infoNotComplete();
		else {
			for ( $i=1; $i<=20; $i++ ) if ( $_POST['a'.$i] && $_POST['color'.$i] ) $qcache[]=array($_POST['a'.$i],$_POST['color'.$i],$_POST['a'.$i.'_c']);
			for ( $i=1; $i<=20; $i++ ) {
				$_POST['a'.$i]=$qcache[($i-1)][0];
				$_POST['color'.$i]=$qcache[($i-1)][1];
				$_POST['a'.$i.'_c']=$qcache[($i-1)][2];
			}
			
			$_POST['secid']=serialize_section($_POST['secid']);
			
			//Veröffentlichung
			if ( $apx->user->has_right('poll.enable') && isset($_POST['t_day_1']) ) {
				$_POST['starttime']=maketime(1);
				$_POST['endtime']=maketime(2);
				if ( $_POST['starttime'] ) {
				if ( !$_POST['endtime'] || $_POST['endtime']<=$_POST['starttime'] ) $_POST['endtime']=3000000000;
				$addfields=',starttime,endtime';
				}
			}
			
			$db->dupdate(PRE.'_poll','secid,question,meta_description,a1,a2,a3,a4,a5,a6,a7,a8,a9,a10,a11,a12,a13,a14,a15,a16,a17,a18,a19,a20,color1,color2,color3,color4,color5,color6,color7,color8,color9,color10,color11,color12,color13,color14,color15,color16,color17,color18,color19,color20,a1_c,a2_c,a3_c,a4_c,a5_c,a6_c,a7_c,a8_c,a9_c,a10_c,a11_c,a12_c,a13_c,a14_c,a15_c,a16_c,a17_c,a18_c,a19_c,a20_c,days,multiple,searchable,allowcoms'.$addfields,"WHERE ( id='".$_REQUEST['id']."' ) LIMIT 1");
			logit('POLL_EDIT','ID #'.$_REQUEST['id']);
			
			//Tags
			$db->query("DELETE FROM ".PRE."_poll_tags WHERE id='".$_REQUEST['id']."'");
			$tagids = produceTagIds($_POST['tags']);
			foreach ( $tagids AS $tagid ) {
				$db->query("INSERT IGNORE INTO ".PRE."_poll_tags VALUES('".$_REQUEST['id']."', '".$tagid."')");
			}
			
			printJSRedirect(get_index('poll.show'));
		}
	}
	else {
		$res=$db->first("SELECT * FROM ".PRE."_poll WHERE ( id='".$_REQUEST['id']."' ) LIMIT 1");
		foreach ( $res AS $key => $val ) $_POST[$key]=$val;
		$_POST['secid']=unserialize_section($_POST['secid']);
		
		//Veröffentlichung
		if ( $res['starttime'] ) {
			maketimepost(1,$res['starttime']);
			if ( $res['endtime']<2147483647 ) maketimepost(2,$res['endtime']);
		}
		
		//Sektionen auflisten
		if ( is_array($apx->sections) && count($apx->sections) ) {
			$seclist='<option value="all" style="font-weight:bold;"'.iif(in_array('all',$_POST['secid']),' selected="selected"').'>'.$apx->lang->get('ALLSEC').'</option>';
			foreach ( $apx->sections AS $id => $info ) {
				$seclist.='<option value="'.$id.'"'.iif(in_array($id,$_POST['secid']),' selected="selected"').'>'.replace($info['title']).'</option>';
			}
		}
		
		//Antwortmöglichkeiten
		for ( $i=1; $i<=20; $i++ ) {
			if ( $i>1 && !$_POST['a'.$i] ) continue;
			$answerdata[$i]['TEXT']=compatible_hsc($_POST['a'.$i]);
			$answerdata[$i]['VOTES']=intval($_POST['a'.$i.'_c']);
			$answerdata[$i]['COLOR']=iif($_POST['color'.$i],$_POST['color'.$i],$this->colors[0]);
			$answerdata[$i]['DISPLAY']=1;
		}
		
		//Felder auffüllen
		while ( count($answerdata)<20 ) {
			$answerdata[]=array('COLOR'=>$this->colors[0]);
		}
		
		//Veröffentlichung
		if ( $apx->user->has_right('poll.enable') && isset($_POST['t_day_1']) ) {
			$apx->tmpl->assign('STARTTIME',choosetime(1,0,maketime(1)));
			$apx->tmpl->assign('ENDTIME',choosetime(2,1,maketime(2)));
		}
		
		//Farben
		foreach ( $this->colors AS $color ) {
			$colordata[]['ID']=$color;
		}
		
		//Tags
		$tags = array();
		$tagdata = $db->fetch("
			SELECT t.tag
			FROM ".PRE."_poll_tags AS n
			LEFT JOIN ".PRE."_tags AS t USING(tagid)
			WHERE n.id='".$_REQUEST['id']."'
			ORDER BY t.tag ASC
		");
		$tags = get_ids($tagdata, 'tag');
		$_POST['tags'] = implode(', ', $tags);
		
		$apx->tmpl->assign('COLOR',$colordata);
		$apx->tmpl->assign('ANSWER',$answerdata);
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('SECLIST',$seclist);
		$apx->tmpl->assign('QUESTION',compatible_hsc($_POST['question']));
		$apx->tmpl->assign('DAYS',intval($_POST['days']));
		$apx->tmpl->assign('META_DESCRIPTION',compatible_hsc($_POST['meta_description']));
		$apx->tmpl->assign('TAGS',compatible_hsc($_POST['tags']));
		$apx->tmpl->assign('MULTIPLE',(int)$_POST['multiple']);
		$apx->tmpl->assign('ALLOWCOMS',(int)$_POST['allowcoms']);
		$apx->tmpl->assign('SEARCHABLE',(int)$_POST['searchable']);
		
		$apx->tmpl->parse('edit');
	}
}


//***************************** Umfrage löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');

	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_poll WHERE ( id='".$_REQUEST['id']."' ) LIMIT 1");
			
			//Kommentare
			if ( $apx->is_module('comments') && $db->affected_rows() ) {
				$db->query("DELETE FROM ".PRE."_comments WHERE ( module='poll' AND mid='".$_REQUEST['id']."' )");
			}
			
			//Tags löschen
			if ( $db->affected_rows() ) {
				$db->query("DELETE FROM ".PRE."_poll_tags WHERE id='".$_REQUEST['id']."'");
			}
			
			logit('POLL_DEL','ID #'.$_REQUEST['id']);
			printJSReload();
		}
	}
	else {
		list($title) = $db->first("SELECT question FROM ".PRE."_poll WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']), '/');
	}
}


//***************************** Umfrage aktivieren *****************************
function enable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send']==1 ) {
		$starttime=maketime(1);
		$endtime=maketime(2);
		if ( !$endtime || $endtime<=$starttime ) $endtime=3000000000;
		
		$db->query("UPDATE ".PRE."_poll SET starttime='".$starttime."',endtime='".$endtime."' WHERE ( id='".$_REQUEST['id']."' ) LIMIT 1");
		logit('POLL_ENABLE','ID #'.$_REQUEST['id']);
		printJSRedirect(get_index('poll.show'));
	}
	else {
		list($title) = $db->first("SELECT question FROM ".PRE."_poll WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('TITLE',compatible_hsc($title));
		$apx->tmpl->assign('STARTTIME',choosetime(1,0,time()));
		$apx->tmpl->assign('ENDTIME',choosetime(2,1));
		tmessageOverlay('enable', $input);
	}
}


//***************************** Umfrage deaktivieren *****************************
function disable() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send'] ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("UPDATE ".PRE."_poll SET starttime='0',endtime='0' WHERE ( id='".$_REQUEST['id']."' ) LIMIT 1");
			logit('POLL_DISABLE','ID #'.$_REQUEST['id']);
			printJSReload();
		}
	}
	else {
		list($title) = $db->first("SELECT question FROM ".PRE."_poll WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('disable',array('ID'=>$_REQUEST['id']));
	}
}


} //END CLASS


?>
