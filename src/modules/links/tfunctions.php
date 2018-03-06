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



require_once(BASEDIR.getmodulepath('links').'functions.php');



//Neuste Links auflisten
function links_last($count=5,$start=0,$catid=false,$template='last') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=links_tree($catid);
	$data=$db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_links AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	links_print($data,'functions/'.$template);
}



//Zufällige Links auflisten
function links_random($count=5,$start=0,$catid=false,$template='random') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=links_tree($catid);
	$data=$db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_links AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) ORDER BY RAND() LIMIT ".iif($start,$start.',').$count);
	
	links_print($data,'functions/'.$template);
}



//Nicht-TOP-Links auflisten
function links_top($count=5,$start=0,$catid=false,$template='nottop') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=links_tree($catid);
	$data=$db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_links AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." AND top='1' ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	links_print($data,'functions/'.$template);
}



//Nicht-TOP-Links auflisten
function links_nottop($count=5,$start=0,$catid=false,$template='nottop') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=links_tree($catid);
	$data=$db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_links AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." AND top='0' ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	links_print($data,'functions/'.$template);
}



//Beste Links: Hits
function links_best_hits($count=5,$start=0,$catid=false,$template='best_hits') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	$cattree=links_tree($catid);
	$data=$db->fetch("SELECT a.*,b.username,b.email,b.pub_hidemail FROM ".PRE."_links AS a LEFT JOIN ".PRE."_user AS b USING(userid) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) ORDER BY hits DESC LIMIT ".iif($start,$start.',').$count);
	
	links_print($data,'functions/'.$template);
}



//Beste Links: Bewertung
function links_best_rating($count=5,$start=0,$catid=false,$template='best_rating') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	if ( !$apx->is_module('ratings') ) return '';
	
	$cattree=links_tree($catid);
	$data=$db->fetch("SELECT avg(rating) AS rating,count(*) AS votes,b.*,c.username,c.email,c.pub_hidemail FROM ".PRE."_ratings AS a LEFT JOIN ".PRE."_links AS b ON a.mid=b.id AND a.module='links' LEFT JOIN ".PRE."_user AS c USING(userid) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".section_filter()." ) GROUP BY a.mid ORDER BY rating DESC,votes DESC LIMIT ".iif($start,$start.',').$count);
	
	links_print($data,'functions/'.$template);
}



//Ähnliche Links auflisten
function links_similar($tagids=array(),$count=5,$start=0,$catid=false,$template='similar') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$catid=(int)$catid;
	
	if ( !is_array($tagids) ) {
		$tagids = getTagIds(strval($tagids));
	}
	$ids = links_search_tags($tagids);
	$ids[] = -1;
	$tagfilter = " AND id IN (".implode(', ', $ids).") ";
	
	$cattree=links_tree($catid);
	$data=$db->fetch("SELECT * FROM ".PRE."_links WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".iif(count($cattree)," AND catid IN (".@implode(',',$cattree).") ")." ".$tagfilter." ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	
	links_print($data,'functions/'.$template);
}



//AUSGABE
function links_print($data,$template) {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	
	$apx->lang->drop('global','links');
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars($template,'links');
	
	//Kategorien auslesen
	if ( in_array('LINK.CATTITLE',$parse) || in_array('LINK.CATTEXT',$parse) || in_array('LINK.CATICON',$parse) || in_array('LINK.CATLINK',$parse) ) {
		$catids=get_ids($data,'catid');
		if ( count($catids) ) {
			$catdata=$db->fetch("SELECT id,title,text,icon FROM ".PRE."_links_cat WHERE id IN (".implode(',',$catids).")");
			if ( count($catdata) ) {
				foreach ( $catdata AS $catres ) $catinfo[$catres['id']]=$catres;
			}
		}
	}
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Dateillink
			$link=mklink(
				'links.php?id='.$res['id'],
				'links,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			//Neu?
			if ( ($res['starttime']+($set['links']['new']*24*3600))>=time() ) $new=1;
			else $new=0;
			
			//Goto-Link
			$gotolink='misc.php?action=gotolink&amp;id='.$res['id'].iif($apx->section_id(),'&amp;sec='.$apx->section_id());
			
			//Linkpic
			if ( in_array('LINK.PICTURE',$parse) || in_array('LINK.PICTURE_POPUP',$parse) || in_array('LINK.PICTURE_POPUPPATH',$parse) ) {
				list($picture,$picture_popup,$picture_popuppath)=links_linkpic($res['linkpic']);
			}
			
			//Username + eMail
			if ( $res['userid'] ) {
				$author=$res['username'];
				$author_email=iif(!$res['pub_hidemail'],$res['email']);
			}
			else {
				$author=$res['send_username'];
				$author_email=$res['send_email'];
			}
			
			//Text
			$text = '';
			if ( in_array('LINK.TEXT',$parse) ) {
				$text = mediamanager_inline($res['text']);
				if ( $apx->is_module('glossar') ) $text = glossar_highlight($text);
			}
			
			//Datehead
			if ( $laststamp!=date('Y/m/d',$res['starttime']-TIMEDIFF) ) {
				$tabledata[$i]['DATEHEAD']=$res['starttime'];
			}
			
			//Tags
			if ( in_array('LINK.TAG',$parse) || in_array('LINK.TAG_IDS',$parse) || in_array('LINK.KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = links_tags($res['id']);
			}
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['TITLE']=$res['title'];
			$tabledata[$i]['URL']=$res['url'];
			$tabledata[$i]['TEXT']=$text;
			$tabledata[$i]['LINK']=$link;
			$tabledata[$i]['PICTURE']=$picture;
			$tabledata[$i]['PICTURE_POPUP']=$picture_popup;
			$tabledata[$i]['PICTURE_POPUPPATH']=$picture_popuppath;
			$tabledata[$i]['HITS']=number_format($res['hits'],0,'','.');
			$tabledata[$i]['TIME']=$res['starttime'];
			$tabledata[$i]['TOP']=$res['top'];
			$tabledata[$i]['RESTRICTED']=$res['restricted'];
			$tabledata[$i]['NEW']=$new;
			$tabledata[$i]['GOTO']=$gotolink;
			
			//Tags
			$tabledata[$i]['TAG']=$tagdata;
			$tabledata[$i]['TAG_IDS']=$tagids;
			$tabledata[$i]['KEYWORDS']=$keywords;
			
			//Autor
			$tabledata[$i]['USERID']=$res['userid'];
			$tabledata[$i]['USERNAME']=replace($author);
			$tabledata[$i]['EMAIL']=replace($author_email);
			$tabledata[$i]['EMAIL_ENCRYPTED']=replace(cryptMail($author_email));
			
			//Kategorien
			$tabledata[$i]['CATID']=$res['catid'];
			$tabledata[$i]['CATTITLE']=$catinfo[$res['catid']]['title'];
			$tabledata[$i]['CATTEXT']=$catinfo[$res['catid']]['text'];
			$tabledata[$i]['CATICON']=$catinfo[$res['catid']]['icon'];
			$tabledata[$i]['CATLINK']=mklink(
				'links.php?catid='.$res['catid'],
				'links,'.$res['catid'].',1'.urlformat($catinfo[$res['catid']]['title']).'.html'
			);
			
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
			if ( $apx->is_module('comments') && $set['links']['coms'] && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('links',$res['id']);
				else $coms->mid=$res['id'];
				
				$link=mklink(
					'links.php?id='.$res['id'],
					'links,id'.$res['id'].urlformat($res['title']).'.html'
				);
				
				$tabledata[$i]['COMMENT_COUNT']=$coms->count();
				$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
				$tabledata[$i]['DISPLAY_COMMENTS']=1;
				if ( in_template(array('LINK.COMMENT_LAST_USERID','LINK.COMMENT_LAST_NAME','LINK.COMMENT_LAST_TIME'),$parse) ) {
					$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
					$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
					$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
			
			//Bewertungen
			if ( $apx->is_module('ratings') && $set['links']['ratings'] && $res['allowrating'] ) {
				require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
				if ( !isset($rate) ) $rate=new ratings('links',$res['id']);
				else $rate->mid=$res['id'];
				
				$tabledata[$i]['RATING']=$rate->display();
				$tabledata[$i]['RATING_VOTES']=$rate->count();
				$tabledata[$i]['DISPLAY_RATING']=1;
			}
			
			$laststamp=date('Y/m/d',$res['starttime']-TIMEDIFF);
		}
	}
	
	$tmpl->assign('LINK',$tabledata);
	$tmpl->parse($template,'links');
}



//Kategorien auflisten
function links_categories($catid=false,$template='categories') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$catid=(int)$catid;
	
	//Eine bestimmte Kategorie
	if ( $catid ) {
		require_once(BASEDIR.'lib/class.recursivetree.php');
		$tree = new RecursiveTree(PRE.'_links_cat', 'id');
		$data = $tree->getTree(array('*'), $catid);
	}
	
	//Alle Kategorien
	else {
		require_once(BASEDIR.'lib/class.recursivetree.php');
		$tree = new RecursiveTree(PRE.'_links_cat', 'id');
		$data = $tree->getTree(array('*'));
	}
	
	foreach ( $data AS $cat ) {
		++$i;
		$catdata[$i]['ID']=$cat['id'];
		$catdata[$i]['TITLE']=$cat['title'];
		$catdata[$i]['ICON']=$cat['icon'];
		$catdata[$i]['LEVEL']=$cat['level'];
		$catdata[$i]['LINK']=mklink(
			'links.php?catid='.$cat['id'],
			'links,'.$cat['id'].',1'.urlformat($cat['title']).'.html'
		);
	}
	
	$tmpl->assign('CATEGORY',$catdata);
	$tmpl->parse('functions/'.$template,'links');
}



//Tags auflisten
function links_tagcloud($count=10, $random=false, $template='tagcloud') {
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
			FROM ".PRE."_links_tags AS nt
			LEFT JOIN ".PRE."_links AS n ON nt.id=n.id
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_links_tags AS nt2 ON nt.tagid=nt2.tagid
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
			FROM ".PRE."_links_tags AS nt
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_links_tags AS nt2 ON nt.tagid=nt2.tagid
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
	$tmpl->parse('functions/'.$template,'links');
}



//Statistik anzeigen
function links_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'links');
	
	$apx->lang->drop('func_stats', 'links');
	
	if ( in_array('COUNT_CATEGORIES', $parse) ) {
		list($count) = $db->first("SELECT count(id) FROM ".PRE."_links_cat");
		$tmpl->assign('COUNT_CATEGORIES', $count);
	}
	if ( in_template(array('COUNT_LINKS', 'AVG_HITS'), $parse) ) {
		list($count, $hits) = $db->first("
			SELECT count(id), avg(hits) FROM ".PRE."_links
			WHERE ".time()." BETWEEN starttime AND endtime
		");
		$tmpl->assign('COUNT_LINKS', $count);
		$tmpl->assign('AVG_HITS', round($hits));
	}
	
	$tmpl->parse('functions/'.$template,'links');
}

?>
