<?php

$apx->lang->drop('register');
headline($apx->lang->get('HEADLINE_REGISTER'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_REGISTER'));

if ($_POST['send']) {
    $_POST['email1'] = trim($_POST['email1']);
    $_POST['email2'] = trim($_POST['email2']);
    $check = $check2 = false;
    list($check) = $db->first('SELECT username_login FROM '.PRE."_user WHERE LOWER(username_login)='".addslashes(strtolower($_POST['username']))."' LIMIT 1");
    if (!$set['user']['mailmultiacc']) {
        list($check2) = $db->first('SELECT email FROM '.PRE."_user WHERE LOWER(email)='".addslashes(strtolower($_POST['email1']))."' LIMIT 1");
    }
    $blockname = $user->block_username($_POST['username']);

    //Captcha prüfen
    if ($set['user']['captcha']) {
        require BASEDIR.'lib/class.captcha.php';
        $captcha = new captcha();
        $captchafailed = $captcha->check();
    }

    if ($captchafailed) {
        message($apx->lang->get('MSG_WRONGCODE'), 'javascript:history.back()');
    } elseif (!$_POST['username'] || !$_POST['pwd1'] || !$_POST['pwd2'] || !$_POST['email1'] || !$_POST['email2']) {
        message('back');
    } elseif ($_POST['pwd1'] != $_POST['pwd2']) {
        message($apx->lang->get('MSG_PWNOMATCH'), 'javascript:history.back()');
    } elseif ($set['user']['userminlen'] && strlen($_POST['username']) < $set['user']['userminlen']) {
        message($apx->lang->get('MSG_USERLENGTH', ['LENGTH' => $set['user']['userminlen']]), 'javascript:history.back()');
    } elseif ($set['user']['pwdminlen'] && strlen($_POST['pwd1']) < $set['user']['pwdminlen']) {
        message($apx->lang->get('MSG_PWDLENGTH', ['LENGTH' => $set['user']['pwdminlen']]), 'javascript:history.back()');
    } elseif ($_POST['email1'] != $_POST['email2']) {
        message($apx->lang->get('MSG_EMAILNOMATCH'), 'javascript:history.back()');
    } elseif (!checkmail($_POST['email1'])) {
        message($apx->lang->get('MSG_NOMAIL'), 'javascript:history.back()');
    } elseif ($blockname) {
        message($apx->lang->get('MSG_USERNOTALLOWED', ['STRING' => $blockname]), 'javascript:history.back()');
    } elseif ($check) {
        message($apx->lang->get('MSG_USEREXISTS'), 'javascript:history.back()');
    } elseif (!$set['user']['mailmultiacc'] && $check2) {
        message($apx->lang->get('MSG_MAILEXISTS'), 'javascript:history.back()');
    } else {
        //Captcha löschen
        if ($set['user']['captcha']) {
            $captcha->remove();
        }

        if ('www.' == substr($_POST['homepage'], 0, 4)) {
            $_POST['homepage'] = 'http://'.$_POST['homepage'];
        }

        if ($_POST['bd_day'] && $_POST['bd_month'] && $_POST['bd_year']) {
            $_POST['birthday'] = sprintf('%02d-%02d-%04d', $_POST['bd_day'], $_POST['bd_month'], $_POST['bd_year']);
        } elseif ($_POST['bd_day'] && $_POST['bd_day']) {
            $_POST['birthday'] = sprintf('%02d-%02d', $_POST['bd_day'], $_POST['bd_month']);
        } else {
            $_POST['birthday'] = '';
        }

        //Location bestimmen
        $_POST['locid'] = user_get_location($_POST['plz'], $_POST['city'], $_POST['country']);

        if (2 == $set['user']['useractivation']) {
            $_POST['reg_key'] = 'BYADMIN';
        } elseif (3 == $set['user']['useractivation']) {
            $_POST['reg_key'] = random_string();
        }

        $_POST['salt'] = random_string();
        $_POST['password'] = md5(md5($_POST['pwd1']).$_POST['salt']);
        $_POST['groupid'] = $set['user']['defaultgroup'];
        $_POST['email'] = $_POST['reg_email'] = $_POST['email1'];
        $_POST['reg_time'] = time();
        $_POST['lastonline'] = time();
        $_POST['lastactive'] = time();
        $_POST['username_login'] = $_POST['username'];
        $_POST['admin_editor'] = 1;

        $db->dinsert(PRE.'_user', 'username_login,username,password,salt,reg_email,reg_time'.iif(1 != $set['user']['useractivation'], ',reg_key').',lastonline,lastactive,email,groupid,homepage,icq,aim,yim,msn,skype,realname,gender,birthday,city,plz,country,interests,locid,work,signature,pub_invisible,pub_hidemail,pub_poppm,pub_mailpm,pub_showbuddies,pub_usegb,pub_gbmail,pub_lang,pub_theme,admin_editor,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10');

        //eMail-Benachrichtigung
        if ($set['user']['mailonnew']) {
            $input = [
                'URL' => HTTP,
                'USERNAME' => $_POST['username'],
            ];
            sendmail($set['user']['mailonnew'], 'NEWREG', $input);
            unset($input);
        }

        //Message + eMail verschicken
        $input['USERNAME'] = $_POST['username'];
        $input['PASSWORD'] = $_POST['pwd1'];
        $input['WEBSITE'] = $set['main']['websitename'];

        //Bestätigungs-eMail verschicken und ENDE
        if (2 == $set['user']['useractivation']) {
            sendmail($_POST['email'], 'REGADMINACTIVATION', $input);
            message($apx->lang->get('MSG_OK_ADMINACTIVATE'), mklink('user.php', 'user.html'));
        } elseif (3 == $set['user']['useractivation']) {
            $input['URL'] = HTTP_HOST.mklink(
                'user.php?action=activate&userid='.$db->insert_id().'&key='.$_POST['reg_key'],
                'user,activate.html?userid='.$db->insert_id().'&key='.$_POST['reg_key']
            );

            sendmail($_POST['email'], 'REGACTIVATION', $input);
            message($apx->lang->get('MSG_OK_ACTIVATE'), mklink('user.php', 'user.html'));
        } else {
            sendmail($_POST['email'], 'REG', $input);
            message($apx->lang->get('MSG_OK'), mklink('user.php', 'user.html'));
        }
    }
}

//Formular anzeigen
elseif (!$set['user']['acceptrules'] || $_POST['accept']) {
    //Sprachen
    $langlist = '<option value="">'.$apx->lang->get('USEDEFAULT').'</option>';
    foreach ($apx->languages as $id => $name) {
        $langlist .= '<option value="'.$id.'"'.iif($user->info['pub_lang'] == $id, ' selected="selected"').'>'.replace($name).'</option>';
        ++$i;
        $langdata[$i] = [
            'ID' => $id,
            'TITLE' => $name,
        ];
    }

    //Themes
    $handle = opendir(BASEDIR.getpath('tmpldir'));
    while ($file = readdir($handle)) {
        if ('.' == $file || '..' == $file || !is_dir(BASEDIR.getpath('tmpldir').$file)) {
            continue;
        }
        $themes[] = $file;
    }
    closedir($handle);
    sort($themes);

    $themelist = '<option value="">'.$apx->lang->get('USEDEFAULT').'</option>';
    foreach ($themes as $themeid) {
        $themelist .= '<option value="'.$themeid.'"'.iif($themeid == $user->info['pub_theme'], ' selected="selected"').'>'.$themeid.'</option>';
        ++$i;
        $themedata[$i] = [
            'ID' => $themeid,
            'TITLE' => $themeid,
        ];
    }

    //Custom-Felder
    for ($i = 1; $i <= 10; ++$i) {
        $apx->tmpl->assign('CUSTOM'.$i.'_NAME', $set['user']['cusfield_names'][($i - 1)]);
    }

    $postto = mklink(
        'user.php?action=register',
        'user,register.html'
    );

    //Captcha erstellen
    if ($set['user']['captcha']) {
        require BASEDIR.'lib/class.captcha.php';
        $captcha = new captcha();
        $captchacode = $captcha->generate();
    }

    //Alte Variablen für Abwärtskompatiblität
    $apx->tmpl->assign('LANGLIST', $langlist);
    $apx->tmpl->assign('THEMELIST', $themelist);

    $apx->tmpl->assign('LANG', $langdata);
    $apx->tmpl->assign('THEME', $themedata);
    $apx->tmpl->assign('CAPCHA', $captchacode); //Abwärtskompatiblität
    $apx->tmpl->assign('CAPTCHA', $captchacode);
    $apx->tmpl->assign('POSTTO', $postto);
    $apx->tmpl->assign('USERLENGTH', $set['user']['userminlen']);
    $apx->tmpl->assign('PWDLENGTH', $set['user']['pwdminlen']);
    $apx->tmpl->parse('register');
}

//Regeln akzeptieren
else {
    $postto = mklink(
        'user.php?action=register',
        'user,register.html'
    );

    $apx->tmpl->assign('POSTTO', $postto);
    $apx->tmpl->parse('register_rules');
}
