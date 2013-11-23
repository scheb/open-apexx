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

require_once(BASEDIR.getmodulepath('news').'functions.php');

$apx->module('news');
$apx->lang->drop('news');
headline($apx->lang->get('HEADLINE_ARCHIVE'),mklink('newsarchive.php','newsarchive.html'));
titlebar($apx->lang->get('HEADLINE_ARCHIVE'));

$recent=news_recent();
$filter=iif(count($recent) && !$set['news']['archiveall'],'AND NOT ( id IN ('.implode(',',$recent).') )');
$_REQUEST['id']=(int)$_REQUEST['id'];


//////////////////////////////////////////////////////////////////////////////////////////////////////// SUCHE

if ( $_REQUEST['action']=='search' ) {
	$apx->lang->drop('search');
	
	//ERGEBNIS ANZEIGEN
	if ( $_REQUEST['searchid'] ) {
		titlebar($apx->lang->get('HEADLINE_SEARCH'));
		
		//Suchergebnis auslesen
		$resultIds = '';
		list($resultIds) = getSearchResult('news', $_REQUEST['searchid']);
		
		//Keine Ergebnisse
		if ( !$resultIds ) {
			message($apx->lang->get('MSG_NORESULT'),'javascript:history.back();');
			require('lib/_end.php');
		}
		
		//Verwendete Variablen auslesen
		$parse=$apx->tmpl->used_vars('search_result');
		
		//Seitenzahlen generieren
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ".time()." BETWEEN starttime AND endtime AND id IN (".implode(', ', $resultIds).") ".section_filter()." )");
		pages(
			mklink(
				'newsarchive.php?action=search&searchid='.$_REQUEST['searchid'],
				'newsarchive.html?action=search&searchid='.$_REQUEST['searchid']
			),
			$count,
			$set['news']['searchepp']
		);
		
		//News ausgeben
		$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ".time()." BETWEEN starttime AND endtime AND id IN (".implode(', ', $resultIds).") ".section_filter()." ) ORDER BY starttime DESC ".getlimit($set['news']['searchepp']));
		
		//Kategorien auslesen
		if ( in_array('NEWS.CATTITLE',$parse) || in_array('NEWS.CATICON',$parse) || in_array('NEWS.CATLINK',$parse) ) {
			$catinfo=news_catinfo(get_ids($data,'catid'));
		}
		
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link=mklink(
				'news.php?id='.$res['id'],
				'news,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			//Newspic
			if ( in_array('NEWS.PICTURE',$parse) || in_array('NEWS.PICTURE_POPUP',$parse) || in_array('NEWS.PICTURE_POPUPPATH',$parse) ) {
				list($picture,$picture_popup,$picture_popuppath)=news_newspic($res['newspic']);
			}
			
			//Username + eMail
			if ( $res['userid'] ) {
				$username=replace($res['username']);
				$email=replace(iif(!$res['pub_hidemail'],$res['email']));
			}
			else {
				$username=replace($res['send_username']);
				$email=replace($res['send_email']);
			}
			
			//Datehead
			if ( $laststamp!=date('Y/m/d',$res['starttime']-TIMEDIFF) ) {
				$tabledata[$i]['DATEHEAD']=$res['starttime'];
			}
			
			//Links
			if ( in_array('NEWS.RELATED',$parse) ) {
				$tabledata[$i]['RELATED']=news_links($res['links']);
			}
			
			//Tags
			if ( in_array('NEWS.TAG',$parse) || in_array('NEWS.TAG_IDS',$parse) || in_array('NEWS.KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = news_tags($res['id']);
			}
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['SECID']=$res['secid'];
			$tabledata[$i]['TITLE']=$res['title'];
			$tabledata[$i]['SUBTITLE']=$res['subtitle'];
			$tabledata[$i]['LINK']=$link;
			if ( in_array('NEWS.TEASER',$parse) && $set['news']['teaser'] ) $tabledata[$i]['TEASER']=mediamanager_inline($res['teaser']);
			if ( in_array('NEWS.TEXT',$parse) ) $tabledata[$i]['TEXT']=mediamanager_inline($res['text']);
			$tabledata[$i]['TIME']=$res['starttime'];
			$tabledata[$i]['PICTURE']=$picture;
			$tabledata[$i]['PICTURE_POPUP']=$picture_popup;
			$tabledata[$i]['PICTURE_POPUPPATH']=$picture_popuppath;
			$tabledata[$i]['USERID']=$res['userid'];
			$tabledata[$i]['USERNAME']=replace($username);
			$tabledata[$i]['EMAIL']=replace($email);
			$tabledata[$i]['EMAIL_ENCRYPTED']=replace(cryptMail($email));
			$tabledata[$i]['HITS']=number_format($res['hits'],0,'','.');
			$tabledata[$i]['TOP']=$res['top'];
			$tabledata[$i]['RESTRICTED']=$res['restricted'];
			
			//Tags
			$tabledata[$i]['TAG']=$tagdata;
			$tabledata[$i]['TAG_IDS']=$tagids;
			$tabledata[$i]['KEYWORDS']=$keywords;
			
			//Produkt
			$tabledata[$i]['PRODUCT_ID']=$res['prodid'];
			
			//Kategorie
			$tabledata[$i]['CATID']=$res['catid'];
			$tabledata[$i]['CATTITLE']=$catinfo[$res['catid']]['title'];
			$tabledata[$i]['CATICON']=$catinfo[$res['catid']]['icon'];
			$tabledata[$i]['CATLINK']=$catinfo[$res['catid']]['link'];
			
			//Galerie
			if ( $apx->is_module('gallery') && $res['galid'] ) {
				$galinfo=gallery_info($res['galid']);
				$tabledata[$i]['GALLERY_ID']=$galinfo['id'];
				$tabledata[$i]['GALLERY_TITLE']=$galinfo['title'];
				$tabledata[$i]['GALLERY_LINK']=mklink(
					'gallery.php?id='.$galinfo['id'],
					'gallery,list'.$galinfo['id'].',1'.urlformat($galinfo['title']).'.html'
				);
			}
			
			//Kommentare
			if ( $apx->is_module('comments') && $set['news']['coms'] && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('news',$res['id']);
				else $coms->mid=$res['id'];
				
				$link=mklink(
					'news.php?id='.$res['id'],
					'news,id'.$res['id'].urlformat($res['title']).'.html'
				);
				
				$tabledata[$i]['COMMENT_COUNT']=$coms->count();
				$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
				$tabledata[$i]['DISPLAY_COMMENTS']=1;
				if ( in_template(array('NEWS.COMMENT_LAST_USERID','NEWS.COMMENT_LAST_NAME','NEWS.COMMENT_LAST_TIME'),$parse) ) {
					$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
					$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
					$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
			
			//Bewertungen
			if ( $apx->is_module('ratings') && $set['news']['ratings'] && $res['allowrating'] ) {
				require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
				if ( !isset($rate) ) $rate=new ratings('news',$res['id']);
				else $rate->mid=$res['id'];
				
				$tabledata[$i]['RATING']=$rate->display();
				$tabledata[$i]['RATING_VOTES']=$rate->count();
				$tabledata[$i]['DISPLAY_RATING']=1;
			}
			
			$laststamp=date('Y/m/d',$res['starttime']-TIMEDIFF);
		}
		
		$apx->tmpl->assign('NEWS',$tabledata);
		$apx->tmpl->parse('search_result');
	}
	
	//SUCHE DURCHFÜHREN
	else {
		$where='';
		
		//Suchbegriffe
		if ( $_REQUEST['item'] ) {
			$items=array();
			$it=explode(' ',preg_replace('#[ ]{2,}#',' ',trim($_REQUEST['item'])));
			$tagmatches = news_match_tags($it);
			foreach ( $it AS $item ) {
				if ( trim($item) ) {
					$string=preg_replace('#[\s_-]+#','[^0-9a-zA-Z]*',$item);
					if ( preg_match('#^[0-9a-zA-Z]+$#',$string) ) $items[]=" LIKE '%".addslashes_like($string)."%' ";
					else $items[]=" REGEXP '".addslashes($string)."' ";
				}
			}
			
			if ( $_REQUEST['conn']=='or' ) $conn=' OR ';
			else $conn=' AND ';
			
			$search=array();
			foreach ( $items AS $regexp ) {
				$tagmatch = array_shift($tagmatches);
				$search[]=" ( ".iif($tagmatch, " id IN (".implode(',', $tagmatch).") OR ")." title ".$regexp." OR subtitle ".$regexp." OR teaser ".$regexp." OR text ".$regexp." ) ";
			}
			$where.=iif($where,' AND ').' ( '.implode($conn,$search).' ) ';
		}
		
		//Nach Tag suchen
		if ( $_REQUEST['tag'] ) {
			$tagid = getTagId($_REQUEST['tag']);
			if ( $tagid ) {
				$data = $db->fetch("SELECT id FROM ".PRE."_news_tags WHERE tagid='".$tagid."'");
				$ids = get_ids($data, 'id');
				if ( $ids ) {
					$where.=iif($where,' AND ').' id IN ('.implode(',', $ids).') ';
				}
				else {
					$where.=iif($where,' AND ').' 0 ';
				}
			}
			else {
				$where.=iif($where,' AND ').' 0 ';
			}
		}
		
		//Kategorie
		if ( $_REQUEST['catid'] ) {
			$cattree=news_tree($_REQUEST['catid']);
			if ( count($cattree) ) {
				$where.=iif($where,' AND ').'catid IN ('.@implode(',',$cattree).')';
			}
		}
		
		//Zeitperiode
		if ( $_REQUEST['start_day'] && $_REQUEST['start_month'] && $_REQUEST['start_year'] && $_REQUEST['end_day'] && $_REQUEST['end_month'] && $_REQUEST['end_year'] ) {
			$where.=iif($where,' AND ')."starttime BETWEEN '".(mktime(0,0,0,intval($_REQUEST['start_month']),intval($_REQUEST['start_day']),intval($_REQUEST['start_year']))+TIMEDIFF)."' AND '".((mktime(0,0,0,intval($_REQUEST['end_month']),intval($_REQUEST['end_day'])+1,intval($_REQUEST['end_year']))-1)+TIMEDIFF)."'";
		}
		
		//Keine Suchkriterien vorhanden
		if ( !$where ) {
			message($apx->lang->get('CORE_BACK'),'javascript:history.back();');
		}
		
		//SUCHE AUSFÜHREN
		else {
			$data = $db->fetch("SELECT id FROM ".PRE."_news WHERE ".$where);
			$resultIds = get_ids($data, 'id');
			
			//Keine Ergebnisse
			if ( !$resultIds ) {
				message($apx->lang->get('MSG_NORESULT'),'javascript:history.back();');
				require('lib/_end.php');
			}
			
			$searchid = saveSearchResult('news', $resultIds);
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.str_replace('&amp;', '&', mklink(
				'newsarchive.php?action=search&searchid='.$searchid,
				'newsarchive.html?action=search&searchid='.$searchid
			)));
		}
	}
	require('lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// NEWS AUFLISTEN

if ( $_REQUEST['month'] ) {
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars('archive_index');
	
	//Headline
	$month=substr($_REQUEST['month'],0,2);
	$year=substr($_REQUEST['month'],2);
	headline(getcalmonth($month).' '.$year,mklink(
		'newsarchive.php?month='.$month.$year,
		'newsarchive,'.$month.','.$year.',1.html')
	);
	titlebar($apx->lang->get('HEADLINE_ARCHIVE').': '.getcalmonth($month).' '.$year);
	
	
	//Seitenzahlen generieren
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ( ( ".time()." BETWEEN starttime AND endtime ) AND starttime BETWEEN '".(mktime(0,0,0,intval($month),1,intval($year))+TIMEDIFF)."' AND '".((mktime(0,0,0,intval($month+1),1,intval($year))-1)+TIMEDIFF)."' ) ".$filter." ".section_filter()." )");
	pages(
		mklink(
			'newsarchive.php?month='.$_REQUEST['month'],
			'newsarchive,'.$month.','.$year.',{P}.html'),
		$count,
		$set['news']['archiveepp']
	);
	
	//News ausgeben
	if ( $set['news']['archiveentrysort']==1 ) $orderby=' starttime DESC ';
	else $orderby=' starttime ASC';
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ( ( ".time()." BETWEEN starttime AND endtime ) AND starttime BETWEEN '".(mktime(0,0,0,intval($month),1,intval($year))+TIMEDIFF)."' AND '".((mktime(0,0,0,intval($month+1),1,intval($year))-1)+TIMEDIFF)."' ) ".$filter." ".section_filter()." ) ORDER BY ".$orderby." ".getlimit($set['news']['archiveepp']));
	
	//Kategorien auslesen
	if ( in_array('NEWS.CATTITLE',$parse) || in_array('NEWS.CATICON',$parse) || in_array('NEWS.CATLINK',$parse) ) {
		$catinfo=news_catinfo(get_ids($data,'catid'));
	}
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link=mklink(
				'news.php?id='.$res['id'],
				'news,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			//Newspic
			if ( in_array('NEWS.PICTURE',$parse) || in_array('NEWS.PICTURE_POPUP',$parse) || in_array('NEWS.PICTURE_POPUPPATH',$parse) ) {
				list($picture,$picture_popup,$picture_popuppath)=news_newspic($res['newspic']);
			}
			
			//Username + eMail
			if ( $res['userid'] ) {
				$username=$res['username'];
				$email=iif(!$res['pub_hidemail'],$res['email']);
			}
			else {
				$username=$res['send_username'];
				$email=$res['send_email'];
			}
			
			//Datehead
			if ( $laststamp!=date('Y/m/d',$res['starttime']-TIMEDIFF) ) {
				$tabledata[$i]['DATEHEAD']=$res['starttime'];
			}
			
			//Links
			if ( in_array('NEWS.RELATED',$parse) ) {
				$tabledata[$i]['RELATED']=news_links($res['links']);
			}
			
			//Tags
			if ( in_array('NEWS.TAG',$parse) || in_array('NEWS.TAG_IDS',$parse) || in_array('NEWS.KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = news_tags($res['id']);
			}
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['SECID']=$res['secid'];
			$tabledata[$i]['TITLE']=$res['title'];
			$tabledata[$i]['SUBTITLE']=$res['subtitle'];
			$tabledata[$i]['LINK']=$link;
			if ( in_array('NEWS.TEASER',$parse) && $set['news']['teaser'] ) $tabledata[$i]['TEASER']=mediamanager_inline($res['teaser']);
			if ( in_array('NEWS.TEXT',$parse) ) $tabledata[$i]['TEXT']=mediamanager_inline($res['text']);
			$tabledata[$i]['TIME']=$res['starttime'];
			$tabledata[$i]['PICTURE']=$picture;
			$tabledata[$i]['PICTURE_POPUP']=$picture_popup;
			$tabledata[$i]['PICTURE_POPUPPATH']=$picture_popuppath;
			$tabledata[$i]['USERID']=$res['userid'];
			$tabledata[$i]['USERNAME']=replace($username);
			$tabledata[$i]['EMAIL']=replace($email);
			$tabledata[$i]['EMAIL_ENCRYPTED']=replace(cryptMail($email));
			$tabledata[$i]['HITS']=number_format($res['hits'],0,'','.');
			$tabledata[$i]['TOP']=$res['top'];
			$tabledata[$i]['RESTRICTED']=$res['restricted'];
			
			//Tags
			$tabledata[$i]['TAG']=$tagdata;
			$tabledata[$i]['TAG_IDS']=$tagids;
			$tabledata[$i]['KEYWORDS']=$keywords;
			
			//Kategorie
			$tabledata[$i]['CATID']=$res['catid'];
			$tabledata[$i]['CATTITLE']=$catinfo[$res['catid']]['title'];
			$tabledata[$i]['CATICON']=$catinfo[$res['catid']]['icon'];
			$tabledata[$i]['CATLINK']=$catinfo[$res['catid']]['link'];
			
			//Produkt
			$tabledata[$i]['PRODUCT_ID']=$res['prodid'];
			
			//Galerie
			if ( $apx->is_module('gallery') && $res['galid'] ) {
				$galinfo=gallery_info($res['galid']);
				$tabledata[$i]['GALLERY_ID']=$galinfo['id'];
				$tabledata[$i]['GALLERY_TITLE']=$galinfo['title'];
				$tabledata[$i]['GALLERY_LINK']=mklink(
					'gallery.php?id='.$galinfo['id'],
					'gallery,list'.$galinfo['id'].',1'.urlformat($galinfo['title']).'.html'
				);
			}
			
			//Kommentare
			if ( $apx->is_module('comments') && $set['news']['coms'] && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('news',$res['id']);
				else $coms->mid=$res['id'];
				
				$link=mklink(
					'news.php?id='.$res['id'],
					'news,id'.$res['id'].urlformat($res['title']).'.html'
				);
				
				$tabledata[$i]['COMMENT_COUNT']=$coms->count();
				$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
				$tabledata[$i]['DISPLAY_COMMENTS']=1;
				if ( in_template(array('NEWS.COMMENT_LAST_USERID','NEWS.COMMENT_LAST_NAME','NEWS.COMMENT_LAST_TIME'),$parse) ) {
					$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
					$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
					$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
			
			//Bewertungen
			if ( $apx->is_module('ratings') && $set['news']['ratings'] && $res['allowrating'] ) {
				require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
				if ( !isset($rate) ) $rate=new ratings('news',$res['id']);
				else $rate->mid=$res['id'];
				
				$tabledata[$i]['RATING']=$rate->display();
				$tabledata[$i]['RATING_VOTES']=$rate->count();
				$tabledata[$i]['DISPLAY_RATING']=1;
			}
			
			
			$laststamp=date('Y/m/d',$res['starttime']-TIMEDIFF);
		}
	}
	
	$apx->tmpl->assign('NEWS',$tabledata);
	$apx->tmpl->parse('archive_index');
	require('lib/_end.php');
}


//////////////////////////////////////////////////////////////////////////////////////////////////////// MONATE AUFLISTEN

$apx->lang->drop('search');
$parse = $apx->tmpl->used_vars('archive');

$data=$db->fetch("SELECT starttime FROM ".PRE."_news WHERE ( '".time()."' BETWEEN starttime AND endtime ".$filter." ".section_filter()." ) ORDER BY starttime ".iif($set['news']['archivesort']==2,'ASC','DESC'));
if ( count($data) ) {
	foreach ( $data AS $res ) {
		if ( $laststamp==date('Y/m',$res['starttime']-TIMEDIFF) ) continue;
		++$i;
		
		//Link
		$link=mklink(
			'newsarchive.php?month='.date('mY',$res['starttime']-TIMEDIFF),
			'newsarchive,'.date('m,Y',$res['starttime']-TIMEDIFF).',1.html'
		);
		
		//Links
		if ( in_array('ARCHIVE.COUNT',$parse) ) {
			$monthStart = mktime(0, 0, 0, date('n',$res['starttime']-TIMEDIFF), 1, date('Y',$res['starttime']-TIMEDIFF))+TIMEDIFF;
			$monthEnd = mktime(0, 0, 0, date('n',$res['starttime']-TIMEDIFF)+1, 1, date('Y',$res['starttime']-TIMEDIFF))+TIMEDIFF-1;
			list($count) = $db->first("SELECT count(id) FROM ".PRE."_news WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) AND ( starttime BETWEEN ".$monthStart." AND ".$monthEnd." ) ".$filter." ".section_filter()." )");
			$tabledata[$i]['COUNT']=$count;
		}
		
		$tabledata[$i]['YEAR']=date('Y',$res['starttime']-TIMEDIFF);
		$tabledata[$i]['MONTH']=$res['starttime'];
		$tabledata[$i]['LINK']=$link;
		
		$laststamp=date('Y/m',$res['starttime']);
	}
}

$apx->tmpl->assign('ARCHIVE',$tabledata);


//Suchbox
$data=news_catinfo();
foreach ( $data AS $id => $cat ) {
	++$i;
	$catdata[$i]['ID']=$id;
	$catdata[$i]['TITLE']=$cat['title'];
	$catdata[$i]['LEVEL']=$cat['level'];
}

$postto=mklink('newsarchive.php','newsarchive.html');
$apx->tmpl->assign('SEARCH_POSTTO',$postto);
$apx->tmpl->assign('SEARCH_CATEGORY',$catdata);

$apx->tmpl->parse('archive');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>