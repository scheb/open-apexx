<?php 

# GUESTBOOK CLASS
# ===============

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class action {

//***************************** GB-Einträge zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	quicklink_multi('guestbook.blockip');
	quicklink_multi('guestbook.blockcontent');
	quicklink_out();
	
	$orderdef[0]='time';
	$orderdef['name']=array('username','ASC','COL_NAME');
	$orderdef['time']=array('time','DESC','SORT_TIME');
	
	if ( $set['guestbook']['mod'] ) $col[]=array('',1,'align="center"');
	$col[]=array('COL_NAME',30,'class="title"');
	$col[]=array('COL_TEXT',50,'');
	$col[]=array('COL_IP',20,'align="center"');
	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_guestbook");
	pages('action.php?action=guestbook.show&amp;sortby='.$_REQUEST['sortby'],$count);
	
	$data=$db->fetch("SELECT id,username,text,ip,active,com_text FROM ".PRE."_guestbook ".getorder($orderdef).getlimit());
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$icol=0;
			
			//Moderiert -> Icons
			if ( $set['guestbook']['mod'] ) {
				if ( $res['active'] ) $tabledata[$i]['COL'.++$icol]='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
				else $tabledata[$i]['COL'.++$icol]='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			}
			
			$tabledata[$i]['COL'.++$icol]=replace($res['username']);
			$tabledata[$i]['COL'.++$icol]=shorttext($res['text'],50);
			$tabledata[$i]['COL'.++$icol]=$res['ip'].iif($apx->user->has_right('guestbook.blockip'),' <a href="action.php?action=guestbook.blockip&amp;setip='.$res['ip'].'"><img src="design/block.gif" alt="'.$apx->lang->get('BLOCK').'" title="'.$apx->lang->get('BLOCK').'" /></a>');
			$tabledata[$i]['ID']=$res['id'];
			
			//Optionen
			if ( $apx->user->has_right('guestbook.edit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'guestbook.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('guestbook.del') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'guestbook.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			//Moderiert -> Enable/Disable
			if ( $set['guestbook']['mod'] ) {
				if ( $res['active'] && $apx->user->has_right('guestbook.disable') ) $tabledata[$i]['OPTIONS'].=optionHTML('disable.gif', 'guestbook.disable', 'id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_DISABLE'));
				elseif ( !$res['active'] && $apx->user->has_right('guestbook.enable') ) $tabledata[$i]['OPTIONS'].=optionHTML('enable.gif', 'guestbook.enable', 'id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_ENABLE'));
				else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			}
			
			$tabledata[$i]['OPTIONS'].='&nbsp;';
			if ( $apx->user->has_right('guestbook.com') ) {
				$icon = 'comment_none.gif';
				if ( $res['com_text'] ) {
					$icon = 'comment.gif';
				}
				$tabledata[$i]['OPTIONS'].= optionHTML($icon, 'guestbook.com', 'id='.$res['id'], $apx->lang->get('ADDCOM'));
			}
		}
	}
	
	
	$multiactions = array();
	if ( $apx->user->has_right('guestbook.del') ) $multiactions[] = array($apx->lang->get('CORE_DEL'), 'action.php?action=guestbook.del&module='.$this->module.'&mid='.$this->mid);
	if( $set['guestbook']['mod'] ) {
		if ( $apx->user->has_right('guestbook.enable') ) $multiactions[] = array($apx->lang->get('CORE_ENABLE'), 'action.php?action=guestbook.enable&module='.$this->module.'&mid='.$this->mid);
		if ( $apx->user->has_right('guestbook.disable') ) $multiactions[] = array($apx->lang->get('CORE_DISABLE'), 'action.php?action=guestbook.disable&module='.$this->module.'&mid='.$this->mid);
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col,$multiactions);
	
	orderstr($orderdef,'action.php?action=guestbook.show&amp;id='.$_REQUEST['id']);
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** GB-Eintrag bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	$res=$db->first("SELECT * FROM ".PRE."_guestbook WHERE id='".$_REQUEST['id']."' LIMIT 1");
	
	//Registrierter Benutzer
	if ( $res['userid'] ) {
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) infoInvalidToken();
			elseif ( !$_POST['id']
				|| !$_POST['text']
				|| ( $set['guestbook']['req_title'] && !$_POST['title'] )
			) infoNotComplete();
			elseif ( $set['guestbook']['maxlen'] && strlen($_POST['text'])>$set['guestbook']['maxlen'] ) info($apx->lang->get('INFO_TOOLONG'));
			else {
				$db->dupdate(PRE.'_guestbook','title,text,custom1,custom2,custom3,custom4,custom5',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
				logit('GUESTBOOK_EDIT',"ID #".$_REQUEST['id']);
				printJSRedirect(get_index('guestbook.show'));
			}
		}
		else {
			$_POST['text']=$res['text'];
			$_POST['title']=$res['title'];
		
			$apx->tmpl->assign('ID',$_REQUEST['id']);
			$apx->tmpl->assign('USERID',$res['userid']);
			$apx->tmpl->assign('USERNAME',replace($res['username']));
			$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
			$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
			
			$apx->tmpl->assign('SET_REQ_EMAIL',$set['guestbook']['req_email']);
			$apx->tmpl->assign('SET_REQ_HOMEPAGE',$set['guestbook']['req_homepage']);
			$apx->tmpl->assign('SET_REQ_TITLE',$set['guestbook']['req_title']);
			$apx->tmpl->assign('SET_MAXLEN',$set['guestbook']['maxlen']);
			
			for ( $i=1; $i<=5; $i++ ) {
				$apx->tmpl->assign('CUSTOM'.$i.'_NAME',$set['guestbook']['cusfield_names'][$i-1]);
				$apx->tmpl->assign('CUSTOM'.$i,compatible_hsc($res['custom'.$i]));
			}
			
			$apx->tmpl->parse('edit');
		}
	}
	
	//Gast
	else {
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) infoInvalidToken();
			elseif ( !$_POST['text']
				|| ( $set['guestbook']['req_email'] && !$_POST['email'] )
				|| ( $set['guestbook']['req_homepage'] && !$_POST['homepage'] )
				|| ( $set['guestbook']['req_title'] && !$_POST['title'] )
			) infoNotComplete();
			elseif ( $set['guestbook']['maxlen'] && strlen($_POST['text'])>$set['guestbook']['maxlen'] ) info($apx->lang->get('INFO_TOOLONG'));
			else {
				if ( substr($_POST['homepage'],0,4)=='www.' ) $_POST['homepage']='http://'.$_POST['homepage'];
				$db->dupdate(PRE.'_guestbook','username,email,homepage,title,text,custom1,custom2,custom3,custom4,custom5',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
				logit('GUESTBOOK_EDIT','ID #'.$_REQUEST['id']);
				printJSRedirect(get_index('guestbook.show'));
			}
		}
		else {
			foreach ( $res AS $id => $val ) $_POST[$id]=$val;
			
			$apx->tmpl->assign('ID',$_REQUEST['id']);
			$apx->tmpl->assign('USERNAME',compatible_hsc($_POST['username']));
			$apx->tmpl->assign('EMAIL',compatible_hsc($_POST['email']));
			$apx->tmpl->assign('HOMEPAGE',compatible_hsc($_POST['homepage']));
			$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
			$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
			
			$apx->tmpl->assign('SET_REQ_EMAIL',$set['guestbook']['req_email']);
			$apx->tmpl->assign('SET_REQ_HOMEPAGE',$set['guestbook']['req_homepage']);
			$apx->tmpl->assign('SET_REQ_TITLE',$set['guestbook']['req_title']);
			$apx->tmpl->assign('SET_MAXLEN',$set['guestbook']['maxlen']);
			
			for ( $i=1; $i<=5; $i++ ) {
				$apx->tmpl->assign('CUSTOM'.$i.'_NAME',$set['guestbook']['cusfield_names'][$i-1]);
				$apx->tmpl->assign('CUSTOM'.$i,compatible_hsc($res['custom'.$i]));
			}
			
			$apx->tmpl->parse('edit');
		}
	}
}



//***************************** GB-Eintrag löschen *****************************
function del() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('guestbook.show'));
				return;
			}
			
			if ( count($cache) ) {	
				$db->query("DELETE FROM ".PRE."_guestbook WHERE id IN (".implode(',',$cache).")");
				foreach ( $cache AS $id ) logit('GUESTBOOK_DEL','ID #'.$id);
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('guestbook.show'));
		}
	}
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				$db->query("DELETE FROM ".PRE."_guestbook WHERE id='".$_REQUEST['id']."' LIMIT 1");
				logit('GUESTBOOK_DEL','ID #'.$_REQUEST['id']);
				printJSRedirect(get_index('guestbook.show'));
			}
		}
		else {
			list($title) = $db->first("SELECT username FROM ".PRE."_guestbook WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
			tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
		}
	}
}



//***************************** GB-Eintrag freigeben *****************************
function enable() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('guestbook.show'));
				return;
			}
			
			if ( count($cache) ) {
				$db->query("UPDATE ".PRE."_guestbook SET active='1' WHERE id IN (".implode(',',$cache).")");
				foreach ( $cache AS $id ) logit('GUESTBOOK_ENABLE','ID #'.$id);
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('guestbook.show'));
		}
	}
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		if ( !$set['guestbook']['mod'] ) return;
		
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("UPDATE ".PRE."_guestbook SET active='1' WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('GUESTBOOK_ENABLE','ID #'.$_REQUEST['id']);
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('guestbook.show'));
		}
	}
}



//***************************** GB-Eintrag sperren *****************************
function disable() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('guestbook.show'));
				return;
			}
			
			if ( count($cache) ) {
				$db->query("UPDATE ".PRE."_guestbook SET active='0' WHERE id IN (".implode(',',$cache).")");
				foreach ( $cache AS $id ) logit('GUESTBOOK_DISABLE','ID #'.$id);
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('guestbook.show'));
		}
	}
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		if ( !$set['guestbook']['mod'] ) return;
		
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("UPDATE ".PRE."_guestbook SET active='0' WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('GUESTBOOK_DISABLE','ID #'.$_REQUEST['id']);
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('guestbook.show'));
		}
	}
}



////////////////////////////////////////////////////////////////////////////////////////// -> SONSTIGE FUNKTIONEN

//***************************** GB-Eintrag kommentieren *****************************
function com() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	$res=$db->first("SELECT b.username,a.com_text FROM ".PRE."_guestbook AS a LEFT JOIN ".PRE."_user AS b ON a.com_userid=b.userid WHERE a.id='".$_REQUEST['id']."' LIMIT 1");	
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['id'] || ( !$_POST['text'] && !$_POST['delcom'] ) ) infoNotComplete();
		else {
			
			if ( $_POST['delcom'] ) $db->query("UPDATE ".PRE."_guestbook SET com_userid='',com_text='',com_time='' WHERE id='".$_REQUEST['id']."' LIMIT 1");
			else $db->query("UPDATE ".PRE."_guestbook SET com_userid=IF(com_userid,com_userid,'".$apx->user->info['userid']."'),com_text='".addslashes($_POST['text'])."',com_time=IF(com_time,com_time,'".time()."') WHERE id='".$_REQUEST['id']."' LIMIT 1");
			
			logit('GUESTBOOK_COM','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('guestbook.show'));
		}
	}
	else {
		$_POST['text']=$res['com_text'];
		
		if ( $res['username'] ) $username=$res['username'];
		else $username=$apx->user->info['username'];
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('USERNAME',compatible_hsc($username));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('DELCOM',(int)$_POST['delcom']);
		
		$apx->tmpl->parse('com');
	}
}



//***************************** IPs sperren *****************************
function blockip() {
	global $set,$db,$apx,$html;
	$_REQUEST['key']=(int)$_REQUEST['key'];
	
	//IP löschen
	if ( $_REQUEST['do']=='del' ) {
		if ( $_POST['send'] ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				unset($set['guestbook']['blockip'][$_REQUEST['id']]);
				$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($set['guestbook']['blockip']))."' WHERE module='guestbook' AND varname='blockip' LIMIT 1");
				printJSRedirect('action.php?action=guestbook.blockip');
			}
		}
		else {
			$ip = float2ip($set['guestbook']['blockip'][$_REQUEST['id']]['startip']);
			if ( $set['guestbook']['blockip'][$_REQUEST['id']]['endip'] ) {
				$ip .= ' - '.float2ip($set['guestbook']['blockip'][$_REQUEST['id']]['endip']);
			}
			$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_DEL', array('TITLE' => compatible_hsc($ip))));
			tmessageOverlay('ipdel', array('ID' => $_REQUEST['id']));
		}
		return;
	}
	
	//IP hinzufügen
	elseif ( $_REQUEST['do']=='add' ) {
		if ( !checkToken() ) infoInvalidToken();
		else {
			for ( $i=1; $i<=4; $i++ ) {
				$_POST['startip_'.$i]=(int)$_POST['startip_'.$i];
				$_POST['endip_'.$i]=(int)$_POST['endip_'.$i];
				if ( $_POST['startip_'.$i]>255 ) $_POST['startip_'.$i]=255;
				if ( $_POST['startip_'.$i]<0 ) $_POST['startip_'.$i]=0;
				if ( $_POST['endip_'.$i]>255 ) $_POST['endip_'.$i]=255;
				if ( $_POST['endip_'.$i]<0 ) $_POST['endip_'.$i]=0;
			}
			
			$start=ip2float($_POST['startip_1'].'.'.$_POST['startip_2'].'.'.$_POST['startip_3'].'.'.$_POST['startip_4']);
			$end=ip2float($_POST['endip_1'].'.'.$_POST['endip_2'].'.'.$_POST['endip_3'].'.'.$_POST['endip_4']);
			if ( $_POST['type']==1 ) $end=false;
			
			//IPs umdrehen
			if ( $end!==false && $end<$start ) {
				$cache=$end;
				$end=$start;
				$start=$cache;
			}
			
			$set['guestbook']['blockip'][]=array('startip'=>$start,'endip'=>$end);
			$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($set['guestbook']['blockip']))."' WHERE module='guestbook' AND varname='blockip' LIMIT 1");
			printJSRedirect('action.php?action=guestbook.blockip');
		}
		return;
	}
	
	quicklink_index('guestbook.show');
	quicklink_out();
	
	//AUFLISTUNG BEGINNT
	$ips=$set['guestbook']['blockip'];
	if ( !is_array($ips) ) $ips=array();
	$ips=array_sort($ips,'startip','asc');
	
	$col[]=array('COL_IPRANGE',100,'class="title"');
	
	foreach ( $ips AS $i => $res ) {
		$start=float2ip($res['startip']);
		$end=float2ip($res['endip']);
		$tabledata[$i]['COL1']=$start.iif($res['endip'],' &#150; '.$end);
		$tabledata[$i]['OPTIONS']=optionHTMLOverlay('del.gif', 'guestbook.blockip', 'do=del&id='.$i, $apx->lang->get('CORE_DEL'));
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	
	//Hinzufügen
	if ( $_REQUEST['setip'] ) {
		$ipp=explode('.',$_REQUEST['setip'],4);
		$apx->tmpl->assign('IP_1',(int)$ipp[0]);
		$apx->tmpl->assign('IP_2',(int)$ipp[1]);
		$apx->tmpl->assign('IP_3',(int)$ipp[2]);
		$apx->tmpl->assign('IP_4',(int)$ipp[3]);
	}
	$apx->tmpl->parse('blockip');
}



//***************************** Inhalte sperren *****************************
function blockcontent() {
	global $set,$db,$apx,$html;
	$_REQUEST['key']=(int)$_REQUEST['key'];
	
	//IP löschen
	if ( $_REQUEST['do']=='del' ) {
		if ( $_POST['send'] ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				unset($set['guestbook']['blockstring'][$_REQUEST['id']]);
				$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($set['guestbook']['blockstring']))."' WHERE module='guestbook' AND varname='blockstring' LIMIT 1");
				printJSRedirect('action.php?action=guestbook.blockcontent');
			}
		}
		else {
			$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_DEL', array('TITLE' => compatible_hsc($set['guestbook']['blockstring'][$_REQUEST['id']]))));
			tmessageOverlay('contentdel', array('ID' => $_REQUEST['id']));
		}
		return;
	}
	
	//IP hinzufügen
	elseif ( $_REQUEST['do']=='add' ) {
		if ( !checkToken() )  printInvalidToken();
		elseif ( !$_POST['string'] ) infoNotComplete();
		else {
			$set['guestbook']['blockstring'][]=$_POST['string'];
			$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($set['guestbook']['blockstring']))."' WHERE module='guestbook' AND varname='blockstring' LIMIT 1");
			printJSRedirect('action.php?action=guestbook.blockcontent');
		}
		return;
	}
	
	quicklink_index('guestbook.show');
	quicklink_out();
	
	//AUFLISTUNG BEGINNT
	$strings=$set['guestbook']['blockstring'];
	if ( !is_array($strings) ) $strings=array();
	$strings=array_sort($strings,0,'asc');
	
	$col[]=array('TITLE_GUESTBOOK_BLOCKCONTENT',100,'class="title"');
	
	foreach ( $strings AS $i => $res ) {
		$tabledata[$i]['COL1']=$res;
		$tabledata[$i]['OPTIONS']=optionHTMLOverlay('del.gif', 'guestbook.blockcontent', 'do=del&id='.$i, $apx->lang->get('CORE_DEL'));
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	
	$apx->tmpl->parse('blockcontent');
}


} //END CLASS


?>