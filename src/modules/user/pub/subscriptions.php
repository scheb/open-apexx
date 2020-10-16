<?php

//Forum-Modul muss aktiv sein!
if (!$apx->is_module('forum')) {
    filenotfound();

    return;
}

$apx->module('forum'); //Diese Aktion gehört dem Forum
$apx->lang->drop('subscribe');
headline($apx->lang->get('HEADLINE_SUBSCRIPTIONS'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_SUBSCRIPTIONS'));
require_once BASEDIR.getmodulepath('forum').'basics.php';

//Abonnement-IDs auslesen
$data = $db->fetch('SELECT id,source,notification FROM '.PRE."_forum_subscriptions WHERE type='forum' AND userid='".$user->info['userid']."'");
$subscr_forums = get_ids($data, 'source');
if (count($data)) {
    foreach ($data as $res) {
        $subsrcinfo_forums[$res['source']] = $res;
    }
}
$data = $db->fetch('SELECT id,source,notification FROM '.PRE."_forum_subscriptions WHERE type='thread' AND userid='".$user->info['userid']."'");
$subscr_threads = get_ids($data, 'source');
if (count($data)) {
    foreach ($data as $res) {
        $subsrcinfo_threads[$res['source']] = $res;
    }
}

//Foren auslesen
function get_forum_info($id)
{
    static $cache;
    if (!isset($cache[$id])) {
        $cache[$id] = forum_info($id);
    }

    return $cache[$id];
}

//Auflistung FOREN
if (count($subscr_forums)) {
    //Fehlerhafte Abonnements löschen
    foreach ($subscr_forums as $key => $forumid) {
        $foruminfo = get_forum_info($forumid);
        if (!$foruminfo || !forum_access_read($foruminfo)) {
            unset($subscr_forums[$key]);
            $db->query('DELETE FROM '.PRE."_forum_subscriptions WHERE ( type='forum' AND source='".$forumid."' AND userid='".$user->info['userid']."' )");
        }
    }

    foreach ($subscr_forums as $forumid) {
        ++$i;
        $res = get_forum_info($forumid);

        $link = HTTPDIR.$set['forum']['directory'].'/'.mkrellink(
            'forum.php?id='.$res['forumid'],
            'forum,'.$res['forumid'].',1'.urlformat($res['title']).'.html'
        );

        $notify = '';
        $notification = $subsrcinfo_forums[$forumid]['notification'];
        if ('none' == $notification) {
            $notify = $apx->lang->get('SUBSCRIPTION_NONE');
        } elseif ('instant' == $notification) {
            $notify = $apx->lang->get('SUBSCRIPTION_INSTANT');
        } elseif ('daily' == $notification) {
            $notify = $apx->lang->get('SUBSCRIPTION_DAILY');
        } elseif ('weekly' == $notification) {
            $notify = $apx->lang->get('SUBSCRIPTION_WEEKLY');
        }

        $forumdata[$i]['ID'] = $res['forumid'];
        $forumdata[$i]['TITLE'] = $res['title'];
        $forumdata[$i]['DESCRIPTION'] = $res['description'];
        $forumdata[$i]['LINK'] = $link;
        $forumdata[$i]['CLOSED'] = iif($res['open'], 0, 1);
        $forumdata[$i]['NOTIFICATION'] = $notify;

        //Nur anzeigen, wenn Leserechte
        if (correct_forum_password($res)) {
            $forumdata[$i]['NEWPOSTS'] = iif($res['lastposttime'] && $res['lastposttime'] > $user->info['forum_lastonline'], 1, 0);

            //Letztes Thema auslesen
            if ($res['lastposttime']) {
                $thread = $db->first('SELECT threadid,prefix,title FROM '.PRE."_forum_threads WHERE ( del=0 AND moved=0 AND forumid='".$res['forumid']."' ) ORDER BY lastposttime DESC LIMIT 1");
            }
            $forumdata[$i]['LASTPOST_THREADID'] = $thread['threadid'];
            $forumdata[$i]['LASTPOST_THREADTITLE'] = $thread['title'];
            $forumdata[$i]['LASTPOST_THREADPREFIX'] = forum_get_prefix($thread['prefix']);
            $forumdata[$i]['LASTPOST_USERNAME'] = replace($res['lastposter']);
            $forumdata[$i]['LASTPOST_USERID'] = $res['lastposter_userid'];
            $forumdata[$i]['LASTPOST_TIME'] = $res['lastposttime'];
            $forumdata[$i]['LASTPOST_LINK'] = HTTPDIR.$set['forum']['directory'].'/'.'thread.php?id='.$thread['threadid'].'&amp;goto=lastpost';

            //Optionen
            $forumdata[$i]['LINK_DEL'] = mklink('user.php?action=subscribe&amp;option=delete&amp;id='.$subsrcinfo_forums[$forumid]['id'], 'user,subscribe.html?option=delete&amp;id='.$subsrcinfo_forums[$forumid]['id']);
            $forumdata[$i]['LINK_EDIT'] = mklink('user.php?action=subscribe&amp;option=edit&amp;id='.$subsrcinfo_forums[$forumid]['id'], 'user,subscribe.html?option=edit&amp;id='.$subsrcinfo_forums[$forumid]['id']);
        }
    }
}

//Auflistung THEMEN
if (count($subscr_threads)) {
    $data = $db->fetch('SELECT * FROM '.PRE.'_forum_threads WHERE threadid IN ('.implode(',', $subscr_threads).') ORDER BY lastposttime DESC');
    if (count($data)) {
        //Fehlerhafte Abonnements löschen
        foreach ($data as $key => $res) {
            $foruminfo = get_forum_info($res['forumid']);
            if (!$foruminfo || !forum_access_read($foruminfo)) {
                unset($data[$key]);
                $db->query('DELETE FROM '.PRE."_forum_subscriptions WHERE ( type='thread' AND source='".$res['threadid']."' AND userid='".$user->info['userid']."' )");
            }
        }

        foreach ($data as $res) {
            ++$i;

            //Link
            $link = HTTPDIR.$set['forum']['directory'].'/'.mkrellink(
                'thread.php?id='.$res['threadid'],
                'thread,'.$res['threadid'].',1'.urlformat($res['title']).'.html'
            );

            //Icon
            if (-1 != $res['icon'] && isset($set['forum']['icons'][(int) $res['icon']])) {
                $icon = $set['forum']['icons'][(int) $res['icon']]['file'];
            } else {
                $icon = '';
            }

            $notify = '';
            $notification = $subsrcinfo_threads[$res['threadid']]['notification'];
            if ('none' == $notification) {
                $notify = $apx->lang->get('SUBSCRIPTION_NONE');
            } elseif ('instant' == $notification) {
                $notify = $apx->lang->get('SUBSCRIPTION_INSTANT');
            } elseif ('daily' == $notification) {
                $notify = $apx->lang->get('SUBSCRIPTION_DAILY');
            } elseif ('weekly' == $notification) {
                $notify = $apx->lang->get('SUBSCRIPTION_WEEKLY');
            }

            $threaddata[$i]['ID'] = $res['threadid'];
            $threaddata[$i]['TITLE'] = replace($res['title']);
            $threaddata[$i]['PREFIX'] = forum_get_prefix($res['prefix']);
            $threaddata[$i]['LINK'] = $link;
            $threaddata[$i]['ICON'] = $icon;
            $threaddata[$i]['OPENER_USERID'] = $res['opener_userid'];
            $threaddata[$i]['OPENER_USERNAME'] = replace($res['opener']);
            $threaddata[$i]['OPENTIME'] = $res['opentime'];
            $threaddata[$i]['LASTPOST_USERID'] = $res['lastposter_userid'];
            $threaddata[$i]['LASTPOST_USERNAME'] = replace($res['lastposter']);
            $threaddata[$i]['LASTPOST_TIME'] = $res['lastposttime'];
            $threaddata[$i]['LASTPOST_LINK'] = HTTPDIR.$set['forum']['directory'].'/'.'thread.php?id='.$res['threadid'].'&amp;goto=lastpost';
            $threaddata[$i]['LINK_UNREAD'] = HTTPDIR.$set['forum']['directory'].'/'.'thread.php?id='.$res['threadid'].'&amp;goto=firstunread';
            $threaddata[$i]['NEWPOSTS'] = iif($res['lastposttime'] && $res['lastposttime'] > $user->info['forum_lastonline'], 1, 0);
            $threaddata[$i]['CLOSED'] = !$res['open'];
            $threaddata[$i]['NOTIFICATION'] = $notify;

            //Optionen
            $threaddata[$i]['LINK_DEL'] = mklink('user.php?action=subscribe&amp;option=delete&amp;id='.$subsrcinfo_threads[$res['threadid']]['id'], 'user,subscribe.html?option=delete&amp;id='.$subsrcinfo_threads[$res['threadid']]['id']);
            $threaddata[$i]['LINK_EDIT'] = mklink('user.php?action=subscribe&amp;option=edit&amp;id='.$subsrcinfo_threads[$res['threadid']]['id'], 'user,subscribe.html?option=edit&amp;id='.$subsrcinfo_threads[$res['threadid']]['id']);
        }
    }
}

$apx->tmpl->assign('HOT_POSTS', $set['forum']['hot_posts']);
$apx->tmpl->assign('HOT_VIEWS', $set['forum']['hot_views']);
$apx->tmpl->assign('FORUM', $forumdata);
$apx->tmpl->assign('THREAD', $threaddata);
$apx->tmpl->parse('subscriptions');
