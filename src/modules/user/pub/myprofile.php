<?php

$apx->lang->drop('myprofile');
headline($apx->lang->get('HEADLINE_MYPROFILE'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_MYPROFILE'));

if ($_POST['send']) {
    //Alter bestätigen
    $identConfirmed = false;
    $identError = '';
    if (!$user->info['ageconfirmed'] && $_POST['identcode'][0] && $_POST['identcode'][1] && $_POST['identcode'][2] && $_POST['identcode'][3]) {
        require_once BASEDIR.getmodulepath('user').'class.identycard.php';
        $identCard = new IdentyCard($_POST['identcode'][0].'D', $_POST['identcode'][1], $_POST['identcode'][2], $_POST['identcode'][3]);
        if (!strlen($_POST['identcode'][0]) || !strlen($_POST['identcode'][1]) || !strlen($_POST['identcode'][2]) || !strlen($_POST['identcode'][3])) {
            $identError = $apx->lang->get('MSG_IDENT_INCOMPLETE');
        } elseif (!preg_match('#^[0-9]+$#', $_POST['identcode'][0].$_POST['identcode'][1].$_POST['identcode'][2].$_POST['identcode'][3])) {
            $identError = $apx->lang->get('MSG_IDENT_INVALIDCHARS');
        } elseif (10 != strlen($_POST['identcode'][0]) || 7 != strlen($_POST['identcode'][1]) || 7 != strlen($_POST['identcode'][2])) {
            $identError = $apx->lang->get('MSG_IDENT_INCOMPLETE');
        } elseif (!$identCard->isValid()) {
            $identError = $apx->lang->get('MSG_IDENT_INVALID');
        } elseif ($identCard->isExpired()) {
            $identError = $apx->lang->get('MSG_IDENT_EXPIRED');
        } elseif ($identCard->getAge() < 18) {
            $identError = $apx->lang->get('MSG_IDENT_TOOYOUNG', ['AGE' => $identCard->getAge()]);
        } else {
            $identConfirmed = true;
        }
    }

    if ((($_POST['pwd1'] || $_POST['pwd2']) && (!$_POST['pwd1'] || !$_POST['pwd2'])) || !$_POST['email']) {
        message('back');
    } elseif ($_POST['pwd1'] != $_POST['pwd2']) {
        message($apx->lang->get('MSG_PWNOMATCH'), 'javascript:history.back()');
    } elseif ($_POST['pwd1'] && $set['user']['pwdminlen'] && strlen($_POST['pwd1']) < $set['user']['pwdminlen']) {
        message($apx->lang->get('MSG_PWDLENGTH', ['LENGTH' => $set['user']['pwdminlen']]), 'javascript:history.back()');
    } elseif ($identError) {
        message($identError, 'javascript:history.back()');
    } elseif (!checkmail($_POST['email'])) {
        message($apx->lang->get('MSG_NOMAIL'), 'javascript:history.back()');
    } else {
        if ('www.' == substr($_POST['homepage'], 0, 4)) {
            $_POST['homepage'] = 'http://'.$_POST['homepage'];
        }
        if ($_POST['pwd1']) {
            $_POST['salt'] = random_string();
            $_POST['password'] = md5(md5($_POST['pwd1']).$_POST['salt']);
        }

        if ($_POST['bd_day'] && $_POST['bd_month'] && $_POST['bd_year']) {
            $_POST['birthday'] = sprintf('%02d-%02d-%04d', $_POST['bd_day'], $_POST['bd_month'], $_POST['bd_year']);
        } elseif ($_POST['bd_day'] && $_POST['bd_day']) {
            $_POST['birthday'] = sprintf('%02d-%02d', $_POST['bd_day'], $_POST['bd_month']);
        } else {
            $_POST['birthday'] = '';
        }

        //Location bestimmen
        if ($_POST['plz'] != $user->info['plz'] || $_POST['city'] != $user->info['city'] || $_POST['country'] != $user->info['country']) {
            $_POST['locid'] = user_get_location($_POST['plz'], $_POST['city'], $_POST['country']);
        } else {
            $_POST['locid'] = $user->info['locid'];
        }

        //Benutzerstatus
        $addstatus = '';
        if (isset($_POST['status']) || isset($_POST['status_smiley'])) {
            $addstatus = ',status,status_smiley';
        }

        //Benutzer muss beim eMail-Änderung seinen Account reaktivieren
        if ($user->info['email'] != $_POST['email'] && 3 == $set['user']['useractivation'] && $set['user']['reactivate']) {
            $_POST['reg_key'] = random_string();
            $_POST['ageconfirmed'] = $identConfirmed;

            $db->dupdate(PRE.'_user', iif($_POST['pwd1'], 'password,salt,').iif($identConfirmed, 'ageconfirmed,').'email,reg_key,homepage,icq,aim,yim,msn,skype,realname,gender,birthday,city,plz,country,locid,interests,work,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10,pub_invisible,pub_hidemail,pub_poppm,pub_mailpm,pub_showbuddies,pub_usegb,pub_gbmail,pub_profileforfriends,pub_lang,pub_theme'.$addstatus.iif($apx->is_module('forum'), ',forum_autosubscribe'), "WHERE userid='".$user->info['userid']."' LIMIT 1");
            $input['USERNAME'] = replace($user->info['username']);
            $input['WEBSITE'] = $set['main']['websitename'];
            $input['URL'] = HTTP_HOST.mklink(
                'user.php?action=activate&userid='.$user->info['userid'].'&key='.$_POST['reg_key'],
                'user,activate.html?userid='.$user->info['userid'].'&key='.$_POST['reg_key']
            );

            sendmail($_POST['email'], 'GETKEY', $input);
            message($apx->lang->get('MSG_OK_NEWEMAIL'), mklink('user.php?action=logout', 'user,logout.html'));
        }

        //Keine Reaktivierung
        else {
            $_POST['ageconfirmed'] = $identConfirmed ? 1 : 0;
            $db->dupdate(PRE.'_user', iif($_POST['pwd1'], 'password,salt,').iif($identConfirmed, 'ageconfirmed,').'email,homepage,icq,aim,yim,msn,skype,realname,gender,birthday,city,plz,locid,country,interests,work,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10,pub_invisible,pub_hidemail,pub_poppm,pub_mailpm,pub_showbuddies,pub_usegb,pub_gbmail,pub_profileforfriends,pub_lang,pub_theme'.$addstatus.iif($apx->is_module('forum'), ',forum_autosubscribe'), "WHERE userid='".$user->info['userid']."' LIMIT 1");
            if ($_POST['pwd1']) {
                message($apx->lang->get('MSG_OK_NEWPWD'), mklink('user.php?action=logout', 'user,logout.html'));
            } else {
                message($apx->lang->get('MSG_OK'), mklink('user.php', 'user.html'));
            }
        }
    }
} else {
    //Sprachen
    $langlist = '<option value="">'.$apx->lang->get('USEDEFAULT').'</option>';
    foreach ($apx->languages as $id => $name) {
        $langlist .= '<option value="'.$id.'"'.iif($user->info['pub_lang'] == $id, ' selected="selected"').'>'.replace($name).'</option>';
        ++$i;
        $langdata[$i] = [
            'ID' => $id,
            'TITLE' => $name,
            'SELECTED' => iif($user->info['pub_lang'] == $id, 1, 0),
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
            'SELECTED' => iif($themeid == $user->info['pub_theme'], 1, 0),
        ];
    }

    list($bd['bd_day'], $bd['bd_month'], $bd['bd_year']) = explode('-', $user->info['birthday']);

    if (count($set['main']['smilies'])) {
        foreach ($set['main']['smilies'] as $res) {
            ++$i;
            $smiledata[$i]['CODE'] = $res['code'];
            $smiledata[$i]['INSERTCODE'] = addslashes($res['code']);
            $smiledata[$i]['IMAGE'] = $res['file'];
            $smiledata[$i]['DESCRIPTION'] = $res['description'];
        }
    }

    $apx->tmpl->assign('SMILEY', $smiledata);
    $apx->tmpl->assign('STATUS', compatible_hsc($user->info['status']));
    $apx->tmpl->assign('STATUS_SMILEY', compatible_hsc($user->info['status_smiley']));
    $apx->tmpl->assign('EMAIL', compatible_hsc($user->info['email']));
    $apx->tmpl->assign('EMAIL_ENCRYPTED', compatible_hsc(cryptMail($user->info['email'])));
    $apx->tmpl->assign('HOMEPAGE', compatible_hsc($user->info['homepage']));
    $apx->tmpl->assign('ICQ', (int) $user->info['icq']);
    $apx->tmpl->assign('AIM', compatible_hsc($user->info['aim']));
    $apx->tmpl->assign('YIM', compatible_hsc($user->info['yim']));
    $apx->tmpl->assign('MSN', compatible_hsc($user->info['msn']));
    $apx->tmpl->assign('SKYPE', compatible_hsc($user->info['skype']));
    $apx->tmpl->assign('REALNAME', compatible_hsc($user->info['realname']));
    $apx->tmpl->assign('CITY', compatible_hsc($user->info['city']));
    $apx->tmpl->assign('COUNTRY', compatible_hsc($user->info['country']));
    $apx->tmpl->assign('PLZ', compatible_hsc($user->info['plz']));
    $apx->tmpl->assign('INTERESTS', compatible_hsc($user->info['interests']));
    $apx->tmpl->assign('WORK', compatible_hsc($user->info['work']));
    $apx->tmpl->assign('GENDER', (int) $user->info['gender']);
    $apx->tmpl->assign('BD_DAY', (int) $bd['bd_day']);
    $apx->tmpl->assign('BD_MONTH', (int) $bd['bd_month']);
    $apx->tmpl->assign('BD_YEAR', (int) $bd['bd_year']);
    $apx->tmpl->assign('AGECONFIRMED', (int) $user->info['ageconfirmed']);
    $apx->tmpl->assign('INVISIBLE', (int) $user->info['pub_invisible']);
    $apx->tmpl->assign('HIDEMAIL', (int) $user->info['pub_hidemail']);
    $apx->tmpl->assign('POPPM', (int) $user->info['pub_poppm']);
    $apx->tmpl->assign('MAILPM', (int) $user->info['pub_mailpm']);
    $apx->tmpl->assign('SHOWBUDDIES', (int) $user->info['pub_showbuddies']);
    $apx->tmpl->assign('USEGB', (int) $user->info['pub_usegb']);
    $apx->tmpl->assign('GBMAIL', (int) $user->info['pub_gbmail']);
    $apx->tmpl->assign('PROFILEFORFRIENDS', (int) $user->info['pub_profileforfriends']);
    $apx->tmpl->assign('AUTOSUBSCRIBE', (int) $user->info['forum_autosubscribe']);
    $apx->tmpl->assign('LANG', $langdata);
    $apx->tmpl->assign('THEME', $themedata);
    $apx->tmpl->assign('PWDLENGTH', $set['user']['pwdminlen']);

    //Alte Platzhalter zwecks Abwärtskompatiblität
    $apx->tmpl->assign('LANGLIST', $langlist);
    $apx->tmpl->assign('THEMELIST', $themelist);

    //Custom-Felder
    for ($i = 1; $i <= 10; ++$i) {
        $apx->tmpl->assign('CUSTOM'.$i.'_NAME', $set['user']['cusfield_names'][($i - 1)]);
        $apx->tmpl->assign('CUSTOM'.$i, compatible_hsc($user->info['custom'.$i]));
    }

    $postto = mklink(
        'user.php?action=myprofile',
        'user,myprofile.html'
    );

    $apx->tmpl->assign('POSTTO', $postto);
    $apx->tmpl->parse('myprofile');
}
