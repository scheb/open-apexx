<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Kommentare (Bilder)
function misc_gallery_comments() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->tmpl->loaddesign('blank');
	
	require_once(BASEDIR.getmodulepath('gallery').'functions.php');
	gallery_showcomments($_REQUEST['id']);
}



//Kommentare (Galerien)
function misc_galleryself_comments() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->tmpl->loaddesign('blank');
	
	require_once(BASEDIR.getmodulepath('gallery').'functions.php');
	galleryself_showcomments($_REQUEST['id']);
}



//Galleryfeed ausgeben
function misc_galleryfeed() {
	global $set,$db,$apx;
	$apx->tmpl->loaddesign('blank');
	header('Content-type: application/rss+xml');
	
	$data=$db->fetch("SELECT id,title,description,starttime FROM ".PRE."_gallery WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter()." ) ORDER BY starttime DESC LIMIT 20");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link=mklink(
				'gallery.php?id='.$res['id'],
				'gallery,list'.$res['id'].',1'.urlformat($res['title']).'.html'
			);
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['TITLE']=rss_replace($res['title']);
			$tabledata[$i]['TIME']=date('r',$res['starttime']); //Kein TIMEDIFF weil Zeitverschiebung mit angegeben!
			$tabledata[$i]['DESCRIPTION']=rss_replace($res['description']);
			$tabledata[$i]['LINK']=HTTP_HOST.$link;
		}
	}
	
	$apx->tmpl->assign('WEBSITENAME',$set['main']['websitename']);
	$apx->tmpl->assign('GALLERY',$tabledata);
	$apx->tmpl->parse('rss','gallery');
}


?>