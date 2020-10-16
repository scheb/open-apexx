<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Loginbox ausgeben
function user_loginbox()
{
    global $set,$apx,$db,$user;
    $tmpl = new tengine();
    $apx->lang->drop('func_loginbox', 'user');

    if (!$user->info['userid']) {
        $tmpl->assign('POSTTO', mklink('user.php', 'user.html'));
    }
    $tmpl->parse('functions/loginbox', 'user');
}

//Profillink erzeugen
function user_profile($id = 0, $username = false)
{
    global $set;
    $id = (int) $id;
    if (!$id) {
        return '#';
    }
    echo $set['forum_url'].'member.php?u='.$id;
}

//Forum-IDs
function vbthreads_parse_ids($string)
{
    if (!$string) {
        return [];
    }
    if (is_int($string)) {
        return $string;
    }
    if (false === strpos($string, ',')) {
        return (int) $string;
    }
    $pp = explode(',', $string);
    $final = [];

    foreach ($pp as $one) {
        $one = (int) $one;
        if ($one) {
            $final[] = $one;
        }
    }

    return array_unique($final);
}

//Offene Foren auslesen
function vbthreads_open_forums($forumids)
{
    global $set,$db,$apx,$user;
    $forumdb = $user->getForumConn();

    //Forum-Liste
    if (is_array($forumids) && count($forumids)) {
        $data = $forumdb->fetch('SELECT a.forumid AS id FROM '.VBPRE.'forum AS a LEFT JOIN '.VBPRE.'forumpermission AS b ON a.forumid=b.forumid WHERE ( a.forumid IN ('.implode(',', $forumids).') AND usergroupid IS NULL ) ');
    }

    //Ein bestimmtes Forum mit Unter-IDs
    elseif (is_int($forumids) && 0 != $forumids) {
        $data = $forumdb->fetch('SELECT a.forumid AS id FROM '.VBPRE.'forum AS a LEFT JOIN '.VBPRE."forumpermission AS b ON a.forumid=b.forumid WHERE ( parentlist REGEXP '".addslashes('(^|,)'.$forumids.'(,|$)')."' AND usergroupid IS NULL ) ");
    }

    //Alle Foren
    else {
        $data = $forumdb->fetch('SELECT a.forumid AS id FROM '.VBPRE.'forum AS a LEFT JOIN '.VBPRE.'forumpermission AS b ON a.forumid=b.forumid WHERE usergroupid IS NULL ');
    }

    $forums = get_ids($data);

    return $forums;
}

//Neuste Threads auslesen
function vbthreads_newthreads($count = 5, $forumid = 0, $filter = 0, $template = 'new')
{
    global $set,$db,$apx,$user;
    $forumdb = $user->getForumConn();

    $open = vbthreads_open_forums(vbthreads_parse_ids($forumid));
    if ($filter) {
        $donot = vbthreads_open_forums(vbthreads_parse_ids($filter));
    } else {
        $donot = [];
    }

    $data = $forumdb->fetch('SELECT a.threadid,a.title,a.lastpost,a.lastposter,a.replycount,a.postusername,a.dateline,a.replycount,a.views FROM '.VBPRE.'thread AS a LEFT JOIN '.VBPRE.'deletionlog AS b ON a.threadid=b.primaryid WHERE ( forumid IN ('.implode(',', $open).') '.iif($donot, ' AND NOT forumid IN ('.implode(',', $donot).')')." AND ( b.type IS NULL OR b.type='post' ) ) ORDER BY dateline DESC ".iif($count, 'LIMIT '.$count));

    vbthreads_threads_print('new', $data, $template);
}

//Aktualisierte Threads auslesen
function vbthreads_updatedthreads($count = 5, $forumid = 0, $filter = 0, $template = 'updated')
{
    global $set,$db,$apx,$user,$forumdb;
    $forumdb = $user->getForumConn();

    $open = vbthreads_open_forums(vbthreads_parse_ids($forumid));
    if ($filter) {
        $donot = vbthreads_open_forums(vbthreads_parse_ids($filter));
    } else {
        $donot = [];
    }

    $data = $forumdb->fetch('SELECT a.threadid,a.title,a.lastpost,a.lastposter,a.replycount,a.postusername,a.dateline,a.replycount,a.views FROM '.VBPRE.'thread AS a LEFT JOIN '.VBPRE.'deletionlog AS b ON a.threadid=b.primaryid WHERE ( forumid IN ('.implode(',', $open).') '.iif($donot, ' AND NOT forumid IN ('.implode(',', $donot).')')." AND ( b.type IS NULL OR b.type='post' ) ) ORDER BY lastpost DESC ".iif($count, 'LIMIT '.$count));
    vbthreads_threads_print('updated', $data, $template);
}

//Threads ausgeben
function vbthreads_threads_print($from, $data, $template)
{
    global $set,$db,$apx,$user;
    $tmpl = new tengine();
    $forumurl = $set['forum_url'];

    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            if ('new' == $from) {
                $link = $forumurl.'showthread.php?t='.$res['threadid'];
            } else {
                $link = $forumurl.'showthread.php?goto=lastpost&t='.$res['threadid'];
            }

            $tabledata[$i]['ID'] = $res['threadid'];
            $tabledata[$i]['TITLE'] = $res['title'];
            $tabledata[$i]['LINK'] = $link;
            $tabledata[$i]['TIME'] = $res['dateline'];
            $tabledata[$i]['STARTER'] = $res['postusername'];
            $tabledata[$i]['LASTPOST'] = $res['lastpost'];
            $tabledata[$i]['LASTPOSTER'] = $res['lastposter'];
            $tabledata[$i]['POSTS'] = $res['replycount'];
            $tabledata[$i]['VIEWS'] = $res['views'];
        }
    }

    $tmpl->assign('THREAD', $tabledata);
    $tmpl->parse($template, 'vbforum');
}
