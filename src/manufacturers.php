<?php

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_start.php';  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once BASEDIR.getmodulepath('products').'functions.php';

$apx->module('products');
$apx->lang->drop('manufacturers');

headline($apx->lang->get('HEADLINE_MANU'), mklink('manufacturers.php', 'manufacturers.html'));
titlebar($apx->lang->get('HEADLINE_MANU'));

$_REQUEST['id'] = (int) $_REQUEST['id'];
$types = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
$manutypes = ['person', 'company'];

////////////////////////////////////////////////////////////////////////////////////////// PRODUKTE ZUM HERSTELLER

if ($_REQUEST['id'] && $_REQUEST['products']) {
    $res = $db->first('SELECT id,type,title FROM '.PRE."_products_units WHERE id='".$_REQUEST['id']."' LIMIT 1");
    if (!$res['id']) {
        filenotfound();
    }
    titlebar($apx->lang->get('PRODUCTS_OF').' '.$res['title']);
    $apx->lang->drop('products');

    //Verwendete Variablen
    $parse = $apx->tmpl->used_vars('manufacturers_products');

    //Link
    $link = mklink(
        'manufacturers.php?id='.$res['id'],
        'manufacturers,id'.$res['id'].urlformat($res['title']).'.html'
    );

    //Link dieser Seite
    $thislink = mklink(
        'manufacturers.php?id='.$res['id'].'&amp;products=1',
        'manufacturers,id'.$res['id'].urlformat($res['title']).'.html?products=1'
    );

    //Hersteller-Info
    $apx->tmpl->assign('ID', $res['id']);
    $apx->tmpl->assign('TYPE', $res['type']);
    $apx->tmpl->assign('LINK', $link);
    $apx->tmpl->assign('TITLE', $res['title']);

    $where = '';
    if (!$_REQUEST['letter']) {
        $_REQUEST['letter'] = '0';
    }
    if (!in_array($_REQUEST['type'], $types)) {
        $_REQUEST['type'] = 0;
    }

    //Typ-Filter
    if ($_REQUEST['type']) {
        $where .= " AND type='".addslashes($_REQUEST['type'])."' ";
    }

    //Buchstaben-Liste
    letters($thislink.'&amp;type='.$_REQUEST['type'].'&amp;sortby='.$_REQUEST['sortby']);
    if ($_REQUEST['letter']) {
        if ('spchar' == $_REQUEST['letter']) {
            $where .= ' AND title NOT REGEXP("^[a-zA-Z]") ';
        } else {
            $where .= " AND title LIKE '".addslashes($_REQUEST['letter'])."%' ";
        }
    }

    //Seitenzahlen
    list($count) = $db->first('SELECT count(id) FROM '.PRE."_products WHERE active='1' AND ( manufacturer='".$res['id']."' OR publisher='".$res['id']."' ) ".$where);
    $pagelink = $thislink.'&amp;type='.$_REQUEST['type'].'&amp;sortby='.$_REQUEST['sortby'].'&amp;letter='.$_REQUEST['letter'];
    pages($pagelink, $count, $set['products']['manuprod_epp']);

    //Orderby
    $orderdef[0] = 'title';
    $orderdef['title'] = ['title', 'ASC'];
    $orderdef['release'] = ['minrelease', 'ASC'];

    //Standardsortierung
    if (!$_REQUEST['sortby']) {
        if (1 == $set['products']['sortby']) {
            $_REQUEST['sortby'] = 'title.ASC';
        } else {
            $_REQUEST['sortby'] = 'release.ASC';
        }
    }

    //Produkte auslesen
    if ('release.ASC' == $_REQUEST['sortby'] || 'release.DESC' == $_REQUEST['sortby']) {
        $data = $db->fetch('SELECT a.*,min(stamp) AS minrelease,IF(b.prodid IS NULL,0,1) AS isset FROM '.PRE.'_products AS a LEFT JOIN '.PRE."_products_releases AS b ON a.id=b.prodid WHERE a.active='1' AND ( a.manufacturer='".$res['id']."' OR a.publisher='".$res['id']."' ) ".$where.' GROUP BY a.id '.getorder($orderdef, 'isset DESC', 1).' '.getlimit($set['products']['manuprod_epp']));
    } else {
        $data = $db->fetch('SELECT * FROM '.PRE."_products WHERE active='1' AND ( manufacturer='".$res['id']."' OR publisher='".$res['id']."' ) ".$where.getorder($orderdef).getlimit($set['products']['manuprod_epp']));
    }
    $ids = get_ids($data, 'id');
    $types = get_ids($data, 'type');
    if (count($data)) {
        $unitvars = [
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
            'PRODUCT.AUTHOR_LINK',
        ];

        //Einheiten auslesen
        $unitinfo = [];
        if (in_template($unitvars, $parse)) {
            $unitids = array_merge(get_ids($data, 'manufacturer'), get_ids($data, 'publisher'));
            $unitinfo = $db->fetch_index('SELECT id,title,website FROM '.PRE.'_products_units WHERE id IN ('.implode(',', $unitids).')', 'id');
        }

        //Gruppen auslesen
        $groupinfo = [];
        $groups = [];
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
        $groupinfo = $db->fetch_index('SELECT id,title,icon FROM '.PRE.'_products_groups', 'id');

        //Veröffentlichungs-Daten auslesen
        $releaseinfo = [];
        if (in_array('PRODUCT.RELEASE', $parse)) {
            $releasedata = $db->fetch('SELECT prodid,system,data,stamp FROM '.PRE.'_products_releases WHERE prodid IN ('.implode(',', $ids).') ORDER BY stamp ASC');
            if (count($releasedata)) {
                foreach ($releasedata as $relres) {
                    $info = unserialize($relres['data']);
                    $releasedate = products_format_release($info);
                    $relentry = [
                        'stamp' => $relres['stamp'],
                        'DATE' => $releasedate,
                        'MEDIA' => $groupinfo[$relres['system']]['title'],
                        'MEDIA_ICON' => $groupinfo[$relres['system']]['icon'],
                        'SYSTEM' => $groupinfo[$relres['system']]['title'],
                        'SYSTEM_ICON' => $groupinfo[$relres['system']]['icon'],
                    ];
                    $releaseinfo[$relres['prodid']][] = $relentry;
                }
            }
        }

        //Produkte auflisten
        foreach ($data as $res) {
            ++$i;

            //Link
            $link = mklink(
                'products.php?id='.$res['id'],
                'products,id'.$res['id'].urlformat($res['title']).'.html'
            );

            //Produktbild
            if (in_array('PRODUCT.PICTURE', $parse) || in_array('PRODUCT.PICTURE_POPUP', $parse) || in_array('PRODUCT.PICTURE_POPUPPATH', $parse)) {
                list($picture, $picture_popup, $picture_popuppath) = products_pic($res['picture']);
            }

            //Teaserbild
            if (in_array('PRODUCT.TEASERPIC', $parse) || in_array('PRODUCT.TEASERPIC_POPUP', $parse) || in_array('PRODUCT.TEASERPIC_POPUPPATH', $parse)) {
                list($teaserpic, $teaserpic_popup, $teaserpic_popuppath) = products_pic($res['teaserpic']);
            }

            //Text
            $text = '';
            if (in_array('PRODUCT.TEXT', $parse)) {
                $text = $res['text'];
                if ($apx->is_module('glossar')) {
                    $text = glossar_highlight($text);
                }
            }

            //Tags
            if (in_array('PRODUCT.TAG', $parse) || in_array('PRODUCT.TAG_IDS', $parse) || in_array('PRODUCT.KEYWORDS', $parse)) {
                list($tagdata, $tagids, $keywords) = products_tags($res['id']);
            }

            //Standard-Platzhalter
            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['TYPE'] = $res['type'];
            $tabledata[$i]['LINK'] = $link;
            $tabledata[$i]['TITLE'] = $res['title'];
            $tabledata[$i]['TEXT'] = $text;
            $tabledata[$i]['WEBSITE'] = $res['website'];
            $tabledata[$i]['BUYLINK'] = $res['buylink'];
            $tabledata[$i]['PRICE'] = $res['price'];
            $tabledata[$i]['PICTURE'] = $picture;
            $tabledata[$i]['PICTURE_POPUP'] = $picture_popup;
            $tabledata[$i]['PICTURE_POPUPPATH'] = $picture_popuppath;
            $tabledata[$i]['TEASERPIC'] = $teaserpic;
            $tabledata[$i]['TEASERPIC_POPUP'] = $teaserpic_popup;
            $tabledata[$i]['TEASERPIC_POPUPPATH'] = $teaserpic_popuppath;
            $tabledata[$i]['PRODUCT_ID'] = $res['prodid'];
            $tabledata[$i]['RECOMMENDED_PRICE'] = $res['recprice'];
            $tabledata[$i]['GUARANTEE'] = $res['guarantee'];

            //Sammlung
            if ($user->info['userid']) {
                if (!products_in_coll($res['id'])) {
                    $tabledata[$i]['LINK_COLLECTION_ADD'] = mklink(
                        'products.php?id='.$res['id'].'&amp;addcoll=1',
                        'products,id'.$res['id'].urlformat($res['title']).'.html?addcoll=1'
                    );
                } else {
                    $tabledata[$i]['LINK_COLLECTION_REMOVE'] = mklink(
                        'products.php?id='.$res['id'].'&amp;removecoll=1',
                        'products,id'.$res['id'].urlformat($res['title']).'.html?removecoll=1'
                    );
                }
            }

            //Tags
            $tabledata[$i]['TAG'] = $tagdata;
            $tabledata[$i]['TAG_IDS'] = $tagids;
            $tabledata[$i]['KEYWORDS'] = $keywords;

            //NORMAL
            if ('normal' == $res['type']) {
                $manulink = mklink(
                    'manufacturers.php?id='.$res['manufacturer'],
                    'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
                );

                $tabledata[$i]['MANUFACTURER'] = $unitinfo[$res['manufacturer']]['title'];
                $tabledata[$i]['MANUFACTURER_WEBSITE'] = $unitinfo[$res['manufacturer']]['website'];
                $tabledata[$i]['MANUFACTURER_LINK'] = $manulink;
            }

            //VIDEOSPIEL
            elseif ('game' == $res['type']) {
                //System-Liste
                $systemdata = [];
                if (in_array('PRODUCT.SYSTEM', $parse)) {
                    $systems = unserialize($res['systems']);
                    if (!is_array($systems)) {
                        $systems = [];
                    }
                    foreach ($systems as $sysid) {
                        ++$ii;
                        $systemdata[$ii]['TITLE'] = $groupinfo[$sysid]['title'];
                        $systemdata[$ii]['ICON'] = $groupinfo[$sysid]['icon'];
                    }
                }

                //Media-Liste
                $media = dash_unserialize($res['media']);
                if (!is_array($media)) {
                    $media = [];
                }
                $mediadata = [];
                foreach ($media as $medid) {
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
            elseif ('hardware' == $res['type']) {
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
            elseif ('software' == $res['type']) {
                //Media-Liste
                $media = dash_unserialize($res['media']);
                if (!is_array($media)) {
                    $media = [];
                }
                $mediadata = [];
                foreach ($media as $medid) {
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
            elseif ('music' == $res['type']) {
                //Media-Liste
                $media = dash_unserialize($res['media']);
                if (!is_array($media)) {
                    $media = [];
                }
                $mediadata = [];
                foreach ($media as $medid) {
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
            elseif ('movie' == $res['type']) {
                //Media-Liste
                $media = dash_unserialize($res['media']);
                if (!is_array($media)) {
                    $media = [];
                }
                $mediadata = [];
                foreach ($media as $medid) {
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
            elseif ('book' == $res['type']) {
                //Media-Liste
                $media = dash_unserialize($res['media']);
                if (!is_array($media)) {
                    $media = [];
                }
                $mediadata = [];
                foreach ($media as $medid) {
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
            for ($ii = 1; $ii <= 10; ++$ii) {
                $tabledata[$i]['CUSTOM'.$ii.'_NAME'] = replace($set['products']['custom_'.$res['type']][($ii - 1)]);
                $tabledata[$i]['CUSTOM'.$ii] = $res['custom'.$ii];
            }

            //Veröffentlichung
            if (in_array('PRODUCT.RELEASE', $parse)) {
                $tabledata[$i]['RELEASE'] = $releaseinfo[$res['id']];
            }

            //Kommentare
            if ($apx->is_module('comments') && $set['products']['coms'] && $res['allowcoms']) {
                require_once BASEDIR.getmodulepath('comments').'class.comments.php';
                if (!isset($coms)) {
                    $coms = new comments('products', $res['id']);
                } else {
                    $coms->mid = $res['id'];
                }

                $link = mklink(
                    'products.php?id='.$res['id'],
                    'products,id'.$res['id'].urlformat($res['title']).'.html'
                );

                $tabledata[$i]['COMMENT_COUNT'] = $coms->count();
                $tabledata[$i]['COMMENT_LINK'] = $coms->link($link);
                $tabledata[$i]['DISPLAY_COMMENTS'] = 1;
                if (in_template(['PRODUCT.COMMENT_LAST_USERID', 'PRODUCT.COMMENT_LAST_NAME', 'PRODUCT.COMMENT_LAST_TIME'], $parse)) {
                    $tabledata[$i]['COMMENT_LAST_USERID'] = $coms->last_userid();
                    $tabledata[$i]['COMMENT_LAST_NAME'] = $coms->last_name();
                    $tabledata[$i]['COMMENT_LAST_TIME'] = $coms->last_time();
                }
            }

            //Bewertungen
            if ($apx->is_module('ratings') && $set['products']['ratings'] && $res['allowrating']) {
                require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
                if (!isset($rate)) {
                    $rate = new ratings('products', $res['id']);
                } else {
                    $rate->mid = $res['id'];
                }

                $tabledata[$i]['RATING'] = $rate->display();
                $tabledata[$i]['RATING_VOTES'] = $rate->count();
                $tabledata[$i]['DISPLAY_RATING'] = 1;
            }
        }
    }

    //Sortieren nach...
    ordervars(
        $orderdef,
        $thislink.'&amp;type='.$_REQUEST['type'].'&amp;letter='.$_REQUEST['letter']
    );

    //Nach Produkttyp filtern
    $apx->tmpl->assign('LINK_SHOWALL', $thislink);
    $apx->tmpl->assign('LINK_SHOWNORMAL', $thislink.'&amp;type=normal');
    $apx->tmpl->assign('LINK_SHOWGAME', $thislink.'&amp;type=game');
    $apx->tmpl->assign('LINK_SHOWSOFTWARE', $thislink.'&amp;type=software');
    $apx->tmpl->assign('LINK_SHOWHARDWARE', $thislink.'&amp;type=hardware');
    $apx->tmpl->assign('LINK_SHOWMUSIC', $thislink.'&amp;type=music');
    $apx->tmpl->assign('LINK_SHOWMOVIE', $thislink.'&amp;type=movie');
    $apx->tmpl->assign('LINK_SHOWBOOK', $thislink.'&amp;type=book');

    $apx->tmpl->assign('FILTERTYPE', iif($_REQUEST['type'], $_REQUEST['type'], ''));
    $apx->tmpl->assign('PRODUCT', $tabledata);
    $apx->tmpl->parse('manufacturers_products');
    require 'lib/_end.php';
}

////////////////////////////////////////////////////////////////////////////////////////// HERSTELLER-DETAIL

if ($_REQUEST['id']) {
    $res = $db->first('SELECT * FROM '.PRE."_products_units WHERE id='".$_REQUEST['id']."' LIMIT 1");
    if (!$res['id']) {
        filenotfound();
    }
    titlebar($apx->lang->get('HEADLINE_MANU').': '.$res['title']);

    //Verwendete Variablen
    $parse = $apx->tmpl->used_vars('manufacturers_detail');

    //Link
    $link = mklink(
        'manufacturers.php?id='.$res['id'],
        'manufacturers,id'.$res['id'].urlformat($res['title']).'.html'
    );

    //Produktbild
    if (in_array('PICTURE', $parse) || in_array('PICTURE_POPUP', $parse) || in_array('PICTURE_POPUPPATH', $parse)) {
        list($picture, $picture_popup, $picture_popuppath) = products_pic($res['picture']);
    }

    //Text
    $text = $res['text'];
    if ($apx->is_module('glossar')) {
        $text = glossar_highlight($text);
    }

    //Standard-Platzhalter
    $apx->tmpl->assign('ID', $res['id']);
    $apx->tmpl->assign('TYPE', $res['type']);
    $apx->tmpl->assign('LINK', $link);
    $apx->tmpl->assign('TITLE', $res['title']);
    $apx->tmpl->assign('TEXT', $text);
    $apx->tmpl->assign_static('META_DESCRIPTION', replace($res['meta_description']));
    $apx->tmpl->assign('WEBSITE', $res['website']);
    $apx->tmpl->assign('PICTURE', $picture);
    $apx->tmpl->assign('PICTURE_POPUP', $picture_popup);
    $apx->tmpl->assign('PICTURE_POPUPPATH', $picture_popuppath);
    $apx->tmpl->assign('FULLNAME', $res['fullname']);
    $apx->tmpl->assign('ADDRESS', $res['address']);
    $apx->tmpl->assign('EMAIL', $res['email']);
    $apx->tmpl->assign('EMAIL_ENCRYPTED', cryptMail($res['email']));
    $apx->tmpl->assign('PHONE', $res['phone']);

    //Nur Firma
    if ('company' == $res['type']) {
        $apx->tmpl->assign('FOUNDER', $res['founder']);
        $apx->tmpl->assign('FOUNDING_YEAR', $res['founding_year']);
        $apx->tmpl->assign('FOUNDING_COUNTRY', $res['founding_country']);
        $apx->tmpl->assign('LEGALFORM', $res['legalform']);
        $apx->tmpl->assign('HEADQUATERS', $res['headquaters']);
        $apx->tmpl->assign('EXECUTIVE', $res['executive']);
        $apx->tmpl->assign('EMPLOYEES', $res['employees']);
        $apx->tmpl->assign('TURNOVER', $res['turnover']);
        $apx->tmpl->assign('SECTOR', $res['sector']);
        $apx->tmpl->assign('PRODUCTS', $res['products']);
    }

    //Link: Produkte anzeigen
    $apx->tmpl->assign('LINK_SHOWPRODUCTS', mklink(
        'manufacturers.php?id='.$res['id'].'&amp;products=1',
        'manufacturers,id'.$res['id'].urlformat($res['title']).'.html?products=1'
    ));

    $apx->tmpl->parse('manufacturers_detail');
    require 'lib/_end.php';
}

//////////////////////////////////////////////////////////////////////////////////////////////////////// SUCHE

if ('search' == $_REQUEST['action']) {
    $apx->lang->drop('manu_search');

    //ERGEBNIS ANZEIGEN
    if ($_REQUEST['searchid']) {
        titlebar($apx->lang->get('HEADLINE_SEARCH'));

        //Suchergebnis auslesen
        $resultIds = '';
        list($resultIds) = getSearchResult('products_manu', $_REQUEST['searchid']);

        //Keine Ergebnisse
        if (!$resultIds) {
            message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //SUCHE AUSFÜHREN
        $parse = $apx->tmpl->used_vars('manufacturers_search_result');

        //Seitenzahlen generieren
        list($count) = $db->first('SELECT count(id) FROM '.PRE.'_products_units WHERE id IN ('.implode(', ', $resultIds).')');
        pages(
            mklink(
                'manufacturers.php?action=search&searchid='.$_REQUEST['searchid'],
                'manufacturers.html?action=search&searchid='.$_REQUEST['searchid']
            ),
            $count,
            $set['products']['manu_searchepp']
        );

        //Keine Ergebnisse
        if (!$count) {
            message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //Hersteller auslesen
        $data = $db->fetch('SELECT * FROM '.PRE.'_products_units WHERE id IN ('.implode(', ', $resultIds).') ORDER BY title ASC '.getlimit($set['products']['manu_searchepp']));
        if (count($data)) {
            foreach ($data as $res) {
                ++$i;

                //Link
                $link = mklink(
                    'manufacturers.php?id='.$res['id'],
                    'manufacturers,id'.$res['id'].urlformat($res['title']).'.html'
                );

                //Produktbild
                if (in_array('MANU.PICTURE', $parse) || in_array('MANU.PICTURE_POPUP', $parse) || in_array('MANU.PICTURE_POPUPPATH', $parse)) {
                    list($picture, $picture_popup, $picture_popuppath) = products_pic($res['picture']);
                }

                //Produktbild
                if (in_array('MANU.PRODUCT_COUNT', $parse)) {
                    list($prodcount) = $db->first('SELECT count(id) FROM '.PRE."_products WHERE active=1 AND manufacturer='".$res['id']."' OR publisher='".$res['id']."'");
                }

                //Text
                $text = $res['text'];
                if ($apx->is_module('glossar')) {
                    $text = glossar_highlight($text);
                }

                //Standard-Platzhalter
                $tabledata[$i]['ID'] = $res['id'];
                $tabledata[$i]['TYPE'] = $res['type'];
                $tabledata[$i]['LINK'] = $link;
                $tabledata[$i]['TITLE'] = $res['title'];
                $tabledata[$i]['TEXT'] = $text;
                $tabledata[$i]['WEBSITE'] = $res['website'];
                $tabledata[$i]['PRODUCT_COUNT'] = $prodcount;
                $tabledata[$i]['PICTURE'] = $picture;
                $tabledata[$i]['PICTURE_POPUP'] = $picture_popup;
                $tabledata[$i]['PICTURE_POPUPPATH'] = $picture_popuppath;
                $tabledata[$i]['FULLNAME'] = $res['fullname'];
                $tabledata[$i]['ADDRESS'] = $res['address'];
                $tabledata[$i]['EMAIL'] = $res['email'];
                $tabledata[$i]['EMAIL_ENCRYPTED'] = cryptMail($res['email']);
                $tabledata[$i]['PHONE'] = $res['phone'];

                //Nur Firma
                if ('company' == $res['type']) {
                    $tabledata[$i]['FOUNDER'] = $res['founder'];
                    $tabledata[$i]['FOUNDING_YEAR'] = $res['founding_year'];
                    $tabledata[$i]['FOUNDING_COUNTRY'] = $res['founding_country'];
                    $tabledata[$i]['LEGALFORM'] = $res['legalform'];
                    $tabledata[$i]['HEADQUATERS'] = $res['headquaters'];
                    $tabledata[$i]['EXECUTIVE'] = $res['executive'];
                    $tabledata[$i]['EMPLOYEES'] = $res['employees'];
                    $tabledata[$i]['TURNOVER'] = $res['turnover'];
                    $tabledata[$i]['SECTOR'] = $res['sector'];
                    $tabledata[$i]['PRODUCTS'] = $res['products'];
                }
            }
        }

        $apx->tmpl->assign('MANU', $tabledata);
        $apx->tmpl->parse('manufacturers_search_result');
    }

    //SUCHE DURCHFÜHREN
    else {
        $where = '';

        //Alle Artikeltypen sind nicht gewählt
        if (!is_array($_REQUEST['type']) || !count($_REQUEST['type'])) {
            $_REQUEST['type'] = $types;
        }

        //Suchbegriffe
        if ($_REQUEST['item']) {
            $items = [];
            $it = explode(' ', preg_replace('#[ ]{2,}#', ' ', trim($_REQUEST['item'])));
            foreach ($it as $item) {
                if (trim($item)) {
                    $string = preg_replace('#[\s_-]+#', '[^0-9a-zA-Z]*', $item);
                    if (preg_match('#^[0-9a-zA-Z]+$#', $string)) {
                        $items[] = " LIKE '%".addslashes_like($string)."%' ";
                    } else {
                        $items[] = " REGEXP '".addslashes($string)."' ";
                    }
                }
            }

            if ('or' == $_REQUEST['conn']) {
                $conn = ' OR ';
            } else {
                $conn = ' AND ';
            }

            $search1 = $search2 = [];
            foreach ($items as $regexp) {
                $search[] = '(
					title '.$regexp.' OR 
					text '.$regexp.' OR 
					fullname '.$regexp.' OR 
					address '.$regexp.' OR 
					email '.$regexp.' OR 
					phone '.$regexp.' OR 
					founder '.$regexp.' OR 
					founding_year '.$regexp.' OR 
					founding_country '.$regexp.' OR 
					legalform '.$regexp.' OR 
					headquaters '.$regexp.' OR 
					executive '.$regexp.'
				) ';
            }
            $where .= iif($where, ' AND ').' ( '.implode($conn, $search).' ) ';
        }

        //Herstellertyp
        $unittypes = [];
        foreach ($_REQUEST['type'] as $type) {
            if (in_array($type, $manutypes)) {
                $unittypes[] = "'".$type."'";
            }
        }
        if (count($unittypes) > 0 && count($unittypes) < 3) {
            $where .= iif($where, ' AND ').'type IN ('.implode(',', $unittypes).')';
        }

        //Keine Suchkriterien vorhanden
        if (!$where) {
            message($apx->lang->get('CORE_BACK'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //SUCHE AUSFÜHREN
        else {
            $data = $db->fetch('SELECT id FROM '.PRE.'_products_units WHERE '.$where);
            $resultIds = get_ids($data, 'id');

            //Keine Ergebnisse
            if (!$resultIds) {
                message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
                require 'lib/_end.php';
            }

            $searchid = saveSearchResult('products_manu', $resultIds);
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.str_replace('&amp;', '&', mklink(
                'manufacturers.php?action=search&searchid='.$searchid,
                'manufacturers.html?action=search&searchid='.$searchid
            )));
        }
    }
    require 'lib/_end.php';
}

////////////////////////////////////////////////////////////////////////////////////////// HERSTELLER-LISTE

$apx->lang->drop('manusearch');

//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('manufacturers_index');

$where = '';
if (!$_REQUEST['letter']) {
    $_REQUEST['letter'] = '0';
}

//Buchstaben-Liste
letters(mklink(
    'manufacturers.php?sortby='.$_REQUEST['sortby'],
    'manufacturers,{LETTER},1.html'.iif($_REQUEST['sortby'], '?sortby='.$_REQUEST['sortby'])
));
if ($_REQUEST['letter']) {
    if ('spchar' == $_REQUEST['letter']) {
        $where .= ' AND title NOT REGEXP("^[a-zA-Z]") ';
    } else {
        $where .= " AND title LIKE '".addslashes($_REQUEST['letter'])."%' ";
    }
}

//Seitenzahlen
list($count) = $db->first('SELECT count(id) FROM '.PRE.'_products_units WHERE 1 '.$where);
$pagelink = mklink(
    'manufacturers.php?sortby='.$_REQUEST['sortby'].'&amp;letter='.$_REQUEST['letter'],
    'manufacturers,'.$_REQUEST['letter'].',{P}.html'.iif($_REQUEST['sortby'], '?sortby='.$_REQUEST['sortby'])
);
pages($pagelink, $count, $set['products']['manu_epp']);

//Orderby
$orderdef[0] = 'title';
$orderdef['title'] = ['title', 'ASC'];

//Hersteller auslesen
$data = $db->fetch('SELECT * FROM '.PRE.'_products_units WHERE 1 '.$where.getorder($orderdef).getlimit($set['products']['manu_epp']));
if (count($data)) {
    foreach ($data as $res) {
        ++$i;

        //Link
        $link = mklink(
            'manufacturers.php?id='.$res['id'],
            'manufacturers,id'.$res['id'].urlformat($res['title']).'.html'
        );

        //Produktbild
        if (in_array('MANU.PICTURE', $parse) || in_array('MANU.PICTURE_POPUP', $parse) || in_array('MANU.PICTURE_POPUPPATH', $parse)) {
            list($picture, $picture_popup, $picture_popuppath) = products_pic($res['picture']);
        }

        //Produktbild
        if (in_array('MANU.PRODUCT_COUNT', $parse)) {
            list($prodcount) = $db->first('SELECT count(id) FROM '.PRE."_products WHERE active=1 AND manufacturer='".$res['id']."' OR publisher='".$res['id']."'");
        }

        //Text
        $text = $res['text'];
        if ($apx->is_module('glossar')) {
            $text = glossar_highlight($text);
        }

        //Standard-Platzhalter
        $tabledata[$i]['ID'] = $res['id'];
        $tabledata[$i]['TYPE'] = $res['type'];
        $tabledata[$i]['LINK'] = $link;
        $tabledata[$i]['TITLE'] = $res['title'];
        $tabledata[$i]['TEXT'] = $text;
        $tabledata[$i]['WEBSITE'] = $res['website'];
        $tabledata[$i]['PRODUCT_COUNT'] = $prodcount;
        $tabledata[$i]['PICTURE'] = $picture;
        $tabledata[$i]['PICTURE_POPUP'] = $picture_popup;
        $tabledata[$i]['PICTURE_POPUPPATH'] = $picture_popuppath;
        $tabledata[$i]['FULLNAME'] = $res['fullname'];
        $tabledata[$i]['ADDRESS'] = $res['address'];
        $tabledata[$i]['EMAIL'] = $res['email'];
        $tabledata[$i]['EMAIL_ENCRYPTED'] = cryptMail($res['email']);
        $tabledata[$i]['PHONE'] = $res['phone'];

        //Nur Firma
        if ('company' == $res['type']) {
            $tabledata[$i]['FOUNDER'] = $res['founder'];
            $tabledata[$i]['FOUNDING_YEAR'] = $res['founding_year'];
            $tabledata[$i]['FOUNDING_COUNTRY'] = $res['founding_country'];
            $tabledata[$i]['LEGALFORM'] = $res['legalform'];
            $tabledata[$i]['HEADQUATERS'] = $res['headquaters'];
            $tabledata[$i]['EXECUTIVE'] = $res['executive'];
            $tabledata[$i]['EMPLOYEES'] = $res['employees'];
            $tabledata[$i]['TURNOVER'] = $res['turnover'];
            $tabledata[$i]['SECTOR'] = $res['sector'];
            $tabledata[$i]['PRODUCTS'] = $res['products'];
        }
    }
}

//Sortieren nach...
ordervars(
    $orderdef,
    mklink(
        'manufacturers.php?letter='.$_REQUEST['letter'],
        'manufacturers,'.$_REQUEST['letter'].',1.html'
    )
);

$apx->tmpl->assign('SEARCH_POSTTO', mklink('manufacturers.php', 'manufacturers.html'));
$apx->tmpl->assign('MANU', $tabledata);
$apx->tmpl->parse('manufacturers_index');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
