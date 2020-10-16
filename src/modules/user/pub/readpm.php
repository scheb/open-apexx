<?php

$_REQUEST['id'] = (int) $_REQUEST['id'];
if (!$_REQUEST['id']) {
    die('missing ID!');
}
$apx->lang->drop('readpm');
headline($apx->lang->get('HEADLINE_READPM'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_READPM'));

$res = $db->first('SELECT a.id,a.subject,a.text,a.time,a.addsig,b.userid,b.username,b.signature FROM '.PRE.'_user_pms AS a LEFT JOIN '.PRE."_user AS b ON a.fromuser=b.userid WHERE ( a.id='".$_REQUEST['id']."' AND ( a.touser='".$user->info['userid']."' OR a.fromuser='".$user->info['userid']."' ) ) LIMIT 1");
if (!$res['id']) {
    die('you can only read your own messages!');
}

$text = $res['text'];
if ($set['user']['pm_badwords']) {
    $text = badwords($text);
}
$text = replace($text, 1);
if ($set['user']['pm_allowsmilies']) {
    $text = dbsmilies($text);
}
if ($set['user']['pm_allowcode']) {
    $text = dbcodes($text);
}

$postto = mklink(
    'user.php?action=readpm',
    'user,readpm.html'
);

$answer = mklink(
    'user.php?action=newpm&amp;answer='.$res['id'],
    'user,newpm.html?answer='.$res['id']
);

$delete = mklink(
    'user.php?action=delpm&amp;id='.$res['id'],
    'user,delpm,'.$res['id'].'.html'
);

$ignore = mklink(
    'user.php?action=ignorelist&amp;add=1&amp;username='.urlencode($res['username']),
    'user,ignorelist.html?add=1&amp;username='.urlencode($res['username'])
);

$apx->tmpl->assign('ID', $res['id']);
$apx->tmpl->assign('SUBJECT', $res['subject']);
$apx->tmpl->assign('TEXT', $text);
$apx->tmpl->assign('SIGNATURE', iif($res['addsig'], $user->mksig($res), ''));
$apx->tmpl->assign('TIME', $res['time']);
$apx->tmpl->assign('USERID', $res['userid']);
$apx->tmpl->assign('USERNAME', replace($res['username']));
$apx->tmpl->assign('LINK_ANSWER', $answer);
$apx->tmpl->assign('LINK_DELETE', $delete);
$apx->tmpl->assign('LINK_IGNORE', $ignore);
$apx->tmpl->assign('POSTTO', $postto);

$apx->tmpl->parse('readpm');

//Als gelesen markieren
if ($res['userid'] != $user->info['userid']) {
    $db->query('UPDATE '.PRE."_user_pms SET isread='1' WHERE id='".$_REQUEST['id']."' LIMIT 1");
}
