<?php

if (!$set['user']['gallery']) {
    die('function disabled!');
}
$_REQUEST['id'] = (int) $_REQUEST['id'];
$_REQUEST['galid'] = (int) $_REQUEST['galid'];
if (!$_REQUEST['id']) {
    die('missing ID!');
}

//Nur für Registrierte
if ($set['user']['profile_regonly'] && !$user->info['userid']) {
    tmessage('profileregonly', [], false, false);
    require 'lib/_end.php';
}

//Benutzernamen auslesen
$profileInfo = $db->first('SELECT userid,username,pub_usegb,pub_profileforfriends FROM '.PRE."_user WHERE userid='".$_REQUEST['id']."' LIMIT 1");
list($userid, $username, $usegb, $friendonly) = $profileInfo;
$apx->tmpl->assign('USERID', $userid);
$apx->tmpl->assign('USERNAME', replace($username));

//Nur für Freunde
if ($friendonly && !$user->is_buddy_of($userid) && $user->info['userid'] != $userid && 1 != $user->info['groupid']) {
    message($apx->lang->get('MSG_FRIENDSONLY'));
    require 'lib/_end.php';
}

$apx->lang->drop('gallery');
headline($apx->lang->get('HEADLINE_GALLERY'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_GALLERY'));

//Links zu den Profil-Funktionen
user_assign_profile_links($apx->tmpl, $profileInfo);

//Galerie ausgewählt
if ($_REQUEST['galid']) {
    $galid = $_REQUEST['galid'];

    //Zugangsrechte?
    $gallery = $db->first('SELECT * FROM '.PRE."_user_gallery WHERE id='".$galid."' AND owner='".$_REQUEST['id']."' LIMIT 1");
    if (!$gallery['id']) {
        die('access denied!');
    }

    //Passwortschutz
    if ($gallery['password']) {
        $password = $gallery['password'];
        $pwdid = $gallery['id'];
    }
    if ($password && $password == $_POST['password']) {
        setcookie('usergallery_pwd_'.$pwdid, md5(md5($_POST['password']).$set['main']['crypt']), time() + 1 * 24 * 3600);
    } elseif ('admin' != $user->info['gtype'] && $user->info['userid'] != $gallery['owner'] && $password && $_COOKIE['usergallery_pwd_'.$pwdid] != md5(md5($password).$set['main']['crypt'])) {
        tmessage('gallerypwdrequired', ['ID' => $_REQUEST['id'], 'GALID' => $_REQUEST['galid']]);
    }

    //Verwendete Variablen
    $parse = $apx->tmpl->used_vars('gallery_pics');

    //Besucher aufzeichnen und ausgeben
    if (in_array('VISITOR', $parse)) {
        if ($userid != $user->info['userid']) {
            user_count_visit('gallery', $_REQUEST['id']);
        }
        if (!$set['user']['visitorself'] || $userid == $user->info['userid']) {
            user_assign_visitors('gallery', $_REQUEST['id'], $apx->tmpl, $parse);
        }
    }

    //Galerie-Infos
    $images = 0;
    if (in_array('COUNT', $parse)) {
        list($images) = $db->first('SELECT count(*) FROM '.PRE."_user_pictures WHERE galid='".$_REQUEST['galid']."'");
    }
    $apx->tmpl->assign('ID', $gallery['id']);
    $apx->tmpl->assign('TITLE', $gallery['title']);
    $apx->tmpl->assign('DESCRIPTION', $gallery['description']);
    $apx->tmpl->assign('TIME', $gallery['time']);
    $apx->tmpl->assign('LASTUPDATE', $gallery['lastupdate']);
    $apx->tmpl->assign('COUNT', $images);

    //Kommentare
    if ($apx->is_module('comments') && $gallery['allowcoms']) {
        require_once BASEDIR.getmodulepath('comments').'class.comments.php';
        $coms = new comments('usergallery', $gallery['id']);
        $coms->assign_comments($parse);
    }

    //Seitenzahlen
    list($count) = $db->first('SELECT count(id) FROM '.PRE."_user_pictures WHERE galid='".$galid."'");
    pages(
        mklink(
            'user.php?action=gallery&amp;id='.$_REQUEST['id'].'&amp;galid='.$galid,
            'user,gallery,'.$_REQUEST['id'].','.$galid.',{P}.html'
        ),
        $count,
        $set['user']['gallery_epp']
    );

    //Einträge auslesen
    $data = $db->fetch('SELECT * FROM '.PRE."_user_pictures WHERE galid='".$galid."' ORDER BY addtime DESC".getlimit($set['user']['gallery_epp']));
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;
            $size = getimagesize(BASEDIR.getpath('uploads').$res['picture']);
            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['IMAGE'] = HTTPDIR.getpath('uploads').$res['thumbnail'];
            $tabledata[$i]['FULLSIZE'] = HTTPDIR.getpath('uploads').$res['picture'];
            $tabledata[$i]['LINK'] = "javascript:popuppic('misc.php?action=picture&amp;pic=".$res['picture']."','".$size[0]."','".$size[1]."');";
            $tabledata[$i]['CAPTION'] = replace($res['caption']);
        }
    }

    //Verstoß melden
    $link_report = "javascript:popupwin('user.php?action=report&amp;contentid=gallery:".$_REQUEST['galid']."',500,300);";
    $apx->tmpl->assign('LINK_REPORT', $link_report);

    $apx->tmpl->assign('PICTURE', $tabledata);
    $apx->tmpl->parse('gallery_pics');
}

//Keine Galerie ausgewählt
else {
    //Verwendete Variablen
    $parse = $apx->tmpl->used_vars('gallery');

    //Galerien auslesen
    $data = $db->fetch('SELECT * FROM '.PRE."_user_gallery WHERE owner='".$_REQUEST['id']."'");
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            //Link
            $link = mklink(
                'user.php?action=gallery&amp;id='.$_REQUEST['id'].'&amp;galid='.$res['id'],
                'user,gallery,'.$_REQUEST['id'].','.$res['id'].',0.html'
            );

            //Bilder
            $images = 0;
            if (in_array('GALLERY.COUNT', $parse)) {
                list($images) = $db->first('SELECT count(*) FROM '.PRE."_user_pictures WHERE galid='".$res['id']."'");
            }

            //Vorschau
            $preview = '';
            if (in_array('GALLERY.PREVIEW', $parse) && (!$res['password'] || $user->info['userid'] == $res['owner'] || md5(md5($res['password']).$set['main']['crypt']) == $_COOKIE['usergallery_pwd_'.$res['id']])) {
                list($preview) = $db->first('SELECT thumbnail FROM '.PRE."_user_pictures WHERE galid='".$res['id']."' ORDER BY RAND() LIMIT 1");
            }

            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['TITLE'] = replace($res['title']);
            $tabledata[$i]['LINK'] = $link;
            $tabledata[$i]['DESCRIPTION'] = replace($res['description']);
            $tabledata[$i]['COUNT'] = $images;
            $tabledata[$i]['TIME'] = $res['addtime'];
            $tabledata[$i]['LASTUPDATE'] = $res['lastupdate'];
            $tabledata[$i]['PROTECTED'] = iif($res['password'], 1, 0);
            $tabledata[$i]['PREVIEW'] = iif($preview, HTTPDIR.getpath('uploads').$preview);

            //Kommentare
            if ($apx->is_module('comments') && $res['allowcoms']) {
                require_once BASEDIR.getmodulepath('comments').'class.comments.php';
                if (!isset($coms)) {
                    $coms = new comments('usergallery', $res['id']);
                } else {
                    $coms->mid = $res['id'];
                }

                $link = mklink(
                    'user.php?action=gallery&amp;id='.$_REQUEST['id'].'&amp;galid='.$res['id'],
                    'user,gallery,'.$_REQUEST['id'].','.$res['id'].',0.html'
                );

                $tabledata[$i]['COMMENT_COUNT'] = $coms->count();
                $tabledata[$i]['COMMENT_LINK'] = $coms->link($link);
                $tabledata[$i]['DISPLAY_COMMENTS'] = 1;
                if (in_template(['GALLERY.COMMENT_LAST_USERID', 'GALLERY.COMMENT_LAST_NAME', 'GALLERY.COMMENT_LAST_TIME'], $parse)) {
                    $tabledata[$i]['COMMENT_LAST_USERID'] = $coms->last_userid();
                    $tabledata[$i]['COMMENT_LAST_NAME'] = $coms->last_name();
                    $tabledata[$i]['COMMENT_LAST_TIME'] = $coms->last_time();
                }
            }
        }
    }

    $apx->tmpl->assign('GALLERY', $tabledata);
    $apx->tmpl->parse('gallery');
}
