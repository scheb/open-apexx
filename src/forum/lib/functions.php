<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

/////////////////////////////////////////////////////////////////////////////// DEFINITIONEN

//Forum-Codes
//Codestamp => Find, Replace, Call, Repeatable
//ACHTUNG: Bei allen Ausdrücken wird die "greedy"-Eigenschaft umgedreht!
$forumcodes = [
    'B#1' => ['\[B\](.*)\[/B\]', '<b>$1</b>', '', false],
    'I#1' => ['\[I\](.*)\[/I\]', '<i>$1</i>', '', false],
    'U#1' => ['\[U\](.*)\[/U\]', '<u>$1</u>', '', false],
    'LEFT#1' => ["\\[LEFT\\](.*)\\[/LEFT\\]\r??\n??", '<div style="text-align:left;">$1</div>', '', false],
    'CENTER#1' => ["\\[CENTER\\](.*)\\[/CENTER\\]\r??\n??", '<div style="text-align:center;">$1</div>', '', false],
    'RIGHT#1' => ["\\[RIGHT\\](.*)\\[/RIGHT\\]\r??\n??", '<div style="text-align:right;">$1</div>', '', false],
    'IMG#1' => ['\[IMG\](.*)\[/IMG\]', '<img src="$1" alt="" />', '', false],
    'URL#1' => ['\[URL\](.*)\[/URL\]', '', 'replace_url', false],
    'URL#2' => ['\[URL=(.*)\](.*)\[/URL\]', '', 'replace_url', false],
    'EMAIL#1' => ['\[EMAIL\](.*)\[/EMAIL\]', '<a href="mailto:$1">$1</a>', '', false],
    'EMAIL#2' => ['\[EMAIL=(.*)\](.*)\[/EMAIL\]', '<a href="mailto:$1">$2</a>', '', false],
    'FONT#2' => ['\[FONT\=(.*)](.*)\[/FONT\]', '<span style="font-family:$1">$2</span>', '', false],
    'COLOR#2' => ['\[COLOR\=(.*)](.*)\[/COLOR\]', '<span style="color:$1">$2</span>', '', false],
    'SIZE#2' => ['\[SIZE\=(.*)](.*)\[/SIZE\]', '<font size="$1">$2</font>', '', false],
    'LIST#1' => ['\[LIST\](.*)\[/LIST\]', '', 'replace_list', false],
    'LIST#2' => ['\[LIST=(1|a)\](.*)\[/LIST\]', '', 'replace_list', false],
    'QUOTE#1' => ['\[QUOTE\](.*)\[/QUOTE\]', '', 'replace_quote', true],
    'QUOTE#2' => ['\[QUOTE=(.*)\](.*)\[/QUOTE\]', '', 'replace_quote', true],
];

//Codecache
$codecache = [];

//Codes zum erstzen
$replacement = [
    'quote' => '<div style="margin:5px 30px;"><div class="inlinebox_headline">'.$apx->lang->get('QUOTE').'{FROM}:</div><div class="quote">{TEXT}</div></div>',
    'code' => '<div style="margin:5px 30px;"><div class="inlinebox_headline">{HEADLINE}:</div><div class="code" style="{HEIGHT}"><table><tr><td class="codelines">{LINES}</td><td width="100%">{CODE}</td></tr></table></div></div>',
];

/////////////////////////////////////////////////////////////////////////////// FOREN

//Pfad zum Forum auslesen
function forum_path($info, $inpath = false)
{
    global $set,$db,$apx;
    if (!$info['forumid']) {
        return [];
    }
    $parents = dash_unserialize($info['parents']);
    if ($inpath) {
        $parents[] = $info['forumid'];
    } //Forum in der Liste zeigen
    if (!count($parents)) {
        return [];
    }
    $data = $db->fetch('SELECT forumid,title FROM '.PRE.'_forums WHERE forumid IN ('.implode(',', $parents).') ORDER BY parents ASC');
    if (!count($data)) {
        return [];
    }
    $pathdata = [];
    foreach ($data as $res) {
        ++$i;

        $link = mkrellink(
            'forum.php?id='.$res['forumid'],
            'forum,'.$res['forumid'].',1'.urlformat($res['title']).'.html'
        );

        $pathdata[$i]['TITLE'] = replace($res['title']);
        $pathdata[$i]['LINK'] = $link;
    }

    return $pathdata;
}

//Cache des Forums aktualisieren
function forum_update_cache($forumid, $addposts = 0, $addthreads = 0)
{
    global $set,$db,$apx,$user;
    $addposts = (int) $addposts;
    $addthreads = (int) $addthreads;

    $lastpost = $db->first('
		SELECT threadid,prefix,title,icon,lastpost,lastposter,lastposter_userid,lastposttime
		FROM '.PRE."_forum_threads
		WHERE forumid='".$forumid."' AND del=0 AND moved=0
		ORDER BY lastposttime DESC
		LIMIT 1
	");
    $db->query('
		UPDATE '.PRE.'_forums
		SET
			'.($addposts ? 'posts=posts+'.$addposts.',' : '').'
			'.($addthreads ? 'threads=threads+'.$addthreads.',' : '')."
			lastpost='".$lastpost['lastpost']."',
			lastposter='".addslashes($lastpost['lastposter'])."',
			lastposter_userid='".$lastpost['lastposter_userid']."',
			lastposttime='".$lastpost['lastposttime']."',
			lastthread='".$lastpost['threadid']."',
			lastthread_title='".addslashes($lastpost['title'])."',
			lastthread_icon='".$lastpost['icon']."',
			lastthread_prefix='".$lastpost['prefix']."'
		WHERE forumid='".$forumid."'
		LIMIT 1
	");
}

$cached_forum_readtime = false;

//Forum als gelesen speichern
function forum_isread($forumid)
{
    global $cached_forum_readtime;
    $forumid = (string) $forumid;

    //Infos auslesen und cachen
    if (false == $cached_forum_readtime) {
        $cached_forum_readtime = get_cookie_keyarray('forum_forumview');
    }

    $cached_forum_readtime[$forumid] = time();
    set_cookie_keyarray('forum_forumview', $cached_forum_readtime);
}

//Wann wurde ein Forum zuletzt gelesen?
function forum_readtime($forumid)
{
    global $cached_forum_readtime;
    $forumid = (string) $forumid;

    //Infos auslesen und cachen
    if (false == $cached_forum_readtime) {
        $cached_forum_readtime = get_cookie_keyarray('forum_forumview');
    }

    if (!isset($cached_forum_readtime[$forumid])) {
        return 0;
    }

    return (int) $cached_forum_readtime[$forumid];
}

/////////////////////////////////////////////////////////////////////////////// THEMEN

//Themen-Info auslesen
function thread_info($threadid)
{
    global $set,$db,$apx;
    $threadid = (int) $threadid;
    if (!$threadid) {
        return [];
    }
    $info = $db->first('SELECT * FROM '.PRE."_forum_threads WHERE threadid='".$threadid."' LIMIT 1");
    if (!$info['threadid']) {
        return [];
    }

    return $info;
}

//Cache des Themas aktualisieren
function thread_update_cache($threadid, $addposts = 0, $updateFirstpost = false)
{
    global $set,$db,$apx,$user;
    $addposts = (int) $addposts;
    $addthreads = (int) $addthreads;

    $lastpost = $db->first('
		SELECT postid,userid,username,time
		FROM '.PRE."_forum_posts
		WHERE threadid='".$threadid."' AND del=0
		ORDER BY time
		DESC LIMIT 1
	");
    if ($updateFirstpost) {
        $firstpost = $db->first('
			SELECT postid,userid,username,time
			FROM '.PRE."_forum_posts
			WHERE threadid='".$threadid."' AND del=0
			ORDER BY time ASC
			LIMIT 1
		");
    }
    $db->query('
		UPDATE '.PRE.'_forum_threads
		SET
			'.($addposts ? 'posts=posts+'.$addposts.',' : '').'
			'.($updateFirstpost ? "
			opener='".addslashes($firstpost['username'])."',
			opener_userid='".$firstpost['userid']."',
			opentime='".$firstpost['time']."',
			firstpost='".$firstpost['postid']."',
			" : '')."
			lastposter='".addslashes($lastpost['username'])."',
			lastposter_userid='".$lastpost['userid']."',
			lastposttime='".$lastpost['time']."',
			lastpost='".$lastpost['postid']."'
		WHERE threadid='".$threadid."'
		LIMIT 1
	");
}

$cached_thread_readtime = false;

//Thema als gelesen speichern
function thread_isread($threadid)
{
    global $cached_thread_readtime;
    $threadid = (string) $threadid;

    //Infos auslesen und cachen
    if (false == $cached_thread_readtime) {
        $cached_thread_readtime = get_cookie_keyarray('forum_threadview');
    }

    $cached_thread_readtime[$threadid] = time();
    set_cookie_keyarray('forum_threadview', $cached_thread_readtime);
}

//Wann wurde ein Thema zuletzt gelesen?
function thread_readtime($threadid)
{
    global $cached_thread_readtime;
    $threadid = (string) $threadid;

    //Infos auslesen und cachen
    if (false == $cached_thread_readtime) {
        $cached_thread_readtime = get_cookie_keyarray('forum_threadview');
    }

    if (!isset($cached_thread_readtime[$threadid])) {
        return 0;
    }

    return (int) $cached_thread_readtime[$threadid];
}

//Alle gelesenen Threads zurückgeben
function threads_get_read()
{
    if (false == $cached_thread_readtime) {
        $cached_thread_readtime = get_cookie_keyarray('forum_threadview');
    }

    return $cached_thread_readtime;
}

/////////////////////////////////////////////////////////////////////////////// BEITRÄGE

//Posting-Infos
function post_info($postid)
{
    global $set,$db,$apx;
    $postid = (int) $postid;
    if (!$postid) {
        return [];
    }
    $info = $db->first('SELECT * FROM '.PRE."_forum_posts WHERE postid='".$postid."' LIMIT 1");
    if (!$info['postid']) {
        return [];
    }

    return $info;
}

/////////////////////////////////////////////////////////////////////////////// ANKÜNDIGUNGEN

$cached_announcement_readtime = false;

//Ankündigung als gelesen speichern
function announcement_isread($announcementid)
{
    global $cached_announcement_readtime;
    $announcementid = (string) $announcementid;

    //Infos auslesen und cachen
    if (false == $cached_announcement_readtime) {
        $cached_announcement_readtime = get_cookie_keyarray('forum_announcementview');
    }

    $cached_announcement_readtime[$announcementid] = time();
    set_cookie_keyarray('forum_announcementview', $cached_announcement_readtime);
}

//Wann wurde ein Ankündigung zuletzt gelesen?
function announcement_readtime($announcementid)
{
    global $cached_announcement_readtime;
    $announcementid = (string) $announcementid;

    //Infos auslesen und cachen
    if (false == $cached_announcement_readtime) {
        $cached_announcement_readtime = get_cookie_keyarray('forum_announcementview');
    }

    if (!isset($cached_announcement_readtime[$announcementid])) {
        return 0;
    }

    return (int) $cached_announcement_readtime[$announcementid];
}

//Alle gelesenen Ankündigungen zurückgeben
function announcements_get_read()
{
    if (false == $cached_announcement_readtime) {
        $cached_announcement_readtime = get_cookie_keyarray('forum_announcementview');
    }

    return $cached_announcement_readtime;
}

/////////////////////////////////////////////////////////////////////////////// COOKIE-ARRAY

//Einen Cookie-Array mit Schlüsselwerten erzeugen
function set_cookie_keyarray($name, $array, $linesep = '|', $valsep = '§')
{
    global $set;
    $lines = [];
    foreach ($array as $key => $value) {
        $lines[] = $key.$valsep.$value;
    }
    $newvalue = implode($linesep, $lines);
    setcookie($set['main']['cookie_pre'].'_'.$name, $newvalue);
}

// Einen Cookie-Array mit Schlüsselwerten auslesen
function get_cookie_keyarray($name, $linesep = '|', $valsep = '§')
{
    global $set;
    $data = [];
    $value = $_COOKIE[$set['main']['cookie_pre'].'_'.$name];
    if (!$value) {
        return $data;
    }
    $lines = explode($linesep, $value);
    foreach ($lines as $line) {
        $pp = explode($valsep, $line);
        $data[$pp[0]] = $pp[1];
    }

    return $data;
}

/////////////////////////////////////////////////////////////////////////////// AKTIVITÄT

//Aktivität speichern
function forum_activity($type, $id)
{
    global $apx, $db, $set, $user;

    $db->query('
		DELETE FROM '.PRE."_forum_activity
		WHERE userid='".$user->info['userid']."' AND type='".addslashes($type)."' AND id='".intval($id)."'
	");

    $db->query('
		INSERT IGNORE INTO '.PRE."_forum_activity
		VALUES ('".$user->info['userid']."', '".ip2integer(get_remoteaddr())."', '".addslashes($type)."', '".intval($id)."', '".time()."', '".$user->info['pub_invisible']."')
	");
}

//Aktivität auslesen
function forum_get_activity($type, $id, $moderators = [])
{
    global $apx, $db, $set, $user;

    $userdata = [];

    //Anzahl auslesen
    list($userCount) = $db->first('
		SELECT count(*)
		FROM '.PRE."_forum_activity AS a
		WHERE a.type='".addslashes($type)."' AND a.id='".intval($id)."' AND a.userid!=0 AND a.time>'".(time() - 300)."' ".($user->is_admin() ? '' : "AND ( a.invisible=0 OR a.userid='".$user->info['userid']."' )").'
	');
    list($totalCount) = $db->first('
		SELECT count(*)
		FROM '.PRE."_forum_activity AS a
		WHERE a.type='".addslashes($type)."' AND a.id='".intval($id)."' AND a.time>'".(time() - 300)."'
	");
    $guestCount = $totalCount - $userCount;

    //Aktive User auslesen
    $data = $db->fetch('
		SELECT u.userid, u.username, u.groupid, u.forum_posts, g.gtype, a.time, a.invisible
		FROM '.PRE.'_forum_activity AS a
		LEFT JOIN '.PRE.'_user AS u USING(userid)
		LEFT JOIN '.PRE."_user_groups AS g USING(groupid)
		WHERE a.type='".addslashes($type)."' AND a.id='".intval($id)."' AND a.userid!=0 AND a.time>'".(time() - 300)."' ".($user->is_admin() ? '' : "AND ( a.invisible=0 OR a.userid='".$user->info['userid']."' )").'
		ORDER BY u.username ASC
	');
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;
            $rankinfo = get_rank($res);

            $userdata[$i]['USERNAME'] = replace($res['username']);
            $userdata[$i]['USERID'] = $res['userid'];
            $userdata[$i]['RANK_COLOR'] = '#'.$rankinfo['color'];
            $userdata[$i]['INVISIBLE'] = $res['invisible'];

            if ('admin' == $res['gtype']) {
                $userdata[$i]['IS_ADMIN'] = 1;
            } elseif ('indiv' == $res['gtype']) {
                $userdata[$i]['IS_TEAM'] = 1;
            } elseif (in_array($res['userid'], $moderators)) {
                $userdata[$i]['IS_MODERATOR'] = 1;
            }
        }
    }

    return [$userCount, $guestCount, $userdata];
}

/////////////////////////////////////////////////////////////////////////////// REPLACEMENT

//Anhang-Größe
function forum_getsize($fsize, $digits = 1)
{
    $fsize = (float) $fsize;
    if ($fsize < 1024) {
        return $fsize.' Byte';
    }
    if ($fsize >= 1024 && $fsize < 1024 * 1024) {
        return  number_format($fsize / (1024), $digits, ',', '').' KB';
    }
    if ($fsize >= 1024 * 1024 && $fsize < 1024 * 1024 * 1024) {
        return number_format($fsize / (1024 * 1024), $digits, ',', '').' MB';
    }
    if ($fsize >= 1024 * 1024 * 1024 && $fsize < 1024 * 1024 * 1024 * 1024) {
        return number_format($fsize / (1024 * 1024 * 1024), $digits, ',', '').' GB';
    }

    return number_format($fsize / (1024 * 1024 * 1024 * 1024), $digits, ',', '').' TB';
}

//Alle Codes entfernen
function clear_codes($text)
{
    while (preg_match('#\[([a-z0-9]+)(=.*?)?\](.*?)\[/\\1\]#si', $text)) {
        $text = preg_replace('#\[([a-z0-9]+)(=.*?)?\](.*?)\[/\\1\]#si', '\\3', $text);
    }

    return $text;
}

//Alle Codes außer Quotes entfernen
function clear_codes_replace_quotes($text)
{
    global $set,$forumcodes;
    static $readout;

    //Benutzerdefinierte Codes auslesen
    if (!$readout) {
        $forumcodes = produce_forum_codes();
        $readout = true;
    }

    //Sonderzeichen ersetzen
    $text = compatible_hsc($text);

    //Codes ersetzen
    foreach ($forumcodes as $stamp => $info) {
        if (!in_array($stamp, ['QUOTE#1', 'QUOTE#2'])) {
            continue;
        }
        //Wiederholbar
        if ($info[3]) {
            while (preg_match('#'.$info[0].'#siU', $text)) {
                if ($info[2]) {
                    $text = preg_replace_callback('#'.$info[0].'#siU', $info[2], $text);
                } else {
                    $text = preg_replace('#'.$info[0].'#siU', $info[1], $text);
                }
            }
        }

        //Nicht wiederholbar
        else {
            if ($info[2]) {
                $text = preg_replace_callback('#'.$info[0].'#siU', $info[2], $text);
            } else {
                $text = preg_replace('#'.$info[0].'#siU', $info[1], $text);
            }
        }
    }

    //Zeilenumbrüche
    $text = nl2br($text);

    //Restliche Codes löschen
    while (preg_match('#\[([a-z0-9]+)(=.*?)?\](.*?)\[/\\1\]#si', $text)) {
        $text = preg_replace('#\[([a-z0-9]+)(=.*?)?\](.*?)\[/\\1\]#si', '\\3', $text);
    }

    return $text;
}

//Funktion zur Ersetzung aller Codes
function forum_replace($text, $codes = true, $smilies = true)
{
    global $set,$apx,$codecache;

    //Badwords ersetzen
    if ($set['forum']['badwords']) {
        $text = badwords($text);
    }

    //Codes ersetzen
    if ($set['forum']['codes'] && $codes) {
        //Code und PHP ausschneiden
        $codecache = [];
        $text = preg_replace_callback('#\[(PHP|CODE|HTML)\](.*?)\[/\\1\]#si', 'save_code', $text);

        //Restliche Codes ersetzen
        $text = forum_codes($text);
    }

    //Sonderzeichen ersetzen
    else {
        $text = replace($text, true);
    }

    //Smilies ersetzen
    if ($set['forum']['smilies'] && $smilies) {
        $text = dbsmilies($text);
    }

    //Code und PHP einfügen
    if ($set['forum']['codes'] && $codes) {
        $text = strtr($text, $codecache);
    }

    //Glossar-Links erzeugen
    if ($apx->is_module('glossar')) {
        $text = glossar_highlight($text, 'forum');
    }

    return $text;
}

//Forum-Codes auslesen
function produce_forum_codes()
{
    global $set,$forumcodes;
    foreach ($set['main']['codes'] as $res) {
        $stamp = strtoupper($res['code']).'#'.$res['count'];
        if (isset($forumcodes[$stamp])) {
            continue;
        }
        if (2 == $res['count']) {
            $find = '\['.$res['code'].'=(.*)\](.*)\[/'.$res['code'].'\]';
            $replace = str_replace('{1}', '$1', str_replace('{2}', '$2', $res['replace']));
        } else {
            $find = '\['.$res['code'].'\](.*)\[/'.$res['code'].'\]';
            $replace = str_replace('{1}', '$1', $res['replace']);
        }

        //Find, Replace, Call, Repeatable
        $forumcodes[$stamp] = [$find, $replace, '', false];
    }

    return $forumcodes;
}

//Codes aus der Datenbank
function forum_codes($text, $sig = false)
{
    global $set,$forumcodes;
    static $readout;

    //Benutzerdefinierte Codes auslesen
    if (!$readout) {
        $forumcodes = produce_forum_codes();
        $readout = true;
    }

    //Sonderzeichen ersetzen
    $text = compatible_hsc($text);

    //Codes ersetzen
    foreach ($forumcodes as $info) {
        //Wiederholbar
        if ($info[3]) {
            while (preg_match('#'.$info[0].'#siU', $text)) {
                if ($info[2]) {
                    $text = preg_replace_callback('#'.$info[0].'#siU', $info[2], $text);
                } else {
                    $text = preg_replace('#'.$info[0].'#siU', $info[1], $text);
                }
            }
        }

        //Nicht wiederholbar
        else {
            if ($info[2]) {
                $text = preg_replace_callback('#'.$info[0].'#siU', $info[2], $text);
            } else {
                $text = preg_replace('#'.$info[0].'#siU', $info[1], $text);
            }
        }
    }

    //Zeilenumbrüche
    $text = nl2br($text);

    return $text;
}

//Zitat
function replace_quote($textinfo)
{
    global $apx,$replacement;

    if ('[QUOTE=' == strtoupper(substr($textinfo[0], 0, 7))) {
        $insert['{FROM}'] = ' '.$apx->lang->get('BY').' '.$textinfo[1];
        $insert['{TEXT}'] = $textinfo[2];

        return strtr($replacement['quote'], $insert);
    }

    $insert['{FROM}'] = '';
    $insert['{TEXT}'] = $textinfo[1];

    return strtr($replacement['quote'], $insert);
}

//URL
function replace_url($urlinfo)
{
    if ('www.' == substr($urlinfo[1], 0, 4)) {
        $urlinfo[1] = 'http://'.$urlinfo[1];
    } //URL vervollständigen

    if ('[URL=' == strtoupper(substr($urlinfo[0], 0, 5))) {
        return '<a href="'.$urlinfo[1].'" target="_blank">'.$urlinfo[2].'</a>';
    }

    if (strlen($urlinfo[1]) > 70) {
        $text = substr($urlinfo[1], 0, 45).'[...]'.substr($urlinfo[1], -20);
    } else {
        $text = $urlinfo[1];
    }

    return '<a href="'.$urlinfo[1].'" target="_blank">'.$text.'</a>';
}

//Liste
function replace_list($listinfo)
{
    if ('[LIST=' == strtoupper(substr($listinfo[0], 0, 6))) {
        $listvar = 2;
    } else {
        $listvar = 1;
    }

    $pp = explode('[*]', $listinfo[$listvar]);
    foreach ($pp as $point) {
        $point = trim($point);
        if (!$point) {
            continue;
        }
        $list .= '<li>'.$point.'</li>';
    }

    if (2 == $listvar) {
        return '<ol'.iif('A' == strtoupper($listinfo[1]), ' type="A"').'>'.$list.'</ol>';
    }

    return '<ul>'.$list.'</ul>';
}

//Code verarbeiten
function save_code($textinfo)
{
    global $apx,$codecache,$replacement;
    static $count;

    ++$count;
    $hash = md5(microtime());
    $type = strtoupper($textinfo[1]);
    //$text=stripslashes(trim($textinfo[2]));
    $text = trim($textinfo[2], "\n\r\t\0\x0B");
    $linecount = substr_count($text, "\n") + 1;

    //PHP-Code
    if ('PHP' == $type) {
        $removetags = false;
        if ('<?php' != strtolower(substr($text, 0, 5))) {
            $text = '<?php '.$text.' ?>';
            $removetags = true;
        }

        $phpcode = highlight_string($text, true);

        //PHP-Tags wieder entfernen
        if ($removetags) {
            $phpcode = preg_replace('#<font color="\#([0-9ABCDEF]{6})">'."\n".'<font color="\#([0-9ABCDEF]{6})">&lt;\?php#', '<font color="#\1"><font color="#\2">', $phpcode);
            $phpcode = str_replace('?&gt;</font>'."\n".'</font>', '</font></font>', $phpcode);
        }

        //PHP-Highlight fügt nur &nbsp; am Anfang der Zeile an
        //create_whitespace() ist daher nicht notwendig!

        $marker = '[PHP_'.$count.'_'.$hash.']';
        $insert['{CODE}'] = $phpcode;
        $insert['{HEADLINE}'] = $apx->lang->get('PHPCODE');
    }

    //HTML-Code
    elseif ('HTML' == $type) {
        $htmlcode = highlight_html($text);
        $htmlcode = create_whitespace($htmlcode);
        $marker = '[HEML_'.$count.'_'.$hash.']';
        $insert['{CODE}'] = $htmlcode;
        $insert['{HEADLINE}'] = $apx->lang->get('HTMLCODE');
    }

    //Normaler Code
    else {
        $normalcode = nl2br(compatible_hsc($text));
        $normalcode = create_whitespace($normalcode);
        $marker = '[CODE_'.$count.'_'.$hash.']';
        //$insert['{CODE}']=nl2br(str_replace(' ','&nbsp;',str_replace("\t",'&nbsp;&nbsp;',compatible_hsc($text))));
        $insert['{CODE}'] = $normalcode;
        $insert['{HEADLINE}'] = $apx->lang->get('CODE');
    }

    //Zeilennummern generieren
    $lines = '1';
    for ($i = 2; $i <= $linecount; ++$i) {
        $lines .= '<br />'.$i;
    }

    $insert['{LINES}'] = $lines;
    if (!$_REQUEST['print']) {
        $insert['{HEIGHT}'] = iif($linecount > 30, 'height:360px;');
    }
    $codecache[$marker] = strtr($replacement['code'], $insert);

    return $marker;
}

//Einrückung am Zeilenanfang erzeugen
function create_whitespace($code)
{
    $lines = explode("\n", str_replace("\r", '', $code));
    foreach ($lines as $key => $line) {
        $whitespace = '';
        for ($i = 0; $i < strlen($line); ++$i) {
            if (' ' == $line[$i]) {
                $whitespace .= '&nbsp;';
            } elseif ("\t" == $line[$i]) {
                $whitespace .= '&nbsp;&nbsp;';
            } else {
                break;
            }
        }
        $lines[$key] = $whitespace.ltrim($line);
    }

    return implode('', $lines);
}

//Codes unverändert zwischenspeichern
function save_code_blank($textinfo)
{
    global $codecache;
    static $count;

    ++$count;
    $hash = md5(microtime());
    $type = $textinfo[1];
    $text = $textinfo[2];
    $marker = '['.strtoupper($type).'_'.$count.'_'.$hash.']';
    $codecache[$marker] = '['.$type.']'.$text.'[/'.$type.']';

    return $marker;
}

//HTML-Code highlighten
function highlight_html($text)
{
    $text = str_replace("\r", '', $text);
    $code = '';

    //Farben
    $color = [
        'xml' => '#DF5EDF',
        'php' => '#DF5EDF',
        'doctype' => '#3399CC',
        'comment' => '#FF9900',
        'html' => '#0000CC',
        'attribute' => '#006600',
        'text' => '#000000',
        'spchar' => '#CC0000',
    ];

    //Flags
    $intag = false; //In einem HTML-Tag
    $inatt = false; //In einem Attribut
    $incomment = false; //In einem Kommentar

    //Zeichen für Zeichen durchlaufen
    for ($i = 0; $i < strlen($text); ++$i) {
        $char = $text[$i];

        //Kommentar beginnt
        if ('<!--' == substr($text, $i, 4)) {
            $incomment = true;
            $code .= '<font color="'.$color['comment'].'">'.compatible_hsc($char);
        }

        //XML beginnt
        elseif ('<?xml' == substr($text, $i, 5)) {
            $inxml = true;
            $code .= '<font color="'.$color['xml'].'">'.compatible_hsc($char);
        }

        //PHP beginnt
        elseif ('<?php' == substr($text, $i, 5) || '<?=' == substr($text, $i, 3)) {
            $inphp = true;
            $code .= '<font color="'.$color['php'].'">'.compatible_hsc($char);
        }

        //HTML beginnt
        elseif (preg_match('#^<[a-z]$#i', substr($text, $i, 2)) || preg_match('#^</[a-z]$#i', substr($text, $i, 3))) {
            $inhtml = true;
            $inatt = false;
            $code .= '<font color="'.$color['html'].'">'.compatible_hsc($char);
        }

        //DOCTYPE beginnt
        elseif ('<!' == substr($text, $i, 2)) {
            $indoctype = true;
            $code .= '<font color="'.$color['doctype'].'">'.compatible_hsc($char);
        }

        //String mit normalen Anführungszeichen beginnt
        elseif ($inhtml && !$inatt && preg_match('#^[a-z]="$#i', substr($text, $i, 3))) {
            $inatt = '"';
            $code .= compatible_hsc($char).'=<font color="'.$color['attribute'].'">&quot;';
            $i += 2;
        }

        //String mit Hochkomma beginnt
        elseif ($inhtml && !$inatt && preg_match('#^[a-z]=\'$#i', substr($text, $i, 3))) {
            $inatt = "'";
            $code .= compatible_hsc($char).'=<font color="'.$color['attribute'].'">\'';
            $i += 2;
        }

        //Attribut beginnt
        elseif ($inhtml && !$inatt && preg_match('#^[a-z]=$#i', substr($text, $i, 2))) {
            $inatt = true;
            $code .= compatible_hsc($char).'=<font color="'.$color['attribute'].'">';
            ++$i;
        }

        ////////////////////

        //Kommentar Ende
        elseif ($incomment && '-->' == substr($text, $i, 3)) {
            $code .= '--&gt;</font>';
            $incomment = false;
            $i += 2;
        }

        //XML Ende
        elseif ($inxml && '?>' == substr($text, $i, 2)) {
            $code .= '?&gt;</font>';
            $inxml = false;
            ++$i;
        }

        //PHP Ende
        elseif ($inphp && '?>' == substr($text, $i, 2)) {
            $code .= '?&gt;</font>';
            $inphp = false;
            ++$i;
        }

        //DOCTYPE Ende
        elseif ($indoctype && '>' == $char) {
            $code .= '&gt;</font>';
            $indoctype = false;
        }

        //HTML Ende
        elseif ($inhtml && '>' == $char) {
            if ($inatt) {
                $code .= '</font>';
            }
            $code .= '&gt;</font>';
            $inhtml = false;
            $inatt = false;
        }

        //HTML Ende
        elseif ($inhtml && '/>' == substr($char, $i, 2)) {
            if ($inatt) {
                $code .= '</font>';
            }
            $code .= '/&gt;</font>';
            $inhtml = false;
            $inatt = false;
        }

        //Attribut ohne Anführungszeichen Ende
        elseif (is_string($inatt) && $inatt && $char == $inatt) {
            $code .= compatible_hsc($char).'</font>';
            $inatt = false;
        }

        //Attribut ohne Anführungszeichen Ende
        elseif (is_bool($inatt) && $inatt && preg_match('#\s#', $char)) {
            $code .= compatible_hsc($char).'</font>';
            $inatt = false;
        }

        //Zeichen einfach anfügen (Text)
        else {
            $char = compatible_hsc($char);
            //$char=str_replace(' ','&nbsp;',str_replace("\t",'&nbsp;&nbsp;',$char));
            $char = nl2br($char);
            $code .= $char;
        }
    }

    //Sonderzeichen-Codes ersetzen
    $code = preg_replace('#&amp;([a-z]+|(\#[0-9]+));#i', '<font color="'.$color['spchar'].'">&amp;$1;</font>', $code);

    return '<code><font color="'.$color['text'].'">'.$code.'</font></code>';
}

//Text Highlight
function text_highlight($text)
{
    static $words;

    if (!isset($words)) {
        $words = explode(' ', $_REQUEST['highlight']);
    }

    foreach ($words as $word) {
        $word = trim($word);
        if (!$word) {
            continue;
        }
        $text = preg_replace_callback('#((<[^>]*)|'.preg_quote($word).')#i', function ($m) {return $m[2] == $m[1] ? $m[1] : '<span class="highlight">'.$m[1].'</span>'; }, $text);
        //$text=preg_replace('#>[^<]*('.preg_quote($word).')[^<]*<[^>]*#i','<span class="highlight">$1</span>',$text);
    }

    return $text;
}

//URLs und eMail-Adressen tranformieren
function transform_urls($text)
{
    global $codecache;

    //Code-Boxen retten
    $text = preg_replace_callback('#\[(PHP|CODE|HTML)\](.*?)\[/\\1\]#si', 'save_code_blank', $text);

    $text = preg_replace('#(^|[\n ])([a-z]+?://[^ "\n\r\t<]*)#is', '\\1[URL]\\2[/URL]', $text);
    $text = preg_replace('#(^|[\n ])((www|ftp)\.[^ "\t\n\r<]*)#is', '\\1[URL]http://\\2[/URL]', $text);
    $text = preg_replace('#(^|[\n ])([a-z0-9&\-_.]+?)@([A-Za-z0-9_\-]+\.([A-Za-z0-9_\-\.]+\.)*[A-Za-z0-9_]+)#i', '\\1[EMAIL]\\2@\\3[/EMAIL]', $text);

    //Codes einfügen
    $text = strtr($text, $codecache);

    return $text;
}

/////////////////////////////////////////////////////////////////////////////// MODERATOREN

//Alle Moderatoren auflisten
function get_modlist()
{
    global $set,$db,$apx;

    $data = $db->fetch('SELECT moderator FROM '.PRE."_forums WHERE moderator!=''");
    if (!count($data)) {
        return [];
    }
    $modlist = '';
    foreach ($data as $res) {
        $modlist .= $res['moderator'];
    }
    $modlist = preg_replace('#\|{2,}#', '|', $modlist);
    $mods = dash_unserialize($modlist);
    $mods = array_unique($mods);

    return $mods;
}

/////////////////////////////////////////////////////////////////////////////// RÄNGE

//Rank eines Benutzers zurückgeben
function get_rank($userinfo)
{
    global $set,$db,$apx;
    static $rankinfo;

    //Ränge beim ersten Mal auslesen
    if (!isset($rankinfo)) {
        $rankinfo = [
            'user' => [],
            'group' => [],
            'posts' => [],
        ];

        $data = $db->fetch('SELECT title,image,color,minposts,userid,groupid FROM '.PRE.'_forum_ranks ORDER BY userid ASC,groupid ASC,minposts DESC');
        if (count($data)) {
            foreach ($data as $res) {
                $rankitem = [
                    'title' => $res['title'],
                    'image' => $res['image'],
                    'color' => $res['color'],
                ];
                if ($res['userid']) {
                    $rankinfo['user'][$res['userid']] = $rankitem;
                } elseif ($res['groupid']) {
                    $rankinfo['group'][$res['groupid']] = $rankitem;
                } else {
                    $rankinfo['posts'][$res['minposts']] = $rankitem;
                }
            }
        }
    }

    $userid = (int) $userinfo['userid'];
    $groupid = (int) $userinfo['groupid'];
    $posts = (int) $userinfo['forum_posts'];
    if (!$groupid) {
        $groupid = 3;
    } //Keine Benutzergruppe => Gast

    //Spezialränge
    if (isset($rankinfo['user'][$userid])) {
        return $rankinfo['user'][$userid];
    }
    if (isset($rankinfo['group'][$groupid])) {
        return $rankinfo['group'][$groupid];
    }
    //Posting-Ränge
    foreach ($rankinfo['posts'] as $minposts => $rank) {
        if ($posts < $minposts) {
            continue;
        }

        return $rank;
    }

    //Kein Rang? :(
    return [];
}

/////////////////////////////////////////////////////////////////////////////// ABONNEMENTS

//Existiert ein Abonnement dieses Forums?
function is_forum_subscr()
{
    global $set,$apx,$db,$user;
    if (!$user->info['userid']) {
        return false;
    }
    list($id, $not) = $db->first('SELECT id,notification FROM '.PRE."_forum_subscriptions WHERE ( type='forum' AND source='".$_REQUEST['id']."' AND userid='".$user->info['userid']."' ) LIMIT 1");
    if ($id) {
        return $not;
    }

    return false;
}

//Existiert ein Abonnement dieses Themas?
function is_thread_subscr()
{
    global $set,$apx,$db,$user;
    if (!$user->info['userid']) {
        return false;
    }
    list($id, $not) = $db->first('SELECT id,notification FROM '.PRE."_forum_subscriptions WHERE ( type='thread' AND source='".$_REQUEST['id']."' AND userid='".$user->info['userid']."' ) LIMIT 1");
    if ($id) {
        return $not;
    }

    return false;
}

/////////////////////////////////////////////////////////////////////////////// INDEX

//Index aktualisieren
function update_index($text, $threadid, $postid, $title = false)
{
    global $set,$db,$apx;
    $threadid = (int) $threadid;
    $postid = (int) $postid;
    $title = (int) $title;
    if (!$threadid) {
        return false;
    }
    if (!$postid) {
        return false;
    }
    //Posting bearbeitet => Einträge löschen
    if ($postid) {
        $db->query('DELETE FROM '.PRE."_forum_index WHERE postid='".$postid."' AND istitle='".iif($title, '1', '0')."'");
    }

    //Codes entfernen
    $text = clear_codes($text);

    //Wörter trennen
    $text = strtolower($text);
    $words = extract_words($text);
    $words = array_unique($words);

    //Wörter filtern
    include dirname(__FILE__).'/stopwords.php';
    $values = '';
    foreach ($words as $word) {
        $word = trim($word);
        if (!$word) {
            continue;
        } //Leere Wörter überspringen
        if (strlen($word) < 3) {
            continue;
        } //Wörter kürzer als 3 Zeichen überspringen
        if (strlen($word) > 50) {
            continue;
        } //Wörter länger als 50 Zeichen überspringen
        if (in_array($word, $stopwords)) {
            continue;
        } //Stopwörter überspringen
        $values .= iif($values, ',')."('".addslashes(strtolower($word))."','".$threadid."','".$postid."','".$title."')";
    }

    //In die Datenbank eintragen
    if ($values) {
        $db->query('INSERT INTO '.PRE.'_forum_index (word,threadid,postid,istitle) VALUES '.$values);
    }

    return true;
}

//Wörter aus Suchstring
function searchstring_to_array($text)
{
    //Codes entfernen
    $text = clear_codes($text);

    //Wörter trennen
    $words = extract_words($text);
    $words = array_unique($words);

    //Stopwörter löschen
    include 'lib/stopwords.php';
    $filteredwords = array_diff($words, $stopwords);
    $ignored = array_intersect($words, $stopwords);

    //Wörter filtern
    foreach ($filteredwords as $key => $word) {
        $word = trim($word);
        if (!$word || strlen($word) < 3 || strlen($word) > 50) {
            if ($word) {
                $ignored[] = $word;
            }
            unset($filteredwords[$key]);
        }
    }

    $filteredwords = array_map('strtolower', $filteredwords); //Kleinschreibung für Suchbegriffe

    return [array_unique($filteredwords), array_unique($ignored)];
}

//Aus einem Array mit Wörtern eine SQL-Abfrage machen
function array_to_searchsql($list)
{
    $list = array_map('addslashes', $list);

    return "'".implode("','", $list)."'";
}
