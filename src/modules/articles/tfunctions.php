<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

require_once BASEDIR.getmodulepath('articles').'functions.php';

//Artikel wählen-Box
function articles_choose($catid = 0, $type = false)
{
    global $set,$db,$apx,$user;
    $catid = (int) $catid;

    $tmpl = new tengine();
    $apx->lang->drop('global', 'articles');

    if (!in_array($type, [false, 'normal', 'preview', 'review'])) {
        $type = false;
    }

    //Baum holen, wenn nur eine bestimmte Kategorie gezeigt werden soll
    $cattree = articles_tree($catid);

    $data = $db->fetch('SELECT id,title,subtitle FROM '.PRE."_articles WHERE ( '".time()."' BETWEEN starttime AND endtime ".iif($type, "AND type='".$type."'").' '.iif(count($cattree), 'AND catid IN ('.@implode(',', $cattree).')').' '.section_filter().' ) ORDER BY title ASC');
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;
            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['TITLE'] = strip_tags($res['title']);
            $tabledata[$i]['SUBTITLE'] = strip_tags($res['subtitle']);
        }
    }

    $tmpl->assign('ARTICLE', $tabledata);
    $tmpl->assign('POSTTO', mklink('articles.php', 'articles.html'));
    $tmpl->parse('functions/choose', 'articles');
}

//Letzte Artikel ausgeben
function articles_last($count = 5, $start = 0, $catid = false, $type = false, $template = 'last')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $catid = (int) $catid;

    if (!in_array($type, [false, 'normal', 'preview', 'review'])) {
        $type = false;
    }

    $cattree = articles_tree($catid);
    $data = $db->fetch('SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE.'_user AS b USING(userid) WHERE ( '.time().' BETWEEN starttime AND endtime '.iif($type, "AND type='".$type."'").' '.iif(count($cattree), ' AND catid IN ('.@implode(',', $cattree).') ').' '.section_filter().' ) ORDER BY starttime DESC LIMIT '.iif($start, $start.',').$count);

    articles_print($data, 'functions/'.$template);
}

//Zufällige Artikel ausgeben
function articles_random($count = 5, $start = 0, $catid = false, $type = false, $template = 'random')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $catid = (int) $catid;

    if (!in_array($type, [false, 'normal', 'preview', 'review'])) {
        $type = false;
    }

    $cattree = articles_tree($catid);
    $data = $db->fetch('SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE.'_user AS b USING(userid) WHERE ( '.time().' BETWEEN starttime AND endtime '.iif($type, "AND type='".$type."'").' '.iif(count($cattree), ' AND catid IN ('.@implode(',', $cattree).') ').' '.section_filter().' ) ORDER BY RAND() LIMIT '.iif($start, $start.',').$count);

    articles_print($data, 'functions/'.$template);
}

//TOP-Artikel ausgeben
function articles_top($count = 5, $start = 0, $catid = false, $type = false, $template = 'top')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $catid = (int) $catid;

    if (!in_array($type, [false, 'normal', 'preview', 'review'])) {
        $type = false;
    }

    $cattree = articles_tree($catid);
    $data = $db->fetch('SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE.'_user AS b USING(userid) WHERE ( '.time().' BETWEEN starttime AND endtime '.iif($type, "AND type='".$type."'").' '.iif(count($cattree), ' AND catid IN ('.@implode(',', $cattree).') ')." AND top='1' ".section_filter().' ) ORDER BY starttime DESC LIMIT '.iif($start, $start.',').$count);

    articles_print($data, 'functions/'.$template);
}

//Nicht-TOP-Artikel ausgeben
function articles_nottop($count = 5, $start = 0, $catid = false, $type = false, $template = 'nottop')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $catid = (int) $catid;

    if (!in_array($type, [false, 'normal', 'preview', 'review'])) {
        $type = false;
    }

    $cattree = articles_tree($catid);
    $data = $db->fetch('SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE.'_user AS b USING(userid) WHERE ( '.time().' BETWEEN starttime AND endtime '.iif($type, "AND type='".$type."'").' '.iif(count($cattree), ' AND catid IN ('.@implode(',', $cattree).') ')." AND top='0' ".section_filter().' ) ORDER BY starttime DESC LIMIT '.iif($start, $start.',').$count);

    articles_print($data, 'functions/'.$template);
}

//Beste Artikel: Hits
function articles_best_hits($count = 5, $start = 0, $catid = false, $type = false, $template = 'best_hits')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $catid = (int) $catid;

    if (!in_array($type, [false, 'normal', 'preview', 'review'])) {
        $type = false;
    }

    $cattree = articles_tree($catid);
    $data = $db->fetch('SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE.'_user AS b USING(userid) WHERE ( '.time().' BETWEEN starttime AND endtime '.iif($type, "AND type='".$type."'").' '.iif(count($cattree), ' AND catid IN ('.@implode(',', $cattree).') ').' '.section_filter().'  ) ORDER BY hits DESC LIMIT '.iif($start, $start.',').$count);

    articles_print($data, 'functions/'.$template);
}

//Beste Artikel: Bewertung
function articles_best_rating($count = 5, $start = 0, $catid = false, $type = false, $template = 'best_rating')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $catid = (int) $catid;
    if (!$apx->is_module('ratings')) {
        return;
    }
    if (!in_array($type, [false, 'normal', 'preview', 'review'])) {
        $type = false;
    }

    $cattree = articles_tree($catid);
    $data = $db->fetch('SELECT avg(rating) AS rating,count(*) AS votes,b.* FROM '.PRE.'_ratings AS a LEFT JOIN '.PRE."_articles AS b ON a.mid=b.id AND a.module='articles' WHERE ( ".time().' BETWEEN b.starttime AND b.endtime '.iif($type, "AND type='".$type."'").' '.iif(count($cattree), ' AND b.catid IN ('.@implode(',', $cattree).') ').' '.section_filter().'  ) GROUP BY a.mid ORDER BY rating DESC,votes DESC LIMIT '.iif($start, $start.',').$count);

    articles_print($data, 'functions/'.$template);
}

//Ähnliche Artikel ausgeben
function articles_similar($tagids = [], $count = 5, $start = 0, $catid = false, $type = false, $template = 'similar')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $catid = (int) $catid;

    if (!is_array($tagids)) {
        $tagids = getTagIds(strval($tagids));
    }
    $ids = articles_search_tags($tagids);
    $ids[] = -1;
    $tagfilter = ' AND a.id IN ('.implode(', ', $ids).') ';

    if (!in_array($type, [false, 'normal', 'preview', 'review'])) {
        $type = false;
    }

    $cattree = articles_tree($catid);
    $data = $db->fetch('SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE.'_user AS b USING(userid) WHERE ( '.time().' BETWEEN starttime AND endtime '.iif($type, "AND type='".$type."'").' '.iif(count($cattree), ' AND catid IN ('.@implode(',', $cattree).') ').' '.$tagfilter.' '.section_filter().' ) ORDER BY starttime DESC LIMIT '.iif($start, $start.',').$count);

    articles_print($data, 'functions/'.$template);
}

//Beste Tests ausgeben
function articles_reviews($sortway = 2, $count = 5, $start = 0, $catid = false, $template = 'bestreviews')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    $catid = (int) $catid;
    $sortway = (int) $sortway;
    if (1 != $sortway && 2 != $sortway) {
        $sortway = 2;
    } //Standardmäßige Sortierrichtung

    $cattree = articles_tree($catid);

    $fields = 'a.*,b.userid,b.username,b.email,b.pub_hidemail';
    $fields .= ',c.custom1,c.custom2,c.custom3,c.custom4,c.custom5,c.custom6,c.custom7,c.custom8,c.custom9,c.custom10,c.final_rate,c.award';
    $data = $db->fetch('SELECT '.$fields.' FROM '.PRE.'_articles AS a LEFT JOIN '.PRE.'_user AS b USING(userid) LEFT JOIN '.PRE.'_articles_reviews AS c ON a.id=c.artid WHERE ( '.time()." BETWEEN starttime AND endtime AND type='review' ".iif(count($cattree), ' AND catid IN ('.@implode(',', $cattree).') ').' '.section_filter().' ) ORDER BY CAST(c.final_rate AS DECIMAL(4,1) ) '.iif(1 == $sortway, 'ASC', 'DESC').' LIMIT '.iif($start, $start.',').$count);

    articles_print($data, 'functions/'.$template, true);
}

//Artikel zu einem Produkt
function articles_product($prodid = 0, $count = 5, $start = 0, $catid = false, $type = false, $template = 'productarticles')
{
    global $set,$db,$apx,$user;
    $prodid = (int) $prodid;
    $count = (int) $count;
    $start = (int) $start;
    $catid = (int) $catid;
    if (!$prodid) {
        return;
    }
    if (!in_array($type, [false, 'normal', 'preview', 'review'])) {
        $type = false;
    }

    $cattree = articles_tree($catid);
    $data = $db->fetch('SELECT a.*,b.userid,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE.'_user AS b USING(userid) WHERE ( '.time()." BETWEEN starttime AND endtime AND prodid='".$prodid."' ".iif($type, "AND type='".$type."'").' '.iif(count($cattree), ' AND catid IN ('.@implode(',', $cattree).') ').' '.section_filter().' ) ORDER BY starttime DESC LIMIT '.iif($start, $start.',').$count);

    articles_print($data, 'functions/'.$template);
}

//AUSGABE
function articles_print($data, $template, $bestreviews = false)
{
    global $set,$db,$apx,$user;
    $tmpl = new tengine();

    $apx->lang->drop('global', 'articles');

    //Verwendete Variablen auslesen
    $parse = $apx->tmpl->used_vars($template, 'articles');

    //Datensatz erweitern durch Preview/Review-Daten, nur wenn keine Reviewbestenliste
    if (!$bestreviews) {
        $data = articles_extend_data($data, $parse);
    }

    //Kategorien auslesen
    if (in_array('ARTICLE.CATID', $parse) || in_array('ARTICLE.CATTITLE', $parse) || in_array('ARTICLE.CATICON', $parse) || in_array('ARTICLE.CATLINK', $parse)) {
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

    $tmpl->assign('ARTICLE', $tabledata);
    $tmpl->parse($template, 'articles');
}

//Kategorien auflisten
function articles_categories($catid = false, $template = 'categories')
{
    global $set,$db,$apx,$user;
    $tmpl = new tengine();
    $catid = (int) $catid;

    //Eine bestimmte Kategorie
    if ($catid && $set['articles']['subcats']) {
        require_once BASEDIR.'lib/class.recursivetree.php';
        $tree = new RecursiveTree(PRE.'_articles_cat', 'id');
        $data = $tree->getTree(['*'], $catid);
    }

    //Alle Kategorien
    else {
        $data = articles_catinfo();
    }

    foreach ($data as $cat) {
        ++$i;

        //Kategorie-Link
        if (isset($cat['link'])) {
            $link = $cat['link'];
        } else {
            $link = mklink(
                'articles.php?catid='.$cat['id'],
                'articles,0,'.$cat['id'].',1.html'
            );
        }

        $catdata[$i]['ID'] = $cat['id'];
        $catdata[$i]['TITLE'] = $cat['title'];
        $catdata[$i]['ICON'] = $cat['icon'];
        $catdata[$i]['LINK'] = $link;
        $catdata[$i]['LEVEL'] = $cat['level'];
    }

    $tmpl->assign('CATEGORY', $catdata);
    $tmpl->parse('functions/'.$template, 'articles');
}

//Tags auflisten
function articles_tagcloud($count = 10, $type = false, $random = false, $template = 'tagcloud')
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
    if ($apx->section_id() || in_array($type, ['normal', 'review', 'preview'])) {
        $data = $db->fetch('
			SELECT t.tagid, t.tag, count(nt.id) AS weight
			FROM '.PRE.'_articles_tags AS nt
			LEFT JOIN '.PRE.'_articles AS n ON nt.id=n.id
			LEFT JOIN '.PRE.'_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN '.PRE.'_articles_tags AS nt2 ON nt.tagid=nt2.tagid
			WHERE 1 '.section_filter(true, 'n.secid').' '.iif($type, " AND n.type='".$type."' ").'
			GROUP BY nt.tagid
			ORDER BY '.$orderby.'
			LIMIT '.$count.'
		');
    }

    //Keine Sektion gewählt
    else {
        $data = $db->fetch('
			SELECT t.tagid, t.tag, count(nt.id) AS weight
			FROM '.PRE.'_articles_tags AS nt
			LEFT JOIN '.PRE.'_tags AS t ON nt.tagid=t.tagid
			LEFT JOIN '.PRE.'_articles_tags AS nt2 ON nt.tagid=nt2.tagid
			GROUP BY nt.tagid
			ORDER BY '.$orderby.'
			LIMIT '.$count.'
		');
    }

    if (count($data)) {
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
    $tmpl->parse('functions/'.$template, 'articles');
}

//Statistik anzeigen
function articles_stats($template = 'stats')
{
    global $set,$db,$apx,$user;
    $tmpl = new tengine();
    $parse = $tmpl->used_vars('functions/'.$template, 'articles');

    $apx->lang->drop('func_stats', 'articles');

    if (in_array('COUNT_CATEGORIES', $parse)) {
        list($count) = $db->first('SELECT count(id) FROM '.PRE.'_articles_cat');
        $tmpl->assign('COUNT_CATEGORIES', $count);
    }
    if (in_template(['COUNT_ARTICLES', 'AVG_HITS'], $parse)) {
        list($count, $hits) = $db->first('
			SELECT count(id), avg(hits) FROM '.PRE.'_articles
			WHERE '.time().' BETWEEN starttime AND endtime
		');
        $tmpl->assign('COUNT_ARTICLES', $count);
        $tmpl->assign('AVG_HITS', round($hits));
    }
    if (in_array('COUNT_NORMAL', $parse)) {
        list($count) = $db->first('
			SELECT count(id), avg(hits) FROM '.PRE.'_articles
			WHERE '.time()." BETWEEN starttime AND endtime AND type='normal'
		");
        $tmpl->assign('COUNT_NORMAL', $count);
    }
    if (in_array('COUNT_REVIEWS', $parse)) {
        list($count) = $db->first('
			SELECT count(id), avg(hits) FROM '.PRE.'_articles
			WHERE '.time()." BETWEEN starttime AND endtime AND type='review'
		");
        $tmpl->assign('COUNT_REVIEWS', $count);
    }
    if (in_array('COUNT_PREVIEWS', $parse)) {
        list($count) = $db->first('
			SELECT count(id), avg(hits) FROM '.PRE.'_articles
			WHERE '.time()." BETWEEN starttime AND endtime AND type='preview'
		");
        $tmpl->assign('COUNT_PREVIEWS', $count);
    }

    $tmpl->parse('functions/'.$template, 'articles');
}
