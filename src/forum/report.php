<?php

define('APXRUN', true);
define('INFORUM', true);
define('BASEREL', '../');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require '../lib/_start.php';  ///////////////////////////////////////////////////////// SYSTEMSTART ///
require 'lib/_start.php';     /////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->lang->drop('report');

$_REQUEST['id'] = (int) $_REQUEST['id'];
if (!$_REQUEST['id']) {
    die('missing post-ID!');
}

$postinfo = post_info($_REQUEST['id']);
if (!$postinfo['postid'] || $postinfo['del']) {
    message($apx->lang->get('MSG_POSTNOTEXIST'));
}
$threadinfo = thread_info($postinfo['threadid']);
if (!$threadinfo['threadid'] || $threadinfo['del']) {
    message($apx->lang->get('MSG_THREADNOTEXIST'));
}
$foruminfo = forum_info($threadinfo['forumid']);
if (!$foruminfo['forumid']) {
    message($apx->lang->get('MSG_FORUMNOTEXIST'));
}
if (!forum_access_read($foruminfo) || !$user->info['userid']) {
    tmessage('noright', [], false, false);
}
check_forum_password($foruminfo);

////////////////////////////////////////////////////////////////////////////////////////// REPORT

if ($_POST['send']) {
    if (!$_POST['text']) {
        message('back');
    } else {
        $moderators = '';
        if (is_array($foruminfo['moderator']) && count($foruminfo['moderator'])) {
            $moderators = ' OR userid IN ('.implode(',', $foruminfo['moderator']).') ';
        }

        //eMails auslesen
        $data = $db->fetch('SELECT DISTINCT email FROM '.PRE.'_user LEFT JOIN '.PRE."_user_groups USING(groupid) WHERE gtype='admin' ".$moderators);
        $emails = get_ids($data, 'email');

        //eMail senden
        if (count($emails)) {
            $input['USERNAME'] = $user->info['username'];
            $input['URL'] = HTTP.'forum/post.php?id='.$postinfo['postid'];
            $input['CAUSE'] = $_POST['text'];
            sendmail(implode(', ', $emails), 'REPORT', $input);
        }

        //Folgende Beiträge zählen
        list($follow) = $db->first('SELECT count(postid) FROM '.PRE."_forum_posts WHERE ( threadid='".$threadinfo['threadid']."' AND time>'".$postinfo['time']."' AND del!=1 )");
        $diff = $threadinfo['posts'] - $follow; //Beiträge vor dem gelöschten Beitrag
        $page = ceil($diff / $user->info['forum_ppp']);

        //Zur vorherigen Seite gehen
        $goto = mkrellink(
            'thread.php?id='.$threadinfo['threadid'].'&amp;p='.$page,
            'thread,'.$threadinfo['threadid'].','.$page.urlformat($threadinfo['title']).'.html'
        );

        message($apx->lang->get('MSG_OK'), $goto);
    }
} else {
    $apx->tmpl->assign('ID', $_REQUEST['id']);
    $apx->tmpl->parse('report');
}

////////////////////////////////////////////////////////////////////////////////////////////////////////

$threadpath = [[
    'TITLE' => trim(compatible_hsc(strip_tags(forum_get_prefix($threadinfo['prefix']).' ').$threadinfo['title'])),
    'LINK' => mkrellink(
        'thread.php?id='.$threadinfo['threadid'],
        'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
    ),
]];

$apx->tmpl->assign('PATH', array_merge(forum_path($foruminfo, 1), $threadpath));
$apx->tmpl->assign('PATHEND', $apx->lang->get('HEADLINE_REPORT'));
titlebar($apx->lang->get('HEADLINE_REPORT'));

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';     ///////////////////////////////////////////////////////////////////////////
require '../lib/_end.php';  //////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
