<?php

if ( !$set['user']['blog'] ) die('function disabled!');
$apx->lang->drop('myblog');
headline($apx->lang->get('HEADLINE_MYBLOG'),mklink('user.php?action=myblog','user,myblog.html'));
titlebar($apx->lang->get('HEADLINE_MYBLOG'));

//ERSTELLEN
if ( $_REQUEST['do']=='add' ) {
	if ( $_POST['send'] ) {
		if ( !$_POST['title'] || !$_POST['text'] ) message('back');
		else {
			$_POST['userid'] = $user->info['userid'];
			$_POST['time'] = time();
			$db->dinsert(PRE.'_user_blog','userid,title,text,time,allowcoms');
			message($apx->lang->get('MSG_ADD_OK'),mklink('user.php?action=myblog','user,myblog.html'));
		}
	}
	else {
		
		//Vorschau
		if ( $_POST['preview'] ) {
			$text = $_POST['text'];
			$text = badwords($text);
			$text = replace($text,1);
			$text = dbsmilies($text);
			$text = dbcodes($text);
			$apx->tmpl->assign('PREVIEW',$text);
		}
		
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('ALLOWCOMS',intval($_POST['allowcoms']));
		$apx->tmpl->assign('POSTTO',mklink('user.php?action=myblog','user,myblog.html'));
		$apx->tmpl->parse('myblog_addedit');
	}
	require('lib/_end.php');
}

//BEARBEITEN
elseif ( $_REQUEST['do']=='edit' ) {
	$_REQUEST['id'] = (int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['send'] ) {
		if ( !$_POST['title'] || !$_POST['text'] ) message('back');
		else {
			$db->dupdate(PRE.'_user_blog','title,text,allowcoms',"WHERE id='".$_REQUEST['id']."' AND userid='".$user->info['userid']."' LIMIT 1");
			message($apx->lang->get('MSG_EDIT_OK'),mklink('user.php?action=myblog','user,myblog.html'));
		}
	}
	else {
		
		//Vorschau
		if ( $_POST['preview'] ) {
			$text = $_POST['text'];
			$text = badwords($text);
			$text = replace($text,1);
			$text = dbsmilies($text);
			$text = dbcodes($text);
			$apx->tmpl->assign('PREVIEW',$text);
		}
		else {
			list($_POST['title'],$_POST['text'],$_POST['allowcoms']) = $db->first("SELECT title,text,allowcoms FROM ".PRE."_user_blog WHERE id='".$_REQUEST['id']."' AND userid='".$user->info['userid']."' LIMIT 1");
		}
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('ALLOWCOMS',intval($_POST['allowcoms']));
		$apx->tmpl->assign('POSTTO',mklink('user.php?action=myblog','user,myblog.html'));
		$apx->tmpl->parse('myblog_addedit');
	}
	require('lib/_end.php');
}

//LÖSCHEN
elseif ( $_REQUEST['do']=='del' ) {
	$_REQUEST['id'] = (int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( $_POST['send'] ) {
		$db->query("DELETE FROM ".PRE."_user_blog WHERE id='".$_REQUEST['id']."' AND userid='".$user->info['userid']."' LIMIT 1");
		message($apx->lang->get('MSG_DEL_OK'),mklink('user.php?action=myblog','user,myblog.html'));
	}
	else tmessage('delblog',array('ID'=>$_REQUEST['id']));
	require('lib/_end.php');
}

//ÜBERSICHT
list($count) = $db->first("SELECT count(id) FROM ".PRE."_user_blog WHERE userid='".$user->info['userid']."'");
pages(
	mklink(
		'user.php?action=myblog',
		'user,myblog.html'
	),
	$count,
	20
);

//Einträge auslesen
$data = $db->fetch("SELECT id,title,time FROM ".PRE."_user_blog WHERE userid='".$user->info['userid']."' ORDER BY time DESC".getlimit(20));
if ( count($data) ) {
	foreach ( $data AS $res ) {
		++$i;
		$tabledata[$i]['ID'] = $res['id'];
		$tabledata[$i]['TITLE'] = replace($res['title']);
		$tabledata[$i]['TIME'] = $res['time'];
		$tabledata[$i]['LINK_EDIT'] = mklink('user.php?action=myblog&amp;do=edit&amp;id='.$res['id'],'user,myblog.html?do=edit&amp;id='.$res['id']);
		$tabledata[$i]['LINK_DEL'] = mklink('user.php?action=myblog&amp;do=del&amp;id='.$res['id'],'user,myblog.html?do=del&amp;id='.$res['id']);
	}
}

$apx->tmpl->assign('ENTRY',$tabledata);
$apx->tmpl->assign('LINK_NEW',mklink('user.php?action=myblog&amp;do=add','user,myblog.html?do=add'));

$apx->tmpl->parse('myblog');

?>