<?php 

define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once(BASEDIR.getmodulepath('news').'functions.php');

$apx->module('news');
$apx->lang->drop('news');

$recent=news_recent();
$_REQUEST['id']=(int)$_REQUEST['id'];
$_REQUEST['catid']=(int)$_REQUEST['catid'];


//////////////////////////////////////////////////////////////////////////////////////////////////////// NUR KOMMENTARE

if ( $_REQUEST['id'] && $_REQUEST['comments'] ) {
	$res=$db->first("SELECT id,title,starttime FROM ".PRE."_news WHERE ( ".time()." BETWEEN starttime AND endtime AND id='".$_REQUEST['id']."' ".section_filter()." ) LIMIT 1");
	
	//Headline + Titlebar
	if ( news_is_recent($res['id']) ) {
		headline($apx->lang->get('HEADLINE'),mklink('news.php','news.html'));
		titlebar($apx->lang->get('HEADLINE').': '.$res['title']);
	}
	else {
		headline($apx->lang->get('HEADLINE_ARCHIVE'),mklink('newsarchive.php','newsarchive.html'));
		headline(getcalmonth(date('m',$res['starttime']-TIMEDIFF)).' '.date('Y',$res['starttime']-TIMEDIFF),mklink('newsarchive.php?month='.date('m',$res['starttime']-TIMEDIFF).date('Y',$res['starttime']-TIMEDIFF),'newsarchive,'.date('m',$res['starttime']-TIMEDIFF).','.date('Y',$res['starttime']-TIMEDIFF).',1.html'));
		titlebar($apx->lang->get('HEADLINE_ARCHIVE').': '.$res['title']);
	}
	
	news_showcomments($_REQUEST['id']);
	require('lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// NEWS DETAIL

if ( $_REQUEST['id'] ) {
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars('detail');
	
	//News ausgeben
	$res=$db->first("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ".iif(!$user->is_team_member(),time()." BETWEEN starttime AND endtime AND ")." id='".$_REQUEST['id']."' ".section_filter()." ) LIMIT 1");
	if ( !$res['id'] ) filenotfound();
	
	//Altersabfrage
	if ( $res['restricted'] ) {
		checkage();
	}
	
	//Counter
	$db->query("UPDATE ".PRE."_news SET hits=hits+1 WHERE ( ".time()." BETWEEN starttime AND endtime AND id='".$_REQUEST['id']."' ".section_filter()." )");
	
	//Headline + Titlebar
	if ( news_is_recent($res['id']) ) {
		headline($apx->lang->get('HEADLINE'),mklink('news.php','news.html'));
		titlebar($apx->lang->get('HEADLINE').': '.$res['title']);
	}
	else {
		headline($apx->lang->get('HEADLINE_ARCHIVE'),mklink('newsarchive.php','newsarchive.html'));
		headline(getcalmonth(date('m',$res['starttime']-TIMEDIFF)).' '.date('Y',$res['starttime']-TIMEDIFF),mklink('newsarchive.php?month='.date('m',$res['starttime']-TIMEDIFF).date('Y',$res['starttime']-TIMEDIFF),'newsarchive,'.date('m',$res['starttime']-TIMEDIFF).','.date('Y',$res['starttime']-TIMEDIFF).',1.html'));
		titlebar($apx->lang->get('HEADLINE_ARCHIVE').': '.$res['title']);
	}
	
	//Kategorie-Info
	if ( in_array('CATTITLE',$parse) || in_array('CATICON',$parse) || in_array('CATLINK',$parse) ) {
		$catinfo=news_catinfo($res['catid']);
	}
	
	//Link
	$link=mklink(
		'news.php?id='.$res['id'],
		'news,id'.$res['id'].urlformat($res['title']).'.html'
	);
	
	//Newspic
	if ( in_array('PICTURE',$parse) || in_array('PICTURE_POPUP',$parse) || in_array('PICTURE_POPUPPATH',$parse) ) {
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
	
	//Text
	$text = '';
	if ( in_array('TEXT',$parse) ) {
		$text = mediamanager_inline($res['text']);
		if ( $apx->is_module('glossar') ) $text = glossar_highlight($text);
	}
	
	//Teaser
	$teaser = '';
	if ( in_array('TEASER',$parse) && $set['news']['teaser'] ) {
		$teaser = mediamanager_inline($res['teaser']);
		if ( $apx->is_module('glossar') ) $teaser = glossar_highlight($teaser);
	}
	
	//Tags
	if ( in_array('TAG',$parse) || in_array('TAG_IDS',$parse) || in_array('KEYWORDS',$parse) ) {
		list($tagdata, $tagids, $keywords) = news_tags($res['id']);
	}
	
	$apx->tmpl->assign('ID',$res['id']);
	$apx->tmpl->assign('SECID',$res['secid']);
	$apx->tmpl->assign('TITLE',$res['title']);
	$apx->tmpl->assign('SUBTITLE',$res['subtitle']);
	$apx->tmpl->assign('LINK',$link);
	$apx->tmpl->assign('TEASER',$teaser);
	$apx->tmpl->assign('TEXT',$text);
	$apx->tmpl->assign_static('META_DESCRIPTION',replace($res['meta_description']));
	$apx->tmpl->assign('TIME',$res['starttime']);
	$apx->tmpl->assign('PICTURE',$picture);
	$apx->tmpl->assign('PICTURE_POPUP',$picture_popup);
	$apx->tmpl->assign('PICTURE_POPUPPATH',$picture_popuppath);
	$apx->tmpl->assign('USERID',$res['userid']);
	$apx->tmpl->assign('USERNAME',replace($username));
	$apx->tmpl->assign('EMAIL',replace($email));
	$apx->tmpl->assign('EMAIL_ENCRYPTED',replace(cryptMail($email)));
	$apx->tmpl->assign('HITS',number_format($res['hits']+1,0,'','.'));
	$apx->tmpl->assign('TOP',$res['top']);
	$apx->tmpl->assign('RESTRICTED',$res['restricted']);
	$apx->tmpl->assign('RELATED',news_links($res['links']));
	
	//Tags
	$apx->tmpl->assign('TAG_IDS', $tagids);
	$apx->tmpl->assign('TAG', $tagdata);
	$apx->tmpl->assign('KEYWORDS', $keywords);
	
	//Produkt
	$apx->tmpl->assign('PRODUCT_ID',$res['prodid']);
	
	//Kategorie
	$apx->tmpl->assign('CATID',$res['catid']);
	$apx->tmpl->assign('CATTITLE',$catinfo['title']);
	$apx->tmpl->assign('CATICON',$catinfo['icon']);
	$apx->tmpl->assign('CATLINK',$catinfo['link']);
	
	//Galerie
	if ( $apx->is_module('gallery') && $res['galid'] ) {
		$galinfo=gallery_info($res['galid']);
		$gallink=mklink(
			'gallery.php?id='.$galinfo['id'],
			'gallery,list'.$galinfo['id'].',1'.urlformat($galinfo['title']).'.html'
		);
		$apx->tmpl->assign('GALLERY_ID',$galinfo['id']);
		$apx->tmpl->assign('GALLERY_TITLE',$galinfo['title']);
		$apx->tmpl->assign('GALLERY_LINK',$gallink);
	}
	
	//Kommentare
	if ( $apx->is_module('comments') && $set['news']['coms'] && $res['allowcoms'] ) {
		require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
		$coms=new comments('news',$res['id']);
		$coms->assign_comments($parse);
		if ( !news_is_recent($res['id']) && !$set['news']['archcoms'] ) $apx->tmpl->assign('COMMENT_NOFORM',1);
	}
	
	//Bewertungen
	if ( $apx->is_module('ratings') && $set['news']['ratings'] && $res['allowrating'] ) {
		require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
		$rate=new ratings('news',$res['id']);
		$rate->assign_ratings($parse);
		if ( !news_is_recent($res['id']) && !$set['news']['archratings'] ) $apx->tmpl->assign('RATING_NOFORM',1);
	}
	
	$apx->tmpl->parse('detail');	
	require('lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// NEWS AUFLISTEN

//Titelleiste
headline($apx->lang->get('HEADLINE'),mklink('news.php','news.html'));
titlebar($apx->lang->get('HEADLINE'));

//Verwendete Variablen auslesen
$parse=$apx->tmpl->used_vars('index');

//Kategorie-Baum holen
$cattree=news_tree($_REQUEST['catid']);

//Seitenzahlen generieren
list($count)=$db->first("SELECT count(id) FROM ".PRE."_news WHERE ( ".time()." BETWEEN starttime AND endtime ".iif(count($cattree),' AND catid IN ('.@implode(',',$cattree).') ' ).section_filter().")");
pages(
	mklink(
		'news.php?catid='.$_REQUEST['catid'],
		'news,'.$_REQUEST['catid'].',{P}.html'),
	$count,
	$set['news']['epp']
);


//News ausgeben
$data=$db->fetch("SELECT a.*,IF(a.sticky>=".time().",1,0) AS sticky,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ".time()." BETWEEN starttime AND endtime ".iif(count($cattree),' AND catid IN ('.@implode(',',$cattree).') ' ).section_filter().") ORDER BY sticky DESC,starttime DESC ".getlimit($set['news']['epp']));

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
		
		//Text
		$text = '';
		if ( in_array('NEWS.TEXT',$parse) ) {
			$text = mediamanager_inline($res['text']);
			if ( $apx->is_module('glossar') ) $text = glossar_highlight($text);
		}
		
		//Teaser
		$teaser = '';
		if ( in_array('NEWS.TEASER',$parse) && $set['news']['teaser'] ) {
			$teaser = mediamanager_inline($res['teaser']);
			if ( $apx->is_module('glossar') ) $teaser = glossar_highlight($teaser);
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
		$tabledata[$i]['TEASER']=$teaser;
		$tabledata[$i]['TEXT']=$text;
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
$apx->tmpl->parse('index');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>