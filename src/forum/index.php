<?php

define('APXRUN', true);
define('INFORUM', true);
define('BASEREL', '../');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require '../lib/_start.php';  ///////////////////////////////////////////////////////// SYSTEMSTART ///
require 'lib/_start.php';     /////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->lang->drop('index');

////////////////////////////////////////////////////////////////////////////////////////// ALLES GELESEN

if ($_REQUEST['allread']) {
    if ($user->info['userid']) {
        $db->query('UPDATE '.PRE."_user SET forum_lastonline=forum_lastactive,forum_lastactive='".time()."' WHERE userid='".$user->info['userid']."' LIMIT 1");
    } else {
        setcookie($set['main']['cookie_pre'].'_forum_lastonline', $user->info['forum_lastactive'], time() + 14 * 24 * 3600);
        setcookie($set['main']['cookie_pre'].'_forum_lastactive', $now, time() + 14 * 24 * 3600);
    }
    $user->info['forum_lastonline'] = $user->info['forum_lastactive'];
    $user->info['forum_lastactive'] = time();
}

////////////////////////////////////////////////////////////////////////////////////////////////// FOREN

//Forum anzeigen
$data = forum_readout();
require 'lib/forum_assign.php';

$apx->tmpl->assign('LINK_NEWPOSTS', mkrellink('search.php?newposts=1', 'search.html?newposts=1'));
$apx->tmpl->assign('LINK_ALLREAD', mkrellink('index.php?allread=1', 'index.html?allread=1'));

//////////////////////////////////////////////////////////////////////////////////////////// ONLINELISTE

list($count['users']) = $db->first('SELECT count(*) FROM '.PRE.'_user WHERE lastactive>='.(time() - $set['user']['timeout'] * 60));
list($count['inv']) = $db->first('SELECT count(*) FROM '.PRE.'_user WHERE lastactive>='.(time() - $set['user']['timeout'] * 60).' AND pub_invisible=1');
if ($set['user']['onlinelist']) {
    list($count['guests']) = $db->first('SELECT count(*) FROM '.PRE.'_user_online WHERE userid=0');
} else {
    $count['guests'] = 0;
}
$count['total'] = $count['users'] + $count['guests'];

$apx->tmpl->assign('ONLINE_TOTAL', $count['total']);
$apx->tmpl->assign('ONLINE_USERS', $count['users']);
$apx->tmpl->assign('ONLINE_GUESTS', $count['guests']);
$apx->tmpl->assign('ONLINE_INVISIBLE', $count['inv']);

$userdata = [];
$mods = get_modlist();
$data = $db->fetch('SELECT b.userid,b.username,b.forum_posts,b.pub_invisible AS invisible,c.gtype FROM '.PRE.'_user b LEFT JOIN '.PRE.'_user_groups AS c USING(groupid) WHERE ( b.lastactive>='.(time() - $set['user']['timeout'] * 60).' '.($user->is_admin() ? '' : "AND ( b.pub_invisible=0 OR b.userid='".$user->info['userid']."' )").' ) ORDER BY b.username ASC');
if (count($data)) {
    foreach ($data as $res) {
        ++$i;
        $rankinfo = get_rank($res);

        $userdata[$i]['USERID'] = $res['userid'];
        $userdata[$i]['USERNAME'] = replace($res['username']);
        $userdata[$i]['INVISIBLE'] = $res['invisible'];

        if ('admin' == $res['gtype']) {
            $userdata[$i]['IS_ADMIN'] = 1;
        } elseif ('indiv' == $res['gtype']) {
            $userdata[$i]['IS_TEAM'] = 1;
        } elseif (in_array($res['userid'], $mods)) {
            $userdata[$i]['IS_MODERATOR'] = 1;
        }
    }
}

$apx->tmpl->assign('ONLINE', $userdata);

////////////////////////////////////////////////////////////////////////////////////////////// STATISTIK

list($threads) = $db->first('SELECT count(threadid) FROM '.PRE.'_forum_threads WHERE del=0 AND moved=0');
list($posts) = $db->first('SELECT count(postid) FROM '.PRE.'_forum_posts WHERE del=0');
list($users) = $db->first('SELECT count(userid) FROM '.PRE.'_user');
$apx->tmpl->assign('STATS_THREADS', $threads);
$apx->tmpl->assign('STATS_POSTS', $posts);
$apx->tmpl->assign('STATS_USERS', $users);

//Neue User
$data = $db->fetch('SELECT userid,username FROM '.PRE."_user WHERE reg_key='' ORDER BY reg_time DESC LIMIT 5");
if (count($data)) {
    foreach ($data as $res) {
        ++$i;
        $newdata[$i]['ID'] = $res['userid'];
        $newdata[$i]['NAME'] = replace($res['username']);
    }
}
$apx->tmpl->assign('NEWUSER', $newdata);

//////////////////////////////////////////////////////////////////////////////////////////// GEBURTSTAGE

$data = $db->fetch('SELECT userid,username,birthday FROM '.PRE."_user WHERE ( birthday='".date('d-m', time() - TIMEDIFF)."' OR birthday LIKE '".date('d-m-', time() - TIMEDIFF)."%' ) ORDER BY username ASC");
if (count($data)) {
    foreach ($data as $res) {
        ++$i;
        $bd = explode('-', $res['birthday']);
        $birthdata[$i]['USERID'] = $res['userid'];
        $birthdata[$i]['USERNAME'] = replace($res['username']);
        if ($bd[2]) {
            $birthdata[$i]['AGE'] = date('Y', time() - TIMEDIFF) - $bd[2];
        }
    }
}

$apx->tmpl->assign('BIRTHDAY', $birthdata);

////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->tmpl->parse('index');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';     ///////////////////////////////////////////////////////////////////////////
require '../lib/_end.php';  //////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
