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

require_once(BASEDIR.getmodulepath('products').'functions.php');

$apx->module('products');
$apx->lang->drop('products');

headline($apx->lang->get('HEADLINE'),mklink('releases.php','releases.html'));
titlebar($apx->lang->get('HEADLINE'));

$_REQUEST['id']=(int)$_REQUEST['id'];
$_REQUEST['genre']=(int)$_REQUEST['genre'];
$_REQUEST['system']=(int)$_REQUEST['system'];
$types=array('normal','game','software','hardware','music','movie','book');



////////////////////////////////////////////////////////////////////////////////////////// RELEASE-LISTE

//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('releases');

$where='';
if ( !$_REQUEST['letter'] ) $_REQUEST['letter']='0';
if ( !in_array($_REQUEST['type'],$types) ) $_REQUEST['type']=0;

//Typ-Filter
if ( $_REQUEST['type'] ) {
	$where.=" AND type='".addslashes($_REQUEST['type'])."' ";
}

//System-Filter
if ( $_REQUEST['system'] ) {
	$where.=" AND systems LIKE '%|".$_REQUEST['system']."|%' ";
}

//Genere-Filter
if ( $_REQUEST['genre'] ) {
	$where.=" AND genre='".$_REQUEST['genre']."' ";
}

//Buchstaben-Liste
letters(mklink(
	'releases.php?type='.$_REQUEST['type'].'&amp;sortby='.$_REQUEST['sortby'],
	'releases,'.$_REQUEST['type'].',{LETTER},1.html'.iif($_REQUEST['sortby'],'?sortby='.$_REQUEST['sortby'])
));
if ( $_REQUEST['letter'] ) {
	if ( $_REQUEST['letter']=='spchar' ) $where.=" AND title NOT REGEXP(\"^[a-zA-Z]\") ";
	else $where.=" AND title LIKE '".addslashes($_REQUEST['letter'])."%' ";
}

//Seitenzahlen
list($count)=$db->first("SELECT count(DISTINCT b.id) FROM ".PRE."_products_releases AS a LEFT JOIN ".PRE."_products AS b ON a.prodid=b.id WHERE b.active='1' AND a.stamp>='".date('Ymd',time()-TIMEDIFF)."' ".$where);
$pagelink=mklink(
	'releases.php?type='.$_REQUEST['type'].'&amp;sortby='.$_REQUEST['sortby'].'&amp;letter='.$_REQUEST['letter'],
	'releases,'.$_REQUEST['type'].','.$_REQUEST['letter'].',{P}.html'.iif($_REQUEST['sortby'],'?sortby='.$_REQUEST['sortby'])
);
pages($pagelink,$count,$set['products']['relepp']);


//Produkte auslesen
$data = $db->fetch("SELECT DISTINCT a.stamp AS releasestamp,b.* FROM ".PRE."_products_releases AS a LEFT JOIN ".PRE."_products AS b ON a.prodid=b.id WHERE b.active='1' AND a.stamp>='".date('Ymd',time()-TIMEDIFF)."' ".$where." ORDER BY a.stamp ASC ".getlimit($set['products']['relepp']));
$ids = get_ids($data,'id');
$types = get_ids($data,'type');
if ( count($data) ) {
	
	$unitvars=array(
		'PRODUCT.DEVELOPER',
		'PRODUCT.DEVELOPER_WEBSITE',
		'PRODUCT.DEVELOPER_LINK',
		'PRODUCT.PUBLISHER',
		'PRODUCT.PUBLISHER_WEBSITE',
		'PRODUCT.PUBLISHER_LINK',
		'PRODUCT.MANUFACTURER',
		'PRODUCT.MANUFACTURER_WEBSITE',
		'PRODUCT.MANUFACTURER_LINK',
		'PRODUCT.STUDIO',
		'PRODUCT.STUDIO_WEBSITE',
		'PRODUCT.STUDIO_LINK',
		'PRODUCT.LABEL',
		'PRODUCT.LABEL_WEBSITE',
		'PRODUCT.LABEL_LINK',
		'PRODUCT.ARTIST',
		'PRODUCT.ARTIST_WEBSITE',
		'PRODUCT.ARTIST_LINK',
		'PRODUCT.AUTHOR',
		'PRODUCT.AUTHOR_WEBSITE',
		'PRODUCT.AUTHOR_LINK'
	);
	
	
	//Einheiten auslesen
	$unitinfo = array();
	if ( in_template($unitvars,$parse) ) {
		$unitids = array_merge(get_ids($data,'manufacturer'),get_ids($data,'publisher'));
		$unitinfo = $db->fetch_index("SELECT id,title,website FROM ".PRE."_products_units WHERE id IN (".implode(',',$unitids).")",'id');
	}
	
	
	//Gruppen auslesen
	$groupinfo = array();
	$groups = array();
	if ( in_template(array('PRODUCT.MEDIA','PRODUCT.MEDIA_ICON'),$parse) ) $groups = array_merge($groups,get_ids($data,'media'));
	if ( in_array('PRODUCT.GENRE',$parse) ) $groups = array_merge($groups,get_ids($data,'genre'));
	/*if ( in_array('game',$types) && in_template(array('PRODUCT.RELEASE.SYSTEM','PRODUCT.RELEASE.SYSTEM_ICON','PRODUCT.SYSTEM'),$parse) ) {
		if ( count($groups)==0 ) $groups = array(0);
		$groupinfo = $db->fetch_index("SELECT id,title,icon FROM ".PRE."_products_groups WHERE id IN (".implode(',',$groups).") OR grouptype='system'",'id');
	}
	elseif ( in_array('movie',$types) && in_template(array('PRODUCT.RELEASE.MEDIA','PRODUCT.RELEASE.MEDIA_ICON','PRODUCT.MEDIA'),$parse) ) {
		if ( count($groups)==0 ) $groups = array(0);
		$groupinfo = $db->fetch_index("SELECT id,title,icon FROM ".PRE."_products_groups WHERE id IN (".implode(',',$groups).") OR grouptype='media'",'id');
	}
	if ( count($groups) ) {
		$groupinfo = $db->fetch_index("SELECT id,title,icon FROM ".PRE."_products_groups WHERE id IN (".implode(',',$groups).")",'id');
	}*/
	$groupinfo = $db->fetch_index("SELECT id,title,icon FROM ".PRE."_products_groups",'id');
	
	
	//Veröffentlichungs-Daten auslesen
	$releaseinfo = array();
	if ( in_array('PRODUCT.RELEASE',$parse) ) {
		$releasedata = $db->fetch("SELECT prodid,system,data,stamp FROM ".PRE."_products_releases WHERE prodid IN (".implode(',',$ids).") ORDER BY stamp ASC");
		if ( count($releasedata) ) {
			foreach ( $releasedata AS $relres ) {
				$info = unserialize($relres['data']);
				$releasedate = products_format_release($info);
				$relentry=array(
					'stamp' => $relres['stamp'],
					'DATE' => $releasedate,
					'MEDIA' => $groupinfo[$relres['system']]['title'],
					'MEDIA_ICON' => $groupinfo[$relres['system']]['icon'],
					'SYSTEM' => $groupinfo[$relres['system']]['title'],
					'SYSTEM_ICON' => $groupinfo[$relres['system']]['icon']
				);
				$releaseinfo[$relres['prodid']][]=$relentry;
			}
		}
	}
	
	
	//Produkte auflisten
	foreach ( $data AS $res ) {
		++$i;
		
		//Link
		$link=mklink(
			'products.php?id='.$res['id'],
			'products,id'.$res['id'].urlformat($res['title']).'.html'
		);
		
		//Produktbild
		if ( in_array('PRODUCT.PICTURE',$parse) || in_array('PRODUCT.PICTURE_POPUP',$parse) || in_array('PRODUCT.PICTURE_POPUPPATH',$parse) ) {
			list($picture,$picture_popup,$picture_popuppath)=products_pic($res['picture']);
		}
		
		//Teaserbild
		if ( in_array('PRODUCT.TEASERPIC',$parse) || in_array('PRODUCT.TEASERPIC_POPUP',$parse) || in_array('PRODUCT.TEASERPIC_POPUPPATH',$parse) ) {
			list($teaserpic,$teaserpic_popup,$teaserpic_popuppath)=products_pic($res['teaserpic']);
		}
		
		//Text
		$text = '';
		if ( in_array('PRODUCT.TEXT',$parse) ) {
			$text = mediamanager_inline($res['text']);
			if ( $apx->is_module('glossar') ) $text = glossar_highlight($text);
		}
		
		//Tags
		if ( in_array('PRODUCT.TAG',$parse) || in_array('PRODUCT.TAG_IDS',$parse) || in_array('PRODUCT.KEYWORDS',$parse) ) {
			list($tagdata, $tagids, $keywords) = products_tags($res['id']);
		}
		
		//Standard-Platzhalter
		$tabledata[$i]['ID']=$res['id'];
		$tabledata[$i]['TYPE']=$res['type'];
		$tabledata[$i]['LINK']=$link;
		$tabledata[$i]['TITLE']=$res['title'];
		$tabledata[$i]['TEXT']=$text;
		$tabledata[$i]['TIME']=$res['addtime'];
		$tabledata[$i]['WEBSITE']=$res['website'];
		$tabledata[$i]['BUYLINK']=$res['buylink'];
		$tabledata[$i]['PRICE']=$res['price'];
		$tabledata[$i]['HITS']=$res['hits'];
		$tabledata[$i]['PICTURE']=$picture;
		$tabledata[$i]['PICTURE_POPUP']=$picture_popup;
		$tabledata[$i]['PICTURE_POPUPPATH']=$picture_popuppath;
		$tabledata[$i]['TEASERPIC']=$teaserpic;
		$tabledata[$i]['TEASERPIC_POPUP']=$teaserpic_popup;
		$tabledata[$i]['TEASERPIC_POPUPPATH']=$teaserpic_popuppath;
		$tabledata[$i]['PRODUCT_ID']=$res['prodid'];
		$tabledata[$i]['RECOMMENDED_PRICE']=$res['recprice'];
		$tabledata[$i]['GUARANTEE']=$res['guarantee'];
		
		//Tags
		$tabledata[$i]['TAG']=$tagdata;
		$tabledata[$i]['TAG_IDS']=$tagids;
		$tabledata[$i]['KEYWORDS']=$keywords;
		
		//NORMAL
		if ( $res['type']=='normal' ) {
			
			$manulink = mklink(
				'manufacturers.php?id='.$res['manufacturer'],
				'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
			);
			
			$tabledata[$i]['MANUFACTURER'] = $unitinfo[$res['manufacturer']]['title'];
			$tabledata[$i]['MANUFACTURER_WEBSITE'] = $unitinfo[$res['manufacturer']]['website'];
			$tabledata[$i]['MANUFACTURER_LINK'] = $manulink;
		}
		
		//VIDEOSPIEL
		elseif ( $res['type']=='game' ) {
			
			//System-Liste
			$systemdata = array();
			if ( in_array('PRODUCT.SYSTEM',$parse) ) {
				$systems = dash_unserialize($res['systems']);
				if ( !is_array($systems) ) $systems = array();
				foreach ( $systems AS $sysid ) {
					++$ii;
					$systemdata[$ii]['TITLE'] = $groupinfo[$sysid]['title'];
					$systemdata[$ii]['ICON'] = $groupinfo[$sysid]['icon'];
				}
			}
			
			//Media-Liste
			$media = dash_unserialize($res['media']);
			if ( !is_array($media) ) $media = array();
			$mediadata = array();
			foreach ( $media AS $medid ) {
				++$ii;
				$mediadata[$ii]['TITLE'] = $groupinfo[$medid]['title'];
				$mediadata[$ii]['ICON'] = $groupinfo[$medid]['icon'];
			}
			
			$manulink = mklink(
				'manufacturers.php?id='.$res['manufacturer'],
				'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
			);
			
			$publink = mklink(
				'manufacturers.php?id='.$res['publisher'],
				'manufacturers,id'.$res['publisher'].urlformat($unitinfo[$res['publisher']]['title']).'.html'
			);
			
			$tabledata[$i]['DEVELOPER'] = $unitinfo[$res['manufacturer']]['title'];
			$tabledata[$i]['DEVELOPER_WEBSITE'] = $unitinfo[$res['manufacturer']]['website'];
			$tabledata[$i]['DEVELOPER_LINK'] = $manulink;
			$tabledata[$i]['PUBLISHER'] = $unitinfo[$res['publisher']]['title'];
			$tabledata[$i]['PUBLISHER_WEBSITE'] = $unitinfo[$res['publisher']]['website'];
			$tabledata[$i]['PUBLISHER_LINK'] = $publink;
			$tabledata[$i]['USK'] = $res['sk'];
			$tabledata[$i]['GENRE'] = $groupinfo[$res['genre']]['title'];
			$tabledata[$i]['MEDIA'] = $mediadata;
			$tabledata[$i]['SYSTEM'] = $systemdata;
			$tabledata[$i]['REQUIREMENTS'] = $res['requirements'];
		}
		
		//HARDWARE
		elseif ( $res['type']=='hardware' ) {
			
			$manulink = mklink(
				'manufacturers.php?id='.$res['manufacturer'],
				'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
			);
			
			$tabledata[$i]['MANUFACTURER'] = $unitinfo[$res['manufacturer']]['title'];
			$tabledata[$i]['MANUFACTURER_WEBSITE'] = $unitinfo[$res['manufacturer']]['website'];
			$tabledata[$i]['MANUFACTURER_LINK'] = $manulink;
			$tabledata[$i]['EQUIPMENT'] = $res['equipment'];
		}
		
		//SOFTWARE
		elseif ( $res['type']=='software' ) {
			
			//Media-Liste
			$media = dash_unserialize($res['media']);
			if ( !is_array($media) ) $media = array();
			$mediadata = array();
			foreach ( $media AS $medid ) {
				++$ii;
				$mediadata[$ii]['TITLE'] = $groupinfo[$medid]['title'];
				$mediadata[$ii]['ICON'] = $groupinfo[$medid]['icon'];
			}
			
			$manulink = mklink(
				'manufacturers.php?id='.$res['manufacturer'],
				'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
			);
			
			$tabledata[$i]['MANUFACTURER'] = $unitinfo[$res['manufacturer']]['title'];
			$tabledata[$i]['MANUFACTURER_WEBSITE'] = $unitinfo[$res['manufacturer']]['website'];
			$tabledata[$i]['MANUFACTURER_LINK'] = $manulink;
			$tabledata[$i]['OS'] = $res['os'];
			$tabledata[$i]['LANGUAGES'] = $res['languages'];
			$tabledata[$i]['REQUIREMENTS'] = $res['requirements'];
			$tabledata[$i]['LICENSE'] = $res['license'];
			$tabledata[$i]['VERSION'] = $res['version'];
			$tabledata[$i]['MEDIA'] = $mediadata;
		}
		
		//MUSIK
		elseif ( $res['type']=='music' ) {
			
			//Media-Liste
			$media = dash_unserialize($res['media']);
			if ( !is_array($media) ) $media = array();
			$mediadata = array();
			foreach ( $media AS $medid ) {
				++$ii;
				$mediadata[$ii]['TITLE'] = $groupinfo[$medid]['title'];
				$mediadata[$ii]['ICON'] = $groupinfo[$medid]['icon'];
			}
			
			$manulink = mklink(
				'manufacturers.php?id='.$res['manufacturer'],
				'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
			);
			
			$publink = mklink(
				'manufacturers.php?id='.$res['publisher'],
				'manufacturers,id'.$res['publisher'].urlformat($unitinfo[$res['publisher']]['title']).'.html'
			);
			
			$tabledata[$i]['ARTIST'] = $unitinfo[$res['manufacturer']]['title'];
			$tabledata[$i]['ARTIST_WEBSITE'] = $unitinfo[$res['manufacturer']]['website'];
			$tabledata[$i]['ARTIST_LINK'] = $manulink;
			$tabledata[$i]['LABEL'] = $unitinfo[$res['publisher']]['title'];
			$tabledata[$i]['LABEL_WEBSITE'] = $unitinfo[$res['publisher']]['website'];
			$tabledata[$i]['LABEL_LINK'] = $publink;
			$tabledata[$i]['FSK'] = $res['sk'];
			$tabledata[$i]['GENRE'] = $groupinfo[$res['genre']]['title'];
			$tabledata[$i]['MEDIA'] = $mediadata;
		}
		
		//FILM
		elseif ( $res['type']=='movie' ) {
			
			//Media-Liste
			$media = dash_unserialize($res['media']);
			if ( !is_array($media) ) $media = array();
			$mediadata = array();
			foreach ( $media AS $medid ) {
				++$ii;
				$mediadata[$ii]['TITLE'] = $groupinfo[$medid]['title'];
				$mediadata[$ii]['ICON'] = $groupinfo[$medid]['icon'];
			}
			
			$publink = mklink(
				'manufacturers.php?id='.$res['publisher'],
				'manufacturers,id'.$res['publisher'].urlformat($unitinfo[$res['publisher']]['title']).'.html'
			);
			
			$tabledata[$i]['STUDIO'] = $unitinfo[$res['publisher']]['title'];
			$tabledata[$i]['STUDIO_WEBSITE'] = $unitinfo[$res['publisher']]['website'];
			$tabledata[$i]['STUDIO_LINK'] = $publink;
			$tabledata[$i]['REGISSEUR'] = $res['regisseur'];
			$tabledata[$i]['ACTORS'] = $res['actors'];
			$tabledata[$i]['LENGTH'] = $res['length'];
			$tabledata[$i]['FSK'] = $res['sk'];
			$tabledata[$i]['GENRE'] = $groupinfo[$res['genre']]['title'];
			$tabledata[$i]['MEDIA'] = $mediadata;
		}
		
		//LITERATUR
		elseif ( $res['type']=='book' ) {
			
			//Media-Liste
			$media = dash_unserialize($res['media']);
			if ( !is_array($media) ) $media = array();
			$mediadata = array();
			foreach ( $media AS $medid ) {
				++$ii;
				$mediadata[$ii]['TITLE'] = $groupinfo[$medid]['title'];
				$mediadata[$ii]['ICON'] = $groupinfo[$medid]['icon'];
			}
			
			$manulink = mklink(
				'manufacturers.php?id='.$res['manufacturer'],
				'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
			);
			
			$publink = mklink(
				'manufacturers.php?id='.$res['publisher'],
				'manufacturers,id'.$res['publisher'].urlformat($unitinfo[$res['publisher']]['title']).'.html'
			);
			
			$tabledata[$i]['AUTHOR'] = $unitinfo[$res['manufacturer']]['title'];
			$tabledata[$i]['AUTHOR_WEBSITE'] = $unitinfo[$res['manufacturer']]['website'];
			$tabledata[$i]['AUTHOR_LINK'] = $manulink;
			$tabledata[$i]['PUBLISHER'] = $unitinfo[$res['publisher']]['title'];
			$tabledata[$i]['PUBLISHER_WEBSITE'] = $unitinfo[$res['publisher']]['website'];
			$tabledata[$i]['PUBLISHER_LINK'] = $publink;
			$tabledata[$i]['GENRE'] = $groupinfo[$res['genre']]['title'];
			$tabledata[$i]['MEDIA'] = $mediadata;
			$tabledata[$i]['ISBN'] = $res['isbn'];
		}
		
		//Benutzerdefinierte Felder
		for ( $ii=1; $ii<=10; $ii++ ) {
			$tabledata[$i]['CUSTOM'.$ii.'_NAME'] = replace($set['products']['custom_'.$res['type']][($ii-1)]);
			$tabledata[$i]['CUSTOM'.$ii] = $res['custom'.$ii];
		}
		
		//Veröffentlichung
		if ( in_array('PRODUCT.RELEASE',$parse) ) {
			$temprel = $releaseinfo[$res['id']];
			foreach ( $temprel AS $rel ) {
				if ( $rel['stamp']!=$res['releasestamp'] ) continue;
				++$ii;
				$tabledata[$i]['RELEASE'][$ii] = $rel;
			}
		}
		
		//Kommentare
		if ( $apx->is_module('comments') && $set['products']['coms'] && $res['allowcoms'] ) {
			require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
			if ( !isset($coms) ) $coms=new comments('products',$res['id']);
			else $coms->mid=$res['id'];
			
			$link=mklink(
				'products.php?id='.$res['id'],
				'products,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			$tabledata[$i]['COMMENT_COUNT']=$coms->count();
			$tabledata[$i]['COMMENT_LINK']=$coms->link($link);
			$tabledata[$i]['DISPLAY_COMMENTS']=1;
			if ( in_template(array('PRODUCT.COMMENT_LAST_USERID','PRODUCT.COMMENT_LAST_NAME','PRODUCT.COMMENT_LAST_TIME'),$parse) ) {
				$tabledata[$i]['COMMENT_LAST_USERID']=$coms->last_userid();
				$tabledata[$i]['COMMENT_LAST_NAME']=$coms->last_name();
				$tabledata[$i]['COMMENT_LAST_TIME']=$coms->last_time();
			}
		}
		
		//Bewertungen
		if ( $apx->is_module('ratings') && $set['products']['ratings'] && $res['allowrating'] ) {
			require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
			if ( !isset($rate) ) $rate=new ratings('products',$res['id']);
			else $rate->mid=$res['id'];
			
			$tabledata[$i]['RATING']=$rate->display();
			$tabledata[$i]['RATING_VOTES']=$rate->count();
			$tabledata[$i]['DISPLAY_RATING']=1;
		}
		
	}
}

//Nach Produkttyp filtern
$apx->tmpl->assign('LINK_SHOWALL',mklink('releases.php','releases,0,0,1.html'));
$apx->tmpl->assign('LINK_SHOWNORMAL',mklink('releases.php?type=normal','releases,normal,0,1.html'));
$apx->tmpl->assign('LINK_SHOWGAME',mklink('releases.php?type=game','releases,game,0,1.html'));
$apx->tmpl->assign('LINK_SHOWSOFTWARE',mklink('releases.php?type=software','releases,software,0,1.html'));
$apx->tmpl->assign('LINK_SHOWHARDWARE',mklink('releases.php?type=hardware','releases,hardware,0,1.html'));
$apx->tmpl->assign('LINK_SHOWMUSIC',mklink('releases.php?type=music','releases,music,0,1.html'));
$apx->tmpl->assign('LINK_SHOWMOVIE',mklink('releases.php?type=movie','releases,movie,0,1.html'));
$apx->tmpl->assign('LINK_SHOWBOOK',mklink('releases.php?type=book','releases,book,0,1.html'));

$apx->tmpl->assign('GENRE',iif($_REQUEST['genre'],$_REQUEST['genre'],''));
$apx->tmpl->assign('SYSTEM',iif($_REQUEST['system'],$_REQUEST['system'],''));
$apx->tmpl->assign('TYPE',iif($_REQUEST['type'],$_REQUEST['type'],''));
$apx->tmpl->assign('PRODUCT',$tabledata);
$apx->tmpl->parse('releases');



////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>