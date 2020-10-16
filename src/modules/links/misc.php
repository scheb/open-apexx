<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



require_once(BASEDIR.getmodulepath('links').'functions.php');



//Kommentar-Popup
function misc_links_comments() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->tmpl->loaddesign('blank');
	links_showcomments($_REQUEST['id']);
}



//Link aufrufen
function misc_gotolink() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	$res=$db->first("SELECT id,url FROM ".PRE."_links WHERE ( id='".$_REQUEST['id']."' AND ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter()." ) LIMIT 1");
	if ( !$res['id'] ) die('entry not found!');
	
	//Counter
	$db->query("UPDATE ".PRE."_links SET hits=hits+1 WHERE id='".$_REQUEST['id']."' LIMIT 1");
	
	//Redirect
	header("HTTP/1.1 301 Moved Permanently");
	header('location:'.$res['url']);
	exit;
}



//Linkfeed ausgeben
function misc_linksfeed() {
	global $set,$db,$apx;
	$apx->tmpl->loaddesign('blank');
	header('Content-type: application/rss+xml');
	
	//Baum holen, wenn nur eine bestimmte Kategorie gezeigt werden soll
	$cattree=links_tree($_REQUEST['catid']);
	
	$data=$db->fetch("SELECT a.id,a.catid,a.title,a.text,a.starttime,a.top,b.username,b.email,b.pub_hidemail FROM ".PRE."_links AS a LEFT JOIN ".PRE."_user AS b USING (userid) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree),"AND catid IN (".@implode(',',$cattree).")")." ".section_filter()." ) ORDER BY starttime DESC LIMIT 20");
	if ( count($data) ) {
		
		//Kategorien auslesen
		$catids=get_ids($data,'catid');
		if ( count($catids) ) {
			$catdata=$db->fetch("SELECT id,title FROM ".PRE."_links_cat WHERE id IN (".implode(',',$catids).")");
			if ( count($catdata) ) {
				foreach ( $catdata AS $catres ) $catinfo[$catres['id']]=$catres;
			}
		}
		
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link=mklink(
				'links.php?id='.$res['id'],
				'links,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['TITLE']=rss_replace($res['title']);
			$tabledata[$i]['URL']=$res['url'];
			$tabledata[$i]['TIME']=date('r',$res['starttime']); //Kein TIMEDIFF weil Zeitverschiebung mit angegeben!
			$tabledata[$i]['TEXT']=rss_replace(preg_replace('#{IMAGE\(([0-9]+)\)}#s','',$res['text']));
			$tabledata[$i]['CATTITLE']=rss_replace($catinfo[$res['catid']]['title']);
			$tabledata[$i]['LINK']=HTTP_HOST.$link;
			$tabledata[$i]['TOP']=$res['top'];
		}
	}
	
	$apx->tmpl->assign('WEBSITENAME',$set['main']['websitename']);
	$apx->tmpl->assign('LINK',$tabledata);
	$apx->tmpl->parse('rss','links');
}

?>