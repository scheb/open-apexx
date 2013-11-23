<?php

if ( !$set['user']['blog'] ) die('function disabled!');
$_REQUEST['id']=(int)$_REQUEST['id'];
$_REQUEST['blogid']=(int)$_REQUEST['blogid'];
if ( !$_REQUEST['id'] ) die('missing ID!');
$apx->lang->drop('blog');

//Nur für Registrierte
if ( $set['user']['profile_regonly'] && !$user->info['userid'] ) {
	tmessage('profileregonly',array(),false,false);
	require('lib/_end.php');
}

//Benutzernamen auslesen
$profileInfo = $db->first("SELECT userid,username,pub_usegb,pub_profileforfriends FROM ".PRE."_user WHERE userid='".$_REQUEST['id']."' LIMIT 1");
list($userid,$username,$usegb,$friendonly) = $profileInfo;
$apx->tmpl->assign('USERID',$userid);
$apx->tmpl->assign('USERNAME',replace($username));

//Nur für Freunde
if ( $friendonly && !$user->is_buddy_of($userid) && $user->info['userid']!=$userid && $user->info['groupid']!=1 ) {
	message($apx->lang->get('MSG_FRIENDSONLY'));
	require('lib/_end.php');
}

//Links zu anderen Funktionen
user_assign_profile_links($apx->tmpl, $profileInfo);

/////////////// EINZELNER EINTRAG
if ( $_REQUEST['blogid'] ) {
	headline($apx->lang->get('HEADLINE_BLOG'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
	titlebar($apx->lang->get('HEADLINE_BLOG'));
	
	//Verwendete Variablen auslesen
	$parse = $apx->tmpl->used_vars('blog_detail');
	
	$res = $db->first("SELECT * FROM ".PRE."_user_blog WHERE userid='".$_REQUEST['id']."' AND id='".$_REQUEST['blogid']."' LIMIT 1");
	if ( !$res['id'] ) die('access denied!');
	
	//Link
	$link = mklink(
		'user.php?action=blog&amp;id='.$_REQUEST['id'].'&amp;blogid='.$res['id'],
		'user,blog,'.$_REQUEST['id'].',id'.$res['id'].urlformat($res['title']).'.html'
	);
	
	//Text
	$text = $res['text'];
	$text = badwords($text);
	$text = replace($text,1);
	$text = dbsmilies($text);
	$text = dbcodes($text);
	
	$apx->tmpl->assign('ID', $res['id']);
	$apx->tmpl->assign('TITLE', replace($res['title']));
	$apx->tmpl->assign('TEXT', $text);
	$apx->tmpl->assign('LINK', $link);
	$apx->tmpl->assign('TIME', $res['time']);
	
	//Kommentare
	if ( $apx->is_module('comments') && $res['allowcoms'] ) {
		require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
		$coms=new comments('userblog',$res['id']);
		$coms->assign_comments($parse);
	}
	
	//Besucher aufzeichnen und ausgeben
	if ( in_array('VISITOR',$parse) ) {
		if ( $userid!=$user->info['userid'] ) {
			user_count_visit('blog',$_REQUEST['id']);
		}
		if ( !$set['user']['visitorself'] || $userid==$user->info['userid'] ) {
			user_assign_visitors('blog',$_REQUEST['id'],$apx->tmpl,$parse);
		}
	}
	
	$link_report = "javascript:popupwin('user.php?action=report&amp;contentid=blogentry:".$_REQUEST['blogid']."',500,300);";
	$apx->tmpl->assign('LINK_REPORT',$link_report);
	
	$apx->tmpl->parse('blog_detail');
}

/////////////// BLOGEINTRÄGE AUFLISTEN
else {
	headline($apx->lang->get('HEADLINE_BLOG'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
	titlebar($apx->lang->get('HEADLINE_BLOG'));
	
	//Verwendete Variablen auslesen
	$parse = $apx->tmpl->used_vars('blog');
	
	//Seitenzahlen
	list($count) = $db->first("SELECT count(id) FROM ".PRE."_user_blog WHERE userid='".$_REQUEST['id']."'");
	pages(
		mklink(
			'user.php?action=blog&amp;id='.$_REQUEST['id'],
			'user,blog,'.$_REQUEST['id'].',{P}.html'
		),
		$count,
		$set['user']['blog_epp']
	);
	
	//Einträge auslesen
	$data = $db->fetch("SELECT * FROM ".PRE."_user_blog WHERE userid='".$_REQUEST['id']."' ORDER BY time DESC".getlimit($set['user']['blog_epp']));
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link = mklink(
				'user.php?action=blog&amp;id='.$_REQUEST['id'].'&amp;blogid='.$res['id'],
				'user,blog,'.$_REQUEST['id'].',id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			//Text
			$text = $res['text'];
			$text = badwords($text);
			$text = replace($text,1);
			$text = dbsmilies($text);
			$text = dbcodes($text);
			
			$tabledata[$i]['ID'] = $res['id'];
			$tabledata[$i]['TITLE'] = replace($res['title']);
			$tabledata[$i]['TEXT'] = $text;
			$tabledata[$i]['LINK'] = $link;
			$tabledata[$i]['TIME'] = $res['time'];
			
			$link_report = "javascript:popupwin('user.php?action=report&amp;contentid=blogentry:".$res['id']."',500,300);";
			$tabledata[$i]['LINK_REPORT'] = $link_report;
			
			//Kommentare
			if ( $apx->is_module('comments') && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('userblog',$res['id']);
				else $coms->mid=$res['id'];
				
				$link = mklink(
					'user.php?action=blog&amp;id='.$_REQUEST['id'].'&amp;blogid='.$res['id'],
					'user,blog,'.$_REQUEST['id'].',id'.$res['id'].urlformat($res['title']).'.html'
				);
				
				$tabledata[$i]['COMMENT_COUNT']=$coms->count();
				$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
				$tabledata[$i]['DISPLAY_COMMENTS']=1;
				if ( in_template(array('ENTRY.COMMENT_LAST_USERID','ENTRY.COMMENT_LAST_NAME','ENTRY.COMMENT_LAST_TIME'),$parse) ) {
					$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
					$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
					$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
		}
	}
	
	//Besucher aufzeichnen und ausgeben
	if ( in_array('VISITOR',$parse) ) {
		if ( $userid!=$user->info['userid'] ) {
			user_count_visit('blog',$_REQUEST['id']);
		}
		if ( !$set['user']['visitorself'] || $userid==$user->info['userid'] ) {
			user_assign_visitors('blog',$_REQUEST['id'],$apx->tmpl,$parse);
		}
	}
	
	$apx->tmpl->assign('ENTRY',$tabledata);
	
	$link_report = "javascript:popupwin('user.php?action=report&amp;contentid=blog:".$_REQUEST['id']."',500,300);";
	$apx->tmpl->assign('LINK_REPORT',$link_report);
	
	$apx->tmpl->parse('blog');
}

?>