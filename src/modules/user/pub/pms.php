<?php

$apx->lang->drop('pms');
headline($apx->lang->get('HEADLINE_PMS'), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
titlebar($apx->lang->get('HEADLINE_PMS'));
$_REQUEST['read'] = (int) $_REQUEST['read'];
$_REQUEST['unread'] = (int) $_REQUEST['unread'];
if ('in' != $_REQUEST['dir'] && 'out' != $_REQUEST['dir']) {
    $_REQUEST['dir'] = 'in';
}

//PM Status verändern
if ($_REQUEST['read']) {
    $db->query('UPDATE '.PRE."_user_pms SET isread='1' WHERE ( id='".$_REQUEST['read']."' AND touser='".$user->info['userid']."' ) LIMIT 1");
} elseif ($_REQUEST['unread']) {
    $db->query('UPDATE '.PRE."_user_pms SET isread='0' WHERE ( id='".$_REQUEST['unread']."' AND touser='".$user->info['userid']."' ) LIMIT 1");
}

//PMS löschen
if (is_array($_POST['multi'])) {
    foreach ($_POST['multi'] as $id => $value) {
        if ('1' != $value) {
            continue;
        }
        $dcache[] = "'".$id."'";
    }

    if (is_array($dcache)) {
        if ('out' == $_REQUEST['dir']) {
            $db->query('UPDATE '.PRE."_user_pms SET del_from='1' WHERE ( id IN (".implode(',', $dcache).") AND fromuser='".$user->info['userid']."' )");
        } else {
            $db->query('UPDATE '.PRE."_user_pms SET del_to='1' WHERE ( id IN (".implode(',', $dcache).") AND touser='".$user->info['userid']."' )");
        }
    }
}

//Speicher bestimmen
list($pmcount) = $db->first('SELECT count(id) FROM '.PRE."_user_pms WHERE ( ( touser='".$user->info['userid']."' AND del_to='0' ) OR ( fromuser='".$user->info['userid']."' AND del_from='0' ) )");
$space = $pmcount.'/'.$set['user']['maxpmcount'];
$percent = round(max(round(($set['user']['maxpmcount'] - $pmcount) / iif(0 == $set['user']['maxpmcount'], 1, $set['user']['maxpmcount']), 2) * 100, 0));
if ($percent > 100) {
    $percent = 100;
}
$width = (100 - $percent).'%';
$percent .= '%';

//Orderby
$orderdef[0] = 'time';
$orderdef['subject'] = ['a.subject', 'ASC', 'SORT_SUBJECT'];
$orderdef['username'] = ['b.username', 'ASC', 'SORT_USERNAME'];
$orderdef['time'] = ['a.time', 'DESC', 'SORT_TIME'];

//Auflistung
if ('out' == $_REQUEST['dir']) {
    $data = $db->fetch('SELECT a.id,a.subject,a.time,a.isread,b.userid,b.username FROM '.PRE.'_user_pms AS a LEFT JOIN '.PRE."_user AS b ON a.touser=b.userid WHERE ( fromuser='".$user->info['userid']."' AND del_from='0' )".getorder($orderdef));
} else {
    $data = $db->fetch('SELECT a.id,a.subject,a.time,a.isread,b.userid,b.username FROM '.PRE.'_user_pms AS a LEFT JOIN '.PRE."_user AS b ON a.fromuser=b.userid WHERE ( touser='".$user->info['userid']."' AND del_to='0' ) ".getorder($orderdef));
}
if (count($data)) {
    foreach ($data as $res) {
        ++$i;

        $tabledata[$i]['ID'] = $res['id'];
        $tabledata[$i]['SUBJECT'] = $res['subject'];
        $tabledata[$i]['TIME'] = $res['time'];
        $tabledata[$i]['NEW'] = iif(!$res['isread'], 1, 0);
        $tabledata[$i]['READPM'] = mklink(
            'user.php?action=readpm&amp;id='.$res['id'],
            'user,readpm,'.$res['id'].'.html'
        );

        if ('out' == $_REQUEST['dir']) {
            $tabledata[$i]['RECIEVER'] = $res['username'];
            $tabledata[$i]['RECIEVER_ID'] = $res['userid'];
        } else {
            $tabledata[$i]['SENDER'] = $res['username'];
            $tabledata[$i]['SENDER_ID'] = $res['userid'];

            $tabledata[$i]['MARKREAD'] = mklink(
                'user.php?action=pms&amp;dir=in&amp;read='.$res['id'],
                'user,pms,in.html?read='.$res['id']
            );

            $tabledata[$i]['MARKUNREAD'] = mklink(
                'user.php?action=pms&amp;dir=in&amp;unread='.$res['id'],
                'user,pms,in.html?unread='.$res['id']
            );
        }
    }
}

//LINKS
$inbox = mklink(
    'user.php?action=pms&amp;dir=in',
    'user,pms,in.html'
);

$outbox = mklink(
    'user.php?action=pms&amp;dir=out',
    'user,pms,out.html'
);

$newpm = mklink(
    'user.php?action=newpm',
    'user,newpm.html'
);

$apx->tmpl->assign('SPACE', $space);
$apx->tmpl->assign('SPACE_PERCENT', $percent);
$apx->tmpl->assign('SPACE_WIDTH', $width);

$apx->tmpl->assign('LINK_INBOX', $inbox);
$apx->tmpl->assign('LINK_OUTBOX', $outbox);
$apx->tmpl->assign('LINK_NEWPM', $newpm);

$apx->tmpl->assign('DIR', $_REQUEST['dir']);
$apx->tmpl->assign('MESSAGE', $tabledata);

//Sortierung
ordervars(
    $orderdef,
    mklink(
        'user.php?action=pms&amp;dir='.$_REQUEST['dir'],
        'user,pms,'.$_REQUEST['dir'].'.html'
    )
);

$apx->tmpl->parse('pms');
