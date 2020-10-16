<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



require_once(BASEDIR.getmodulepath('videos').'functions.php');



//Kommentar-Popup
function misc_videos_comments() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->tmpl->loaddesign('blank');
	videos_showcomments($_REQUEST['id']);
}



//Datei aufrufen
function misc_videofile() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( $set['videos']['regonly'] && !$user->info['userid'] ) die('video only for registered users!');
	
	$apx->lang->drop('detail','videos');
	
	//Secure Check
	$res=$db->first("SELECT id,title,file,regonly,`limit`,password,source FROM ".PRE."_videos WHERE ( id='".$_REQUEST['id']."' ".iif(!$user->is_team_member(),"AND ( '".time()."' BETWEEN starttime AND endtime )")." ".section_filter()." ) LIMIT 1");
	if ( !$res['id'] ) die('file not found!');
	if ( $res['regonly'] && !$user->info['userid'] ) die('video only for registered users!');
	if ( videos_limit_is_reached($res['id'],$res['limit']) ) message($apx->lang->get('MSG_LIMITREACHED'),'back');
	
	if ( $res['password'] && $_POST['password']!=$res['password'] ) {
		tmessage('pwdrequired',array('ID'=>$_REQUEST['id'],'SECHASH'=>$_REQUEST['sechash']),'videos');
	}
		
	$checkhash=md5($_SERVER['HTTP_HOST'].$res['file'].date('Y/m/d',time()-TIMEDIFF));
	if ( $checkhash!=$_REQUEST['sechash'] ) {
		header("HTTP/1.1 301 Moved Permanently");
		header('location:'.str_replace('&amp;', '&', mklink(
			'videos.php?id='.$_REQUEST['id'],
			'videos,id'.$_REQUEST['id'].urlformat($res['title']).'.html'
		)));
		exit;
	}
	
	//Datei downloadbar?
	if ( !in_array($res['source'], array('apexx', 'external')) ) {
		header("HTTP/1.1 404 Not Found");
		exit;
	}
	
	//Statistik
	$thefsize=videos_filesize($res);
	videos_insert_stats($res['id'],$thefsize,$res['source']=='apexx');
	
	//Datei senden
	if ( $res['source']=='external' ) {
		header("HTTP/1.1 301 Moved Permanently");
		header('location:'.$res['file']);
		exit;
	}
	else {
		header("HTTP/1.1 301 Moved Permanently");
		header('location:'.HTTPDIR.getpath('uploads').$res['file']);
		exit;
	}
}



//Videofeed ausgeben
function misc_videosfeed() {
	global $set,$db,$apx;
	$apx->tmpl->loaddesign('blank');
	header('Content-type: application/rss+xml');
	
	//Baum holen, wenn nur eine bestimmte Kategorie gezeigt werden soll
	$cattree=videos_tree($_REQUEST['catid']);
	
	$data=$db->fetch("SELECT a.id,a.catid,a.title,a.text,a.starttime,a.top,b.username,b.email,b.pub_hidemail FROM ".PRE."_videos AS a LEFT JOIN ".PRE."_user AS b USING (userid) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree),"AND catid IN (".@implode(',',$cattree).")")." ".section_filter()." ) ORDER BY starttime DESC LIMIT 20");
	if ( count($data) ) {
		
		//Kategorien auslesen
		$catids=get_ids($data,'catid');
		if ( count($catids) ) {
			$catdata=$db->fetch("SELECT id,title FROM ".PRE."_videos_cat WHERE id IN (".implode(',',$catids).")");
			if ( count($catdata) ) {
				foreach ( $catdata AS $catres ) $catinfo[$catres['id']]=$catres;
			}
		}
		
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link=mklink(
				'videos.php?id='.$res['id'],
				'videos,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['TITLE']=rss_replace($res['title']);
			$tabledata[$i]['TIME']=date('r',$res['starttime']); //Kein TIMEDIFF weil Zeitverschiebung mit angegeben!
			$tabledata[$i]['TEXT']=rss_replace(preg_replace('#{IMAGE\(([0-9]+)\)}#s','',$res['text']));
			$tabledata[$i]['CATTITLE']=rss_replace($catinfo[$res['catid']]['title']);
			$tabledata[$i]['LINK']=HTTP_HOST.$link;
			$tabledata[$i]['USERNAME']=rss_replace($res['username']);
			$tabledata[$i]['EMAIL']=rss_replace(iif(!$res['pub_hidemail'],$res['email']));
			$tabledata[$i]['EMAIL_ENCRYPTED']=rss_replace(iif(!$res['pub_hidemail'],cryptMail($res['email'])));
			$tabledata[$i]['TOP']=$res['top'];
		}
	}
	
	$apx->tmpl->assign('WEBSITENAME',$set['main']['websitename']);
	$apx->tmpl->assign('VIDEO',$tabledata);
	$apx->tmpl->parse('rss','videos');
}

?>