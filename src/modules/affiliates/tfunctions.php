<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Affiliates
function affiliates_show($count = 0, $start = 0, $template = 'affiliates')
{
    global $set,$db,$apx;

    $count = (int) $count;
    $start = (int) $start;
    $tmpl = new tengine();

    if (1 == $set['affiliates']['orderby']) {
        $data = $db->fetch('SELECT id,title,image,link,hits FROM '.PRE."_affiliates WHERE active='1' ORDER BY ord ASC".iif($count, ' LIMIT '.iif($start, $start.',').$count));
    } elseif (2 == $set['affiliates']['orderby']) {
        $data = $db->fetch('SELECT id,title,image,link,hits FROM '.PRE."_affiliates WHERE active='1' ORDER BY hits DESC".iif($count, ' LIMIT '.iif($start, $start.',').$count));
    } elseif (3 == $set['affiliates']['orderby']) {
        $data = $db->fetch('SELECT id,title,image,link,hits FROM '.PRE."_affiliates WHERE active='1' ORDER BY hits ASC".iif($count, ' LIMIT '.iif($start, $start.',').$count));
    } else {
        $data = $db->fetch('SELECT id,title,image,hits FROM '.PRE."_affiliates WHERE active='1'");
        if (count($data)) {
            srand((float) microtime() * 1000000);
            shuffle($data);

            if ($count) {
                foreach ($data as $res) {
                    ++$ii;
                    $newdata[] = $res;
                    if ($ii == $count) {
                        break;
                    }
                }
                $data = $newdata;
                unset($newdata);
            }
        }
    }

    if (count($data)) {
        $apx->lang->drop('affiliates', 'affiliates');

        foreach ($data as $res) {
            ++$i;
            $affdata[$i]['TITLE'] = $res['title'];
            $affdata[$i]['IMAGE'] = iif($res['image'], getpath('uploads').$res['image']);
            $affdata[$i]['HITS'] = number_format($res['hits'], 0, '', '.');
            $affdata[$i]['LINK'] = HTTPDIR.'misc.php?action=afflink&amp;id='.$res['id'];
            $affdata[$i]['URL'] = $res['link'];
        }
    }

    $tmpl->assign('AFFILIATE', $affdata);
    $tmpl->parse($template, 'affiliates');
}
