<?php

$apx->lang->drop('usermap');
headline($apx->lang->get('HEADLINE_USERMAP'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_USERMAP'));
$tabledata = [];

$topleft_x = $set['user']['usermap_topleft_x'];
$topleft_y = $set['user']['usermap_topleft_y'];
$bottomright_x = $set['user']['usermap_bottomright_x'];
$bottomright_y = $set['user']['usermap_bottomright_y'];
$mapwidth = $set['user']['usermap_width'];
$mapheight = $set['user']['usermap_height'];

//Orte auslesen
$posids = [];
$location = [];
$data = $db->fetch('SELECT userid,username,locid FROM '.PRE.'_user WHERE locid!=0 ORDER BY username ASC');
if (count($data)) {
    foreach ($data as $res) {
        $locationid = $res['locid'];
        if (!isset($location[$locationid])) {
            $location[$locationid] = [];
        }
        $location[$locationid][] = [
            'userid' => $res['userid'],
            'username' => $res['username'],
        ];
        $posids[] = $locationid;
    }
}

//Positionen auslesen
if (count($location)) {
    $posids = array_unique($posids);
    $position = $db->fetch_index('SELECT id,name,l,b FROM '.PRE."_user_locations WHERE id IN ('".implode("','", $posids)."')", 'id');
    if (count($position)) {
        foreach ($location as $lockey => $locinfo) {
            if (!isset($position[$lockey])) {
                continue;
            }
            $posinfo = $position[$lockey];
            $posx = $posinfo['l'];
            $posy = $posinfo['b'];

            if (!($posx >= $topleft_x && $posx <= $bottomright_x && $posy <= $topleft_y && $posy >= $bottomright_y)) {
                continue;
            }
            $posleft = floor(($posx - $topleft_x) * ($mapwidth / ($bottomright_x - $topleft_x)));
            $postop = floor(($posy - $topleft_y) * ($mapheight / ($bottomright_y - $topleft_y)));

            //Benutzer
            $userdata = [];
            foreach ($locinfo as $userinfo) {
                ++$ii;
                $userdata[$ii]['ID'] = $userinfo['userid'];
                $userdata[$ii]['NAME'] = replace($userinfo['username']);
            }

            //Position ausgleichen
            $usercount = count($userdata);
            if ($usercount > 10) {
                $sub = 3;
            } elseif ($usercount > 5) {
                $sub = 2;
            } else {
                $sub = 1;
            }
            $posleft -= $sub;
            $postop -= $sub;

            //Ausgabe
            ++$i;
            $tabledata[$i]['ID'] = $lockey;
            $tabledata[$i]['CITY'] = $posinfo['name'];
            $tabledata[$i]['POS_TOP'] = $postop;
            $tabledata[$i]['POS_LEFT'] = $posleft;
            $tabledata[$i]['USER'] = $userdata;
            $tabledata[$i]['COUNT'] = $usercount;
        }
    }
}

$apx->tmpl->assign('LOCATION', $tabledata);
$apx->tmpl->parse('usermap');
