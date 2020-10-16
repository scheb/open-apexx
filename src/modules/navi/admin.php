<?php

// NAVI
// ====

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class action
{
    public $nav;

    //Startup
    public function action()
    {
        require_once BASEDIR.'lib/class.recursivetree.php';
        $this->cat = new RecursiveTree(PRE.'_navi', 'id');
    }

    //***************************** Navigation zeigen *****************************
    public function show()
    {
        global $set,$db,$apx,$html;

        //Struktur reparieren
        if ($_REQUEST['repair']) {
            $this->cat->repair();
            echo 'Repair done!';

            return;
        }

        quicklink('navi.add', 'action.php', 'nid='.$_REQUEST['nid']);

        $_REQUEST['nid'] = (int) $_REQUEST['nid'];
        if (!$_REQUEST['nid']) {
            $_REQUEST['nid'] = 1;
        }
        $navidata = [];
        foreach ($set['navi']['groups'] as $id => $title) {
            $navidata[] = [
                'ID' => $id,
                'TITLE' => compatible_hsc($title),
                'SELECTED' => $_REQUEST['nid'] == $id,
            ];
        }
        $apx->tmpl->assign('NAVI', $navidata);
        $apx->tmpl->parse('show_choose');

        //DnD-Hinweis
        if ($apx->user->has_right('navi.edit')) {
            echo '<p class="hint">'.$apx->lang->get('USEDND').'</p>';
        }

        $col[] = ['ID', 0, 'align="center"'];
        $col[] = ['COL_TEXT', 100, 'class="title"'];

        $data = $this->cat->getTree(['text'], null, "nid='".$_REQUEST['nid']."'");
        if (count($data)) {
            //Ausgabe erfolgt
            foreach ($data as $res) {
                ++$i;

                if (1 == $res['level']) {
                    ++$tree;
                }

                $tabledata[$i]['COL1'] = $res['id'];
                $tabledata[$i]['COL2'] = $space[$res['id']].' '.replace(shorttext($res['text'], 100));
                $tabledata[$i]['CLASS'] = 'l'.($res['level'] - 1).($res['children'] ? ' haschildren' : '').($res['level'] > 1 ? ' hidden' : '');
                $tabledata[$i]['ID'] = 'node:'.$res['id'];

                //Optionen
                if ($apx->user->has_right('navi.edit')) {
                    $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'navi.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                if ($apx->user->has_right('navi.del')) {
                    $tabledata[$i]['OPTIONS'] .= '<span class="ifhasnochildren">'.optionHTMLOverlay('del.gif', 'navi.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL')).'</span><span class="ifhaschildren"><img alt="" src="design/ispace.gif"/></span>';
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                /*$tabledata[$i]['OPTIONS'].='&nbsp;';
                if ( $apx->user->has_right('navi.move') && $follow[$res['id']]['prev'] ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'navi.move', 'direction=up&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
                else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" />';
                if ( $apx->user->has_right('navi.move') && $follow[$res['id']]['next'] ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'navi.move', 'direction=down&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
                else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" />';*/
            }
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        echo '<div class="treeview" id="tree">';
        $html->table($col);
        echo '</div>';

        $open = $apx->session->get('navi_open');
        $open = dash_unserialize($open);
        $opendata = [];
        foreach ($open as $catid) {
            $opendata[] = [
                'ID' => $catid,
            ];
        }
        $apx->tmpl->assign('OPEN', $opendata);
        $apx->tmpl->assign('EDIT_ALLOWED', $apx->user->has_Right('navi.edit'));
        $apx->tmpl->parse('show_js');

        save_index($_SERVER['REQUEST_URI']);
    }

    //***************************** Neuer Navigationspunkt *****************************
    public function add()
    {
        global $set,$db,$apx;
        $_REQUEST['nid'] = (int) $_REQUEST['nid'];
        if (!$_REQUEST['nid']) {
            die('missing nID!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['parent'] || !$_POST['text'] || ('link' == $_POST['display'] && !$_POST['link']) || ('code' == $_POST['display'] && !$_POST['code']) || !$_POST['parent']) {
                infoNotComplete();
            } else {
                //Was soll gespeichert werden?
                if ('code' == $_POST['display']) {
                    unset($_POST['link'],$_POST['link_popup']);
                } elseif ('link' == $_POST['display']) {
                    unset($_POST['code']);
                } else {
                    unset($_POST['code'],$_POST['link'],$_POST['link_popup']);
                }

                //WENN ROOT
                if ('root' == $_POST['parent']) {
                    $nid = $this->cat->createNode(0, [
                        'nid' => $_POST['nid'],
                        'text' => $_POST['text'],
                        'link' => $_POST['link'],
                        'link_popup' => $_POST['link_popup'],
                        'code' => $_POST['code'],
                        'staticsub' => $_POST['staticsub'],
                    ]);
                    logit('NAVI_ADD', 'ID #'.$nid);
                }

                //WENN NODE
                else {
                    $nid = $this->cat->createNode(intval($_POST['parent']), [
                        'nid' => $_POST['nid'],
                        'text' => $_POST['text'],
                        'link' => $_POST['link'],
                        'link_popup' => $_POST['link_popup'],
                        'code' => $_POST['code'],
                        'staticsub' => $_POST['staticsub'],
                    ]);
                    logit('NAVI_ADD', 'ID #'.$nid);
                }

                //Message ausgeben oder neuer Eintrag
                if ($_POST['submit_next']) {
                    printJSRedirect('action.php?action=navi.add&nid='.$_REQUEST['nid'].'&parent='.$_REQUEST['parent'].'&display='.$_POST['display']);
                } else {
                    printJSRedirect(get_index('navi.show'));
                }
            }
        } else {
            $_POST['staticsub'] = 1;
            $_POST['display'] = 'link';
            if ($_GET['display']) {
                $_POST['display'] = $_GET['display'];
            }
            if ($_GET['parent']) {
                $_POST['parent'] = $_GET['parent'];
            }

            //Baum
            $catlist = '<option value="root" style="font-weight:bold;"'.iif('root' == $_POST['parent'], ' selected="selected"').'>'.$apx->lang->get('ROOT').'</option>';
            $data = $this->cat->getTree(['text'], null, "nid='".$_REQUEST['nid']."'");
            if (count($data)) {
                $catlist .= '<option value=""></option>';
                foreach ($data as $res) {
                    $catlist .= '<option value="'.$res['id'].'"'.iif($_POST['parent'] == $res['id'], ' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;', ($res['level'] - 1)).replace(shorttext($res['text'], 50)).'</option>';
                }
            }

            //Link oder Code
            if ('code' == $_POST['display']) {
                $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
            } elseif ('link' == $_POST['display']) {
                $apx->tmpl->assign('LINK', compatible_hsc($_POST['link']));
                $apx->tmpl->assign('LINK_POPUP', (int) $_POST['link_popup']);
            }

            $apx->tmpl->assign('DISPLAY', $_POST['display']);
            $apx->tmpl->assign('TEXT', compatible_hsc($_POST['text']));
            $apx->tmpl->assign('STATICSUB', (int) $_POST['staticsub']);
            $apx->tmpl->assign('CATLIST', $catlist);
            $apx->tmpl->assign('NID', $_REQUEST['nid']);
            $apx->tmpl->assign('ACTION', 'add');

            $apx->tmpl->parse('add_edit');
        }
    }

    //***************************** Navigationspunkt bearbeiten *****************************
    public function edit()
    {
        global $set,$apx,$db;
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['parent'] || !$_POST['text'] || ('link' == $_POST['display'] && !$_POST['link']) || ('code' == $_POST['display'] && !$_POST['code'])) {
                infoNotComplete();
            } else {
                //Was soll gespeichert werden?
                if ('code' == $_POST['display']) {
                    unset($_POST['link'],$_POST['link_popup']);
                } elseif ('link' == $_POST['display']) {
                    unset($_POST['code']);
                } else {
                    unset($_POST['code'],$_POST['link'],$_POST['link_popup']);
                }

                $this->cat->moveNode($_REQUEST['id'], $_POST['parent'], [
                    'text' => $_POST['text'],
                    'link' => $_POST['link'],
                    'link_popup' => $_POST['link_popup'],
                    'code' => $_POST['code'],
                    'staticsub' => $_POST['staticsub'],
                ]);
                logit('NAVI_EDIT', 'ID #'.$_REQUEST['id']);
                printJSRedirect(get_index('navi.show'));
            }
        } else {
            $res = $this->cat->getNode($_REQUEST['id'], ['nid', 'text', 'link', 'link_popup', 'code', 'staticsub']);
            $_POST['text'] = $res['text'];
            $_POST['link'] = $res['link'];
            $_POST['link_popup'] = $res['link_popup'];
            $_POST['code'] = $res['code'];
            $_POST['staticsub'] = $res['staticsub'];
            if (!$res['parents']) {
                $_POST['parent'] = 'root';
            } else {
                $_POST['parent'] = array_pop($res['parents']);
            }

            if ($_POST['code']) {
                $_POST['display'] = 'code';
            } elseif ($_POST['link']) {
                $_POST['display'] = 'link';
            }

            //Baum
            $catlist = '<option value="root" style="font-weight:bold;"'.iif('root' == $_POST['parent'], ' selected="selected"').'>'.$apx->lang->get('ROOT').'</option>';
            $data = $this->cat->getTree(['text'], null, "nid='".$res['nid']."'");
            if (count($data)) {
                $catlist .= '<option value=""></option>';
                foreach ($data as $res) {
                    if ($jumplevel && $res['level'] > $jumplevel) {
                        continue;
                    }
                    $jumplevel = 0;
                    if ($_REQUEST['id'] == $res['id']) {
                        $jumplevel = $res['level'];

                        continue;
                    }
                    $catlist .= '<option value="'.$res['id'].'"'.iif($_POST['parent'] === $res['id'], ' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;', ($res['level'] - 1)).replace($res['text']).'</option>';
                }
            }

            //Link oder Code
            if ('code' == $_POST['display']) {
                $apx->tmpl->assign('CODE', compatible_hsc($_POST['code']));
            } elseif ('link' == $_POST['display']) {
                $apx->tmpl->assign('LINK', compatible_hsc($_POST['link']));
                $apx->tmpl->assign('LINK_POPUP', (int) $_POST['link_popup']);
            }

            $apx->tmpl->assign('DISPLAY', $_POST['display']);
            $apx->tmpl->assign('TEXT', compatible_hsc($_POST['text']));
            $apx->tmpl->assign('STATICSUB', (int) $_POST['staticsub']);
            $apx->tmpl->assign('CATLIST', $catlist);
            $apx->tmpl->assign('ID', $_REQUEST['id']);
            $apx->tmpl->assign('NID', $_REQUEST['nid']);
            $apx->tmpl->assign('ACTION', 'edit');

            $apx->tmpl->parse('add_edit');
        }
    }

    //***************************** Navigationspunkt löschen *****************************
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
                $this->cat->deleteNode($_REQUEST['id']);
                logit('NAVI_DEL', 'ID #'.$_REQUEST['id']);
                printJSRedirect(get_index('navi.show'));
            }
        } else {
            list($title) = $db->first('SELECT text FROM '.PRE."_navi WHERE id='".$_REQUEST['id']."' LIMIT 1");
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($title)]));
            tmessageOverlay('deltitle', ['ID' => $_REQUEST['id']], '/');
        }
    }

    /*//***************************** Navigationspunkt verschieben *****************************
    function move() {
        global $set,$db,$apx;
        $_REQUEST['id']=(int)$_REQUEST['id'];
        if ( !$_REQUEST['id'] ) die('missing ID!');

        if ( !checkToken() ) printInvalidToken();
        else {
            $this->cat->move($_REQUEST['id'],$_REQUEST['direction']);
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: '.get_index('navi.show'));
        }
    }*/

    //***************************** Navigationsleisten *****************************

    public function group()
    {
        global $set,$db,$apx,$html;
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        $data = $set['navi']['groups'];

        //Kategorie löschen
        if ('del' == $_REQUEST['do'] && isset($data[$_REQUEST['id']])) {
            list($count) = $db->first('SELECT count(*) FROM '.PRE."_navi WHERE nid='".$id."'");
            if (!$count) {
                if (isset($_POST['id'])) {
                    if (!checkToken()) {
                        infoInvalidToken();
                    } else {
                        //Navigationspunkte löschen
                        $queryData = $db->fetch('SELECT id FROM '.PRE."_navi WHERE nid='".$_REQUEST['id']."' AND parents='|'");
                        foreach ($queryData as $res) {
                            $this->cat->deleteSubtree($res['id']);
                        }

                        //Navigation löschen
                        unset($data[$_REQUEST['id']]);
                        $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='navi' AND varname='groups' LIMIT 1");
                        logit('NAVI_CATDEL', $_REQUEST['id']);
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
                    info('back');
                } else {
                    $data[$_REQUEST['id']] = $_POST['title'];
                    $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='navi' AND varname='groups' LIMIT 1");
                    logit('NAVI_CATEDIT', $_REQUEST['id']);
                    printJSRedirect('action.php?action=navi.group');

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
                    info('back');
                } else {
                    if (!count($data)) {
                        $data[1] = $_POST['title'];
                    } else {
                        $data[] = $_POST['title'];
                    }
                    $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='navi' AND varname='groups' LIMIT 1");
                    logit('NAVI_CATADD', array_key_max($data));
                    printJSRedirect('action.php?action=navi.group');

                    return;
                }
            }
        } else {
            $apx->tmpl->assign('ACTION', 'add');
            $apx->tmpl->parse('catadd_catedit');
        }

        $col[] = ['ID', 1, 'align="center"'];
        $col[] = ['COL_TITLE', 80, 'class="title"'];
        $col[] = ['COL_ENTRIES', 20, 'align="center"'];

        //AUSGABE
        asort($data);
        foreach ($data as $id => $res) {
            ++$i;
            list($count) = $db->first('SELECT count(*) FROM '.PRE."_navi WHERE nid='".$id."'");
            $tabledata[$i]['COL1'] = $id;
            $tabledata[$i]['COL2'] = $res;
            $tabledata[$i]['COL3'] = $count;
            $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'navi.group', 'do=edit&id='.$id, $apx->lang->get('CORE_EDIT'));
            $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'navi.group', 'do=del&id='.$id, $apx->lang->get('CORE_DEL'));
        }
        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);
    }
} //END CLASS
