<?php 

define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once(BASEDIR.getmodulepath('downloads').'functions.php');

$apx->module('downloads');
$apx->lang->drop('global');

headline($apx->lang->get('HEADLINE'),mklink('downloads.php','downloads.html'));
titlebar($apx->lang->get('HEADLINE'));
$_REQUEST['catid']=(int)$_REQUEST['catid'];
$_REQUEST['id']=(int)$_REQUEST['id'];


////////////////////////////////////////////////////////////////////////////////// DEFEKTER DOWNLOAD

if ( $_REQUEST['id'] && $_REQUEST['broken'] ) {
	$apx->lang->drop('broken');
	
	if ( $_POST['broken'] ) {
		$res=$db->first("SELECT title FROM ".PRE."_downloads WHERE ( id='".$_REQUEST['id']."' ".section_filter()." ) LIMIT 1");
		titlebar($apx->lang->get('HEADLINE').': '.$res['title']);
		
		$link=mklink(
			'downloads.php?id='.$_REQUEST['id'],
			'downloads,id'.$_REQUEST['id'].urlformat($res['title']).'.html'
		);
		
		$db->query("UPDATE ".PRE."_downloads SET broken='".time()."' WHERE ( id='".$_REQUEST['id']."' ".section_filter()." ) LIMIT 1");
		
		//eMail-Benachrichtigung
		if ( $set['downloads']['mailonbroken'] ) {
			$input=array('URL'=>substr(HTTP, 0, -1).$link);
			sendmail($set['downloads']['mailonbroken'],'BROKEN',$input);
		}
		
		message($apx->lang->get('MSG_BROKEN'),$link);
		require('lib/_end.php');
	}
	else {
		tmessage('broken',array('ID'=>$_REQUEST['id']));
	}
}



////////////////////////////////////////////////////////////////////////////////// NUR KOMMENTARE

if ( $_REQUEST['id'] && $_REQUEST['comments'] ) {
	$res=$db->first("SELECT title FROM ".PRE."_downloads WHERE ( id='".$_REQUEST['id']."' ".section_filter()." ) LIMIT 1");
	titlebar($apx->lang->get('HEADLINE').': '.$res['title']);
	
	downloads_showcomments($_REQUEST['id']);
}



///////////////////////////////////////////////////////////////////////////////////////// DETAILS
if ( $_REQUEST['id'] ) {
	$apx->lang->drop('detail');
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars('detail');
	
	//Download-Info
	$res=$db->first("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_downloads AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( a.id='".$_REQUEST['id']."' ".iif(!$user->is_team_member(),"AND ( '".time()."' BETWEEN a.starttime AND a.endtime )")." ".section_filter()." ) LIMIT 1");
	if ( !$res['id'] ) filenotfound();
	
	//Altersabfrage
	if ( $res['restricted'] ) {
		checkage();
	}
	
	//Headline
	titlebar($apx->lang->get('HEADLINE').': '.$res['title']);
	
	//Kategorie-Info
	if ( true || in_array('CATTITLE',$parse) || in_array('CATTEXT',$parse) || in_array('CATICON',$parse) || in_array('CATLINK',$parse) || in_array('CATCOUNT',$parse) ) {
		
		//Tree-Manager
		require_once(BASEDIR.'lib/class.recursivetree.php');
		$tree = new RecursiveTree(PRE.'_downloads_cat', 'id');

		$catinfo = $tree->getNode($res['catid'], array('*'));
	}
	
	//Downloads in der Kategorien
	$catcount = 0;
	if ( in_array('CATCOUNT',$parse) ) {
		$wholetree = array_merge(array($res['catid']), $catinfo['children']);
		list($catcount)=$db->first("SELECT count(id) FROM ".PRE."_downloads WHERE ( catid IN (".implode(',', $wholetree).") AND ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter()." )");
	}
	
	//Link
	$link=mklink(
		'downloads.php?id='.$res['id'],
		'downloads,id'.$res['id'].urlformat($res['title']).'.html'
	);
	
	//Teaserbild
	if ( in_array('TEASERPIC',$parse) || in_array('TEASERPIC_POPUP',$parse) || in_array('TEASERPIC_POPUPPATH',$parse) ) {
		list($picture,$picture_popup,$picture_popuppath)=downloads_teaserpic($res['teaserpic']);
	}
	
	//Dateigröße auslesen
	$thefsize=downloads_filesize($res);
	
	//Download-Link
	if ( ( !$set['downloads']['regonly'] && !$res['regonly'] ) || $user->info['userid'] ) {
		$sechash=md5($_SERVER['HTTP_HOST'].$res['file'].date('Y/m/d',time()-TIMEDIFF));
		$dllink='misc.php?action=downloadfile&amp;id='.$res['id'].'&amp;sechash='.$sechash.iif($apx->section_id(),'&amp;sec='.$apx->section_id());
	}
	else $dllink=mklink('user.php','user.html');
	
	//Neu?
	if ( ($res['addtime']+($set['downloads']['new']*24*3600))>=time() ) $new=1;
	else $new=0;
	
	//Username + eMail
	if ( $res['userid'] ) {
		$uploader=$res['username'];
		$uploader_email=iif(!$res['pub_hidemail'],$res['email']);
	}
	else {
		$uploader=$res['send_username'];
		$uploader_email=$res['send_email'];
	}
	
	//Broken
	$blink=mklink(
		'downloads.php?id='.$res['id'].'&amp;broken=1',
		'downloads,id'.$res['id'].urlformat($res['title']).'.html?broken=1'
	);
	
	//Text
	$text = mediamanager_inline($res['text']);
	if ( $apx->is_module('glossar') ) $text = glossar_highlight($text);
	
	//Tags
	if ( in_array('TAG',$parse) || in_array('TAG_IDS',$parse) || in_array('KEYWORDS',$parse) ) {
		list($tagdata, $tagids, $keywords) = downloads_tags($res['id']);
	}
	
	$apx->tmpl->assign('ID',$res['id']);
	$apx->tmpl->assign('SECID',$res['secid']);
	$apx->tmpl->assign('TITLE',$res['title']);
	$apx->tmpl->assign('TEXT',$text);
	$apx->tmpl->assign_static('META_DESCRIPTION',replace($res['meta_description']));
	$apx->tmpl->assign('LINK',$link);
	$apx->tmpl->assign('SIZE',downloads_getsize($thefsize));
	$apx->tmpl->assign('FORMAT',downloads_getformat($res));
	$apx->tmpl->assign('HITS',number_format($res['hits'],0,'','.'));
	$apx->tmpl->assign('TIME',$res['starttime']);
	$apx->tmpl->assign('TEASERPIC',$picture);
	$apx->tmpl->assign('TEASERPIC_POPUP',$picture_popup);
	$apx->tmpl->assign('TEASERPIC_POPUPPATH',$picture_popuppath);
	$apx->tmpl->assign('DOWNLOAD',$dllink);
	$apx->tmpl->assign('DOWNLOADFILE',($res['local'] ? HTTP_HOST.HTTPDIR.getpath('uploads') : '').$res['file']);
	$apx->tmpl->assign('LOCAL',$res['local']);
	$apx->tmpl->assign('TOP',$res['top']);
	$apx->tmpl->assign('RESTRICTED',$res['restricted']);
	$apx->tmpl->assign('NEW',$new);
	$apx->tmpl->assign('BROKEN',$blink);
	$apx->tmpl->assign('REGONLY',($res['regonly'] || $set['downloads']['regonly']));
	
	//Tags
	$apx->tmpl->assign('TAG_IDS', $tagids);
	$apx->tmpl->assign('TAG', $tagdata);
	$apx->tmpl->assign('KEYWORDS', $keywords);
	
	//Produkt
	$apx->tmpl->assign('PRODUCT_ID',$res['prodid']);
	
	//Uploader
	$apx->tmpl->assign('UPLOADER',replace($uploader));
	$apx->tmpl->assign('UPLOADER_EMAIL',replace($uploader_email));
	$apx->tmpl->assign('UPLOADER_EMAIL_ENCRYPTED',replace(cryptMail($uploader_email)));
	$apx->tmpl->assign('UPLOADER_ID',$res['userid']);
	
	//Autor
	$apx->tmpl->assign('AUTHOR',replace($res['author']));
	$apx->tmpl->assign('AUTHOR_LINK',replace($res['author_link']));
	
	$apx->tmpl->assign('MIRROR',downloads_mirrors($res['id'],$res['mirrors']));
	$apx->tmpl->assign('PICTURE',downloads_pictures($res['pictures']));
	
	//Download-Zeit
	$apx->tmpl->assign('TIME_MODEM',downloads_gettime($thefsize,56));
	$apx->tmpl->assign('TIME_ISDN',downloads_gettime($thefsize,64));
	$apx->tmpl->assign('TIME_ISDN2',downloads_gettime($thefsize,128));
	$apx->tmpl->assign('TIME_DSL1000',downloads_gettime($thefsize,1024));
	$apx->tmpl->assign('TIME_DSL2000',downloads_gettime($thefsize,1024*2));
	$apx->tmpl->assign('TIME_DSL6000',downloads_gettime($thefsize,1024*6));
	$apx->tmpl->assign('TIME_DSL10000',downloads_gettime($thefsize,1024*10));
	$apx->tmpl->assign('TIME_DSL12000',downloads_gettime($thefsize,1024*12));
	$apx->tmpl->assign('TIME_DSL16000',downloads_gettime($thefsize,1024*16));
	
	//Download-Limit
	if ( downloads_limit_is_reached($res['id'],$res['limit']) ) $apx->tmpl->assign('LIMIT',1);
	
	//Kategorie
	$apx->tmpl->assign('CATID',$res['catid']);
	$apx->tmpl->assign('CATTITLE',$catinfo['title']);
	$apx->tmpl->assign('CATTEXT',$catinfo['text']);
	$apx->tmpl->assign('CATICON',$catinfo['icon']);
	$apx->tmpl->assign('CATCOUNT',$catcount);
	$apx->tmpl->assign('CATLINK',mklink(
		'downloads.php?catid='.$catinfo['id'],
		'downloads,'.$catinfo['id'].',1'.urlformat($catinfo['title']).'.html'
	));
	
	//Pfad
	if ( in_array('PATH',$parse) ) {
		$apx->tmpl->assign('PATH',downloads_path($res['catid']));
	}
	
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
	if ( $apx->is_module('comments') && $set['downloads']['coms'] && $res['allowcoms'] ) {
		require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
		$coms=new comments('downloads',$res['id']);
		$coms->assign_comments($parse);
	}
	
	//Bewertungen
	if ( $apx->is_module('ratings') && $set['downloads']['ratings'] && $res['allowrating'] ) {
		require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
		$rate=new ratings('downloads',$res['id']);
		$rate->assign_ratings($parse);
	}
	
	$apx->tmpl->parse('detail');
	require('lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// SUCHE

if ( $_REQUEST['action']=='search' ) {
	$apx->lang->drop('list');
	$apx->lang->drop('search');
	
	//ERGEBNIS ANZEIGEN
	if ( $_REQUEST['searchid'] ) {
		titlebar($apx->lang->get('HEADLINE_SEARCH'));
		
		//Suchergebnis auslesen
		$resultIds = '';
		list($resultIds) = getSearchResult('downloads', $_REQUEST['searchid']);
		
		//Keine Ergebnisse
		if ( !$resultIds ) {
			message($apx->lang->get('MSG_NORESULT'),'javascript:history.back();');
			require('lib/_end.php');
		}
		
		//SUCHE AUSFÜHREN
		$parse=$apx->tmpl->used_vars('search_result');
		
		//Seitenzahlen generieren
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_downloads AS a WHERE '".time()."' BETWEEN starttime AND endtime AND id IN (".implode(', ', $resultIds).") ".section_filter());
		pages(
			mklink(
				'downloads.php?action=search&amp;searchid='.$_REQUEST['searchid'],
				'downloads.html?action=search&amp;searchid='.$_REQUEST['searchid']
			),
			$count,
			$set['downloads']['searchepp']
		);
		
		//Keine Ergebnisse
		if ( !$count ) {
			message($apx->lang->get('MSG_NORESULT'),'javascript:history.back();');
			require('lib/_end.php');
		}
		
		//Sortierung
		if ( $set['downloads']['sortby']==2 ) $orderby=' ORDER BY starttime DESC ';
		else $orderby=' ORDER BY title ASC ';
		$data=$db->fetch("SELECT *,b.username,b.email,b.pub_hidemail FROM ".PRE."_downloads AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE '".time()."' BETWEEN starttime AND endtime AND id IN (".implode(', ', $resultIds).") ".section_filter()." ".$orderby.getlimit($set['downloads']['searchepp']));
		$catids=get_ids($data,'catid');
		
		//Kategorien auslesen, falls notwendig
		$catinfo=array();
		if ( count($catids) && in_template(array('DOWNLOAD.CATTITLE','DOWNLOAD.CATTEXT','DOWNLOAD.CATICON'),$parse) ) {
			$catinfo = downloads_catinfo($catids);
		}
		
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				++$i;
				
				//Link
				$link=mklink(
					'downloads.php?id='.$res['id'],
					'downloads,id'.$res['id'].urlformat($res['title']).'.html'
				);
				
				//Teaserbild
				if ( in_array('DOWNLOAD.TEASERPIC',$parse) || in_array('DOWNLOAD.TEASERPIC_POPUP',$parse) || in_array('DOWNLOAD.TEASERPIC_POPUPPATH',$parse) ) {
					list($picture,$picture_popup,$picture_popuppath)=downloads_teaserpic($res['teaserpic']);
				}
				
				//Dateigröße auslesen
				if ( in_array('DOWNLOAD.SIZE',$parse) ) {
					$thefsize=downloads_filesize($res);
				}
				
				//Download-Link
				if ( ( !$set['downloads']['regonly'] && !$res['regonly'] ) || $user->info['userid'] ) {
					$sechash=md5($_SERVER['HTTP_HOST'].$res['file'].date('Y/m/d',time()-TIMEDIFF));
					$dllink='misc.php?action=downloadfile&amp;id='.$res['id'].'&amp;sechash='.$sechash.iif($apx->section_id(),'&amp;sec='.$apx->section_id());
				}
				else $dllink=mklink('user.php','user.html');
				
				//Bilder
				if ( in_array('DOWNLOAD.PICTURE',$parse) ) {
					$picdata=downloads_pictures($res['pictures']);
				}
				
				//Neu?
				if ( ($res['addtime']+($set['downloads']['new']*24*3600))>=time() ) $new=1;
				else $new=0;
				
				//Username + eMail
				if ( $res['userid'] ) {
					$uploader=$res['username'];
					$uploader_email=iif(!$res['pub_hidemail'],$res['email']);
				}
				else {
					$uploader=$res['send_username'];
					$uploader_email=$res['send_email'];
				}
				
				//Text
				$text = '';
				if ( in_array('DOWNLOAD.TEXT',$parse) ) {
					$text = mediamanager_inline($res['text']);
					if ( $apx->is_module('glossar') ) $text = glossar_highlight($text);
				}
				
				//Tags
				if ( in_array('DOWNLOAD.TAG',$parse) || in_array('DOWNLOAD.TAG_IDS',$parse) || in_array('DOWNLOAD.KEYWORDS',$parse) ) {
					list($tagdata, $tagids, $keywords) = downloads_tags($res['id']);
				}
				
				$tabledata[$i]['ID']=$res['id'];
				$tabledata[$i]['TITLE']=$res['title'];
				$tabledata[$i]['TEXT']=$text;
				$tabledata[$i]['LINK']=$link;
				$tabledata[$i]['TEASERPIC'] = $picture;
				$tabledata[$i]['TEASERPIC_POPUP'] = $picture_popup;
				$tabledata[$i]['TEASERPIC_POPUPPATH'] = $picture_popuppath;
				$tabledata[$i]['SIZE']=downloads_getsize($thefsize);
				$tabledata[$i]['FORMAT']=downloads_getformat($res);
				$tabledata[$i]['HITS']=number_format($res['hits'],0,'','.');
				$tabledata[$i]['TIME']=$res['starttime'];
				$tabledata[$i]['PICTURE']=$picdata;
				$tabledata[$i]['TOP']=$res['top'];
				$tabledata[$i]['RESTRICTED']=$res['restricted'];
				$tabledata[$i]['NEW']=$new;
				$tabledata[$i]['DOWNLOADLINK']=$dllink;
				
				//Tags
				$tabledata[$i]['TAG']=$tagdata;
				$tabledata[$i]['TAG_IDS']=$tagids;
				$tabledata[$i]['KEYWORDS']=$keywords;
				
				//Kategorie
				$tabledata[$i]['CATID']=$res['catid'];
				$tabledata[$i]['CATTITLE']=$catinfo[$res['catid']]['title'];
				$tabledata[$i]['CATTEXT']=$catinfo[$res['catid']]['text'];
				$tabledata[$i]['CATICON']=$catinfo[$res['catid']]['icon'];
				
				//Produkt
				$tabledata[$i]['PRODUCT_ID']=$res['prodid'];
				
				//Uploader
				$tabledata[$i]['UPLOADER_ID']=$res['userid'];
				$tabledata[$i]['UPLOADER']=replace($uploader);
				$tabledata[$i]['UPLOADER_EMAIL']=replace($uploader_email);
				$tabledata[$i]['UPLOADER_EMAIL_ENCRYPTED']=replace(cryptMail($uploader_email));
				
				//Autor
				$tabledata[$i]['AUTHOR']=replace($res['author']);
				$tabledata[$i]['AUTHOR_LINK']=replace($res['author_link']);
				
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
				if ( $apx->is_module('comments') && $set['downloads']['coms'] && $res['allowcoms'] ) {
					require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
					if ( !isset($coms) ) $coms=new comments('downloads',$res['id']);
					else $coms->mid=$res['id'];
					
					$link=mklink(
						'downloads.php?id='.$res['id'],
						'downloads,id'.$res['id'].urlformat($res['title']).'.html'
					);
					
					$tabledata[$i]['COMMENT_COUNT']=$coms->count();
					$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
					$tabledata[$i]['DISPLAY_COMMENTS']=1;
					if ( in_template(array('DOWNLOAD.COMMENT_LAST_USERID','DOWNLOAD.COMMENT_LAST_NAME','DOWNLOAD.COMMENT_LAST_TIME'),$parse) ) {
						$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
						$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
						$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
					}
				}
				
				//Bewertungen
				if ( $apx->is_module('ratings') && $set['downloads']['ratings'] && $res['allowrating'] ) {
					require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
					if ( !isset($rate) ) $rate=new ratings('downloads',$res['id']);
					else $rate->mid=$res['id'];
					
					$tabledata[$i]['RATING']=$rate->display();
					$tabledata[$i]['RATING_VOTES']=$rate->count();
					$tabledata[$i]['DISPLAY_RATING']=1;
				}
				
			}
		}
		
		$apx->tmpl->assign('DOWNLOAD',$tabledata);
		$apx->tmpl->parse('search_result');
	}
	
	//SUCHE DURCHFÜHREN
	else {
		$where='';
		
		//Suchbegriffe
		if ( $_REQUEST['item'] ) {
			$items=array();
			$it=explode(' ',preg_replace('#[ ]{2,}#',' ',trim($_REQUEST['item'])));
			$tagmatches = downloads_match_tags($it);
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
				$search[]=" ( ".iif($tagmatch, " id IN (".implode(',', $tagmatch).") OR ")." title ".$regexp." OR text ".$regexp." OR file ".$regexp." OR author ".$regexp." ) ";
			}
			$where.=iif($where,' AND ').' ( '.implode($conn,$search).' ) ';
		}
		
		//Nach Tag suchen
		if ( $_REQUEST['tag'] ) {
			$tagid = getTagId($_REQUEST['tag']);
			if ( $tagid ) {
				$data = $db->fetch("SELECT id FROM ".PRE."_downloads_tags WHERE tagid='".$tagid."'");
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
			$cattree=downloads_tree($_REQUEST['catid']);
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
			require('lib/_end.php');
		}
		
		//SUCHE AUSFÜHREN
		else {
			$data = $db->fetch("SELECT id FROM ".PRE."_downloads WHERE ".$where);
			$resultIds = get_ids($data, 'id');
			
			//Keine Ergebnisse
			if ( !$resultIds ) {
				message($apx->lang->get('MSG_NORESULT'),'javascript:history.back();');
				require('lib/_end.php');
			}
			
			$searchid = saveSearchResult('downloads', $resultIds);
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.str_replace('&amp;', '&', mklink(
				'downloads.php?action=search&searchid='.$searchid,
				'downloads.html?action=search&searchid='.$searchid
			)));
		}
	}
	require('lib/_end.php');
}



///////////////////////////////////////////////////////////////////////////////////////// KATEGORIEN DURCHSUCHEN

//Sprachpaket
$apx->lang->drop('list');
$apx->lang->drop('search');

//Verwendete Variablen auslesen
$parse=$apx->tmpl->used_vars('index');

//Kategorie auslesen
$catinfo = array();
if ( $_REQUEST['catid'] ) {
	$catinfo = $db->first("SELECT id, title, text, icon, open FROM ".PRE."_downloads_cat WHERE id='".$_REQUEST['catid']."' LIMIT 1");
}

//Tree-Manager
require_once(BASEDIR.'lib/class.recursivetree.php');
$tree = new RecursiveTree(PRE.'_downloads_cat', 'id');


//KATEGORIEN
if ( $_REQUEST['catid'] ) {
	$wholetree = array($_REQUEST['catid']);
	$data = $tree->getLevel(array('title', 'text', 'icon', 'open'), $_REQUEST['catid']);
}
else {
	$wholetree = array();
	$data = $tree->getLevel(array('title', 'text', 'icon', 'open'));
}

if ( count($data) ) {
	
	//Kategorien auflisten
	$catdata = array();
	foreach ( $data AS $res ) {
		++$i;
		
		//Link
		$link=mklink(
			'downloads.php?catid='.$res['id'],
			'downloads,'.$res['id'].',1'.urlformat($res['title']).'.html'
		);
		
		//Download-Zahl
		$contentIds = $res['children'];
		$contentIds[] = $res['id'];
		$wholetree = array_merge($wholetree, $contentIds);
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_downloads WHERE ( catid IN (".implode(',',$contentIds).") AND ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter()." )");
		$catdata[$i]['ID']=$res['id'];
		$catdata[$i]['TITLE']=$res['title'];
		$catdata[$i]['TEXT']=$res['text'];
		$catdata[$i]['ICON']=$res['icon'];
		$catdata[$i]['LINK']=$link;
		$catdata[$i]['COUNT']=$count;
	}
}

$apx->tmpl->assign('CATEGORY',$catdata);
$apx->tmpl->assign('CATID',$catinfo['id']);
$apx->tmpl->assign('CATTITLE',$catinfo['title']);
$apx->tmpl->assign('CATTEXT',$catinfo['text']);
$apx->tmpl->assign('CATICON',$catinfo['icon']);
$apx->tmpl->assign('CATLINK',mklink(
	'downloads.php?catid='.$catinfo['id'],
	'downloads,'.$catinfo['id'].',1'.urlformat($catinfo['title']).'.html'
));

//Pfad
if ( in_array('PATH',$parse) ) {
	$apx->tmpl->assign('PATH',downloads_path($_REQUEST['catid']));
}


//Suchbox
$catdata = array();
if ( in_array('SEARCH_CATEGORY',$parse) ) {
	$data = $tree->getTree(array('title'));
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$catdata[$i]['ID']=$res['id'];
			$catdata[$i]['TITLE']=$res['title'];
			$catdata[$i]['LEVEL']=$res['level'];
		}
	}
}

$postto=mklink('downloads.php','downloads.html');
$apx->tmpl->assign('SEARCH_POSTTO',$postto);
$apx->tmpl->assign('SEARCH_CATEGORY',$catdata);



///////////////////////////////////////////////////
//Parings ausführen, wenn keine Kategorie gewählt//
///////////////////////////////////////////////////
if ( !$_REQUEST['catid'] && $set['downloads']['catonly'] ) {
	$apx->tmpl->parse('index');
	require('lib/_end.php');
}


//Filter bestimmen
if ( $set['downloads']['catonly'] ) $filter="catid='".$_REQUEST['catid']."'";
else $filter="catid IN (".implode(',',$wholetree).")";


//Seitenzahlen
list($count)=$db->first("SELECT count(id) FROM ".PRE."_downloads WHERE ( ".$filter." AND ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter()." )");
pages(
	mklink(
		'downloads.php?catid='.$_REQUEST['catid'].iif($_REQUEST['sortby'],'&amp;sortby='.$_REQUEST['sortby']),
		'downloads,'.$_REQUEST['catid'].',{P}.html'.iif($_REQUEST['sortby'],'?sortby='.$_REQUEST['sortby'])
	),
	$count,
	$set['downloads']['epp']
);
$apx->tmpl->assign('CATCOUNT', $count);


//Orderby
if ( $set['downloads']['sortby']==2 ) $orderdef[0]='date';
else $orderdef[0]='title';
$orderdef['title']=array('a.title','ASC');
$orderdef['date']=array('a.starttime','DESC');
$orderdef['hits']=array('a.hits','DESC');
$orderdef['uploader']=array('b.username','ASC');
$orderdef['author']=array('a.author','ASC');
if ( $apx->is_module('ratings') ) $orderdef['rating']=array('c.rating','DESC');


//Downloads Select
if ( $apx->is_module('ratings') && ( $_REQUEST['sortby']=='rating.ASC' || $_REQUEST['sortby']=='rating.DESC' ) ) {
	$data=$db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail,avg(c.rating) AS rating FROM ".PRE."_downloads AS a LEFT JOIN ".PRE."_user AS b USING(userid) LEFT JOIN ".PRE."_ratings AS c ON ( c.module='downloads' AND a.id=c.mid ) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) AND ".$filter." ".section_filter()." ) GROUP BY a.id ".getorder($orderdef).getlimit($set['downloads']['epp']));
}
else {
	$data=$db->fetch("SELECT *,b.username,b.email,b.pub_hidemail FROM ".PRE."_downloads AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) AND ".$filter." ".section_filter()." ) ".getorder($orderdef).getlimit($set['downloads']['epp']));
}
$catids=get_ids($data,'catid');

//Kategorien auslesen, falls notwendig
$catinfo=array();
if ( count($catids) && in_template(array('DOWNLOAD.CATTITLE','DOWNLOAD.CATTEXT','DOWNLOAD.CATICON'),$parse) ) {
	$catinfo = downloads_catinfo($catids);
}

if ( count($data) ) {
	foreach ( $data AS $res ) {
		++$i;
		
		//Link
		$link=mklink(
			'downloads.php?id='.$res['id'],
			'downloads,id'.$res['id'].urlformat($res['title']).'.html'
		);
		
		//Teaserbild
		if ( in_array('DOWNLOAD.TEASERPIC',$parse) || in_array('DOWNLOAD.TEASERPIC_POPUP',$parse) || in_array('DOWNLOAD.TEASERPIC_POPUPPATH',$parse) ) {
			list($picture,$picture_popup,$picture_popuppath)=downloads_teaserpic($res['teaserpic']);
		}
		
		//Dateigröße auslesen
		if ( in_array('DOWNLOAD.SIZE',$parse) ) {
			$thefsize=downloads_filesize($res);
		}
		
		//Download-Link
		if ( ( !$set['downloads']['regonly'] && !$res['regonly'] ) || $user->info['userid'] ) {
			$sechash=md5($_SERVER['HTTP_HOST'].$res['file'].date('Y/m/d',time()-TIMEDIFF));
			$dllink='misc.php?action=downloadfile&amp;id='.$res['id'].'&amp;sechash='.$sechash.iif($apx->section_id(),'&amp;sec='.$apx->section_id());
		}
		else $dllink=mklink('user.php','user.html');
		
		//Bilder
		if ( in_array('DOWNLOAD.PICTURE',$parse) ) {
			$picdata=downloads_pictures($res['pictures']);
		}
		
		//Neu?
		if ( ($res['addtime']+($set['downloads']['new']*24*3600))>=time() ) $new=1;
		else $new=0;
		
		//Username + eMail
		if ( $res['userid'] ) {
			$uploader=$res['username'];
			$uploader_email=iif(!$res['pub_hidemail'],$res['email']);
		}
		else {
			$uploader=$res['send_username'];
			$uploader_email=$res['send_email'];
		}
		
		//Text
		$text = '';
		if ( in_array('DOWNLOAD.TEXT',$parse) ) {
			$text = mediamanager_inline($res['text']);
			if ( $apx->is_module('glossar') ) $text = glossar_highlight($text);
		}
		
		//Tags
		if ( in_array('DOWNLOAD.TAG',$parse) || in_array('DOWNLOAD.TAG_IDS',$parse) || in_array('DOWNLOAD.KEYWORDS',$parse) ) {
			list($tagdata, $tagids, $keywords) = downloads_tags($res['id']);
		}
		
		$tabledata[$i]['ID']=$res['id'];
		$tabledata[$i]['SECID']=$res['secid'];
		$tabledata[$i]['TITLE']=$res['title'];
		$tabledata[$i]['TEXT']=$text;
		$tabledata[$i]['LINK']=$link;
		$tabledata[$i]['TEASERPIC'] = $picture;
		$tabledata[$i]['TEASERPIC_POPUP'] = $picture_popup;
		$tabledata[$i]['TEASERPIC_POPUPPATH'] = $picture_popuppath;
		$tabledata[$i]['SIZE']=downloads_getsize($thefsize);
		$tabledata[$i]['FORMAT']=downloads_getformat($res);
		$tabledata[$i]['HITS']=number_format($res['hits'],0,'','.');
		$tabledata[$i]['TIME']=$res['starttime'];
		$tabledata[$i]['PICTURE']=$picdata;
		$tabledata[$i]['TOP']=$res['top'];
		$tabledata[$i]['RESTRICTED']=$res['restricted'];
		$tabledata[$i]['NEW']=$new;
		$tabledata[$i]['DOWNLOADLINK']=$dllink;
		
		//Tags
		$tabledata[$i]['TAG']=$tagdata;
		$tabledata[$i]['TAG_IDS']=$tagids;
		$tabledata[$i]['KEYWORDS']=$keywords;
		
		//Kategorie
		$tabledata[$i]['CATID']=$res['catid'];
		$tabledata[$i]['CATTITLE']=$catinfo[$res['catid']]['title'];
		$tabledata[$i]['CATTEXT']=$catinfo[$res['catid']]['text'];
		$tabledata[$i]['CATICON']=$catinfo[$res['catid']]['icon'];
		
		//Uploader
		$tabledata[$i]['UPLOADER_ID']=$res['userid'];
		$tabledata[$i]['UPLOADER']=replace($uploader);
		$tabledata[$i]['UPLOADER_EMAIL']=replace($uploader_email);
		$tabledata[$i]['UPLOADER_EMAIL_ENCRYPTED']=replace(cryptMail($uploader_email));
		
		//Autor
		$tabledata[$i]['AUTHOR']=replace($res['author']);
		$tabledata[$i]['AUTHOR_LINK']=replace($res['author_link']);
		
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
		if ( $apx->is_module('comments') && $set['downloads']['coms'] && $res['allowcoms'] ) {
			require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
			if ( !isset($coms) ) $coms=new comments('downloads',$res['id']);
			else $coms->mid=$res['id'];
			
			$link=mklink(
				'downloads.php?id='.$res['id'],
				'downloads,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			$tabledata[$i]['COMMENT_COUNT']=$coms->count();
			$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
			$tabledata[$i]['DISPLAY_COMMENTS']=1;
			if ( in_template(array('DOWNLOAD.COMMENT_LAST_USERID','DOWNLOAD.COMMENT_LAST_NAME','DOWNLOAD.COMMENT_LAST_TIME'),$parse) ) {
				$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
				$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
				$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
			}
		}
		
		//Bewertungen
		if ( $apx->is_module('ratings') && $set['downloads']['ratings'] && $res['allowrating'] ) {
			require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
			if ( !isset($rate) ) $rate=new ratings('downloads',$res['id']);
			else $rate->mid=$res['id'];
			
			$tabledata[$i]['RATING']=$rate->display();
			$tabledata[$i]['RATING_VOTES']=$rate->count();
			$tabledata[$i]['DISPLAY_RATING']=1;
		}
		
	}
}

//Sortby
ordervars(
	$orderdef,
	mklink(
		'downloads.php?catid='.$_REQUEST['catid'],
		'downloads,'.$_REQUEST['catid'].',1.html'
	)
);

$apx->tmpl->assign('DOWNLOAD',$tabledata);
$apx->tmpl->parse('index');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>
