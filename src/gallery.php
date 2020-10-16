<?php 

define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once(BASEDIR.getmodulepath('gallery').'functions.php');

$apx->module('gallery');
$apx->lang->drop('gallery');
headline($apx->lang->get('HEADLINE'),mklink('gallery.php','gallery.html'));
titlebar($apx->lang->get('HEADLINE'));

$_REQUEST['id']=(int)$_REQUEST['id'];
$_REQUEST['pic']=(int)$_REQUEST['pic'];


//////////////////////////////////////////////////////////////////////////////////////////////////////// KOMMENTARE ZU GALERIEN

if ( $_REQUEST['id'] && $_REQUEST['comments'] ) {
	
	//Galerie-INFO
	$gallery=$db->first("SELECT id,title FROM ".PRE."_gallery WHERE ( id='".$_REQUEST['id']."' ".section_filter()." ".iif(!$user->is_team_member()," AND ( '".time()."' BETWEEN starttime AND endtime ) ")." ) LIMIT 1");
	if ( !$gallery['id'] ) filenotfound();
	
	//Headline + Titlebar
	$gallink=mklink(
		'gallery.php?id='.$gallery['id'],
		'gallery,list'.$gallery['id'].',1'.urlformat($gallery['title']).'.html'
	);
	headline(strip_tags($gallery['title']),$gallink);
	titlebar($apx->lang->get('HEADLINE').': '.strip_tags($gallery['title']));
	
	galleryself_showcomments($_REQUEST['id']);
	require('lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// KOMMENTARE ZU BILDERN

if ( $_REQUEST['pic'] && $_REQUEST['comments'] ) {
	
	//Bild-INFO
	$pic=$db->first("SELECT id,galid FROM ".PRE."_gallery_pics WHERE ( id='".$_REQUEST['pic']."' ".iif(!$user->is_team_member()," AND active='1' ")." )  LIMIT 1");
	if ( !$pic['id'] ) filenotfound();
	
	//Galerie-INFO
	$gallery=$db->first("SELECT id,title FROM ".PRE."_gallery WHERE ( id='".$pic['galid']."' ".section_filter()." ".iif(!$user->is_team_member()," AND ( '".time()."' BETWEEN starttime AND endtime ) ")." ) LIMIT 1");
	if ( !$gallery['id'] ) filenotfound();
	
	//Headline + Titlebar
	$gallink=mklink(
		'gallery.php?id='.$gallery['id'],
		'gallery,list'.$gallery['id'].',1'.urlformat($gallery['title']).'.html'
	);
	headline(strip_tags($gallery['title']),$gallink);
	titlebar($apx->lang->get('HEADLINE').': '.strip_tags($gallery['title']));
	
	gallery_showcomments($_REQUEST['pic']);
	require('lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// EINZELNE BILDER

if ( $_REQUEST['pic'] ) {
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars('picture');
	
	//Update Hits
	if ( !$_REQUEST['comments'] ) $db->query("UPDATE ".PRE."_gallery_pics SET hits=hits+1 WHERE id='".$_REQUEST['pic']."' LIMIT 1");
	
	//Bild-INFO
	$pic=$db->first("SELECT * FROM ".PRE."_gallery_pics WHERE ( id='".$_REQUEST['pic']."' ".iif(!$user->is_team_member()," AND active='1' ")." )  LIMIT 1");
	if ( !$pic['id'] ) filenotfound();
	
	//Galerie-INFO
	$gallery=$db->first("SELECT * FROM ".PRE."_gallery WHERE ( id='".$pic['galid']."' ".section_filter()." ".iif(!$user->is_team_member()," AND ( '".time()."' BETWEEN starttime AND endtime ) ")." ) LIMIT 1");
	if ( !$gallery['id'] ) filenotfound();
	
	//Altersabfrage
	if ( $gallery['restricted'] ) {
		checkage();
	}
	
	//Design = blank bei Popup
	if ( $set['gallery']['popup'] ) {
		//Resize
		$modulepath=HTTPDIR.getmodulepath('gallery');
		if ( file_exists(BASEDIR.getpath('uploads').$pic['picture']) ) {
			$size=getimagesize(BASEDIR.getpath('uploads').$pic['picture']);
		}
		else {
			$size = array(10000,10000);
		}
		$javascript=<<<HTML
<script language="JavaScript" type="text/javascript">
<!--
resizex = $size[0]+{$set[gallery][popup_addwidth]};
resizey = $size[1]+{$set[gallery][popup_addheight]};
//-->
</script>
<script language="JavaScript" type="text/javascript" src="{$modulepath}gallery_resize.js"></script>
HTML;
		$apx->tmpl->assign('RESIZE',$javascript);
		$apx->tmpl->loaddesign('blank');
	}
	
	//Passwortschutz
	if ( $gallery['password'] ) {
		$password=$gallery['password'];
		$pwdid=$gallery['id'];
	}
	else {
		$parentIds = dash_unserialize($gallery['parents']);
		if ( $parentIds ) {
			list($pwdid,$password)=$db->first("SELECT id,password FROM ".PRE."_gallery WHERE id='".$parentIds[0]."' LIMIT 1");
		}
	}
	if ( $password && $password==$_POST['password'] ) {
		setcookie('gallery_pwd_'.$pwdid,$_POST['password'],time()+1*24*3600);
	}
	elseif ( $password && $_COOKIE['gallery_pwd_'.$pwdid]!=$password ) {
		tmessage('pwdrequired',array('ID'=>$_REQUEST['id'],'PIC'=>$_REQUEST['pic']));
	}
	
	//Headline + Titlebar
	$gallink=mklink(
		'gallery.php?id='.$gallery['id'],
		'gallery,list'.$gallery['id'].',1'.urlformat($gallery['title']).'.html'
	);
	headline(strip_tags($gallery['title']),$gallink);
	titlebar($apx->lang->get('HEADLINE').': '.strip_tags($gallery['title']));
	
	
	//Galerie-Platzhalter
	list($piccount)=$db->first("SELECT count(id) FROM ".PRE."_gallery_pics WHERE ( galid='".$gallery['id']."' AND active='1' )");
	list($updatetime)=$db->first("SELECT max(addtime) FROM ".PRE."_gallery_pics WHERE ( galid='".$gallery['id']."' AND active='1' )");
	$link=mklink(
		'gallery.php?id='.$gallery['id'],
		'gallery,list'.$gallery['id'].',1'.urlformat($res['title']).'.html'
	);
	
	//Tags
	if ( in_array('TAG',$parse) || in_array('TAG_IDS',$parse) || in_array('KEYWORDS',$parse) ) {
		list($tagdata, $tagids, $keywords) = gallery_tags($gallery['id']);
	}
	
	$apx->tmpl->assign('SECID',$gallery['secid']);
	$apx->tmpl->assign('ID',$gallery['id']);
	$apx->tmpl->assign('TITLE',$gallery['title']);
	$apx->tmpl->assign('LINK',$link);
	$apx->tmpl->assign('DESCRIPTION',$gallery['description']);
	$apx->tmpl->assign('RESTRICTED',$gallery['restricted']);
	$apx->tmpl->assign('TIME',$gallery['starttime']);
	$apx->tmpl->assign('UPDATETIME',$updatetime);
	$apx->tmpl->assign('COUNT',number_format($piccount,0,'','.'));
	
	//Tags
	$apx->tmpl->assign('TAG_IDS', $tagids);
	$apx->tmpl->assign('TAG', $tagdata);
	$apx->tmpl->assign('KEYWORDS', $keywords);
	
	//Produkt
	$apx->tmpl->assign('PRODUCT_ID',$gallery['prodid']);
	
	//Untergalerien
	if ( $set['gallery']['subgals'] ) {
		$apx->tmpl->assign('PATH',gallery_path($gallery['id']));
	}
	
	//KOMMENTARE
	if ( $_REQUEST['comments'] ) {
		gallery_showcomments($_REQUEST['pic']);
		require('lib/_end.php');
	}
	
	//Seitenzahlen
	gallery_pages($gallery['id']);
	
	//Neues Bild
	if ( ($pic['addtime']+($set['gallery']['new']*24*3600))>=time() ) $new=1;
	else $new=0;
	
	//Bild-Platzhalter
	$apx->tmpl->assign('IMAGE',getpath('uploads').$pic['picture']);
	$apx->tmpl->assign('HITS',number_format($pic['hits'],0,'','.'));
	$apx->tmpl->assign('CAPTION',$pic['caption']);
	$apx->tmpl->assign('NEW',$new);
	
	//Kommentare	
	if ( $apx->is_module('comments') && $set['gallery']['coms'] && $pic['allowcoms'] ) {
		require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
		$coms=new comments('gallery',$pic['id']);
		$coms->assign_comments($parse);
	}
	
	//Bewertung
	if ( $apx->is_module('ratings') && $set['gallery']['ratings'] && $pic['allowrating'] ) {
		require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
		$rate=new ratings('gallery',$pic['id']);
		$rate->assign_ratings($parse);
	}
	
	$apx->tmpl->parse('picture');
	require('lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// BILDER AUFLISTEN

if ( $_REQUEST['id'] ) {
	
	//Verwendete Variablen auslesen
	$parse=$apx->tmpl->used_vars('gallery');
	
	//Voreinstellungen
	if ( !$set['gallery']['picwidth'] || !$set['gallery']['picheight'] ) {
		$set['gallery']['picwidth']=9999999;
		$set['gallery']['picheight']=9999999;
	}
	
	//Galerie-INFO
	$gallery=$db->first("SELECT * FROM ".PRE."_gallery WHERE ( id='".$_REQUEST['id']."' ".section_filter()." ".iif(!$user->is_team_member()," AND ( '".time()."' BETWEEN starttime AND endtime ) ")." ) LIMIT 1");
	if ( !$gallery['id'] ) filenotfound();
	//$gallery['parents'] = dash_unserialize($gallery['parents']);
	$gallery['children'] = dash_unserialize($gallery['children']);
	
	//Altersabfrage
	if ( $gallery['restricted'] ) {
		checkage();
	}
	
	//Passwortschutz
	if ( $gallery['password'] ) {
		$password=$gallery['password'];
		$pwdid=$gallery['id'];
	}
	else {
		$parentIds = dash_unserialize($gallery['parents']);
		if ( $parentIds ) {
			list($pwdid,$password)=$db->first("SELECT id,password FROM ".PRE."_gallery WHERE id='".$parentIds[0]."' LIMIT 1");
		}
	}
	if ( $password && $password==$_POST['password'] ) {
		setcookie('gallery_pwd_'.$pwdid,$_POST['password'],time()+1*24*3600);
	}
	elseif ( $password && $_COOKIE['gallery_pwd_'.$pwdid]!=$password ) {
		tmessage('pwdrequired',array('ID'=>$_REQUEST['id'],'PIC'=>$_REQUEST['pic']));
	}
	
	//Headline + Titlebar
	headline(strip_tags($gallery['title']),mklink(
		'gallery.php?id='.$_REQUEST['id'].'&amp;p='.$_REQUEST['p'],
		'gallery,list'.$_REQUEST['id'].','.iif($_REQUEST['p'],$_REQUEST['p'],1).urlformat($gallery['title']).'.html'
	));
	titlebar($apx->lang->get('HEADLINE').': '.strip_tags($gallery['title']));
	
	//Unter-Galerien auslesen, die veröffentlicht sind
	if ( $set['gallery']['subgals'] && $gallery['children'] ) {
		$openData = $db->fetch("SELECT id FROM ".PRE."_gallery WHERE id IN (".implode(', ', $gallery['children']).") AND '".time()."' BETWEEN starttime AND endtime");
		$openIds = array_merge(get_ids($openData), array($gallery['id']));
	}
	else {
		$openIds = array($gallery['id']);
	}
	
	//////////////////// UNTERGALERIEN
	if ( $set['gallery']['subgals'] ) {
		require_once(BASEDIR.'lib/class.recursivetree.php');
		$tree = new RecursiveTree(PRE.'_gallery', 'id');
		$data = $tree->getLevel(array('*'), $_REQUEST['id'], "'".time()."' BETWEEN starttime AND endtime");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				++$i;
				$subtreeIds = array_merge($res['children'], array($res['id']));
				$subtreeIds = array_intersect($subtreeIds, $openIds);
				
				//Link
				$link=mklink(
					'gallery.php?id='.$res['id'],
					'gallery,list'.$res['id'].',1'.urlformat($res['title']).'.html'
				);
				
				//Enthaltene Bilder, Letzte Aktualisierung
				if ( in_template(array('GALLERY.COUNT', 'GALLERY.UPDATETIME'),$parse) ) {
					list($count, $updatetime)=$db->first("SELECT count(id), max(addtime) FROM ".PRE."_gallery_pics WHERE ( galid IN (".implode(', ', $subtreeIds).") AND active='1' )");
				}
				
				//Vorschau-Bild
				if ( !$res['password'] && in_template(array('GALLERY.PREVIEW', 'GALLERY.PREVIEW_FULLSIZE'),$parse) ) {
					if ( $res['preview'] && file_exists(BASEDIR.getpath('uploads').$res['preview']) ) {
						$preview=getpath('uploads').$res['preview'];
					}
					else {
						list($image)=$db->first("SELECT thumbnail FROM ".PRE."_gallery_pics WHERE ( galid IN (".implode(', ', $subtreeIds).") AND active='1' ) ORDER BY addtime DESC,id DESC LIMIT 1");
						$preview=getpath('uploads').$image;
					}
					$fullsize_preview = str_replace('-thumb', '', $preview);
					if ( !file_exists(BASEDIR.$fullsize_preview) ) {
						$fullsize_preview = '';
					}
				}
				
				//Tags
				if ( in_array('GALLERY.TAG',$parse) || in_array('GALLERY.TAG_IDS',$parse) || in_array('GALLERY.KEYWORDS',$parse) ) {
					list($tagdata, $tagids, $keywords) = gallery_tags($res['id']);
				}
				
				$galdata[$i]['SECID']=$res['secid'];
				$galdata[$i]['ID']=$res['id'];
				$galdata[$i]['TITLE']=$res['title'];
				$galdata[$i]['DESCRIPTION']=$res['description'];
				$galdata[$i]['RESTRICTED']=$res['restricted'];
				$galdata[$i]['TIME']=$res['starttime'];
				$galdata[$i]['UPDATETIME']=$updatetime;
				$galdata[$i]['LINK']=$link;
				$galdata[$i]['COUNT']=$count;
				$galdata[$i]['PREVIEW']=iif($preview, HTTPDIR.$preview);
				$galdata[$i]['PREVIEW_FULLSIZE']=iif($fullsize_preview, HTTPDIR.$fullsize_preview);
				$galdata[$i]['PRODUCT_ID']=$res['prodid'];
				
				//Tags
				$galdata[$i]['TAG']=$tagdata;
				$galdata[$i]['TAG_IDS']=$tagids;
				$galdata[$i]['KEYWORDS']=$keywords;
			}
		}
		
		$apx->tmpl->assign('GALLERY',$galdata);
		
		//Galerie-Pfad
		if ( in_array('PATH',$parse) ) {
			$apx->tmpl->assign('PATH',gallery_path($gallery['id']));
		}
	}
	
	/////////////////////// GALERIE
	
	//Enthaltene Bilder, Aktualisierte Bilder
	if ( in_template(array('COUNT', 'UPDATETIME'),$parse) ) {
		list($piccount, $updatetime)=$db->first("SELECT count(id), max(addtime) FROM ".PRE."_gallery_pics WHERE ( galid IN (".implode(',',$openIds).") AND active='1' )");
	}
	
	//Tags
	if ( in_array('TAG',$parse) || in_array('TAG_IDS',$parse) || in_array('KEYWORDS',$parse) ) {
		list($tagdata, $tagids, $keywords) = gallery_tags($gallery['id']);
	}
	
	$link=mklink(
		'gallery.php?id='.$gallery['id'],
		'gallery,list'.$gallery['id'].',1'.urlformat($gallery['title']).'.html'
	);
	
	$apx->tmpl->assign('ID',$gallery['id']);
	$apx->tmpl->assign('TITLE',$gallery['title']);
	$apx->tmpl->assign('LINK',$link);
	$apx->tmpl->assign('DESCRIPTION',$gallery['description']);
	$apx->tmpl->assign_static('META_DESCRIPTION',replace($gallery['meta_description']));
	$apx->tmpl->assign('RESTRICTED',$gallery['restricted']);
	$apx->tmpl->assign('TIME',$gallery['starttime']);
	$apx->tmpl->assign('UPDATETIME',$updatetime);
	$apx->tmpl->assign('COUNT',number_format($piccount,0,'','.'));
	$apx->tmpl->assign('PRODUCT_ID',$gallery['prodid']);
	
	//Tags
	$apx->tmpl->assign('TAG_IDS', $tagids);
	$apx->tmpl->assign('TAG', $tagdata);
	$apx->tmpl->assign('KEYWORDS', $keywords);
	
	//Kommentare
	if ( $apx->is_module('comments') && $set['gallery']['galcoms'] && $gallery['allowcoms'] ) {
		require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
		$coms=new comments('galleryself',$gallery['id']);
		$coms->assign_comments($parse);
	}
	
	//Seitenzahlen	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_gallery_pics WHERE ( galid='".$_REQUEST['id']."' ".iif(!$user->is_team_member()," AND active='1' ")." )");
	pages(
		mklink(
			'gallery.php?id='.$_REQUEST['id'],
			'gallery,list'.$_REQUEST['id'].',{P}'.urlformat($gallery['title']).'.html'
		),
		$count,
		$set['gallery']['galepp']
	);
	
	//Order by
	if ( $set['gallery']['orderpics']==2 ) $orderby='id ASC';
	else $orderby='id DESC';
	
	
	//BILDER
	unset($coms);
	$data=$db->fetch("SELECT id,thumbnail,picture,caption,hits,addtime,allowcoms,allowrating FROM ".PRE."_gallery_pics WHERE ( galid='".$_REQUEST['id']."' ".iif(!$user->is_team_member()," AND active='1' ")." ) ORDER BY ".$orderby." ".iif($set['gallery']['galepp'],getlimit($set['gallery']['galepp'])));
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Neue Bilder
			if ( ($res['addtime']+($set['gallery']['new']*24*3600))>=time() ) $new=1;
			else $new=0;
			
			//Link
			$link=mklink(
				'gallery.php?pic='.$res['id'],
				'gallery,pic'.$res['id'].urlformat($res['caption']).'.html'
			);
			if ( $set['gallery']['popup'] ) {
				$link="javascript:popupwin('".$link."','".$set['gallery']['picwidth']."','".$set['gallery']['picheight']."',".iif($set['gallery']['popup_resizeable'],1,0).")";
			}
			
			$tabledata[$i]['CAPTION']=$res['caption'];
			$tabledata[$i]['IMAGE']=getpath('uploads').$res['thumbnail'];
			$tabledata[$i]['FULLSIZE']=getpath('uploads').$res['picture'];
			$tabledata[$i]['HITS']=$res['hits'];
			$tabledata[$i]['NEW']=$new;
			$tabledata[$i]['LINK']=$link;
			$tabledata[$i]['TIME']=$res['addtime'];
			
			//Kommentare
			if ( $apx->is_module('comments') && $set['gallery']['coms'] && $res['allowcoms'] ) {
				require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
				if ( !isset($coms) ) $coms=new comments('gallery',$res['id']);
				else $coms->mid=$res['id'];
				
				//Kommentar-Link = Link, wenn Bild im Popup-Fenster und Kommentare nicht im Popup
				if ( $set['gallery']['popup'] && !$coms->set['popup'] ) {
					$tabledata[$i]['COMMENT_LINK']=$link;
				}
				else {
					$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
				}
				
				$tabledata[$i]['COMMENT_COUNT']=$coms->count();
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
	
	$apx->tmpl->assign('PICTURE',$tabledata);
	$apx->tmpl->parse('gallery');
	require('lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// GALERIEN AUFLISTEN

//Verwendete Variablen auslesen
$parse=$apx->tmpl->used_vars('index');

//Letters
letters(mklink(
	'gallery.php',
	'gallery,{LETTER},1.html'
));
if ( $_REQUEST['letter'] ) {
	if ( $_REQUEST['letter']=='spchar' ) $where="AND title NOT REGEXP(\"^[a-zA-Z]\")";
	else $where="AND title LIKE '".addslashes($_REQUEST['letter'])."%'";
}

if ( !$_REQUEST['letter'] ) $_REQUEST['letter']=0;

//Seitenzahlen
if ( $set['gallery']['subgals'] ) list($count)=$db->first("SELECT count(id) FROM ".PRE."_gallery WHERE ( '".time()."' BETWEEN starttime AND endtime AND parents='|' ".section_filter()." )");
else list($count)=$db->first("SELECT count(id) FROM ".PRE."_gallery WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".$where." ".section_filter()." )");
pages(
	mklink(
		'gallery.php'.iif($_REQUEST['letter'],'?letter='.$_REQUEST['letter']),
		'gallery,'.$_REQUEST['letter'].',{P}.html'),
	$count,
	$set['gallery']['listepp']
);


//Galerie sortieren nach
if ( $set['gallery']['subgals'] ) {
	require_once(BASEDIR.'lib/class.recursivetree.php');
	$tree = new RecursiveTree(PRE.'_gallery', 'id');
	$data = $tree->getLevel(array('*'), 0, "'".time()."' BETWEEN starttime AND endtime ".section_filter(true,'secid'));
}
else {
	if ( $_REQUEST['letter'] ) $orderby='title ASC';
	elseif ( $set['gallery']['ordergal']==2 ) $orderby='title ASC';
	elseif ( $set['gallery']['ordergal']==3 ) $orderby='lft ASC';
	else $orderby='starttime DESC';
	$data=$db->fetch("SELECT *,1 AS level FROM ".PRE."_gallery WHERE ( '".time()."' BETWEEN starttime AND endtime ".$where." ".section_filter()." ) ORDER BY ".$orderby." ".iif($set['gallery']['listepp'],getlimit($set['gallery']['listepp'])));
}

//Galerien auslesen, die veröffentlicht sind
if ( $set['gallery']['subgals'] ) {
	$openData = $db->fetch("SELECT id FROM ".PRE."_gallery WHERE '".time()."' BETWEEN starttime AND endtime");
	$openIds = get_ids($openData);
}

//Galerien auflisten
if ( count($data) ) {
	foreach ( $data AS $res ) {
		++$i;
		if ( $set['gallery']['subgals'] ) {
			$subtreeIds = array_merge($res['children'], array($res['id']));
			$subtreeIds = array_intersect($subtreeIds, $openIds);
		}
		else {
			$subtreeIds = array($res['id']);
		}
		
		//Nur immerhalb der gewählten Seitenzahl
		if ( $set['gallery']['subgals'] && $set['gallery']['listepp'] ) {
			if ( !( $i>($_REQUEST['p']-1)*$set['gallery']['listepp'] && $i<=$_REQUEST['p']*$set['gallery']['listepp'] ) ) continue;
		}
		
		if ( $laststamp!=date('Y/m/d',$res['starttime']-TIMEDIFF) ) {
			$tabledata[$i]['DATEHEAD']=$res['starttime'];
		}
		
		//Link
		$link=mklink(
			'gallery.php?id='.$res['id'],
			'gallery,list'.$res['id'].',1'.urlformat($res['title']).'.html'
		);
		
		//Enthaltene Bilder, Letzte Aktualisierung
		if ( in_template(array('GALLERY.COUNT', 'GALLERY.UPDATETIME'), $parse) ) {
			list($count, $updatetime)=$db->first("SELECT count(id), max(addtime) FROM ".PRE."_gallery_pics WHERE ( galid IN (".implode(', ', $subtreeIds).") AND active='1' )");
		}
		
		//Vorschau-Bild
		if ( !$res['password'] && in_template(array('GALLERY.PREVIEW', 'GALLERY.PREVIEW_FULLSIZE'),$parse) ) {
			if ( $res['preview'] && file_exists(BASEDIR.getpath('uploads').$res['preview']) ) {
				$preview=getpath('uploads').$res['preview'];
			}
			else {
				list($image)=$db->first("SELECT thumbnail FROM ".PRE."_gallery_pics WHERE ( galid IN (".implode(', ', $subtreeIds).") AND active='1' ) ORDER BY addtime DESC,id DESC LIMIT 1");
				$preview=getpath('uploads').$image;
			}
			$fullsize_preview = str_replace('-thumb', '', $preview);
			if ( !file_exists(BASEDIR.$fullsize_preview) ) {
				$fullsize_preview = '';
			}
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
		$tabledata[$i]['UPDATETIME']=$updatetime;
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
				'gallery,list'.$res['id'].',1'.urlformat($res['title']).'.html'
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
	
$apx->tmpl->assign('GALLERY',$tabledata);
$apx->tmpl->parse('index');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>