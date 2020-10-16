<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Download-Größe
function user_getsize($fsize, $digits = 1)
{
    $fsize = (float) $fsize;
    if ($digits) {
        $format = '%01.'.$digits.'f';
    } else {
        $format = '%01d';
    }

    if ($fsize < 1024) {
        return $fsize.' Byte';
    }
    if ($fsize >= 1024 && $fsize < 1024 * 1024) {
        return  number_format($fsize / (1024), $digits, ',', '').' KB';
    }
    if ($fsize >= 1024 * 1024 && $fsize < 1024 * 1024 * 1024) {
        return number_format($fsize / (1024 * 1024), $digits, ',', '').' MB';
    }
    if ($fsize >= 1024 * 1024 * 1024 && $fsize < 1024 * 1024 * 1024 * 1024) {
        return number_format($fsize / (1024 * 1024 * 1024), $digits, ',', '').' GB';
    }

    return number_format($fsize / (1024 * 1024 * 1024 * 1024), $digits, ',', '').' TB';
}

//Besuch zählen
function user_count_visit($object, $id)
{
    global $apx,$set,$db,$user;
    if (!$user->info['userid']) {
        return;
    }
    $db->query('DELETE FROM '.PRE."_user_visits WHERE object='".$object."' AND userid='".$user->info['userid']."'");
    $db->query('INSERT INTO '.PRE."_user_visits (object,id,userid,time) VALUES ('".$object."','".$id."','".$user->info['userid']."','".time()."')");
}

//Besucher assign
function user_assign_visitors($object, $id, &$tmpl)
{
    global $apx,$set,$db,$user;

    $userdata = [];
    $data = $db->fetch('SELECT u.userid,u.username,u.groupid,u.realname,u.gender,u.city,u.plz,u.country,u.city,u.lastactive,u.pub_invisible,u.avatar,u.avatar_title,u.custom1,u.custom2,u.custom3,u.custom4,u.custom5,u.custom6,u.custom7,u.custom8,u.custom9,u.custom10 FROM '.PRE.'_user_visits AS v LEFT JOIN '.PRE."_user AS u USING(userid) WHERE v.object='".addslashes($object)."' AND v.id='".intval($id)."' AND v.time>='".(time() - 24 * 3600)."' ORDER BY u.username ASC");
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            $userdata[$i]['ID'] = $res['userid'];
            $userdata[$i]['USERID'] = $res['userid'];
            $userdata[$i]['USERNAME'] = replace($res['username']);
            $userdata[$i]['GROUPID'] = $res['groupid'];
            $userdata[$i]['ONLINE'] = iif(!$res['pub_invisible'] && ($res['lastactive'] + $set['user']['timeout'] * 60) >= time(), 1, 0);
            $userdata[$i]['REALNAME'] = replace($res['realname']);
            $userdata[$i]['GENDER'] = $res['gender'];
            $userdata[$i]['CITY'] = replace($res['city']);
            $userdata[$i]['PLZ'] = replace($res['plz']);
            $userdata[$i]['COUNTRY'] = $res['country'];
            $userdata[$i]['LASTACTIVE'] = $res['lastactive'];
            $userdata[$i]['AVATAR'] = $user->mkavatar($res);
            $userdata[$i]['AVATAR_TITLE'] = $user->mkavtitle($res);

            //Custom-Felder
            for ($ii = 1; $ii <= 10; ++$ii) {
                $tabledata[$i]['CUSTOM'.$ii.'_NAME'] = $set['user']['cusfield_names'][($ii - 1)];
                $tabledata[$i]['CUSTOM'.$ii] = compatible_hsc($res['custom'.$ii]);
            }
        }
    }

    $tmpl->assign('VISITOR', $userdata);
}

//Links zu Profil-Funktionen
function user_assign_profile_links(&$tmpl, $userinfo)
{
    global $apx,$set,$db,$user;

    $link_profile = mklink(
        'user.php?action=profile&amp;id='.$userinfo['userid'],
        'user,profile,'.$userinfo['userid'].urlformat($userinfo['username']).'.html'
    );
    if ($set['user']['blog']) {
        $link_blog = mklink(
            'user.php?action=blog&amp;id='.$userinfo['userid'],
            'user,blog,'.$userinfo['userid'].',1.html'
        );
    }
    if ($set['user']['gallery']) {
        $link_gallery = mklink(
            'user.php?action=gallery&amp;id='.$userinfo['userid'],
            'user,gallery,'.$userinfo['userid'].',0,0.html'
        );
    }
    if ($set['user']['guestbook'] && $userinfo['pub_usegb']) {
        $link_guestbook = mklink(
            'user.php?action=guestbook&amp;id='.$userinfo['userid'],
            'user,guestbook,'.$userinfo['userid'].',1.html'
        );
    }
    if ($apx->is_module('products') && $set['products']['collection']) {
        $link_collection = mklink(
            'user.php?action=collection&amp;id='.$userinfo['userid'],
            'user,collection,'.$userinfo['userid'].',0,1.html'
        );
    }

    $tmpl->assign('LINK_PROFILE', $link_profile);
    $tmpl->assign('LINK_BLOG', $link_blog);
    $tmpl->assign('LINK_GALLERY', $link_gallery);
    $tmpl->assign('LINK_GUESTBOOK', $link_guestbook);
    $tmpl->assign('LINK_COLLECTION', $link_collection);
}
