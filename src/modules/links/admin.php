<?php

// LINKS
// =====

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Funktionen laden
include BASEDIR.getmodulepath('links').'admin_extend.php';

class action extends links_functions
{
    public $cat;

    //Startup
    public function action()
    {
        require_once BASEDIR.'lib/class.recursivetree.php';
        $this->cat = new RecursiveTree(PRE.'_links_cat', 'id');

        require_once BASEDIR.'lib/class.mediamanager.php';
        $this->mm = new mediamanager();
    }

    ////////////////////////////////////////////////////////////////////////////////////////// LINKS

    //***************************** Links zeigen *****************************
    public function show()
    {
        global $set,$db,$apx,$html;

        //Suche durchführen
        if (($_REQUEST['item'] && ($_REQUEST['title'] || $_REQUEST['url'] || $_REQUEST['text'])) || $_REQUEST['secid'] || $_REQUEST['catid'] || $_REQUEST['userid']) {
            $where = '';
            $_REQUEST['secid'] = (int) $_REQUEST['secid'];
            $_REQUEST['catid'] = (int) $_REQUEST['catid'];
            $_REQUEST['userid'] = (int) $_REQUEST['userid'];

            //Suchbegriff
            if ($_REQUEST['item']) {
                if ($_REQUEST['title']) {
                    $sc[] = "title LIKE '%".addslashes_like($_REQUEST['item'])."%'";
                }
                if ($_REQUEST['url']) {
                    $sc[] = "url LIKE '%".addslashes_like($_REQUEST['item'])."%'";
                }
                if ($_REQUEST['text']) {
                    $sc[] = "text LIKE '%".addslashes_like($_REQUEST['item'])."%'";
                }
                if (is_array($sc)) {
                    $where .= ' AND ( '.implode(' OR ', $sc).' )';
                }
            }

            //Sektion
            if (!$apx->session->get('section') && $_REQUEST['secid']) {
                $where .= " AND ( secid LIKE '%|".$_REQUEST['secid']."|%' OR secid='all' ) ";
            }

            //Kategorie
            if ($_REQUEST['catid']) {
                $tree = $this->cat->getChildrenIds($_REQUEST['catid']);
                $tree[] = $_REQUEST['catid'];
                if (is_array($tree)) {
                    $where .= ' AND catid IN ('.implode(',', $tree).') ';
                }
            }

            //Benutzer
            if ($_REQUEST['userid']) {
                $where .= " AND userid='".$_REQUEST['userid']."' ";
            }

            $data = $db->fetch('SELECT id FROM '.PRE.'_links WHERE 1 '.$where);
            $ids = get_ids($data, 'id');
            $ids[] = -1;
            $searchid = saveSearchResult('admin_links', $ids, [
                'item' => $_REQUEST['item'],
                'title' => $_REQUEST['title'],
                'url' => $_REQUEST['url'],
                'text' => $_REQUEST['text'],
                'catid' => $_REQUEST['catid'],
                'secid' => $_REQUEST['secid'],
                'userid' => $_REQUEST['userid'],
            ]);
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: action.php?action=links.show&what='.$_REQUEST['what'].'&searchid='.$searchid);

            return;
        }

        //Unbroken setzen
        $_REQUEST['unbroken'] = (int) $_REQUEST['unbroken'];
        if ($_REQUEST['unbroken']) {
            $db->query('UPDATE '.PRE."_links SET broken='' WHERE id='".$_REQUEST['unbroken']."' LIMIT 1");
        }

        //Vorgaben
        $_REQUEST['title'] = 1;
        $_REQUEST['url'] = 1;
        $_REQUEST['text'] = 1;

        quicklink('links.add');

        $layerdef[] = ['LAYER_ALL', 'action.php?action=links.show', !$_REQUEST['what']];
        $layerdef[] = ['LAYER_SEND', 'action.php?action=links.show&amp;what=send', 'send' == $_REQUEST['what']];
        $layerdef[] = ['LAYER_BROKEN', 'action.php?action=links.show&amp;what=broken', 'broken' == $_REQUEST['what']];

        //Layer Header ausgeben
        $html->layer_header($layerdef);

        $orderdef[0] = 'creation';
        $orderdef['title'] = ['a.title', 'ASC', 'COL_TITLE'];
        $orderdef['user'] = ['b.username', 'ASC', 'COL_UPLOADER'];
        $orderdef['category'] = ['c.title', 'ASC', 'COL_CATEGORY'];
        $orderdef['creation'] = ['a.addtime', 'DESC', 'SORT_ADDTIME'];
        $orderdef['publication'] = ['a.starttime', 'DESC', 'SORT_STARTTIME'];
        $orderdef['hits'] = ['a.hits', 'DESC', 'COL_HITS'];

        //Suchergebnis?
        $resultFilter = '';
        if ($_REQUEST['searchid']) {
            $searchRes = getSearchResult('admin_links', $_REQUEST['searchid']);
            if ($searchRes) {
                list($resultIds, $resultMeta) = $searchRes;
                $_REQUEST['item'] = $resultMeta['item'];
                $_REQUEST['title'] = $resultMeta['title'];
                $_REQUEST['subtitle'] = $resultMeta['subtitle'];
                $_REQUEST['teaser'] = $resultMeta['teaser'];
                $_REQUEST['text'] = $resultMeta['text'];
                $_REQUEST['catid'] = $resultMeta['catid'];
                $_REQUEST['secid'] = $resultMeta['secid'];
                $_REQUEST['userid'] = $resultMeta['userid'];
                $resultFilter = ' AND a.id IN ('.implode(', ', $resultIds).')';
            } else {
                $_REQUEST['searchid'] = '';
            }
        }

        //Sektionen auflisten
        $seclist = '';
        if (is_array($apx->sections) && count($apx->sections)) {
            foreach ($apx->sections as $res) {
                $seclist .= '<option value="'.$res['id'].'"'.iif($_REQUEST['secid'] == $res['id'], ' selected="selected"').'>'.replace($res['title']).'</option>';
            }
        }

        //Kategorien auflisten
        $catlist = '';
        $data = $this->cat->getTree(['title', 'open']);
        if (count($data)) {
            foreach ($data as $res) {
                if ($res['level']) {
                    $space = str_repeat('&nbsp;&nbsp;', $res['level'] - 1);
                }
                $catlist .= '<option value="'.$res['id'].'"'.iif($_REQUEST['catid'] == $res['id'], ' selected="selected"').'>'.$space.replace($res['title']).'</option>';
            }
        }

        //Benutzer auflisten
        $userlist = '';
        $data = $db->fetch('SELECT b.userid,b.username FROM '.PRE.'_links AS a LEFT JOIN '.PRE.'_user AS b USING (userid) WHERE a.userid!=0 GROUP BY userid ORDER BY username ASC');
        if (count($data)) {
            foreach ($data as $res) {
                $userlist .= '<option value="'.$res['userid'].'"'.iif($_REQUEST['userid'] == $res['userid'], ' selected="selected"').'>'.replace($res['username']).'</option>';
            }
        }

        $apx->tmpl->assign('ITEM', compatible_hsc($_REQUEST['item']));
        $apx->tmpl->assign('SECLIST', $seclist);
        $apx->tmpl->assign('CATLIST', $catlist);
        $apx->tmpl->assign('USERLIST', $userlist);
        $apx->tmpl->assign('STITLE', (int) $_REQUEST['title']);
        $apx->tmpl->assign('SSUBTITLE', (int) $_REQUEST['subtitle']);
        $apx->tmpl->assign('STEASER', (int) $_REQUEST['teaser']);
        $apx->tmpl->assign('SURL', (int) $_REQUEST['url']);
        $apx->tmpl->assign('STEXT', (int) $_REQUEST['text']);
        $apx->tmpl->assign('WHAT', $_REQUEST['what']);
        $apx->tmpl->assign('EXTENDED', $searchRes);
        $apx->tmpl->parse('search');

        //Filter
        $layerFilter = '';
        if ('broken' == $_REQUEST['what']) {
            $layerFilter = ' AND a.broken!=0 ';
        } elseif ('send' == $_REQUEST['what']) {
            $layerFilter = " AND a.send_ip!='' ";
        }

        list($count) = $db->first('SELECT count(userid) FROM '.PRE.'_links AS a WHERE 1 '.$resultFilter.$layerFilter.section_filter(true, 'secid'));
        pages('action.php?action=links.show&amp;what='.$_REQUEST['what'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']).'&amp;sortby='.$_REQUEST['sortby'], $count);
        $data = $db->fetch('SELECT a.id,a.secid,a.send_username,a.title,a.addtime,a.allowcoms,a.allowrating,a.starttime,a.endtime,a.hits,a.broken,b.userid,b.username,c.title AS catname FROM '.PRE.'_links AS a LEFT JOIN '.PRE.'_user AS b USING(userid) LEFT JOIN '.PRE.'_links_cat AS c ON a.catid=c.id WHERE 1 '.$resultFilter.$layerFilter.section_filter(true, 'a.secid').' '.getorder($orderdef).getlimit());
        $this->show_print($data);
        orderstr($orderdef, 'action.php?action=links.show&amp;what='.$_REQUEST['what'].iif($_REQUEST['searchid'], '&amp;searchid='.$_REQUEST['searchid']));
        save_index($_SERVER['REQUEST_URI']);

        //Layer-Footer ausgeben
        $html->layer_footer();
    }

    //Links auflisten
    public function show_print($data)
    {
        global $set,$db,$apx,$html;

        $col[] = ['', 1, 'align="center"'];
        $col[] = [$apx->lang->get('COL_TITLE').' / '.$apx->lang->get('COL_AUTHOR'), 45, 'class="title"'];
        $col[] = ['COL_CATEGORY', 25, 'align="center"'];
        $col[] = ['COL_ADDTIME', 20, 'align="center"'];
        $col[] = ['COL_HITS', 10, 'align="center"'];

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

                $tmp = unserialize_section($res['secid']);
                $title = shorttext(strip_tags($res['title']), 40);
                $link = mklink(
                    'links.php?id='.$res['id'],
                    'links,id'.$res['id'].urlformat($res['title']).'.html',
                    iif($set['main']['forcesection'], iif(unserialize_section($res['secid']) == ['all'], $apx->section_default, array_shift($tmp)), 0)
                );

                $tabledata[$i]['ID'] = $res['id'];
                $tabledata[$i]['COL2'] .= '<a href="'.$link.'" target="_blank">'.$title.'</a>';
                $tabledata[$i]['COL3'] = replace($res['catname']);
                $tabledata[$i]['COL4'] = iif($res['starttime'], mkdate($res['starttime'], '<br />'), '&nbsp;');
                $tabledata[$i]['COL5'] = $res['hits'];

                if ($res['username']) {
                    $tabledata[$i]['COL2'] .= '<br /><small>'.$apx->lang->get('BY').' '.replace($res['username']).'</small>';
                } else {
                    $tabledata[$i]['COL2'] .= '<br /><small>'.$apx->lang->get('BY').' '.$apx->lang->get('GUEST').': <i>'.replace($res['send_username']).'</i></small>';
                }
                if ($res['broken']) {
                    $tabledata[$i]['COL2'] = '<a href="action.php?action=links.show&amp;what='.$_REQUEST['what'].'&amp;p='.$_REQUEST['p'].'&amp;item='.$_REQUEST['item'].'&amp;sortby='.$_REQUEST['sortby'].'&amp;title='.$_REQUEST['title'].'&amp;text='.$_REQUEST['text'].'&amp;catid='.$_REQUEST['catid'].'&amp;unbroken='.$res['id'].'"><img src="../'.getmodulepath('links').'images/broken.gif" alt="'.$apx->lang->get('BROKEN').': '.mkdate($res['broken']).'" title="'.$apx->lang->get('BROKEN').': '.mkdate($res['broken']).'" align="right" /></a>'.$tabledata[$i]['COL2'];
                }

                //Optionen
                if ($apx->user->has_right('links.edit') && ($res['userid'] == $apx->user->info['userid'] || $apx->user->has_spright('links.edit'))) {
                    $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'links.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                if ($apx->user->has_right('links.del') && ($res['userid'] == $apx->user->info['userid'] || $apx->user->has_spright('links.del'))) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('del.gif', 'links.del', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                if ((!$res['starttime'] || $res['endtime'] < time()) && $apx->user->has_right('links.enable') && ($res['userid'] == $apx->user->info['userid'] || $apx->user->has_spright('links.enable'))) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('enable.gif', 'links.enable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
                } elseif ($res['starttime'] && $apx->user->has_right('links.disable') && ($res['userid'] == $apx->user->info['userid'] || $apx->user->has_spright('links.disable'))) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('disable.gif', 'links.disable', 'id='.$res['id'], $apx->lang->get('CORE_DISABLE'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                //Kommentare + Bewertungen
                if ($apx->is_module('comments') || $apx->is_module('ratings')) {
                    $tabledata[$i]['OPTIONS'] .= '&nbsp;';
                }
                if ($apx->is_module('comments')) {
                    list($comments) = $db->first('SELECT count(id) FROM '.PRE."_comments WHERE ( module='links' AND mid='".$res['id']."' )");
                    if ($comments && ($apx->is_module('comments') && $set['links']['coms']) && $res['allowcoms'] && $apx->user->has_right('comments.show')) {
                        $tabledata[$i]['OPTIONS'] .= optionHTML('comments.gif', 'comments.show', 'module=links&mid='.$res['id'], $apx->lang->get('COMMENTS').' ('.$comments.')');
                    } else {
                        $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                    }
                }
                if ($apx->is_module('ratings')) {
                    list($ratings) = $db->first('SELECT count(id) FROM '.PRE."_ratings WHERE ( module='links' AND mid='".$res['id']."' )");
                    if ($ratings && ($apx->is_module('ratings') && $set['links']['ratings']) && $res['allowrating'] && $apx->user->has_right('ratings.show')) {
                        $tabledata[$i]['OPTIONS'] .= optionHTML('ratings.gif', 'ratings.show', 'module=links&mid='.$res['id'], $apx->lang->get('RATINGS').' ('.$ratings.')');
                    } else {
                        $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                    }
                }
            }
        }

        $multiactions = [];
        if ($apx->user->has_right('links.del')) {
            $multiactions[] = [$apx->lang->get('CORE_DEL'), 'action.php?action=links.del', false];
        }
        if ($apx->user->has_right('links.enable')) {
            $multiactions[] = [$apx->lang->get('CORE_ENABLE'), 'action.php?action=links.enable', false];
        }
        if ($apx->user->has_right('links.disable')) {
            $multiactions[] = [$apx->lang->get('CORE_DISABLE'), 'action.php?action=links.disable', false];
        }

        $apx->tmpl->assign('TABLE', $tabledata);
        $html->table($col, $multiactions);
    }

    //***************************** Neuer Link *****************************
    public function add()
    {
        global $set,$db,$apx;

        //Sektions-Liste
        if (!is_array($_POST['secid']) || 'all' == $_POST['secid'][0]) {
            $_POST['secid'] = ['all'];
        }

        //Anfrage absenden
        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['title'] || !$_POST['url'] || !$_POST['catid'] || !$_POST['text']) {
                infoNotComplete();
            } elseif (!$this->update_linkpic()) { /*DO NOTHING*/
            } else {
                $_POST['secid'] = serialize_section($_POST['secid']);
                $_POST['addtime'] = time();
                $_POST['linkpic'] = $this->linkpicpath;

                //Veröffentlichung
                if ($apx->user->has_right('links.enable') && $_POST['pubnow']) {
                    $addfield = ',starttime,endtime';
                    $_POST['starttime'] = time();
                    $_POST['endtime'] = '3000000000';
                }

                //Autor
                if (!$apx->user->has_spright('links.edit')) {
                    $_POST['userid'] = $apx->user->info['userid'];
                }

                $db->dinsert(PRE.'_links', 'secid,catid,userid,title,url,linkpic,text,meta_description,galid,addtime,searchable,restricted,allowcoms,allowrating,top'.$addfield);
                $nid = $db->insert_id();
                logit('LINKS_ADD', 'ID #'.$nid);

                //Tags
                $tagids = produceTagIds($_POST['tags']);
                foreach ($tagids as $tagid) {
                    $db->query('INSERT IGNORE INTO '.PRE."_links_tags VALUES('".$nid."', '".$tagid."')");
                }

                if ('newcat' == $_POST['catid'] && $apx->user->has_right('links.catadd')) {
                    return printJSRedirect('action.php?action=links.catadd&addid='.$nid);
                }
                printJSRedirect('action.php?action=links.show');
            }
        } else {
            $_POST['searchable'] = 1;
            $_POST['allowcoms'] = 1;
            $_POST['allowrating'] = 1;
            $_POST['userid'] = $apx->user->info['userid'];

            mediamanager('links');

            $apx->tmpl->assign('USERID', $_POST['userid']);
            $apx->tmpl->assign('SECID', $_POST['secid']);
            $apx->tmpl->assign('GALID', $_POST['galid']);
            $apx->tmpl->assign('CATLIST', $this->get_catlist());
            $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
            $apx->tmpl->assign('URL', compatible_hsc($_POST['url']));
            $apx->tmpl->assign('PIC_COPY', compatible_hsc($_POST['pic_copy']));
            $apx->tmpl->assign('TEXT', compatible_hsc($_POST['text']));
            $apx->tmpl->assign('META_DESCRIPTION', compatible_hsc($_POST['meta_description']));
            $apx->tmpl->assign('TAGS', compatible_hsc($_POST['tags']));

            $apx->tmpl->assign('ALLOWCOMS', (int) $_POST['allowcoms']);
            $apx->tmpl->assign('ALLOWRATING', (int) $_POST['allowrating']);
            $apx->tmpl->assign('TOP', (int) $_POST['top']);
            $apx->tmpl->assign('PUBNOW', (int) $_POST['pubnow']);
            $apx->tmpl->assign('SEARCHABLE', (int) $_POST['searchable']);
            $apx->tmpl->assign('RESTRICTED', (int) $_POST['restricted']);

            $apx->tmpl->assign('ACTION', 'add');
            $apx->tmpl->parse('add_edit');
        }
    }

    //***************************** Link bearbeiten *****************************
    public function edit()
    {
        global $set,$db,$apx;
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        //Sektions-Liste
        if (!is_array($_POST['secid']) || 'all' == $_POST['secid'][0]) {
            $_POST['secid'] = ['all'];
        }

        //Anfrage abesenden
        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['title'] || !$_POST['url'] || !$_POST['catid'] || !$_POST['text']) {
                infoNotComplete();
            } elseif (!$this->update_linkpic()) { /*DO NOTHING*/
            } else {
                $_POST['secid'] = serialize_section($_POST['secid']);
                $_POST['linkpic'] = $this->linkpicpath;

                //Autor
                if ($apx->user->has_spright('links.edit') && $_POST['userid']) {
                    if ('send' == $_POST['userid']) {
                        $_POST['userid'] = 0;
                    } else {
                        $_POST['userid'] = $_POST['userid'];
                    }
                    $addfields .= ',userid';
                }

                //Veröffentlichung
                if ($apx->user->has_right('links.enable') && isset($_POST['t_day_1'])) {
                    $_POST['starttime'] = maketime(1);
                    $_POST['endtime'] = maketime(2);
                    if ($_POST['starttime']) {
                        if (!$_POST['endtime'] || $_POST['endtime'] <= $_POST['starttime']) {
                            $_POST['endtime'] = 3000000000;
                        }
                        $addfields .= ',starttime,endtime';
                    }
                }

                $db->dupdate(PRE.'_links', 'secid,catid,userid,title,url,linkpic,text,meta_description,galid,searchable,restricted,allowcoms,allowrating,top'.$addfield, "WHERE id='".$_REQUEST['id']."' LIMIT 1");
                logit('LINKS_EDIT', 'ID #'.$_REQUEST['id']);

                //Tags
                $db->query('DELETE FROM '.PRE."_links_tags WHERE id='".$_REQUEST['id']."'");
                $tagids = produceTagIds($_POST['tags']);
                foreach ($tagids as $tagid) {
                    $db->query('INSERT IGNORE INTO '.PRE."_links_tags VALUES('".$_REQUEST['id']."', '".$tagid."')");
                }

                if ('newcat' == $_POST['catid'] && $apx->user->has_right('links.catadd')) {
                    return printJSRedirect('action.php?action=links.catadd&addid='.$_REQUEST['id']);
                }
                printJSRedirect(get_index('links.show'));
            }
        } else {
            $res = $db->first('SELECT * FROM '.PRE."_links WHERE id='".$_REQUEST['id']."' LIMIT 1", 1);
            foreach ($res as $key => $val) {
                $_POST[$key] = $val;
            }
            $_POST['secid'] = unserialize_section($_POST['secid']);

            //Keine Benutzer-ID gesetzt => Eingesendeter Link
            if (!$res['userid']) {
                $_POST['userid'] = 'send';
            }

            //Veröffentlichung
            if ($res['starttime']) {
                maketimepost(1, $res['starttime']);
                if ($res['endtime'] < 2147483647) {
                    maketimepost(2, $res['endtime']);
                }
            }

            mediamanager('links');

            //Veröffentlichung
            if ($apx->user->has_right('links.enable') && isset($_POST['t_day_1'])) {
                $apx->tmpl->assign('STARTTIME', choosetime(1, 0, maketime(1)));
                $apx->tmpl->assign('ENDTIME', choosetime(2, 1, maketime(2)));
            }

            //Einsende-User beachten
            $send = $db->first('SELECT send_username,send_email FROM '.PRE."_links WHERE id='".$_REQUEST['id']."' LIMIT 1");
            if ($send['send_username']) {
                $usersend = '<option value="send"'.iif('send' == $_POST['userid'], ' selected="selected"').'>'.$apx->lang->get('GUEST').': '.$send['send_username'].iif($send['send_email'], ' ('.$send['send_email'].')').'</option>';
            }

            //Bild
            $teaserpic = '';
            if ($_POST['linkpic']) {
                $teaserpicpath = $_POST['linkpic'];
                $poppicpath = str_replace('-thumb.', '.', $teaserpicpath);
                if (file_exists(BASEDIR.getpath('uploads').$poppicpath)) {
                    $teaserpic = '../'.getpath('uploads').$poppicpath;
                } else {
                    $teaserpic = '../'.getpath('uploads').$teaserpicpath;
                }
            }

            //Tags
            $tags = [];
            $tagdata = $db->fetch('
			SELECT t.tag
			FROM '.PRE.'_links_tags AS n
			LEFT JOIN '.PRE."_tags AS t USING(tagid)
			WHERE n.id='".$_REQUEST['id']."'
			ORDER BY t.tag ASC
		");
            $tags = get_ids($tagdata, 'tag');
            $_POST['tags'] = implode(', ', $tags);

            $apx->tmpl->assign('USERID', $_POST['userid']);
            $apx->tmpl->assign('USER_SEND', $usersend);
            $apx->tmpl->assign('SECID', $_POST['secid']);
            $apx->tmpl->assign('GALID', $_POST['galid']);
            $apx->tmpl->assign('CATLIST', $this->get_catlist());
            $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
            $apx->tmpl->assign('URL', compatible_hsc($_POST['url']));
            $apx->tmpl->assign('LINKPIC', $teaserpic);
            $apx->tmpl->assign('PIC_COPY', compatible_hsc($_POST['pic_copy']));
            $apx->tmpl->assign('TEXT', compatible_hsc($_POST['text']));
            $apx->tmpl->assign('META_DESCRIPTION', compatible_hsc($_POST['meta_description']));
            $apx->tmpl->assign('TAGS', compatible_hsc($_POST['tags']));

            $apx->tmpl->assign('ALLOWCOMS', (int) $_POST['allowcoms']);
            $apx->tmpl->assign('ALLOWRATING', (int) $_POST['allowrating']);
            $apx->tmpl->assign('TOP', (int) $_POST['top']);
            $apx->tmpl->assign('SEARCHABLE', (int) $_POST['searchable']);
            $apx->tmpl->assign('RESTRICTED', (int) $_POST['restricted']);

            $apx->tmpl->assign('ACTION', 'edit');
            $apx->tmpl->assign('ID', $_REQUEST['id']);
            $apx->tmpl->parse('add_edit');
        }
    }

    //***************************** Link löschen *****************************
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
                    header('Location: '.get_index('links.show'));

                    return;
                }

                if (count($cache)) {
                    foreach ($cache as $id) {
                        $db->query('DELETE FROM '.PRE."_links WHERE id='".$id."' ".iif(!$apx->user->has_spright('links.del'), " AND userid='".$apx->user->info['userid']."'").' )');

                        //Tags löschen
                        if ($db->affected_rows()) {
                            $db->query('DELETE FROM '.PRE."_links_tags WHERE id='".$id."'");
                        }

                        logit('LINKS_DEL', 'ID #'.$id);
                    }
                }

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.get_index('links.show'));
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
                    $db->query('DELETE FROM '.PRE."_links WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('links.del'), " AND userid='".$apx->user->info['userid']."'").' ) LIMIT 1');
                    if (!$db->affected_rows()) {
                        die('access denied!');
                    }

                    //Kommentare + Bewertungen löschen (nur wenn ein Eintrag gelöscht wurde -> User hat Recht dazu!)
                    if ($apx->is_module('comments')) {
                        $db->query('DELETE FROM '.PRE."_comments WHERE ( module='links' AND mid='".$_REQUEST['id']."' )");
                    }
                    if ($apx->is_module('ratings')) {
                        $db->query('DELETE FROM '.PRE."_ratings WHERE ( module='links' AND mid='".$_REQUEST['id']."' )");
                    }

                    //Tags löschen
                    $db->query('DELETE FROM '.PRE."_links_tags WHERE id='".$_REQUEST['id']."'");

                    logit('LINKS_DEL', 'ID #'.$_REQUEST['id']);
                    printJSRedirect(get_index('links.show'));
                }
            } else {
                list($title) = $db->first('SELECT title FROM '.PRE."_links WHERE id='".$_REQUEST['id']."' LIMIT 1");
                $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($title)]));
                tmessageOverlay('deltitle', ['ID' => $_REQUEST['id']], '/');
            }
        }
    }

    //***************************** Link aktivieren *****************************
    public function enable()
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
                    header('Location: '.get_index('links.show'));

                    return;
                }

                if (count($cache)) {
                    $db->query('UPDATE '.PRE."_links SET starttime='".time()."',endtime='3000000000' WHERE ( id IN (".implode(',', $cache).') '.iif(!$apx->user->has_spright('links.enable'), " AND userid='".$apx->user->info['userid']."'").' ) ');
                    foreach ($cache as $id) {
                        logit('LINKS_ENABLE', 'ID #'.$id);
                    }
                }

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.get_index('links.show'));
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
                    $starttime = maketime(1);
                    $endtime = maketime(2);
                    if (!$endtime || $endtime <= $starttime) {
                        $endtime = 3000000000;
                    }

                    $db->query('UPDATE '.PRE."_links SET starttime='".$starttime."',endtime='".$endtime."' WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('links.enable'), " AND userid='".$apx->user->info['userid']."'").' ) LIMIT 1');
                    logit('LINKS_ENABLE', 'ID #'.$_REQUEST['id']);
                    printJSRedirect(get_index('links.show'));
                }
            } else {
                list($title) = $db->first('SELECT title FROM '.PRE."_links WHERE id='".$_REQUEST['id']."' LIMIT 1");
                $apx->tmpl->assign('ID', $_REQUEST['id']);
                $apx->tmpl->assign('TITLE', compatible_hsc($title));
                $apx->tmpl->assign('STARTTIME', choosetime(1, 0, time()));
                $apx->tmpl->assign('ENDTIME', choosetime(2, 1));
                tmessageOverlay('enable');
            }
        }
    }

    //***************************** Link widerrufen *****************************
    public function disable()
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
                    header('Location: '.get_index('links.show'));

                    return;
                }

                if (count($cache)) {
                    $db->query('UPDATE '.PRE."_links SET starttime='0',endtime='0' WHERE ( id IN (".implode(',', $cache).') '.iif(!$apx->user->has_spright('links.enable'), " AND userid='".$apx->user->info['userid']."'").' ) ');
                    foreach ($cache as $id) {
                        logit('LINKS_DISABLE', 'ID #'.$id);
                    }
                }

                header('HTTP/1.1 301 Moved Permanently');
                header('Location: '.get_index('links.show'));
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
                    $db->query('UPDATE '.PRE."_links SET starttime='0',endtime='0' WHERE ( id='".$_REQUEST['id']."' ".iif(!$apx->user->has_spright('links.disable'), " AND userid='".$apx->user->info['userid']."'").' ) LIMIT 1');
                    logit('LINKS_DISABLE', 'ID #'.$_REQUEST['id']);
                    printJSRedirect(get_index('links.show'));
                }
            } else {
                list($title) = $db->first('SELECT title FROM '.PRE."_links WHERE id='".$_REQUEST['id']."' LIMIT 1");
                $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($title)]));
                tmessageOverlay('disable', ['ID' => $_REQUEST['id']]);
            }
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////// KATEGORIEN

    //***************************** Kategorien zeigen *****************************
    public function catshow()
    {
        global $set,$db,$apx,$html;

        //Struktur reparieren
        if ($_REQUEST['repair']) {
            $this->cat->repair();
            echo 'Repair done!';

            return;
        }

        quicklink('links.catadd');

        //DnD-Hinweis
        if ($apx->user->has_right('links.edit')) {
            echo '<p class="hint">'.$apx->lang->get('USEDND').'</p>';
        }

        $col[] = ['ID', 0, 'align="center"'];
        $col[] = ['COL_CATNAME', 75, 'class="title"'];
        $col[] = ['COL_LINKS', 25, 'align="center"'];

        $data = $this->cat->getTree(['title', 'open']);
        if (count($data)) {
            //Ausgabe erfolgt
            foreach ($data as $res) {
                ++$i;

                if ($res['open']) {
                    list($links) = $db->first('SELECT count(id) FROM '.PRE."_links WHERE catid='".$res['id']."'");
                }

                $tabledata[$i]['COL1'] = $res['id'];
                $tabledata[$i]['COL2'] = replace($res['title']);
                $tabledata[$i]['COL3'] = iif(isset($links), $links, '&nbsp;');
                $tabledata[$i]['CLASS'] = 'l'.($res['level'] - 1).($res['children'] ? ' haschildren' : '').($res['level'] > 1 ? ' hidden' : '').($res['iscat'] ? ' dark' : '');
                $tabledata[$i]['ID'] = 'node:'.$res['id'];

                //Optionen
                if ($apx->user->has_right('links.catedit')) {
                    $tabledata[$i]['OPTIONS'] .= optionHTML('edit.gif', 'links.catedit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                if ($apx->user->has_right('links.catdel') && !$links) {
                    $tabledata[$i]['OPTIONS'] .= '<span class="ifhasnochildren">'.optionHTMLOverlay('del.gif', 'links.catdel', 'id='.$res['id'], $apx->lang->get('CORE_DEL')).'</span><span class="ifhaschildren"><img alt="" src="design/ispace.gif"/></span>';
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                if ($apx->user->has_right('links.catclean') && $links) {
                    $tabledata[$i]['OPTIONS'] .= optionHTMLOverlay('clean.gif', 'links.catclean', 'id='.$res['id'], $apx->lang->get('CLEAN'));
                } else {
                    $tabledata[$i]['OPTIONS'] .= '<img src="design/ispace.gif" alt="" />';
                }

                //Anordnen nur bei Unterkategorien
                /*$tabledata[$i]['OPTIONS'].='&nbsp;';
                if ( $apx->user->has_right('links.catmove') && $follow[$res['id']]['prev'] ) $tabledata[$i]['OPTIONS'].=optionHTML('moveup.gif', 'links.catmove', 'direction=up&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEUP'));
                else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
                if ( $apx->user->has_right('links.catmove') && $follow[$res['id']]['next'] ) $tabledata[$i]['OPTIONS'].=optionHTML('movedown.gif', 'links.catmove', 'direction=down&id='.$res['id'].'&sectoken='.$apx->session->get('sectoken'), $apx->lang->get('MOVEDOWN'));
                else $tabledata[$i]['OPTIONS'].='<img src="design/ispace_small.gif" alt="" style="vertical-align:middle;" />';
                */

                unset($links);
            }
        }

        $apx->tmpl->assign('TABLE', $tabledata);

        //Mit Unter-Kategorien
        echo '<div class="treeview" id="tree">';
        $html->table($col);
        echo '</div>';

        $open = $apx->session->get('links_cat_open');
        $open = dash_unserialize($open);
        $opendata = [];
        foreach ($open as $catid) {
            $opendata[] = [
                'ID' => $catid,
            ];
        }
        $apx->tmpl->assign('OPEN', $opendata);
        $apx->tmpl->assign('EDIT_ALLOWED', $apx->user->has_Right('links.edit'));
        $apx->tmpl->parse('catshow_js');

        save_index($_SERVER['REQUEST_URI']);
    }

    //***************************** Neue Kategorie *****************************
    public function catadd()
    {
        global $set,$db,$apx;

        if ($_REQUEST['updateparent']) {
            $_POST['open'] = 1;
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['title'] || !$_POST['parent']) {
                infoNotComplete();
            } else {
                //WENN ROOT
                if ('root' == $_POST['parent']) {
                    $nid = $this->cat->createNode(0, [
                        'title' => $_POST['title'],
                        'text' => $_POST['text'],
                        'icon' => $_POST['icon'],
                        'open' => $_POST['open'],
                    ]);
                    logit('LINKS_CATADD', 'ID #'.$nid);

                    //Beitrag der Kategorie hinzufügen
                    if ($_REQUEST['updateparent']) {
                        printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
                    } else {
                        printJSRedirect('action.php?action=links.catshow');
                    }
                }

                //WENN NODE
                else {
                    $nid = $this->cat->createNode(intval($_POST['parent']), [
                        'title' => $_POST['title'],
                        'text' => $_POST['text'],
                        'icon' => $_POST['icon'],
                        'open' => $_POST['open'],
                    ]);
                    logit('LINKS_CATADD', 'ID #'.$nid);

                    //Beitrag der Kategorie hinzufügen
                    if ($_REQUEST['updateparent']) {
                        printJSUpdateObject($_REQUEST['updateparent'], $this->get_catlist($nid));
                    } else {
                        printJSRedirect('action.php?action=links.catshow');
                    }
                }
            }
        } else {
            $_POST['open'] = 1;

            //Baum
            $catlist = '<option value="root" style="font-weight:bold;">'.$apx->lang->get('ROOT').'</option>';
            $data = $this->cat->getTree(['title']);
            if (count($data)) {
                $catlist .= '<option value=""></option>';
                foreach ($data as $res) {
                    $catlist .= '<option value="'.$res['id'].'"'.iif($_POST['parent'] == $res['id'], ' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;', ($res['level'] - 1)).replace($res['title']).'</option>';
                }
            }

            $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
            $apx->tmpl->assign('TEXT', compatible_hsc($_POST['text']));
            $apx->tmpl->assign('ICON', compatible_hsc($_POST['icon']));
            $apx->tmpl->assign('OPEN', (int) $_POST['open']);
            $apx->tmpl->assign('CATLIST', $catlist);
            $apx->tmpl->assign('ACTION', 'add');
            $apx->tmpl->assign('UPDATEPARENT', (int) $_REQUEST['updateparent']);

            $apx->tmpl->parse('catadd_catedit');
        }
    }

    //***************************** Kategorie bearbeiten *****************************
    public function catedit()
    {
        global $set,$apx,$tmpl,$db,$user;
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            list($links) = $db->first('SELECT count(id) FROM '.PRE."_links WHERE catid='".$_REQUEST['id']."'");

            if (!checkToken()) {
                infoInvalidToken();
            } elseif (!$_POST['id'] || !$_POST['parent'] || !$_POST['title']) {
                infoNotComplete();
            } elseif (!$_POST['open'] && $links) {
                info($apx->lang->get('INFO_CONTAINSLINKS'));
            } else {
                $this->cat->moveNode($_REQUEST['id'], intval($_POST['parent']), [
                    'title' => $_POST['title'],
                    'text' => $_POST['text'],
                    'icon' => $_POST['icon'],
                    'open' => $_POST['open'],
                ]);
                logit('LINKS_CATEDIT', 'ID #'.$_REQUEST['id']);
                printJSRedirect(get_index('links.catshow'));
            }
        } else {
            $res = $this->cat->getNode($_REQUEST['id'], ['title', 'text', 'icon', 'open']);
            $_POST['title'] = $res['title'];
            $_POST['text'] = $res['text'];
            $_POST['icon'] = $res['icon'];
            $_POST['open'] = $res['open'];
            if (!$res['parents']) {
                $_POST['parent'] = 'root';
            } else {
                $_POST['parent'] = array_pop($res['parents']);
            }

            //Baum
            $catlist = '<option value="root" style="font-weight:bold;">'.$apx->lang->get('ROOT').'</option>';
            $data = $this->cat->getTree(['title']);
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
                    $catlist .= '<option value="'.$res['id'].'"'.iif($_POST['parent'] === $res['id'], ' selected="selected"').'>'.str_repeat('&nbsp;&nbsp;', ($res['level'] - 1)).replace($res['title']).'</option>';
                }
            }

            $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
            $apx->tmpl->assign('TEXT', compatible_hsc($_POST['text']));
            $apx->tmpl->assign('ICON', compatible_hsc($_POST['icon']));
            $apx->tmpl->assign('OPEN', (int) $_POST['open']);
            $apx->tmpl->assign('CATLIST', $catlist);
            $apx->tmpl->assign('ID', $_REQUEST['id']);
            $apx->tmpl->assign('ACTION', 'edit');

            $apx->tmpl->parse('catadd_catedit');
        }
    }

    //***************************** Kategorie löschen *****************************
    public function catdel()
    {
        global $set,$db,$apx;
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        list($links) = $db->first('SELECT count(id) FROM '.PRE."_links WHERE catid='".$_REQUEST['id']."'");
        if ($links) {
            die('category still contains links!');
        }

        if (1 == $_POST['send']) {
            if (!checkToken()) {
                printInvalidToken();
            } else {
                $this->cat->deleteNode($_REQUEST['id']);
                logit('LINKS_CATDEL', 'ID #'.$_REQUEST['id']);
                printJSRedirect(get_index('links.catshow'));
            }
        } else {
            list($title) = $db->first('SELECT title FROM '.PRE."_links_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
            $apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', ['TITLE' => compatible_hsc($title)]));
            tmessageOverlay('deltitle', ['ID' => $_REQUEST['id']], '/');
        }
    }

    //***************************** Kategorie leeren + löschen *****************************
    public function catclean()
    {
        global $set,$db,$apx;
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }

        if (1 == $_POST['send']) {
            if ($_POST['delcat']) {
                $nodeInfo = $this->cat->getNode($_REQUEST['id']);
                if ($nodeInfo['children']) {
                    $_POST['delcat'] = 0;
                }
            }

            if (!checkToken()) {
                printInvalidToken();
            } elseif ($_POST['id'] && $_POST['moveto']) {
                $db->query('UPDATE '.PRE."_links SET catid='".intval($_POST['moveto'])."' WHERE catid='".$_REQUEST['id']."'");
                logit('LINKS_CATCLEAN', 'ID #'.$_REQUEST['id']);

                //Kategorie löschen
                if ($_POST['delcat']) {
                    $this->cat->deleteNode($_REQUEST['id']);
                    logit('LINKS_CATDEL', 'ID #'.$_REQUEST['id']);
                }

                printJSRedirect(get_index('links.catshow'));

                return;
            }
        }

        $data = $this->cat->getTree(['title', 'open']);
        if (count($data)) {
            foreach ($data as $res) {
                if ($res['level']) {
                    $space = str_repeat('&nbsp;&nbsp;', ($res['level'] - 1));
                }
                if ($res['id'] != $_REQUEST['id'] && $res['open']) {
                    $catlist .= '<option value="'.$res['id'].'" '.iif($_POST['moveto'] == $res['id'], ' selected="selected"').' style="color:green;">'.$space.replace($res['title']).'</option>';
                } else {
                    $catlist .= '<option value="" disabled="disabled" style="color:grey;">'.$space.replace($res['title']).'</option>';
                }
            }
        }

        list($title, $children) = $db->first('SELECT title,children FROM '.PRE."_links_cat WHERE id='".$_REQUEST['id']."' LIMIT 1");
        $children = dash_unserialize($children);

        $apx->tmpl->assign('ID', $_REQUEST['id']);
        $apx->tmpl->assign('TITLE', compatible_hsc($title));
        $apx->tmpl->assign('DELCAT', (int) $_POST['delcat']);
        $apx->tmpl->assign('DELETEABLE', !$children);
        $apx->tmpl->assign('CATLIST', $catlist);

        tmessageOverlay('catclean');
    }

    //***************************** Kategorie verschieben *****************************
/*function catmove() {
    global $set,$db,$apx;
    $_REQUEST['id']=(int)$_REQUEST['id'];
    if ( !$_REQUEST['id'] ) die('missing ID!');

    if ( !checkToken() ) printInvalidToken();
    else {
        $this->cat->move($_REQUEST['id'],$_REQUEST['direction']);
        header("HTTP/1.1 301 Moved Permanently");
        header('Location: '.get_index('links.catshow'));
    }
}*/
} //END CLASS
