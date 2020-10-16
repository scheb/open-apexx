<?php

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_start.php';  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once BASEDIR.getmodulepath('videos').'functions.php';

$apx->module('videos');
$apx->lang->drop('global');

headline($apx->lang->get('HEADLINE'), mklink('videos.php', 'videos.html'));
titlebar($apx->lang->get('HEADLINE'));
$_REQUEST['catid'] = (int) $_REQUEST['catid'];
$_REQUEST['id'] = (int) $_REQUEST['id'];

////////////////////////////////////////////////////////////////////////////////// DEFEKTER VIDEO

if ($_REQUEST['id'] && $_REQUEST['broken']) {
    $apx->lang->drop('broken');

    if ($_POST['broken']) {
        $res = $db->first('SELECT title FROM '.PRE."_videos WHERE ( id='".$_REQUEST['id']."' ".section_filter().' ) LIMIT 1');
        titlebar($apx->lang->get('HEADLINE').': '.$res['title']);

        $link = mklink(
            'videos.php?id='.$_REQUEST['id'],
            'videos,id'.$_REQUEST['id'].urlformat($res['title']).'.html'
        );

        $db->query('UPDATE '.PRE."_videos SET broken='".time()."' WHERE ( id='".$_REQUEST['id']."' ".section_filter().' ) LIMIT 1');

        //eMail-Benachrichtigung
        if ($set['videos']['mailonbroken']) {
            $input = ['URL' => substr(HTTP, 0, -1).$link];
            sendmail($set['videos']['mailonbroken'], 'BROKEN', $input);
        }

        message($apx->lang->get('MSG_BROKEN'), $link);
        require 'lib/_end.php';
    } else {
        tmessage('broken', ['ID' => $_REQUEST['id']]);
    }
}

////////////////////////////////////////////////////////////////////////////////// NUR KOMMENTARE

if ($_REQUEST['id'] && $_REQUEST['comments']) {
    $res = $db->first('SELECT title FROM '.PRE."_videos WHERE ( id='".$_REQUEST['id']."' ".section_filter().' ) LIMIT 1');
    titlebar($apx->lang->get('HEADLINE').': '.$res['title']);

    videos_showcomments($_REQUEST['id']);
}

///////////////////////////////////////////////////////////////////////////////////////// DETAILS
if ($_REQUEST['id']) {
    $apx->lang->drop('detail');

    //Verwendete Variablen auslesen
    $parse = $apx->tmpl->used_vars('detail');

    //Video-Info
    $res = $db->first('SELECT a.*,b.username,b.email,b.pub_hidemail FROM '.PRE.'_videos AS a LEFT JOIN '.PRE."_user AS b USING(userid) WHERE ( a.id='".$_REQUEST['id']."' AND a.status='finished' ".iif(!$user->is_team_member(), "AND ( '".time()."' BETWEEN a.starttime AND a.endtime )").' '.section_filter().' ) LIMIT 1');
    if (!$res['id']) {
        filenotfound();
    }

    //Altersabfrage
    if ($res['restricted']) {
        checkage();
    }

    //Counter
    $db->query('UPDATE '.PRE.'_videos SET hits=hits+1 WHERE ( '.time()." BETWEEN starttime AND endtime AND id='".$_REQUEST['id']."' ".section_filter().' )');

    //Headline
    titlebar($apx->lang->get('HEADLINE').': '.$res['title']);

    //Kategorie-Info
    if (in_array('CATTITLE', $parse) || in_array('CATTEXT', $parse) || in_array('CATICON', $parse) || in_array('CATLINK', $parse) || in_array('CATCOUNT', $parse)) {
        //Tree-Manager
        require_once BASEDIR.'lib/class.recursivetree.php';
        $tree = new RecursiveTree(PRE.'_videos_cat', 'id');

        $catinfo = $tree->getNode($res['catid'], ['*']);
    }

    //Videos in der Kategorien
    $catcount = 0;
    if (in_array('CATCOUNT', $parse)) {
        $wholetree = array_merge([$res['catid']], $catinfo['children']);
        list($catcount) = $db->first('SELECT count(id) FROM '.PRE.'_videos WHERE ( catid IN ('.implode(',', $wholetree).") AND ( '".time()."' BETWEEN starttime AND endtime ) ".section_filter().' )');
    }

    //Link
    $link = mklink(
        'videos.php?id='.$res['id'],
        'videos,id'.$res['id'].urlformat($res['title']).'.html'
    );

    //Teaserbild
    if (in_array('PICTURE', $parse) || in_array('PICTURE_POPUP', $parse) || in_array('PICTURE_POPUPPATH', $parse)) {
        list($picture, $picture_popup, $picture_popuppath) = videos_teaserpic($res['teaserpic']);
    }

    //Dateigröße auslesen
    $thefsize = videos_filesize($res);

    //Download-Link
    if ((!$set['videos']['regonly'] && !$res['regonly']) || $user->info['userid']) {
        $sechash = md5($_SERVER['HTTP_HOST'].$res['file'].date('Y/m/d', time() - TIMEDIFF));
        $dllink = 'misc.php?action=videofile&amp;id='.$res['id'].'&amp;sechash='.$sechash.iif($apx->section_id(), '&amp;sec='.$apx->section_id());
    } else {
        $dllink = mklink('user.php', 'user.html');
    }

    //Neu?
    if (($res['addtime'] + ($set['videos']['new'] * 24 * 3600)) >= time()) {
        $new = 1;
    } else {
        $new = 0;
    }

    //Username + eMail
    if ($res['userid']) {
        $uploader = $res['username'];
        $uploader_email = iif(!$res['pub_hidemail'], $res['email']);
    } else {
        $uploader = $res['send_username'];
        $uploader_email = $res['send_email'];
    }

    //Broken
    $blink = mklink(
        'videos.php?id='.$res['id'].'&amp;broken=1',
        'videos,id'.$res['id'].urlformat($res['title']).'.html?broken=1'
    );

    //Text
    $text = mediamanager_inline($res['text']);
    if ($apx->is_module('glossar')) {
        $text = glossar_highlight($text);
    }

    //Tags
    if (in_array('TAG', $parse) || in_array('TAG_IDS', $parse) || in_array('KEYWORDS', $parse)) {
        list($tagdata, $tagids, $keywords) = videos_tags($res['id']);
    }

    //Embeded?
    if ('apexx' != $res['source'] && 'external' != $res['source']) {
        $embedcode = videos_embedcode($res['source'], $res['flvfile']);
        $file = '';
        $flvfile = '';
        $dllink = '';
    }

    //Extern
    elseif ('external' == $res['source']) {
        $embedcode = '';
        $flvfile = $res['flvfile'];
        if ($res['file']) {
            $file = $res['file'];
        } else {
            $dllink = '';
        }
    }

    //Lokal
    else {
        $embedcode = '';
        $flvfile = HTTPDIR.getpath('uploads').$res['flvfile'];
        if ($res['file']) {
            $file = HTTP_HOST.HTTPDIR.getpath('uploads').$res['file'];
        } else {
            $dllink = '';
        }
    }

    $apx->tmpl->assign('ID', $res['id']);
    $apx->tmpl->assign('SECID', $res['secid']);
    $apx->tmpl->assign('USERID', $res['userid']);
    $apx->tmpl->assign('USERNAME', replace($uploader));
    $apx->tmpl->assign('EMAIL', replace($uploader_email));
    $apx->tmpl->assign('EMAIL_ENCRYPTED', replace(cryptMail($uploader_email)));
    $apx->tmpl->assign('TITLE', $res['title']);
    $apx->tmpl->assign('TEXT', $text);
    $apx->tmpl->assign_static('META_DESCRIPTION', replace($res['meta_description']));
    $apx->tmpl->assign('LINK', $link);
    $apx->tmpl->assign('PICTURE', $picture);
    $apx->tmpl->assign('PICTURE_POPUP', $picture_popup);
    $apx->tmpl->assign('PICTURE_POPUPPATH', $picture_popuppath);
    $apx->tmpl->assign('SIZE', videos_getsize($thefsize));
    $apx->tmpl->assign('HITS', number_format($res['hits'] + 1, 0, '', '.'));
    $apx->tmpl->assign('TIME', $res['starttime']);
    $apx->tmpl->assign('SCREENSHOT', videos_screenshots($res['id']));
    $apx->tmpl->assign('SOURCE', 'external' == $res['source'] ? 'apexx' : $res['source']);
    $apx->tmpl->assign('VIDEOFILE', $flvfile);
    $apx->tmpl->assign('EMBEDCODE', $embedcode);
    $apx->tmpl->assign('TOP', $res['top']);
    $apx->tmpl->assign('RESTRICTED', $res['restricted']);
    $apx->tmpl->assign('NEW', $new);

    //Tags
    $apx->tmpl->assign('TAG_IDS', $tagids);
    $apx->tmpl->assign('TAG', $tagdata);
    $apx->tmpl->assign('KEYWORDS', $keywords);

    //Produkt
    $apx->tmpl->assign('PRODUCT_ID', $res['prodid']);

    //Download
    $apx->tmpl->assign('DOWNLOAD', $dllink);
    $apx->tmpl->assign('DOWNLOADFILE', $file);
    $apx->tmpl->assign('DOWNLOADS', number_format($res['downloads'], 0, '', '.'));
    $apx->tmpl->assign('BROKEN', $blink);
    $apx->tmpl->assign('REGONLY', ($res['regonly'] || $set['videos']['regonly']));

    //Video-Zeit
    $apx->tmpl->assign('TIME_MODEM', videos_gettime($thefsize, 56));
    $apx->tmpl->assign('TIME_ISDN', videos_gettime($thefsize, 64));
    $apx->tmpl->assign('TIME_ISDN2', videos_gettime($thefsize, 128));
    $apx->tmpl->assign('TIME_DSL1000', videos_gettime($thefsize, 1024));
    $apx->tmpl->assign('TIME_DSL2000', videos_gettime($thefsize, 1024 * 2));
    $apx->tmpl->assign('TIME_DSL6000', videos_gettime($thefsize, 1024 * 6));
    $apx->tmpl->assign('TIME_DSL10000', videos_gettime($thefsize, 1024 * 10));
    $apx->tmpl->assign('TIME_DSL12000', videos_gettime($thefsize, 1024 * 12));
    $apx->tmpl->assign('TIME_DSL16000', videos_gettime($thefsize, 1024 * 16));

    //Video-Limit
    if (videos_limit_is_reached($res['id'], $res['limit'])) {
        $apx->tmpl->assign('LIMIT', 1);
    }

    //Kategorie
    $apx->tmpl->assign('CATID', $res['catid']);
    $apx->tmpl->assign('CATTITLE', $catinfo['title']);
    $apx->tmpl->assign('CATTEXT', $catinfo['text']);
    $apx->tmpl->assign('CATICON', $catinfo['icon']);
    $apx->tmpl->assign('CATCOUNT', $catcount);
    $apx->tmpl->assign('CATLINK', mklink(
        'videos.php?catid='.$catinfo['catid'],
        'videos,'.$catinfo['catid'].',1'.urlformat($catinfo['title']).'.html'
    ));

    //Pfad
    if (in_array('PATH', $parse)) {
        $apx->tmpl->assign('PATH', videos_path($res['catid']));
    }

    //Kommentare
    if ($apx->is_module('comments') && $set['videos']['coms'] && $res['allowcoms']) {
        require_once BASEDIR.getmodulepath('comments').'class.comments.php';
        $coms = new comments('videos', $res['id']);
        $coms->assign_comments($parse);
    }

    //Bewertungen
    if ($apx->is_module('ratings') && $set['videos']['ratings'] && $res['allowrating']) {
        require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
        $rate = new ratings('videos', $res['id']);
        $rate->assign_ratings($parse);
    }

    $apx->tmpl->parse('detail');
    require 'lib/_end.php';
}

//////////////////////////////////////////////////////////////////////////////////////////////////////// SUCHE

if ('search' == $_REQUEST['action']) {
    $apx->lang->drop('list');
    $apx->lang->drop('search');

    //ERGEBNIS ANZEIGEN
    if ($_REQUEST['searchid']) {
        titlebar($apx->lang->get('HEADLINE_SEARCH'));

        //Suchergebnis auslesen
        $resultIds = '';
        list($resultIds) = getSearchResult('videos', $_REQUEST['searchid']);

        //Keine Ergebnisse
        if (!$resultIds) {
            message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //SUCHE AUSFÜHREN
        $parse = $apx->tmpl->used_vars('search_result');

        //Seitenzahlen generieren
        list($count) = $db->first('SELECT count(id) FROM '.PRE."_videos AS a WHERE a.status='finished' AND '".time()."' BETWEEN starttime AND endtime AND id IN (".implode(', ', $resultIds).') '.section_filter());
        pages(
            mklink(
                'videos.php?action=search&amp;searchid='.$_REQUEST['searchid'],
                'videos.html?action=search&amp;searchid='.$_REQUEST['searchid']
            ),
            $count,
            $set['videos']['searchepp']
        );

        //Keine Ergebnisse
        if (!$count) {
            message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //Sortierung
        if (2 == $set['videos']['sortby']) {
            $orderby = ' ORDER BY starttime DESC ';
        } else {
            $orderby = ' ORDER BY title ASC ';
        }
        $data = $db->fetch('SELECT *,b.username,b.email,b.pub_hidemail FROM '.PRE.'_videos AS a LEFT JOIN '.PRE."_user AS b USING(userid) WHERE a.status='finished' AND '".time()."' BETWEEN starttime AND endtime AND id IN (".implode(', ', $resultIds).') '.section_filter().' '.$orderby.getlimit($set['videos']['searchepp']));
        $catids = get_ids($data, 'catid');

        //Kategorien auslesen, falls notwendig
        $catinfo = [];
        if (count($catids) && in_template(['VIDEO.CATTITLE', 'VIDEO.CATTEXT', 'VIDEO.CATICON'], $parse)) {
            $catinfo = videos_catinfo($catids);
        }

        if (count($data)) {
            foreach ($data as $res) {
                ++$i;

                //Link
                $link = mklink(
                    'videos.php?id='.$res['id'],
                    'videos,id'.$res['id'].urlformat($res['title']).'.html'
                );

                //Teaserbild
                if (in_array('VIDEO.PICTURE', $parse) || in_array('VIDEO.PICTURE_POPUP', $parse) || in_array('VIDEO.PICTURE_POPUPPATH', $parse)) {
                    list($picture, $picture_popup, $picture_popuppath) = videos_teaserpic($res['teaserpic']);
                }

                //Dateigröße auslesen
                if (in_array('VIDEO.SIZE', $parse)) {
                    $thefsize = videos_filesize($res);
                }

                //Download-Link
                if ((!$set['videos']['regonly'] && !$res['regonly']) || $user->info['userid']) {
                    $sechash = md5($_SERVER['HTTP_HOST'].$res['file'].date('Y/m/d', time() - TIMEDIFF));
                    $dllink = 'misc.php?action=videofile&amp;id='.$res['id'].'&amp;sechash='.$sechash.iif($apx->section_id(), '&amp;sec='.$apx->section_id());
                } else {
                    $dllink = mklink('user.php', 'user.html');
                }

                //Bilder
                if (in_array('VIDEO.SCREENSHOT', $parse)) {
                    $picdata = videos_screenshots($res['id']);
                }

                //Neu?
                if (($res['addtime'] + ($set['videos']['new'] * 24 * 3600)) >= time()) {
                    $new = 1;
                } else {
                    $new = 0;
                }

                //Username + eMail
                if ($res['userid']) {
                    $uploader = $res['username'];
                    $uploader_email = iif(!$res['pub_hidemail'], $res['email']);
                } else {
                    $uploader = $res['send_username'];
                    $uploader_email = $res['send_email'];
                }

                //Text
                $text = '';
                if (in_array('VIDEO.TEXT', $parse)) {
                    $text = mediamanager_inline($res['text']);
                    if ($apx->is_module('glossar')) {
                        $text = glossar_highlight($text);
                    }
                }

                //Tags
                if (in_array('VIDEO.TAG', $parse) || in_array('VIDEO.TAG_IDS', $parse) || in_array('VIDEO.KEYWORDS', $parse)) {
                    list($tagdata, $tagids, $keywords) = videos_tags($res['id']);
                }

                //Embeded?
                if ('apexx' != $res['source'] && 'external' != $res['source']) {
                    $embedcode = videos_embedcode($res['source'], $res['flvfile']);
                    $file = '';
                    $flvfile = '';
                    $dllink = '';
                }

                //Extern
                elseif ('external' == $res['source']) {
                    $embedcode = '';
                    $flvfile = $res['flvfile'];
                    if ($res['file']) {
                        $file = $res['file'];
                    } else {
                        $dllink = '';
                    }
                }

                //Lokal
                else {
                    $embedcode = '';
                    $flvfile = HTTPDIR.getpath('uploads').$res['flvfile'];
                    if ($res['file']) {
                        $file = HTTP_HOST.HTTPDIR.getpath('uploads').$res['file'];
                    } else {
                        $dllink = '';
                    }
                }

                $tabledata[$i]['ID'] = $res['id'];
                $tabledata[$i]['SECID'] = $res['secid'];
                $tabledata[$i]['USERID'] = $res['userid'];
                $tabledata[$i]['USERNAME'] = replace($uploader);
                $tabledata[$i]['EMAIL'] = replace($uploader_email);
                $tabledata[$i]['EMAIL_ENCRYPTED'] = replace(cryptMail($uploader_email));
                $tabledata[$i]['TITLE'] = $res['title'];
                $tabledata[$i]['TEXT'] = $text;
                $tabledata[$i]['LINK'] = $link;
                $tabledata[$i]['PICTURE'] = $picture;
                $tabledata[$i]['PICTURE_POPUP'] = $picture_popup;
                $tabledata[$i]['PICTURE_POPUPPATH'] = $picture_popuppath;
                $tabledata[$i]['SIZE'] = videos_getsize($thefsize);
                $tabledata[$i]['HITS'] = number_format($res['hits'], 0, '', '.');
                $tabledata[$i]['TIME'] = $res['starttime'];
                $tabledata[$i]['SCREENSHOT'] = $picdata;
                $tabledata[$i]['SOURCE'] = 'external' == $res['source'] ? 'apexx' : $res['source'];
                $tabledata[$i]['VIDEOFILE'] = $flvfile;
                $tabledata[$i]['EMBEDCODE'] = $embedcode;
                $tabledata[$i]['LOCAL'] = 'apexx' == $res['source'];
                $tabledata[$i]['TOP'] = $res['top'];
                $tabledata[$i]['RESTRICTED'] = $res['restricted'];
                $tabledata[$i]['NEW'] = $new;

                $tabledata[$i]['DOWNLOADLINK'] = $dllink;
                $tabledata[$i]['DOWNLOADFILE'] = $file;
                $tabledata[$i]['DOWNLOADS'] = number_format($res['downloads'], 0, '', '.');

                //Tags
                $tabledata[$i]['TAG'] = $tagdata;
                $tabledata[$i]['TAG_IDS'] = $tagids;
                $tabledata[$i]['KEYWORDS'] = $keywords;

                //Kategorie
                $tabledata[$i]['CATID'] = $res['catid'];
                $tabledata[$i]['CATTITLE'] = $catinfo[$res['catid']]['title'];
                $tabledata[$i]['CATTEXT'] = $catinfo[$res['catid']]['text'];
                $tabledata[$i]['CATICON'] = $catinfo[$res['catid']]['icon'];

                //Produkt
                $tabledata[$i]['PRODUCT_ID'] = $res['prodid'];

                //Kommentare
                if ($apx->is_module('comments') && $set['videos']['coms'] && $res['allowcoms']) {
                    require_once BASEDIR.getmodulepath('comments').'class.comments.php';
                    if (!isset($coms)) {
                        $coms = new comments('videos', $res['id']);
                    } else {
                        $coms->mid = $res['id'];
                    }

                    $link = mklink(
                        'videos.php?id='.$res['id'],
                        'videos,id'.$res['id'].urlformat($res['title']).'.html'
                    );

                    $tabledata[$i]['COMMENT_COUNT'] = $coms->count();
                    $tabledata[$i]['COMMENT_LINK'] = $coms->link($link);
                    $tabledata[$i]['DISPLAY_COMMENTS'] = 1;
                    if (in_template(['VIDEO.COMMENT_LAST_USERID', 'VIDEO.COMMENT_LAST_NAME', 'VIDEO.COMMENT_LAST_TIME'], $parse)) {
                        $tabledata[$i]['COMMENT_LAST_USERID'] = $coms->last_userid();
                        $tabledata[$i]['COMMENT_LAST_NAME'] = $coms->last_name();
                        $tabledata[$i]['COMMENT_LAST_TIME'] = $coms->last_time();
                    }
                }

                //Bewertungen
                if ($apx->is_module('ratings') && $set['videos']['ratings'] && $res['allowrating']) {
                    require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
                    if (!isset($rate)) {
                        $rate = new ratings('videos', $res['id']);
                    } else {
                        $rate->mid = $res['id'];
                    }

                    $tabledata[$i]['RATING'] = $rate->display();
                    $tabledata[$i]['RATING_VOTES'] = $rate->count();
                    $tabledata[$i]['DISPLAY_RATING'] = 1;
                }
            }
        }

        $apx->tmpl->assign('VIDEO', $tabledata);
        $apx->tmpl->parse('search_result');
    }

    //SUCHE DURCHFÜHREN
    else {
        $where = '';

        //Suchbegriffe
        if ($_REQUEST['item']) {
            $items = [];
            $it = explode(' ', preg_replace('#[ ]{2,}#', ' ', trim($_REQUEST['item'])));
            $tagmatches = videos_match_tags($it);
            foreach ($it as $item) {
                if (trim($item)) {
                    $string = preg_replace('#[\s_-]+#', '[^0-9a-zA-Z]*', $item);
                    if (preg_match('#^[0-9a-zA-Z]+$#', $string)) {
                        $items[] = " LIKE '%".addslashes_like($string)."%' ";
                    } else {
                        $items[] = " REGEXP '".addslashes($string)."' ";
                    }
                }
            }

            if ('or' == $_REQUEST['conn']) {
                $conn = ' OR ';
            } else {
                $conn = ' AND ';
            }

            $search = [];
            foreach ($items as $regexp) {
                $tagmatch = array_shift($tagmatches);
                $search[] = ' ( '.iif($tagmatch, ' id IN ('.implode(',', $tagmatch).') OR ').' title '.$regexp.' OR text '.$regexp.' ) ';
            }
            $where .= iif($where, ' AND ').' ( '.implode($conn, $search).' ) ';
        }

        //Nach Tag suchen
        if ($_REQUEST['tag']) {
            $tagid = getTagId($_REQUEST['tag']);
            if ($tagid) {
                $data = $db->fetch('SELECT id FROM '.PRE."_videos_tags WHERE tagid='".$tagid."'");
                $ids = get_ids($data, 'id');
                if ($ids) {
                    $where .= iif($where, ' AND ').' id IN ('.implode(',', $ids).') ';
                } else {
                    $where .= iif($where, ' AND ').' 0 ';
                }
            } else {
                $where .= iif($where, ' AND ').' 0 ';
            }
        }

        //Kategorie
        if ($_REQUEST['catid']) {
            $cattree = videos_tree($_REQUEST['catid']);
            if (count($cattree)) {
                $where .= iif($where, ' AND ').'catid IN ('.@implode(',', $cattree).')';
            }
        }

        //Zeitperiode
        if ($_REQUEST['start_day'] && $_REQUEST['start_month'] && $_REQUEST['start_year'] && $_REQUEST['end_day'] && $_REQUEST['end_month'] && $_REQUEST['end_year']) {
            $where .= iif($where, ' AND ')."starttime BETWEEN '".(mktime(0, 0, 0, intval($_REQUEST['start_month']), intval($_REQUEST['start_day']), intval($_REQUEST['start_year'])) + TIMEDIFF)."' AND '".((mktime(0, 0, 0, intval($_REQUEST['end_month']), intval($_REQUEST['end_day']) + 1, intval($_REQUEST['end_year'])) - 1) + TIMEDIFF)."'";
        }

        //Keine Suchkriterien vorhanden
        if (!$where) {
            message($apx->lang->get('CORE_BACK'), 'javascript:history.back();');
            require 'lib/_end.php';
        }

        //SUCHE AUSFÜHREN
        else {
            $data = $db->fetch('SELECT id FROM '.PRE.'_videos WHERE '.$where);
            $resultIds = get_ids($data, 'id');

            //Keine Ergebnisse
            if (!$resultIds) {
                message($apx->lang->get('MSG_NORESULT'), 'javascript:history.back();');
                require 'lib/_end.php';
            }

            $searchid = saveSearchResult('videos', $resultIds);
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.str_replace('&amp;', '&', mklink(
                'videos.php?action=search&searchid='.$searchid,
                'videos.html?action=search&searchid='.$searchid
            )));
        }
    }
    require 'lib/_end.php';
}

///////////////////////////////////////////////////////////////////////////////////////// KATEGORIEN DURCHSUCHEN

//Sprachpaket
$apx->lang->drop('list');
$apx->lang->drop('search');

//Verwendete Variablen auslesen
$parse = $apx->tmpl->used_vars('index');

//Kategorie auslesen
$catinfo = [];
if ($_REQUEST['catid']) {
    $catinfo = $db->first('SELECT id, title, text, icon, open FROM '.PRE."_videos_cat WHERE id='".$_REQUEST['catid']."' LIMIT 1");
}

//Tree-Manager
require_once BASEDIR.'lib/class.recursivetree.php';
$tree = new RecursiveTree(PRE.'_videos_cat', 'id');

//KATEGORIEN
if ($_REQUEST['catid']) {
    $wholetree = [$_REQUEST['catid']];
    $data = $tree->getLevel(['title', 'text', 'icon', 'open'], $_REQUEST['catid']);
} else {
    $wholetree = [];
    $data = $tree->getLevel(['title', 'text', 'icon', 'open']);
}

if (count($data)) {
    //Kategorien auflisten
    $catdata = [];
    foreach ($data as $res) {
        ++$i;

        //Link
        $link = mklink(
            'videos.php?catid='.$res['id'],
            'videos,'.$res['id'].',1'.urlformat($res['title']).'.html'
        );

        //Video-Zahl
        $contentIds = $res['children'];
        $contentIds[] = $res['id'];
        $wholetree = array_merge($wholetree, $contentIds);
        list($count) = $db->first('SELECT count(id) FROM '.PRE.'_videos WHERE ( catid IN ('.implode(',', $contentIds).") AND ( status='finished' AND '".time()."' BETWEEN starttime AND endtime ) ".section_filter().' )');
        $catdata[$i]['ID'] = $res['id'];
        $catdata[$i]['TITLE'] = $res['title'];
        $catdata[$i]['TEXT'] = $res['text'];
        $catdata[$i]['ICON'] = $res['icon'];
        $catdata[$i]['LINK'] = $link;
        $catdata[$i]['COUNT'] = $count;
    }
}

$apx->tmpl->assign('CATEGORY', $catdata);
$apx->tmpl->assign('CATID', $catinfo['id']);
$apx->tmpl->assign('CATTITLE', $catinfo['title']);
$apx->tmpl->assign('CATTEXT', $catinfo['text']);
$apx->tmpl->assign('CATICON', $catinfo['icon']);
$apx->tmpl->assign('CATLINK', mklink(
    'videos.php?catid='.$catinfo['catid'],
    'videos,'.$catinfo['catid'].',1'.urlformat($catinfo['title']).'.html'
));

//Pfad
if (in_array('PATH', $parse)) {
    $apx->tmpl->assign('PATH', videos_path($_REQUEST['catid']));
}

//Suchbox
$catdata = [];
if (in_array('SEARCH_CATEGORY', $parse)) {
    $data = $tree->getTree(['title']);
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;
            $catdata[$i]['ID'] = $res['id'];
            $catdata[$i]['TITLE'] = $res['title'];
            $catdata[$i]['LEVEL'] = $res['level'];
        }
    }
}

$postto = mklink('videos.php', 'videos.html');
$apx->tmpl->assign('SEARCH_POSTTO', $postto);
$apx->tmpl->assign('SEARCH_CATEGORY', $catdata);

///////////////////////////////////////////////////
//Parings ausführen, wenn keine Kategorie gewählt//
///////////////////////////////////////////////////
if (!$_REQUEST['catid'] && $set['videos']['catonly']) {
    $apx->tmpl->parse('index');
    require 'lib/_end.php';
}

//Filter bestimmen
if ($set['videos']['catonly']) {
    $filter = "catid='".$_REQUEST['catid']."'";
} else {
    $filter = 'catid IN ('.implode(',', $wholetree).')';
}

//Seitenzahlen
list($count) = $db->first('SELECT count(id) FROM '.PRE.'_videos WHERE ( '.$filter." AND ( status='finished' AND '".time()."' BETWEEN starttime AND endtime ) ".section_filter().' )');
pages(
    mklink(
        'videos.php?catid='.$_REQUEST['catid'].iif($_REQUEST['sortby'], '&amp;sortby='.$_REQUEST['sortby']),
        'videos,'.$_REQUEST['catid'].',{P}.html'.iif($_REQUEST['sortby'], '?sortby='.$_REQUEST['sortby'])
    ),
    $count,
    $set['videos']['epp']
);
$apx->tmpl->assign('CATCOUNT', $count);

//Orderby
if (2 == $set['videos']['sortby']) {
    $orderdef[0] = 'date';
} else {
    $orderdef[0] = 'title';
}
$orderdef['title'] = ['a.title', 'ASC'];
$orderdef['date'] = ['a.starttime', 'DESC'];
$orderdef['hits'] = ['a.hits', 'DESC'];
$orderdef['user'] = ['b.username', 'ASC'];
if ($apx->is_module('ratings')) {
    $orderdef['rating'] = ['c.rating', 'DESC'];
}

//Videos Select
if ($apx->is_module('ratings') && ('rating.ASC' == $_REQUEST['sortby'] || 'rating.DESC' == $_REQUEST['sortby'])) {
    $data = $db->fetch('SELECT a.*,b.username,b.email,b.pub_hidemail,avg(c.rating) AS rating FROM '.PRE.'_videos AS a LEFT JOIN '.PRE.'_user AS b USING(userid) LEFT JOIN '.PRE."_ratings AS c ON ( c.module='videos' AND a.id=c.mid ) WHERE ( a.status='finished' AND ( '".time()."' BETWEEN starttime AND endtime ) AND ".$filter.' '.section_filter().' ) GROUP BY a.id '.getorder($orderdef).getlimit($set['videos']['epp']));
} else {
    $data = $db->fetch('SELECT *,b.username,b.email,b.pub_hidemail FROM '.PRE.'_videos AS a LEFT JOIN '.PRE."_user AS b USING(userid) WHERE ( a.status='finished' AND ( '".time()."' BETWEEN starttime AND endtime ) AND ".$filter.' '.section_filter().' ) '.getorder($orderdef).getlimit($set['videos']['epp']));
}
$catids = get_ids($data, 'catid');

//Kategorien auslesen, falls notwendig
$catinfo = [];
if (count($catids) && in_template(['VIDEO.CATTITLE', 'VIDEO.CATTEXT', 'VIDEO.CATICON'], $parse)) {
    $catinfo = videos_catinfo($catids);
}

if (count($data)) {
    foreach ($data as $res) {
        ++$i;

        //Link
        $link = mklink(
            'videos.php?id='.$res['id'],
            'videos,id'.$res['id'].urlformat($res['title']).'.html'
        );

        //Teaserbild
        if (in_array('VIDEO.PICTURE', $parse) || in_array('VIDEO.PICTURE_POPUP', $parse) || in_array('VIDEO.PICTURE_POPUPPATH', $parse)) {
            list($picture, $picture_popup, $picture_popuppath) = videos_teaserpic($res['teaserpic']);
        }

        //Dateigröße auslesen
        if (in_array('VIDEO.SIZE', $parse)) {
            $thefsize = videos_filesize($res);
        }

        //Download-Link
        if ((!$set['videos']['regonly'] && !$res['regonly']) || $user->info['userid']) {
            $sechash = md5($_SERVER['HTTP_HOST'].$res['file'].date('Y/m/d', time() - TIMEDIFF));
            $dllink = 'misc.php?action=videofile&amp;id='.$res['id'].'&amp;sechash='.$sechash.iif($apx->section_id(), '&amp;sec='.$apx->section_id());
        } else {
            $dllink = mklink('user.php', 'user.html');
        }

        //Bilder
        if (in_array('VIDEO.SCREENSHOT', $parse)) {
            $picdata = videos_screenshots($res['id']);
        }

        //Neu?
        if (($res['addtime'] + ($set['videos']['new'] * 24 * 3600)) >= time()) {
            $new = 1;
        } else {
            $new = 0;
        }

        //Username + eMail
        if ($res['userid']) {
            $uploader = $res['username'];
            $uploader_email = iif(!$res['pub_hidemail'], $res['email']);
        } else {
            $uploader = $res['send_username'];
            $uploader_email = $res['send_email'];
        }

        //Text
        $text = '';
        if (in_array('VIDEO.TEXT', $parse)) {
            $text = mediamanager_inline($res['text']);
            if ($apx->is_module('glossar')) {
                $text = glossar_highlight($text);
            }
        }

        //Tags
        if (in_array('VIDEO.TAG', $parse) || in_array('VIDEO.TAG_IDS', $parse) || in_array('VIDEO.KEYWORDS', $parse)) {
            list($tagdata, $tagids, $keywords) = videos_tags($res['id']);
        }

        //Embeded?
        if ('apexx' != $res['source'] && 'external' != $res['source']) {
            $embedcode = videos_embedcode($res['source'], $res['flvfile']);
            $file = '';
            $flvfile = '';
            $dllink = '';
        }

        //Extern
        elseif ('external' == $res['source']) {
            $embedcode = '';
            $flvfile = $res['flvfile'];
            if ($res['file']) {
                $file = $res['file'];
            } else {
                $dllink = '';
            }
        }

        //Lokal
        else {
            $embedcode = '';
            $flvfile = HTTPDIR.getpath('uploads').$res['flvfile'];
            if ($res['file']) {
                $file = HTTP_HOST.HTTPDIR.getpath('uploads').$res['file'];
            } else {
                $dllink = '';
            }
        }

        $tabledata[$i]['ID'] = $res['id'];
        $tabledata[$i]['SECID'] = $res['secid'];
        $tabledata[$i]['USERID'] = $res['userid'];
        $tabledata[$i]['USERNAME'] = replace($uploader);
        $tabledata[$i]['EMAIL'] = replace($uploader_email);
        $tabledata[$i]['EMAIL_ENCRYPTED'] = replace(cryptMail($uploader_email));
        $tabledata[$i]['TITLE'] = $res['title'];
        $tabledata[$i]['TEXT'] = $text;
        $tabledata[$i]['LINK'] = $link;
        $tabledata[$i]['PICTURE'] = $picture;
        $tabledata[$i]['PICTURE_POPUP'] = $picture_popup;
        $tabledata[$i]['PICTURE_POPUPPATH'] = $picture_popuppath;
        $tabledata[$i]['SIZE'] = videos_getsize($thefsize);
        $tabledata[$i]['HITS'] = number_format($res['hits'], 0, '', '.');
        $tabledata[$i]['TIME'] = $res['starttime'];
        $tabledata[$i]['SCREENSHOT'] = $picdata;
        $tabledata[$i]['SOURCE'] = 'external' == $res['source'] ? 'apexx' : $res['source'];
        $tabledata[$i]['VIDEOFILE'] = $flvfile;
        $tabledata[$i]['EMBEDCODE'] = $embedcode;
        $tabledata[$i]['LOCAL'] = 'apexx' == $res['source'];
        $tabledata[$i]['TOP'] = $res['top'];
        $tabledata[$i]['RESTRICTED'] = $res['restricted'];
        $tabledata[$i]['NEW'] = $new;

        $tabledata[$i]['DOWNLOADLINK'] = $dllink;
        $tabledata[$i]['DOWNLOADFILE'] = $file;
        $tabledata[$i]['DOWNLOADS'] = number_format($res['downloads'], 0, '', '.');

        //Tags
        $tabledata[$i]['TAG'] = $tagdata;
        $tabledata[$i]['TAG_IDS'] = $tagids;
        $tabledata[$i]['KEYWORDS'] = $keywords;

        //Kategorie
        $tabledata[$i]['CATID'] = $res['catid'];
        $tabledata[$i]['CATTITLE'] = $catinfo[$res['catid']]['title'];
        $tabledata[$i]['CATTEXT'] = $catinfo[$res['catid']]['text'];
        $tabledata[$i]['CATICON'] = $catinfo[$res['catid']]['icon'];

        //Produkt
        $tabledata[$i]['PRODUCT_ID'] = $res['prodid'];

        //Kommentare
        if ($apx->is_module('comments') && $set['videos']['coms'] && $res['allowcoms']) {
            require_once BASEDIR.getmodulepath('comments').'class.comments.php';
            if (!isset($coms)) {
                $coms = new comments('videos', $res['id']);
            } else {
                $coms->mid = $res['id'];
            }

            $link = mklink(
                'videos.php?id='.$res['id'],
                'videos,id'.$res['id'].urlformat($res['title']).'.html'
            );

            $tabledata[$i]['COMMENT_COUNT'] = $coms->count();
            $tabledata[$i]['COMMENT_LINK'] = $coms->link($link);
            $tabledata[$i]['DISPLAY_COMMENTS'] = 1;
            if (in_template(['VIDEO.COMMENT_LAST_USERID', 'VIDEO.COMMENT_LAST_NAME', 'VIDEO.COMMENT_LAST_TIME'], $parse)) {
                $tabledata[$i]['COMMENT_LAST_USERID'] = $coms->last_userid();
                $tabledata[$i]['COMMENT_LAST_NAME'] = $coms->last_name();
                $tabledata[$i]['COMMENT_LAST_TIME'] = $coms->last_time();
            }
        }

        //Bewertungen
        if ($apx->is_module('ratings') && $set['videos']['ratings'] && $res['allowrating']) {
            require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
            if (!isset($rate)) {
                $rate = new ratings('videos', $res['id']);
            } else {
                $rate->mid = $res['id'];
            }

            $tabledata[$i]['RATING'] = $rate->display();
            $tabledata[$i]['RATING_VOTES'] = $rate->count();
            $tabledata[$i]['DISPLAY_RATING'] = 1;
        }
    }
}

//Sortby
ordervars(
    $orderdef,
    mklink(
        'videos.php?catid='.$_REQUEST['catid'],
        'videos,'.$_REQUEST['catid'].',1.html'
    )
);

$apx->tmpl->assign('VIDEO', $tabledata);
$apx->tmpl->parse('index');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
