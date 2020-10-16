<?php

// Rating Class
// ============

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class ratings
{
    public $set = [];

    //Startup
    public function ratings($module, $mid = false)
    {
        global $set,$apx;
        if (!isset($module) || !isset($mid)) {
            return;
        }
        if (!(int) $mid && false !== $mid) {
            die('invalid MID!');
        }
        if (!$apx->is_module($module)) {
            die('invalid module!');
        }

        $this->module = $module;
        $this->mid = (int) $mid;
        $this->getsettings($module);
    }

    //Kommentar-Settings
    public function getsettings($module)
    {
        global $set;

        foreach ($set['ratings'] as $key => $info) {
            if (isset($set[$module]['rate_'.$key])) {
                $this->set[$key] = $set[$module]['rate_'.$key];
            } else {
                $this->set[$key] = $set['ratings'][$key];
            }
        }

        return $this->set;
    }

    //Link holen
    public function getpage()
    {
        return str_replace('&', '&amp;', $_SERVER['REQUEST_URI']);
        /*list($file,$query)=explode('?',str_replace('&','&amp;',$_SERVER['REQUEST_URI']),2);

        //Query-String analysieren
        if ( $query ) {
            $args=explode('&',$query);
            $params=array();

            foreach ( $args AS $arg ) {
                list($var,$value)=explode('=',$arg);
                if ( $var=='comp' ) continue;
                $params[]=$arg;
            }

            $qstring=implode('&',$params);
        }

        return $file.iif($qstring,'?').$qstring;*/
    }

    // Alle Bewertungs-Platzhalter
    public function assign_ratings($parse = false, $tmpl = null)
    {
        global $set,$db,$apx;
        if (false !== $parse && !is_array($parse)) {
            $parse = [];
        }
        if (!$tmpl) {
            $tmpl = &$apx->tmpl;
        }

        if (false === $parse || in_array('RATING', $parse)) {
            $tmpl->assign('RATING', $this->display());
        }
        if (false === $parse || in_array('RATING_VOTES', $parse)) {
            $tmpl->assign('RATING_VOTES', $this->count());
        }
        if (false === $parse || in_array('RATING_HASVOTED', $parse)) {
            $tmpl->assign('RATING_HASVOTED', $this->hasvoted());
        }
        if (false === $parse || in_array('RATING_POSTTO', $parse)) {
            $tmpl->assign('RATING_POSTTO', $this->postto());
        }
        if (false === $parse || in_array('RATING_OPTION', $parse)) {
            $tmpl->assign('RATING_OPTION', $this->options());
        }

        $tmpl->assign('DISPLAY_RATING', 1);
        $tmpl->overwrite('MID', $this->mid);
        $tmpl->overwrite('MODULE', $this->module);
    }

    // Bewertung ausgeben
    public function display()
    {
        global $set,$db,$apx,$user;

        $apx->lang->drop('rating', 'ratings');

        list($rating) = $db->first('SELECT avg(rating) FROM '.PRE."_ratings WHERE ( module='".$this->module."' AND mid='".$this->mid."' )");
        $value = round($rating, $this->set['digits']);
        if ($this->set['digits'] > 0) {
            $value = sprintf('%01.'.$this->set['digits'].'f', $value);
        }

        return $value;
    }

    // Formular-Optionen
    public function options()
    {
        global $set;

        $optdata = [];
        foreach ($this->set['possible'] as $key => $title) {
            ++$i;
            $optdata[$i]['VALUE'] = $key;
            $optdata[$i]['TITLE'] = $title;
        }

        return $optdata;
    }

    // Formular senden an...
    public function postto()
    {
        return $this->getpage();
    }

    // Bewertungen zählen
    public function count()
    {
        global $set,$db,$apx,$user;
        list($count) = $db->first('SELECT count(rating) FROM '.PRE."_ratings WHERE ( module='".$this->module."' AND mid='".$this->mid."' )");

        return $count;
    }

    // Benutzer hat abgestimmt?
    public function hasvoted()
    {
        global $db,$apx,$user;
        list($spam) = $db->first('SELECT time FROM '.PRE."_ratings WHERE ( module='".addslashes($this->module)."' AND ip='".get_remoteaddr()."' AND mid='".intval($this->mid)."' ) ORDER BY time DESC");

        return ($spam + $this->set['block'] * 60) > time();
    }

    // Bewertung hinzufügen
    public function addrate()
    {
        global $db,$apx,$user;
        $_POST['mid'] = (int) $_POST['mid'];
        if (!$_POST['mid']) {
            die('missing mID!');
        }
        if (!$apx->is_module($_POST['module'])) {
            die('invalid MODULE!');
        }

        $apx->lang->drop('add', 'ratings');

        list($spam) = $db->first('SELECT time FROM '.PRE."_ratings WHERE ( module='".$_POST['module']."' AND ip='".get_remoteaddr()."' AND mid='".intval($_POST['mid'])."' ) ORDER BY time DESC");

        if (!$_POST['mid'] || !$_POST['rating']) {
            message('back');
        } elseif (($spam + $this->set['block'] * 60) > time()) {
            message($apx->lang->get('MSG_RATE_HASVOTED'), 'back');
        } else {
            $_POST['module'] = $_POST['module'];
            $_POST['time'] = time();
            $_POST['ip'] = get_remoteaddr();

            if (!array_key_exists(intval($_POST['rating']), $this->set['possible'])) {
                die('invalid RATING!');
            }

            $db->dinsert(PRE.'_ratings', 'module,mid,rating,time,ip');
            message($apx->lang->get('MSG_RATE_OK'), $_SERVER['REQUEST_URI']);
        }
    }
} //END CLASS
