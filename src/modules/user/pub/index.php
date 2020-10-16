<?php

$apx->lang->drop('index');
$parse = $apx->tmpl->used_vars('index');

//Links
$link_profile = mklink('user.php?action=myprofile', 'user,myprofile.html');
$link_showprofile = mklink('user.php?action=profile&amp;id='.$user->info['userid'], 'user,profile,'.$user->info['userid'].urlformat($user->info['username']).'.html');
$link_sig = mklink('user.php?action=signature', 'user,signature.html');
$link_avatar = mklink('user.php?action=avatar', 'user,avatar.html');
$link_friends = mklink('user.php?action=friends', 'user,friends.html');
$link_pms = mklink('user.php?action=pms', 'user,pms.html');
$link_newpm = mklink('user.php?action=newpm', 'user,newpm.html');
$link_newmail = mklink('user.php?action=newmail', 'user,newmail.html');
$link_ignore = mklink('user.php?action=ignorelist', 'user,ignorelist.html');
$link_blog = mklink('user.php?action=myblog', 'user,myblog.html');
$link_showblog = mklink('user.php?action=blog&amp;id='.$user->info['userid'], 'user,blog,'.$user->info['userid'].',1.html');
$link_gallery = mklink('user.php?action=mygallery', 'user,mygallery.html');
$link_showgallery = mklink('user.php?action=gallery&amp;id='.$user->info['userid'], 'user,gallery,'.$user->info['userid'].',0,0.html');
$link_showguestbook = mklink('user.php?action=guestbook&amp;id='.$user->info['userid'], 'user,guestbook,'.$user->info['userid'].',1.html');
$link_subscriptions = mklink('user.php?action=subscriptions', 'user,subscriptions.html');
$link_logout = mklink('user.php?action=logout', 'user,logout.html');
$apx->tmpl->assign('LINK_PROFILE', $link_profile);
$apx->tmpl->assign('LINK_SHOWPROFILE', $link_showprofile);
$apx->tmpl->assign('LINK_SIGNATURE', $link_sig);
$apx->tmpl->assign('LINK_AVATAR', $link_avatar);
$apx->tmpl->assign('LINK_FRIENDS', $link_friends);
$apx->tmpl->assign('LINK_PMS', $link_pms);
$apx->tmpl->assign('LINK_IGNORELIST', $link_ignore);
$apx->tmpl->assign('LINK_NEWPM', $link_newpm);
$apx->tmpl->assign('LINK_NEWMAIL', $link_newmail);
$apx->tmpl->assign('LINK_LOGOUT', $link_logout);
if ($set['user']['blog']) {
    $apx->tmpl->assign('LINK_BLOG', $link_blog);
    $apx->tmpl->assign('LINK_SHOWBLOG', $link_showblog);
}
if ($set['user']['gallery']) {
    $apx->tmpl->assign('LINK_GALLERY', $link_gallery);
    $apx->tmpl->assign('LINK_SHOWGALLERY', $link_showgallery);
}
if ($set['user']['guestbook']) {
    $apx->tmpl->assign('LINK_SHOWGUESTBOOK', $link_showguestbook);
}
if ($apx->is_module('forum')) {
    $apx->tmpl->assign('LINK_SUBSCRIPTIONS', $link_subscriptions);
}

if ($apx->is_module('products') && $set['products']['collection']) {
    $apx->tmpl->assign('LINK_COLLECTION', mklink(
        'user.php?action=collection&amp;id='.$user->info['userid'],
        'user,collection,'.$user->info['userid'].',0,1.html'
    ));
}

//PMs
list($totalpms) = $db->first('SELECT count(id) FROM '.PRE."_user_pms WHERE ( touser='".$user->info['userid']."' AND del_to='0' )");
list($newpms) = $db->first('SELECT count(id) FROM '.PRE."_user_pms WHERE ( touser='".$user->info['userid']."' AND del_to='0' AND isread='0' )");
$apx->tmpl->assign('TOTALPMS', $totalpms);
$apx->tmpl->assign('NEWPMS', $newpms);
if ($set['user']['guestbook']) {
    list($newgb) = $db->first('SELECT count(id) FROM '.PRE."_user_guestbook WHERE owner='".$user->info['userid']."' AND userid!='".$user->info['userid']."' AND time>'".$user->info['lastonline']."'");
    $apx->tmpl->assign('NEWGB', $newgb);
}

//Besucher aufzeichnen und ausgeben
if (in_array('VISITOR', $parse)) {
    user_assign_visitors('profile', $user->info['userid'], $apx->tmpl, $parse);
}

$apx->tmpl->parse('index');
