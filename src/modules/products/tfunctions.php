<?php

// GAMES CLASS
// ===========

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

require_once BASEDIR.getmodulepath('products').'functions.php';

//Produkt-Informationen anzeigen
function products_info($prodid = 0, $template = 'information')
{
    global $set,$db,$apx,$user;
    static $cache;
    $prodid = (int) $prodid;
    $tmpl = new tengine();
    if (!$prodid) {
        return;
    }
    $apx->lang->drop('fields', 'products');

    //Informationen auslesen
    if (!isset($cache[$prodid])) {
        $cache[$prodid] = $db->first('SELECT * FROM '.PRE."_products WHERE id='".$prodid."' AND active='1' LIMIT 1");
    }
    $res = $cache[$prodid];
    if (!$res['id']) {
        return;
    }
    $systems = [];

    //Verwendete Variablen
    $parse = $tmpl->used_vars('functions/'.$template, 'products');

    //Link
    $link = mklink(
        'products.php?id='.$res['id'],
        'products,id'.$res['id'].urlformat($res['title']).'.html'
    );

    //Produktbild
    if (in_array('PICTURE', $parse) || in_array('PICTURE_POPUP', $parse) || in_array('PICTURE_POPUPPATH', $parse)) {
        list($picture, $picture_popup, $picture_popuppath) = products_pic($res['picture']);
    }

    //Teaserbild
    if (in_array('TEASERPIC', $parse) || in_array('TEASERPIC_POPUP', $parse) || in_array('TEASERPIC_POPUPPATH', $parse)) {
        list($teaserpic, $teaserpic_popup, $teaserpic_popuppath) = products_pic($res['teaserpic']);
    }

    //Text
    $text = '';
    if (in_array('TEXT', $parse)) {
        $text = mediamanager_inline($res['text']);
        if ($apx->is_module('glossar')) {
            $text = glossar_highlight($text);
        }
    }

    //Tags
    if (in_array('TAG', $parse) || in_array('TAG_IDS', $parse) || in_array('KEYWORDS', $parse)) {
        list($tagdata, $tagids, $keywords) = products_tags($res['id']);
    }

    //Standard-Platzhalter
    $tmpl->assign('ID', $res['id']);
    $tmpl->assign('TYPE', $res['type']);
    $tmpl->assign('LINK', $link);
    $tmpl->assign('TITLE', $res['title']);
    $tmpl->assign('TEXT', $text);
    $tmpl->assign('TIME', $res['addtime']);
    $tmpl->assign('WEBSITE', $res['website']);
    $tmpl->assign('BUYLINK', $res['buylink']);
    $tmpl->assign('PRICE', $res['price']);
    $tmpl->assign('HITS', $res['hits']);
    $tmpl->assign('PICTURE', $picture);
    $tmpl->assign('PICTURE_POPUP', $picture_popup);
    $tmpl->assign('PICTURE_POPUPPATH', $picture_popuppath);
    $tmpl->assign('TEASERPIC', $teaserpic);
    $tmpl->assign('TEASERPIC_POPUP', $teaserpic_popup);
    $tmpl->assign('TEASERPIC_POPUPPATH', $teaserpic_popuppath);
    $tmpl->assign('PRODUCT_ID', $res['prodid']);
    $tmpl->assign('RECOMMENDED_PRICE', $res['recprice']);
    $tmpl->assign('GUARANTEE', $res['guarantee']);

    //Sammlung
    if ($user->info['userid']) {
        if (!products_in_coll($res['id'])) {
            $tmpl->assign('LINK_COLLECTION_ADD', mklink(
                'products.php?id='.$res['id'].'&amp;addcoll=1',
                'products,id'.$res['id'].urlformat($res['title']).'.html?addcoll=1'
            ));
        } else {
            $tmpl->assign('LINK_COLLECTION_REMOVE', mklink(
                'products.php?id='.$res['id'].'&amp;removecoll=1',
                'products,id'.$res['id'].urlformat($res['title']).'.html?removecoll=1'
            ));
        }
    }

    //Tags
    $tmpl->assign('TAG_IDS', $tagids);
    $tmpl->assign('TAG', $tagdata);
    $tmpl->assign('KEYWORDS', $keywords);

    //Units auslesen
    $units = [$res['manufacturer'], $res['publisher']];
    $unitinfo = $db->fetch_index('SELECT id,title,website FROM '.PRE.'_products_units WHERE id IN ('.implode(',', $units).')', 'id');

    //Gruppen auslesen
    $groups = array_merge([$res['genre']], dash_unserialize($res['media']));
    $groupinfo = [];
    if ('game' == $res['type']) {
        $groupinfo = $db->fetch_index('SELECT id,title,icon FROM '.PRE.'_products_groups WHERE id IN ('.implode(',', $groups).") OR grouptype='system'", 'id');
    } elseif ('movie' == $res['type']) {
        $groupinfo = $db->fetch_index('SELECT id,title,icon FROM '.PRE.'_products_groups WHERE id IN ('.implode(',', $groups).") OR grouptype='media'", 'id');
    } else {
        $groupinfo = $db->fetch_index('SELECT id,title,icon FROM '.PRE.'_products_groups WHERE id IN ('.implode(',', $groups).')', 'id');
    }

    //NORMAL
    if ('normal' == $res['type']) {
        $manulink = mklink(
            'manufacturers.php?id='.$res['manufacturer'],
            'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
        );

        $tmpl->assign('MANUFACTURER', $unitinfo[$res['manufacturer']]['title']);
        $tmpl->assign('MANUFACTURER_WEBSITE', $unitinfo[$res['manufacturer']]['website']);
        $tmpl->assign('MANUFACTURER_LINK', $manulink);
    }

    //VIDEOSPIEL
    elseif ('game' == $res['type']) {
        //System-Liste
        $systems = dash_unserialize($res['systems']);
        if (!is_array($systems)) {
            $systems = [];
        }
        $systemdata = [];
        foreach ($systems as $sysid) {
            ++$i;
            $systemdata[$i]['TITLE'] = $groupinfo[$sysid]['title'];
            $systemdata[$i]['ICON'] = $groupinfo[$sysid]['icon'];
        }

        //Media-Liste
        $media = dash_unserialize($res['media']);
        if (!is_array($media)) {
            $media = [];
        }
        $mediadata = [];
        foreach ($media as $medid) {
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

        $tmpl->assign('DEVELOPER', $unitinfo[$res['manufacturer']]['title']);
        $tmpl->assign('DEVELOPER_WEBSITE', $unitinfo[$res['manufacturer']]['website']);
        $tmpl->assign('DEVELOPER_LINK', $manulink);
        $tmpl->assign('PUBLISHER', $unitinfo[$res['publisher']]['title']);
        $tmpl->assign('PUBLISHER_WEBSITE', $unitinfo[$res['publisher']]['website']);
        $tmpl->assign('PUBLISHER_LINK', $publink);
        $tmpl->assign('USK', $res['sk']);
        $tmpl->assign('GENRE', $groupinfo[$res['genre']]['title']);
        $tmpl->assign('MEDIA', $mediadata);
        $tmpl->assign('SYSTEM', $systemdata);
        $tmpl->assign('REQUIREMENTS', $res['requirements']);
    }

    //HARDWARE
    elseif ('hardware' == $res['type']) {
        $manulink = mklink(
            'manufacturers.php?id='.$res['manufacturer'],
            'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
        );

        $tmpl->assign('MANUFACTURER', $unitinfo[$res['manufacturer']]['title']);
        $tmpl->assign('MANUFACTURER_WEBSITE', $unitinfo[$res['manufacturer']]['website']);
        $tmpl->assign('MANUFACTURER_LINK', $manulink);
        $tmpl->assign('EQUIPMENT', $res['equipment']);
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
            ++$i;
            $mediadata[$i]['TITLE'] = $groupinfo[$medid]['title'];
            $mediadata[$i]['ICON'] = $groupinfo[$medid]['icon'];
        }

        $manulink = mklink(
            'manufacturers.php?id='.$res['manufacturer'],
            'manufacturers,id'.$res['manufacturer'].urlformat($unitinfo[$res['manufacturer']]['title']).'.html'
        );

        $tmpl->assign('MANUFACTURER', $unitinfo[$res['manufacturer']]['title']);
        $tmpl->assign('MANUFACTURER_WEBSITE', $unitinfo[$res['manufacturer']]['website']);
        $tmpl->assign('MANUFACTURER_LINK', $manulink);
        $tmpl->assign('OS', $res['os']);
        $tmpl->assign('LANGUAGES', $res['languages']);
        $tmpl->assign('REQUIREMENTS', $res['requirements']);
        $tmpl->assign('LICENSE', $res['license']);
        $tmpl->assign('VERSION', $res['version']);
        $tmpl->assign('MEDIA', $mediadata);
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

        $tmpl->assign('ARTIST', $unitinfo[$res['manufacturer']]['title']);
        $tmpl->assign('ARTIST_WEBSITE', $unitinfo[$res['manufacturer']]['website']);
        $tmpl->assign('ARTIST_LINK', $manulink);
        $tmpl->assign('LABEL', $unitinfo[$res['publisher']]['title']);
        $tmpl->assign('LABEL_WEBSITE', $unitinfo[$res['publisher']]['website']);
        $tmpl->assign('LABEL_LINK', $publink);
        $tmpl->assign('FSK', $res['sk']);
        $tmpl->assign('GENRE', $groupinfo[$res['genre']]['title']);
        $tmpl->assign('MEDIA', $mediadata);
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
            ++$i;
            $mediadata[$i]['TITLE'] = $groupinfo[$medid]['title'];
            $mediadata[$i]['ICON'] = $groupinfo[$medid]['icon'];
        }

        $publink = mklink(
            'manufacturers.php?id='.$res['publisher'],
            'manufacturers,id'.$res['publisher'].urlformat($unitinfo[$res['publisher']]['title']).'.html'
        );

        $tmpl->assign('STUDIO', $unitinfo[$res['publisher']]['title']);
        $tmpl->assign('STUDIO_WEBSITE', $unitinfo[$res['publisher']]['website']);
        $tmpl->assign('STUDIO_LINK', $publink);
        $tmpl->assign('REGISSEUR', $res['regisseur']);
        $tmpl->assign('ACTORS', $res['actors']);
        $tmpl->assign('LENGTH', $res['length']);
        $tmpl->assign('FSK', $res['sk']);
        $tmpl->assign('GENRE', $groupinfo[$res['genre']]['title']);
        $tmpl->assign('MEDIA', $mediadata);
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

        $tmpl->assign('AUTHOR', $unitinfo[$res['manufacturer']]['title']);
        $tmpl->assign('AUTHOR_WEBSITE', $unitinfo[$res['manufacturer']]['website']);
        $tmpl->assign('AUTHOR_LINK', $manulink);
        $tmpl->assign('PUBLISHER', $unitinfo[$res['publisher']]['title']);
        $tmpl->assign('PUBLISHER_WEBSITE', $unitinfo[$res['publisher']]['website']);
        $tmpl->assign('PUBLISHER_LINK', $publink);
        $tmpl->assign('GENRE', $groupinfo[$res['genre']]['title']);
        $tmpl->assign('MEDIA', $mediadata);
        $tmpl->assign('ISBN', $res['isbn']);
    }

    //Benutzerdefinierte Felder
    for ($i = 1; $i <= 10; ++$i) {
        $tmpl->assign('CUSTOM'.$i.'_NAME', replace($set['products']['custom_'.$res['type']][($i - 1)]));
        $tmpl->assign('CUSTOM'.$i, $res['custom'.$i]);
    }

    //Veröffentlichung
    if (in_array('RELEASE', $parse)) {
        $releasedata = [];
        $pubdata = $db->fetch('SELECT system,data FROM '.PRE."_products_releases WHERE prodid='".$res['id']."' ORDER BY stamp ASC");
        if (count($pubdata)) {
            foreach ($pubdata as $pubres) {
                ++$i;
                $info = unserialize($pubres['data']);
                $releasedate = products_format_release($info);
                $releasedata[$i]['DATE'] = $releasedate;
                if ('game' == $res['type']) {
                    $releasedata[$i]['SYSTEM'] = $groupinfo[$pubres['system']]['title'];
                    $releasedata[$i]['SYSTEM_ICON'] = $groupinfo[$pubres['system']]['icon'];
                } elseif ($pubres['system']) {
                    $releasedata[$i]['MEDIA'] = $groupinfo[$pubres['system']]['title'];
                    $releasedata[$i]['MEDIA_ICON'] = $groupinfo[$pubres['system']]['icon'];
                }
            }
        }
        $tmpl->assign('RELEASE', $releasedata);
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

        $tmpl->assign('COMMENT_COUNT', $coms->count());
        $tmpl->assign('COMMENT_LINK', $coms->link($link));
        $tmpl->assign('DISPLAY_COMMENTS', 1);
        if (in_template(['COMMENT_LAST_USERID', 'COMMENT_LAST_NAME', 'COMMENT_LAST_TIME'], $parse)) {
            $tmpl->assign('COMMENT_LAST_USERID', $coms->last_userid());
            $tmpl->assign('COMMENT_LAST_NAME', $coms->last_name());
            $tmpl->assign('COMMENT_LAST_TIME', $coms->last_time());
        }
    }

    //Bewertungen
    if ($apx->is_module('ratings') && $set['products']['ratings'] && $res['allowrating']) {
        require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
        $rate = new ratings('products', $res['id']);
        $rate->assign_ratings($parse, $tmpl);
    }

    $tmpl->parse('functions/'.$template, 'products');
}

//Neuste Produkte
function products_last($count = 5, $start = 0, $type = '', $template = 'last')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $alltypes = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
    if (!in_array($type, $alltypes)) {
        $type = '';
    }

    //Neuste Produkte auslesen
    $data = $db->fetch('SELECT * FROM '.PRE."_products WHERE active='1' ".iif($type, " AND type='".$type."'").' ORDER BY addtime DESC LIMIT '.$start.','.$count);
    products_print($data, 'functions/'.$template);
}

//Top-Produkte
function products_top($count = 5, $start = 0, $type = '', $template = 'top')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $alltypes = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
    if (!in_array($type, $alltypes)) {
        $type = '';
    }

    //Neuste Produkte auslesen
    $data = $db->fetch('SELECT * FROM '.PRE."_products WHERE active='1' AND top=1 ".iif($type, " AND type='".$type."'").' ORDER BY addtime DESC LIMIT '.$start.','.$count);
    products_print($data, 'functions/'.$template);
}

//Nicht-Top-Produkte
function products_nottop($count = 5, $start = 0, $type = '', $template = 'nottop')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $alltypes = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
    if (!in_array($type, $alltypes)) {
        $type = '';
    }

    //Neuste Produkte auslesen
    $data = $db->fetch('SELECT * FROM '.PRE."_products WHERE active='1' AND top=0 ".iif($type, " AND type='".$type."'").' ORDER BY addtime DESC LIMIT '.$start.','.$count);
    products_print($data, 'functions/'.$template);
}

//Bevorstehende Veröffentlichungen
function products_releases($count = 5, $start = 0, $type = '', $template = 'releases')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $alltypes = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
    if (!in_array($type, $alltypes)) {
        $type = '';
    }

    //Releases auslesen
    $data = $db->fetch('SELECT DISTINCT a.stamp AS releasestamp,b.* FROM '.PRE.'_products_releases AS a LEFT JOIN '.PRE."_products AS b ON a.prodid=b.id WHERE b.active='1' AND a.stamp>='".date('Ymd', time() - TIMEDIFF)."' ".iif($type, " AND type='".$type."'").' ORDER BY a.stamp ASC LIMIT '.$start.','.$count);
    products_print($data, 'functions/'.$template);
}

//Ähnliche Produkte
function products_similar($tagids = [], $count = 5, $start = 0, $type = '', $template = 'similar')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;

    if (!is_array($tagids)) {
        $tagids = getTagIds(strval($tagids));
    }
    $ids = products_search_tags($tagids);
    $ids[] = -1;
    $tagfilter = ' AND id IN ('.implode(', ', $ids).') ';

    $alltypes = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
    if (!in_array($type, $alltypes)) {
        $type = '';
    }

    //Releases auslesen
    $data = $db->fetch('SELECT * FROM '.PRE."_products WHERE active='1' ".iif($type, " AND type='".$type."'").' '.$tagfilter.' ORDER BY addtime DESC LIMIT '.$start.','.$count);
    products_print($data, 'functions/'.$template);
}

//Zufällige Produkte
function products_random($count = 5, $start = 0, $type = '', $template = 'random')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $alltypes = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
    if (!in_array($type, $alltypes)) {
        $type = '';
    }

    //Neuste Produkte auslesen
    $data = $db->fetch('SELECT * FROM '.PRE."_products WHERE active='1' ".iif($type, " AND type='".$type."'").' ORDER BY RAND() LIMIT '.$start.','.$count);
    products_print($data, 'functions/'.$template);
}

//Beste Produkte: Klicks
function products_best_hits($count = 5, $start = 0, $type = '', $template = 'best_hits')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $alltypes = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
    if (!in_array($type, $alltypes)) {
        $type = '';
    }

    //Beste Produkte auslesen
    $data = $db->fetch('SELECT * FROM '.PRE."_products WHERE active='1' ".iif($type, " AND type='".$type."'").' ORDER BY hits DESC LIMIT '.$start.','.$count);
    products_print($data, 'functions/'.$template);
}

//Beste Produkte: Bewertung
function products_best_rating($count = 5, $start = 0, $type = '', $template = 'best_rating')
{
    global $set,$db,$apx,$user;
    if (!$apx->is_module('ratings')) {
        return '';
    }
    $count = (int) $count;
    $start = (int) $start;
    $alltypes = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
    if (!in_array($type, $alltypes)) {
        $type = '';
    }

    //Beste Produkte auslesen
    $data = $db->fetch('SELECT avg(rating) AS rating,count(*) AS votes,b.* FROM '.PRE.'_ratings AS a LEFT JOIN '.PRE."_products AS b ON a.mid=b.id AND a.module='products' WHERE b.active='1' ".iif($type, " AND b.type='".$type."'").' GROUP BY a.mid ORDER BY rating DESC,votes DESC LIMIT '.iif($start, $start.',').$count);
    products_print($data, 'functions/'.$template);
}

//Produkte von einem Hersteller
function products_related($prodid = 0, $count = 5, $start = 0, $type = '', $template = 'related')
{
    global $set,$db,$apx,$user;
    $prodid = (int) $prodid;
    $count = (int) $count;
    $start = (int) $start;
    if (!$prodid) {
        return;
    }
    $alltypes = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
    if (!in_array($type, $alltypes)) {
        $type = '';
    }

    //Neuste Produkte auslesen
    $data = $db->fetch('SELECT * FROM '.PRE."_products WHERE active='1' AND prodid='".$prodid."' ".iif($type, " AND type='".$type."'").' ORDER BY title ASC LIMIT '.$start.','.$count);
    products_print($data, 'functions/'.$template);
}

//Zufällige Produkte
function products_collection($userid = 0, $count = 5, $start = 0, $type = '', $template = 'collection')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $userid = (int) $userid;
    if (!$userid) {
        return;
    }
    $alltypes = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
    if (!in_array($type, $alltypes)) {
        $type = '';
    }

    //Produkte auslesen
    $data = $db->fetch('SELECT a.* FROM '.PRE.'_products_coll AS pc JOIN '.PRE."_products AS a ON pc.prodid=a.id WHERE pc.userid='".$userid."' AND active='1' ".iif($type, " AND type='".$type."'").' LIMIT '.$start.','.$count);
    products_print($data, 'functions/'.$template);
}

//Produkte von einem Hersteller
function products_form_manufacturer($manuid = 0, $count = 5, $start = 0, $type = '', $template = 'manufacturer_products')
{
    global $set,$db,$apx,$user;
    $manuid = (int) $manuid;
    $count = (int) $count;
    $start = (int) $start;
    if (!$manuid) {
        return;
    }
    $alltypes = ['normal', 'game', 'software', 'hardware', 'music', 'movie', 'book'];
    if (!in_array($type, $alltypes)) {
        $type = '';
    }

    //Neuste Produkte auslesen
    $data = $db->fetch('SELECT * FROM '.PRE."_products AS a WHERE active='1' AND ( manufacturer='".$manuid."' OR publisher='".$manuid."' ) ".iif($type, " AND type='".$type."'").' ORDER BY a.title ASC LIMIT '.$start.','.$count);
    products_print($data, 'functions/'.$template);
}

//Produkte ausgeben
function products_print($data, $template)
{
    global $set,$db,$apx,$user;
    $tmpl = new tengine();
    $apx->lang->drop('fields', 'products');

    //Verwendet Variablen auslesen
    $parse = $apx->tmpl->used_vars($template, 'products');

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
        /*if ( in_template(array('PRODUCT.MEDIA'),$parse) ) $groups = array_merge($groups,get_ids($data,'media'));
        if ( in_array('PRODUCT.GENRE',$parse) ) $groups = array_merge($groups,get_ids($data,'genre'));
        if ( in_array('game',$types) && in_template(array('PRODUCT.RELEASE.SYSTEM','PRODUCT.RELEASE.SYSTEM_ICON','PRODUCT.SYSTEM'),$parse) ) {
            if ( count($groups)==0 ) $groups = array(0);
            $groupinfo = $db->fetch_index("SELECT id,title,icon FROM ".PRE."_products_groups WHERE id IN (".implode(',',$groups).") OR grouptype='system'",'id');
        }
        elseif ( in_array('movie',$types) && in_template(array('PRODUCT.RELEASE.MEDIA','PRODUCT.RELEASE.MEDIA_ICON','PRODUCT.MEDIA'),$parse) ) {
            if ( count($groups)==0 ) $groups = array(0);
            $groupinfo = $db->fetch_index("SELECT id,title,icon FROM ".PRE."_products_groups WHERE id IN (".implode(',',$groups).") OR grouptype='media'",'id');
        }
        elseif ( count($groups) ) {
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
                        'SYSTEM' => $groupinfo[$relres['system']]['title'],
                        'SYSTEM_ICON' => $groupinfo[$relres['system']]['icon'],
                        'MEDIA' => $groupinfo[$relres['system']]['title'],
                        'MEDIA_ICON' => $groupinfo[$relres['system']]['icon'],
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
                $text = mediamanager_inline($res['text']);
                if ($apx->is_module('glossar')) {
                    $text = glossar_highlight($text);
                }
            }

            //Datehead
            if ($laststamp != date('Y/m/d', $res['addtime'] - TIMEDIFF)) {
                $tabledata[$i]['DATEHEAD'] = $res['addtime'];
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
            $tabledata[$i]['TIME'] = $res['addtime'];
            $tabledata[$i]['WEBSITE'] = $res['website'];
            $tabledata[$i]['BUYLINK'] = $res['buylink'];
            $tabledata[$i]['PRICE'] = $res['price'];
            $tabledata[$i]['HITS'] = $res['hits'];
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
                    $systems = dash_unserialize($res['systems']);
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
                if ($res['releasestamp']) {
                    $temprel = $releaseinfo[$res['id']];
                    foreach ($temprel as $rel) {
                        if ($rel['stamp'] != $res['releasestamp']) {
                            continue;
                        }
                        ++$ii;
                        $tabledata[$i]['RELEASE'][$ii] = $rel;
                    }
                } else {
                    $tabledata[$i]['RELEASE'] = $releaseinfo[$res['id']];
                }
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

            $laststamp = date('Y/m/d', $res['addtime'] - TIMEDIFF);
        }
    }

    $tmpl->assign('PRODUCT', $tabledata);
    $tmpl->parse($template, 'products');
}

//Tags auflisten
function products_tagcloud($count = 10, $type = '', $random = false, $template = 'tagcloud')
{
    global $set,$db,$apx,$user;
    $tmpl = new tengine();
    $catid = (int) $catid;

    if ($random) {
        $orderby = 'RAND()';
    } else {
        $orderby = 'weight DESC';
    }

    //Sektion gewählt
    $data = $db->fetch('
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM '.PRE.'_products_tags AS nt
		LEFT JOIN '.PRE.'_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN '.PRE.'_products_tags AS nt2 ON nt.tagid=nt2.tagid
		GROUP BY nt.tagid
		ORDER BY '.$orderby.'
		LIMIT '.$count.'
	');

    if (is_array($data) && count($data)) {
        $maxweight = 1;
        foreach ($data as $res) {
            if ($res['weight'] > $maxweight) {
                $maxweight = $res['weight'];
            }
        }
        if (!$random) {
            shuffle($data);
        }
        foreach ($data as $res) {
            $tagdata[] = [
                'ID' => $res['tagid'],
                'NAME' => replace($res['tag']),
                'WEIGHT' => $res['weight'] / $maxweight,
            ];
        }
    }

    $tmpl->assign('TAG', $tagdata);
    $tmpl->parse('functions/'.$template, 'products');
}

//Statistik anzeigen
function products_stats($template = 'stats')
{
    global $set,$db,$apx,$user;
    $tmpl = new tengine();
    $parse = $tmpl->used_vars('functions/'.$template, 'products');

    $apx->lang->drop('func_stats', 'products');

    if (in_template(['COUNT_PRODUCTS', 'AVG_HITS'], $parse)) {
        list($count, $hits) = $db->first('
			SELECT count(id), avg(hits) FROM '.PRE.'_products
			WHERE active=1
		');
        $tmpl->assign('COUNT_PRODUCTS', $count);
        $tmpl->assign('AVG_HITS', round($hits));
    }

    $types = ['normal', 'game', 'music', 'movie', 'book', 'software', 'hardware'];
    foreach ($types as $type) {
        $varname = 'COUNT_PRODUCTS_'.strtoupper($type);
        if (in_array($varname, $parse)) {
            list($count) = $db->first('
				SELECT count(id) FROM '.PRE."_products
				WHERE active=1 AND type='".$type."'
			");
            $tmpl->assign($varname, $count);
        }
    }

    $tmpl->parse('functions/'.$template, 'products');
}

//Neuste Produkte
function products_collection_user($prodid = 0, $count = 999999, $template = 'collectionuser')
{
    global $set,$apx,$db,$user;
    require_once BASEDIR.getmodulepath('user').'tfunctions.php';

    $prodid = (int) $prodid;
    if (!$prodid) {
        return;
    }
    $count = (int) $count;
    if ($count < 1) {
        $count = 1;
    }
    $apx->lang->drop('func_newuser', 'user');

    $data = $db->fetch('SELECT a.userid,a.username,a.email,a.groupid,a.reg_time,a.realname,a.gender,a.city,a.plz,a.country,a.city,a.lastactive,a.avatar,a.avatar_title,a.birthday,a.pub_hidemail,a.custom1,a.custom2,a.custom3,a.custom4,a.custom5,a.custom6,a.custom7,a.custom8,a.custom9,a.custom10 FROM '.PRE.'_products_coll AS c LEFT JOIN '.PRE."_user AS a USING(userid) WHERE c.prodid='".$prodid."' AND a.reg_key='' ORDER BY a.username ASC LIMIT ".$count);
    user_print($data, 'functions/'.$template, 'USER', false, 'products');
}
