<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Codes auflisten
function misc_codes() {
	global $set,$db,$apx;
	$apx->lang->drop('showcodes','main');
	$apx->tmpl->loaddesign('blank');
	
	if ( count($set['main']['codes']) ) {
		foreach ( $set['main']['codes'] AS $res ) {
			++$i;
			$codedata[$i]['TAG']=$res['code'];
			$codedata[$i]['EXAMPLE']=$res['example'];
			$codedata[$i]['ALLOWSIG']=$res['allowsig'];
		}
	}
	
	$apx->tmpl->assign('CODE',$codedata);
	$apx->tmpl->parse('showcodes','main');
}



//Smilies auflisten
function misc_smilies() {
	global $set,$db,$apx;
	$apx->lang->drop('showsmilies','main');
	$apx->tmpl->loaddesign('blank');
	
	if ( count($set['main']['smilies']) ) {
		foreach ( $set['main']['smilies'] AS $res ) {
			++$i;
			$smiledata[$i]['CODE']=$res['code'];
			$smiledata[$i]['INSERTCODE']=addslashes($res['code']);
			$smiledata[$i]['IMAGE']=$res['file'];
			$smiledata[$i]['DESCRIPTION']=$res['description'];
		}
	}

	$apx->tmpl->assign('SMILEY',$smiledata);
	$apx->tmpl->parse('showsmileys','main');
}



//Bild im Popup zeigen
function misc_picture() {
	global $set,$db,$apx;
	if ( !$_REQUEST['pic'] ) die('missing PIC!');
	
	$apx->tmpl->loaddesign('blank');
	$apx->tmpl->assign('IMAGE',getpath('uploads').$_REQUEST['pic']);
	$apx->tmpl->parse('showpic','main');
}



//Redirect
function misc_redirect() {
	global $set,$db,$apx;
	if ( !$_REQUEST['url'] ) return;
	if ( strlen($_REQUEST['url'])<100 ) $urltext=$_REQUEST['url'];
	else $urltext=substr($_REQUEST['url'],0,80).' ... '.substr($_REQUEST['url'],-15);
	message($apx->lang->get('CORE_REDIRECT').' '.$urltext,$_REQUEST['url']);
}


?>