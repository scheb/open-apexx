<?php

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_start.php';  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once BASEDIR.getmodulepath('articles').'functions.php';

$apx->module('articles');
$apx->lang->drop('articles');
$apx->lang->drop('global');

$_POST['id'] = (int) $_POST['id'];
$_REQUEST['id'] = (int) $_REQUEST['id'];
$_REQUEST['pic'] = (int) $_REQUEST['pic'];
$_REQUEST['page'] = (int) $_REQUEST['page'];
$_REQUEST['catid'] = (int) $_REQUEST['catid'];
$conclusionpage = false;

//Artikel-Typ prüfen
if (!$arttype) {
    $arttype = 'normal';
}
if (!in_array($arttype, ['normal', 'preview', 'review'])) {
    die('invalid article type!');
}

//MySQL-Filter erstellen
if ('normal' != $arttype || $set['articles']['normalonly']) {
    $artfilter = " AND type='".$arttype."' ";
} else {
    $artfilter = '';
}

//Wohin verlinken?
if ('normal' == $arttype) {
    $link2file = 'articles';
} else {
    $link2file = $arttype.'s';
}

//////////////////////////////////////////////////////////////////////////////////////////////////////// FORWARD ZU ARTIKEL

if ($_POST['id']) {
    //Kein Artfilter, sonst können Reviews/Previews nicht ausgewählt werden
    $res = $db->first('SELECT id,type,title FROM '.PRE.'_articles WHERE ( '.time()." BETWEEN starttime AND endtime AND id='".$_POST['id']."' ".section_filter().' ) LIMIT 1');
    if (!$res['id']) {
        filenotfound();
    }

    //Wohin verlinken?
    if ('normal' == $res['type']) {
        $link2file = 'articles';
    } else {
        $link2file = $res['type'].'s';
    }

    $link = str_replace('&amp;', '&', mklink(
        $link2file.'.php?id='.$res['id'],
        $link2file.',id'.$res['id'].',0'.urlformat($res['title']).'.html'
    ));

    //Weiterleiten und Exit
    header('HTTP/1.1 301 Moved Permanently');
    header('location:'.$link);
    exit;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////// NUR KOMMENTARE

if ($_REQUEST['id'] && $_REQUEST['comments']) {
    $res = $db->first('SELECT id,title,starttime FROM '.PRE.'_articles WHERE ( '.time()." BETWEEN starttime AND endtime AND id='".$_REQUEST['id']."' ".$artfilter.' '.section_filter().' ) LIMIT 1');

    //Headline + Titlebar
    if (articles_is_recent($res['id'])) {
        headline($apx->lang->get('HEADLINE_'.strtoupper($arttype)), mklink($link2file.'.php', $link2file.'.html'));
        titlebar($apx->lang->get('HEADLINE_'.strtoupper($arttype)).': '.$res['title']);
    } else {
        headline($apx->lang->get('HEADLINE_ARCHIVE'), mklink('articlearchive.php', 'articlearchive.html'));
        headline(getcalmonth(date('m', $res['starttime'] - TIMEDIFF)).' '.date('Y', $res['starttime'] - TIMEDIFF), mklink('articlearchive.php?month='.date('m', $res['starttime'] - TIMEDIFF).date('Y', $res['starttime'] - TIMEDIFF), 'articlearchive,'.date('m', $res['starttime'] - TIMEDIFF).','.date('Y', $res['starttime'] - TIMEDIFF).',1.html'));
        titlebar($apx->lang->get('HEADLINE_ARCHIVE').': '.$res['title']);
    }

    articles_showcomments($_REQUEST['id']);
    require 'lib/_end.php';
}

//////////////////////////////////////////////////////////////////////////////////////////////////////// BILDERSERIE ANZEIGEN

if ($_REQUEST['id'] && $_REQUEST['pic']) {
    //Verwendete Variablen auslesen
    $parse = $apx->tmpl->used_vars('picture');

    //Galerie-INFO
    $article = $db->first('SELECT id,catid,title,subtitle,starttime,pictures,restricted FROM '.PRE."_articles WHERE ( id='".$_REQUEST['id']."' ".section_filter().' '.iif(!$user->is_team_member(), " AND ( '".time()."' BETWEEN starttime AND endtime ) ").' ) LIMIT 1');
    if (!$article['id']) {
        filenotfound();
    }

    //Altersabfrage
    if ($article['restricted']) {
        checkage();
    }

    //Bild auswählen
    $pic = false;
    $article['pictures'] = unserialize($article['pictures']);
    if (!is_array($article['pictures'])) {
        require 'lib/_end.php';
    } //Kein Bilder vorhanden
    foreach ($article['pictures'] as $id => $info) {
        if ($id == $_REQUEST['pic']) {
            $pic = $info;

            break;
        }
    }
    if (!$pic) {
        filenotfound();
    } //Bild nicht gefunden

    //INFO: Kategorie
    if (in_array('CATTITLE', $parse) || in_array('CATICON', $parse) || in_array('CATLINK', $parse)) {
        $catinfo = articles_catinfo($article['catid']);
    }

    //Artikel-Platzhalter
    $piccount = count($article['pictures']);
    $link = mklink(
        $link2file.'.php?id='.$article['id'],
        $link2file.',id'.$article['id'].',0'.urlformat($article['title']).'.html'
    );
    $apx->tmpl->assign('ID', $article['id']);
    $apx->tmpl->assign('TITLE', $article['title']);
    $apx->tmpl->assign('SUBTITLE', $article['subtitle']);
    $apx->tmpl->assign('LINK', $link);
    $apx->tmpl->assign('TIME', $article['starttime']);
    $apx->tmpl->assign('COUNT', number_format($piccount, 0, '', '.'));
    $apx->tmpl->assign('CATID', $article['catid']);
    $apx->tmpl->assign('CATTITLE', $catinfo['title']);
    $apx->tmpl->assign('CATICON', $catinfo['icon']);
    $apx->tmpl->assign('CATLINK', $catinfo['link']);

    //Resize
    $modulepath = HTTPDIR.getmodulepath('articles');
    $size = getimagesize(BASEDIR.getpath('uploads').$pic['picture']);
    $javascript = <<<HTML
<script language="JavaScript" type="text/javascript">
<!--
resizex = {$size[0]}+{$set[articles][popup_addwidth]};
resizey = {$size[1]}+{$set[articles][popup_addheight]};
//-->
</script>
<script language="JavaScript" type="text/javascript" src="{$modulepath}picseries_resize.js"></script>
HTML;

    //Bild-Platzhalter
    $apx->tmpl->assign('IMAGE', getpath('uploads').$pic['picture']);
    $apx->tmpl->assign('RESIZE', $javascript);

    //Seitenzahlen
    articles_picseries_pages($article['pictures'], $link2file, $article['id']);

    $apx->tmpl->loaddesign('blank');
    $apx->tmpl->parse('picture');
    require 'lib/_end.php';
}

//////////////////////////////////////////////////////////////////////////////////////////////////////// ARTIKEL-SEITE ZEIGEN

//Artikel-Seiten
if ($_REQUEST['id']) {
    //INFO: Artikel
    $article = $db->first('SELECT a.*,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE."_user AS b USING(userid) WHERE ( id='".$_REQUEST['id']."' ".$artfilter.' '.iif(!$user->is_team_member(), "AND '".time()."' BETWEEN starttime AND endtime").' '.section_filter().' ) LIMIT 1');
    if (!$article['id']) {
        filenotfound();
    }

    //Hits hochzählen + Seite 1 aufrufen
    if (!$_REQUEST['page']) {
        $db->query('UPDATE '.PRE."_articles SET hits=hits+1 WHERE ( id='".$_REQUEST['id']."' ".$artfilter.' '.section_filter().' ) LIMIT 1');

        $link = str_replace('&amp;', '&', mklink(
            $link2file.'.php?id='.$_REQUEST['id'].'&amp;page=1',
            $link2file.',id'.$_REQUEST['id'].',1'.urlformat($article['title']).'.html'
        ));

        //Weiterleiten und Exit
        header('HTTP/1.1 301 Moved Permanently');
        header('location:'.$link);
        exit;
    }

    //Altersabfrage
    if ($article['restricted']) {
        checkage();
    }

    //INFO: Seitenzahl
    list($page_count) = $db->first('SELECT count(id) FROM '.PRE."_articles_pages WHERE artid='".$_REQUEST['id']."'");
    if ($set['articles']['reviews_conclusionpage'] && 'review' == $arttype) {
        ++$page_count;
    }
    if ($set['articles']['previews_conclusionpage'] && 'preview' == $arttype) {
        ++$page_count;
    }

    //INFO: Fazitseite
    if ((($set['articles']['reviews_conclusionpage'] && 'review' == $arttype) || ($set['articles']['previews_conclusionpage'] && 'preview' == $arttype)) && $_REQUEST['page'] == $page_count) {
        $conclusionpage = true;
    }

    //INFO: Aktuelle Seite
    else {
        $page = $db->first('SELECT * FROM '.PRE."_articles_pages WHERE artid='".$_REQUEST['id']."' ORDER BY ord ASC LIMIT ".($_REQUEST['page'] - 1).',1');
        if (!$page['id']) {
            filenotfound();
        }
    }

    //Verwendete Variablen auslesen
    if ($conclusionpage) {
        $template = 'conclusion_'.$arttype.'s';
    } else {
        $template = 'page'.iif('normal' != $arttype, '_'.$arttype.'s');
    }
    $parse = $apx->tmpl->used_vars($template);

    //Headline + Titlebar
    if (articles_is_recent($article['id'])) {
        headline($apx->lang->get('HEADLINE_'.strtoupper($arttype)), mklink($link2file.'.php', $link2file.'.html'));
        titlebar($apx->lang->get('HEADLINE_'.strtoupper($arttype)).': '.$article['title']);
    } else {
        headline($apx->lang->get('HEADLINE_ARCHIVE'), mklink('articlearchive.php', 'articlearchive.html'));
        headline(getcalmonth(date('m', $article['starttime'] - TIMEDIFF)).' '.date('Y', $article['starttime'] - TIMEDIFF), mklink('articlearchive.php?month='.date('m', $article['starttime'] - TIMEDIFF).date('Y', $article['starttime'] - TIMEDIFF), 'articlearchive,'.date('m', $article['starttime'] - TIMEDIFF).','.date('Y', $article['starttime'] - TIMEDIFF).',1.html'));
        titlebar($apx->lang->get('HEADLINE_ARCHIVE').': '.$article['title']);
    }

    //INFO: Kategorie
    if (in_array('CATTITLE', $parse) || in_array('CATICON', $parse) || in_array('CATLINK', $parse)) {
        $catinfo = articles_catinfo($article['catid']);
    }

    //Link
    $link = mklink(
        $link2file.'.php?id='.$article['id'],
        $link2file.',id'.$article['id'].',0'.urlformat($article['title']).'.html'
    );

    //Artikelpic
    if (in_array('PICTURE', $parse) || in_array('PICTURE_POPUP', $parse) || in_array('PICTURE_POPUPPATH', $parse)) {
        list($picture, $picture_popup, $picture_popuppath) = articles_artpic($article['artpic']);
    }

    //Links
    if (in_array('RELATED', $parse)) {
        $apx->tmpl->assign('RELATED', articles_links($article['links']));
    }

    //Bilderserie
    if (in_array('PICSERIES', $parse)) {
        $apx->tmpl->assign('PICSERIES', articles_picseries($article['pictures'], $article['id'], $link2file));
    }

    //Teaser
    $teaser = '';
    if (in_array('TEASER', $parse)) {
        $teaser = mediamanager_inline($article['teaser']);
        if ($apx->is_module('glossar')) {
            $teaser = glossar_highlight($teaser);
        }
    }

    //Tags
    if (in_array('TAG', $parse) || in_array('TAG_IDS', $parse) || in_array('KEYWORDS', $parse)) {
        list($tagdata, $tagids, $keywords) = articles_tags($article['id']);
    }

    $apx->tmpl->assign('ID', $article['id']);
    $apx->tmpl->assign('SECID', $article['secid']);
    $apx->tmpl->assign('TITLE', $article['title']);
    $apx->tmpl->assign('SUBTITLE', $article['subtitle']);
    $apx->tmpl->assign('TEASER', $teaser);
    $apx->tmpl->assign_static('META_DESCRIPTION', replace($article['meta_description']));
    $apx->tmpl->assign('LINK', $link);
    $apx->tmpl->assign('TIME', $article['starttime']);
    $apx->tmpl->assign('PICTURE', $picture);
    $apx->tmpl->assign('PICTURE_POPUP', $picture_popup);
    $apx->tmpl->assign('PICTURE_POPUPPATH', $picture_popuppath);
    $apx->tmpl->assign('USERID', $article['userid']);
    $apx->tmpl->assign('USERNAME', replace($article['username']));
    $apx->tmpl->assign('EMAIL', replace(iif(!$article['pub_hidemail'], $article['email'])));
    $apx->tmpl->assign('EMAIL_ENCRYPTED', replace(iif(!$article['pub_hidemail'], cryptMail($article['email']))));
    $apx->tmpl->assign('HITS', number_format($article['hits'], 0, '', '.'));
    $apx->tmpl->assign('TOP', $article['top']);
    $apx->tmpl->assign('RESTRICTED', $article['restricted']);

    //Tags
    $apx->tmpl->assign('TAG_IDS', $tagids);
    $apx->tmpl->assign('TAG', $tagdata);
    $apx->tmpl->assign('KEYWORDS', $keywords);

    //Produkt
    $apx->tmpl->assign('PRODUCT_ID', $article['prodid']);

    //Kategorie
    $apx->tmpl->assign('CATID', $article['catid']);
    $apx->tmpl->assign('CATTITLE', $catinfo['title']);
    $apx->tmpl->assign('CATICON', $catinfo['icon']);
    $apx->tmpl->assign('CATLINK', $catinfo['link']);

    //Galerie
    if ($apx->is_module('gallery') && $article['galid']) {
        $galinfo = gallery_info($article['galid']);
        $gallink = mklink(
            'gallery.php?id='.$galinfo['id'],
            'gallery,list'.$galinfo['id'].',1'.urlformat($galinfo['title']).'.html'
        );
        $apx->tmpl->assign('GALLERY_ID', $galinfo['id']);
        $apx->tmpl->assign('GALLERY_TITLE', $galinfo['title']);
        $apx->tmpl->assign('GALLERY_LINK', $gallink);
    }

    //Vorherige Seite
    $previous = '';
    if ($_REQUEST['page'] > 1) {
        $previous = mklink(
            $link2file.'.php?id='.$article['id'].'&amp;page='.($_REQUEST['page'] - 1),
            $link2file.',id'.$article['id'].','.($_REQUEST['page'] - 1).urlformat($article['title']).'.html'
        );
    }

    //Nächste Seite
    $next = '';
    if ($_REQUEST['page'] < $page_count) {
        $next = mklink(
            $link2file.'.php?id='.$article['id'].'&amp;page='.($_REQUEST['page'] + 1),
            $link2file.',id'.$article['id'].','.($_REQUEST['page'] + 1).urlformat($article['title']).'.html'
        );
    }

    //Erste Seite
    if ($_REQUEST['page'] < $page_count) {
        $first = mklink(
            $link2file.'.php?id='.$article['id'].'&amp;page=1',
            $link2file.',id'.$article['id'].',1'.urlformat($article['title']).'.html'
        );
    }

    //Letzte Seite
    if ($_REQUEST['page'] < $page_count) {
        $last = mklink(
            $link2file.'.php?id='.$article['id'].'&amp;page='.$page_count,
            $link2file.',id'.$article['id'].','.$page_count.urlformat($article['title']).'.html'
        );
    }

    //Fazitseite
    if ($conclusionpage) {
        $apx->tmpl->assign('PAGE_TITLE', $apx->lang->get('CONCLUSION'));
    }

    //Normale Seite
    else {
        $pagetext = mediamanager_inline($page['text']);
        if ($apx->is_module('glossar')) {
            $pagetext = glossar_highlight($pagetext);
        }
        $apx->tmpl->assign('PAGE_TITLE', $page['title']);
        $apx->tmpl->assign('PAGE_TEXT', $pagetext);
    }

    $apx->tmpl->assign('PAGE_COUNT', $page_count);
    $apx->tmpl->assign('PAGE_NUMBER', $_REQUEST['page']);
    $apx->tmpl->assign('PAGE_PREVIOUS', $previous);
    $apx->tmpl->assign('PAGE_NEXT', $next);
    $apx->tmpl->assign('PAGE_FIRST', $first);
    $apx->tmpl->assign('PAGE_LAST', $last);
    $apx->tmpl->assign('INDEX', articles_index($article['id'], $article['title'], $link2file));

    //Besondere Platzhalter für PREVIEWS
    if ('preview' == $arttype) {
        $preview = $db->first('SELECT * FROM '.PRE."_articles_previews WHERE artid='".$article['id']."' LIMIT 1");

        for ($i = 1; $i <= 10; ++$i) {
            if (!$set['articles']['custom_preview'][$i - 1]) {
                continue;
            }
            $apx->tmpl->assign('CUSTOM'.$i.'_NAME', $set['articles']['custom_preview'][$i - 1]);
            $apx->tmpl->assign('CUSTOM'.$i, $preview['custom'.$i]);
        }

        $apx->tmpl->assign('IMPRESSION', $preview['impression']);
        if (in_array('CONCLUSION', $parse)) {
            $conclusion = mediamanager_inline($preview['conclusion']);
            if ($apx->is_module('glossar')) {
                $conclusion = glossar_highlight($conclusion);
            }
            $apx->tmpl->assign('CONCLUSION', $conclusion);
        }
    }

    //Besondere Platzhalter für REVIEWS
    elseif ('review' == $arttype) {
        $review = $db->first('SELECT * FROM '.PRE."_articles_reviews WHERE artid='".$article['id']."' LIMIT 1");

        for ($i = 1; $i <= 10; ++$i) {
            if (!$set['articles']['custom_review'][$i - 1]) {
                continue;
            }
            $apx->tmpl->assign('CUSTOM'.$i.'_NAME', $set['articles']['custom_review'][$i - 1]);
            $apx->tmpl->assign('CUSTOM'.$i, $review['custom'.$i]);
        }

        for ($i = 1; $i <= 10; ++$i) {
            if (!$set['articles']['ratefields'][$i - 1]) {
                continue;
            }
            $apx->tmpl->assign('RATING'.$i.'_NAME', $set['articles']['ratefields'][$i - 1]);
            $apx->tmpl->assign('RATING'.$i, $review['rate'.$i]);
        }

        $apx->tmpl->assign('FINAL_RATING', $review['final_rate']);
        $apx->tmpl->assign('POSITIVE', $review['positive']);
        $apx->tmpl->assign('NEGATIVE', $review['negative']);
        if (in_array('CONCLUSION', $parse)) {
            $apx->tmpl->assign('CONCLUSION', mediamanager_inline($review['conclusion']));
        }
        $apx->tmpl->assign('AWARD', $review['award']);
    }

    //Kommentare
    if ($apx->is_module('comments') && $set['articles']['coms'] && $article['allowcoms']) {
        require_once BASEDIR.getmodulepath('comments').'class.comments.php';
        $coms = new comments('articles', $article['id']);
        $coms->assign_comments($parse);
        if (!articles_is_recent($article['id']) && !$set['articles']['archcoms']) {
            $apx->tmpl->assign('COMMENT_NOFORM', 1);
        }
    }

    //Bewertungen
    if ($apx->is_module('ratings') && $set['articles']['ratings'] && $article['allowrating']) {
        require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
        $rate = new ratings('articles', $article['id']);
        $rate->assign_ratings($parse);
        if (!articles_is_recent($article['id']) && !$set['articles']['archratings']) {
            $apx->tmpl->assign('RATING_NOFORM', 1);
        }
    }

    $apx->tmpl->parse($template);
    require 'lib/_end.php';
}

//////////////////////////////////////////////////////////////////////////////////////////////////////// ARTIKEL AUFLISTEN

//Headline + Titlebar
headline($apx->lang->get('HEADLINE_'.strtoupper($arttype)), mklink($link2file.'.php', $link2file.'.html'));
titlebar($apx->lang->get('HEADLINE_'.strtoupper($arttype)));

//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('index'.iif('normal' != $arttype, '_'.$arttype.'s'));

//Baum holen, wenn nur eine bestimmte Kategorie gezeigt werden soll
$cattree = articles_tree($_REQUEST['catid']);

//Letters
letters(mklink(
    $link2file.'.php?catid='.$_REQUEST['catid'],
    $link2file.',{LETTER},'.$_REQUEST['catid'].',1.html'
));

if (!$_REQUEST['letter']) {
    $_REQUEST['letter'] = 0;
}
if ($_REQUEST['letter']) {
    if ('spchar' == $_REQUEST['letter']) {
        $where = 'AND title NOT REGEXP("^[a-zA-Z]")';
    } else {
        $where = "AND title LIKE '".addslashes($_REQUEST['letter'])."%'";
    }
}

//Seitenzahlen
list($count) = $db->first('SELECT count(id) FROM '.PRE."_articles WHERE ( '".time()."' BETWEEN starttime AND endtime ".$where.' '.iif(count($cattree), 'AND catid IN ('.@implode(',', $cattree).')').' '.$artfilter.' '.section_filter().' )');
pages(
    mklink(
        $link2file.'.php?catid='.$_REQUEST['catid'].'&amp;letter='.$_REQUEST['letter'],
        $link2file.','.$_REQUEST['letter'].','.$_REQUEST['catid'].',{P}.html'
    ),
    $count,
    $set['articles']['epp']
);

//Ausgabe erfolgt
$data = $db->fetch('SELECT a.*,IF(a.sticky>='.time().',1,0) AS sticky,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE."_user AS b USING(userid) WHERE ( '".time()."' BETWEEN starttime AND endtime ".$where.' '.iif(count($cattree), 'AND catid IN ('.@implode(',', $cattree).')').' '.$artfilter.' '.section_filter().' ) ORDER BY sticky DESC,starttime DESC '.getlimit($set['articles']['epp']));
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
            $page1text = mediamanager_inline($page1text);
            if ($apx->is_module('glossar')) {
                $page1text = glossar_highlight($page1text);
            }
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

        //Teaser
        $teaser = '';
        if (in_array('ARTICLE.TEASER', $parse)) {
            $teaser = mediamanager_inline($res['teaser']);
            if ($apx->is_module('glossar')) {
                $teaser = glossar_highlight($teaser);
            }
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
        $tabledata[$i]['SECID'] = $res['secid'];
        $tabledata[$i]['TYPE'] = $res['type'];
        $tabledata[$i]['TITLE'] = $res['title'];
        $tabledata[$i]['SUBTITLE'] = $res['subtitle'];
        $tabledata[$i]['TEASER'] = $teaser;
        $tabledata[$i]['TEXT'] = $page1text;
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
$apx->tmpl->parse('index'.iif('normal' != $arttype, '_'.$arttype.'s'));

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
