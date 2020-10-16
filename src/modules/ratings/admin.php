<?php

// RATING CLASS
// ============

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class action
{
    public $module;
    public $mid;
    public $rate;

    //***************************** STARTUP *****************************
    public function action()
    {
        global $apx;

        if ($_REQUEST['module'] && !$apx->is_module($_REQUEST['module'])) {
            die('module does not exist!');
        }

        $this->module = $_REQUEST['module'];
        $this->mid = (int) $_REQUEST['mid'];

        $apx->tmpl->assign('MODULE', $this->module);
        $apx->tmpl->assign('MID', $this->mid);

        //Settings
        require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
        $this->rate = @new ratings($this->module);
    }

    //***************************** Bewertungen zeigen *****************************
    public function show()
    {
        global $set,$db,$apx,$html;

        //Layer generieren
        if (!$this->mid) {
            $data = $db->fetch('SELECT module FROM '.PRE.'_ratings GROUP BY module ORDER BY module ASC');
            if (count($data)) {
                foreach ($data as $res) {
                    if (!$apx->is_module($res['module'])) {
                        continue;
                    }
                    ++$mi;
                    if (1 == $mi && !$this->module) {
                        $this->module = $res['module'];
                    }
                    $commodules[] = $res['module'];
                }
            }

            if (!is_array($commodules)) {
                return;
            }
            foreach ($apx->modules as $module => $trash) {
                if ('ratings' == $module) {
                    continue;
                }
                if (!in_array($module, $commodules) && $_REQUEST['module'] != $module) {
                    continue;
                }
                $layerdef[] = ['MODULENAME_'.strtoupper($module), 'action.php?action=ratings.show&amp;module='.$module, $this->module == $module];
            }

            $html->layer_header($layerdef);
        }

        $orderdef[0] = 'time';
        $orderdef['rating'] = ['rating', 'ASC', 'COL_RATING'];
        $orderdef['name'] = ['ip', 'ASC', 'COL_IP'];
        $orderdef['time'] = ['time', 'DESC', 'COL_TIME'];

        $col[] = ['COL_RATING', 20, 'align="center"'];
        $col[] = ['COL_IP', 35, 'align="center"'];
        $col[] = ['COL_TIME', 45, 'align="center"'];

        list($count) = $db->first('SELECT count(id) FROM '.PRE."_ratings WHERE ( module='".$this->module."' ".iif($this->mid, "AND mid='".$this->mid."'").' )');
        pages('action.php?action=ratings.show&amp;module='.$this->module.'&amp;mid='.$this->mid.'&amp;sortby='.$_REQUEST['sortby'], $count);

        $data = $db->fetch('SELECT id,ip,rating,time FROM '.PRE."_ratings WHERE ( module='".$this->module."' ".iif($this->mid, "AND mid='".$this->mid."'").' ) '.getorder($orderdef).getlimit());
        if (count($data)) {
            foreach ($data as $res) {
                ++$i;

                $tabledata[$i]['ID'] = $res['id'];
                $tabledata[$i]['COL1'] = $res['rating'];
                $tabledata[$i]['COL2'] = $res['ip'];
                $tabledata[$i]['COL3'] = mkdate($res['time']);

                //Optionen
                if ($apx->user->has_right('ratings.del')) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'ratings.del', 'module='.$this->module.'&mid='.$this->mid.'&id='.$res['id'], $apx->lang->get('CORE_DEL'));
                }
            }
        }

        $multiactions = [];
        if ($apx->user->has_right('ratings.del')) {
            $multiactions[] = [$apx->lang->get('CORE_DEL'), 'action.php?action=ratings.del&module='.$this->module.'&mid='.$this->mid, false];
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col, $multiactions);

        orderstr($orderdef, 'action.php?action=ratings.show&amp;module='.$this->module.'&amp;mid='.$this->mid);

        //Layer-Footer ausgeben
        if (!$this->mid) {
            $html->layer_footer();
        }

        save_index($_SERVER['REQUEST_URI']);
    }

    //***************************** Bewertung löschen *****************************
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
                    header('Location: '.get_index('ratings.show'));

                    return;
                }

                if (count($cache)) {
                    $db->query('DELETE FROM '.PRE."_ratings WHERE ( module='".$this->module."' AND id IN (".implode(',', $cache).') )');
                    foreach ($cache as $id) {
                        logit('RATINGS_DEL', 'ID #'.$id);
                    }
                }

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.get_index('ratings.show'));
            }
        }

        //Einzeln
        else {
            $_REQUEST['id'] = (int) $_REQUEST['id'];
            if (!$_REQUEST['id']) {
                die('missing ID!');
            }

            if (1 == $_POST['send']) {
                if (!checkToken()) {
                    printInvalidToken();
                } else {
                    $db->query('DELETE FROM '.PRE."_ratings WHERE ( id='".$_REQUEST['id']."' AND module='".$this->module."' ) LIMIT 1");
                    logit('RATINGS_DEL', 'ID #'.$_REQUEST['id']);
                    printJSRedirect(get_index('ratings.show'));
                }
            } else {
                tmessageOverlay('del', ['ID' => $_REQUEST['id']]);
            }
        }
    }
} //END CLASS
