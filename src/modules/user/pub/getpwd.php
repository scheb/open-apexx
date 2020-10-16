<?php

$apx->lang->drop('getpwd');
headline($apx->lang->get('HEADLINE_GETPWD'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_GETPWD'));

//Passwortänderung bestätigen
if ($_REQUEST['verify'] && $_REQUEST['userid']) {
    $_REQUEST['userid'] = (int) $_REQUEST['userid'];
    $res = $db->first('SELECT userid,username_login,email,salt,lastpwget FROM '.PRE."_user WHERE userid='".$_REQUEST['userid']."'");
    $key = md5($res['userid'].$res['lastpwget'].$set['main']['crypt']);

    if ($key != $_REQUEST['verify']) {
        message($apx->lang->get('MSG_NOTALLOWED'), 'back');
    } else {
        $newpwd = random_string();
        $salt = random_string();
        $db->query('UPDATE '.PRE."_user SET password='".md5(md5($newpwd).$salt)."',salt='".$salt."',lastpwget='".time()."',lastpwget_by='".get_remoteaddr()."' WHERE userid='".$res['userid']."' LIMIT 1");

        $input['USERNAME'] = replace($res['username_login']);
        $input['WEBSITE'] = $set['main']['websitename'];
        $input['PWD'] = $newpwd;
        sendmail($res['email'], 'GETPWD', $input);

        message($apx->lang->get('MSG_OK_PWD'), mklink('user.php', 'user.html'));
    }
}

//Passwortänderung einleiten
elseif ($_POST['send']) {
    if (!$_POST['username']) {
        message('back');
    } else {
        $res = $db->first('SELECT userid,username_login,email,salt,lastpwget FROM '.PRE."_user WHERE username_login='".addslashes($_POST['username'])."'");

        if (!$res['userid']) {
            message($apx->lang->get('MSG_NOMATCH'), 'javascript:history.back()');
        } else {
            $key = md5($res['userid'].$res['lastpwget'].$set['main']['crypt']);

            $input['USERNAME'] = replace($res['username_login']);
            $input['WEBSITE'] = $set['main']['websitename'];
            $input['URL'] = HTTP_HOST.mklink(
                'user.php?action=getpwd&userid='.$res['userid'].'&verify='.$key,
                'user,getpwd.html?userid='.$res['userid'].'&verify='.$key
            );
            sendmail($res['email'], 'GETPWDREQ', $input);

            message($apx->lang->get('MSG_OK_PWDREQ', ['EMAIL' => $res['email']]), mklink('user.php', 'user.html'));
        }
    }
}

//Formular zeigen
else {
    $postto = mklink(
        'user.php?action=getpwd',
        'user,getpwd.html'
    );

    $apx->tmpl->assign('POSTTO', $postto);
    $apx->tmpl->parse('getpwd');
}
