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



//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



require_once(BASEDIR.getmodulepath('downloads').'functions.php');



//Neuste Downloads auflisten
function downloads_last($count=5,$start=0,$catid=false,$template='last') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=downloads_tree($catid);
	$data=$db->fetch("SELECT * FROM ".PRE."_downloads WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	downloads_print($data,'functions/'.$template);
}



//Zufällige Downloads auflisten
function downloads_random($count=5,$start=0,$catid=false,$template='random') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=downloads_tree($catid);
	$data=$db->fetch("SELECT * FROM ".PRE."_downloads WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) ORDER BY RAND() LIMIT ".iif($start,$start.',').$count);
	
	downloads_print($data,'functions/'.$template);
}



//TOP-Downloads auflisten
function downloads_top($count=5,$start=0,$catid=false,$template='top') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=downloads_tree($catid);
	$data=$db->fetch("SELECT * FROM ".PRE."_downloads WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." AND top='1' ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	downloads_print($data,'functions/'.$template);
}



//Nicht-TOP-Downloads auflisten
function downloads_nottop($count=5,$start=0,$catid=false,$template='nottop') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=downloads_tree($catid);
	$data=$db->fetch("SELECT * FROM ".PRE."_downloads WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." AND top='0' ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	downloads_print($data,'functions/'.$template);
}



//Beste Downloads: Hits
function downloads_best_hits($count=5,$start=0,$catid=false,$template='best_hits') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=downloads_tree($catid);
	$data=$db->fetch("SELECT * FROM ".PRE."_downloads WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) ORDER BY hits DESC LIMIT ".iif($start,$start.',').$count);
	
	downloads_print($data,'functions/'.$template);
}



//Beste Downloads: Bewertung
function downloads_best_rating($count=5,$start=0,$catid=false,$template='best_rating') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( !$apx->is_module('ratings') ) return '';
	
	$cattree=downloads_tree($catid);
	$data=$db->fetch("SELECT avg(rating) AS rating,count(*) AS votes,b.* FROM ".PRE."_ratings AS a LEFT JOIN ".PRE."_downloads AS b ON a.mid=b.id AND a.module='downloads' WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) GROUP BY a.mid ORDER BY rating DESC,votes DESC LIMIT ".iif($start,$start.',').$count);
	
	downloads_print($data,'functions/'.$template);
}



//Ähnliche Downloads auflisten
function downloads_similar($tagids=array(),$count=5,$start=0,$catid=false,$template='similar') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	if ( !is_array($tagids) ) {
		$tagids = getTagIds(strval($tagids));
	}
	$ids = downloads_search_tags($tagids);
	$ids[] = -1;
	$tagfilter = " AND id IN (".implode(', ', $ids).") ";
	
	$cattree=downloads_tree($catid);
	$data=$db->fetch("SELECT * FROM ".PRE."_downloads WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".$tagfilter." ".section_filter()." ) ORDER BY addtime DESC LIMIT ".iif($start,$start.',').$count);
	
	downloads_print($data,'functions/'.$template);
}



//Downloads zu einem Produkt ausgeben
function downloads_product($prodid=0,$count=5,$start=0,$catid=false,$template='productdownloads') {
	global $set,$db,$apx,$user;
	$prodid=(int)$prodid;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( !$prodid ) return;
	
	$cattree=downloads_tree($catid);
	$data=$db->fetch("SELECT * FROM ".PRE."_downloads WHERE ( ( '".time()."' BETWEEN starttime AND endtime )  AND prodid='".$prodid."' ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	downloads_print($data,'functions/'.$template);
}



//AUSGABE
function downloads_print($data,$template) {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	
	$apx->lang->drop('global','downloads');
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars($template,'downloads');
	
	//Kategorien auslesen
	if ( in_array('DOWNLOAD.CATTITLE',$parse) || in_array('DOWNLOAD.CATTEXT',$parse) || in_array('DOWNLOAD.CATICON',$parse) || in_array('DOWNLOAD.CATLINK',$parse) ) {
		$catids=get_ids($data,'catid');
		if ( count($catids) ) {
			$catdata=$db->fetch("SELECT id,title,text,icon FROM ".PRE."_downloads_cat WHERE id IN (".implode(',',$catids).")");
			if ( count($catdata) ) {
				foreach ( $catdata AS $catres ) $catinfo[$catres['id']]=$catres;
			}
		}
	}
	
	//User auslesen
	$userinfo = array();
	if ( in_template(array('DOWNLOAD.UPLOADER', 'DOWNLOAD.UPLOADER_EMAIL', 'DOWNLOAD.UPLOADER_EMAIL_ENCRYPTED'),$parse) ) {
		$userids = get_ids($data, 'userid');
		if ( $userids ) {
			$userinfo = $db->fetch_index("
				SELECT userid, username, email, pub_hidemail
				FROM ".PRE."_user
				WHERE userid IN (".implode(',', $userids).")
			", 'userid');
		}
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
				$userdata = $userinfo[$res['userid']];
				$uploader=$userdata['username'];
				$uploader_email=iif(!$userdata['pub_hidemail'],$userdata['email']);
			}
			else {
				$uploader=$res['send_username'];
				$uploader_email=$res['send_email'];
			}
			
			//Datehead
			if ( $laststamp!=date('Y/m/d',$res['starttime']-TIMEDIFF) ) {
				$tabledata[$i]['DATEHEAD']=$res['starttime'];
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
			
			//Kategorien
			$tabledata[$i]['CATID']=$res['catid'];
			$tabledata[$i]['CATTITLE']=$catinfo[$res['catid']]['title'];
			$tabledata[$i]['CATTEXT']=$catinfo[$res['catid']]['text'];
			$tabledata[$i]['CATICON']=$catinfo[$res['catid']]['icon'];
			$tabledata[$i]['CATLINK']=mklink(
				'downloads.php?catid='.$res['catid'],
				'downloads,'.$res['catid'].',1'.urlformat($catinfo[$res['catid']]['title']).'.html'
			);
			
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
					'downloads,id'.$res['id'].urlformat($res['title']).',1.html'
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
			
			
			$laststamp=date('Y/m/d',$res['starttime']-TIMEDIFF);
		}
	}
	
	$tmpl->assign('DOWNLOAD',$tabledata);
	$tmpl->parse($template,'downloads');
}



//Kategorien auflisten
function downloads_categories($catid=false,$template='categories') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$catid=(int)$catid;
	
	//Eine bestimmte Kategorie
	if ( $catid ) {
		require_once(BASEDIR.'lib/class.recursivetree.php');
		$tree = new RecursiveTree(PRE.'_downloads_cat', 'id');
		$data = $tree->getTree(array('*'), $catid);
	}
	
	//Alle Kategorien
	else {
		require_once(BASEDIR.'lib/class.recursivetree.php');
		$tree = new RecursiveTree(PRE.'_downloads_cat', 'id');
		$data = $tree->getTree(array('*'));
	}
	
	foreach ( $data AS $cat ) {
		++$i;
		$catdata[$i]['ID']=$cat['id'];
		$catdata[$i]['TITLE']=$cat['title'];
		$catdata[$i]['ICON']=$cat['icon'];
		$catdata[$i]['LEVEL']=$cat['level'];
		$catdata[$i]['LINK']=mklink(
			'downloads.php?catid='.$id,
			'downloads,'.$cat['id'].',1'.urlformat($cat['title']).'.html'
		);
	}
	
	$tmpl->assign('CATEGORY',$catdata);
	$tmpl->parse('functions/'.$template,'downloads');
}



//Tags auflisten
function downloads_tagcloud($count=10, $random=false, $template='tagcloud') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$catid=(int)$catid;
	
	if ( $random ) {
		$orderby = "RAND()";
	}
	else {
		$orderby = "weight DESC";
	}
	
	//Sektion gewählt
	if ( $apx->section_id() ) {
		$data = $db->fetch("
			SELECT t.tagid, t.tag, count(nt.id) AS weight
			FROM ".PRE."_downloads_tags AS nt
			LEFT JOIN ".PRE."_downloads AS n ON nt.id=n.id
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_downloads_tags AS nt2 ON nt.tagid=nt2.tagid
			WHERE 1 ".section_filter(true, 'n.secid')."
			GROUP BY nt.tagid
			ORDER BY ".$orderby."
			LIMIT ".$count."
		");
	}
	
	//Keine Sektion gewählt
	else {
		$data = $db->fetch("
			SELECT t.tagid, t.tag, count(nt.id) AS weight
			FROM ".PRE."_downloads_tags AS nt
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_downloads_tags AS nt2 ON nt.tagid=nt2.tagid
			GROUP BY nt.tagid
			ORDER BY ".$orderby."
			LIMIT ".$count."
		");
	}
	
	if ( count($data) ) {
		$maxweight = 1;
		foreach ( $data AS $res ) {
			if ( $res['weight']>$maxweight ) {
				$maxweight = $res['weight'];
			}
		}
		if ( !$random ) {
			shuffle($data);
		}
		foreach ( $data AS $res ) {
			$tagdata[] = array(
				'ID' => $res['tagid'],
				'NAME' => replace($res['tag']),
				'WEIGHT' => $res['weight']/$maxweight
			);
		}
	}
	
	$tmpl->assign('TAG',$tagdata);
	$tmpl->parse('functions/'.$template,'downloads');
}



//Statistik anzeigen
function downloads_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'downloads');
	
	$apx->lang->drop('func_stats', 'downloads');
	
	if ( in_array('COUNT_CATEGORIES', $parse) ) {
		list($count) = $db->first("SELECT count(id) FROM ".PRE."_downloads_cat");
		$tmpl->assign('COUNT_CATEGORIES', $count);
	}
	if ( in_template(array('COUNT_DOWNLOADS', 'AVG_HITS'), $parse) ) {
		list($count, $hits) = $db->first("
			SELECT count(id), avg(hits) FROM ".PRE."_downloads
			WHERE ".time()." BETWEEN starttime AND endtime
		");
		$tmpl->assign('COUNT_DOWNLOADS', $count);
		$tmpl->assign('AVG_HITS', round($hits));
	}
	
	$tmpl->parse('functions/'.$template,'downloads');
}

?>