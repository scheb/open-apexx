<?php

$apx->lang->drop('ignorelist');
headline($apx->lang->get('HEADLINE_IGNORELIST'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_IGNORELIST'));
$_REQUEST['del'] = (int)$_REQUEST['del'];

if ( $_REQUEST['add'] ) {
	if ( $_POST['send'] ) {
		if ( !$_POST['username'] ) message('back');
		else {
			list($userid) = $db->first("SELECT userid FROM ".PRE."_user WHERE LOWER(username)='".addslashes(strtolower($_POST['username']))."'");
			$data = $db->fetch("SELECT ignored FROM ".PRE."_user_ignore WHERE userid='".$user->info['userid']."'");
			$existing = get_ids($data,'ignored');
			if ( !$userid ) message($apx->lang->get('MSG_NOMATCH'),'back');
			elseif ( in_array($userid,$existing) ) message($apx->lang->get('MSG_EXISTS'),'back');
			elseif ( $userid==$user->info['userid'] ) message($apx->lang->get('MSG_NOTSELF'),'back');
			else {
				$db->query("INSERT INTO ".PRE."_user_ignore (userid,ignored,reason) VALUES ('".$user->info['userid']."','".$userid."','".addslashes($_POST['reason'])."')");
				$goto = mklink(
					'user.php?action=ignorelist',
					'user,ignorelist.html'
				);
				message($apx->lang->get('MSG_ADD_OK'),$goto);
			}
		}
	}
	else {
		tmessage('addignore',array('USERNAME'=>compatible_hsc($_REQUEST['username'])));
	}
}
elseif ( $_REQUEST['del'] ) {
	if ( $_POST['del'] ) {
		$db->query("DELETE FROM ".PRE."_user_ignore WHERE userid='".$user->info['userid']."' AND ignored='".intval($_POST['del'])."' LIMIT 1");
		$goto = mklink(
			'user.php?action=ignorelist',
			'user,ignorelist.html'
		);
		message($apx->lang->get('MSG_DEL_OK'),$goto);
	}
	else {
		tmessage('delignore',array('ID'=>$_REQUEST['del']));
	}
}
else {
	
	//Ignorierte Benutzer auslesen
	$data = $db->fetch("SELECT u.userid,u.username,i.reason FROM ".PRE."_user_ignore AS i LEFT JOIN ".PRE."_user AS u ON i.ignored=u.userid WHERE i.userid='".$user->info['userid']."' ORDER BY u.username ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$tabledata[$i]['ID'] = $res['userid'];
			$tabledata[$i]['NAME'] = replace($res['username']);
			$tabledata[$i]['REASON'] = replace($res['reason']);
			$tabledata[$i]['LINK_DEL'] = mklink(
				'user.php?action=ignorelist&amp;del='.$res['userid'],
				'user,ignorelist.html?del='.$res['userid']
			);
		}
	}
	
	$apx->tmpl->assign('LINK_ADD',mklink('user.php?action=ignorelist&amp;add=1','user,ignorelist.html?add=1'));
	$apx->tmpl->assign('USER',$tabledata);
	$apx->tmpl->parse('ignorelist');
}

?>