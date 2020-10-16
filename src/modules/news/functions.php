<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Aktuelle News auf Seite 1 herausfiltern
function news_recent()
{
    global $set,$db,$apx,$user;
    static $recent;
    if (isset($recent)) {
        return $recent;
    }
    if (!$set['news']['epp']) {
        return [];
    }
    $data = $db->fetch('SELECT id,IF(sticky>='.time().',1,0) AS sticky FROM '.PRE.'_news WHERE ( '.time().' BETWEEN starttime AND endtime '.section_filter().' ) ORDER BY sticky DESC,starttime DESC LIMIT '.$set['news']['epp']);
    if (!count($data)) {
        return [];
    }
    $recent = [];
    foreach ($data as $res) {
        $recent[] = $res['id'];
    }

    return $recent;
}

//Prüfen ob eine News auf Seite 1 ist
function news_is_recent($id)
{
    $recent = news_recent();
    if (in_array($id, $recent)) {
        return true;
    }
    if (!count($recent)) {
        return true;
    }

    return false;
}

//Kategorien-Informationen
function news_catinfo($id = false)
{
    global $set,$db,$apx,$user;

    //Eine Kategorie
    if (is_int($id) || is_string($id)) {
        $id = (int) $id;
        if (isset($catinfo[$id])) {
            return $catinfo[$id];
        }
        $res = $db->first('SELECT id,title,icon,open FROM '.PRE."_news_cat WHERE ( id='".$id."' ) LIMIT 1", 1);
        $catinfo[$id] = $res;
        $catinfo[$id]['link'] = mklink(
            'news.php?catid='.$res['id'],
            'news,'.$res['id'].',1.html'
        );

        return $catinfo[$id];
    }

    //Mehrere Kategorien
    if (is_array($id)) {
        if (!count($id)) {
            return [];
        }
        $data = $db->fetch('SELECT id,title,icon,open FROM '.PRE.'_news_cat WHERE id IN ('.implode(',', $id).')');
        if (!count($data)) {
            return [];
        }
        foreach ($data as $res) {
            $catinfo[$res['id']] = $res;
            $catinfo[$res['id']]['link'] = mklink(
                'news.php?catid='.$res['id'],
                'news,'.$res['id'].',1.html'
            );
        }

        return $catinfo;
    }

    //Alle Kategorien;

    if ($set['news']['subcats']) {
        require_once BASEDIR.'lib/class.recursivetree.php';
        $tree = new RecursiveTree(PRE.'_news_cat', 'id');
        $data = $tree->getTree(['*']);
    } else {
        $data = $db->fetch('SELECT * FROM '.PRE.'_news_cat ORDER BY title ASC');
    }
    if (!count($data)) {
        return [];
    }
    foreach ($data as $res) {
        $catinfo[$res['id']] = $res;
        $catinfo[$res['id']]['link'] = mklink(
            'news.php?catid='.$res['id'],
            'news,'.$res['id'].',1.html'
        );
    }

    return $catinfo;
}

//Kategorie-Baum holen
function news_tree($catid)
{
    global $set,$db,$apx,$user;
    static $saved;
    $catid = (int) $catid;

    $catid = (int) $catid;
    if (!$catid) {
        return [];
    }
    if (!$set['news']['subcats']) {
        return [$catid];
    }
    if (isset($saved[$catid])) {
        return $saved[$catid];
    }
    require_once BASEDIR.'lib/class.recursivetree.php';
    $tree = new RecursiveTree(PRE.'_news_cat', 'id');
    $data = $tree->getTree(['title', 'open']);
    $cattree = $tree->getChildrenIds($catid);
    $cattree[] = $catid;

    $saved[$catid] = $cattree;

    return $cattree;
}

//Links generieren
function news_links($res)
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

//News zu Tags suchen
function news_search_tags($tagids, $conn = 'or')
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
			FROM '.PRE.'_news_tags
			WHERE tagid IN ('.implode(', ', $tagids).')
		');
        $ids = get_ids($data, 'id');
    } else {
        $data = $db->fetch('
			SELECT id, tagid, count(id) AS hits
			FROM '.PRE.'_news_tags
			WHERE tagid IN ('.implode(', ', $tagids).')
			GROUP BY id
			HAVING hits='.count($tagids).'
		');
        $ids = get_ids($data, 'id');
    }

    return $ids;
}

//Nach Übereinstimmungen in den Tags suchen
function news_match_tags($items)
{
    global $set,$db,$apx,$user;
    if (!is_array($items)) {
        return [];
    }
    $result = [];
    foreach ($items as $item) {
        $data = $db->fetch('
			SELECT DISTINCT at.id
			FROM '.PRE.'_news_tags AS at
			LEFT JOIN '.PRE."_tags AS t USING(tagid)
			WHERE t.tag LIKE '%".addslashes_like($item)."%'
		");
        $result[$item] = get_ids($data, 'id');
    }

    return $result;
}

//Tags zu einer News auslesen
function news_tags($id)
{
    global $set,$db,$apx,$user;
    $tagdata = [];
    $tagids = [];
    $tags = [];
    $data = $db->fetch('
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM '.PRE.'_news_tags AS nt
		LEFT JOIN '.PRE.'_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN '.PRE.'_news_tags AS nt2 ON nt.tagid=nt2.tagid
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

//Newspic generieren
function news_newspic($newspic)
{
    global $set,$db,$apx,$user;
    if (!$newspic) {
        return [];
    }
    $picture = getpath('uploads').$newspic;
    $poppic = str_replace('-thumb.', '.', $newspic);

    if ($set['news']['newspic_popup'] && false !== strpos($newspic, '-thumb.') && file_exists(BASEDIR.getpath('uploads').$poppic)) {
        $size = getimagesize(BASEDIR.getpath('uploads').$poppic);
        $picture_popup = "javascript:popupwin('misc.php?action=picture&amp;pic=".$poppic."','".$size[0]."','".$size[1]."')";
    } else {
        $poppic = '';
    }

    return [$picture, $picture_popup, iif($poppic, HTTPDIR.getpath('uploads').$poppic)];
}

//Kommentar-Seite
function news_showcomments($id)
{
    global $set,$db,$apx,$user;
    $id = (int) $id;

    $res = $db->first('SELECT id,allowcoms FROM '.PRE."_news WHERE ( id='".$id."' ".section_filter().' ) LIMIT 1');
    if (!$apx->is_module('comments') || !$set['news']['coms'] || !$res['allowcoms']) {
        return;
    }
    require_once BASEDIR.getmodulepath('comments').'class.comments.php';
    $coms = new comments('news', $id);
    $coms->assign_comments();
    if (!news_is_recent($id) && !$set['news']['archcoms']) {
        $apx->tmpl->assign('COMMENT_NOFORM', 1);
    }

    $apx->tmpl->parse('comments', 'comments');
    require 'lib/_end.php';
}
