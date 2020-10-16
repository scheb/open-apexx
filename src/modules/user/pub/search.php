<?php

$apx->lang->drop('search');
$apx->lang->drop('userlist');
headline($apx->lang->get('HEADLINE_SEARCH'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_SEARCH'));

//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('search');

//Suche durchführen
if ($_POST['send']) {
    $_POST['locid'] = (int) $_POST['locid'];
    $_POST['age_min'] = (int) $_POST['age_min'];
    $_POST['age_max'] = (int) $_POST['age_max'];
    $where = '';

    //Wohnort ausgewählt
    if ($_POST['locid']) {
        //Locid-Suche
        list($l, $b) = $db->first('SELECT l,b FROM '.PRE."_user_locations WHERE id='".$_POST['locid']."' LIMIT 1");
        $distance = (int) $_POST['distance'];
        $data = $db->fetch('
			SELECT id
			FROM `'.PRE.'_user_locations`
			WHERE (sqrt((l-'.$l.')*70*(l-'.$l.')*70+(b-'.$b.')*111*(b-'.$b.')*111))<='.$distance.'
		');
        $neighbours = get_ids($data, 'id');

        //Auf Locids eingrenzen
        $where .= ' AND locid IN ('.implode(',', $neighbours).') ';
    }

    //Suche nach PLZ bzw. Ortsnamen
    elseif ($_POST['city'] || $_POST['plz']) {
        //PLZ bekannt
        if ($_POST['plz']) {
            $plzstamp = sprintf('%05d', intval($_POST['plz']));
            $data = $db->fetch('
				SELECT l.id,l.name,p.stamp
				FROM '.PRE.'_user_locations_plz AS p
				LEFT JOIN '.PRE."_user_locations AS l ON p.locid=l.id
				WHERE p.plz='".addslashes($plzstamp)."'
				GROUP BY p.stamp,l.name
				ORDER BY p.stamp ASC
			");
            $locids = get_ids($data, 'id');

            //City-Match bei mehreren Treffern
            if (count($data) > 1 && $_POST['city']) {
                foreach ($data as $key => $res) {
                    if (!user_city_match($res['name'], $_POST['city'])) {
                        unset($data[$key]);
                    }
                }
                $locids = get_ids($data, 'id');
            }
        }

        //Stadtname bekannt
        elseif ($_POST['city']) {
            $name = user_city_mysql_match($_POST['city']);
            $data = $db->fetch('
				SELECT l.id,l.name,p.stamp
				FROM '.PRE.'_user_locations_plz AS p
				LEFT JOIN '.PRE."_user_locations AS l ON p.locid=l.id
				WHERE l.name LIKE '".addslashes($name)."'
				GROUP BY p.stamp,l.name
				ORDER BY p.stamp ASC
			");
            $locids = get_ids($data, 'id');
        }

        //Keine passende Stadt gefunden
        if (!count($locids)) {
            message($apx->lang->get('MSG_NOCITY'), 'back');
            require 'lib/_end.php';
        }

        //Mehrere Städte gefunden => Auswählen
        elseif (count($locids) > 1) {
            $inputfields = '';
            foreach ($_POST as $key => $value) {
                if ('locid' == $key) {
                    continue;
                }
                $inputfields .= '<input type="hidden" name="'.$key.'" value="'.compatible_hsc($value).'" />';
            }
            $select = [];
            foreach ($data as $res) {
                ++$i;
                $select[$i]['ID'] = $res['id'];
                $select[$i]['NAME'] = $res['stamp'].' '.replace($res['name']);
            }
            $input = [
                'INPUTS' => $inputfields,
                'SELECT' => $select,
            ];
            tmessage('choosecity', $input);
        }

        //Locid-Suche
        list($l, $b) = $db->first('SELECT l,b FROM '.PRE."_user_locations WHERE id='".$locids[0]."' LIMIT 1");
        $distance = (int) $_POST['distance'];
        $data = $db->fetch('
			SELECT id
			FROM `'.PRE.'_user_locations`
			WHERE (sqrt((l-'.$l.')*70*(l-'.$l.')*70+(b-'.$b.')*111*(b-'.$b.')*111))<='.$distance.'
		');
        $neighbours = get_ids($data, 'id');

        //Auf Locids eingrenzen
        $where .= ' AND locid IN ('.implode(',', $neighbours).') ';
    }

    //Suchbegriff
    if ($_POST['item']) {
        $items = explode(' ', $_POST['item']);
        $items = array_map('trim', $items);
        $itemsearchfields = [
            'username',
            'homepage',
            'realname',
            'interests',
            'work',
        ];
        for ($i = 1; $i <= 10; ++$i) {
            if ($set['user']['cusfield_names'][($i - 1)]) {
                $itemsearchfields[] = 'custom'.$i;
            }
        }
        foreach ($items as $item) {
            $itemsearch .= ' AND ( ';
            $elementsearch = '';
            foreach ($itemsearchfields as $fieldname) {
                if ($elementsearch) {
                    $elementsearch .= ' OR ';
                }
                $elementsearch .= ' '.$fieldname." LIKE '%".addslashes_like($item)."%' ";
            }
            $itemsearch .= $elementsearch.' ) ';
        }
        $where .= $itemsearch;
    }

    //Alter
    if ($_POST['age_min'] || $_POST['age_max']) {
        $min = $_POST['age_min'];
        $max = $_POST['age_max'];
        if ($min && $max) {
            $where .= ' AND (IF(LENGTH(birthday)>5,IF(CONCAT(SUBSTRING(birthday,4,2),LEFT(birthday,2))<='.date('md', time() - TIMEDIFF).','.date('Y', time() - TIMEDIFF).' -RIGHT(birthday,4),'.date('Y', time() - TIMEDIFF)."-1-RIGHT(birthday,4)),NULL)) BETWEEN '".$min."' AND '".$max."' ";
        } elseif ($min && !$max) {
            $where .= ' AND (IF(LENGTH(birthday)>5,IF(CONCAT(SUBSTRING(birthday,4,2),LEFT(birthday,2))<='.date('md', time() - TIMEDIFF).','.date('Y', time() - TIMEDIFF).' -RIGHT(birthday,4),'.date('Y', time() - TIMEDIFF)."-1-RIGHT(birthday,4)),NULL))>='".$min."' ";
        } elseif (!$min && $max) {
            $where .= ' AND (IF(LENGTH(birthday)>5,IF(CONCAT(SUBSTRING(birthday,4,2),LEFT(birthday,2))<='.date('md', time() - TIMEDIFF).','.date('Y', time() - TIMEDIFF).' -RIGHT(birthday,4),'.date('Y', time() - TIMEDIFF)."-1-RIGHT(p.birthday,4)),NULL))<='".$max."' ";
        }
    }

    //Geschlecht
    if ($_POST['gender']) {
        if (1 == $_POST['gender']) {
            $where .= ' AND gender=1 ';
        } elseif (2 == $_POST['gender']) {
            $where .= ' AND gender=2 ';
        }
    }

    //Online
    if ($_POST['online']) {
        /*$data = $db->fetch("SELECT userid FROM ".PRE."_user_online WHERE userid!='0' AND invisible='0'");
        $onlineIds = get_ids($data, 'userid');
        $onlineIds[] = -1;
        $where .= " AND userid IN (".implode(',', $onlineIds).") ";*/
        $where .= ' AND lastactive>='.(time() - $set['user']['timeout'] * 60).' AND pub_invisible=0';
    }

    //Keine Suchkriterien vorhanden
    if (!$where) {
        message($apx->lang->get('CORE_BACK'), 'back');
        require 'lib/_end.php';
    }

    //Suche durchführen
    else {
        if ($set['user']['listactiveonly']) {
            $where .= " AND reg_key='' ";
        }

        $data = $db->fetch('SELECT userid FROM '.PRE.'_user WHERE 1 '.$where);
        $result = get_ids($data, 'userid');
        if (!count($result)) {
            message($apx->lang->get('MSG_NORESULT'), 'back');
            require 'lib/_end.php';
        }

        //Suche speichern und weiterleiten
        $searchid = md5(uniqid('search').microtime());
        $db->query('INSERT INTO '.PRE."_search VALUES ('".addslashes($searchid)."','usersearch','".addslashes(serialize($result))."','".addslashes(serialize($_POST))."','".time()."')");
        $redirect = str_replace('&amp;', '&', mklink(
            'user.php?action=search&searchid='.$searchid,
            'user,search.html?searchid='.$searchid
        ));
        header('HTTP/1.1 301 Moved Permanently');
        header('location:'.$redirect);
        exit;
    }
}

//Suchergebnisse
if ($_REQUEST['searchid']) {
    list($results, $options) = $db->first('SELECT results,options FROM '.PRE."_search WHERE object='usersearch' AND searchid='".addslashes($_REQUEST['searchid'])."' ORDER BY time DESC");
    $results = unserialize($results);
    $_POST = unserialize($options);

    //Seitenzahlen
    list($count) = $db->first('SELECT count(userid) FROM '.PRE."_user WHERE ( active='1' AND userid IN (".implode(',', $results).') )');
    $pagelink = mklink(
        'user.php?action=search&amp;searchid='.$_REQUEST['searchid'].'&amp;sortby='.$_REQUEST['sortby'].'&amp;letter='.$_REQUEST['letter'],
        'user,search.html?searchid='.$_REQUEST['searchid'].iif($_REQUEST['sortby'], '&amp;sortby='.$_REQUEST['sortby'])
    );
    pages($pagelink, $count, $set['user']['userlistepp']);

    //Orderby
    $orderdef[0] = 'username';
    $orderdef['regdate'] = ['reg_time', 'DESC'];
    $orderdef['username'] = ['username', 'ASC'];
    if ($apx->is_module('forum')) {
        $orderdef['forumposts'] = ['forum_posts', 'DESC'];
    }

    if ($apx->is_module('forum')) {
        $data = $db->fetch('SELECT userid,username,email,reg_time,pub_hidemail,groupid,realname,gender,city,plz,country,city,lastactive,avatar,avatar_title,forum_posts,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM '.PRE."_user WHERE ( active='1' AND userid IN (".implode(',', $results).') ) '.getorder($orderdef).' '.getlimit($set['user']['userlistepp']));
    } else {
        $data = $db->fetch('SELECT userid,username,email,reg_time,pub_hidemail,groupid,realname,gender,city,plz,country,city,lastactive,avatar,avatar_title,custom1,custom2,custom3,custom4,custom5,custom6,custom7,custom8,custom9,custom10 FROM '.PRE."_user WHERE ( active='1' AND userid IN (".implode(',', $results).') ) '.getorder($orderdef).' '.getlimit($set['user']['userlistepp']));
    }
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;
            $tabledata[$i]['ID'] = $res['userid'];
            $tabledata[$i]['NAME'] = $res['username'];
            $tabledata[$i]['REGTIME'] = $res['reg_time'];
            $tabledata[$i]['LASTACTIVE'] = $res['lastactive'];
            $tabledata[$i]['REGDAYS'] = floor((time() - $res['reg_time']) / (24 * 3600));
            $tabledata[$i]['EMAIL'] = replace(iif(!$res['pub_hidemail'], $res['email']));
            $tabledata[$i]['EMAIL_ENCRYPTED'] = replace(iif(!$res['pub_hidemail'], cryptMail($res['email'])));
            $tabledata[$i]['GROUPID'] = $res['groupid'];
            $tabledata[$i]['REALNAME'] = replace($res['realname']);
            $tabledata[$i]['GENDER'] = $res['gender'];
            $tabledata[$i]['CITY'] = replace($res['city']);
            $tabledata[$i]['PLZ'] = replace($res['plz']);
            $tabledata[$i]['COUNTRY'] = $res['country'];
            $tabledata[$i]['AVATAR'] = $user->mkavatar($res);
            $tabledata[$i]['AVATAR_TITLE'] = $user->mkavtitle($res);
            $tabledata[$i]['ISBUDDY'] = $user->is_buddy($res['userid']);

            //Custom-Felder
            for ($ii = 1; $ii <= 10; ++$ii) {
                $tabledata[$i]['CUSTOM'.$ii.'_NAME'] = $set['user']['cusfield_names'][($ii - 1)];
                $tabledata[$i]['CUSTOM'.$ii] = compatible_hsc($res['custom'.$ii]);
            }

            //Forumbeiträge
            if ($apx->is_module('forum')) {
                $tabledata[$i]['FORUMPOSTS'] = $res['forum_posts'];
            }

            $tabledata[$i]['LINK_BUDDY'] = iif($user->info['userid'] && !$user->is_buddy($res['userid']), mklink(
                'user.php?action=addbuddy&amp;id='.$res['userid'],
                'user,addbuddy,'.$res['userid'].'.html'
            ));

            $tabledata[$i]['LINK_SENDPM'] = iif($user->info['userid'], mklink(
                'user.php?action=newpm&amp;touser='.$res['userid'],
                'user,newpm,'.$res['userid'].'.html'
            ));

            $tabledata[$i]['LINK_SENDEMAIL'] = iif($user->info['userid'] || $set['user']['sendmail_guests'], mklink(
                'user.php?action=newmail&amp;touser='.$res['userid'],
                'user,newmail,'.$res['userid'].'.html'
            ));
        }
    }

    //Sortieren nach...
    ordervars(
        $orderdef,
        mklink(
            'user.php?action=search&amp;searchid='.$_REQUEST['searchid'],
            'user,search.html?searchid='.$_REQUEST['searchid']
        )
    );

    $apx->tmpl->assign('USER', $tabledata);
}

//Noch keine Suchergebnisse
else {
    $_POST['gender'] = 0;
    $_POST['distance'] = 25;
}

//Formular erzeugen
$apx->tmpl->assign('ITEM', compatible_hsc($_POST['item']));
$apx->tmpl->assign('AGE_MIN', intval($_POST['age_min']));
$apx->tmpl->assign('AGE_MAX', intval($_POST['age_max']));
$apx->tmpl->assign('GENDER', intval($_POST['gender']));
$apx->tmpl->assign('CITY', compatible_hsc($_POST['city']));
$apx->tmpl->assign('PLZ', compatible_hsc($_POST['plz']));
$apx->tmpl->assign('DISTANCE', intval($_POST['distance']));
$apx->tmpl->assign('ONLINE', intval($_POST['online']));
$postto = mklink(
    'user.php?action=search',
    'user,search.html'
);
$apx->tmpl->assign('POSTTO', $postto);
$apx->tmpl->parse('search');
