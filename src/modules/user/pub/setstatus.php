<?php

if (isset($_POST['status'])) {
    $db->query('UPDATE '.PRE."_user SET status='".addslashes($_POST['status'])."', status_smiley='".addslashes($_POST['status_smiley'])."' WHERE userid='".$user->info['userid']."' LIMIT 1");
}

$link = str_replace('&amp;', '&', $user->mkprofile($user->info['userid'], $user->info['username']));
header('HTTP/1.1 301 Moved Permanently');
header('Location: '.$link);
