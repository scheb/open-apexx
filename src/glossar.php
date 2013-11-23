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


define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once(BASEDIR.getmodulepath('glossar').'functions.php');

$apx->module('glossar');
$apx->lang->drop('global');

headline($apx->lang->get('HEADLINE'),mklink('glossar.php','glossar.html'));
titlebar($apx->lang->get('HEADLINE'));
$_REQUEST['catid']=(int)$_REQUEST['catid'];
$_REQUEST['id']=(int)$_REQUEST['id'];


////////////////////////////////////////////////////////////////////////////////// NUR KOMMENTARE

if ( $_REQUEST['id'] && $_REQUEST['comments'] ) {
	$res=$db->first("SELECT title FROM ".PRE."_glossar WHERE ( id='".$_REQUEST['id']."' ".section_filter()." ) LIMIT 1");
	titlebar($apx->lang->get('HEADLINE').': '.$res['title']);
	
	glossar_showcomments($_REQUEST['id']);
}



///////////////////////////////////////////////////////////////////////////////////////// DETAILS
if ( $_REQUEST['id'] ) {
	$apx->lang->drop('detail');
	
	//Counter
	$db->query("UPDATE ".PRE."_glossar SET hits=hits+1 WHERE id='".$_REQUEST['id']."' LIMIT 1");
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars('detail');
	
	//Begriff-Info
	$res=$db->first("SELECT * FROM ".PRE."_glossar WHERE ( id='".$_REQUEST['id']."' ".iif(!$user->is_team_member(),"AND starttime!='0'")." ) LIMIT 1");
	if ( !$res['id'] ) filenotfound();
	
	//Kategorie-Info
	$catinfo=$db->first("SELECT * FROM ".PRE."_glossar_cat WHERE id='".$res['catid']."' LIMIT 1");
	$catlink=mklink(
		'glossar.php?catid='.$catinfo['id'],
		'glossar,'.$catinfo['id'].',0,1'.urlformat($catinfo['title']).'.html'
	);
	
	//Headline
	headline($catinfo['title'],$catlink);
	titlebar($apx->lang->get('HEADLINE').': '.$res['title']);
	
	//Link
	$link=mklink(
		'glossar.php?id='.$res['id'],
		'glossar,id'.$res['id'].urlformat($res['title']).'.html'
	);
	
	//Tags
	if ( in_array('TAG',$parse) || in_array('TAG_IDS',$parse) || in_array('KEYWORDS',$parse) ) {
		list($tagdata, $tagids, $keywords) = glossar_tags($res['id']);
	}
	
	//Text
	$text = mediamanager_inline($res['text']);
	$text = glossar_highlight($text,false,$res['id']);
	
	$apx->tmpl->assign('LETTER',glossar_letter($res['title']));
	$apx->tmpl->assign('ID',$res['id']);
	$apx->tmpl->assign('TITLE',$res['title']);
	$apx->tmpl->assign('TEXT',$text);
	$apx->tmpl->assign_static('META_DESCRIPTION',replace($res['meta_description']));
	$apx->tmpl->assign('SPELLING',$res['spelling']);
	$apx->tmpl->assign('LINK',$link);
	$apx->tmpl->assign('HITS',number_format($res['hits'],0,'','.'));
	$apx->tmpl->assign('TIME',$res['starttime']);
	
	//Tags
	$apx->tmpl->assign('TAG_IDS', $tagids);
	$apx->tmpl->assign('TAG', $tagdata);
	$apx->tmpl->assign('KEYWORDS', $keywords);
	
	//Kategorie
	$apx->tmpl->assign('CATID',$res['catid']);
	$apx->tmpl->assign('CATTITLE',$catinfo['title']);
	$apx->tmpl->assign('CATTEXT',$catinfo['text']);
	$apx->tmpl->assign('CATICON',$catinfo['icon']);
	$apx->tmpl->assign('CATLINK',$catlink);
	
	//Kommentare
	if ( $apx->is_module('comments') && $set['glossar']['coms'] && $res['allowcoms'] ) {
		require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
		$coms=new comments('glossar',$res['id']);
		$coms->assign_comments($parse);
	}
	
	//Bewertungen
	if ( $apx->is_module('ratings') && $set['glossar']['ratings'] && $res['allowrating'] ) {
		require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
		$rate=new ratings('glossar',$res['id']);
		$rate->assign_ratings($parse);
	}
	
	$apx->tmpl->parse('detail');
	require('lib/_end.php');
}



///////////////////////////////////////////////////////////////////////////////////////// BEGRIFFE EINES THEMENGEBIETS

if ( $_REQUEST['catid'] ) {
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars('index');
	
	//Letter prüfen
	if ( !preg_match('#^([a-z]|spchar)$#',$_REQUEST['letter']) ) {
		$_REQUEST['letter']=0;
	}
	
	//Buchstabenfilter
	if ( $_REQUEST['letter'] ) {
		if ( $_REQUEST['letter']=='spchar' ) $letterfilter=" AND title NOT REGEXP(\"^[a-zA-Z]\")";
		else $letterfilter=" AND title LIKE '".$_REQUEST['letter']."%'";
	}
	
	//Kategorie-Info auslesen
	$catinfo=$db->first("SELECT * FROM ".PRE."_glossar_cat WHERE id='".$_REQUEST['catid']."' LIMIT 1");
	$catlink=mklink(
		'glossar.php?catid='.$catinfo['id'],
		'glossar,'.$catinfo['id'].',0,1'.urlformat($catinfo['title']).'.html'
	);
	
	//Headline
	headline($catinfo['title'],$catlink);
	titlebar($apx->lang->get('HEADLINE').': '.$catinfo['title']);
	
	//Seitenzahlen
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_glossar WHERE ( catid='".$_REQUEST['catid']."' AND starttime!=0 ".$letterfilter." )");
	pages(
		mklink(
			'glossar.php?catid='.$catinfo['id'].'&amp;letter='.$_REQUEST['letter'],
			'glossar,'.$catinfo['id'].','.$_REQUEST['letter'].',{P}'.urlformat($catinfo['title']).'.html'
		),
		$count,
		$set['glossar']['epp']
	);
	
	//Begriffe auslesen
	$data=$db->fetch("SELECT * FROM ".PRE."_glossar WHERE ( catid='".$_REQUEST['catid']."' AND starttime!=0 ".$letterfilter." ) ORDER BY title ASC ".getlimit($set['glossar']['epp']));
	$index=array();
	if ( count($data) ) {
		
		//Nach Buchstaben sortieren
		$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ#';
		for ( $i=0; $i<strlen($letters); $i++ ) {
			$index[$letters[$i]] = array();
		}
		foreach ( $data AS $res ) {
			$letter=glossar_letter($res['title']);
			$index[$letter][]=$res;
		}
		
		//Index erstellen
		foreach ( $index AS $letter => $data ) {
			
			//Link: Nur Begriffe mit diesem Buchstaben
			$letterlink=mklink(
				'glossar.php?catid='.$_REQUEST['catid'].'&amp;letter='.iif($letter=='#','spchar',strtolower($letter)),
				'glossar,'.$_REQUEST['catid'].','.iif($letter=='#','spchar',strtolower($letter)).',1'.urlformat($catinfo['title']).'.html'
			);
			
			foreach ( $data AS $res ) {
				++$i;
				
				//Link
				$link=mklink(
					'glossar.php?id='.$res['id'],
					'glossar,id'.$res['id'].urlformat($res['title']).'.html'
				);
				
				//Text
				$text = '';
				if ( in_array('INDEX.TEXT',$parse) ) {
					$text = mediamanager_inline($res['text']);
					$text = glossar_highlight($text);
				}
				
				//Tags
				if ( in_array('INDEX.TAG',$parse) || in_array('INDEX.TAG_IDS',$parse) || in_array('INDEX.KEYWORDS',$parse) ) {
					list($tagdata, $tagids, $keywords) = glossar_tags($res['id']);
				}
				
				$tabledata[$i]['LETTER']=$letter;
				$tabledata[$i]['LETTERLINK']=$letterlink;
				
				$tabledata[$i]['TITLE']=$res['title'];
				$tabledata[$i]['TEXT']=$text;
				$tabledata[$i]['SPELLING']=$res['spelling'];
				$tabledata[$i]['LINK']=$link;
				$tabledata[$i]['TIME']=$res['starttime'];
				$tabledata[$i]['HITS']=number_format($res['hits'],0,'','.');
				
				//Tags
				$tabledata[$i]['TAG']=$tagdata;
				$tabledata[$i]['TAG_IDS']=$tagids;
				$tabledata[$i]['KEYWORDS']=$keywords;
				
				//Kommentare
				if ( $apx->is_module('comments') && $set['glossar']['coms'] && $res['allowcoms'] ) {
					require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
					if ( !isset($coms) ) $coms=new comments('glossar',$res['id']);
					else $coms->mid=$res['id'];
					
					$link=mklink(
						'glossar.php?id='.$res['id'],
						'glossar,id'.$res['id'].urlformat($res['title']).'.html'
					);
					
					$tabledata[$i]['COMMENT_COUNT']=$coms->count();
					$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
					$tabledata[$i]['DISPLAY_COMMENTS']=1;
					if ( in_template(array('INDEX.COMMENT_LAST_USERID','INDEX.COMMENT_LAST_NAME','INDEX.COMMENT_LAST_TIME'),$parse) ) {
						$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
						$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
						$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
					}
				}
				
				//Bewertungen
				if ( $apx->is_module('ratings') && $set['glossar']['ratings'] && $res['allowrating'] ) {
					require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
					if ( !isset($rate) ) $rate=new ratings('glossar',$res['id']);
					else $rate->mid=$res['id'];
					
					$tabledata[$i]['RATING']=$rate->display();
					$tabledata[$i]['RATING_VOTES']=$rate->count();
					$tabledata[$i]['DISPLAY_RATING']=1;
				}
				
			}
		}
	}
	
	//Kategorie
	$apx->tmpl->assign('CATID',$catinfo['id']);
	$apx->tmpl->assign('CATTITLE',$catinfo['title']);
	$apx->tmpl->assign('CATTEXT',$catinfo['text']);
	$apx->tmpl->assign('CATICON',$catinfo['icon']);
	$apx->tmpl->assign('CATLINK',$catlink);
	$apx->tmpl->assign('COUNT',$count);
	
	//Index
	$apx->tmpl->assign('INDEX',$tabledata);
	$apx->tmpl->parse('index');
	
	require('lib/_end.php');
}



///////////////////////////////////////////////////////////////////////////////////////// ALLE THEMENGEBIETE

//Verwendete Variablen auslesen
$parse=$apx->tmpl->used_vars('categories');

$data=$db->fetch("SELECT * FROM ".PRE."_glossar_cat ORDER BY title ASC");
if ( count($data) ) {
	foreach ( $data AS $res ) {
		++$i;
		
		$link=mklink(
			'glossar.php?catid='.$res['id'],
			'glossar,'.$res['id'].',0,1'.urlformat($res['title']).'.html'
		);
		
		//Enthaltene Begriffe
		if ( in_array('CATEGORY.COUNT',$parse) ) {
			list($count)=$db->first("SELECT count(id) FROM ".PRE."_glossar WHERE ( catid='".$res['id']."' AND starttime!=0 )");
		}
		
		$tabledata[$i]['ID']=$res['id'];
		$tabledata[$i]['TITLE']=$res['title'];
		$tabledata[$i]['ICON']=$res['icon'];
		$tabledata[$i]['TEXT']=$res['text'];
		$tabledata[$i]['LINK']=$link;
		$tabledata[$i]['COUNT']=$count;
	}
}

$apx->tmpl->assign('CATEGORY',$tabledata);
$apx->tmpl->parse('categories');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>