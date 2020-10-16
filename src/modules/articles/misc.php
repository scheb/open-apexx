<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

require_once BASEDIR.getmodulepath('articles').'functions.php';

//Kommentar-Popup
function misc_articles_comments()
{
    global $set,$db,$apx,$user;
    $_REQUEST['id'] = (int) $_REQUEST['id'];
    if (!$_REQUEST['id']) {
        die('missing ID!');
    }
    $apx->tmpl->loaddesign('blank');
    articles_showcomments($_REQUEST['id']);
}

//Artikel-Feed ausgeben
function misc_articlesfeed()
{
    global $set,$db,$apx;
    $apx->tmpl->loaddesign('blank');
    header('Content-type: application/rss+xml');

    //Verwendete Variablen
    $parse = $apx->tmpl->used_vars('rss', 'articles');

    //Baum holen, wenn nur eine bestimmte Kategorie gezeigt werden soll
    $cattree = articles_tree($_REQUEST['catid']);

    $data = $db->fetch('SELECT a.id,a.type,a.catid,a.title,a.subtitle,a.teaser,a.starttime,a.top,b.username,b.email,b.pub_hidemail FROM '.PRE.'_articles AS a LEFT JOIN '.PRE.'_user AS b USING (userid) WHERE ( '.time().' BETWEEN starttime AND endtime '.iif(count($cattree), 'AND catid IN ('.@implode(',', $cattree).')').' '.section_filter().' ) ORDER BY starttime DESC LIMIT 20');

    //Kategorien auslesen
    $catinfo = articles_catinfo(get_ids($data, 'catid'));

    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            //Wohin soll verlinkt werden?
            if ('normal' == $res['type']) {
                $link2file = 'articles';
            } else {
                $link2file = $res['type'].'s';
            }

            //Link
            $link = mklink(
                $link2file.'.php?id='.$res['id'],
                $link2file.',id'.$res['id'].',0'.urlformat($res['title']).'.html'
            );

            //Text: Teaser oder Artikelseite
            if ($res['teaser'] && $set['articles']['teaser']) {
                $text = $res['teaser'];
            } else {
                list($text) = $db->first('SELECT text FROM '.PRE."_articles_pages WHERE artid='".$res['id']."' ORDER BY ord ASC LIMIT 1");
                $text = $text;
            }

            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['TITLE'] = rss_replace($res['title']);
            $tabledata[$i]['SUBTITLE'] = rss_replace($res['subtitle']);
            $tabledata[$i]['TIME'] = date('r', $res['starttime']); //Kein TIMEDIFF weil Zeitverschiebung mit angegeben!
            $tabledata[$i]['TEXT'] = rss_replace(preg_replace('#{IMAGE\(([0-9]+)\)}#s', '', $text));
            $tabledata[$i]['CATTITLE'] = rss_replace($catinfo[$res['catid']]['title']);
            $tabledata[$i]['LINK'] = HTTP_HOST.$link;
            $tabledata[$i]['USERNAME'] = replace($res['username']);
            $tabledata[$i]['EMAIL'] = replace(iif(!$res['pub_hidemail'], $res['email']));
            $tabledata[$i]['EMAIL_ENCRYPTED'] = replace(iif(!$res['pub_hidemail'], cryptMail($res['email'])));
            $tabledata[$i]['TOP'] = $res['top'];
        }
    }

    $apx->tmpl->assign('WEBSITENAME', $set['main']['websitename']);
    $apx->tmpl->assign('ARTICLE', $tabledata);
    $apx->tmpl->parse('rss', 'articles');
}
