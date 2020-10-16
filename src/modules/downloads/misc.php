<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



require_once(BASEDIR.getmodulepath('downloads').'functions.php');



//Kommentar-Popup
function misc_downloads_comments() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->tmpl->loaddesign('blank');
	downloads_showcomments($_REQUEST['id']);
}



//Datei aufrufen
function misc_downloadfile() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( $set['downloads']['regonly'] && !$user->info['userid'] ) die('download only for registered users!');
	
	$apx->lang->drop('detail','downloads');
	
	//Secure Check
	$res=$db->first("SELECT id,title,file,local,filesize,regonly,".PRE."_downloads.limit,password FROM ".PRE."_downloads WHERE ( id='".$_REQUEST['id']."' ".iif(!$user->is_team_member(),"AND ( '".time()."' BETWEEN starttime AND endtime )")." ".section_filter()." ) LIMIT 1");
	if ( !$res['id'] ) die('file not found!');
	if ( $res['regonly'] && !$user->info['userid'] ) die('download only for registered users!');
	if ( downloads_limit_is_reached($res['id'],$res['limit']) ) message($apx->lang->get('MSG_LIMITREACHED'),'back');
	
	if ( $res['password'] && $_POST['password']!=$res['password'] ) {
		tmessage('pwdrequired',array('ID'=>$_REQUEST['id'],'SECHASH'=>$_REQUEST['sechash']),'downloads');
	}
		
	$checkhash=md5($_SERVER['HTTP_HOST'].$res['file'].date('Y/m/d',time()-TIMEDIFF));
	if ( $checkhash!=$_REQUEST['sechash'] ) {
		header("HTTP/1.1 301 Moved Permanently");
		header('location:'.str_replace('&amp;', '&', mklink(
			'downloads.php?id='.$_REQUEST['id'],
			'downloads,id'.$_REQUEST['id'].urlformat($res['title']).'.html'
		)));
		exit;
	}
	
	//Statistik
	$thefsize=downloads_filesize($res);
	downloads_insert_stats($res['id'],$thefsize,$res['local']);
	
	//Datei senden
	if ( !$res['local'] ) {
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



//Mirror aufrufen
function misc_downloadmirror() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	$_REQUEST['mirror']=(int)$_REQUEST['mirror'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	if ( $set['downloads']['regonly'] && !$user->info['userid'] ) die('download only for registered users!');

	$res=$db->first("SELECT id,mirrors,regonly FROM ".PRE."_downloads WHERE ( id='".$_REQUEST['id']."' ".iif(!$user->is_team_member(),"AND ( '".time()."' BETWEEN starttime AND endtime )")." ".section_filter()." ) LIMIT 1");
	if ( !$res['id'] ) die('file not found!');
	if ( $res['regonly'] && !$user->info['userid'] ) die('download only for registered users!');
	
	$mirrors=unserialize($res['mirrors']);
	if ( !is_array($mirrors) || !isset($mirrors[$_REQUEST['mirror']]) )	die('invalid MIRROR or MIRROR-ID!');
	
	//Statistik
	if ( $set['downloads']['mirrorstats'] ) {
		$thefsize=downloads_filesize($res);
		downloads_insert_stats($res['id'],$thefsize,false);
	}
	
	header("HTTP/1.1 301 Moved Permanently");
	header('location:'.$mirrors[$_REQUEST['mirror']]['url']);
}



//Downloadfeed ausgeben
function misc_downloadsfeed() {
	global $set,$db,$apx;
	$apx->tmpl->loaddesign('blank');
	header('Content-type: application/rss+xml');
	
	//Baum holen, wenn nur eine bestimmte Kategorie gezeigt werden soll
	$cattree=downloads_tree($_REQUEST['catid']);
	
	$data=$db->fetch("SELECT a.id,a.catid,a.title,a.text,a.starttime,a.top,b.username,b.email,b.pub_hidemail FROM ".PRE."_downloads AS a LEFT JOIN ".PRE."_user AS b USING (userid) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree),"AND catid IN (".@implode(',',$cattree).")")." ".section_filter()." ) ORDER BY starttime DESC LIMIT 20");
	if ( count($data) ) {
		
		//Kategorien auslesen
		$catids=get_ids($data,'catid');
		if ( count($catids) ) {
			$catdata=$db->fetch("SELECT id,title FROM ".PRE."_downloads_cat WHERE id IN (".implode(',',$catids).")");
			if ( count($catdata) ) {
				foreach ( $catdata AS $catres ) $catinfo[$catres['id']]=$catres;
			}
		}
		
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link=mklink(
				'downloads.php?id='.$res['id'],
				'downloads,id'.$res['id'].urlformat($res['title']).'.html'
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
	$apx->tmpl->assign('DOWNLOAD',$tabledata);
	$apx->tmpl->parse('rss','downloads');
}

?>