<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Tags Autocomplete
function togglestate()
{
    global $apx, $db, $set;

    $id = (int) $_REQUEST['id'];
    $status = (int) $_REQUEST['status'];
    if (!$id) {
        terminate();
    }

    $open = $apx->session->get('forum_open');
    $open = array_map('intval', dash_unserialize($open));
    if (!is_array($open)) {
        $open = [];
    }

    if ($status) {
        if (!in_array($id, $open)) {
            $open[] = $id;
        }
    } else {
        $index = array_search($id, $open);
        if (false !== $index) {
            unset($open[$index]);
        }
    }

    $apx->session->set('forum_open', dash_serialize($open));
}

//Tags Autocomplete
function nodemoved()
{
    global $apx;
    if (!checkToken()) {
        return;
    }
    if (!$apx->user->has_right('forum.edit')) {
        return;
    }
    $id = (int) $_REQUEST['id'];
    $newparent = (int) $_REQUEST['parentid'];
    $beforeid = (int) $_REQUEST['before'];
    $afterid = (int) $_REQUEST['after'];
    if (!$id) {
        return;
    }
    require_once BASEDIR.'lib/class.recursivetree.php';
    $tree = new RecursiveTree(PRE.'_forums', 'forumid');

    //In einen Knoten verschieben
    if (!$beforeid && !$afterid) {
        $tree->moveNode($id, $newparent);
    }

    //Vor einen Knoten verschieben
    elseif ($beforeid) {
        $tree->moveNodeBefore($id, $newparent, $beforeid);
    }

    //Nach einen Knoten
    elseif ($afterid) {
        $tree->moveNodeAfter($id, $newparent, $afterid);
    }
}

//Themenicon verschoben
function iconmoved()
{
    global $apx, $set, $db;
    if (!checkToken()) {
        return;
    }
    if (!$apx->user->has_right('forum.icons')) {
        return;
    }
    $icons = array_sort($set['forum']['icons'], 'ord', 'ASC');

    $id = (int) $_REQUEST['id'];
    $beforeid = (int) $_REQUEST['before'];
    $afterid = (int) $_REQUEST['after'];
    if (!$id || (!$beforeid && !$afterid)) {
        return;
    }
    //Vor einen Knoten verschieben
    if ($beforeid) {
        $targetid = $beforeid;

        if ($id == $targetid) {
            return;
        }
        if (!isset($icons[$id])) {
            return;
        }
        if (!isset($icons[$targetid])) {
            return;
        }
        $ord = $icons[$id]['ord'];
        $targetOrd = $icons[$targetid]['ord'];

        //Sonderfall
        if ($ord < $targetOrd) {
            --$targetOrd;
        }

        $diff = ($ord < $targetOrd ? -1 : 1);
        $minOrd = min([$ord, $targetOrd]);
        $maxOrd = max([$ord, $targetOrd]);
        foreach ($icons as $key => $icon) {
            if ($icon['ord'] >= $minOrd && $icon['ord'] <= $maxOrd) {
                $icons[$key]['ord'] += $diff;
            }
        }

        $icons[$id]['ord'] = $targetOrd;
    }

    //Nach einen Knoten verschieben
    elseif ($afterid) {
        $targetid = $afterid;

        if ($id == $targetid) {
            return;
        }
        if (!isset($icons[$id])) {
            return;
        }
        if (!isset($icons[$targetid])) {
            return;
        }
        $ord = $icons[$id]['ord'];
        $targetOrd = $icons[$targetid]['ord'];

        //Sonderfall
        if ($ord > $targetOrd) {
            ++$targetOrd;
        }

        $diff = ($ord < $targetOrd ? -1 : 1);
        $minOrd = min([$ord, $targetOrd]);
        $maxOrd = max([$ord, $targetOrd]);
        foreach ($icons as $key => $icon) {
            if ($icon['ord'] >= $minOrd && $icon['ord'] <= $maxOrd) {
                $icons[$key]['ord'] += $diff;
            }
        }

        $icons[$id]['ord'] = $targetOrd;
    }

    //Speichern
    ksort($icons);
    $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($icons))."' WHERE module='forum' AND varname='icons' LIMIT 1");
}
