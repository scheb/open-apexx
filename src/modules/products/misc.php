<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2009, Christian Scheb            |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| THIS SOFTWARE IS NOT FREE! MAKE SURE YOU OWN A VALID LICENSE! |
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/


# GAMES CLASS
# ===========

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Kommentare im Popup
function misc_products_comments() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->tmpl->loaddesign('blank');
	
	require_once(BASEDIR.getmodulepath('calendar').'functions.php');
	products_showcomments($_REQUEST['id']);
}



//Feed ausgeben
function misc_productsfeed() {
	global $set,$db,$apx;
	$apx->tmpl->loaddesign('blank');
	header('Content-type: application/rss+xml');
	
	$apx->lang->drop('types', 'products');
	
	$type = $_REQUEST['type'];
	$alltypes = array('normal','game','software','hardware','music','movie','book');
	if ( !in_array($type,$alltypes) ) $type='';
	
	$data = $db->fetch("SELECT * FROM ".PRE."_products WHERE active='1' ".iif($type," AND type='".$type."'")." ORDER BY addtime DESC LIMIT 20");
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link=mklink(
				'products.php?id='.$res['id'],
				'products,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['TITLE']=rss_replace($res['title']);
			$tabledata[$i]['TIME']=date('r',$res['starttime']); //Kein TIMEDIFF weil Zeitverschiebung mit angegeben!
			$tabledata[$i]['TEXT']=rss_replace(preg_replace('#{IMAGE\(([0-9]+)\)}#s','',$res['text']));
			$tabledata[$i]['TYPE']=$res['type'];
			$tabledata[$i]['LINK']=HTTP_HOST.$link;
		}
	}
	
	$apx->tmpl->assign('WEBSITENAME',$set['main']['websitename']);
	$apx->tmpl->assign('PRODUCT',$tabledata);
	$apx->tmpl->parse('rss','products');
}

?>