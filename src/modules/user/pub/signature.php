<?php

$apx->lang->drop('signature');
headline($apx->lang->get('HEADLINE_SIGNATURE'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_SIGNATURE'));

if (!$_POST['preview'] && $_POST['send']) {
    /*if ( !$_POST['signature'] ) message('back');
    else*/if (strlen($_POST['signature']) > $set['user']['sigmaxlen']) {
        message($apx->lang->get('MSG_SIGTOOLONG'), 'javascript:history.back()');
    } else {
        $db->query('UPDATE '.PRE."_user SET signature='".addslashes($_POST['signature'])."' WHERE userid='".$user->info['userid']."' LIMIT 1");
        message($apx->lang->get('MSG_OK'), mklink('user.php', 'user.html'));
    }
} else {
    if (!isset($_POST['signature'])) {
        $_POST['signature'] = $user->info['signature'];
    }
    if ($_POST['preview']) {
        $apx->tmpl->assign('PREVIEW', $user->mksig($_POST, 1));
    }
    $apx->tmpl->assign('SIGNATURE', compatible_hsc($_POST['signature']));
    $apx->tmpl->assign('MAXLEN', $set['user']['sigmaxlen']);

    $postto = mklink(
        'user.php?action=signature',
        'user,signature.html'
    );

    $apx->tmpl->assign('POSTTO', $postto);
    $apx->tmpl->parse('signature');
}
