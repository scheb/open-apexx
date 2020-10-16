<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



require_once(BASEDIR.getmodulepath('gallery').'functions.php');



//Galerie wählen-Box
function gallery_choose() {
	global $set,$db,$apx,$user;
	
	$tmpl=new tengine;
	$apx->lang->drop('func_choose','gallery');
	
	if ( $set['gallery']['subgals'] ) {
		require_once(BASEDIR.'lib/class.recursivetree.php');
		$tree = new RecursiveTree(PRE.'_gallery', 'id');
		$data = $tree->getTree(array('title'), null, "'".time()."' BETWEEN starttime AND endtime");
	}
	else $data=$db->fetch("SELECT id,title FROM ".PRE."_gallery WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter()." ) ORDER BY title ASC");
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['TITLE']=strip_tags($res['title']);
			$tabledata[$i]['LEVEL']=iif($res['level'],$res['level'],1);
		}
	}
	
	$tmpl->assign('GALLERY',$tabledata);
	$tmpl->assign('POSTTO',mklink('gallery.php','gallery.html'));
	$tmpl->parse('functions/choose','gallery');
}



//Neuste Galerien
function gallery_last($count=5,$start=0,$template='last') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	
	$data=$db->fetch("SELECT * FROM ".PRE."_gallery WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	gallery_print($data,'functions/'.$template);
}



//Zufällige Galerien
function gallery_random($count=5,$start=0,$template='random') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	
	$data=$db->fetch("SELECT * FROM ".PRE."_gallery WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter()." ) ORDER BY RAND() LIMIT ".iif($start,$start.',').$count);
	gallery_print($data,'functions/'.$template);
}



//Aktualisierte Galerien
function gallery_updated($count=5,$start=0,$template='updated') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	
	$open=gallery_active();
	$data=$db->fetch("SELECT max(a.addtime) AS updatetime,b.* FROM ".PRE."_gallery_pics AS a LEFT JOIN ".PRE."_gallery AS b ON a.galid=b.id WHERE ( a.active='1' AND ( '".time()."' BETWEEN b.starttime AND b.endtime ) AND b.id IN (".implode(',',$open).") ".section_filter(true,'b.secid')." ) GROUP BY galid ORDER BY updatetime DESC LIMIT ".iif($start,$start.',').$count);
	gallery_print($data,'functions/'.$template);
}



//Neuste Galerien
function gallery_similar($tagids=array(),$count=5,$start=0,$template='similar') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	
	if ( !is_array($tagids) ) {
		$tagids = getTagIds(strval($tagids));
	}
	$ids = gallery_search_tags($tagids);
	$ids[] = -1;
	$tagfilter = " AND id IN (".implode(', ', $ids).") ";
	
	$data=$db->fetch("SELECT * FROM ".PRE."_gallery WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".$tagfilter." ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	gallery_print($data,'functions/'.$template);
}



//Galerien zu einem Produkt
function gallery_products($prodid=0,$count=5,$start=0,$template='productgallery') {
	global $set,$db,$apx,$user;
	$prodid=(int)$prodid;
	$count=(int)$count;
	$start=(int)$start;
	if ( !$prodid ) return;
	
	$data=$db->fetch("SELECT * FROM ".PRE."_gallery WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) AND prodid='".$prodid."' ".section_filter()." ) ORDER BY starttime DESC LIMIT ".iif($start,$start.',').$count);
	gallery_print($data,'functions/'.$template);
}



//AUSGABE: Galerie
function gallery_print($data,$template) {
	global $set,$db,$apx,$user;	
	$tmpl=new tengine;
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars($template,'gallery');
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link=mklink(
				'gallery.php?id='.$res['id'],
				'gallery,list'.$res['id'].',1'.urlformat($res['title']).'.html'
			);
			
			//Enthaltene Bilder, Letzte Aktualisierung
			if ( in_template(array('GALLERY.COUNT', 'GALLERY.UPDATETIME'),$parse) ) {
				list($count, $res['updatetime'])=$db->first("SELECT count(id), max(addtime) FROM ".PRE."_gallery_pics WHERE ( galid='".$res['id']."' AND active='1' )");
			}
			
			//Vorschau-Bild
			if ( in_array('GALLERY.PREVIEW',$parse) ) {
				if ( $res['preview'] && file_exists(BASEDIR.getpath('uploads').$res['preview']) ) {
					$preview=getpath('uploads').$res['preview'];
				}
				else {
					list($image)=$db->first("SELECT thumbnail FROM ".PRE."_gallery_pics WHERE ( galid='".$res['id']."' AND active='1' ) ORDER BY addtime DESC,id DESC LIMIT 1");
					$preview=getpath('uploads').$image;
				}
				$fullsize_preview = str_replace('-thumb', '', $preview);
				if ( !file_exists(BASEDIR.$fullsize_preview) ) {
					$fullsize_preview = '';
				}
			}
			
			//Datehead
			if ( $laststamp!=date('Y/m/d',$res['starttime']-TIMEDIFF) ) {
				$tabledata[$i]['DATEHEAD']=$res['starttime'];
			}
			
			//Tags
			if ( in_array('GALLERY.TAG',$parse) || in_array('GALLERY.TAG_IDS',$parse) || in_array('GALLERY.KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = gallery_tags($res['id']);
			}
			
			$tabledata[$i]['SECID']=$res['secid'];
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['TITLE']=$res['title'];
			$tabledata[$i]['DESCRIPTION']=$res['description'];
			$tabledata[$i]['RESTRICTED']=$res['restricted'];
			$tabledata[$i]['TIME']=$res['starttime'];
			$tabledata[$i]['UPDATETIME']=$res['updatetime'];
			$tabledata[$i]['LINK']=$link;
			$tabledata[$i]['COUNT']=$count;
			$tabledata[$i]['PREVIEW']=iif($preview, HTTPDIR.$preview);
			$tabledata[$i]['PREVIEW_FULLSIZE']=iif($fullsize_preview, HTTPDIR.$fullsize_preview);
			$tabledata[$i]['PRODUCT_ID']=$res['prodid'];
			
			//Tags
			$tabledata[$i]['TAG']=$tagdata;
			$tabledata[$i]['TAG_IDS']=$tagids;
			$tabledata[$i]['KEYWORDS']=$keywords;
			
			//Kommentare
			if ( $apx->is_module('comments') && $set['gallery']['galcoms'] && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('galleryself',$res['id']);
				else $coms->mid=$res['id'];
				
				$link=mklink(
					'gallery.php?id='.$res['id'],
					'gallery,id'.$res['id'].',1'.urlformat($res['title']).'.html'
				);
				
				$tabledata[$i]['COMMENT_COUNT']=$coms->count();
				$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
				$tabledata[$i]['DISPLAY_COMMENTS']=1;
				if ( in_template(array('GALLERY.COMMENT_LAST_USERID','GALLERY.COMMENT_LAST_NAME','GALLERY.COMMENT_LAST_TIME'),$parse) ) {
					$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
					$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
					$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
			
			$laststamp=date('Y/m/d',$res['starttime']-TIMEDIFF);
		}
	}
	
	$tmpl->assign('GALLERY',$tabledata);
	$tmpl->parse($template,'gallery');
}



//Neuste Bilder
function gallery_lastpics($count=5,$start=0,$galid=false,$template='lastpics') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$galid=(int)$galid;
	
	$open=gallery_active();
	$data=$db->fetch("SELECT * FROM ".PRE."_gallery_pics WHERE ( active='1' AND galid IN (".@implode(',',$open).") ".iif($galid," AND galid='".$galid."'")." ) ORDER BY addtime DESC, id DESC LIMIT ".iif($start,$start.',').$count);
	gallery_printpic($data,'functions/'.$template);
}



//Beste Bilder: Hits
function gallery_bestpics_hits($count=5,$start=0,$galid=false,$template='bestpics_hits') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$galid=(int)$galid;
	
	$open=gallery_active();
	$data=$db->fetch("SELECT * FROM ".PRE."_gallery_pics WHERE ( active='1' AND galid IN (".@implode(',',$open).") ".iif($galid," AND galid='".$galid."'")." ) ORDER BY hits DESC LIMIT ".iif($start,$start.',').$count);
	gallery_printpic($data,'functions/'.$template);
}



//Beste Bilder: Bewertung
function gallery_bestpics_rating($count=5,$start=0,$galid=false,$template='bestpics_rating') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$start=(int)$start;
	$galid=(int)$galid;
	if ( !$apx->is_module('ratings') ) return;
	
	$open=gallery_active();
	$data=$db->fetch("SELECT avg(rating) AS rating,count(*) AS votes,b.* FROM ".PRE."_ratings AS a LEFT JOIN ".PRE."_gallery_pics AS b ON a.mid=b.id AND a.module='gallery' WHERE ( b.active='1' AND b.galid IN (".@implode(',',$open).") ".iif($galid," AND b.galid='".$galid."'")." ) GROUP BY a.mid ORDER BY rating DESC,votes DESC LIMIT ".iif($start,$start.',').$count);
	gallery_printpic($data,'functions/'.$template);
}



//Zufällige Bilder
function gallery_randompics($count=5,$galid=false,$template='randompics') {
	global $set,$db,$apx,$user;
	$count=(int)$count;
	$galid=(int)$galid;
	
	$open=gallery_active();
	$data=$db->fetch("SELECT * FROM ".PRE."_gallery_pics WHERE ( active='1' AND galid IN (".@implode(',',$open).") ".iif($galid," AND galid='".$galid."'")." ) ORDER BY RAND() LIMIT ".$count);
	gallery_printpic($data,'functions/'.$template);
}



//Bilder zu einem Produkt
function gallery_productspics($prodid=0,$count=5,$start=0,$galid=false,$template='productpics') {
	global $set,$db,$apx,$user;
	$prodid=(int)$prodid;
	$count=(int)$count;
	$start=(int)$start;
	$galid=(int)$galid;
	if ( !$prodid ) return;
	
	$open=gallery_active(false,$prodid);
	$data=$db->fetch("SELECT * FROM ".PRE."_gallery_pics WHERE ( active='1' AND galid IN (".@implode(',',$open).") ".iif($galid," AND galid='".$galid."'")." ) ORDER BY RAND() LIMIT ".iif($start,$start.',').$count);
	gallery_printpic($data,'functions/'.$template);
}



//AUSGABE Toplisten
function gallery_printpic($data,$template) {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	
	if ( !$set['gallery']['picwidth'] || !$set['gallery']['picheight'] ) {
		$set['gallery']['picwidth']=999999;
		$set['gallery']['picheight']=999999;
	}
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars($template,'gallery');
	
	if ( count($data) ) {
		
		//Galerie-Info auslesen
		$galinfo=array();
		if ( in_template(array('PICTURE.GALLERY_TITLE','PICTURE.GALLERY_DESCRIPTION','PICTURE.GALLERY_LINK','PICTURE.GALLERY_TIME','PICTURE.GALLERY_PRODUCT_ID','PICTURE.GALLERY_RESTRICTED'),$parse) ) {
			$galids=get_ids($data,'galid');
			$galinfo=$db->fetch_index("SELECT id,secid,prodid,title,description,restricted,starttime FROM ".PRE."_gallery WHERE id IN (".implode(',',$galids).")",'id');
		}
		
		//Bilder auflisten
		foreach ( $data AS $res ) {
			++$i;
			
			//GALERIE
			$gallink=mklink(
				'gallery.php?id='.$res['galid'],
				'gallery,list'.$res['galid'].',1'.urlformat($galinfo[$res['galid']]['title']).'.html'
			);
			
			//Tags
			if ( in_array('PICTURE.GALLERY_TAG',$parse) || in_array('PICTURE.GALLERY_TAG_IDS',$parse) || in_array('PICTURE.GALLERY_KEYWORDS',$parse) ) {
				list($tagdata, $tagids, $keywords) = gallery_tags($res['galid']);
			}
			
			$tabledata[$i]['GALLERY_SECID']=$galinfo[$res['galid']]['secid'];
			$tabledata[$i]['GALLERY_ID']=$res['galid'];
			$tabledata[$i]['GALLERY_TITLE']=$galinfo[$res['galid']]['title'];
			$tabledata[$i]['GALLERY_DESCRIPTION']=$galinfo[$res['galid']]['description'];
			$tabledata[$i]['GALLERY_RESTRICTED']=$galinfo[$res['galid']]['restricted'];
			$tabledata[$i]['GALLERY_TIME']=$galinfo[$res['galid']]['starttime'];
			$tabledata[$i]['GALLERY_LINK']=$gallink;
			$tabledata[$i]['GALLERY_PRODUCT_ID']=$galinfo[$res['galid']]['prodid'];
			
			//Tags
			$tabledata[$i]['GALLERY_TAG']=$tagdata;
			$tabledata[$i]['GALLERY_TAG_IDS']=$tagids;
			$tabledata[$i]['GALLERY_KEYWORDS']=$keywords;
			
			//Enthaltene Bilder, Letzte Aktualisierung
			if ( in_template(array('PICTURE.GALLERY_COUNT', 'PICTURE.GALLERY_UPDATETIME'),$parse) ) {
				list($galcount, $updatetime)=$db->first("SELECT count(id), max(addtime) FROM ".PRE."_gallery_pics WHERE ( galid='".$res['galid']."' AND active='1' )");
				$tabledata[$i]['GALLERY_COUNT']=$galcount;
				$tabledata[$i]['GALLERY_UPDATETIME']=$updatetime;
			}
			
			//BILD
			$link=mklink(
				'gallery.php?pic='.$res['id'],
				'gallery,pic'.$res['id'].urlformat($res['caption']).'.html'
			);
			if ( $set['gallery']['popup'] ) $link="javascript:popupwin('".$link."','".$set['gallery']['picwidth']."','".$set['gallery']['picheight']."',".iif($set['gallery']['popup_resizeable'],1,0).")";
			
			$tabledata[$i]['CAPTION']=$res['caption'];
			$tabledata[$i]['IMAGE']=HTTPDIR.getpath('uploads').$res['thumbnail'];
			$tabledata[$i]['FULLSIZE']=HTTPDIR.getpath('uploads').$res['picture'];
			$tabledata[$i]['LINK']=$link;
			$tabledata[$i]['TIME']=$res['addtime'];
			$tabledata[$i]['HITS']=$res['hits'];
			
			
			//Kommentare
			if ( $apx->is_module('comments') && $set['gallery']['coms'] && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('gallery',$res['id']);
				else $coms->mid=$res['id'];
				
				//Link
				$link=mklink(
					'gallery.php?pic='.$res['id'],
					'gallery,pic'.$res['id'].urlformat($res['caption']).'.html'
				);
				
				$tabledata[$i]['COMMENT_COUNT']=$coms->count();
				$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
				$tabledata[$i]['DISPLAY_COMMENTS']=1;
				if ( in_template(array('PICTURE.COMMENT_LAST_USERID','PICTURE.COMMENT_LAST_NAME','PICTURE.COMMENT_LAST_TIME'),$parse) ) {
					$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
					$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
					$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
				}
			}
			
			//Bewertungen
			if ( $apx->is_module('ratings') && $set['gallery']['ratings'] && $res['allowrating'] ) {
				require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
				if ( !isset($rate) ) $rate=new ratings('gallery',$res['id']);
				else $rate->mid=$res['id'];
				
				$tabledata[$i]['RATING']=$rate->display();
				$tabledata[$i]['RATING_VOTES']=$rate->count();
				$tabledata[$i]['DISPLAY_RATING']=1;
			}
		}
	}
	
	$tmpl->assign('PICTURE',$tabledata);
	$tmpl->parse($template,'gallery');
}



//POTW
function gallery_potw($template='potw') {
	global $set,$db,$apx,$user;
	
	//Auto-Funktion
	if ( $set['gallery']['potw_auto'] && $set['gallery']['potw_time']+7*24*3600<=time() ) {
		$db->query("UPDATE ".PRE."_gallery_pics SET potw='0'"); //Alle auf 0 setzen
		$sections=$apx->sections;
		if ( !is_array($sections) || !count($sections) ) $sections=array(0=>'');
		
		//Setze für jede Sektion neues POTW
		foreach ( $sections AS $id => $trash ) {
			$active=gallery_active($id);
			list($newpotw)=$db->first("SELECT id FROM ".PRE."_gallery_pics WHERE ( active='1' AND galid IN (".implode(',',$active).") ) ORDER BY RAND() LIMIT 1");
			if ( !$newpotw ) continue; //Nichts gefunden
			$db->query("UPDATE ".PRE."_gallery_pics SET potw='1' WHERE id='".$newpotw."' LIMIT 1");
		}
		
		$db->query("UPDATE ".PRE."_config SET value='".time()."' WHERE ( module='gallery' AND varname='potw_time' ) LIMIT 1");
	}
	
	//POTW auswählen
	$open=gallery_active();
	if ( count($open) ) {
		$res=$db->first("SELECT * FROM ".PRE."_gallery_pics WHERE ( potw='1' AND galid IN (".implode(',',$open).") ) LIMIT 1");
	}
	
	gallery_printsingle($res,'functions/'.$template);
}



//POTM
function gallery_potm($galid=0,$template='potm') {
	global $set,$db,$apx,$user;
	$galid=(int)$galid;
	
	//Zufallsauswahl
	$open=gallery_active();
	if ( count($open) && ( !$galid || in_array($galid,$open) ) ) {
		if ( $galid ) $open=array($galid);
		$res=$db->first("SELECT * FROM ".PRE."_gallery_pics WHERE ( active='1' AND galid IN (".@implode(',',$open).") ) ORDER BY RAND() LIMIT 1");
	}
	
	gallery_printsingle($res,'functions/'.$template);
}



//AUSGABE POTW & POTM
function gallery_printsingle($res,$template) {
	global $set,$db,$apx,$user;
	if ( !$res['id'] ) return;
	$tmpl=new tengine;
	
	//Voreinstellungen
	if ( !$set['gallery']['picwidth'] || !$set['gallery']['picheight'] ) {
		$set['gallery']['picwidth']=9999999;
		$set['gallery']['picheight']=9999999;
	}
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars($template,'gallery');
	
	//GALERIE
	if ( in_template(array('GALLERY_TITLE','GALLERY_DESCRIPTION','GALLERY_LINK','GALLERY_TIME','GALLERY_PRODUCT_ID','GALLERY_RESTRICTED'),$parse) ) {
		$galinfo=$db->first("SELECT secid,title,prodid,description,restricted,starttime FROM ".PRE."_gallery WHERE id='".$res['galid']."'");
	}
	
	$gallink=mklink(
		'gallery.php?id='.$res['galid'],
		'gallery,list'.$res['galid'].',1'.urlformat($galinfo['title']).'.html'
	);
	
	//Tags
	if ( in_array('GALLERY_TAG',$parse) || in_array('GALLERY_TAG_IDS',$parse) || in_array('GALLERY_KEYWORDS',$parse) ) {
		list($tagdata, $tagids, $keywords) = gallery_tags($res['galid']);
	}
	
	$tmpl->assign('GALLERY_ID',$res['galid']);
	$tmpl->assign('GALLERY_SECID',$galinfo['secid']);
	$tmpl->assign('GALLERY_TITLE',$galinfo['title']);
	$tmpl->assign('GALLERY_DESCRIPTION',$galinfo['description']);
	$tmpl->assign('GALLERY_RESTRICTED',$galinfo['restricted']);
	$tmpl->assign('GALLERY_TIME',$galinfo['starttime']);
	$tmpl->assign('GALLERY_LINK',$gallink);
	$tmpl->assign('GALLERY_PRODUCT_ID',$galinfo['prodid']);
	
	//Tags
	$tmpl->assign('TAG_IDS', $tagids);
	$tmpl->assign('TAG', $tagdata);
	$tmpl->assign('KEYWORDS', $keywords);
	
	//Enthaltene Bilder, Letzte Aktualisierung
	if ( in_template(array('GALLERY_COUNT', 'GALLERY_UPDATETIME'),$parse) ) {
		list($galcount, $updatetime)=$db->first("SELECT count(id), max(addtime) FROM ".PRE."_gallery_pics WHERE ( galid='".$res['galid']."' AND active='1' )");
		$tmpl->assign('GALLERY_COUNT',$galcount);
		$tmpl->assign('GALLERY_COUNT',$updatetime);
	}
	
	//BILD
	$link=mklink(
		'gallery.php?pic='.$res['id'],
		'gallery,pic'.$res['id'].urlformat($res['caption']).'.html'
	);
	if ( $set['gallery']['popup'] ) $link="javascript:popupwin('".$link."','".$set['gallery']['picwidth']."','".$set['gallery']['picheight']."',".iif($set['gallery']['popup_resizeable'],1,0).")";
	
	$tmpl->assign('CAPTION',$res['caption']);
	$tmpl->assign('IMAGE',getpath('uploads').$res['thumbnail']);
	$tmpl->assign('FULLSIZE',getpath('uploads').$res['picture']);
	$tmpl->assign('LINK',$link);
	$tmpl->assign('TIME',$res['addtime']);
	$tmpl->assign('HITS',number_format($res['hits'],0,'','.'));
	
	
	//Kommentare
	if ( $apx->is_module('comments') && $set['gallery']['coms'] && $res['allowcoms'] ) {
		require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
		$coms=new comments('gallery',$res['id']);
    
		//Link
		$gallink=mklink(
			'gallery.php?id='.$res['galid'],
			'gallery,list'.$res['galid'].',1'.urlformat($galinfo['title']).'.html'
		);
		
		$tmpl->assign('COMMENT_COUNT',$coms->count());
		$tmpl->assign('COMMENT_LINK',$coms->link($link));
		$tmpl->assign('DISPLAY_COMMENTS',1);
	}
	
	//Bewertungen
	if ( $apx->is_module('ratings') && $set['gallery']['ratings'] && $res['allowrating'] ) {
		require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
		$rate=new ratings('gallery',$res['id']);
		
		$tmpl->assign('RATING',$rate->display());
		$tmpl->assign('RATING_VOTES',$rate->count());
		$tmpl->assign('DISPLAY_RATING',1);
	}
	
	$tmpl->parse($template,'gallery');
}



//Tags auflisten
function gallery_tagcloud($count=10, $random=false, $template='tagcloud') {
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
			FROM ".PRE."_gallery_tags AS nt
			LEFT JOIN ".PRE."_gallery AS n ON nt.id=n.id
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_gallery_tags AS nt2 ON nt.tagid=nt2.tagid
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
			FROM ".PRE."_gallery_tags AS nt
			LEFT JOIN ".PRE."_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN ".PRE."_gallery_tags AS nt2 ON nt.tagid=nt2.tagid
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
	$tmpl->parse('functions/'.$template,'gallery');
}



//Statistik anzeigen
function gallery_stats($template='stats') {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	$parse = $tmpl->used_vars('functions/'.$template,'gallery');
	
	$apx->lang->drop('func_stats', 'gallery');
	
	if ( in_array('COUNT_GALLERIES', $parse) ) {
		list($count) = $db->first("
			SELECT count(id) FROM ".PRE."_gallery
			WHERE '".time()."' BETWEEN starttime AND endtime
		");
		$tmpl->assign('COUNT_GALLERIES', $count);
	}
	if ( in_template(array('COUNT_PICTURES', 'AVG_HITS'), $parse) ) {
		list($count, $hits) = $db->first("
			SELECT count(p.id), avg(p.hits) FROM ".PRE."_gallery_pics AS p
			LEFT JOIN ".PRE."_gallery AS g ON p.galid=g.id
			WHERE '".time()."' BETWEEN g.starttime AND g.endtime AND p.active=1
		");
		$tmpl->assign('COUNT_PICTURES', $count);
		$tmpl->assign('AVG_HITS', round($hits));
	}
	
	$tmpl->parse('functions/'.$template,'gallery');
}


?>