<?php

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_start.php';  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once BASEDIR.getmodulepath('links').'functions.php';

$apx->module('links');
$apx->lang->drop('global');

headline($apx->lang->get('HEADLINE'), mklink('links.php', 'links.html'));
titlebar($apx->lang->get('HEADLINE'));
$_REQUEST['catid'] = (int) $_REQUEST['catid'];
$_REQUEST['id'] = (int) $_REQUEST['id'];

////////////////////////////////////////////////////////////////////////////////// DEFEKTER LINK

if ($_REQUEST['id'] && $_REQUEST['broken']) {
    $apx->lang->drop('broken');

    if ($_POST['broken']) {
        $res = $db->first('SELECT title FROM '.PRE."_links WHERE ( id='".$_REQUEST['id']."' ".section_filter().' ) LIMIT 1');
        titlebar($apx->lang->get('HEADLINE').': '.$res['title']);

        $link = mklink(
            'links.php?id='.$_REQUEST['id'],
            'links,id'.$_REQUEST['id'].urlformat($res['title']).'.html'
        );

        $db->query('UPDATE '.PRE."_links SET broken='".time()."' WHERE ( id='".$_REQUEST['id']."' ".section_filter().' ) LIMIT 1');

        //eMail-Benachrichtigung
        if ($set['links']['mailonbroken']) {
            $input = ['URL' => substr(HTTP, 0, -1).$link];
            sendmail($set['links']['mailonbroken'], 'BROKEN', $input);
        }

        message($apx->lang->get('MSG_BROKEN'), $link);
        require 'lib/_end.php';
    } else {
        tmessage('broken', ['ID' => $_REQUEST['id']]);
    }
}

////////////////////////////////////////////////////////////////////////////////// NUR KOMMENTARE

if ($_REQUEST['id'] && $_REQUEST['comments']) {
    $res = $db->first('SELECT title FROM '.PRE."_links WHERE ( id='".$_REQUEST['id']."' ".section_filter().' ) LIMIT 1');
    titlebar($apx->lang->get('HEADLINE').': '.$res['title']);

    links_showcomments($_REQUEST['id']);
}

///////////////////////////////////////////////////////////////////////////////////////// DETAILS
if ($_REQUEST['id']) {
    $apx->lang->drop('detail');

    //Verwendete Variablen auslesen
    $parse = $apx->tmpl->used_vars('detail');

    //Link-Info
    $res = $db->first('SELECT a.*,b.username,b.email,b.pub_hidemail FROM '.PRE.'_links AS a LEFT JOIN '.PRE."_user AS b USING(userid) WHERE ( a.id='".$_REQUEST['id']."' ".iif(!$user->is_team_member(), "AND ( '".time()."' BETWEEN a.starttime AND a.endtime )").' '.section_filter().' ) LIMIT 1');
    if (!$res['id']) {
        filenotfound();
    }

    //Altersabfrage
    if ($res['restricted']) {
        checkage();
    }

    //Headline
    titlebar($apx->lang->get('HEADLINE').': '.$res['title']);

    //Kategorie-Info
    if (in_array('CATTITLE', $parse) || in_array('CATTEXT', $parse) || in_array('CATICON', $parse) || in_array('CATLINK', $parse) || in_array('CATCOUNT', $parse)) {
        //Tree-Manager
        require_once BASEDIR.'lib/class.recursivetree.php';
        $tree = new RecursiveTree(PRE.'_links_cat', 'id');

        $catinfo = $tree->getNode($res['catid'], ['*']);
    }

    //Links in der Kategorien
    $catcount = 0;
    if (in_array('CATCOUNT', $parse)) {
        $wholetree = array_merge([$res['catid']], $catinfo['children']);
        list($catcount) = $db->first('SELECT count(id) FROM '.PRE.'_links WHERE ( catid IN ('.implode(',', $wholetree).") AND ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter().' )');
    }

    //Goto-Link
    $gotolink = 'misc.php?action=gotolink&amp;id='.$res['id'].iif($apx->section_id(), '&amp;sec='.$apx->section_id());

    //Neu?
    if (($res['addtime'] + ($set['links']['new'] * 24 * 3600)) >= time()) {
        $new = 1;
    } else {
        $new = 0;
    }

    //Linkpic
    if (in_array('PICTURE', $parse) || in_array('PICTURE_POPUP', $parse) || in_array('PICTURE_POPUPPATH', $parse)) {
        list($picture, $picture_popup, $picture_popuppath) = links_linkpic($res['linkpic']);
    }

    //Username + eMail
    if ($res['userid']) {
        $author = $res['username'];
        $author_email = iif(!$res['pub_hidemail'], $res['email']);
    } else {
        $author = $res['send_username'];
        $author_email = $res['send_email'];
    }

    //Broken
    $blink = mklink(
        'links.php?id='.$res['id'].'&amp;broken=1',
        'links,id'.$res['id'].urlformat($res['title']).'.html?broken=1'
    );

    //Tags
    if (in_array('TAG', $parse) || in_array('TAG_IDS', $parse) || in_array('KEYWORDS', $parse)) {
        list($tagdata, $tagids, $keywords) = links_tags($res['id']);
    }

    //Text
    $text = mediamanager_inline($res['text']);
    if ($apx->is_module('glossar')) {
        $text = glossar_highlight($text);
    }

    $apx->tmpl->assign('ID', $res['id']);
    $apx->tmpl->assign('TITLE', $res['title']);
    $apx->tmpl->assign('URL', $res['url']);
    $apx->tmpl->assign('TEXT', $text);
    $apx->tmpl->assign_static('META_DESCRIPTION', replace($res['meta_description']));
    $apx->tmpl->assign('GOTOLINK', $gotolink);
    $apx->tmpl->assign('PICTURE', $picture);
    $apx->tmpl->assign('PICTURE_POPUP', $picture_popup);
    $apx->tmpl->assign('PICTURE_POPUPPATH', $picture_popuppath);
    $apx->tmpl->assign('HITS', number_format($res['hits'], 0, '', '.'));
    $apx->tmpl->assign('TIME', $res['starttime']);
    $apx->tmpl->assign('TOP', $res['top']);
    $apx->tmpl->assign('RESTRICTED', $res['restricted']);
    $apx->tmpl->assign('NEW', $new);
    $apx->tmpl->assign('BROKEN', $blink);

    //Tags
    $apx->tmpl->assign('TAG_IDS', $tagids);
    $apx->tmpl->assign('TAG', $tagdata);
    $apx->tmpl->assign('KEYWORDS', $keywords);

    //Autor
    $apx->tmpl->assign('USERID', $res['userid']);
    $apx->tmpl->assign('USERNAME', replace($author));
    $apx->tmpl->assign('EMAIL', replace($author_email));
    $apx->tmpl->assign('EMAIL_ENCRYPTED', replace(cryptMail($author_email)));

    //Kategorie
    $apx->tmpl->assign('CATID', $res['catid']);
    $apx->tmpl->assign('CATTITLE', $catinfo['title']);
    $apx->tmpl->assign('CATTEXT', $catinfo['text']);
    $apx->tmpl->assign('CATICON', $catinfo['icon']);
    $apx->tmpl->assign('CATCOUNT', $catcount);
    $apx->tmpl->assign('CATLINK', $catinfo['link']);

    //Pfad
    if (in_array('PATH', $parse)) {
        $apx->tmpl->assign('PATH', links_path($res['catid']));
    }

    //Galerie
    if ($apx->is_module('gallery') && $res['galid']) {
        $galinfo = gallery_info($res['galid']);
        $gallink = mklink(
            'gallery.php?id='.$galinfo['id'],
            'gallery,list'.$galinfo['id'].',1'.urlformat($galinfo['title']).'.html'
        );
        $apx->tmpl->assign('GALLERY_ID', $galinfo['id']);
        $apx->tmpl->assign('GALLERY_TITLE', $galinfo['title']);
        $apx->tmpl->assign('GALLERY_LINK', $gallink);
    }

    //Kommentare
    if ($apx->is_module('comments') && $set['links']['coms'] && $res['allowcoms']) {
        require_once BASEDIR.getmodulepath('comments').'class.comments.php';
        $coms = new comments('links', $res['id']);
        $coms->assign_comments($parse);
    }

    //Bewertungen
    if ($apx->is_module('ratings') && $set['links']['ratings'] && $res['allowrating']) {
        require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
        $rate = new ratings('links', $res['id']);
        $rate->assign_ratings($parse);
    }

    $apx->tmpl->parse('detail');
    require 'lib/_end.php';
}

//////////////////////////////////////////////////////////////////////////////////////////////////////// SUCHE

if ('search' == $_REQUEST['action']) {
    $apx->lang->drop('list');
    $apx->lang->drop('search');

    //ERGEBNIS ANZEIGEN
    if ($_REQUEST['searchid']) {
        titlebar($apx->lang->get('HEADLINE_SEARCH'));

        //Suchergebnis auslesen
        $resultIds = '';
        list($resultIds) = getSearchResult('links', $_REQUEST['searchid']);

        //Keine Ergebnisse
        if (!$resultIds) {
            message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //SUCHE AUSFÜHREN
        $parse = $apx->tmpl->used_vars('search_result');

        //Seitenzahlen
        list($count) = $db->first('SELECT count(id) FROM '.PRE."_links WHERE '".time()."' BETWEEN starttime AND endtime AND id IN (".implode(', ', $resultIds).') '.section_filter());
        pages(
            mklink(
                'links.php?searchid='.$_REQUEST['searchid'],
                'links.html?searchid='.$_REQUEST['searchid']
            ),
            $count,
            $set['links']['searchepp']
        );

        //Keine Ergebnisse
        if (!$count) {
            message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //Sortierung
        if (2 == $set['links']['sortby']) {
            $orderby = ' ORDER BY starttime DESC ';
        } else {
            $orderby = ' ORDER BY title ASC ';
        }
        $data = $db->fetch('SELECT a.*,b.username,b.email,b.pub_hidemail FROM '.PRE.'_links AS a LEFT JOIN '.PRE."_user AS b USING(userid) WHERE '".time()."' BETWEEN starttime AND endtime AND id IN (".implode(', ', $resultIds).') '.section_filter().' '.getorder($orderdef).getlimit($set['links']['searchepp']));
        $catids = get_ids($data, 'catid');

        //Kategorien auslesen, falls notwendig
        $catinfo = [];
        if (count($catids) && in_template(['LINK.CATTITLE', 'LINK.CATTEXT', 'LINK.CATICON'], $parse)) {
            $catinfo = links_catinfo($catids);
        }

        if (count($data)) {
            foreach ($data as $res) {
                ++$i;

                //Dateillink
                $link = mklink(
                    'links.php?id='.$res['id'],
                    'links,id'.$res['id'].urlformat($res['title']).'.html'
                );

                //Neu?
                if (($res['addtime'] + ($set['links']['new'] * 24 * 3600)) >= time()) {
                    $new = 1;
                } else {
                    $new = 0;
                }

                //Goto-Link
                $gotolink = 'misc.php?action=gotolink&amp;id='.$res['id'].iif($apx->section_id(), '&amp;sec='.$apx->section_id());

                //Linkpic
                if (in_array('LINK.PICTURE', $parse) || in_array('LINK.PICTURE_POPUP', $parse) || in_array('LINK.PICTURE_POPUPPATH', $parse)) {
                    list($picture, $picture_popup, $picture_popuppath) = links_linkpic($res['linkpic']);
                }

                //Username + eMail
                if ($res['userid']) {
                    $author = $res['username'];
                    $author_email = iif(!$res['pub_hidemail'], $res['email']);
                } else {
                    $author = $res['send_username'];
                    $author_email = $res['send_email'];
                }
                //Text
                $text = '';
                if (in_array('LINK.TEXT', $parse)) {
                    $text = mediamanager_inline($res['text']);
                    if ($apx->is_module('glossar')) {
                        $text = glossar_highlight($text);
                    }
                }

                //Tags
                if (in_array('LINK.TAG', $parse) || in_array('LINK.TAG_IDS', $parse) || in_array('LINK.KEYWORDS', $parse)) {
                    list($tagdata, $tagids, $keywords) = links_tags($res['id']);
                }

                $tabledata[$i]['ID'] = $res['id'];
                $tabledata[$i]['TITLE'] = $res['title'];
                $tabledata[$i]['URL'] = $res['url'];
                $tabledata[$i]['TEXT'] = $text;
                $tabledata[$i]['LINK'] = $link;
                $tabledata[$i]['PICTURE'] = $picture;
                $tabledata[$i]['PICTURE_POPUP'] = $picture_popup;
                $tabledata[$i]['PICTURE_POPUPPATH'] = $picture_popuppath;
                $tabledata[$i]['HITS'] = number_format($res['hits'], 0, '', '.');
                $tabledata[$i]['TIME'] = $res['starttime'];
                $tabledata[$i]['TOP'] = $res['top'];
                $tabledata[$i]['RESTRICTED'] = $res['restricted'];
                $tabledata[$i]['NEW'] = $new;
                $tabledata[$i]['GOTO'] = $gotolink;

                //Tags
                $tabledata[$i]['TAG'] = $tagdata;
                $tabledata[$i]['TAG_IDS'] = $tagids;
                $tabledata[$i]['KEYWORDS'] = $keywords;

                //Kategorie
                $tabledata[$i]['CATID'] = $res['catid'];
                $tabledata[$i]['CATTITLE'] = $catinfo[$res['catid']]['title'];
                $tabledata[$i]['CATTEXT'] = $catinfo[$res['catid']]['text'];
                $tabledata[$i]['CATICON'] = $catinfo[$res['catid']]['icon'];

                //Autor
                $tabledata[$i]['USERID'] = $res['userid'];
                $tabledata[$i]['USERNAME'] = replace($author);
                $tabledata[$i]['EMAIL'] = replace($author_email);
                $tabledata[$i]['EMAIL_ENCRYPTED'] = replace(cryptMail($author_email));

                //Galerie
                if ($apx->is_module('gallery') && $res['galid']) {
                    $galinfo = gallery_info($res['galid']);
                    $tabledata[$i]['GALLERY_ID'] = $galinfo['id'];
                    $tabledata[$i]['GALLERY_TITLE'] = $galinfo['title'];
                    $tabledata[$i]['GALLERY_LINK'] = mklink(
                        'gallery.php?id='.$galinfo['id'],
                        'gallery,list'.$galinfo['id'].',1'.urlformat($galinfo['title']).'.html'
                    );
                }

                //Kommentare
                if ($apx->is_module('comments') && $set['links']['coms'] && $res['allowcoms']) {
                    require_once BASEDIR.getmodulepath('comments').'class.comments.php';
                    if (!isset($coms)) {
                        $coms = new comments('links', $res['id']);
                    } else {
                        $coms->mid = $res['id'];
                    }

                    $link = mklink(
                        'links.php?id='.$res['id'],
                        'links,id'.$res['id'].urlformat($res['title']).'.html'
                    );

                    $tabledata[$i]['COMMENT_COUNT'] = $coms->count();
                    $tabledata[$i]['COMMENT_LINK'] = $coms->link($link);
                    $tabledata[$i]['DISPLAY_COMMENTS'] = 1;
                    if (in_template(['LINK.COMMENT_LAST_USERID', 'LINK.COMMENT_LAST_NAME', 'LINK.COMMENT_LAST_TIME'], $parse)) {
                        $tabledata[$i]['COMMENT_LAST_USERID'] = $coms->last_userid();
                        $tabledata[$i]['COMMENT_LAST_NAME'] = $coms->last_name();
                        $tabledata[$i]['COMMENT_LAST_TIME'] = $coms->last_time();
                    }
                }

                //Bewertungen
                if ($apx->is_module('ratings') && $set['links']['ratings'] && $res['allowrating']) {
                    require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
                    if (!isset($rate)) {
                        $rate = new ratings('links', $res['id']);
                    } else {
                        $rate->mid = $res['id'];
                    }

                    $tabledata[$i]['RATING'] = $rate->display();
                    $tabledata[$i]['RATING_VOTES'] = $rate->count();
                    $tabledata[$i]['DISPLAY_RATING'] = 1;
                }
            }
        }

        $apx->tmpl->assign('LINK', $tabledata);
        $apx->tmpl->parse('search_result');
    }

    //SUCHE DURCHFÜHREN
    else {
        $where = '';

        //Suchbegriffe
        if ($_REQUEST['item']) {
            $items = [];
            $it = explode(' ', preg_replace('#[ ]{2,}#', ' ', trim($_REQUEST['item'])));
            $tagmatches = links_match_tags($it);
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

            $search = [];
            foreach ($items as $regexp) {
                $tagmatch = array_shift($tagmatches);
                $search[] = ' ( '.iif($tagmatch, ' id IN ('.implode(',', $tagmatch).') OR ').' title '.$regexp.' OR text '.$regexp.' OR url '.$regexp.' ) ';
            }
            $where .= iif($where, ' AND ').' ( '.implode($conn, $search).' ) ';
        }

        //Nach Tag suchen
        if ($_REQUEST['tag']) {
            $tagid = getTagId($_REQUEST['tag']);
            if ($tagid) {
                $data = $db->fetch('SELECT id FROM '.PRE."_links_tags WHERE tagid='".$tagid."'");
                $ids = get_ids($data, 'id');
                if ($ids) {
                    $where .= iif($where, ' AND ').' id IN ('.implode(',', $ids).') ';
                } else {
                    $where .= iif($where, ' AND ').' 0 ';
                }
            } else {
                $where .= iif($where, ' AND ').' 0 ';
            }
        }

        //Kategorie
        if ($_REQUEST['catid']) {
            $cattree = links_tree($_REQUEST['catid']);
            if (count($cattree)) {
                $where .= iif($where, ' AND ').'catid IN ('.@implode(',', $cattree).')';
            }
        }

        //Zeitperiode
        if ($_REQUEST['start_day'] && $_REQUEST['start_month'] && $_REQUEST['start_year'] && $_REQUEST['end_day'] && $_REQUEST['end_month'] && $_REQUEST['end_year']) {
            $where .= iif($where, ' AND ')."starttime BETWEEN '".(mktime(0, 0, 0, intval($_REQUEST['start_month']), intval($_REQUEST['start_day']), intval($_REQUEST['start_year'])) + TIMEDIFF)."' AND '".((mktime(0, 0, 0, intval($_REQUEST['end_month']), intval($_REQUEST['end_day']) + 1, intval($_REQUEST['end_year'])) - 1) + TIMEDIFF)."'";
        }

        //Keine Suchkriterien vorhanden
        if (!$where) {
            message($apx->lang->get('CORE_BACK'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //SUCHE AUSFÜHREN
        else {
            $data = $db->fetch('SELECT id FROM '.PRE.'_links WHERE '.$where);
            $resultIds = get_ids($data, 'id');

            //Keine Ergebnisse
            if (!$resultIds) {
                message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
                require 'lib/_end.php';
            }

            $searchid = saveSearchResult('links', $resultIds);
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.str_replace('&amp;', '&', mklink(
                'links.php?action=search&searchid='.$searchid,
                'links.html?action=search&searchid='.$searchid
            )));
        }
    }
    require 'lib/_end.php';
}

///////////////////////////////////////////////////////////////////////////////////////// KATEGORIEN DURCHSUCHEN

//Sprachpaket
$apx->lang->drop('list');
$apx->lang->drop('search');

//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('index');

//Kategorie auslesen
$catinfo = [];
if ($_REQUEST['catid']) {
    $catinfo = $db->first('SELECT id, title, text, icon, open FROM '.PRE."_links_cat WHERE id='".$_REQUEST['catid']."' LIMIT 1");
}

//Tree-Manager
require_once BASEDIR.'lib/class.recursivetree.php';
$tree = new RecursiveTree(PRE.'_links_cat', 'id');

//KATEGORIEN
if ($_REQUEST['catid']) {
    $wholetree = [$_REQUEST['catid']];
    $data = $tree->getLevel(['title', 'text', 'icon', 'open'], $_REQUEST['catid']);
} else {
    $wholetree = [];
    $data = $tree->getLevel(['title', 'text', 'icon', 'open']);
}

if (count($data)) {
    //Kategorien auflisten
    foreach ($data as $res) {
        ++$i;

        //Link
        $link = mklink(
            'links.php?catid='.$res['id'],
            'links,'.$res['id'].',1'.urlformat($res['title']).'.html'
        );

        //Link-Zahl
        $contentIds = $res['children'];
        $contentIds[] = $res['id'];
        $wholetree = array_merge($wholetree, $contentIds);
        list($count) = $db->first('SELECT count(id) FROM '.PRE.'_links WHERE ( catid IN ('.implode(',', $contentIds).") AND ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter().' )');
        $catdata[$i]['ID'] = $res['id'];
        $catdata[$i]['TITLE'] = $res['title'];
        $catdata[$i]['TEXT'] = $res['text'];
        $catdata[$i]['ICON'] = $res['icon'];
        $catdata[$i]['LINK'] = $link;
        $catdata[$i]['COUNT'] = $count;
    }
}

$apx->tmpl->assign('CATEGORY', $catdata);
$apx->tmpl->assign('CATID', $catinfo['id']);
$apx->tmpl->assign('CATTITLE', $catinfo['title']);
$apx->tmpl->assign('CATTEXT', $catinfo['text']);
$apx->tmpl->assign('CATICON', $catinfo['icon']);
$apx->tmpl->assign('CATLINK', mklink(
    'links.php?catid='.$catinfo['catid'],
    'links,'.$catinfo['catid'].',1'.urlformat($catinfo['title']).'.html'
));

//Pfad
if (in_array('PATH', $parse)) {
    $apx->tmpl->assign('PATH', links_path($_REQUEST['catid']));
}

//Suchbox
$catdata = [];
if (in_array('SEARCH_CATEGORY', $parse)) {
    $data = $tree->getTree(['title']);
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;
            $catdata[$i]['ID'] = $res['id'];
            $catdata[$i]['TITLE'] = $res['title'];
            $catdata[$i]['LEVEL'] = $res['level'];
        }
    }
}

$postto = mklink('links.php', 'links.html');
$apx->tmpl->assign('SEARCH_POSTTO', $postto);
$apx->tmpl->assign('SEARCH_CATEGORY', $catdata);

///////////////////////////////////////////////////
//Parings ausführen, wenn keine Kategorie gewählt//
///////////////////////////////////////////////////
if (!$_REQUEST['catid'] && $set['links']['catonly']) {
    $apx->tmpl->parse('index');
    require 'lib/_end.php';
}

//Filter bestimmen
if ($set['links']['catonly']) {
    $filter = "catid='".$_REQUEST['catid']."'";
} else {
    $filter = 'catid IN ('.implode(',', $wholetree).')';
}

//Seitenzahlen
list($count) = $db->first('SELECT count(id) FROM '.PRE.'_links WHERE ( '.$filter." AND ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter().' )');
pages(
    mklink(
        'links.php?catid='.$_REQUEST['catid'].iif($_REQUEST['sortby'], '&amp;sortby='.$_REQUEST['sortby']),
        'links,'.$_REQUEST['catid'].',{P}.html'.iif($_REQUEST['sortby'], '?sortby='.$_REQUEST['sortby'])
    ),
    $count,
    $set['links']['epp']
);
$apx->tmpl->assign('CATCOUNT', $count);

//Orderby
if (2 == $set['links']['sortby']) {
    $orderdef[0] = 'date';
} else {
    $orderdef[0] = 'title';
}
$orderdef['title'] = ['a.title', 'ASC'];
$orderdef['date'] = ['a.starttime', 'DESC'];
$orderdef['hits'] = ['a.hits', 'DESC'];
if ($apx->is_module('rating')) {
    $orderdef['rating'] = ['b.rating', 'DESC'];
}

//Links Select
if ($apx->is_module('ratings') && ('rating.ASC' == $_REQUEST['sortby'] || 'rating.DESC' == $_REQUEST['sortby'])) {
    $data = $db->fetch('SELECT a.*,avg(b.rating) AS rating FROM '.PRE.'_links AS a LEFT JOIN '.PRE."_ratings AS b ON ( b.module='links' AND a.id=b.mid ) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) AND ".$filter.' '.section_filter().' ) GROUP BY a.id '.getorder($orderdef).getlimit($set['links']['epp']));
} else {
    $data = $db->fetch('SELECT a.*,b.username,b.email,b.pub_hidemail FROM '.PRE.'_links AS a LEFT JOIN '.PRE."_user AS b USING(userid) WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) AND ".$filter.' '.section_filter().' ) '.getorder($orderdef).getlimit($set['links']['epp']));
}
$catids = get_ids($data, 'catid');

//Kategorien auslesen, falls notwendig
$catinfo = [];
if (count($catids) && in_template(['LINK.CATTITLE', 'LINK.CATTEXT', 'LINK.CATICON'], $parse)) {
    $catinfo = links_catinfo($catids);
}

if (count($data)) {
    foreach ($data as $res) {
        ++$i;

        //Dateillink
        $link = mklink(
            'links.php?id='.$res['id'],
            'links,id'.$res['id'].urlformat($res['title']).'.html'
        );

        //Neu?
        if (($res['addtime'] + ($set['links']['new'] * 24 * 3600)) >= time()) {
            $new = 1;
        } else {
            $new = 0;
        }

        //Goto-Link
        $gotolink = 'misc.php?action=gotolink&amp;id='.$res['id'].iif($apx->section_id(), '&amp;sec='.$apx->section_id());

        //Linkpic
        if (in_array('LINK.PICTURE', $parse) || in_array('LINK.PICTURE_POPUP', $parse) || in_array('LINK.PICTURE_POPUPPATH', $parse)) {
            list($picture, $picture_popup, $picture_popuppath) = links_linkpic($res['linkpic']);
        }

        //Username + eMail
        if ($res['userid']) {
            $author = $res['username'];
            $author_email = iif(!$res['pub_hidemail'], $res['email']);
        } else {
            $author = $res['send_username'];
            $author_email = $res['send_email'];
        }
        //Text
        $text = '';
        if (in_array('LINK.TEXT', $parse)) {
            $text = mediamanager_inline($res['text']);
            if ($apx->is_module('glossar')) {
                $text = glossar_highlight($text);
            }
        }

        //Tags
        if (in_array('LINK.TAG', $parse) || in_array('LINK.TAG_IDS', $parse) || in_array('LINK.KEYWORDS', $parse)) {
            list($tagdata, $tagids, $keywords) = links_tags($res['id']);
        }

        $tabledata[$i]['ID'] = $res['id'];
        $tabledata[$i]['TITLE'] = $res['title'];
        $tabledata[$i]['URL'] = $res['url'];
        $tabledata[$i]['TEXT'] = $text;
        $tabledata[$i]['LINK'] = $link;
        $tabledata[$i]['PICTURE'] = $picture;
        $tabledata[$i]['PICTURE_POPUP'] = $picture_popup;
        $tabledata[$i]['PICTURE_POPUPPATH'] = $picture_popuppath;
        $tabledata[$i]['HITS'] = number_format($res['hits'], 0, '', '.');
        $tabledata[$i]['TIME'] = $res['starttime'];
        $tabledata[$i]['TOP'] = $res['top'];
        $tabledata[$i]['RESTRICTED'] = $res['restricted'];
        $tabledata[$i]['NEW'] = $new;
        $tabledata[$i]['GOTO'] = $gotolink;

        //Tags
        $tabledata[$i]['TAG'] = $tagdata;
        $tabledata[$i]['TAG_IDS'] = $tagids;
        $tabledata[$i]['KEYWORDS'] = $keywords;

        //Kategorie
        $tabledata[$i]['CATID'] = $res['catid'];
        $tabledata[$i]['CATTITLE'] = $catinfo[$res['catid']]['title'];
        $tabledata[$i]['CATTEXT'] = $catinfo[$res['catid']]['text'];
        $tabledata[$i]['CATICON'] = $catinfo[$res['catid']]['icon'];

        //Autor
        $tabledata[$i]['USERID'] = $res['userid'];
        $tabledata[$i]['USERNAME'] = replace($author);
        $tabledata[$i]['EMAIL'] = replace($author_email);
        $tabledata[$i]['EMAIL_ENCRYPTED'] = replace(cryptMail($author_email));

        //Galerie
        if ($apx->is_module('gallery') && $res['galid']) {
            $galinfo = gallery_info($res['galid']);
            $tabledata[$i]['GALLERY_ID'] = $galinfo['id'];
            $tabledata[$i]['GALLERY_TITLE'] = $galinfo['title'];
            $tabledata[$i]['GALLERY_LINK'] = mklink(
                'gallery.php?id='.$galinfo['id'],
                'gallery,list'.$galinfo['id'].',1'.urlformat($galinfo['title']).'.html'
            );
        }

        //Kommentare
        if ($apx->is_module('comments') && $set['links']['coms'] && $res['allowcoms']) {
            require_once BASEDIR.getmodulepath('comments').'class.comments.php';
            if (!isset($coms)) {
                $coms = new comments('links', $res['id']);
            } else {
                $coms->mid = $res['id'];
            }

            $link = mklink(
                'links.php?id='.$res['id'],
                'links,id'.$res['id'].urlformat($res['title']).'.html'
            );

            $tabledata[$i]['COMMENT_COUNT'] = $coms->count();
            $tabledata[$i]['COMMENT_LINK'] = $coms->link($link);
            $tabledata[$i]['DISPLAY_COMMENTS'] = 1;
            if (in_template(['LINK.COMMENT_LAST_USERID', 'LINK.COMMENT_LAST_NAME', 'LINK.COMMENT_LAST_TIME'], $parse)) {
                $tabledata[$i]['COMMENT_LAST_USERID'] = $coms->last_userid();
                $tabledata[$i]['COMMENT_LAST_NAME'] = $coms->last_name();
                $tabledata[$i]['COMMENT_LAST_TIME'] = $coms->last_time();
            }
        }

        //Bewertungen
        if ($apx->is_module('ratings') && $set['links']['ratings'] && $res['allowrating']) {
            require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
            if (!isset($rate)) {
                $rate = new ratings('links', $res['id']);
            } else {
                $rate->mid = $res['id'];
            }

            $tabledata[$i]['RATING'] = $rate->display();
            $tabledata[$i]['RATING_VOTES'] = $rate->count();
            $tabledata[$i]['DISPLAY_RATING'] = 1;
        }
    }
}

//Sortby
ordervars(
    $orderdef,
    mklink(
        'links.php?catid='.$_REQUEST['catid'],
        'links,'.$_REQUEST['catid'].',1.html'
    )
);

$apx->tmpl->assign('LINK', $tabledata);
$apx->tmpl->parse('index');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
