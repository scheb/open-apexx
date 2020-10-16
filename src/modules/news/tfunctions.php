<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



require_once(BASEDIR.getmodulepath('news').'functions.php');



//Letzte News
function news_last($count=5,$start=0,$catid=false,$template='last') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=news_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ".time()." BETWEEN starttime AND endtime ".iif(count($cattree),' AND catid IN ('.@implode(',',$cattree).') ' )." ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	news_print($data,'functions/'.$template);
}



//Zufällige News
function news_random($count=5,$start=0,$catid=false,$template='random') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=news_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ".time()." BETWEEN starttime AND endtime ".iif(count($cattree),' AND catid IN ('.@implode(',',$cattree).') ' )." ".section_filter()." ) ORDER BY RAND() LIMIT ".iif($start,$start.',').$count);
	
	news_print($data,'functions/'.$template);
}



//TOP-News ausgeben
function news_top($count=5,$start=0,$catid=false,$template='top') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=news_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ".time()." BETWEEN starttime AND endtime ".iif(count($cattree),' AND catid IN ('.@implode(',',$cattree).') ' )." AND top='1'  ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	news_print($data,'functions/'.$template);
}



//Nicht-TOP-News ausgeben
function news_nottop($count=5,$start=0,$catid=false,$template='nottop') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=news_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ".time()." BETWEEN starttime AND endtime ".iif(count($cattree),' AND catid IN ('.@implode(',',$cattree).') ' )." AND top='0'  ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	news_print($data,'functions/'.$template);
}



//Beste News: Hits
function news_best_hits($count=5,$start=0,$catid=false,$template='best_hits') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=news_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ".time()." BETWEEN starttime AND endtime ".iif(count($cattree),' AND catid IN ('.@implode(',',$cattree).') ' )."  ".section_filter()." ) ORDER BY hits DESC LIMIT ".iif($start,$start.',').$count);
	
	news_print($data,'functions/'.$template);
}



//Beste News: Bewertung
function news_best_rating($count=5,$start=0,$catid=false,$template='best_rating') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( !$apx->is_module('ratings') ) return '';
	
	$cattree=news_tree($catid);
	$data=$db->fetch("SELECT avg(rating) AS rating,count(*) AS votes,b.*,c.userid,c.username,c.email,c.pub_hidemail FROM ".PRE."_ratings AS a LEFT JOIN ".PRE."_news AS b ON a.mid=b.id AND a.module='news' LEFT JOIN ".PRE."_user AS c USING(userid) WHERE ( ".time()." BETWEEN b.starttime AND b.endtime ".iif(count($cattree),' AND b.catid IN ('.@implode(',',$cattree).') ' )."  ".section_filter()." ) GROUP BY a.mid ORDER BY rating DESC,votes DESC LIMIT ".iif($start,$start.',').$count);
	
	news_print($data,'functions/'.$template);
}



//Ähnliche News
function news_similar($tagids=array(),$count=5,$start=0,$catid=false,$template='similar') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	if ( !is_array($tagids) ) {
		$tagids = getTagIds(strval($tagids));
	}
	$ids = news_search_tags($tagids);
	$ids[] = -1;
	$tagfilter = " AND a.id IN (".implode(', ', $ids).") ";
	
	$cattree=news_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ".time()." BETWEEN starttime AND endtime ".iif(count($cattree),' AND catid IN ('.@implode(',',$cattree).') ' )." ".$tagfilter." ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	news_print($data,'functions/'.$template);
}



//News zum einem Produkt
function news_product($prodid=0,$count=5,$start=0,$catid=false,$template='productnews') {
	global $set,$db,$apx,$user;
	$prodid=(int)$prodid;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( !$prodid ) return;
	
	$cattree=news_tree($catid);
	$data=$db->fetch("SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ".time()." BETWEEN starttime AND endtime AND prodid='".$prodid."' ".iif(count($cattree),' AND catid IN ('.@implode(',',$cattree).') ' )." ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	news_print($data,'functions/'.$template);
}



//AUSGABE
function news_print($data,$template) {
	global $set,$db,$apx,$user; 
	$tmpl=new tengine;
	
	$apx->lang->drop('func','news');
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars($template,'news');
	
	//Kategorien auslesen
	if ( in_array('NEWS.CATID',$parse) || in_array('NEWS.CATTITLE',$parse) || in_array('NEWS.CATICON',$parse) || in_array('NEWS.CATLINK',$parse) ) {
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
	
	$tmpl->assign('NEWS',$tabledata);
	$tmpl->parse($template,'news');
}



//Kategorien auflisten
function news_categories($catid=false,$template='categories') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$catid=(int)$catid;
	
	//Eine bestimmte Kategorie
	if ( $catid && $set['news']['subcats'] ) {
		require_once(BASEDIR.'lib/class.recursivetree.php');
		$tree=new RecursiveTree(PRE.'_news_cat', 'id');
		$data = $tree->getTree(array('*'), $catid);
	}
	
	//Alle Kategorien
	else {
		$data=news_catinfo();
	}
	
	foreach ( $data AS $cat ) {
		++$i;
		
		//Kategorie-Link
		if ( isset($cat['link']) ) $link=$cat['link'];
		else $link=mklink(
			'news.php?catid='.$res['id'],
			'news,'.$res['id'].',1.html'
		);
		
		$catdata[$i]['ID']=$cat['id'];
		$catdata[$i]['TITLE']=$cat['title'];
		$catdata[$i]['ICON']=$cat['icon'];
		$catdata[$i]['LINK']=$link;
		$catdata[$i]['LEVEL']=$cat['level'];
	}
	
	$tmpl->assign('CATEGORY',$catdata);
	$tmpl->parse('functions/'.$template,'news');
}



//Tags auflisten
function news_tagcloud($count=10, $random=false, $template='tagcloud') {
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
			FROM ".PRE."_news_tags AS nt
			LEFT JOIN ".PRE."_news AS n ON nt.id=n.id
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_news_tags AS nt2 ON nt.tagid=nt2.tagid
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
			FROM ".PRE."_news_tags AS nt
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_news_tags AS nt2 ON nt.tagid=nt2.tagid
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
	$tmpl->parse('functions/'.$template,'news');
}



//Statistik anzeigen
function news_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'news');
	
	$apx->lang->drop('func_stats', 'news');
	
	if ( in_array('COUNT_CATEGORIES', $parse) ) {
		list($count) = $db->first("SELECT count(id) FROM ".PRE."_news_cat");
		$tmpl->assign('COUNT_CATEGORIES', $count);
	}
	if ( in_template(array('COUNT_NEWS', 'AVG_HITS'), $parse) ) {
		list($count, $hits) = $db->first("
			SELECT count(id), avg(hits) FROM ".PRE."_news
			WHERE ".time()." BETWEEN starttime AND endtime
		");
		$tmpl->assign('COUNT_NEWS', $count);
		$tmpl->assign('AVG_HITS', round($hits));
	}
	
	$tmpl->parse('functions/'.$template,'news');
}

?>
