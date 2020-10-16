<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Embedcode erzeugen
function videos_embedcode($source, $id)
{
    global $set;
    require BASEDIR.getmodulepath('videos').'plattforms.php';
    if (isset($plattforms[$source])) {
        return strtr($plattforms[$source][3], [
            '{VIDEOID}' => $id,
            '{WIDTH}' => $set['videos']['embed_width'],
            '{HEIGHT}' => $set['videos']['embed_height'],
        ]);
    }

    return '';
}

//Screenshots auslesen
function videos_screenshots($id)
{
    global $set,$db,$apx,$user;

    $data = $db->fetch('SELECT thumbnail, picture FROM '.PRE."_videos_screens WHERE videoid='".$id."' ORDER BY pictureid ASC");
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            $size = getimagesize(BASEDIR.getpath('uploads').$res['picture']);

            $mirrordata[$i]['IMAGE'] = getpath('uploads').$res['thumbnail'];
            $mirrordata[$i]['FULLSIZE'] = getpath('uploads').$res['picture'];
            $mirrordata[$i]['LINK'] = "javascript:popuppic('misc.php?action=picture&amp;pic=".$res['picture']."','".$size[0]."','".$size[1]."');";
        }
    }

    return $mirrordata;
}

//Teaserpic generieren
function videos_teaserpic($teaserpic)
{
    global $set,$db,$apx,$user;
    if (!$teaserpic) {
        return [];
    }
    $picture = getpath('uploads').$teaserpic;
    $poppic = str_replace('-thumb.', '.', $teaserpic);

    if ($set['videos']['teaserpic_popup'] && false !== strpos($teaserpic, '-thumb.') && file_exists(BASEDIR.getpath('uploads').$poppic)) {
        $size = getimagesize(BASEDIR.getpath('uploads').$poppic);
        $picture_popup = "javascript:popupwin('misc.php?action=picture&amp;pic=".$poppic."','".$size[0]."','".$size[1]."')";
    } else {
        $poppic = '';
    }

    return [$picture, $picture_popup, iif($poppic, HTTPDIR.getpath('uploads').$poppic)];
}

//Video-Größe
function videos_getsize($fsize, $digits = 1)
{
    $fsize = (float) $fsize;
    if ($digits) {
        $format = '%01.'.$digits.'f';
    } else {
        $format = '%01d';
    }

    if ($fsize < 1024) {
        return $fsize.' Byte';
    }
    if ($fsize >= 1024 && $fsize < 1024 * 1024) {
        return  number_format($fsize / (1024), $digits, ',', '').' KB';
    }
    if ($fsize >= 1024 * 1024 && $fsize < 1024 * 1024 * 1024) {
        return number_format($fsize / (1024 * 1024), $digits, ',', '').' MB';
    }
    if ($fsize >= 1024 * 1024 * 1024 && $fsize < 1024 * 1024 * 1024 * 1024) {
        return number_format($fsize / (1024 * 1024 * 1024), $digits, ',', '').' GB';
    }

    return number_format($fsize / (1024 * 1024 * 1024 * 1024), $digits, ',', '').' TB';
}

//Dateigröße auslesen
function videos_filesize($info)
{
    if ('external' == $info['source']) {
        return $info['filesize'];
    }
    if ('apexx' == $info['source'] && file_exists(BASEDIR.getpath('uploads').$info['file'])) {
        return filesize(BASEDIR.getpath('uploads').$info['file']);
    }

    return 0;
}

//Video-Zeit berechnen
function videos_gettime($size, $speed)
{
    $sec = round($size / ($speed * 1000 / 8));
    if (0 == $sec) {
        return '&lt; 00:00:01';
    }
    $min = floor($sec / 60);
    $hours = floor($min / 60);

    $sec = $sec % 60;
    $min = $min % 60;

    return sprintf('%02d', $hours).':'.sprintf('%02d', $min).':'.sprintf('%02d', $sec);
}

//Kategorien-Informationen
function videos_catinfo($id = false)
{
    global $set,$db,$apx,$user;

    //Eine Kategorie
    if (is_int($id) || is_string($id)) {
        $id = (int) $id;
        if (isset($catinfo[$id])) {
            return $catinfo[$id];
        }
        $res = $db->first('SELECT id,title,icon,open FROM '.PRE."_videos_cat WHERE ( id='".$id."' ) LIMIT 1", 1);
        $catinfo[$id] = $res;
        $catinfo[$id]['link'] = mklink(
            'videos.php?catid='.$res['id'],
            'videos,'.$res['id'].',1.html'
        );

        return $catinfo[$id];
    }

    //Mehrere Kategorien
    if (is_array($id)) {
        if (!count($id)) {
            return [];
        }
        $data = $db->fetch('SELECT id,title,icon,open FROM '.PRE.'_videos_cat WHERE id IN ('.implode(',', $id).')');
        if (!count($data)) {
            return [];
        }
        foreach ($data as $res) {
            $catinfo[$res['id']] = $res;
            $catinfo[$res['id']]['link'] = mklink(
                'videos.php?catid='.$res['id'],
                'videos,'.$res['id'].',1.html'
            );
        }

        return $catinfo;
    }

    //Alle Kategorien;

    if ($set['videos']['subcats']) {
        require_once BASEDIR.'lib/class.recursivetree.php';
        $tree = new RecursiveTree(PRE.'_videos_cat', 'id');
        $data = $tree->getTree(['*']);
    } else {
        $data = $db->fetch('SELECT * FROM '.PRE.'_videos_cat ORDER BY title ASC');
    }
    if (!count($data)) {
        return [];
    }
    foreach ($data as $res) {
        $catinfo[$res['id']] = $res;
        $catinfo[$res['id']]['link'] = mklink(
            'videos.php?catid='.$res['id'],
            'videos,'.$res['id'].',1.html'
        );
    }

    return $catinfo;
}

//Pfad holen
function videos_path($id)
{
    global $set,$db,$apx,$user;
    $id = (int) $id;
    if (!$id) {
        return [];
    }
    require_once BASEDIR.'lib/class.recursivetree.php';
    $tree = new RecursiveTree(PRE.'_videos_cat', 'id');
    $data = $tree->getPathTo(['title'], $id);
    if (!count($data)) {
        return [];
    }
    foreach ($data as $res) {
        ++$i;

        $pathdata[$i]['ID'] = $res['id'];
        $pathdata[$i]['TITLE'] = $res['title'];
        $pathdata[$i]['LINK'] = mklink(
            'videos.php?catid='.$res['id'],
            'videos,'.$res['id'].',1'.urlformat($res['title']).'.html'
        );
    }

    return $pathdata;
}

//Kategorie-Baum holen
function videos_tree($catid)
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
    $tree = new RecursiveTree(PRE.'_videos_cat', 'id');
    $cattree = $tree->getChildrenIds($catid);
    $cattree[] = $catid;

    $saved[$catid] = $cattree;

    return $cattree;
}

//Videos zu Tags suchen
function videos_search_tags($tagids, $conn = 'or')
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
			FROM '.PRE.'_videos_tags
			WHERE tagid IN ('.implode(', ', $tagids).')
		');
        $ids = get_ids($data, 'id');
    } else {
        $data = $db->fetch('
			SELECT id, tagid, count(id) AS hits
			FROM '.PRE.'_videos_tags
			WHERE tagid IN ('.implode(', ', $tagids).')
			GROUP BY id
			HAVING hits='.count($tagids).'
		');
        $ids = get_ids($data, 'id');
    }

    return $ids;
}

//Nach Übereinstimmungen in den Tags suchen
function videos_match_tags($items)
{
    global $set,$db,$apx,$user;
    if (!is_array($items)) {
        return [];
    }
    $result = [];
    foreach ($items as $item) {
        $data = $db->fetch('
			SELECT DISTINCT at.id
			FROM '.PRE.'_videos_tags AS at
			LEFT JOIN '.PRE."_tags AS t USING(tagid)
			WHERE t.tag LIKE '%".addslashes_like($item)."%'
		");
        $result[$item] = get_ids($data, 'id');
    }

    return $result;
}

//Tags zu einem Video auslesen
function videos_tags($id)
{
    global $set,$db,$apx,$user;
    $tagdata = [];
    $tagids = [];
    $tags = [];
    $data = $db->fetch('
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM '.PRE.'_videos_tags AS nt
		LEFT JOIN '.PRE.'_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN '.PRE.'_videos_tags AS nt2 ON nt.tagid=nt2.tagid
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

//Weekstamp
function videos_weekstamp($time)
{
    //Wenn Kalenderwoche >= 52 und wir uns im Januar befinden
    //-> Kalenderwoche gehört zum vorherigen Jahr!
    if (intval(date('W', $time - TIMEDIFF)) >= 52 && 1 == intval(date('n', $time - TIMEDIFF))) {
        return (date('Y', $time - TIMEDIFF) - 1).sprintf('%02d', date('W', $time - TIMEDIFF));
    }

    return date('Y', $time - TIMEDIFF).sprintf('%02d', date('W', $time - TIMEDIFF));
}

//Statistik
function videos_insert_stats($id, $filesize = 0, $local = true)
{
    global $set,$db,$apx,$user;
    $id = (int) $id;
    if (!$set['videos']['exttraffic'] && !$local) {
        $filesize = 0;
    }

    $statsnow = time();
    $datestamp = date('Ymd', $statsnow - TIMEDIFF);

    list($stats_exists) = $db->first('SELECT daystamp FROM '.PRE."_videos_stats WHERE ( daystamp='".$datestamp."' AND dlid='".$id."' )");
    if ($stats_exists) {
        $db->query('UPDATE '.PRE."_videos_stats SET hits=hits+1 WHERE ( daystamp='".$datestamp."' AND dlid='".$id."' )");
    } else {
        $db->quieterror = true;
        $db->query('INSERT INTO '.PRE."_videos_stats VALUES ('".$datestamp."','".time()."','".$id."','".$filesize."',1)");
        $db->quieterror = false;
    }
    $db->query('UPDATE '.PRE."_videos SET downloads=downloads+1 WHERE id='".$id."' LIMIT 1");
}

//Video-Limit erreicht?
function videos_limit_is_reached($id = false, $limit = false)
{
    global $set,$db;
    $id = (int) $id;
    $limit = (int) $limit;

    //Maximaler Traffic
    if ($set['videos']['maxtraffic']) {
        list($traffic) = $db->first('SELECT sum(hits*bytes) FROM '.PRE."_videos_stats WHERE daystamp='".date('Ymd', time() - TIMEDIFF)."'");
        if ($traffic >= $set['videos']['maxtraffic']) {
            return true;
        }
    }

    //Maximale Videozahl
    if ($id && $limit) {
        list($hits) = $db->first('SELECT hits FROM '.PRE."_videos_stats WHERE ( dlid='".$id."' AND daystamp='".date('Ymd', time() - TIMEDIFF)."' ) LIMIT 1");
        if ($hits >= $limit) {
            return true;
        }
    }

    return false;
}

//Kommentarseite
function videos_showcomments($id)
{
    global $db,$tmpl,$user,$set,$apx;

    $res = $db->first('SELECT id,allowcoms FROM '.PRE."_videos WHERE id='".intval($id)."' LIMIT 1");
    if (!$set['videos']['coms'] || !$res['allowcoms'] || !$apx->is_module('comments')) {
        return;
    }
    require_once BASEDIR.getmodulepath('comments').'class.comments.php';
    $coms = new comments('videos', $res['id']);
    $coms->assign_comments();

    $apx->tmpl->parse('comments', 'comments');
    require 'lib/_end.php';
}
