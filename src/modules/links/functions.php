<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Kategorien-Informationen
function links_catinfo($id = false)
{
    global $set,$db,$apx,$user;

    //Eine Kategorie
    if (is_int($id) || is_string($id)) {
        $id = (int) $id;
        if (isset($catinfo[$id])) {
            return $catinfo[$id];
        }
        $res = $db->first('SELECT id,title,icon,open FROM '.PRE."_links_cat WHERE ( id='".$id."' ) LIMIT 1", 1);
        $catinfo[$id] = $res;
        $catinfo[$id]['link'] = mklink(
            'links.php?catid='.$res['id'],
            'links,'.$res['id'].',1.html'
        );

        return $catinfo[$id];
    }

    //Mehrere Kategorien
    if (is_array($id)) {
        if (!count($id)) {
            return [];
        }
        $data = $db->fetch('SELECT id,title,icon,open FROM '.PRE.'_links_cat WHERE id IN ('.implode(',', $id).')');
        if (!count($data)) {
            return [];
        }
        foreach ($data as $res) {
            $catinfo[$res['id']] = $res;
            $catinfo[$res['id']]['link'] = mklink(
                'links.php?catid='.$res['id'],
                'links,'.$res['id'].',1.html'
            );
        }

        return $catinfo;
    }

    //Alle Kategorien;

    if ($set['links']['subcats']) {
        require_once BASEDIR.'lib/class.recursivetree.php';
        $tree = new RecursiveTree(PRE.'_links_cat', 'id');
        $data = $tree->getTree(['*']);
    } else {
        $data = $db->fetch('SELECT * FROM '.PRE.'_links_cat ORDER BY title ASC');
    }
    if (!count($data)) {
        return [];
    }
    foreach ($data as $res) {
        $catinfo[$res['id']] = $res;
        $catinfo[$res['id']]['link'] = mklink(
            'links.php?catid='.$res['id'],
            'links,'.$res['id'].',1.html'
        );
    }

    return $catinfo;
}

//Pfad holen
function links_path($id)
{
    global $set,$db,$apx,$user;
    $id = (int) $id;
    if (!$id) {
        return [];
    }
    require_once BASEDIR.'lib/class.recursivetree.php';
    $tree = new RecursiveTree(PRE.'_links_cat', 'id');
    $data = $tree->getPathTo(['title'], $id);
    if (!count($data)) {
        return [];
    }
    foreach ($data as $res) {
        ++$i;

        $pathdata[$i]['TITLE'] = $res['title'];
        $pathdata[$i]['LINK'] = mklink(
            'links.php?catid='.$res['id'],
            'links,'.$res['id'].',1'.urlformat($res['title']).'.html'
        );
    }

    return $pathdata;
}

//Kategorie-Baum holen
function links_tree($catid)
{
    global $set,$db,$apx,$user;
    static $saved;
    $catid = (int) $catid;

    $catid = (int) $catid;
    if (!$catid) {
        return [];
    }
    if (isset($saved[$catid])) {
        return $saved[$catid];
    }
    $cattree = [];
    require_once BASEDIR.'lib/class.recursivetree.php';
    $tree = new RecursiveTree(PRE.'_links_cat', 'id');
    $cattree = $tree->getChildrenIds($catid);
    $cattree[] = $catid;

    $saved[$catid] = $cattree;

    return $cattree;
}

//Links zu Tags suchen
function links_search_tags($tagids, $conn = 'or')
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
			FROM '.PRE.'_links_tags
			WHERE tagid IN ('.implode(', ', $tagids).')
		');
        $ids = get_ids($data, 'id');
    } else {
        $data = $db->fetch('
			SELECT id, tagid, count(id) AS hits
			FROM '.PRE.'_links_tags
			WHERE tagid IN ('.implode(', ', $tagids).')
			GROUP BY id
			HAVING hits='.count($tagids).'
		');
        $ids = get_ids($data, 'id');
    }

    return $ids;
}

//Nach Übereinstimmungen in den Tags suchen
function links_match_tags($items)
{
    global $set,$db,$apx,$user;
    if (!is_array($items)) {
        return [];
    }
    $result = [];
    foreach ($items as $item) {
        $data = $db->fetch('
			SELECT DISTINCT at.id
			FROM '.PRE.'_links_tags AS at
			LEFT JOIN '.PRE."_tags AS t USING(tagid)
			WHERE t.tag LIKE '%".addslashes_like($item)."%'
		");
        $result[$item] = get_ids($data, 'id');
    }

    return $result;
}

//Tags zu einem Link auslesen
function links_tags($id)
{
    global $set,$db,$apx,$user;
    $tagdata = [];
    $tagids = [];
    $tags = [];
    $data = $db->fetch('
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM '.PRE.'_links_tags AS nt
		LEFT JOIN '.PRE.'_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN '.PRE.'_links_tags AS nt2 ON nt.tagid=nt2.tagid
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

//Bild generieren
function links_linkpic($linkpic)
{
    global $set,$db,$apx,$user;
    if (!$linkpic) {
        return [];
    }
    $picture = getpath('uploads').$linkpic;
    $poppic = str_replace('-thumb.', '.', $linkpic);

    if ($set['links']['linkpic_popup'] && false !== strpos($linkpic, '-thumb.') && file_exists(BASEDIR.getpath('uploads').$poppic)) {
        $size = getimagesize(BASEDIR.getpath('uploads').$poppic);
        $picture_popup = "javascript:popupwin('misc.php?action=picture&amp;pic=".$poppic."','".$size[0]."','".$size[1]."')";
    } else {
        $poppic = '';
    }

    return [$picture, $picture_popup, iif($poppic, HTTPDIR.getpath('uploads').$poppic)];
}

//Kommentarseite
function links_showcomments($id)
{
    global $db,$tmpl,$user,$set,$apx;

    $res = $db->first('SELECT id,allowcoms FROM '.PRE."_links WHERE id='".intval($id)."' LIMIT 1");
    if (!$set['links']['coms'] || !$res['allowcoms'] || !$apx->is_module('comments')) {
        return;
    }
    require_once BASEDIR.getmodulepath('comments').'class.comments.php';
    $coms = new comments('links', $res['id']);
    $coms->assign_comments();

    $apx->tmpl->parse('comments', 'comments');
    require 'lib/_end.php';
}
