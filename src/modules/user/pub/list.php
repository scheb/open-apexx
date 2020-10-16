<?php

$apx->lang->drop('userlist');
headline($apx->lang->get('HEADLINE_USERLIST'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_USERLIST'));
$where = '';

//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('list');

//Link zur Usersuche
$searchlink = mklink(
    'user.php?action=search',
    'user,search.html'
);
$apx->tmpl->assign('LINK_SEARCH', $searchlink);

//Anzahl
if (in_array('USERCOUNT', $parse)) {
    list($totalcount) = $db->first('SELECT count(*) FROM '.PRE.'_user WHERE active=1');
    $apx->tmpl->assign('USERCOUNT', $totalcount);
}
if (in_array('TODAYCOUNT', $parse)) {
    list($todaycount) = $db->first('SELECT count(*) FROM '.PRE."_user WHERE active=1 AND reg_time>='".mktime(0, 0, 0, date('n', time() - TIMEDIFF), date('d', time() - TIMEDIFF), date('Y', time() - TIMEDIFF))."'");
    $apx->tmpl->assign('TODAYCOUNT', $todaycount);
}

if (!$_REQUEST['letter']) {
    $_REQUEST['letter'] = '0';
}

//Buchstaben-Liste
letters(mklink(
    'user.php?action=list&amp;sortby='.$_REQUEST['sortby'],
    'user,list,{LETTER},1.html'.iif($_REQUEST['sortby'], '?sortby='.$_REQUEST['sortby'])
));

if ($_REQUEST['letter']) {
    if ('spchar' == $_REQUEST['letter']) {
        $where = 'AND username NOT REGEXP("^[a-zA-Z]")';
    } else {
        $where = "AND username LIKE '".addslashes($_REQUEST['letter'])."%'";
    }
}
if ($set['user']['listactiveonly']) {
    $where .= " AND reg_key='' ";
}

//Seitenzahlen
list($count) = $db->first('SELECT count(userid) FROM '.PRE."_user WHERE ( active='1' ".$where.' )');
$pagelink = mklink(
    'user.php?action=list&amp;sortby='.$_REQUEST['sortby'].'&amp;letter='.$_REQUEST['letter'],
    'user,list,'.$_REQUEST['letter'].',{P}.html'.iif($_REQUEST['sortby'], '?sortby='.$_REQUEST['sortby'])
);
pages($pagelink, $count, $set['user']['userlistepp']);

//Orderby
$orderdef[0] = 'username';
$orderdef['regdate'] = ['reg_time', 'DESC'];
$orderdef['username'] = ['username', 'ASC'];
if ($apx->is_module('forum')) {
    $orderdef['forumposts'] = ['forum_posts', 'DESC'];
}

$fields = 'userid,username,email,groupid,reg_time,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,birthday,pub_hidemail,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10';
if ($apx->is_module('forum')) {
    $fields .= ',forum_posts';
}
$data = $db->fetch('SELECT '.$fields.' FROM '.PRE."_user WHERE ( active='1' ".$where.' ) '.getorder($orderdef).' '.getlimit($set['user']['userlistepp']));
if (count($data)) {
    foreach ($data as $res) {
        ++$i;

        $age = 0;
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

        $tabledata[$i]['ID'] = $res['userid'];
        $tabledata[$i]['USERID'] = $res['userid'];
        $tabledata[$i]['NAME'] = replace($res['username']);
        $tabledata[$i]['USERNAME'] = replace($res['username']);
        $tabledata[$i]['GROUPID'] = $res['groupid'];
        $tabledata[$i]['EMAIL'] = replace(iif(!$res['pub_hidemail'], $res['email']));
        $tabledata[$i]['EMAIL_ENCRYPTED'] = replace(iif(!$res['pub_hidemail'], cryptMail($res['email'])));
        $tabledata[$i]['ONLINE'] = iif(!$res['pub_invisible'] && ($res['lastactive'] + $set['user']['timeout'] * 60) >= time(), 1, 0);
        $tabledata[$i]['ISONLINE'] = $tabledata[$i]['ONLINE'];
        $tabledata[$i]['REALNAME'] = replace($res['realname']);
        $tabledata[$i]['GENDER'] = $res['gender'];
        $tabledata[$i]['CITY'] = replace($res['city']);
        $tabledata[$i]['PLZ'] = replace($res['plz']);
        $tabledata[$i]['COUNTRY'] = $res['country'];
        $tabledata[$i]['REGTIME'] = $res['reg_time'];
        $tabledata[$i]['REGDAYS'] = floor((time() - $res['reg_time']) / (24 * 3600));
        $tabledata[$i]['LASTACTIVE'] = $res['lastactive'];
        $tabledata[$i]['AVATAR'] = $user->mkavatar($res);
        $tabledata[$i]['AVATAR_TITLE'] = $user->mkavtitle($res);
        $tabledata[$i]['BIRTHDAY'] = $birthday;
        $tabledata[$i]['AGE'] = $age;
        if (in_array('USER.ISBUDDY', $parse)) {
            $tabledata[$i]['ISBUDDY'] = $user->is_buddy($res['userid']);
        }

        //Custom-Felder
        for ($ii = 1; $ii <= 10; ++$ii) {
            $tabledata[$i]['CUSTOM'.$ii.'_NAME'] = $set['user']['cusfield_names'][($ii - 1)];
            $tabledata[$i]['CUSTOM'.$ii] = compatible_hsc($res['custom'.$ii]);
        }

        //Interaktions-Links
        if ($user->info['userid']) {
            $tabledata[$i]['LINK_SENDPM'] = mklink(
                'user.php?action=newpm&amp;touser='.$res['userid'],
                'user,newpm,'.$res['userid'].'.html'
            );

            $tabledata[$i]['LINK_SENDEMAIL'] = mklink(
                'user.php?action=newmail&amp;touser='.$res['userid'],
                'user,newmail,'.$res['userid'].'.html'
            );

            if (in_array('USER.LINK_BUDDY', $parse) && $userid != $user->info['userid'] && !$user->is_buddy($res['userid'])) {
                $tabledata[$i]['LINK_BUDDY'] = mklink(
                    'user.php?action=addbuddy&amp;id='.$res['userid'],
                    'user,addbuddy,'.$res['userid'].'.html'
                );
            }
        }

        //Forumbeiträge
        if ($apx->is_module('forum')) {
            $tabledata[$i]['FORUMPOSTS'] = $res['forum_posts'];
        }
    }
}

//Sortieren nach...
ordervars(
    $orderdef,
    mklink(
        'user.php?action=list&amp;letter='.$_REQUEST['letter'],
        'user,list,'.$_REQUEST['letter'].',1.html'
    )
);

$apx->tmpl->assign('USER', $tabledata);
$apx->tmpl->parse('list');
