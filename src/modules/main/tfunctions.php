<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Datum generieren
function main_mkdate($pattern = 'd.m.Y - H:i:s', $time = false)
{
    global $apx,$set;
    static $yesterday,$today,$tomorrow;

    if (false === $time) {
        $time = time();
    }
    $time = (int) $time;

    //Timestamps
    if (!isset($yesterday)) {
        $yesterday = date('d/m/Y', (time() - 24 * 3600 - TIMEDIFF));
    }
    if (!isset($today)) {
        $today = date('d/m/Y');
    }
    if (!isset($tomorrow)) {
        $tomorrow = date('d/m/Y', (time() + 24 * 3600 - TIMEDIFF));
    }
    $stamp = date('d/m/Y', $time - TIMEDIFF);

    //Gestern/Heute/Morgen
    if ('date' == strtolower($pattern) && $stamp == $yesterday) {
        echo '<b>'.$apx->lang->get('YESTERDAY').'</b>';

        return;
    }
    if ('date' == strtolower($pattern) && $stamp == $today) {
        echo '<b>'.$apx->lang->get('TODAY').'</b>';

        return;
    }
    if ('date' == strtolower($pattern) && $stamp == $tomorrow) {
        echo '<b>'.$apx->lang->get('TOMORROW').'</b>';

        return;
    }

    //Standard-Pattern verwenden
    if ('date' == strtolower($pattern)) {
        $pattern = $set['main']['dateformat'];
    }
    if ('time' == strtolower($pattern)) {
        $pattern = $set['main']['timeformat'];
    }

    $string = date($pattern, $time - TIMEDIFF);
    if (false !== strpos($pattern, 'F') || false !== strpos($pattern, 'M')) {
        $string = getcalmonth($string);
    }
    if (false !== strpos($pattern, 'l') || false !== strpos($pattern, 'D')) {
        $string = getweekday($string);
    }

    echo $string;
}

//Suchefeld ausgeben
function main_searchbox($module = '', $template = 'search')
{
    global $apx;
    $tmpl = new tengine();
    $apx->lang->drop('search_basic', 'main');

    if ($apx->is_module($module)) {
        $tmpl->assign('SEARCHIN', $module);
    }
    $tmpl->assign('POSTTO', mklink('search.php', 'search.html'));
    $tmpl->parse('functions/'.$template, 'main');
}

//Druckversion
function main_printlink()
{
    $url = str_replace('&', '&amp;', $_SERVER['REQUEST_URI']);
    if (false !== strpos($url, '?')) {
        $url .= '&amp;print=1';
    } else {
        $url .= '?print=1';
    }
    echo $url;
}

//Seite empfehlen
function main_telllink()
{
    $url = str_replace('&', '&amp;', $_SERVER['REQUEST_URI']);

    //Add ?tell=1
    if (false === strpos($url, 'tell=1')) {
        if (false !== strpos($url, '?')) {
            $url .= '&amp;tell=1';
        } else {
            $url .= '?tell=1';
        }
    }

    echo $url;
}

//Shorttext
function main_shorttext($text, $length)
{
    $length = (int) $length;
    echo shorttext($text, $length);
}

//Titlebar setzen
function main_set_titlebar($title = '')
{
    if (!$title) {
        return;
    }
    titlebar($title);
}

//Headline setzen
function main_set_headline($title = '', $link = '')
{
    global $apx;
    static $reset;

    //Clean
    if (!isset($reset)) {
        $apx->tmpl->headline = [];
        $reset = true;
    }

    //Set
    headline($title, $link);
}

//Design erzwingen
function main_set_design($designid = '')
{
    global $apx;
    if (!$designid) {
        return;
    }
    $apx->tmpl->loaddesign($designid);
}

//Snippet ausgeben
function main_snippet($id = 0)
{
    global $apx,$db;
    $id = (int) $id;
    if (!$id) {
        return;
    }
    list($code) = $db->first('SELECT code FROM '.PRE."_snippets WHERE id='".$id."' LIMIT 1");
    echo $code;
}

//Sektionen
function main_sections($template = 'sections')
{
    global $apx,$set,$db;
    $tmpl = new tengine();

    $secdata = [];
    if (count($apx->sections)) {
        foreach ($apx->sections as $id => $info) {
            ++$i;
            $secdata[$i]['ID'] = $id;
            $secdata[$i]['TITLE'] = $info['title'];
            $secdata[$i]['VIRTUALDIR'] = $info['virtual'];
            $secdata[$i]['LINK'] = mklink('index.php', 'index.html', $id);
        }
    }

    $tmpl->assign('SECTION', $secdata);
    $tmpl->parse('functions/'.$template, 'main');
}
