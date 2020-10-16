<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Navigationsleiste mit Baumstruktur
function navi_tree($nid = 1, $nodeid = 0, $template = 'tree')
{
    global $set,$db,$apx;
    $nid = (int) $nid;
    $nodeid = (int) $nodeid;
    $tmpl = new tengine();

    if ($nodeid) {
        require_once BASEDIR.'lib/class.recursivetree.php';
        $tree = new RecursiveTree(PRE.'_navi', 'id');
        $data = $tree->getTree(['*'], $nodeid, "nid='".$nid."'");
    } else {
        require_once BASEDIR.'lib/class.recursivetree.php';
        $tree = new RecursiveTree(PRE.'_navi', 'id');
        $data = $tree->getTree(['*'], null, "nid='".$nid."'");
    }

    if (count($data)) {
        $selected = navi_get_selected($data);
        $jump = 99;
        foreach ($data as $res) {
            ++$i;

            if ($jump < $res['level']) {
                continue;
            }
            if ($jump >= $res['level']) {
                $jump = 99;
            }

            if (in_array($res['id'], $selected)) {
                $issel = true;
            } else {
                $issel = false;
            }

            if (!$res['staticsub'] && !$issel) {
                $jump = $res['level'];
            }

            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['LEVEL'] = $res['level'];
            $tabledata[$i]['CHILDREN'] = $res['children'];
            $tabledata[$i]['TEXT'] = $res['text'];
            $tabledata[$i]['LINK'] = $res['link'];
            $tabledata[$i]['POPUP'] = $res['link_popup'];
            $tabledata[$i]['CODE'] = $res['code'];
            $tabledata[$i]['SELECTED'] = $issel;
        }
    }

    $tmpl->assign('SUBTREE_ID', $nodeid);
    $tmpl->assign('NAVI', $tabledata);
    $tmpl->parse($template, 'navi');
}

//Ausgew�hlte Navigationspunkte bestimmen
function navi_get_selected($data)
{
    global $set,$db,$apx;

    $levellast = [];
    $mother = [];
    $selected = [];

    //Mutterelemente auflisten
    foreach ($data as $res) {
        $levellast[$res['level']] = $res['id'];
        if (!isset($levellast[($res['level'] - 1)])) {
            continue;
        }
        $mother[$res['id']] = $levellast[($res['level'] - 1)];
    }

    //Selektierte Elemente
    $currentquality = 0;
    foreach ($data as $res) {
        if (!$res['link']) {
            continue;
        }
        $matchquality = navi_match_url($res['link']);
        if (!$matchquality) {
            continue;
        }
        if ($matchquality > $currentquality) {
            $selected = [$res['id']];
            $currentquality = $matchquality;
        } elseif ($matchquality == $currentquality) {
            $selected[] = $res['id'];
        }
    }

    //Selektierte Pfade
    foreach ($selected as $sel) {
        while (isset($mother[$sel])) {
            $sel = $mother[$sel];
            $selected[] = $sel;
        }
    }

    return $selected;
}

//Navigationsleiste mit Leveln
function navi_level($nid = 1, $level = 1, $template = 'level')
{
    global $set,$db,$apx;
    static $cache;
    $nid = (int) $nid;
    $level = (int) $level;
    $tmpl = new tengine();

    //Wenn Navigation bereits generiert
    if (isset($cache[$nid])) {
        $leveldata = $cache[$nid];
        if (is_array($leveldata[$level])) {
            $newdata = $leveldata[$level];
        } else {
            $newdata = [];
        }

        foreach ($newdata as $res) {
            ++$i;
            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['LEVEL'] = $res['level'];
            $tabledata[$i]['TEXT'] = $res['text'];
            $tabledata[$i]['LINK'] = $res['link'];
            $tabledata[$i]['POPUP'] = $res['link_popup'];
            $tabledata[$i]['CODE'] = $res['code'];
            $tabledata[$i]['SELECTED'] = $res['selected'];
        }

        $tmpl->assign('NAVI', $tabledata);
        $tmpl->assign('ID', $nid);
        $tmpl->assign('LEVEL', $level);

        $tmpl->parse($template, 'navi');

        return;
    }

    //Ansonsten Daten auslesen
    $levellast = [];
    $mother = [];
    $selected = [];
    $leveldata = [];

    require_once BASEDIR.'lib/class.recursivetree.php';
    $tree = new RecursiveTree(PRE.'_navi', 'id');
    $data = $tree->getTree(['*'], null, "nid='".$nid."'");
    if (count($data)) {
        //Mutterelemente auflisten
        foreach ($data as $res) {
            $levellast[$res['level']] = $res['id'];
            if (!isset($levellast[($res['level'] - 1)])) {
                continue;
            }
            $mother[$res['id']] = $levellast[($res['level'] - 1)];
        }

        //Selektiertes Element mit der besten Match-Quality
        $currentquality = 0;
        foreach ($data as $res) {
            if (!$res['link']) {
                continue;
            }
            $matchquality = navi_match_url($res['link']);
            if ($matchquality && $matchquality > $currentquality) {
                $currentquality = $matchquality;
                $selected = [];
            }
            if ($matchquality && $matchquality >= $currentquality) {
                $sel = $res['id'];
                $selected[] = $sel;
            }
        }

        //Mutterelemente selektieren
        while (isset($mother[$sel])) {
            $sel = $mother[$sel];
            $selected[] = $sel;
        }

        //Elemente de einzelnen Level herausfiltern
        foreach ($data as $res) {
            $motherelement = $mother[$res['id']];
            if ($motherelement && !in_array($motherelement, $selected)) {
                continue;
            }
            if (in_array($res['id'], $selected)) {
                $issel = true;
            } else {
                $issel = false;
            }
            ++$ei;
            $leveldata[$res['level']][$ei] = $res;
            $leveldata[$res['level']][$ei]['selected'] = $issel;
        }

        $cache[$nid] = $leveldata;
    }

    //Navigation generieren
    if (is_array($leveldata[$level])) {
        $newdata = $leveldata[$level];
    } else {
        $newdata = [];
    }

    foreach ($newdata as $res) {
        ++$i;
        $tabledata[$i]['TEXT'] = $res['text'];
        $tabledata[$i]['LINK'] = $res['link'];
        $tabledata[$i]['POPUP'] = $res['link_popup'];
        $tabledata[$i]['CODE'] = $res['code'];
        $tabledata[$i]['SELECTED'] = $res['selected'];
    }

    $tmpl->assign('NAVI', $tabledata);
    $tmpl->assign('ID', $nid);
    $tmpl->assign('LEVEL', $level);

    $tmpl->parse($template, 'navi');
}

//URL ist gew�hlt?
function navi_match_url($url)
{
    global $set,$db,$apx;
    static $current, $predir;
    if (!isset($current)) {
        $current = navi_current_url();
    }

    //Aktueller Ordner
    if (!isset($predir)) {
        if (preg_match('#\.[a-z0-9]+$#i', $current['path'])) {
            $predir = str_replace('\\', '/', dirname($current['path']));
            if ('/' != substr($predir, -1)) {
                $predir .= '/';
            }
        } else {
            $predir = $current['path'];
        }
    }

    $url = trim(str_replace('&amp;', '&', $url));
    if ('/' != substr($url, 0, 1) && !preg_match('#^[A-Za-z]{3,}://#', $url)) {
        $url = $predir.$url;
    }
    $parsed = @parse_url($url);

    if ($parsed['host'] && strtolower($parsed['host']) != strtolower($_SERVER['HTTP_HOST'])) {
        return false;
    }

    //Link = "downloads.html", URL = "downloads,*.html" => selektiert
    if (!$parsed['query'] && preg_match('#^(/([^/]+/)*[a-z0-9_-]+)\.(html|php)$#si', $parsed['path'], $matches)) {
        $pageid = $matches[1];
        if (preg_match('#'.preg_quote($pageid).',#', $current['path'])) {
            return 2;
        }
    }

    if (!$parsed['query'] && preg_match('#^(/([^/]+/)*)([a-z0-9_-]+)\.(html|php)$#si', $parsed['path'], $matches)) {
        $pageid = $matches[3];
        if (preg_match('#'.preg_quote($matches[1]).preg_quote($pageid).',#', $current['path'])) {
            return 2;
        }
    }

    //Link = "/ordner/", URL = "/ordner/whatever.html" => selektiert
    if (preg_match('#^/([^/]+/)+(index\.(html|php))?$#', $parsed['path'], $matches)) {
        $dirname = $matches[1];
        if ($dirname && preg_match('#^/'.preg_quote($dirname).'#', $current['path'])) {
            return 1;
        }
    }

    //Dateipfade stimmen nicht �berein
    if ($parsed['path'] != $current['path']) {
        return false;
    }
    //Dateipfade sind gleich. Wenn der Link keine Parameter hat, die URL aber ist der Link selektiert
    if (!$parsed['query'] /*&& !$current['query']*/) {
        return 3;
    }
    //Ansonsten Abgleich der Query-Parameter
    $query = explode('&', $parsed['query']);
    $vars = [];

    foreach ($query as $one) {
        $pp = explode('=', $one, 2);
        $vars[$pp[0]] = $pp[1];
    }

    foreach ($vars as $varname => $value) {
        if (!isset($current['vars'][$varname])) {
            return false;
        }
        if ($current['vars'][$varname] != $value) {
            return false;
        }
    }

    return 4;
}

//Aktuelle URL auslesen und parsen
function navi_current_url()
{
    $parsed = @parse_url($_SERVER['REQUEST_URI']);
    $parsed['vars'] = [];

    if ($parsed['query']) {
        $query = explode('&', $parsed['query']);
        foreach ($query as $one) {
            $pp = explode('=', $one, 2);
            $vars[$pp[0]] = $pp[1];
        }
        $parsed['vars'] = $vars;
    }

    return $parsed;
}

//Einzelnen Knoten auslesen
function navi_node($nodeid = 0, $template = 'node')
{
    global $set,$db,$apx;
    $nodeid = (int) $nodeid;
    if (!$nodeid) {
        return;
    }
    $tmpl = new tengine();

    require_once BASEDIR.'lib/class.recursivetree.php';
    $tree = new RecursiveTree(PRE.'_navi', 'id');
    $res = $tree->getNode($nodeid, ['*']);

    $tmpl->assign('ID', $res['id']);
    $tmpl->assign('LEVEL', $res['level']);
    $tmpl->assign('CHILDREN', $res['children']);
    $tmpl->assign('TEXT', $res['text']);
    $tmpl->assign('LINK', $res['link']);
    $tmpl->assign('POPUP', $res['link_popup']);
    $tmpl->assign('CODE', $res['code']);

    $tmpl->parse($template, 'navi');
}

//Breadcrumb auslesen
function navi_breadcrumb($nid = 1, $template = 'breadcrumb')
{
    global $set,$db,$apx;
    $nid = (int) $nid;
    if (!$nid) {
        return;
    }
    $tmpl = new tengine();

    require_once BASEDIR.'lib/class.recursivetree.php';
    $tree = new RecursiveTree(PRE.'_navi', 'id');
    $data = $tree->getTree(['*'], null, "nid='".$nid."'");
    $selected = navi_get_selected($data);

    $selectedPath = null;
    $path = [];
    $selectedNodeLevel = 0;

    foreach ($data as $res) {
        if (in_array($res['id'], $selected)) {
            //Tiefere Pfadteile entfernen
            $dellevel = $res['level'];
            while (isset($path[$dellevel])) {
                unset($path[$dellevel]);
                ++$dellevel;
            }

            //Node zum Pfad hinzuf�gen
            $path[$res['level']] = $res;

            //Dieser Pfad ist l�nger als der bisherige l�ngste
            if ($res['level'] > $selectedNodeLevel) {
                $selectedPath = $path;
                $selectedNodeLevel = $res['level'];
            }
        }
    }

    if (count($selectedPath)) {
        foreach ($selectedPath as $res) {
            ++$i;
            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['LEVEL'] = $res['level'];
            $tabledata[$i]['CHILDREN'] = $res['children'];
            $tabledata[$i]['TEXT'] = $res['text'];
            $tabledata[$i]['LINK'] = $res['link'];
            $tabledata[$i]['POPUP'] = $res['link_popup'];
            $tabledata[$i]['CODE'] = $res['code'];
            $tabledata[$i]['SELECTED'] = true;
        }
    }

    $tmpl->assign('NAVI', $tabledata);
    $tmpl->parse($template, 'navi');
}
