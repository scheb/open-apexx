<?php

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_start.php';  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once BASEDIR.getmodulepath('content').'functions.php';

$apx->module('content');
$_REQUEST['id'] = (int) $_REQUEST['id'];

////////////////////////////////////////////////////////////////////////////////////////////////////////

    //Parse File
    if ($_REQUEST['show']) {
        $template = $_REQUEST['show'];
        $template = str_replace('/', '', $template);
        $template = str_replace('\\', '', $template);
        $template = str_replace('.', '/', $template);
        if (file_exists(BASEDIR.getpath('tmpl_modules_public', ['MODULE' => 'content', 'THEME' => $apx->tmpl->theme]).$template.'.html')) {
            $apx->tmpl->parse($template);
        } else {
            filenotfound();
        }
    }

    //Include File
    elseif ($_REQUEST['inc']) {
        $template = $_REQUEST['inc'];
        $template = str_replace('/', '', $template);
        $template = str_replace('\\', '', $template);
        $filepath = str_replace('.', '/', $template).'.php';
        if (file_exists(BASEDIR.getpath('content').$filepath)) {
            include BASEDIR.getpath('content').$filepath;
        } else {
            filenotfound();
        }
    }

    //Kommentare zeigen
    elseif ($apx->is_module('comments') && $_REQUEST['id'] && $_REQUEST['comments']) {
        $res = $db->first('SELECT title FROM '.PRE."_content AS a WHERE ( id='".$_REQUEST['id']."' AND active='1' ".section_filter().' ) LIMIT 1');

        //Titel
        $tt = explode('->', $res['title']);
        $number = count($tt);
        foreach ($tt as $one) {
            ++$hi;
            if ($number == $hi) {
                headline(trim($one), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
            } else {
                headline(trim($one));
            }
            $last = $one;
        }
        titlebar(strip_tags($last));

        content_showcomments($_REQUEST['id']);
        require 'lib/_end.php';
    }

    //Content aus der Datenbank
    elseif ($_REQUEST['id']) {
        $apx->lang->drop('content');

        //Klicks+1
        $db->query('UPDATE '.PRE."_content SET hits=hits+1 WHERE id='".$_REQUEST['id']."' LIMIT 1");

        $res = $db->first('SELECT a.*,b.username,b.email,b.pub_hidemail,c.username AS lc_username,c.email AS lc_email,c.pub_hidemail AS lc_pub_hidemail FROM '.PRE.'_content AS a LEFT JOIN '.PRE.'_user AS b USING(userid) LEFT JOIN '.PRE."_user AS c ON a.lastchange_userid=c.userid WHERE ( a.id='".$_REQUEST['id']."' ".iif(!$user->is_team_member(), " AND a.active='1' ".section_filter().' ').' ) LIMIT 1');
        if (!$res['id']) {
            filenotfound();
        }

        //Titel
        $headline = [];
        $tt = explode('->', $res['title']);
        $number = count($tt);
        foreach ($tt as $one) {
            ++$hi;
            if ($number == $hi) {
                headline(trim($one), str_replace('&', '&amp;', $_SERVER['REQUEST_URI']));
            } else {
                headline(trim($one));
            }
            $last = $one;
            $headline[] = [
                'TEXT' => trim($one),
            ];
        }
        titlebar(strip_tags($last));

        //Alte Platzhalter für Abwärtskompatiblität
        $apx->tmpl->assign('USERID', $res['userid']);
        $apx->tmpl->assign('USERNAME', replace($res['username']));
        $apx->tmpl->assign('EMAIL', replace(iif(!$res['pub_hidemail'], $res['email'])));
        $apx->tmpl->assign('EMAIL_ENCRYPTED', replace(iif(!$res['pub_hidemail'], cryptMail($res['email']))));

        //Autor
        $apx->tmpl->assign('AUTHOR_USERID', $res['userid']);
        $apx->tmpl->assign('AUTHOR_USERNAME', replace($res['username']));
        $apx->tmpl->assign('AUTHOR_EMAIL', replace(iif(!$res['pub_hidemail'], $res['email'])));
        $apx->tmpl->assign('AUTHOR_EMAIL_ENCRYPTED', replace(iif(!$res['pub_hidemail'], cryptMail($res['email']))));

        //Letzte Änderung
        $apx->tmpl->assign('LASTCHANGE_TIME', $res['lastchange']);
        $apx->tmpl->assign('LASTCHANGE_USERID', $res['lastchange_userid']);
        $apx->tmpl->assign('LASTCHANGE_USERNAME', replace($res['lc_username']));
        $apx->tmpl->assign('LASTCHANGE_EMAIL_ENCRYPTED', replace(iif(!$res['lc_pub_hidemail'], cryptMail($res['email']))));

        //Content
        $content = mediamanager_inline($res['text']);
        if ($apx->is_module('glossar')) {
            $content = glossar_highlight($content);
        }

        $apx->tmpl->assign('ID', $res['id']);
        $apx->tmpl->assign('HEADLINE', $headline);
        $apx->tmpl->assign('TITLE', $last);
        $apx->tmpl->assign('CONTENT', $content);
        $apx->tmpl->assign_static('META_DESCRIPTION', replace($res['meta_description']));
        $apx->tmpl->assign('HITS', number_format($res['hits'], 0, '', '.'));

        //Kategorie
        $apx->tmpl->assign('CATID', $res['catid']);
        $apx->tmpl->assign('CATTITLE', $set['content']['groups'][$res['catid']]);

        //Kommentare
        if ($apx->is_module('comments') && $set['content']['coms'] && $res['allowcoms']) {
            require_once BASEDIR.getmodulepath('comments').'class.comments.php';
            $coms = new comments('content', $res['id']);
            $coms->assign_comments();
        }

        //Bewertung
        if ($apx->is_module('ratings') && $set['content']['ratings'] && $res['allowrating']) {
            require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
            $rate = new ratings('content', $res['id']);
            $rate->assign_ratings();
        }

        $apx->tmpl->parse('content');
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'lib/_end.php';  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
