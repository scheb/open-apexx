<?php

if (!$_REQUEST['contentid']) {
    die('missing ContentID!');
}
$apx->lang->drop('report');
$apx->tmpl->loaddesign('blank');
headline($apx->lang->get('HEADLINE_REPORT'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_REPORT'));

//Absenden
if ($_POST['send']) {
    if (!$_POST['text']) {
        message('back');
    } else {
        list($content, $id) = explode(':', $_REQUEST['contentid'], 2);
        $id = (int) $id;
        if (!in_array($content, ['blog', 'blogentry', 'gallery', 'profile'])) {
            die('invalid content-name!');
        }
        if (!$id) {
            die('invalid ID!');
        }

        //Link erzeugen
        if ('blog' == $content) {
            $url = mklink(
                'user.php?action=blog&id='.$id,
                'user,blog,'.$id.',1.html'
            );
        } elseif ('blogentry' == $content) {
            list($userid) = $db->first('SELECT userid FROM '.PRE."_user_blog WHERE id='".$id."' LIMIT 1");
            $url = mklink(
                'user.php?action=blog&id='.$userid.'&blogid='.$id,
                'user,blog,'.$userid.',id'.$id.'.html'
            );
        } elseif ('gallery' == $content) {
            list($userid) = $db->first('SELECT owner FROM '.PRE."_user_gallery WHERE id='".$id."' LIMIT 1");
            $url = mklink(
                'user.php?action=gallery&id='.$userid.'&galid='.$id,
                'user,gallery,'.$userid.','.$id.',0.html'
            );
        } else {
            list($username) = $user->get_info($id, 'username');
            $url = mklink(
                'user.php?action=profile&id='.$id,
                'user,profile,'.$id.urlformat($username).'.html'
            );
        }

        //eMail senden
        if ($set['user']['reportmail']) {
            $input['URL'] = HTTP_HOST.$url;
            $input['REASON'] = $_POST['text'];
            sendmail($set['user']['reportmail'], 'REPORT', $input);
        }

        message($apx->lang->get('MSG_OK'));
    }
} else {
    $apx->tmpl->assign('POSTTO', mklink(
        'user.php?action=report',
        'user,report.html'
    ));
    $apx->tmpl->assign('CONTENTID', compatible_hsc($_REQUEST['contentid']));
    $apx->tmpl->parse('report');
}
