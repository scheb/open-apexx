<?php

// BANNER CLASS
// ===========

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class action
{
    //***************************** Banner zeigen *****************************
    public function show()
    {
        global $set,$db,$apx,$html;

        quicklink('banner.add');

        //Gruppen-Auswahl
        $_REQUEST['gid'] = (int) $_REQUEST['gid'];
        $groupdata = [];
        foreach ($set['banner']['groups'] as $id => $title) {
            $groupdata[] = [
                'ID' => $id,
                'TITLE' => compatible_hsc($title),
                'SELECTED' => $_REQUEST['gid'] == $id,
            ];
        }
        $apx->tmpl->assign('GROUP', $groupdata);
        $apx->tmpl->parse('show_choose');

        $orderdef[0] = 'partner';
        $orderdef['partner'] = ['partner', 'ASC', 'COL_PARTNER'];
        $orderdef['views'] = ['views', 'ASC', 'COL_VIEWS'];
        $orderdef['group'] = ['a.group', 'ASC', 'COL_GROUP'];

        $col[] = ['', 1, ''];
        $col[] = ['COL_PARTNER', 40, 'class="title"'];
        $col[] = ['COL_PERIOD', 25, 'align="center"'];
        $col[] = ['COL_VIEWS', 15, 'align="center"'];
        $col[] = ['COL_GROUP', 20, 'align="center"'];

        list($count) = $db->first('SELECT count(id) FROM '.PRE.'_banner WHERE 1 '.iif($_REQUEST['gid'], 'AND `group`='.$_REQUEST['gid']));
        pages('action.php?action=banner.show&amp;sortby='.$_REQUEST['sortby'].iif($_REQUEST['gid'], '&amp;gid='.$_REQUEST['gid']), $count);

        $data = $db->fetch('SELECT * FROM '.PRE.'_banner AS a WHERE 1 '.iif($_REQUEST['gid'], 'AND `group`='.$_REQUEST['gid']).getorder($orderdef).getlimit());
        if (count($data)) {
            foreach ($data as $res) {
                ++$i;

                if (!$res['starttime']) {
                    $tabledata[$i]['COL1'] = '<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
                } elseif ($res['endtime'] < time()) {
                    $tabledata[$i]['COL1'] = '<img src="design/greendotcross.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
                } elseif ($res['starttime'] > time()) {
                    $tabledata[$i]['COL1'] = '<img src="design/greendotwait.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
                } else {
                    $tabledata[$i]['COL1'] = '<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
                }

                $period = '';
                if ($res['starttime']) {
                    $period = $apx->lang->get('FROM').': '.mkdate($res['starttime']);
                    if (3000000000 != $res['endtime']) {
                        $period .= '<br />'.$apx->lang->get('TILL').': '.mkdate($res['endtime']);
                    }
                }

                $tabledata[$i]['COL2'] = replace($res['partner']);
                $tabledata[$i]['COL3'] = $period;
                $tabledata[$i]['COL4'] = number_format($res['views'], 0, '', '.').iif($res['limit'], ' / '.number_format($res['limit'], 0, '', '.'));
                $tabledata[$i]['COL5'] = $set['banner']['groups'][$res['group']];

                //Limit erreicht?
                if ($res['limit'] && $res['views'] >= $res['limit']) {
                    $tabledata[$i]['COL4'] = '<span style="color:red;">'.$tabledata[$i]['COL4'].'</span>';
                }

                //Optionen
                if ($apx->user->has_right('banner.edit')) {
                    $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'banner.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                if ($apx->user->has_right('banner.del')) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'banner.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                if ((!$res['starttime'] || $res['endtime'] < time()) && $apx->user->has_right('banner.enable')) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('enable.gif', 'banner.enable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
                } elseif ($res['starttime'] && $apx->user->has_right('banner.disable')) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('disable.gif', 'banner.disable', 'id='.$res['id'], $apx->lang->get('CORE_DISABLE'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }
            }
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);

        orderstr($orderdef, 'action.php?action=banner.show');
        save_index($_SERVER['REQUEST_URI']);
    }

    //***************************** Banner hinzufügen *****************************
    public function add()
    {
        global $set,$apx,$tmpl,$db,$user;

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['partner'] || !$_POST['code'] || !$_POST['ratio']) {
                infoNotComplete();
            } else {
                //Sofort freischalten
                $addfields = '';
                if ($apx->user->has_right('banner.enable')) {
                    $_POST['starttime'] = maketime(1);
                    $_POST['endtime'] = maketime(2);
                    if ($_POST['starttime']) {
                        if (!$_POST['endtime'] || $_POST['endtime'] <= $_POST['starttime']) {
                            $_POST['endtime'] = 3000000000;
                        }
                        $addfields = ',starttime,endtime';
                    }
                }

                if ((int) $_POST['ratio'] < 1) {
                    $_POST['ratio'] = 1;
                }
                $db->dinsert(PRE.'_banner', 'partner,code,ratio,limit,capping,group'.$addfields);
                logit('BANNER_ADD', 'ID #'.$db->insert_id());
                printJSRedirect(get_index('banner.show'));
            }
        } else {
            $_POST['ratio'] = 1;
            maketimepost(1, time());

            //Bannergruppen auflisten
            $grouplist = '';
            foreach ($set['banner']['groups'] as $id => $title) {
                $grouplist .= '<option value="'.$id.'"'.iif($id == $_POST['group'], ' selected="selected"').'>'.replace($title).'</option>';
            }

            //Freischaltung
            if ($apx->user->has_right('banner.enable')) {
                $apx->tmpl->assign('STARTTIME', choosetime(1, 1, maketime(1)));
                $apx->tmpl->assign('ENDTIME', choosetime(2, 1, maketime(2)));
            }

            $apx->tmpl->assign('PARTNER', compatible_hsc($_POST['partner']));
            $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
            $apx->tmpl->assign('LIMIT', (int) $_POST['limit']);
            $apx->tmpl->assign('CAPPING', (int) $_POST['capping']);
            $apx->tmpl->assign('RATIO', (int) $_POST['ratio']);
            $apx->tmpl->assign('GROUPS', $grouplist);
            $apx->tmpl->assign('ACTION', 'add');

            $apx->tmpl->parse('add_edit');
        }
    }

    //***************************** Banner bearbeiten *****************************
    public function edit()
    {
        global $set,$apx,$tmpl,$db,$user;
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['partner'] || !$_POST['code'] || !$_POST['ratio']) {
                infoNotComplete();
            } else {
                //Freischaltung
                $addfields = '';
                if ($apx->user->has_right('banner.enable')) {
                    $_POST['starttime'] = maketime(1);
                    $_POST['endtime'] = maketime(2);
                    if ($_POST['starttime']) {
                        if (!$_POST['endtime'] || $_POST['endtime'] <= $_POST['starttime']) {
                            $_POST['endtime'] = 3000000000;
                        }
                        $addfields = ',starttime,endtime';
                    }
                }

                if (intval($_POST['ratio']) < 1) {
                    $_POST['ratio'] = 1;
                }
                $db->dupdate(PRE.'_banner', 'partner,code,ratio,limit,capping,group'.$addfields, "WHERE id='".$_REQUEST['id']."' LIMIT 1");
                logit('BANNER_EDIT', 'ID #'.$_REQUEST['id']);
                printJSRedirect(get_index('banner.show'));
            }
        } else {
            $res = $db->first('SELECT * FROM '.PRE."_banner AS a WHERE id='".$_REQUEST['id']."' LIMIT 1");
            foreach ($res as $key => $val) {
                $_POST[$key] = $val;
            }

            //Freischaltung
            if ($res['starttime']) {
                maketimepost(1, $res['starttime']);
                if ($res['endtime'] < 2147483647) {
                    maketimepost(2, $res['endtime']);
                }
            }

            //Bannergruppen auflisten
            $grouplist = '';
            foreach ($set['banner']['groups'] as $id => $title) {
                $grouplist .= '<option value="'.$id.'"'.iif($id == $_POST['group'], ' selected="selected"').'>'.replace($title).'</option>';
            }

            //Freischaltung
            if ($apx->user->has_right('banner.enable')) {
                $apx->tmpl->assign('STARTTIME', choosetime(1, 1, maketime(1)));
                $apx->tmpl->assign('ENDTIME', choosetime(2, 1, maketime(2)));
            }

            $apx->tmpl->assign('PARTNER', compatible_hsc($_POST['partner']));
            $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
            $apx->tmpl->assign('LIMIT', (int) $_POST['limit']);
            $apx->tmpl->assign('CAPPING', (int) $_POST['capping']);
            $apx->tmpl->assign('RATIO', (int) $_POST['ratio']);
            $apx->tmpl->assign('GROUPS', $grouplist);
            $apx->tmpl->assign('ID', $_REQUEST['id']);
            $apx->tmpl->assign('ACTION', 'edit');

            $apx->tmpl->parse('add_edit');
        }
    }

    //***************************** Banner löschen *****************************
    public function del()
    {
        global $set,$db,$apx;
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $db->query('DELETE FROM '.PRE."_banner WHERE id='".$_REQUEST['id']."' LIMIT 1");
                logit('BANNER_DEL', 'ID #'.$_REQUEST['id']);
                printJSRedirect(get_index('banner.show'));
            }
        } else {
            list($title) = $db->first('SELECT partner FROM '.PRE."_banner WHERE id='".$_REQUEST['id']."' LIMIT 1");
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($title)]));
            tmessageOverlay('del', ['ID' => $_REQUEST['id']]);
        }
    }

    //***************************** Banner aktivieren *****************************
    public function enable()
    {
        global $set,$db,$apx;
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            $starttime = maketime(1);
            $endtime = maketime(2);
            if (!$endtime || $endtime <= $starttime) {
                $endtime = 3000000000;
            }

            if (!checkToken()) {
                return printInvalidToken();
            }
            if ($starttime) {
                $db->query('UPDATE '.PRE."_banner SET starttime='".$starttime."',endtime='".$endtime."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
                logit('BANNER_ENABLE', 'ID #'.$_REQUEST['id']);
                printJSRedirect(get_index('banner.show'));

                return;
            }
        }
        list($title) = $db->first('SELECT partner FROM '.PRE."_banner WHERE id='".$_REQUEST['id']."' LIMIT 1");
        $apx->tmpl->assign('TITLE', compatible_hsc($title));
        $apx->tmpl->assign('ID', $_REQUEST['id']);
        $apx->tmpl->assign('STARTTIME', choosetime(1, 0, time()));
        $apx->tmpl->assign('ENDTIME', choosetime(2, 1));
        tmessageOverlay('enable');
    }

    //***************************** Banner deaktivieren *****************************
    public function disable()
    {
        global $set,$db,$apx;
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $db->query('UPDATE '.PRE."_banner SET starttime='0',endtime='0' WHERE id='".$_REQUEST['id']."' LIMIT 1");
                logit('BANNER_DISABLE', 'ID #'.$_REQUEST['id']);
                printJSRedirect(get_index('banner.show'));
            }
        } else {
            list($title) = $db->first('SELECT partner FROM '.PRE."_banner WHERE id='".$_REQUEST['id']."' LIMIT 1");
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($title)]));
            tmessageOverlay('disable', ['ID' => $_REQUEST['id']]);
        }
    }

    //***************************** Bannergruppen *****************************

    public function group()
    {
        global $set,$db,$apx,$html;
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        $data = $set['banner']['groups'];

        //Kategorie löschen
        if ('del' == $_REQUEST['do'] && isset($data[$_REQUEST['id']])) {
            list($count) = $db->first('SELECT count(*) FROM '.PRE.'_banner WHERE '.PRE."_banner.group='".$id."'");
            if (!$count) {
                if (isset($_POST['id'])) {
                    if (!checkToken()) {
                        infoInvalidToken();
                    } else {
                        unset($data[$_REQUEST['id']]);
                        $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='banner' AND varname='groups' LIMIT 1");
                        logit('BANNER_CATDEL', $_REQUEST['id']);
                        printJSReload();
                    }
                } else {
                    $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($data[$_REQUEST['id']])]));
                    tmessageOverlay('catdel', ['ID' => $_REQUEST['id']]);
                }

                return;
            }
        }

        //Kategorie bearbeiten
        elseif ('edit' == $_REQUEST['do'] && isset($data[$_REQUEST['id']])) {
            if (isset($_POST['title'])) {
                if (!checkToken()) {
                    infoInvalidToken();
                } elseif (!$_POST['title']) {
                    infoNotComplete();
                } else {
                    $data[$_REQUEST['id']] = $_POST['title'];
                    $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='banner' AND varname='groups' LIMIT 1");
                    logit('BANNER_CATEDIT', $_REQUEST['id']);
                    printJSRedirect('action.php?action=banner.group');

                    return;
                }
            } else {
                $_POST['title'] = $data[$_REQUEST['id']];
                $apx->tmpl->assign('TITLE', $_POST['title']);
                $apx->tmpl->assign('ACTION', 'edit');
                $apx->tmpl->assign('ID', $_REQUEST['id']);
                $apx->tmpl->parse('catadd_catedit');
            }
        }

        //Kategorie erstellen
        elseif ('add' == $_REQUEST['do']) {
            if ($_POST['send']) {
                if (!checkToken()) {
                    printInvalidToken();
                } elseif (!$_POST['title']) {
                    infoNotComplete();
                } else {
                    if (!count($data)) {
                        $data[1] = $_POST['title'];
                    } else {
                        $data[] = $_POST['title'];
                    }
                    $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='banner' AND varname='groups' LIMIT 1");
                    logit('BANNER_CATADD', array_key_max($data));
                    printJSRedirect('action.php?action=banner.group');

                    return;
                }
            }
        } else {
            $apx->tmpl->assign('ACTION', 'add');
            $apx->tmpl->parse('catadd_catedit');
        }

        $col[] = ['ID', 1, 'align="center"'];
        $col[] = ['COL_TITLE', 80, 'class="title"'];
        $col[] = ['COL_BANNERS', 20, 'align="center"'];

        //AUSGABE
        asort($data);
        foreach ($data as $id => $res) {
            ++$i;
            list($count) = $db->first('SELECT count(*) FROM '.PRE.'_banner WHERE '.PRE."_banner.group='".$id."'");
            $tabledata[$i]['COL1'] = $id;
            $tabledata[$i]['COL2'] = $res;
            $tabledata[$i]['COL3'] = $count;
            $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'banner.group', 'do=edit&id='.$id, $apx->lang->get('CORE_EDIT'));
            if (!$count) {
                $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'banner.group', 'do=del&id='.$id, $apx->lang->get('CORE_DEL'));
            } else {
                $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
            }
        }
        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);
    }
} //END CLASS
