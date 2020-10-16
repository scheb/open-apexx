<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Aktuelle Artikel auf Seite 1 herausfiltern
function articles_recent()
{
    global $set,$db,$apx,$user;
    static $recent;
    if (isset($recent)) {
        return $recent;
    }
    if (!$set['articles']['epp']) {
        return [];
    }
    $data = $db->fetch('SELECT id,IF(sticky>='.time().',1,0) AS sticky FROM '.PRE.'_articles WHERE ( '.time().' BETWEEN starttime AND endtime '.section_filter().' ) ORDER BY sticky DESC,starttime DESC LIMIT '.$set['articles']['epp']);
    if (!count($data)) {
        return [];
    }
    $recent = [];
    foreach ($data as $res) {
        $recent[] = $res['id'];
    }

    return $recent;
}

//Prüfen ob ein Artikel auf Seite 1 ist
function articles_is_recent($id)
{
    $recent = articles_recent();
    if (in_array($id, $recent)) {
        return true;
    }
    if (!count($recent)) {
        return true;
    }

    return false;
}

//Kategorien-Informationen
function articles_catinfo($id = false)
{
    global $set,$db,$apx,$user;

    //Eine Kategorie
    if (is_int($id) || is_string($id)) {
        $id = (int) $id;
        if (isset($catinfo[$id])) {
            return $catinfo[$id];
        }
        $res = $db->first('SELECT id,title,icon,open FROM '.PRE."_articles_cat WHERE ( id='".$id."' ) LIMIT 1", 1);
        $catinfo[$id] = $res;
        $catinfo[$id]['link'] = mklink(
            'articles.php?catid='.$res['id'],
            'articles,0,'.$res['id'].',1.html'
        );

        return $catinfo[$id];
    }

    //Mehrere Kategorien
    if (is_array($id)) {
        if (!count($id)) {
            return [];
        }
        $data = $db->fetch('SELECT id,title,icon,open FROM '.PRE.'_articles_cat WHERE id IN ('.implode(',', $id).')');
        if (!count($data)) {
            return [];
        }
        foreach ($data as $res) {
            $catinfo[$res['id']] = $res;
            $catinfo[$res['id']]['link'] = mklink(
                'articles.php?catid='.$res['id'],
                'articles,0,'.$res['id'].',1.html'
            );
        }

        return $catinfo;
    }

    //Alle Kategorien

    if ($set['articles']['subcats']) {
        require_once BASEDIR.'lib/class.recursivetree.php';
        $tree = new RecursiveTree(PRE.'_articles_cat', 'id');
        $data = $tree->getTree(['title', 'icon', 'open']);
    } else {
        $data = $db->fetch('SELECT * FROM '.PRE.'_articles_cat ORDER BY title ASC');
    }
    if (!count($data)) {
        return [];
    }
    foreach ($data as $res) {
        $catinfo[$res['id']] = $res;
        $catinfo[$res['id']]['link'] = mklink(
            'articles.php?catid='.$res['id'],
            'articles,0,'.$res['id'].',1.html'
        );
    }

    return $catinfo;
}

//Kategorie-Baum holen
function articles_tree($catid)
{
    global $set,$db,$apx,$user;
    static $saved;
    $catid = (int) $catid;

    $catid = (int) $catid;
    if (!$catid) {
        return [];
    }
    if (!$set['articles']['subcats']) {
        return [$catid];
    }
    if (isset($saved[$catid])) {
        return $saved[$catid];
    }
    require_once BASEDIR.'lib/class.recursivetree.php';
    $tree = new RecursiveTree(PRE.'_articles_cat', 'id');
    $cattree = $tree->getChildrenIds($catid);
    $cattree[] = $catid;

    $saved[$catid] = $cattree;

    return $cattree;
}

//Artikel zu Tags suchen
function articles_search_tags($tagids, $conn = 'or')
{
    global $set,$db,$apx,$user;
    if (!is_array($tagids)) {
        return [];
    }
    $tagids = array_map('intval', $tagids);
    if (!$tagids) {
        return [];
    }
    if ('or' == $conn) {
        $data = $db->fetch('
			SELECT DISTINCT id
			FROM '.PRE.'_articles_tags
			WHERE tagid IN ('.implode(', ', $tagids).')
		');
        $ids = get_ids($data, 'id');
    } else {
        $data = $db->fetch('
			SELECT id, tagid, count(id) AS hits
			FROM '.PRE.'_articles_tags
			WHERE tagid IN ('.implode(', ', $tagids).')
			GROUP BY id
			HAVING hits='.count($tagids).'
		');
        $ids = get_ids($data, 'id');
    }

    return $ids;
}

//Nach Übereinstimmungen in den Tags suchen
function articles_match_tags($items)
{
    global $set,$db,$apx,$user;
    if (!is_array($items)) {
        return [];
    }
    $result = [];
    foreach ($items as $item) {
        $data = $db->fetch('
			SELECT DISTINCT at.id
			FROM '.PRE.'_articles_tags AS at
			LEFT JOIN '.PRE."_tags AS t USING(tagid)
			WHERE t.tag LIKE '%".addslashes_like($item)."%'
		");
        $result[$item] = get_ids($data, 'id');
    }

    return $result;
}

//Tags zu einem Artikel auslesen
function articles_tags($id)
{
    global $set,$db,$apx,$user;
    $tagdata = [];
    $tagids = [];
    $tags = [];
    $data = $db->fetch('
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM '.PRE.'_articles_tags AS nt
		LEFT JOIN '.PRE.'_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN '.PRE.'_articles_tags AS nt2 ON nt.tagid=nt2.tagid
		WHERE nt.id='.intval($id).'
		GROUP BY nt.tagid
		ORDER BY t.tag ASC
	');
    if (count($data)) {
        $maxweight = 1;
        foreach ($data as $res) {
            if ($res['weight'] > $maxweight) {
                $maxweight = $res['weight'];
            }
        }
        foreach ($data as $res) {
            $tags[] = $res['tag'];
            $tagids[] = $res['tagid'];
            $tagdata[] = [
                'ID' => $res['tagid'],
                'NAME' => replace($res['tag']),
                'WEIGHT' => $res['weight'] / $maxweight,
            ];
        }
    }

    return [$tagdata, $tagids, implode(', ', $tags)];
}

//Artikelpic generieren
function articles_artpic($artpic)
{
    global $set,$db,$apx,$user;
    if (!$artpic) {
        return [];
    }
    $picture = getpath('uploads').$artpic;
    $poppic = str_replace('-thumb.', '.', $artpic);

    if ($set['articles']['artpic_popup'] && false !== strpos($artpic, '-thumb.') && file_exists(BASEDIR.getpath('uploads').$poppic)) {
        $size = getimagesize(BASEDIR.getpath('uploads').$poppic);
        $picture_popup = "javascript:popupwin('misc.php?action=picture&amp;pic=".$poppic."','".$size[0]."','".$size[1]."')";
    } else {
        $poppic = '';
    }

    return [$picture, $picture_popup, iif($poppic, HTTPDIR.getpath('uploads').$poppic)];
}

//Links generieren
function articles_links($res)
{
    $res = unserialize($res);
    if (!is_array($res) || !count($res)) {
        return [];
    }
    foreach ($res as $link) {
        ++$i;
        $linkdata[$i]['TITLE'] = $link['title'];
        $linkdata[$i]['TEXT'] = $link['text'];
        $linkdata[$i]['URL'] = $link['url'];
        $linkdata[$i]['POPUP'] = $link['popup'];
    }

    return $linkdata;
}

//Bilderserie
function articles_picseries($data, $artid, $link2file)
{
    global $set,$db,$apx,$user;
    $data = unserialize($data);
    if (!is_array($data)) {
        return [];
    }
    $artid = (int) $artid;

    //Keys eintragen
    foreach ($data as $key => $res) {
        $data[$key]['id'] = $key;
    }

    //Zufallssortierung
    shuffle($data);

    //Ausgabe vorbereiten
    foreach ($data as $res) {
        ++$i;
        $link = $link2file.'.php?id='.$artid.'&amp;pic='.$res['id'].iif($apx->section_id(), '&amp;sec='.$apx->section_id());
        $picdata[$i]['IMAGE'] = HTTPDIR.getpath('uploads').$res['thumbnail'];
        $picdata[$i]['FULLSIZE'] = HTTPDIR.getpath('uploads').$res['picture'];
        $picdata[$i]['LINK'] = "javascript:popupwin('".$link."','".$set['articles']['picwidth']."','".$set['articles']['picheight']."',".iif($set['articles']['popup_resizeable'], 1, 0).')';
    }

    return $picdata;
}

//Bilderserie: Seiten + Weiter/Zurück generieren
function articles_picseries_pages($data, $link2file, $artid)
{
    global $set,$db,$apx,$user;
    $id = (int) $id;

    $pages = count($data);
    foreach ($data as $picid => $res) {
        ++$i;
        $res['id'] = $picid;

        //Seitenzahlen
        $pagedata[$i]['NUMBER'] = $i;
        $pagedata[$i]['LINK'] = $link2file.'.php?id='.$artid.'&amp;pic='.$res['id'].iif($apx->section_id(), '&amp;sec='.$apx->section_id());

        //Nächste Seite
        if (false === $current['next']) {
            $current['next'] = [
                'link' => $link2file.'.php?id='.$artid.'&amp;pic='.$res['id'].iif($apx->section_id(), '&amp;sec='.$apx->section_id()),
                'preview' => HTTPDIR.getpath('uploads').$res['thumbnail'],
            ];
        }

        //Vorherige Seite
        if ($_REQUEST['pic'] == $res['id']) {
            $selected = $i;
            $current['next'] = false;

            if ($last) {
                $current['prev'] = [
                    'link' => $link2file.'.php?id='.$artid.'&amp;pic='.$last['id'].iif($apx->section_id(), '&amp;sec='.$apx->section_id()),
                    'preview' => HTTPDIR.getpath('uploads').$last['thumbnail'],
                ];
            }
        }

        //Erste Seite
        if (1 == $i) {
            $link_first = $link2file.'.php?id='.$artid.'&amp;pic='.$res['id'].iif($apx->section_id(), '&amp;sec='.$apx->section_id());
        }

        //Letzte Seite
        if ($i == $pages) {
            $link_last = $link2file.'.php?id='.$artid.'&amp;pic='.$res['id'].iif($apx->section_id(), '&amp;sec='.$apx->section_id());
        }

        $last = $res;
    }

    $apx->tmpl->assign('PICTURE', $pagedata);
    $apx->tmpl->assign('PICTURE_COUNT', $pages);
    $apx->tmpl->assign('PICTURE_SELECTED', $selected);

    //Vorherige Seite
    if ($current['prev']) {
        $apx->tmpl->assign('PICTURE_PREVIOUS', $current['prev']['link']);
        $apx->tmpl->assign('PICTURE_PREVIOUS_PREVIEW', $current['prev']['preview']);
    }

    //Nächste Seite
    if ($current['next']) {
        $apx->tmpl->assign('PICTURE_NEXT', $current['next']['link']);
        $apx->tmpl->assign('PICTURE_NEXT_PREVIEW', $current['next']['preview']);
    }

    $apx->tmpl->assign('PICTURE_FIRST', $link_first);
    $apx->tmpl->assign('PICTURE_LAST', $link_last);
}

//Index erstellen
function articles_index($id, $arttitle, $link2file)
{
    global $set,$db,$apx,$user;
    $id = (int) $id;

    $data = $db->fetch('SELECT title FROM '.PRE."_articles_pages WHERE artid='".$id."' ORDER BY ord ASC");
    if (count($data)) {
        //Fazit hinzufügen bei Reviews
        if (($set['articles']['reviews_conclusionpage'] && 'reviews' == $link2file) || ($set['articles']['previews_conclusionpage'] && 'previews' == $link2file)) {
            $data[] = [
                'title' => $apx->lang->get('CONCLUSION'),
            ];
        }

        foreach ($data as $res) {
            ++$i;

            $link = mklink(
                $link2file.'.php?id='.$id.'&amp;page='.$i,
                $link2file.',id'.$id.','.$i.urlformat($arttitle).'.html'
            );

            $indexdata[$i]['NUMBER'] = $i;
            $indexdata[$i]['TITLE'] = $res['title'];
            $indexdata[$i]['LINK'] = $link;
        }
    }

    return $indexdata;
}

//Datensatz durch Preview/Review-Daten erweitern
function articles_extend_data($data, $parse)
{
    global $set,$db,$apx,$user;
    if (!is_array($data) || !count($data)) {
        return $data;
    }
    //Prüfen ob die Daten benötigt werden
    $preview_needed = $review_needed = false;
    for ($i = 1; $i <= 10; ++$i) {
        if (in_array('ARTICLE.CUSTOM'.$i, $parse)) {
            $preview_needed = $review_needed = true;
        }
    }
    if (in_array('ARTICLE.IMPRESSION', $parse)) {
        $preview_needed = true;
    }
    if (in_array('ARTICLE.FINAL_RATING', $parse)) {
        $review_needed = true;
    }
    if (in_array('ARTICLE.AWARD', $parse)) {
        $review_needed = true;
    }
    if (in_array('ARTICLE.POSITIVE', $parse)) {
        $review_needed = true;
    }
    if (in_array('ARTICLE.NEGATIVE', $parse)) {
        $review_needed = true;
    }
    if (!$preview_needed && !$review_needed) {
        return $data;
    } //Keine Daten benötigt
    //Previews und Reviews herausfiltern
    $previews = $reviews = $fit = [];
    foreach ($data as $key => $res) {
        if ($preview_needed && 'preview' == $res['type']) {
            $previews[] = $res['id'];
        }
        if ($review_needed && 'review' == $res['type']) {
            $reviews[] = $res['id'];
        }
        $fit[$res['id']] = $key; //Datensatz Zugehörigkeit
    }

    //Daten selektieren und einfügen
    if (count($previews)) {
        $previewdata = $db->fetch('SELECT artid,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10,impression FROM '.PRE.'_articles_previews WHERE artid IN ('.implode(',', $previews).')', 1);
        if (count($previewdata)) {
            foreach ($previewdata as $res) {
                $key = $fit[$res['artid']];
                $data[$key] = array_merge($data[$key], $res);
            }
        }
    }

    if (count($reviews)) {
        $reviewdata = $db->fetch('SELECT artid,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10,final_rate,positive,negative,award FROM '.PRE.'_articles_reviews WHERE artid IN ('.implode(',', $reviews).')', 1);
        if (count($reviewdata)) {
            foreach ($reviewdata as $res) {
                $key = $fit[$res['artid']];
                $data[$key] = array_merge($data[$key], $res);
            }
        }
    }

    return $data;
}

//Kommentar-Seite
function articles_showcomments($id)
{
    global $set,$db,$apx,$user;
    $id = (int) $id;

    $res = $db->first('SELECT id,allowcoms FROM '.PRE."_articles WHERE ( id='".$id."' ".section_filter().' ) LIMIT 1');
    if (!$apx->is_module('comments') || !$set['articles']['coms'] || !$res['allowcoms']) {
        return;
    }
    require_once BASEDIR.getmodulepath('comments').'class.comments.php';
    $coms = new comments('articles', $id);
    $coms->assign_comments();
    if (!articles_recent($id) && !$set['articles']['archcoms']) {
        $apx->tmpl->assign('COMMENT_NOFORM', 1);
    }

    $apx->tmpl->parse('comments', 'comments');
    require 'lib/_end.php';
}
