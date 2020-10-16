<?php

if (3 != $set['user']['useractivation']) {
    exit;
}
$apx->lang->drop('activate');
$_REQUEST['userid'] = (int) $_REQUEST['userid'];
if (!$_REQUEST['userid'] || !$_REQUEST['key']) {
    message('back');
    require 'lib/_end.php';
}

$res = $db->first('SELECT userid,reg_key FROM '.PRE."_user WHERE userid='".$_REQUEST['userid']."' LIMIT 1");

if ($res['userid'] && !$res['reg_key']) {
    message($apx->lang->get('MSG_ISACTIVE'), mklink('user.php', 'user.html'));
} elseif ($res['reg_key'] == $_REQUEST['key']) {
    $db->query('UPDATE '.PRE."_user SET reg_key='' WHERE userid='".$_REQUEST['userid']."' LIMIT 1");
    message($apx->lang->get('MSG_OK'), mklink('user.php', 'user.html'));
} else {
    message($apx->lang->get('MSG_WRONGKEY'));
}
