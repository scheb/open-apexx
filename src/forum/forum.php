<?php

define('APXRUN', true);
define('INFORUM', true);
define('BASEREL', '../');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require '../lib/_start.php';  ///////////////////////////////////////////////////////// SYSTEMSTART ///
require 'lib/_start.php';     /////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->lang->drop('forum');

$_REQUEST['id'] = (int) $_REQUEST['id'];
if (!$_REQUEST['id']) {
    die('missing forum-ID!');
}

$foruminfo = forum_info($_REQUEST['id']);
if (!$foruminfo['forumid']) {
    message($apx->lang->get('MSG_FORUMNOTEXIST'));
}
if (!forum_access_read($foruminfo)) {
    tmessage('noright', [], false, false);
}
check_forum_password($foruminfo);

///////////////////////////////////////////////////////////////////////////////////////////////// FORUM IST GELESEN

if ($_REQUEST['allread']) {
    forum_isread($foruminfo['forumid']);
}

///////////////////////////////////////////////////////////////////////////////////////////////// TPP SETZEN

if (intval($_POST['tpp']) && $user->info['userid']) {
    $db->query('UPDATE '.PRE."_user SET forum_tpp='".intval($_POST['tpp'])."' WHERE userid='".$user->info['userid']."' LIMIT 1");
    $user->info['forum_tpp'] = intval($_POST['tpp']);
}

///////////////////////////////////////////////////////////////////////////////////////////////// UNTERFOREN

//Forum anzeigen
$data = $forumData = forum_readout($_REQUEST['id']);
require 'lib/forum_assign.php';

///////////////////////////////////////////////////////////////////////////////////////////////// ANKÜNDIGUNGEN

$data = $db->fetch('
	SELECT a.id,a.title,a.userid,a.starttime,a.endtime,b.username
	FROM '.PRE.'_forum_announcements AS a
	LEFT JOIN '.PRE.'_forum_anndisplay AS ad ON a.id=ad.id
	LEFT JOIN '.PRE."_user AS b ON a.userid=b.userid
	WHERE '".time()."' BETWEEN starttime AND endtime AND ad.forumid IN (0, ".$foruminfo['forumid'].')
	ORDER BY starttime DESC
');
if (count($data)) {
    foreach ($data as $res) {
        $pre = [];

        //Lastvisit bestimmen
        $lastview = max([
            $user->info['forum_lastonline'],
            announcement_readtime($res['id']),
        ]);

        //Link
        $link = mkrellink(
            'announcement.php?id='.$res['id'],
            'announcement,'.$res['id'].urlformat($res['title']).'.html'
        );

        $pre['ID'] = $res['id'];
        $pre['TITLE'] = replace($res['title']);
        $pre['LINK'] = $link;
        $pre['USERID'] = $res['userid'];
        $pre['USERNAME'] = replace($res['username']);
        $pre['TIME'] = $res['starttime'];
        $pre['VIEWS'] = number_format($res['views'], 0, '', '.');
        $pre['ISNEW'] = iif($res['starttime'] && $res['starttime'] > $lastview, 1, 0);

        $anndata[] = $pre;
    }
}
$apx->tmpl->assign('ANNOUNCEMENT', $anndata);

///////////////////////////////////////////////////////////////////////////////////////////////// THEMEN

//Gelöschte Themen filtern
$delfilter = '';
if (!($user->info['userid'] && ($user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator'])))) {
    $delfilter = ' AND del=0 ';
}

//Seitenzahlen
list($count) = $db->first('SELECT count(threadid) FROM '.PRE."_forum_threads WHERE (forumid='".$foruminfo['forumid']."' ".$delfilter.' )');
pages(
    mkrellink(
        'forum.php?id='.$foruminfo['forumid'].iif($_REQUEST['sortby'], '&amp;sortby='.$_REQUEST['sortby']),
        'forum,'.$foruminfo['forumid'].',{P}'.urlformat($foruminfo['title']).'.html'.iif($_REQUEST['sortby'], '?sortby='.$_REQUEST['sortby'])
    ),
    $count,
    $user->info['forum_tpp']
);

//Sortierung
$orderdef[0] = 'lastpost';
$orderdef['title'] = ['title', 'ASC'];
$orderdef['opener'] = ['opener', 'ASC'];
$orderdef['lastpost'] = ['lastposttime', 'DESC'];
$orderdef['posts'] = ['posts', 'DESC'];
$orderdef['views'] = ['views', 'DESC'];

$threadData = $db->fetch('SELECT *, IF(del!=0,0,sticky) AS stickyord FROM '.PRE."_forum_threads WHERE ( forumid='".$_REQUEST['id']."' ".$delfilter.' ) '.getorder($orderdef, 'stickyord DESC', 1).' '.getlimit($user->info['forum_tpp']));
if (count($threadData)) {
    foreach ($threadData as $res) {
        ++$i;
        $pre = [];

        //Lastvisit bestimmen
        $lastview = max([
            $user->info['forum_lastonline'],
            thread_readtime($res['threadid']),
            forum_readtime($foruminfo['forumid']),
        ]);

        //Verschoben
        if ($res['moved']) {
            $res['threadid'] = $res['moved'];
        }

        //Link
        $link = mkrellink(
            'thread.php?id='.$res['threadid'],
            'thread,'.$res['threadid'].',1'.urlformat($res['title']).'.html'
        );

        //Icon
        if (-1 != $res['icon'] && isset($set['forum']['icons'][(int) $res['icon']])) {
            $icon = $set['forum']['icons'][(int) $res['icon']]['file'];
        } else {
            $icon = '';
        }

        $pre['ID'] = $res['threadid'];
        $pre['TITLE'] = replace($res['title']);
        $pre['PREFIX'] = forum_get_prefix($res['prefix']);
        $pre['LINK'] = $link;
        $pre['ICON'] = $icon;
        $pre['OPENER_USERID'] = $res['opener_userid'];
        $pre['OPENER_USERNAME'] = replace($res['opener']);
        $pre['OPENTIME'] = $res['opentime'];
        $pre['LASTPOST_USERID'] = $res['lastposter_userid'];
        $pre['LASTPOST_USERNAME'] = replace($res['lastposter']);
        $pre['LASTPOST_TIME'] = $res['lastposttime'];
        $pre['LASTPOST_LINK'] = 'thread.php?id='.$res['threadid'].'&amp;goto=lastpost';
        $pre['LINK_UNREAD'] = 'thread.php?id='.$res['threadid'].'&amp;goto=firstunread';
        $pre['STICKY'] = replace($res['sticky_text']);
        $pre['POSTS'] = number_format(($res['posts'] - 1), 0, '', '.');
        $pre['VIEWS'] = number_format($res['views'], 0, '', '.');
        $pre['HOT'] = iif($res['posts'] >= $set['forum']['hot_posts'] || $res['views'] >= $set['forum']['hot_views'], 1, 0);
        $pre['MOVED'] = $res['moved'];
        $pre['NEWPOSTS'] = iif($res['lastposttime'] && $res['lastposttime'] > $lastview, 1, 0);
        $pre['CLOSED'] = !$res['open'];
        $pre['DELETED'] = $res['del'];

        //Bewertungen
        if ($apx->is_module('ratings') && $set['forum']['ratings']) {
            require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
            if (!isset($rate)) {
                $rate = new ratings('forum', $res['threadid']);
            } else {
                $rate->mid = $res['threadid'];
            }
            $pre['RATING'] = $rate->display();
            $pre['RATING_VOTES'] = $rate->count();
            $pre['DISPLAY_RATING'] = 1;
        }

        if ($res['sticky'] && !$res['del']) {
            $pinneddata[] = $pre;
        } else {
            $threaddata[] = $pre;
        }
    }
}

//Sortieren nach...
ordervars(
    $orderdef,
    mkrellink(
        'forum.php?id='.$foruminfo['forumid'],
        'forum,'.$foruminfo['forumid'].',1'.urlformat($foruminfo['title']).'.html'
    )
);

//Moderatoren
if (count($foruminfo['moderator'])) {
    $data = $db->fetch('SELECT userid,username FROM '.PRE.'_user WHERE userid IN ('.implode(',', $foruminfo['moderator']).') ORDER BY username ASC');
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;
            $moddata[$i]['USERID'] = $res['userid'];
            $moddata[$i]['USERNAME'] = $res['username'];
        }
    }
}

//Hot-Parameter in Sprachplatzhalter
$langvar = strtr($apx->lang->get('HOTTHREAD'), ['{HOT_POSTS}' => $set['forum']['hot_posts'], '{HOT_VIEWS}' => $set['forum']['hot_views']]);
$apx->lang->langpack['HOTTHREAD'] = $langvar;

//Optionen-Links
if ($user->info['userid'] && !is_forum_subscr($foruminfo['forumid'])) {
    $subscribelink = HTTPDIR.mkrellink(
        'user.php?action=subscribe&option=addforum&amp;id='.$foruminfo['forumid'],
        'user,subscribe.html?option=addforum&amp;id='.$foruminfo['forumid']
    );
}
$readlink = mkrellink(
    'forum.php?id='.$foruminfo['forumid'].'&amp;allread=1',
    'forum,'.$foruminfo['forumid'].',1'.urlformat($foruminfo['title']).'.html?allread=1'
);

$forumlink = mkrellink(
    'forum.php?id='.$foruminfo['forumid'],
    'forum,'.$foruminfo['forumid'].',1'.urlformat($foruminfo['title']).'.html'
);

$apx->tmpl->assign('HOT_POSTS', $set['forum']['hot_posts']);
$apx->tmpl->assign('HOT_VIEWS', $set['forum']['hot_views']);
$apx->tmpl->assign('MODERATOR', $moddata);
$apx->tmpl->assign('LINK_SUBSCRIBE', $subscribelink);
$apx->tmpl->assign('LINK_MARKASREAD', $readlink);
$apx->tmpl->assign('PINNED', $pinneddata);
$apx->tmpl->assign('THREAD', $threaddata);
$apx->tmpl->assign('FORUMID', $_REQUEST['id']);
$apx->tmpl->assign('FORUM_TITLE', replace($foruminfo['title']));
$apx->tmpl->assign_static('META_DESCRIPTION', replace($foruminfo['meta_description']));
$apx->tmpl->assign('FORUM_LINK', $forumlink);
$apx->tmpl->assign('NOTHREADS', $foruminfo['iscat']);
$apx->tmpl->assign('RIGHT_OPEN', forum_access_open($foruminfo));
$apx->tmpl->assign('THREADSPERPAGE', $user->info['forum_tpp']);

//Rechte
$apx->tmpl->assign('LOGGED_IS_ADMIN', iif('admin' == $user->info['gtype'], 1, 0));
$apx->tmpl->assign('LOGGED_IS_MODERATOR', iif(in_array($user->info['userid'], $foruminfo['moderator']), 1, 0));

//Aktivität
forum_activity('forum', $foruminfo['forumid']);
list($userCount, $guestCount, $activelist) = forum_get_activity('forum', $foruminfo['forumid'], $foruminfo['moderator']);
$apx->tmpl->assign('ACTIVITY_USERS', $userCount);
$apx->tmpl->assign('ACTIVITY_GUESTS', $userCount);
$apx->tmpl->assign('ACTIVITY', $activelist);

$apx->tmpl->parse('forum');

////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->tmpl->assign_static('STYLESHEET', compatible_hsc($foruminfo['stylesheet']));
$apx->tmpl->assign('PATH', forum_path($foruminfo));
$apx->tmpl->assign('PATHEND', replace($foruminfo['title']));
titlebar($foruminfo['title']);

///////////////////////////////////////////////////////////////////////////////////////////////// GELESEN-STATUS

$isread = true;

//Fieser Hack: Verwende die Daten, die forum_assign.php erzeugt hat,
//denn da steht schon die lastposttime des Forums (inklusive Unterforen) drin.
foreach ($forumRec as $forum) {
    if (forum_access_visible($forum) && forum_access_read($forum) && correct_forum_password($forum)) {
        $forumLastview = max([
            $user->info['forum_lastonline'],
            forum_readtime($forum['forumid']),
        ]);
        if ($forumLastview < $forum['lastposttime']) {
            $isread = false;

            break;
        }
    }
}

//Themen brauchen wir nur anzuschauen, wenn die Unterforen alle gelesen sind
if ($isread) {
    //Eine detaillierte Prüfung ist nur notwendig, wenn es in diesem Forum etwas neues gibt
    $forumLastview = max([
        $user->info['forum_lastonline'],
        forum_readtime($foruminfo['forumid']),
    ]);
    if ($forumLastview < $foruminfo['lastposttime']) {
        //Alle ungelesenen Themen bestimmen und prüfen, ob bereits gelesen
        $data = $db->fetch('
			SELECT threadid, lastposttime
			FROM '.PRE."_forum_threads
			WHERE forumid='".$foruminfo['forumid']."' AND del=0 AND moved=0 AND lastposttime>'".$forumLastview."'
			ORDER BY lastposttime DESC
		");
        $readThreads = threads_get_read(); //Alle gelesenen Themen
        foreach ($data as $res) {
            //Thema wurde noch nicht gelesen bzw. es gibt neue Beiträge
            if (!isset($readThreads[$res['threadid']]) || $res['lastposttime'] > $readThreads[$res['threadid']]) {
                $isread = false;

                break;
            }
        }
    }
}

//Dieses Forum ist komplett gelesen
if ($isread) {
    forum_isread($foruminfo['forumid']);
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';     ///////////////////////////////////////////////////////////////////////////
require '../lib/_end.php';  //////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
