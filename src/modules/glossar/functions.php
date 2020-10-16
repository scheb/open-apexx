<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Anfangsbuchstabe bestimmen
function glossar_letter($text)
{
    $first = $text[0];
    $first = strtoupper($first);
    if ('Ä' == $first) {
        return 'A';
    }
    if ('Ö' == $first) {
        return 'O';
    }
    if ('Ü' == $first) {
        return 'U';
    }
    if (!preg_match('#^[A-Z]$#', $first)) {
        return '#';
    }

    return $first;
}

//Begriffe zu Tags suchen
function glossar_search_tags($tagids, $conn = 'or')
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
			FROM '.PRE.'_glossar_tags
			WHERE tagid IN ('.implode(', ', $tagids).')
		');
        $ids = get_ids($data, 'id');
    } else {
        $data = $db->fetch('
			SELECT id, tagid, count(id) AS hits
			FROM '.PRE.'_glossar_tags
			WHERE tagid IN ('.implode(', ', $tagids).')
			GROUP BY id
			HAVING hits='.count($tagids).'
		');
        $ids = get_ids($data, 'id');
    }

    return $ids;
}

//Tags zu einem Begriff auslesen
function glossar_tags($id)
{
    global $set,$db,$apx,$user;
    $tagdata = [];
    $tagids = [];
    $tags = [];
    $data = $db->fetch('
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM '.PRE.'_glossar_tags AS nt
		LEFT JOIN '.PRE.'_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN '.PRE.'_glossar_tags AS nt2 ON nt.tagid=nt2.tagid
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

//Nach Übereinstimmungen in den Tags suchen
function glossar_match_tags($items)
{
    global $set,$db,$apx,$user;
    if (!is_array($items)) {
        return [];
    }
    $result = [];
    foreach ($items as $item) {
        $data = $db->fetch('
			SELECT DISTINCT at.id
			FROM '.PRE.'_glossar_tags AS at
			LEFT JOIN '.PRE."_tags AS t USING(tagid)
			WHERE t.tag LIKE '%".addslashes_like($item)."%'
		");
        $result[$item] = get_ids($data, 'id');
    }

    return $result;
}

//Kommentarseite
function glossar_showcomments($id)
{
    global $set,$db,$apx,$user;
    $id = (int) $id;

    $res = $db->first('SELECT id,allowcoms FROM '.PRE."_glossar WHERE ( id='".$id."' AND starttime!='0' ) LIMIT 1");
    if (!$apx->is_module('comments') || !$set['glossar']['coms'] || !$res['allowcoms']) {
        return;
    }
    require_once BASEDIR.getmodulepath('comments').'class.comments.php';
    $coms = new comments('glossar', $id);
    $coms->assign_comments();

    $apx->tmpl->parse('comments', 'comments');
    require 'lib/_end.php';
}
