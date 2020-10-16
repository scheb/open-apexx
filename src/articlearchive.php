<?php

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_start.php';  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once BASEDIR.getmodulepath('articles').'functions.php';

$apx->module('articles');
$apx->lang->drop('articles');
$apx->lang->drop('global');
headline($apx->lang->get('HEADLINE_ARCHIVE'), mklink('articlearchive.php', 'articlearchive.html'));
titlebar($apx->lang->get('HEADLINE_ARCHIVE'));

$recent = articles_recent();
$filter = iif(count($recent) && !$set['articles']['archiveall'], 'AND NOT ( id IN ('.implode(',', $recent).') )');
$_REQUEST['id'] = (int) $_REQUEST['id'];

//////////////////////////////////////////////////////////////////////////////////////////////////////// SUCHE

if ('search' == $_REQUEST['action']) {
    $apx->lang->drop('search');

    //ERGEBNIS ANZEIGEN
    if ($_REQUEST['searchid']) {
        titlebar($apx->lang->get('HEADLINE_SEARCH'));

        //Suchergebnis auslesen
        $resultIds = '';
        list($resultIds) = getSearchResult('articles', $_REQUEST['searchid']);

        //Keine Ergebnisse
        if (!$resultIds) {
            message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //SUCHE AUSFÜHREN
        $parse = $apx->tmpl->used_vars('search_result');

        //Seitenzahlen generieren
        list($count) = $db->first('SELECT count(id) FROM '.PRE.'_articles WHERE '.time().' BETWEEN starttime AND endtime AND id IN ('.implode(', ', $resultIds).') '.section_filter());
        pages(
            mklink(
                'articlearchive.php?action=search&searchid='.$_REQUEST['searchid'],
                'articlearchive.html?action=search&searchid='.$_REQUEST['searchid']
            ),
            $count,
            $set['articles']['searchepp']
        );

        //Keine Ergebnisse
        if (!$count) {
            message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //Artikel ausgeben
        $data = $db->fetch('SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE.'_user AS b USING(userid) WHERE '.time().' BETWEEN starttime AND endtime AND id IN ('.implode(', ', $resultIds).') '.section_filter().' ORDER BY starttime DESC '.getlimit($set['articles']['searchepp']));
        $data = articles_extend_data($data, $parse); //Datensatz erweitern durch Preview/Review-Daten

        //Kategorien auslesen
        if (in_array('ARTICLE.CATTITLE', $parse) || in_array('ARTICLE.CATICON', $parse) || in_array('ARTICLE.CATLINK', $parse)) {
            $catinfo = articles_catinfo(get_ids($data, 'catid'));
        }

        foreach ($data as $res) {
            ++$i;

            //Wohin soll verlinkt werden?
            if ('normal' == $res['type']) {
                $link2file = 'articles';
            } else {
                $link2file = $res['type'].'s';
            }

            //Link
            $link = mklink(
                $link2file.'.php?id='.$res['id'],
                $link2file.',id'.$res['id'].',0'.urlformat($res['title']).'.html'
            );

            //Artikelpic
            if (in_array('ARTICLE.PICTURE', $parse) || in_array('ARTICLE.PICTURE_POPUP', $parse) || in_array('ARTICLE.PICTURE_POPUPPATH', $parse)) {
                list($picture, $picture_popup, $picture_popuppath) = articles_artpic($res['artpic']);
            }

            //Artikeltext
            if (in_array('ARTICLE.TEXT', $parse)) {
                list($page1text) = $db->first('SELECT text FROM '.PRE."_articles_pages WHERE artid='".$res['id']."' ORDER BY ord ASC LIMIT 1");
            }

            //Datehead
            if ($laststamp != date('Y/m/d', $res['starttime'] - TIMEDIFF)) {
                $tabledata[$i]['DATEHEAD'] = $res['starttime'];
            }

            //Links
            if (in_array('ARTICLE.RELATED', $parse)) {
                $tabledata[$i]['RELATED'] = articles_links($res['links']);
            }

            //Bilderserie
            if (in_array('ARTICLE.PICSERIES', $parse)) {
                $tabledata[$i]['PICSERIES'] = articles_picseries($res['pictures'], $res['id'], $link2file);
            }

            //Tags
            if (in_array('ARTICLE.TAG', $parse) || in_array('ARTICLE.TAG_IDS', $parse) || in_array('ARTICLE.KEYWORDS', $parse)) {
                list($tagdata, $tagids, $keywords) = articles_tags($res['id']);
            }

            //Index
            $pageIndex = [];
            if (in_array('ARTICLE.INDEX', $parse)) {
                $pageIndex = articles_index($res['id'], $res['title'], $link2file);
            }

            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['TYPE'] = $res['type'];
            $tabledata[$i]['TITLE'] = $res['title'];
            $tabledata[$i]['SUBTITLE'] = $res['subtitle'];
            if (in_array('ARTICLE.TEASER', $parse)) {
                $tabledata[$i]['TEASER'] = mediamanager_inline($res['teaser']);
            }
            if (in_array('ARTICLE.TEXT', $parse)) {
                $tabledata[$i]['TEXT'] = mediamanager_inline($page1text);
            }
            $tabledata[$i]['LINK'] = $link;
            $tabledata[$i]['TIME'] = $res['starttime'];
            $tabledata[$i]['INDEX'] = $pageIndex;
            $tabledata[$i]['PICTURE'] = $picture;
            $tabledata[$i]['PICTURE_POPUP'] = $picture_popup;
            $tabledata[$i]['PICTURE_POPUPPATH'] = $picture_popuppath;
            $tabledata[$i]['USERID'] = $res['userid'];
            $tabledata[$i]['USERNAME'] = replace($res['username']);
            $tabledata[$i]['EMAIL'] = replace(iif(!$res['pub_hidemail'], $res['email']));
            $tabledata[$i]['EMAIL_ENCRYPTED'] = replace(iif(!$res['pub_hidemail'], cryptMail($res['email'])));
            $tabledata[$i]['HITS'] = number_format($res['hits'], 0, '', '.');
            $tabledata[$i]['TOP'] = $res['top'];
            $tabledata[$i]['RESTRICTED'] = $res['restricted'];

            //Tags
            $tabledata[$i]['TAG'] = $tagdata;
            $tabledata[$i]['TAG_IDS'] = $tagids;
            $tabledata[$i]['KEYWORDS'] = $keywords;

            //Kategorie
            $tabledata[$i]['CATID'] = $res['catid'];
            $tabledata[$i]['CATTITLE'] = $catinfo[$res['catid']]['title'];
            $tabledata[$i]['CATICON'] = $catinfo[$res['catid']]['icon'];
            $tabledata[$i]['CATLINK'] = $catinfo[$res['catid']]['link'];

            //Produkt
            $tabledata[$i]['PRODUCT_ID'] = $res['prodid'];

            //Zusätzliche Felder: PREVIEWS
            if ('preview' == $res['type']) {
                for ($ii = 1; $ii <= 10; ++$ii) {
                    if (!$set['articles']['custom_preview'][$ii - 1]) {
                        continue;
                    }
                    $tabledata[$i]['CUSTOM'.$ii.'_TITLE'] = $set['articles']['custom_preview'][$ii - 1];
                    $tabledata[$i]['CUSTOM'.$ii] = $res['custom'.$ii];
                }
                $tabledata[$i]['IMPRESSION'] = $res['impression'];
            }

            //Zusätzliche Felder: REVIEWS
            elseif ('review' == $res['type']) {
                for ($ii = 1; $ii <= 10; ++$ii) {
                    if (!$set['articles']['custom_review'][$ii - 1]) {
                        continue;
                    }
                    $tabledata[$i]['CUSTOM'.$ii.'_TITLE'] = $set['articles']['custom_review'][$ii - 1];
                    $tabledata[$i]['CUSTOM'.$ii] = $res['custom'.$ii];
                }
                $tabledata[$i]['FINAL_RATING'] = $res['final_rate'];
                $tabledata[$i]['POSITIVE'] = $res['positive'];
                $tabledata[$i]['NEGATIVE'] = $res['negative'];
                $tabledata[$i]['AWARD'] = $res['award'];
            }

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
            if ($apx->is_module('comments') && $set['articles']['coms'] && $res['allowcoms']) {
                require_once BASEDIR.getmodulepath('comments').'class.comments.php';
                if (!isset($coms)) {
                    $coms = new comments('articles', $res['id']);
                } else {
                    $coms->mid = $res['id'];
                }

                $link = mklink(
                    $link2file.'.php?id='.$res['id'],
                    $link2file.',id'.$res['id'].',1'.urlformat($res['title']).'.html'
                );

                $tabledata[$i]['COMMENT_COUNT'] = $coms->count();
                $tabledata[$i]['COMMENT_LINK'] = $coms->link($link);
                $tabledata[$i]['DISPLAY_COMMENTS'] = 1;
                if (in_template(['ARTICLE.COMMENT_LAST_USERID', 'ARTICLE.COMMENT_LAST_NAME', 'ARTICLE.COMMENT_LAST_TIME'], $parse)) {
                    $tabledata[$i]['COMMENT_LAST_USERID'] = $coms->last_userid();
                    $tabledata[$i]['COMMENT_LAST_NAME'] = $coms->last_name();
                    $tabledata[$i]['COMMENT_LAST_TIME'] = $coms->last_time();
                }
            }

            //Bewertungen
            if ($apx->is_module('ratings') && $set['articles']['ratings'] && $res['allowrating']) {
                require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
                if (!isset($rate)) {
                    $rate = new ratings('articles', $res['id']);
                } else {
                    $rate->mid = $res['id'];
                }

                $tabledata[$i]['RATING'] = $rate->display();
                $tabledata[$i]['RATING_VOTES'] = $rate->count();
                $tabledata[$i]['DISPLAY_RATING'] = 1;
            }

            $laststamp = date('Y/m/d', $res['starttime'] - TIMEDIFF);
        }

        $apx->tmpl->assign('ARTICLE', $tabledata);
        $apx->tmpl->parse('search_result');
    }

    //SUCHE DURCHFÜHREN
    else {
        $where = '';
        $pagewhere = '';

        //Alle Artikeltypen sind nicht gewählt
        if (!is_array($_REQUEST['type']) || !count($_REQUEST['type'])) {
            $_REQUEST['type'] = ['normal', 'preview', 'review'];
        }

        //Suchbegriffe
        if ($_REQUEST['item']) {
            $items = [];
            $it = explode(' ', preg_replace('#[ ]{2,}#', ' ', trim($_REQUEST['item'])));
            $tagmatches = articles_match_tags($it);
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
                $tagmatch = array_shift($tagmatches);
                $search[] = '( '.iif($tagmatch, ' a.id IN ('.implode(',', $tagmatch).') OR ').' a.title '.$regexp.' OR a.subtitle '.$regexp.' OR a.teaser '.$regexp.' OR p.title '.$regexp.' OR p.text '.$regexp.' ) ';
            }
            $where .= iif($where, ' AND ').' ( '.implode($conn, $search).' ) ';
        }

        //Nach Tag suchen
        if ($_REQUEST['tag']) {
            $tagid = getTagId($_REQUEST['tag']);
            if ($tagid) {
                $data = $db->fetch('SELECT id FROM '.PRE."_articles_tags WHERE tagid='".$tagid."'");
                $ids = get_ids($data, 'id');
                if ($ids) {
                    $where .= iif($where, ' AND ').' a.id IN ('.implode(',', $ids).') ';
                } else {
                    $where .= iif($where, ' AND ').' 0 ';
                }
            } else {
                $where .= iif($where, ' AND ').' 0 ';
            }
        }

        //Kategorie
        if ($_REQUEST['catid']) {
            $cattree = articles_tree($_REQUEST['catid']);
            if (count($cattree)) {
                $where .= iif($where, ' AND ').' a.catid IN ('.@implode(',', $cattree).')';
            }
        }

        //Artikeltyp
        $arttypes = [];
        foreach ($_REQUEST['type'] as $type) {
            if (in_array($type, ['normal', 'preview', 'review'])) {
                $arttypes[] = "'".$type."'";
            }
        }
        if (count($arttypes) > 0 && count($arttypes) < 3) {
            $where .= iif($where, ' AND ').' a.type IN ('.implode(',', $arttypes).')';
        }

        //Zeitperiode
        if ($_REQUEST['start_day'] && $_REQUEST['start_month'] && $_REQUEST['start_year'] && $_REQUEST['end_day'] && $_REQUEST['end_month'] && $_REQUEST['end_year']) {
            $where .= iif($where, ' AND ')." a.starttime BETWEEN '".(mktime(0, 0, 0, intval($_REQUEST['start_month']), intval($_REQUEST['start_day']), intval($_REQUEST['start_year'])) + TIMEDIFF)."' AND '".((mktime(0, 0, 0, intval($_REQUEST['end_month']), intval($_REQUEST['end_day']) + 1, intval($_REQUEST['end_year'])) - 1) + TIMEDIFF)."'";
        }

        //Keine Suchkriterien vorhanden
        if (!$where) {
            message($apx->lang->get('CORE_BACK'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //SUCHE AUSFÜHREN
        else {
            $data = $db->fetch(
                '
				SELECT DISTINCT a.id
				FROM '.PRE.'_articles AS a
				LEFT JOIN '.PRE.'_articles_pages AS p ON a.id=p.artid
				WHERE '.$where
            );
            $resultIds = get_ids($data, 'id');

            //Keine Ergebnisse
            if (!$resultIds) {
                message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
                require 'lib/_end.php';
            }

            $searchid = saveSearchResult('articles', $resultIds);
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.str_replace('&amp;', '&', mklink(
                'articlearchive.php?action=search&searchid='.$searchid,
                'articlearchive.html?action=search&searchid='.$searchid
            )));
        }
    }
    require 'lib/_end.php';
}

//////////////////////////////////////////////////////////////////////////////////////////////////////// ARTIKEL AUFLISTEN

if ($_REQUEST['month']) {
    //Verwendete Variablen auslesen
    $parse = $apx->tmpl->used_vars('archive_index');

    //Headline
    $month = substr($_REQUEST['month'], 0, 2);
    $year = substr($_REQUEST['month'], 2);
    headline(
        getcalmonth($month).' '.$year,
        mklink(
        'articlearchive.php?month='.$month.$year,
        'articlearchive,'.$month.','.$year.',1.html'
    )
    );
    titlebar($apx->lang->get('HEADLINE_ARCHIVE').': '.getcalmonth($month).' '.$year);

    //Seitenzahlen generieren
    list($count) = $db->first('SELECT count(id) FROM '.PRE.'_articles WHERE ( ( ( '.time()." BETWEEN starttime AND endtime ) AND starttime BETWEEN '".(mktime(0, 0, 0, intval($month), 1, intval($year)) + TIMEDIFF)."' AND '".((mktime(0, 0, 0, intval($month + 1), 1, intval($year)) - 1) + TIMEDIFF)."' ) ".$filter.' '.section_filter().' )');
    pages(
        mklink(
            'articlearchive.php?month='.$_REQUEST['month'],
            'articlearchive,'.$month.','.$year.',{P}.html'
        ),
        $count,
        $set['articles']['archiveepp']
    );

    //Artikel ausgeben
    if (1 == $set['articles']['archiveentrysort']) {
        $orderby = ' starttime DESC ';
    } else {
        $orderby = ' starttime ASC';
    }
    $data = $db->fetch('SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE.'_user AS b USING(userid) WHERE ( ( ( '.time()." BETWEEN starttime AND endtime ) AND starttime BETWEEN '".(mktime(0, 0, 0, intval($month), 1, intval($year)) + TIMEDIFF)."' AND '".((mktime(0, 0, 0, intval($month + 1), 1, intval($year)) - 1 + TIMEDIFF))."' ) ".$filter.' '.section_filter().' ) ORDER BY '.$orderby.' '.getlimit($set['articles']['archiveepp']));
    $data = articles_extend_data($data, $parse); //Datensatz erweitern durch Preview/Review-Daten

    //Kategorien auslesen
    if (in_array('ARTICLE.CATTITLE', $parse) || in_array('ARTICLE.CATICON', $parse) || in_array('ARTICLE.CATLINK', $parse)) {
        $catinfo = articles_catinfo(get_ids($data, 'catid'));
    }

    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            //Wohin soll verlinkt werden?
            if ('normal' == $res['type']) {
                $link2file = 'articles';
            } else {
                $link2file = $res['type'].'s';
            }

            //Link
            $link = mklink(
                $link2file.'.php?id='.$res['id'],
                $link2file.',id'.$res['id'].',0'.urlformat($res['title']).'.html'
            );

            //Artikelpic
            if (in_array('ARTICLE.PICTURE', $parse) || in_array('ARTICLE.PICTURE_POPUP', $parse) || in_array('ARTICLE.PICTURE_POPUPPATH', $parse)) {
                list($picture, $picture_popup, $picture_popuppath) = articles_artpic($res['artpic']);
            }

            //Artikeltext
            if (in_array('ARTICLE.TEXT', $parse)) {
                list($page1text) = $db->first('SELECT text FROM '.PRE."_articles_pages WHERE artid='".$res['id']."' ORDER BY ord ASC LIMIT 1");
            }

            //Datehead
            if ($laststamp != date('Y/m/d', $res['starttime'] - TIMEDIFF)) {
                $tabledata[$i]['DATEHEAD'] = $res['starttime'];
            }

            //Links
            if (in_array('ARTICLE.RELATED', $parse)) {
                $tabledata[$i]['RELATED'] = articles_links($res['links']);
            }

            //Bilderserie
            if (in_array('ARTICLE.PICSERIES', $parse)) {
                $tabledata[$i]['PICSERIES'] = articles_picseries($res['pictures'], $res['id'], $link2file);
            }

            //Tags
            if (in_array('ARTICLE.TAG', $parse) || in_array('ARTICLE.TAG_IDS', $parse) || in_array('ARTICLE.KEYWORDS', $parse)) {
                list($tagdata, $tagids, $keywords) = articles_tags($res['id']);
            }

            //Index
            $pageIndex = [];
            if (in_array('ARTICLE.INDEX', $parse)) {
                $pageIndex = articles_index($res['id'], $res['title'], $link2file);
            }

            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['TYPE'] = $res['type'];
            $tabledata[$i]['TITLE'] = $res['title'];
            $tabledata[$i]['SUBTITLE'] = $res['subtitle'];
            if (in_array('ARTICLE.TEASER', $parse)) {
                $tabledata[$i]['TEASER'] = mediamanager_inline($res['teaser']);
            }
            if (in_array('ARTICLE.TEXT', $parse)) {
                $tabledata[$i]['TEXT'] = mediamanager_inline($page1text);
            }
            $tabledata[$i]['LINK'] = $link;
            $tabledata[$i]['TIME'] = $res['starttime'];
            $tabledata[$i]['INDEX'] = $pageIndex;
            $tabledata[$i]['PICTURE'] = $picture;
            $tabledata[$i]['PICTURE_POPUP'] = $picture_popup;
            $tabledata[$i]['PICTURE_POPUPPATH'] = $picture_popuppath;
            $tabledata[$i]['USERID'] = $res['userid'];
            $tabledata[$i]['USERNAME'] = replace($res['username']);
            $tabledata[$i]['EMAIL'] = replace(iif(!$res['pub_hidemail'], $res['email']));
            $tabledata[$i]['EMAIL_ENCRYPTED'] = replace(iif(!$res['pub_hidemail'], cryptMail($res['email'])));
            $tabledata[$i]['HITS'] = number_format($res['hits'], 0, '', '.');
            $tabledata[$i]['TOP'] = $res['top'];
            $tabledata[$i]['RESTRICTED'] = $res['restricted'];

            //Tags
            $tabledata[$i]['TAG'] = $tagdata;
            $tabledata[$i]['TAG_IDS'] = $tagids;
            $tabledata[$i]['KEYWORDS'] = $keywords;

            //Kategorie
            $tabledata[$i]['CATID'] = $res['catid'];
            $tabledata[$i]['CATTITLE'] = $catinfo[$res['catid']]['title'];
            $tabledata[$i]['CATICON'] = $catinfo[$res['catid']]['icon'];
            $tabledata[$i]['CATLINK'] = $catinfo[$res['catid']]['link'];

            //Produkt
            $tabledata[$i]['PRODUCT_ID'] = $res['prodid'];

            //Zusätzliche Felder: PREVIEWS
            if ('preview' == $res['type']) {
                for ($ii = 1; $ii <= 10; ++$ii) {
                    if (!$set['articles']['custom_preview'][$ii - 1]) {
                        continue;
                    }
                    $tabledata[$i]['CUSTOM'.$ii.'_TITLE'] = $set['articles']['custom_preview'][$ii - 1];
                    $tabledata[$i]['CUSTOM'.$ii] = $res['custom'.$ii];
                }
                $tabledata[$i]['IMPRESSION'] = $res['impression'];
            }

            //Zusätzliche Felder: REVIEWS
            elseif ('review' == $res['type']) {
                for ($ii = 1; $ii <= 10; ++$ii) {
                    if (!$set['articles']['custom_review'][$ii - 1]) {
                        continue;
                    }
                    $tabledata[$i]['CUSTOM'.$ii.'_TITLE'] = $set['articles']['custom_review'][$ii - 1];
                    $tabledata[$i]['CUSTOM'.$ii] = $res['custom'.$ii];
                }
                $tabledata[$i]['FINAL_RATING'] = $res['final_rate'];
                $tabledata[$i]['POSITIVE'] = $res['positive'];
                $tabledata[$i]['NEGATIVE'] = $res['negative'];
                $tabledata[$i]['AWARD'] = $res['award'];
            }

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
            if ($apx->is_module('comments') && $set['articles']['coms'] && $res['allowcoms']) {
                require_once BASEDIR.getmodulepath('comments').'class.comments.php';
                if (!isset($coms)) {
                    $coms = new comments('articles', $res['id']);
                } else {
                    $coms->mid = $res['id'];
                }

                $link = mklink(
                    $link2file.'.php?id='.$res['id'],
                    $link2file.',id'.$res['id'].',1'.urlformat($res['title']).'.html'
                );

                $tabledata[$i]['COMMENT_COUNT'] = $coms->count();
                $tabledata[$i]['COMMENT_LINK'] = $coms->link($link);
                $tabledata[$i]['DISPLAY_COMMENTS'] = 1;
                if (in_template(['ARTICLE.COMMENT_LAST_USERID', 'ARTICLE.COMMENT_LAST_NAME', 'ARTICLE.COMMENT_LAST_TIME'], $parse)) {
                    $tabledata[$i]['COMMENT_LAST_USERID'] = $coms->last_userid();
                    $tabledata[$i]['COMMENT_LAST_NAME'] = $coms->last_name();
                    $tabledata[$i]['COMMENT_LAST_TIME'] = $coms->last_time();
                }
            }

            //Bewertungen
            if ($apx->is_module('ratings') && $set['articles']['ratings'] && $res['allowrating']) {
                require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
                if (!isset($rate)) {
                    $rate = new ratings('articles', $res['id']);
                } else {
                    $rate->mid = $res['id'];
                }

                $tabledata[$i]['RATING'] = $rate->display();
                $tabledata[$i]['RATING_VOTES'] = $rate->count();
                $tabledata[$i]['DISPLAY_RATING'] = 1;
            }

            $laststamp = date('Y/m/d', $res['starttime'] - TIMEDIFF);
        }
    }

    $apx->tmpl->assign('ARTICLE', $tabledata);
    $apx->tmpl->parse('archive_index');
    require 'lib/_end.php';
}

//////////////////////////////////////////////////////////////////////////////////////////////////////// MONATE AUFLISTEN

$apx->lang->drop('search');
$parse = $apx->tmpl->used_vars('archive');

$data = $db->fetch('SELECT id,starttime FROM '.PRE."_articles WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) ".$filter.' '.section_filter().' ) ORDER BY starttime '.iif(2 == $set['articles']['archivesort'], 'ASC', 'DESC'));
if (count($data)) {
    foreach ($data as $res) {
        if ($laststamp == date('Y/m', $res['starttime'] - TIMEDIFF)) {
            continue;
        }
        ++$i;

        //Link
        $link = mklink(
            'articlearchive.php?month='.date('mY', $res['starttime'] - TIMEDIFF),
            'articlearchive,'.date('m,Y', $res['starttime'] - TIMEDIFF).',1.html'
        );

        //Links
        if (in_array('ARCHIVE.COUNT', $parse)) {
            $monthStart = mktime(0, 0, 0, date('n', $res['starttime'] - TIMEDIFF), 1, date('Y', $res['starttime'] - TIMEDIFF)) + TIMEDIFF;
            $monthEnd = mktime(0, 0, 0, date('n', $res['starttime'] - TIMEDIFF) + 1, 1, date('Y', $res['starttime'] - TIMEDIFF)) + TIMEDIFF - 1;
            list($count) = $db->first('SELECT count(id) FROM '.PRE."_articles WHERE ( ( '".time()."' BETWEEN starttime AND endtime ) AND ( starttime BETWEEN ".$monthStart.' AND '.$monthEnd.' ) '.$filter.' '.section_filter().' )');
            $tabledata[$i]['COUNT'] = $count;
        }

        $tabledata[$i]['YEAR'] = date('Y', $res['starttime'] - TIMEDIFF);
        $tabledata[$i]['MONTH'] = $res['starttime'];
        $tabledata[$i]['LINK'] = $link;

        $laststamp = date('Y/m', $res['starttime'] - TIMEDIFF);
    }
}

$apx->tmpl->assign('ARCHIVE', $tabledata);

//Suchbox
$data = articles_catinfo();
foreach ($data as $id => $cat) {
    ++$i;
    $catdata[$i]['ID'] = $id;
    $catdata[$i]['TITLE'] = $cat['title'];
    $catdata[$i]['LEVEL'] = $cat['level'];
}

$postto = mklink('articlearchive.php', 'articlearchive.html');
$apx->tmpl->assign('SEARCH_POSTTO', $postto);
$apx->tmpl->assign('SEARCH_CATEGORY', $catdata);

$apx->tmpl->parse('archive');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
