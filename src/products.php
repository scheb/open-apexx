<?php 

define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once(BASEDIR.getmodulepath('products').'functions.php');

$apx->module('products');
$apx->lang->drop('products');

headline($apx->lang->get('HEADLINE'),mklink('products.php','products.html'));
titlebar($apx->lang->get('HEADLINE'));

$_REQUEST['id']=(int)$_REQUEST['id'];
$_REQUEST['genre']=(int)$_REQUEST['genre'];
$_REQUEST['system']=(int)$_REQUEST['system'];
$types=array('normal','game','software','hardware','music','movie','book');


////////////////////////////////////////////////////////////////////////////////////////// SAMMLUNG

//Hinzufügen
if ( $_REQUEST['id'] && $user->info['userid'] && $_REQUEST['addcoll'] ) {
	$apx->lang->drop('collection');
	$db->query("INSERT IGNORE INTO ".PRE."_products_coll VALUES (".$user->info['userid'].", ".$_REQUEST['id'].")");
	$goto = mklink(
		'user.php?action=collection&amp;id='.$user->info['userid'],
		'user,collection,'.$user->info['userid'].',0,1.html'
	);
	message($apx->lang->get('MSG_COLL_ADD'), $goto);
}


//Entfernen
elseif ( $_REQUEST['id'] && $user->info['userid'] && $_REQUEST['removecoll'] ) {
	$apx->lang->drop('collection');
	$db->query("DELETE FROM ".PRE."_products_coll WHERE userid=".$user->info['userid']." AND prodid=".$_REQUEST['id']." LIMIT 1");
	$goto = mklink(
		'user.php?action=collection&amp;id='.$user->info['userid'],
		'user,collection,'.$user->info['userid'].',0,1.html'
	);
	message($apx->lang->get('MSG_COLL_REMOVE'), $goto);
}



////////////////////////////////////////////////////////////////////////////////////////// PRODUKT-DETAIL

elseif ( $_REQUEST['id'] ) {
	
	//Produkt auslesen
	$res=$db->first("SELECT * FROM ".PRE."_products WHERE id='".$_REQUEST['id']."' ".iif(!$user->is_team_member()," AND active='1' ")." LIMIT 1");
	if ( !$res['id'] ) filenotfound();
	
	//Altersabfrage
	if ( $res['restricted'] ) {
		checkage();
	}
	
	//Klicks zählen
	$db->query("UPDATE ".PRE."_products SET hits=hits+1 WHERE id='".$_REQUEST['id']."' ".iif(!$user->is_team_member()," AND active='1' ")." LIMIT 1");
	
	titlebar($apx->lang->get('HEADLINE').': '.$res['title']);
	$systems = array();
	
	//Verwendete Variablen
	$parse = $apx->tmpl->used_vars('detail');
	
	//Link
	$link=mklink(
		'products.php?id='.$res['id'],
		'products,id'.$res['id'].urlformat($res['title']).'.html'
	);
	
	//Produktbild
	if ( in_array('PICTURE',$parse) || in_array('PICTURE_POPUP',$parse) || in_array('PICTURE_POPUPPATH',$parse) ) {
		list($picture,$picture_popup,$picture_popuppath)=products_pic($res['picture']);
	}
	
	//Teaserbild
	if ( in_array('TEASERPIC',$parse) || in_array('TEASERPIC_POPUP',$parse) || in_array('TEASERPIC_POPUPPATH',$parse) ) {
		list($teaserpic,$teaserpic_popup,$teaserpic_popuppath)=products_pic($res['teaserpic']);
	}
	
	//Tags
	if ( in_array('TAG',$parse) || in_array('TAG_IDS',$parse) || in_array('KEYWORDS',$parse) ) {
		list($tagdata, $tagids, $keywords) = products_tags($res['id']);
	}
	
	//Text
	if ( in_array('TEXT',$parse) ) {
		$text = mediamanager_inline($res['text']);
		if ( $apx->is_module('glossar') ) $text = glossar_highlight($text);
	}
	
	//Standard-Platzhalter
	$apx->tmpl->assign('ID',$res['id']);
	$apx->tmpl->assign('TYPE',$res['type']);
	$apx->tmpl->assign('LINK',$link);
	$apx->tmpl->assign('TITLE',$res['title']);
	$apx->tmpl->assign('TEXT',$text);
	$apx->tmpl->assign_static('META_DESCRIPTION',replace($res['meta_description']));
	$apx->tmpl->assign('TIME',$res['addtime']);
	$apx->tmpl->assign('WEBSITE',$res['website']);
	$apx->tmpl->assign('BUYLINK',$res['buylink']);
	$apx->tmpl->assign('PRICE',$res['price']);
	$apx->tmpl->assign('HITS',$res['hits']+1);
	$apx->tmpl->assign('PICTURE',$picture);
	$apx->tmpl->assign('PICTURE_POPUP',$picture_popup);
	$apx->tmpl->assign('PICTURE_POPUPPATH',$picture_popuppath);
	$apx->tmpl->assign('TEASERPIC',$teaserpic);
	$apx->tmpl->assign('TEASERPIC_POPUP',$teaserpic_popup);
	$apx->tmpl->assign('TEASERPIC_POPUPPATH',$teaserpic_popuppath);
	$apx->tmpl->assign('PRODUCT_ID',$res['prodid']);
	$apx->tmpl->assign('RECOMMENDED_PRICE',$res['recprice']);
	$apx->tmpl->assign('GUARANTEE',$res['guarantee']);
	
	//Sammlung
	if ( $user->info['userid'] ) {
		if ( !products_in_coll($res['id']) ) {
			$apx->tmpl->assign('LINK_COLLECTION_ADD', mklink(
				'products.php?id='.$res['id'].'&amp;addcoll=1',
				'products,id'.$res['id'].urlformat($res['title']).'.html?addcoll=1'
			));
		}
		else {
			$apx->tmpl->assign('LINK_COLLECTION_REMOVE', mklink(
				'products.php?id='.$res['id'].'&amp;removecoll=1',
				'products,id'.$res['id'].urlformat($res['title']).'.html?removecoll=1'
			));
		}
	}
	
	//Tags
	$apx->tmpl->assign('TAG_IDS', $tagids);
	$apx->tmpl->assign('TAG', $tagdata);
	$apx->tmpl->assign('KEYWORDS', $keywords);
	
	//Units auslesen
	$units = array($res['manufacturer'],$res['publisher']);
	$unitinfo = $db->fetch_index("SELECT id,title,website FROM ".PRE."_products_units WHERE id IN (".implode(',',$units).")",'id');
	
	//Gruppen auslesen
	$groups = array_merge(array($res['genre']),dash_unserialize($res['media']));
	$groupinfo = array();
	if ( $res['type']=='game' ) $groupinfo = $db->fetch_index("SELECT id,title,icon FROM ".PRE."_products_groups WHERE id IN (".implode(',',$groups).") OR grouptype='system'",'id');
	elseif ( $res['type']=='movie' ) $groupinfo = $db->fetch_index("SELECT id,title,icon FROM ".PRE."_products_groups WHERE id IN (".implode(',',$groups).") OR grouptype='media'",'id');
	else $groupinfo = $db->fetch_index("SELECT id,title,icon FROM ".PRE."_products_groups WHERE id IN (".implode(',',$groups).")",'id');
	
	
	//NORMAL
	if ( $res['type']=='normal' ) {
		
		$manulink = mklink(
			'manufacturers.php?id='.$res['manufacturer'],
			'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
		);
		
		$apx->tmpl->assign('MANUFACTURER',$unitinfo[$res['manufacturer']]['title']);
		$apx->tmpl->assign('MANUFACTURER_WEBSITE',$unitinfo[$res['manufacturer']]['website']);
		$apx->tmpl->assign('MANUFACTURER_LINK',$manulink);
	}
	
	//VIDEOSPIEL
	elseif ( $res['type']=='game' ) {
		
		//System-Liste
		$systems = dash_unserialize($res['systems']);
		if ( !is_array($systems) ) $systems = array();
		$systemdata = array();
		foreach ( $systems AS $sysid ) {
			++$i;
			$systemdata[$i]['TITLE'] = $groupinfo[$sysid]['title'];
			$systemdata[$i]['ICON'] = $groupinfo[$sysid]['icon'];
		}
		
		//Media-Liste
		$media = dash_unserialize($res['media']);
		if ( !is_array($media) ) $media = array();
		$mediadata = array();
		foreach ( $media AS $medid ) {
			++$i;
			$mediadata[$i]['TITLE'] = $groupinfo[$medid]['title'];
			$mediadata[$i]['ICON'] = $groupinfo[$medid]['icon'];
		}
		
		$manulink = mklink(
			'manufacturers.php?id='.$res['manufacturer'],
			'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
		);
		
		$publink = mklink(
			'manufacturers.php?id='.$res['publisher'],
			'manufacturers,id'.$res['publisher'].urlformat($unitinfo[$res['publisher']]['title']).'.html'
		);
		
		$apx->tmpl->assign('DEVELOPER',$unitinfo[$res['manufacturer']]['title']);
		$apx->tmpl->assign('DEVELOPER_WEBSITE',$unitinfo[$res['manufacturer']]['website']);
		$apx->tmpl->assign('DEVELOPER_LINK',$manulink);
		$apx->tmpl->assign('PUBLISHER',$unitinfo[$res['publisher']]['title']);
		$apx->tmpl->assign('PUBLISHER_WEBSITE',$unitinfo[$res['publisher']]['website']);
		$apx->tmpl->assign('PUBLISHER_LINK',$publink);
		$apx->tmpl->assign('USK',$res['sk']);
		$apx->tmpl->assign('GENRE',$groupinfo[$res['genre']]['title']);
		$apx->tmpl->assign('MEDIA',$mediadata);
		$apx->tmpl->assign('SYSTEM',$systemdata);
		$apx->tmpl->assign('REQUIREMENTS',$res['requirements']);
	}
	
	//HARDWARE
	elseif ( $res['type']=='hardware' ) {
		
		$manulink = mklink(
			'manufacturers.php?id='.$res['manufacturer'],
			'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
		);
		
		$apx->tmpl->assign('MANUFACTURER',$unitinfo[$res['manufacturer']]['title']);
		$apx->tmpl->assign('MANUFACTURER_WEBSITE',$unitinfo[$res['manufacturer']]['website']);
		$apx->tmpl->assign('MANUFACTURER_LINK',$manulink);
		$apx->tmpl->assign('EQUIPMENT', $res['equipment']);
	}
	
	//SOFTWARE
	elseif ( $res['type']=='software' ) {
		
		//Media-Liste
		$media = dash_unserialize($res['media']);
		if ( !is_array($media) ) $media = array();
		$mediadata = array();
		foreach ( $media AS $medid ) {
			++$i;
			$mediadata[$i]['TITLE'] = $groupinfo[$medid]['title'];
			$mediadata[$i]['ICON'] = $groupinfo[$medid]['icon'];
		}
		
		$manulink = mklink(
			'manufacturers.php?id='.$res['manufacturer'],
			'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
		);
		
		$apx->tmpl->assign('MANUFACTURER',$unitinfo[$res['manufacturer']]['title']);
		$apx->tmpl->assign('MANUFACTURER_WEBSITE',$unitinfo[$res['manufacturer']]['website']);
		$apx->tmpl->assign('MANUFACTURER_LINK',$manulink);
		$apx->tmpl->assign('OS', $res['os']);
		$apx->tmpl->assign('LANGUAGES', $res['languages']);
		$apx->tmpl->assign('REQUIREMENTS', $res['requirements']);
		$apx->tmpl->assign('LICENSE', $res['license']);
		$apx->tmpl->assign('VERSION', $res['version']);
		$apx->tmpl->assign('MEDIA',$mediadata);
	}
	
	//MUSIK
	elseif ( $res['type']=='music' ) {
		
		//Media-Liste
		$media = dash_unserialize($res['media']);
		if ( !is_array($media) ) $media = array();
		$mediadata = array();
		foreach ( $media AS $medid ) {
			++$i;
			$mediadata[$i]['TITLE'] = $groupinfo[$medid]['title'];
			$mediadata[$i]['ICON'] = $groupinfo[$medid]['icon'];
		}
		
		$manulink = mklink(
			'manufacturers.php?id='.$res['manufacturer'],
			'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
		);
		
		$publink = mklink(
			'manufacturers.php?id='.$res['publisher'],
			'manufacturers,id'.$res['publisher'].urlformat($unitinfo[$res['publisher']]['title']).'.html'
		);
		
		$apx->tmpl->assign('ARTIST',$unitinfo[$res['manufacturer']]['title']);
		$apx->tmpl->assign('ARTIST_WEBSITE',$unitinfo[$res['manufacturer']]['website']);
		$apx->tmpl->assign('ARTIST_LINK',$manulink);
		$apx->tmpl->assign('LABEL',$unitinfo[$res['publisher']]['title']);
		$apx->tmpl->assign('LABEL_WEBSITE',$unitinfo[$res['publisher']]['website']);
		$apx->tmpl->assign('LABEL_LINK',$publink);
		$apx->tmpl->assign('FSK',$res['sk']);
		$apx->tmpl->assign('GENRE',$groupinfo[$res['genre']]['title']);
		$apx->tmpl->assign('MEDIA',$mediadata);
	}
	
	//FILM
	elseif ( $res['type']=='movie' ) {
		
		//Media-Liste
		$media = dash_unserialize($res['media']);
		if ( !is_array($media) ) $media = array();
		$mediadata = array();
		foreach ( $media AS $medid ) {
			++$i;
			$mediadata[$i]['TITLE'] = $groupinfo[$medid]['title'];
			$mediadata[$i]['ICON'] = $groupinfo[$medid]['icon'];
		}
		
		$publink = mklink(
			'manufacturers.php?id='.$res['publisher'],
			'manufacturers,id'.$res['publisher'].urlformat($unitinfo[$res['publisher']]['title']).'.html'
		);
		
		$apx->tmpl->assign('STUDIO',$unitinfo[$res['publisher']]['title']);
		$apx->tmpl->assign('STUDIO_WEBSITE',$unitinfo[$res['publisher']]['website']);
		$apx->tmpl->assign('STUDIO_LINK',$publink);
		$apx->tmpl->assign('REGISSEUR',$res['regisseur']);
		$apx->tmpl->assign('ACTORS',$res['actors']);
		$apx->tmpl->assign('LENGTH',$res['length']);
		$apx->tmpl->assign('FSK',$res['sk']);
		$apx->tmpl->assign('GENRE',$groupinfo[$res['genre']]['title']);
		$apx->tmpl->assign('MEDIA',$mediadata);
	}
	
	//LITERATUR
	elseif ( $res['type']=='book' ) {
		
		//Media-Liste
		$media = dash_unserialize($res['media']);
		if ( !is_array($media) ) $media = array();
		$mediadata = array();
		foreach ( $media AS $medid ) {
			++$i;
			$mediadata[$i]['TITLE'] = $groupinfo[$medid]['title'];
			$mediadata[$i]['ICON'] = $groupinfo[$medid]['icon'];
		}
		
		$manulink = mklink(
			'manufacturers.php?id='.$res['manufacturer'],
			'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
		);
		
		$publink = mklink(
			'manufacturers.php?id='.$res['publisher'],
			'manufacturers,id'.$res['publisher'].urlformat($unitinfo[$res['publisher']]['title']).'.html'
		);
		
		$apx->tmpl->assign('AUTHOR',$unitinfo[$res['manufacturer']]['title']);
		$apx->tmpl->assign('AUTHOR_WEBSITE',$unitinfo[$res['manufacturer']]['website']);
		$apx->tmpl->assign('AUTHOR_LINK',$manulink);
		$apx->tmpl->assign('PUBLISHER',$unitinfo[$res['publisher']]['title']);
		$apx->tmpl->assign('PUBLISHER_WEBSITE',$unitinfo[$res['publisher']]['website']);
		$apx->tmpl->assign('PUBLISHER_LINK',$publink);
		$apx->tmpl->assign('GENRE',$groupinfo[$res['genre']]['title']);
		$apx->tmpl->assign('MEDIA',$mediadata);
		$apx->tmpl->assign('ISBN',$res['isbn']);
	}
	
	//Benutzerdefinierte Felder
	for ( $i=1; $i<=10; $i++ ) {
		$apx->tmpl->assign('CUSTOM'.$i.'_NAME',replace($set['products']['custom_'.$res['type']][($i-1)]));
		$apx->tmpl->assign('CUSTOM'.$i,$res['custom'.$i]);
	}
	
	//Veröffentlichung
	if ( in_array('RELEASE',$parse) ) {
		$releasedata = array();
		$pubdata = $db->fetch("SELECT system,data FROM ".PRE."_products_releases WHERE prodid='".$res['id']."' ORDER BY stamp ASC");
		if ( count($pubdata) ) {
			foreach ( $pubdata AS $pubres ) {
				++$i;
				$info = unserialize($pubres['data']);
				$releasedate = products_format_release($info);
				$releasedata[$i]['DATE'] = $releasedate;
				if ( $res['type']=='game' ) {
					$releasedata[$i]['SYSTEM'] = $groupinfo[$pubres['system']]['title'];
					$releasedata[$i]['SYSTEM_ICON'] = $groupinfo[$pubres['system']]['icon'];
				}
				elseif ( $pubres['system'] ) {
					$releasedata[$i]['MEDIA'] = $groupinfo[$pubres['system']]['title'];
					$releasedata[$i]['MEDIA_ICON'] = $groupinfo[$pubres['system']]['icon'];
				}
			}
		}
		$apx->tmpl->assign('RELEASE',$releasedata);
	}
	
	//Kommentare
	if ( $apx->is_module('comments') && $set['products']['coms'] && $res['allowcoms'] ) {
		require_once(BASEDIR.getmodulepath('comments').'class.comments.php');
		$coms=new comments('products',$res['id']);
		$coms->assign_comments($parse);
	}
	
	//Bewertungen
	if ( $apx->is_module('ratings') && $set['products']['ratings'] && $res['allowrating'] ) {
		require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
		$rate=new ratings('products',$res['id']);
		$rate->assign_ratings($parse);
	}
	
	$apx->tmpl->parse('detail');
	require('lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////////////////////// SUCHE

if ( $_REQUEST['action']=='search' ) {
	$apx->lang->drop('search');
	
	//ERGEBNIS ANZEIGEN
	if ( $_REQUEST['searchid'] ) {
		titlebar($apx->lang->get('HEADLINE_SEARCH'));
		
		//Suchergebnis auslesen
		$resultIds = '';
		list($resultIds) = getSearchResult('products', $_REQUEST['searchid']);
		
		//Keine Ergebnisse
		if ( !$resultIds ) {
			message($apx->lang->get('MSG_NORESULT'),'javascript:history.back();');
			require('lib/_end.php');
		}
		
		//SUCHE AUSFÜHREN
		$parse=$apx->tmpl->used_vars('search_result');
		
		//Seitenzahlen generieren
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_products WHERE active='1' AND id IN (".implode(', ', $resultIds).")");
		pages(
			mklink(
				'products.php?action=search&searchid='.$_REQUEST['searchid'],
				'products.html?action=search&searchid='.$_REQUEST['searchid']
			),
			$count,
			$set['products']['searchepp']
		);
		
		//Keine Ergebnisse
		if ( !$count ) {
			message($apx->lang->get('MSG_NORESULT'),'javascript:history.back();');
			require('lib/_end.php');
		}
		
		//Produkte auslesen
		if ( $set['products']['sortby']!=1 || $_REQUEST['sortby']=='release.DESC' ) $data=$db->fetch("SELECT a.*,min(stamp) AS minrelease,IF(b.prodid IS NULL,0,1) AS isset FROM ".PRE."_products AS a LEFT JOIN ".PRE."_products_releases AS b ON a.id=b.prodid WHERE a.active='1' AND id IN (".implode(', ', $resultIds).") GROUP BY a.id ORDER BY isset DESC, minrelease ASC ".getlimit($set['products']['searchepp']));
		else $data = $db->fetch("SELECT * FROM ".PRE."_products WHERE active='1' AND id IN (".implode(', ', $resultIds).") ORDER BY title ASC ".getlimit($set['products']['searchepp']));
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
			$groups = array();
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
				
				//Sammlung
				if ( $user->info['userid'] ) {
					if ( !products_in_coll($res['id']) ) {
						$tabledata[$i]['LINK_COLLECTION_ADD'] = mklink(
							'products.php?id='.$res['id'].'&amp;addcoll=1',
							'products,id'.$res['id'].urlformat($res['title']).'.html?addcoll=1'
						);
					}
					else {
						$tabledata[$i]['LINK_COLLECTION_REMOVE'] = mklink(
							'products.php?id='.$res['id'].'&amp;removecoll=1',
							'products,id'.$res['id'].urlformat($res['title']).'.html?removecoll=1'
						);
					}
				}
				
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
					$tabledata[$i]['RELEASE'] = $releaseinfo[$res['id']];
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
		
		$apx->tmpl->assign('PRODUCT',$tabledata);
		$apx->tmpl->parse('search_result');
	}
	
	//SUCHE DURCHFÜHREN
	else {
		$where='';
		
		//Alle Produkttypen sind nicht gewählt
		if ( !is_array($_REQUEST['type']) || !count($_REQUEST['type']) ) {
			$_REQUEST['type'] = $types;
		}
		
		//Suchbegriffe
		if ( $_REQUEST['item'] ) {
			$items=array();
			$it=explode(' ',preg_replace('#[ ]{2,}#',' ',trim($_REQUEST['item'])));
			$tagmatches = products_match_tags($it);
			foreach ( $it AS $item ) {
				if ( trim($item) ) {
					$string=preg_replace('#[\s_-]+#','[^0-9a-zA-Z]*',$item);
					if ( preg_match('#^[0-9a-zA-Z]+$#',$string) ) $items[]=" LIKE '%".addslashes_like($string)."%' ";
					else $items[]=" REGEXP '".addslashes($string)."' ";
				}
			}
			
			if ( $_REQUEST['conn']=='or' ) $conn=' OR ';
			else $conn=' AND ';
			
			$search1=$search2=array();
			foreach ( $items AS $regexp ) {
				$tagmatch = array_shift($tagmatches);
				$search[]="(
					".iif($tagmatch, " id IN (".implode(',', $tagmatch).") OR ")."
					title ".$regexp." OR 
					text ".$regexp." OR 
					regisseur ".$regexp." OR 
					actors ".$regexp." OR 
					requirements ".$regexp." OR 
					equipment ".$regexp." OR 
					os ".$regexp." OR 
					languages ".$regexp." OR 
					license ".$regexp." OR 
					version ".$regexp." OR 
					isbn ".$regexp." OR 
					custom1 ".$regexp." OR 
					custom2 ".$regexp." OR 
					custom3 ".$regexp." OR 
					custom4 ".$regexp." OR 
					custom5 ".$regexp." OR 
					custom6 ".$regexp." OR 
					custom7 ".$regexp." OR 
					custom8 ".$regexp." OR 
					custom9 ".$regexp." OR 
					custom10 ".$regexp."
				) ";
			}
			$where.=iif($where,' AND ').' ( '.implode($conn,$search).' ) ';
		}
		
		//Nach Tag suchen
		if ( $_REQUEST['tag'] ) {
			$tagid = getTagId($_REQUEST['tag']);
			if ( $tagid ) {
				$data = $db->fetch("SELECT id FROM ".PRE."_products_tags WHERE tagid='".$tagid."'");
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
		
		//Produkttyp
		$prodtypes=array();
		foreach ( $_REQUEST['type'] AS $type ) {
			if ( in_array($type,$types) ) $prodtypes[]="'".$type."'";
		}
		if ( count($prodtypes)>0 && count($prodtypes)<3 ) {
			$where.=iif($where,' AND ').'type IN ('.implode(',',$prodtypes).')';
		}
		
		//Keine Suchkriterien vorhanden
		if ( !$where ) {
			message($apx->lang->get('CORE_BACK'),'javascript:history.back();');
			require('lib/_end.php');
		}
		
		//SUCHE AUSFÜHREN
		else {
			$data = $db->fetch("SELECT id FROM ".PRE."_products WHERE ".$where);
			$resultIds = get_ids($data, 'id');
			
			//Keine Ergebnisse
			if ( !$resultIds ) {
				message($apx->lang->get('MSG_NORESULT'),'javascript:history.back();');
				require('lib/_end.php');
			}
			
			$searchid = saveSearchResult('products', $resultIds);
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.str_replace('&amp;', '&', mklink(
				'products.php?action=search&searchid='.$searchid,
				'products.html?action=search&searchid='.$searchid
			)));
		}
	}
	require('lib/_end.php');
}



////////////////////////////////////////////////////////////////////////////////////////// PRODUKT-LISTE

//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('index');

$apx->lang->drop('search');

$where='';
if ( !$_REQUEST['letter'] ) $_REQUEST['letter']='0';
if ( !in_array($_REQUEST['type'],$types) ) $_REQUEST['type']=0;

//Typ-Filter
if ( $_REQUEST['type'] ) {
	$where.=" AND type='".addslashes($_REQUEST['type'])."' ";
}
if ( $_REQUEST['genre'] ) {
	$where.=" AND genre='".addslashes($_REQUEST['genre'])."' ";
}
if ( $_REQUEST['system'] ) {
	$where.=" AND systems LIKE '%|".$_REQUEST['system']."|%' ";
}

//Buchstaben-Liste
letters(mklink(
	'products.php?type='.$_REQUEST['type'].'&amp;sortby='.$_REQUEST['sortby'],
	'products,'.$_REQUEST['type'].',{LETTER},1.html'.iif($_REQUEST['sortby'],'?sortby='.$_REQUEST['sortby'])
));
if ( $_REQUEST['letter'] ) {
	if ( $_REQUEST['letter']=='spchar' ) $where.=" AND title NOT REGEXP(\"^[a-zA-Z]\") ";
	else $where.=" AND title LIKE '".addslashes($_REQUEST['letter'])."%' ";
}

//Seitenzahlen
list($count)=$db->first("SELECT count(id) FROM ".PRE."_products WHERE active='1' ".$where);
$pagelink=mklink(
	'products.php?type='.$_REQUEST['type'].'&amp;sortby='.$_REQUEST['sortby'].'&amp;letter='.$_REQUEST['letter'],
	'products,'.$_REQUEST['type'].','.$_REQUEST['letter'].',{P}.html'.iif($_REQUEST['sortby'],'?sortby='.$_REQUEST['sortby'])
);
pages($pagelink,$count,$set['products']['epp']);

//Orderby
$orderdef[0]='title';
$orderdef['title']=array('title','ASC');
$orderdef['release']=array('minrelease','ASC');
$orderdef['hits']=array('hits','DESC');


//Standardsortierung
if ( !$_REQUEST['sortby'] ) {
	if ( $set['products']['sortby']==1 ) {
		$_REQUEST['sortby'] = 'title.ASC';
	}
	else {
		$_REQUEST['sortby'] = 'release.ASC';
	}	
}


//Produkte auslesen
if ( $_REQUEST['sortby']=='release.ASC' || $_REQUEST['sortby']=='release.DESC' ) $data=$db->fetch("SELECT a.*,min(stamp) AS minrelease,IF(b.prodid IS NULL,0,1) AS isset FROM ".PRE."_products AS a LEFT JOIN ".PRE."_products_releases AS b ON a.id=b.prodid WHERE a.active='1' ".$where." GROUP BY a.id ".getorder($orderdef,'isset DESC',1)." ".getlimit($set['products']['epp']));
else $data = $db->fetch("SELECT * FROM ".PRE."_products WHERE active='1' ".$where.getorder($orderdef).getlimit($set['products']['epp']));
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
	/*if ( in_template(array('PRODUCT.MEDIA','PRODUCT.MEDIA_ICON'),$parse) ) $groups = array_merge($groups,get_ids($data,'media'));
	if ( in_array('PRODUCT.GENRE',$parse) ) $groups = array_merge($groups,get_ids($data,'genre'));
	if ( in_array('game',$types) && in_template(array('PRODUCT.RELEASE.SYSTEM','PRODUCT.RELEASE.SYSTEM_ICON','PRODUCT.SYSTEM'),$parse) ) {
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
		
		//Sammlung
		if ( $user->info['userid'] ) {
			if ( !products_in_coll($res['id']) ) {
				$tabledata[$i]['LINK_COLLECTION_ADD'] = mklink(
					'products.php?id='.$res['id'].'&amp;addcoll=1',
					'products,id'.$res['id'].urlformat($res['title']).'.html?addcoll=1'
				);
			}
			else {
				$tabledata[$i]['LINK_COLLECTION_REMOVE'] = mklink(
					'products.php?id='.$res['id'].'&amp;removecoll=1',
					'products,id'.$res['id'].urlformat($res['title']).'.html?removecoll=1'
				);
			}
		}
		
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
			$tabledata[$i]['RELEASE'] = $releaseinfo[$res['id']];
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

//Sortieren nach...
ordervars(
	$orderdef,
	mklink(
		'products.php?type='.$_REQUEST['type'].'&amp;letter='.$_REQUEST['letter'],
		'products,'.$_REQUEST['type'].','.$_REQUEST['letter'].',1.html'
	)
);

//Nach Produkttyp filtern
$apx->tmpl->assign('LINK_SHOWALL',mklink('products.php','products,0,0,1.html'));
$apx->tmpl->assign('LINK_SHOWNORMAL',mklink('products.php?type=normal','products,normal,0,1.html'));
$apx->tmpl->assign('LINK_SHOWGAME',mklink('products.php?type=game','products,game,0,1.html'));
$apx->tmpl->assign('LINK_SHOWSOFTWARE',mklink('products.php?type=software','products,software,0,1.html'));
$apx->tmpl->assign('LINK_SHOWHARDWARE',mklink('products.php?type=hardware','products,hardware,0,1.html'));
$apx->tmpl->assign('LINK_SHOWMUSIC',mklink('products.php?type=music','products,music,0,1.html'));
$apx->tmpl->assign('LINK_SHOWMOVIE',mklink('products.php?type=movie','products,movie,0,1.html'));
$apx->tmpl->assign('LINK_SHOWBOOK',mklink('products.php?type=book','products,book,0,1.html'));

$apx->tmpl->assign('TYPE',iif($_REQUEST['type'],$_REQUEST['type'],''));
$apx->tmpl->assign('PRODUCT',$tabledata);
$apx->tmpl->assign('SEARCH_POSTTO',mklink('products.php', 'products.html'));
$apx->tmpl->parse('index');



////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>