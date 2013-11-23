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



require_once(BASEDIR.getmodulepath('videos').'functions.php');



//Neuste Videos auflisten
function videos_last($count=5,$start=0,$catid=false,$template='last') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=videos_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_videos AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.status='finished' AND ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	videos_print($data,'functions/'.$template);
}



//Zufällige Videos auflisten
function videos_random($count=5,$start=0,$catid=false,$template='random') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=videos_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_videos AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.status='finished' AND ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) ORDER BY RAND() LIMIT ".iif($start,$start.',').$count);
	
	videos_print($data,'functions/'.$template);
}



//TOP-Videos auflisten
function videos_top($count=5,$start=0,$catid=false,$template='top') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=videos_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_videos AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.status='finished' AND ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." AND top='1' ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	videos_print($data,'functions/'.$template);
}



//Nicht-TOP-Videos auflisten
function videos_nottop($count=5,$start=0,$catid=false,$template='nottop') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=videos_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_videos AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.status='finished' AND ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." AND top='0' ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	videos_print($data,'functions/'.$template);
}



//Beste Videos: Hits
function videos_best_hits($count=5,$start=0,$catid=false,$template='best_hits') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=videos_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_videos AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.status='finished' AND ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) ORDER BY hits DESC LIMIT ".iif($start,$start.',').$count);
	
	videos_print($data,'functions/'.$template);
}



//Beste Videos: Bewertung
function videos_best_rating($count=5,$start=0,$catid=false,$template='best_rating') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( !$apx->is_module('ratings') ) return '';
	
	$cattree=videos_tree($catid);
	$data=$db->fetch("SELECT avg(rating) AS rating,count(*) AS votes,b.* FROM ".PRE."_ratings AS a LEFT JOIN ".PRE."_videos AS b ON a.mid=b.id AND a.module='videos' WHERE ( b.status='finished' AND ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) GROUP BY a.mid ORDER BY rating DESC,votes DESC LIMIT ".iif($start,$start.',').$count);
	
	videos_print($data,'functions/'.$template);
}



//Ähnliche Videos auflisten
function videos_similar($tagids=array(),$count=5,$start=0,$catid=false,$template='similar') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	if ( !is_array($tagids) ) {
		$tagids = getTagIds(strval($tagids));
	}
	$ids = videos_search_tags($tagids);
	$ids[] = -1;
	$tagfilter = " AND id IN (".implode(', ', $ids).") ";
	
	$cattree=videos_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_videos AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.status='finished' AND ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".$tagfilter." ".section_filter()." ) ORDER BY addtime DESC LIMIT ".iif($start,$start.',').$count);
	
	videos_print($data,'functions/'.$template);
}



//Videos zu einem Produkt ausgeben
function videos_product($prodid=0,$count=5,$start=0,$catid=false,$template='productvideos') {
	global $set,$db,$apx,$user;
	$prodid=(int)$prodid;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( !$prodid ) return;
	
	$cattree=videos_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_videos AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE a.status='finished' AND ( ( '".time()."' BETWEEN starttime AND endtime )  AND prodid='".$prodid."' ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	videos_print($data,'functions/'.$template);
}



//AUSGABE
function videos_print($data,$template) {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	
	$apx->lang->drop('globalwohl','videos');
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars($template,'videos');
	
	//Kategorien auslesen
	if ( in_array('VIDEO.CATTITLE',$parse) || in_array('VIDEO.CATTEXT',$parse) || in_array('VIDEO.CATICON',$parse) || in_array('VIDEO.CATLINK',$parse) ) {
		$catids=get_ids($data,'catid');
		if ( count($catids) ) {
			$catdata=$db->fetch("SELECT id,title,text,icon FROM ".PRE."_videos_cat WHERE id IN (".implode(',',$catids).")");
			if ( count($catdata) ) {
				foreach ( $catdata AS $catres ) $catinfo[$catres['id']]=$catres;
			}
		}
	}
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link=mklink(
				'videos.php?id='.$res['id'],
				'videos,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			//Teaserbild
			if ( in_array('VIDEO.PICTURE',$parse) || in_array('VIDEO.PICTURE_POPUP',$parse) || in_array('VIDEO.PICTURE_POPUPPATH',$parse) ) {
				list($picture,$picture_popup,$picture_popuppath)=videos_teaserpic($res['teaserpic']);
			}
			
			//Dateigröße auslesen
			if ( in_array('VIDEO.SIZE',$parse) ) {
				$thefsize=videos_filesize($res);
			}
			
			//Download-Link
			if ( ( !$set['videos']['regonly'] && !$res['regonly'] ) || $user->info['userid'] ) {
				$sechash=md5($_SERVER['HTTP_HOST'].$res['file'].date('Y/m/d',time()-TIMEDIFF));
				$dllink='misc.php?action=videofile&amp;id='.$res['id'].'&amp;sechash='.$sechash.iif($apx->section_id(),'&amp;sec='.$apx->section_id());
			}
			else $dllink=mklink('user.php','user.html');
			
			//Bilder
			if ( in_array('VIDEO.SCREENSHOT',$parse) ) {
				$picdata=videos_screenshots($res['id']);
			}
			
			//Neu?
			if ( ($res['addtime']+($set['videos']['new']*24*3600))>=time() ) $new=1;
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
			
			//Datehead
			if ( $laststamp!=date('Y/m/d',$res['starttime']-TIMEDIFF) ) {
				$tabledata[$i]['DATEHEAD']=$res['starttime'];
			}
			
			//Text
			$text = '';
			if ( in_array('VIDEO.TEXT',$parse) ) {
				$text = mediamanager_inline($res['text']);
				if ( $apx->is_module('glossar') ) $text = glossar_highlight($text);
			}
			
			//Tags
			if ( in_array('VIDEO.TAG',$parse) || in_array('VIDEO.TAG_IDS',$parse) || in_array('VIDEO.KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = videos_tags($res['id']);
			}
			
			//Embeded?
			if ( $res['source']!='apexx' && $res['source']!='external' ) {
				$embedcode = videos_embedcode($res['source'], $res['flvfile']);
				$file = '';
				$flvfile = '';
				$dllink = '';
			}
			
			//Extern
			elseif ( $res['source']=='external' ) {
				$embedcode = '';
				$flvfile = $res['flvfile'];
				if ( $res['file'] ) {
					$file = $res['file'];
				}
				else {
					$dllink = '';
				}
			}
			
			//Lokal
			else {
				$embedcode = '';
				$flvfile = HTTPDIR.getpath('uploads').$res['flvfile'];
				if ( $res['file'] ) {
					$file = HTTP_HOST.HTTPDIR.getpath('uploads').$res['file'];
				}
				else {
					$dllink = '';
				}
			}
			
			$tabledata[$i]['ID'] = $res['id'];
			$tabledata[$i]['SECID'] = $res['secid'];
			$tabledata[$i]['USERID'] = $res['userid'];
			$tabledata[$i]['USERNAME'] = replace($uploader);
			$tabledata[$i]['EMAIL'] = replace($uploader_email);
			$tabledata[$i]['EMAIL_ENCRYPTED'] = replace(cryptMail($uploader_email));
			$tabledata[$i]['TITLE'] = $res['title'];
			$tabledata[$i]['TEXT'] = $text;
			$tabledata[$i]['LINK'] = $link;
			$tabledata[$i]['PICTURE'] = $picture;
			$tabledata[$i]['PICTURE_POPUP'] = $picture_popup;
			$tabledata[$i]['PICTURE_POPUPPATH'] = $picture_popuppath;
			$tabledata[$i]['SIZE'] = videos_getsize($thefsize);
			$tabledata[$i]['HITS'] = number_format($res['hits'],0,'','.');
			$tabledata[$i]['TIME'] = $res['starttime'];
			$tabledata[$i]['SCREENSHOT'] = $picdata;
			$tabledata[$i]['SOURCE'] = $res['source']=='external' ? 'apexx' : $res['source'];
			$tabledata[$i]['VIDEOFILE'] = $flvfile;
			$tabledata[$i]['EMBEDCODE'] = $embedcode;
			$tabledata[$i]['LOCAL'] = $res['source']=='apexx';
			$tabledata[$i]['TOP'] = $res['top'];
			$tabledata[$i]['RESTRICTED'] = $res['restricted'];
			$tabledata[$i]['NEW'] = $new;
			
			$tabledata[$i]['DOWNLOADLINK']=$dllink;
			$tabledata[$i]['DOWNLOADFILE'] = $file;
			$tabledata[$i]['DOWNLOADS'] = number_format($res['downloads'],0,'','.');
			
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
			
			//Kommentare
			if ( $apx->is_module('comments') && $set['videos']['coms'] && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('videos',$res['id']);
				else $coms->mid=$res['id'];
				
				$link=mklink(
					'videos.php?id='.$res['id'],
					'videos,id'.$res['id'].urlformat($res['title']).'.html'
				);
				
				$tabledata[$i]['COMMENT_COUNT']=$coms->count();
				$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
				$tabledata[$i]['DISPLAY_COMMENTS']=1;
				if ( in_template(array('VIDEO.COMMENT_LAST_USERID','VIDEO.COMMENT_LAST_NAME','VIDEO.COMMENT_LAST_TIME'),$parse) ) {
					$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
					$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
					$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
			
			//Bewertungen
			if ( $apx->is_module('ratings') && $set['videos']['ratings'] && $res['allowrating'] ) {
				require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
				if ( !isset($rate) ) $rate=new ratings('videos',$res['id']);
				else $rate->mid=$res['id'];
				
				$tabledata[$i]['RATING']=$rate->display();
				$tabledata[$i]['RATING_VOTES']=$rate->count();
				$tabledata[$i]['DISPLAY_RATING']=1;
			}
			
			
			$laststamp=date('Y/m/d',$res['starttime']-TIMEDIFF);
		}
	}
	
	$tmpl->assign('VIDEO',$tabledata);
	$tmpl->parse($template,'videos');
}



//Kategorien auflisten
function videos_categories($catid=false,$template='categories') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$catid=(int)$catid;
	
	//Eine bestimmte Kategorie
	if ( $catid ) {
		require_once(BASEDIR.'lib/class.recursivetree.php');
		$tree = new RecursiveTree(PRE.'_videos_cat', 'id');
		$data = $tree->getTree(array('*'), $catid);
	}
	
	//Alle Kategorien
	else {
		require_once(BASEDIR.'lib/class.recursivetree.php');
		$tree = new RecursiveTree(PRE.'_videos_cat', 'id');
		$data = $tree->getTree(array('*'));
	}
	
	foreach ( $data AS $cat ) {
		++$i;
		$catdata[$i]['ID']=$cat['id'];
		$catdata[$i]['TITLE']=$cat['title'];
		$catdata[$i]['ICON']=$cat['icon'];
		$catdata[$i]['LEVEL']=$cat['level'];
		$catdata[$i]['LINK']=mklink(
			'videos.php?catid='.$id,
			'videos,'.$cat['id'].',1'.urlformat($cat['title']).'.html'
		);
	}
	
	$tmpl->assign('CATEGORY',$catdata);
	$tmpl->parse('functions/'.$template,'videos');
}



//Tags auflisten
function videos_tagcloud($count=10, $random=false, $template='tagcloud') {
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
			FROM ".PRE."_videos_tags AS nt
			LEFT JOIN ".PRE."_videos AS n ON nt.id=n.id
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_videos_tags AS nt2 ON nt.tagid=nt2.tagid
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
			FROM ".PRE."_videos_tags AS nt
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_videos_tags AS nt2 ON nt.tagid=nt2.tagid
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
	$tmpl->parse('functions/'.$template,'videos');
}



//Statistik anzeigen
function videos_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'videos');
	
	$apx->lang->drop('func_stats', 'videos');
	
	if ( in_array('COUNT_CATEGORIES', $parse) ) {
		list($count) = $db->first("SELECT count(id) FROM ".PRE."_videos_cat");
		$tmpl->assign('COUNT_CATEGORIES', $count);
	}
	if ( in_template(array('COUNT_VIDEOS', 'AVG_HITS'), $parse) ) {
		list($count, $hits) = $db->first("
			SELECT count(id), avg(hits) FROM ".PRE."_videos
			WHERE status='finished' AND ".time()." BETWEEN starttime AND endtime
		");
		$tmpl->assign('COUNT_VIDEOS', $count);
		$tmpl->assign('AVG_HITS', round($hits));
	}
	
	$tmpl->parse('functions/'.$template,'videos');
}

?>