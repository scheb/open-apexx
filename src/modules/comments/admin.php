<?php

// COMMENT CLASS
// =============

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class action
{
    public $module;
    public $mid;
    public $coms;

    //Startup
    public function action()
    {
        global $apx;

        //if ( $_REQUEST['module'] && !$apx->is_module($_REQUEST['module']) ) die('module does not exist!');

        $this->module = $_REQUEST['module'];
        $this->mid = (int) $_REQUEST['mid'];

        $apx->tmpl->assign('MODULE', $this->module);
        $apx->tmpl->assign('MID', $this->mid);

        //Settings
        require_once BASEDIR.getmodulepath('comments').'class.comments.php';
        $this->coms = @new comments($this->module);
    }

    //***************************** Kommentare zeigen *****************************
    public function show()
    {
        global $set,$db,$apx,$html;

        quicklink_multi('comments.blockip');
        quicklink_multi('comments.blockcontent');
        quicklink_out();

        //Layer generieren
        if (!$this->mid) {
            $commodules = [];
            $layerdef = [];

            $data = $db->fetch('SELECT module FROM '.PRE.'_comments GROUP BY module ORDER BY module ASC');
            if (count($data)) {
                foreach ($data as $res) {
                    //if ( !$apx->is_module($res['module']) ) continue;
                    ++$mi;
                    if (1 == $mi && !$this->module) {
                        $this->module = $res['module'];
                    }
                    $commodules[] = $res['module'];
                }
            }

            foreach ($apx->modules as $module => $trash) {
                if ('comments' == $module) {
                    continue;
                }
                if (!in_array($module, $commodules) && $_REQUEST['module'] != $module) {
                    continue;
                }
                $layerdef[] = ['MODULENAME_'.strtoupper($module), 'action.php?action=comments.show&amp;module='.$module, $this->module == $module];
            }

            if (count($layerdef)) {
                $html->layer_header($layerdef);
            }
        }

        $orderdef[0] = 'time';
        $orderdef['name'] = ['username', 'ASC', 'COL_NAME'];
        $orderdef['time'] = ['time', 'DESC', 'SORT_TIME'];

        if ($this->coms->set['mod']) {
            $col[] = ['', 2, 'align="center"'];
        }
        $col[] = ['COL_NAME', 33, 'class="title"'];
        $col[] = ['COL_TEXT', 50, ''];
        $col[] = ['COL_IP', 17, 'align="center"'];

        list($count) = $db->first('SELECT count(id) FROM '.PRE."_comments WHERE ( module='".$this->module."' ".iif($this->mid, "AND mid='".$this->mid."'").' )');
        pages('action.php?action=comments.show&amp;module='.$this->module.'&amp;mid='.$this->mid.'&amp;sortby='.$_REQUEST['sortby'], $count);

        $data = $db->fetch('SELECT id,username,text,ip,active FROM '.PRE."_comments WHERE ( module='".$this->module."' ".iif($this->mid, "AND mid='".$this->mid."'").' ) '.getorder($orderdef).getlimit());
        if (count($data)) {
            foreach ($data as $res) {
                ++$i;
                $icol = 0;

                //Moderiert -> Icons
                if ($this->coms->set['mod']) {
                    if ($res['active']) {
                        $tabledata[$i]['COL'.++$icol] = '<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
                    } else {
                        $tabledata[$i]['COL'.++$icol] = '<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
                    }
                }

                $tabledata[$i]['COL'.++$icol] = replace($res['username']);
                $tabledata[$i]['COL'.++$icol] = shorttext($res['text'], 50);
                $tabledata[$i]['COL'.++$icol] = $res['ip'].iif($apx->user->has_right('comments.blockip'), ' <a href="action.php?action=comments.blockip&amp;setip='.$res['ip'].'"><img src="design/block.gif" alt="'.$apx->lang->get('BLOCK').'" title="'.$apx->lang->get('BLOCK').'" /></a>');
                $tabledata[$i]['ID'] = $res['id'];

                //Optionen
                if ($apx->user->has_right('comments.edit')) {
                    $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'comments.edit', 'module='.$this->module.'&mid='.$this->mid.'&id='.$res['id'], $apx->lang->get('CORE_EDIT'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                if ($apx->user->has_right('comments.del')) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'comments.del', 'module='.$this->module.'&mid='.$this->mid.'&id='.$res['id'], $apx->lang->get('CORE_DEL'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                //Moderiert -> Enable/Disable
                if ($this->coms->set['mod']) {
                    if ($res['active'] && $apx->user->has_right('comments.disable')) {
                        $tabledata[$i]['OPTIONS'] .= optionHTML('disable.gif', 'comments.disable', 'module='.$this->module.'&mid='.$this->mid.'&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_DISABLE'));
                    } elseif (!$res['active'] && $apx->user->has_right('comments.enable')) {
                        $tabledata[$i]['OPTIONS'] .= optionHTML('enable.gif', 'comments.enable', 'module='.$this->module.'&mid='.$this->mid.'&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('CORE_ENABLE'));
                    } else {
                        $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                    }
                }
            }
        }

        $multiactions = [];
        if ($apx->user->has_right('comments.del')) {
            $multiactions[] = [$apx->lang->get('CORE_DEL'), 'action.php?action=comments.del&module='.$this->module.'&mid='.$this->mid];
        }
        if ($this->coms->set['mod']) {
            if ($apx->user->has_right('comments.enable')) {
                $multiactions[] = [$apx->lang->get('CORE_ENABLE'), 'action.php?action=comments.enable&module='.$this->module.'&mid='.$this->mid];
            }
            if ($apx->user->has_right('comments.disable')) {
                $multiactions[] = [$apx->lang->get('CORE_DISABLE'), 'action.php?action=comments.disable&module='.$this->module.'&mid='.$this->mid];
            }
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col, $multiactions);

        orderstr($orderdef, 'action.php?action=comments.show&amp;module='.$this->module.'&amp;mid='.$this->mid);

        //Layer-Footer ausgeben
        if (!$this->mid && count($layerdef)) {
            $html->layer_footer();
        }

        save_index($_SERVER['REQUEST_URI']);
    }

    //***************************** Kommentar bearbeiten *****************************
    public function edit()
    {
        global $set,$db,$apx;
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        $res = $db->first('SELECT id,userid,username,email,homepage,title,text FROM '.PRE."_comments WHERE id='".$_REQUEST['id']."' LIMIT 1");

        //Registrierter Benutzer
        if ($res['userid']) {
            if (1 == $_POST['send']) {
                if (!checkToken()) {
                    infoInvalidToken();
                } elseif (!$_POST['id']
                || !$_POST['text']
                || ($this->coms->set['req_title'] && !$_POST['title'])
            ) {
                    infoNotComplete();
                } elseif ($this->coms->set['maxlen'] && strlen($_POST['text']) > $this->coms->set['maxlen']) {
                    info($apx->lang->get('INFO_TOOLONG'));
                } else {
                    $db->dupdate(PRE.'_comments', 'title,text', "WHERE ( module='".$this->module."' AND id='".$_REQUEST['id']."' ) LIMIT 1");
                    logit('COMMENTS_EDIT', 'ID #'.$_REQUEST['id']);
                    if ($_POST['outer']) {
                        $goto = 'action.php?action=comments.show&amp;module='.$this->module.'&amp;mid='.$this->mid;
                    } else {
                        $goto = get_index('comments.show');
                    }
                    printJSRedirect($goto);
                }
            } else {
                $_POST['text'] = $res['text'];
                $_POST['title'] = $res['title'];

                $apx->tmpl->assign('ID', $_REQUEST['id']);
                $apx->tmpl->assign('USERID', $res['userid']);
                $apx->tmpl->assign('USERNAME', replace($res['username']));
                $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
                $apx->tmpl->assign('TEXT', compatible_hsc($_POST['text']));
                $apx->tmpl->assign('SET_MAXLEN', $this->coms->set['maxlen']);
                $apx->tmpl->assign('OUTER', (int) $_REQUEST['outer']);

                $apx->tmpl->assign('SET_REQ_EMAIL', $this->coms->set['req_email']);
                $apx->tmpl->assign('SET_REQ_HOMEPAGE', $this->coms->set['req_homepage']);
                $apx->tmpl->assign('SET_REQ_TITLE', $this->coms->set['req_title']);

                $apx->tmpl->parse('edit');
            }
        }

        //Gast
        else {
            if (1 == $_POST['send']) {
                if (!checkToken()) {
                    infoInvalidToken();
                } elseif (!$_POST['id'] || !$_POST['text']
                || ($this->coms->set['req_email'] && !$_POST['email'])
                || ($this->coms->set['req_homepage'] && !$_POST['homepage'])
                || ($this->coms->set['req_title'] && !$_POST['title'])
            ) {
                    infoNotComplete();
                } elseif ($this->coms->set['maxlen'] && strlen($_POST['text']) > $this->coms->set['maxlen']) {
                    info($apx->lang->get('INFO_TOOLONG'));
                } else {
                    if ('www.' == substr($_POST['homepage'], 0, 4)) {
                        $_POST['homepage'] = 'http://'.$_POST['homepage'];
                    }
                    $db->dupdate(PRE.'_comments', 'username,email,homepage,title,text', "WHERE ( module='".$this->module."' AND id='".$_REQUEST['id']."' ) LIMIT 1");
                    logit('COMMENTS_EDIT', 'ID #'.$_REQUEST['id']);

                    //Weiterleitung
                    if ($_POST['outer']) {
                        $goto = 'action.php?action=comments.show&module='.$this->module.'&mid='.$this->mid;
                    } else {
                        $goto = get_index('comments.show');
                    }
                    printJSRedirect($goto);
                }
            } else {
                foreach ($res as $key => $val) {
                    $_POST[$key] = $val;
                }

                $apx->tmpl->assign('ID', $_REQUEST['id']);
                $apx->tmpl->assign('USERNAME', compatible_hsc($_POST['username']));
                $apx->tmpl->assign('EMAIL', compatible_hsc($_POST['email']));
                $apx->tmpl->assign('HOMEPAGE', compatible_hsc($_POST['homepage']));
                $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
                $apx->tmpl->assign('TEXT', compatible_hsc($_POST['text']));
                $apx->tmpl->assign('OUTER', (int) $_REQUEST['outer']);

                $apx->tmpl->assign('SET_REQ_EMAIL', $this->coms->set['req_email']);
                $apx->tmpl->assign('SET_REQ_HOMEPAGE', $this->coms->set['req_homepage']);
                $apx->tmpl->assign('SET_REQ_TITLE', $this->coms->set['req_title']);
                $apx->tmpl->assign('SET_MAXLEN', $this->coms->set['maxlen']);

                $apx->tmpl->parse('edit');
            }
        }
    }

    //***************************** Kommentar löschen *****************************
    public function del()
    {
        global $set,$db,$apx;

        //Mehrere
        if (is_array($_REQUEST['multiid'])) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $cache = array_map('intval', $_REQUEST['multiid']);
                if (!count($cache)) {
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: '.get_index('comments.show'));

                    return;
                }

                if (count($cache)) {
                    $db->query('DELETE FROM '.PRE.'_comments WHERE ( id IN ('.implode(',', $cache).") AND module='".$this->module."' )");
                    foreach ($cache as $id) {
                        logit('COMMENTS_DEL', 'ID #'.$id);
                    }
                }

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.get_index('comments.show'));
            }
        }

        //Einzeln
        else {
            $_REQUEST['id'] = (int) $_REQUEST['id'];
            if (!$_REQUEST['id']) {
                die('missing ID!');
            }

            //Zurück
            if (1 == $_POST['send'] && $_POST['outer'] && $_POST['backbutton']) {
                $goto = 'action.php?action=comments.show&module='.$this->module.'&mid='.$this->mid;
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.$goto);
            }

            //Löschen
            elseif (1 == $_POST['send']) {
                if (!checkToken()) {
                    printInvalidToken();
                } else {
                    $db->query('DELETE FROM '.PRE."_comments WHERE ( id='".$_REQUEST['id']."' AND module='".$this->module."' ) LIMIT 1");
                    logit('COMMENTS_DEL', 'ID #'.$_REQUEST['id']);

                    //Weiterleitung
                    if ($_POST['outer']) {
                        $goto = 'action.php?action=comments.show&module='.$this->module.'&mid='.$this->mid;
                    } else {
                        $goto = get_index('comments.show');
                    }
                    printJSRedirect($goto);
                }
            }

            //Msg anzeigen
            else {
                list($title) = $db->first('SELECT username FROM '.PRE."_comments WHERE id='".$_REQUEST['id']."' AND module='".$this->module."' LIMIT 1");
                $apx->tmpl->assign('OUTER', $_REQUEST['outer']);
                $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($title)]));
                tmessageOverlay('del', ['ID' => $_REQUEST['id']]);
            }
        }
    }

    //***************************** Kommentar aktivieren *****************************
    public function enable()
    {
        global $set,$db,$apx;
        if (!$this->coms->set['mod']) {
            return;
        }
        //Mehrere
        if (is_array($_REQUEST['multiid'])) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $cache = array_map('intval', $_REQUEST['multiid']);
                if (!count($cache)) {
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: '.get_index('comments.show'));

                    return;
                }

                if (count($cache)) {
                    $db->query('UPDATE '.PRE."_comments SET active='1' WHERE ( id IN (".implode(',', $cache).") AND module='".$this->module."' )");
                    foreach ($cache as $id) {
                        logit('COMMENTS_ENABLE', 'ID #'.$id);
                    }
                }

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.get_index('comments.show'));
            }
        }

        //Einzeln
        else {
            $_REQUEST['id'] = (int) $_REQUEST['id'];
            if (!$_REQUEST['id']) {
                die('missing ID!');
            }
            if (!$this->coms->set['mod']) {
                return;
            }
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $db->query('UPDATE '.PRE."_comments SET active='1' WHERE ( id='".$_REQUEST['id']."' AND module='".$this->module."' ) LIMIT 1");
                logit('COMMENTS_ENABLE', 'ID #'.$_REQUEST['id']);

                //eMail-Benachrichtigung (User)
                /*list($mid, $userid) = $db->first("SELECT mid, userid FROM ".PRE."_comments WHERE id='".$_REQUEST['id']."' LIMIT 1");
                $data = $db->fetch("
                    SELECT DISTINCT IF(c.userid, u.email, c.email) AS email
                    FROM ".PRE."_comments AS c
                    LEFT JOIN ".PRE."_user AS u USING(userid)
                    WHERE c.module='".addslashes($this->module)."' AND c.mid='".addslashes($mid)."' AND c.notify=1 AND c.id!='".$_REQUEST['id']."' ".iif($userid, " AND c.userid!=".$userid)."
                ");
                if ( count($data) ) {
                    foreach ( $data AS $res ) {
                        $input=array(
                            'URL' => HTTP,
                            'GOTO' => HTTP_HOST.$_SERVER['REQUEST_URI']
                        );
                        sendmail($res['email'],'NOTIFYCOM',$input);
                    }
                }

                //Notify zurücksetzen
                $db->query("UPDATE ".PRE."_comments SET notify=0 WHERE module='".addslashes($this->module)."' AND mid='".addslashes($mid)."' AND id!='".$_REQUEST['id']."'");
                */

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.get_index('comments.show'));
            }
        }
    }

    //***************************** Kommentar deaktivieren *****************************
    public function disable()
    {
        global $set,$db,$apx;
        if (!$this->coms->set['mod']) {
            return;
        }
        //Mehrere
        if (is_array($_REQUEST['multiid'])) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $cache = array_map('intval', $_REQUEST['multiid']);
                if (!count($cache)) {
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: '.get_index('comments.show'));

                    return;
                }

                if (count($cache)) {
                    $db->query('UPDATE '.PRE."_comments SET active='0' WHERE ( id IN (".implode(',', $cache).") AND module='".$this->module."' )");
                    foreach ($cache as $id) {
                        logit('COMMENTS_DISABLE', 'ID #'.$id);
                    }
                }

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.get_index('comments.show'));
            }
        }

        //Einzeln
        else {
            $_REQUEST['id'] = (int) $_REQUEST['id'];
            if (!$_REQUEST['id']) {
                die('missing ID!');
            }
            if (!$this->coms->set['mod']) {
                return;
            }
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $db->query('UPDATE '.PRE."_comments SET active='0' WHERE ( id='".$_REQUEST['id']."' AND module='".$this->module."' ) LIMIT 1");
                logit('COMMENTS_DISABLE', 'ID #'.$_REQUEST['id']);
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.get_index('comments.show'));
            }
        }
    }

    //***************************** IPs sperren *****************************
    public function blockip()
    {
        global $set,$db,$apx,$html;
        $_REQUEST['key'] = (int) $_REQUEST['key'];

        //IP löschen
        if ('del' == $_REQUEST['do']) {
            if ($_POST['send']) {
                if (!checkToken()) {
                    printInvalidToken();
                } else {
                    unset($set['comments']['blockip'][$_REQUEST['id']]);
                    $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($set['comments']['blockip']))."' WHERE module='comments' AND varname='blockip' LIMIT 1");
                    printJSRedirect('action.php?action=comments.blockip');
                }
            } else {
                $ip = float2ip($set['comments']['blockip'][$_REQUEST['id']]['startip']);
                if ($set['comments']['blockip'][$_REQUEST['id']]['endip']) {
                    $ip .= ' - '.float2ip($set['comments']['blockip'][$_REQUEST['id']]['endip']);
                }
                $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_DEL', ['TITLE' => compatible_hsc($ip)]));
                tmessageOverlay('ipdel', ['ID' => $_REQUEST['id']]);
            }

            return;
        }

        //IP hinzufügen
        if ('add' == $_REQUEST['do']) {
            if (!checkToken()) {
                infoInvalidToken();
            } else {
                for ($i = 1; $i <= 4; ++$i) {
                    $_POST['startip_'.$i] = (int) $_POST['startip_'.$i];
                    $_POST['endip_'.$i] = (int) $_POST['endip_'.$i];
                    if ($_POST['startip_'.$i] > 255) {
                        $_POST['startip_'.$i] = 255;
                    }
                    if ($_POST['startip_'.$i] < 0) {
                        $_POST['startip_'.$i] = 0;
                    }
                    if ($_POST['endip_'.$i] > 255) {
                        $_POST['endip_'.$i] = 255;
                    }
                    if ($_POST['endip_'.$i] < 0) {
                        $_POST['endip_'.$i] = 0;
                    }
                }

                $start = ip2float($_POST['startip_1'].'.'.$_POST['startip_2'].'.'.$_POST['startip_3'].'.'.$_POST['startip_4']);
                $end = ip2float($_POST['endip_1'].'.'.$_POST['endip_2'].'.'.$_POST['endip_3'].'.'.$_POST['endip_4']);
                if (1 == $_POST['type']) {
                    $end = false;
                }

                //IPs umdrehen
                if (false !== $end && $end < $start) {
                    $cache = $end;
                    $end = $start;
                    $start = $cache;
                }

                $set['comments']['blockip'][] = ['startip' => $start, 'endip' => $end];
                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($set['comments']['blockip']))."' WHERE module='comments' AND varname='blockip' LIMIT 1");
                printJSRedirect('action.php?action=comments.blockip');
            }

            return;
        }

        quicklink_index('comments.show');
        quicklink_out();

        //AUFLISTUNG BEGINNT
        $ips = $set['comments']['blockip'];
        if (!is_array($ips)) {
            $ips = [];
        }
        $ips = array_sort($ips, 'startip', 'asc');

        $col[] = ['COL_IPRANGE', 100, 'class="title"'];

        foreach ($ips as $i => $res) {
            $start = float2ip($res['startip']);
            $end = float2ip($res['endip']);
            $tabledata[$i]['COL1'] = $start.iif($res['endip'], ' &#150; '.$end);
            $tabledata[$i]['OPTIONS'] = optionHTMLOverlay('del.gif', 'comments.blockip', 'do=del&id='.$i, $apx->lang->get('CORE_DEL'));
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);

        //Hinzufügen
        if ($_REQUEST['setip']) {
            $ipp = explode('.', $_REQUEST['setip'], 4);
            $apx->tmpl->assign('IP_1', (int) $ipp[0]);
            $apx->tmpl->assign('IP_2', (int) $ipp[1]);
            $apx->tmpl->assign('IP_3', (int) $ipp[2]);
            $apx->tmpl->assign('IP_4', (int) $ipp[3]);
        }
        $apx->tmpl->parse('blockip');
    }

    //***************************** Inhalte sperren *****************************
    public function blockcontent()
    {
        global $set,$db,$apx,$html;
        $_REQUEST['key'] = (int) $_REQUEST['key'];

        //IP löschen
        if ('del' == $_REQUEST['do']) {
            if ($_POST['send']) {
                if (!checkToken()) {
                    printInvalidToken();
                } else {
                    unset($set['comments']['blockstring'][$_REQUEST['id']]);
                    $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($set['comments']['blockstring']))."' WHERE module='comments' AND varname='blockstring' LIMIT 1");
                    printJSRedirect('action.php?action=comments.blockcontent');
                }
            } else {
                $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_DEL', ['TITLE' => compatible_hsc($set['comments']['blockstring'][$_REQUEST['id']])]));
                tmessageOverlay('contentdel', ['ID' => $_REQUEST['id']]);
            }

            return;
        }

        //IP hinzufügen
        if ('add' == $_REQUEST['do']) {
            if (!checkToken()) {
                printInvalidToken();
            } elseif (!$_POST['string']) {
                infoNotComplete();
            } else {
                $set['comments']['blockstring'][] = $_POST['string'];
                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($set['comments']['blockstring']))."' WHERE module='comments' AND varname='blockstring' LIMIT 1");
                printJSRedirect('action.php?action=comments.blockcontent');
            }

            return;
        }

        quicklink_index('comments.show');
        quicklink_out();

        //AUFLISTUNG BEGINNT
        $strings = $set['comments']['blockstring'];
        if (!is_array($strings)) {
            $strings = [];
        }
        $strings = array_sort($strings, 0, 'asc');

        $col[] = ['TITLE_COMMENTS_BLOCKCONTENT', 100, 'class="title"'];

        foreach ($strings as $i => $res) {
            $tabledata[$i]['COL1'] = $res;
            $tabledata[$i]['OPTIONS'] = optionHTMLOverlay('del.gif', 'comments.blockcontent', 'do=del&id='.$i, $apx->lang->get('CORE_DEL'));
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col);

        $apx->tmpl->parse('blockcontent');
    }
} //END CLASS
