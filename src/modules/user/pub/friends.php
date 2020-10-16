<?php

$apx->lang->drop('friends');
headline($apx->lang->get('HEADLINE_FRIENDS'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_FRIENDS'));

//Seitenzahlen
list($count) = $db->first('SELECT count(userid) FROM '.PRE."_user WHERE ( active='1' ".$where.' )');
$pagelink = mklink(
    'user.php?action=list&amp;sortby='.$_REQUEST['sortby'].'&amp;letter='.$_REQUEST['letter'],
    'user,list,'.$_REQUEST['letter'].',{P}.html'.iif($_REQUEST['sortby'], '?sortby='.$_REQUEST['sortby'])
);
pages($pagelink, $count, $set['user']['friendsepp']);

//Buddyliste
$userdata = [];
$buddies = $user->get_buddies();
if (count($buddies)) {
    $data = $db->fetch('SELECT userid,username,groupid,realname,gender,city,plz,country,city,lastactive,pub_invisible,avatar,avatar_title,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM '.PRE.'_user WHERE userid IN ('.implode(',', $buddies).') ORDER BY username ASC'.getlimit($set['user']['friendsepp']));
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

            $userdata[$i]['LINK_DELBUDDY'] = mklink(
                'user.php?action=delbuddy&amp;id='.$res['userid'],
                'user,delbuddy,'.$res['userid'].'.html'
            );

            $userdata[$i]['LINK_SENDPM'] = mklink(
                'user.php?action=newpm&amp;touser='.$res['userid'],
                'user,newpm,'.$res['userid'].'.html'
            );

            $userdata[$i]['LINK_SENDEMAIL'] = mklink(
                'user.php?action=newmail&amp;touser='.$res['userid'],
                'user,newmail,'.$res['userid'].'.html'
            );
        }
    }
}

$apx->tmpl->assign('FRIEND', $userdata);
$apx->tmpl->parse('friends');
