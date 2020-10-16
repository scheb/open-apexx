<?php

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_start.php';  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->module('user');
$apx->lang->drop('team');
headline($apx->lang->get('HEADLINE'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE'));

//Daten erzeugen
function createTeamData($res, $parse)
{
    global $apx, $db, $set, $user;
    $userdata = [];

    if ($res['birthday']) {
        $bd = explode('-', $res['birthday']);
        $birthday = intval($bd[0]).'. '.getcalmonth($bd[1]).iif($bd[2], ' '.$bd[2]);
        if ($bd[2]) {
            $age = date('Y') - $bd[2];
            if (intval(sprintf('%02d%02d', $bd[1], $bd[0])) > intval(date('md'))) {
                --$age;
            }
        }
    }

    $userdata['ID'] = $res['userid'];
    $userdata['USERID'] = $res['userid'];
    $userdata['NAME'] = replace($res['username']);
    $userdata['USERNAME'] = replace($res['username']);
    $userdata['GROUPID'] = $res['groupid'];
    $userdata['EMAIL'] = replace(iif(!$res['pub_hidemail'], $res['email']));
    $userdata['EMAIL_ENCRYPTED'] = replace(iif(!$res['pub_hidemail'], cryptMail($res['email'])));
    $userdata['ONLINE'] = iif(!$res['pub_invisible'] && ($res['lastactive'] + $set['user']['timeout'] * 60) >= time(), 1, 0);
    $userdata['ISONLINE'] = $userdata['ONLINE'];
    $userdata['REALNAME'] = replace($res['realname']);
    $userdata['GENDER'] = $res['gender'];
    $userdata['CITY'] = replace($res['city']);
    $userdata['PLZ'] = replace($res['plz']);
    $userdata['COUNTRY'] = $res['country'];
    $userdata['REGTIME'] = $res['reg_time'];
    $userdata['REGDAYS'] = floor((time() - $res['reg_time']) / (24 * 3600));
    $userdata['LASTACTIVE'] = $res['lastactive'];
    $userdata['AVATAR'] = $user->mkavatar($res);
    $userdata['AVATAR_TITLE'] = $user->mkavtitle($res);
    $userdata['BIRTHDAY'] = $birthday;
    $userdata['AGE'] = $age;
    if (in_array($varname.'.ISBUDDY', $parse)) {
        $userdata['ISBUDDY'] = $user->is_buddy($res['userid']);
    }

    //Custom-Felder
    for ($ii = 1; $ii <= 10; ++$ii) {
        $userdata['CUSTOM'.$ii.'_NAME'] = $set['user']['cusfield_names'][($ii - 1)];
        $userdata['CUSTOM'.$ii] = compatible_hsc($res['custom'.$ii]);
    }

    //Interaktions-Links
    if ($user->info['userid']) {
        $userdata['LINK_SENDPM'] = mklink(
            'user.php?action=newpm&amp;touser='.$res['userid'],
            'user,newpm,'.$res['userid'].'.html'
        );

        $userdata['LINK_SENDEMAIL'] = mklink(
            'user.php?action=newmail&amp;touser='.$res['userid'],
            'user,newmail,'.$res['userid'].'.html'
        );

        if (in_array($varname.'.LINK_BUDDY', $parse) && !$user->is_buddy($res['userid'])) {
            $userdata['LINK_BUDDY'] = mklink(
                'user.php?action=addbuddy&amp;id='.$res['userid'],
                'user,addbuddy,'.$res['userid'].'.html'
            );
        }
    }

    return $userdata;
}

//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('team');

//Benutzer auslesen
$data = $db->fetch('
	SELECT u.userid,u.username,u.email,u.groupid,u.reg_time,u.realname,u.gender,u.city,u.plz,u.country,u.city,u.lastactive,u.pub_invisible,u.avatar,u.avatar_title,u.birthday,u.pub_hidemail,u.custom1,u.custom2,u.custom3,u.custom4,u.custom5,u.custom6,u.custom7,u.custom8,u.custom9,u.custom10,g.groupid,g.name,g.gtype
	FROM '.PRE.'_user AS u
	LEFT JOIN '.PRE."_user_groups AS g USING(groupid)
	WHERE g.gtype IN ('admin', 'indiv') AND u.active=1
	ORDER BY g.gtype ASC, g.name ASC, u.username ASC
");
$gi = 0;
$lastgroup = 0;
$groupdata = [];
if (count($data)) {
    foreach ($data as $res) {
        ++$i;

        //Neue Gruppe beginnt
        if ($res['groupid'] != $lastgroup) {
            //User in vorherige Gruppe einfügen
            if ($groupdata) {
                $tabledata[$gi]['USER'] = $groupdata;
            }

            ++$gi;
            $tabledata[$gi]['ID'] = $res['groupid'];
            $tabledata[$gi]['TITLE'] = compatible_hsc($res['name']);
            $tabledata[$gi]['ISADMIN'] = 'admin' == $res['gtype'];
            $groupdata = [];
        }
        $lastgroup = $res['groupid'];
        $groupdata[$i] = createTeamData($res, $parse);
    }
}

//Letzte Gruppe einfügen
if ($groupdata) {
    $tabledata[$gi]['USER'] = $groupdata;
}

//Forum-Moderatoren
if ($apx->is_module('forum')) {
    $data = $db->fetch('
		SELECT moderator FROM '.PRE."_forums
		WHERE moderator!='' AND moderator!='|'
	");
    $modIds = [];
    foreach ($data as $res) {
        $modIds = array_merge($modIds, dash_unserialize($res['moderator']));
    }
    if ($modIds) {
        $data = $db->fetch('
			SELECT u.userid,u.username,u.email,u.groupid,u.reg_time,u.realname,u.gender,u.city,u.plz,u.country,u.city,u.lastactive,u.pub_invisible,u.avatar,u.avatar_title,u.birthday,u.pub_hidemail,u.custom1,u.custom2,u.custom3,u.custom4,u.custom5,u.custom6,u.custom7,u.custom8,u.custom9,u.custom10
			FROM '.PRE.'_user AS u
			WHERE u.active=1 AND u.userid IN ('.implode(',', $modIds).')
			ORDER BY u.username ASC
		');
    }
    $groupdata = [];
    foreach ($data as $res) {
        $groupdata[] = createTeamData($res, $parse);
    }

    ++$gi;
    $tabledata[$gi]['ID'] = 0;
    $tabledata[$gi]['TITLE'] = $apx->lang->get('FORUM_MOD');
    $tabledata[$gi]['ISADMIN'] = false;
    $tabledata[$gi]['USER'] = $groupdata;
}

$apx->tmpl->assign('GROUP', $tabledata);
$apx->tmpl->parse('team');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
