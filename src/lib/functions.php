<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//////////////////////////////////////////////////////////////////////////////////

//IIF Funktion
function iif($arg, $true, $false = '')
{
    if ($arg) {
        return $true;
    }

    return $false;
}

////////////////////////////////////////////////////////////////////////////////// -> SUCHE

//Suchergebnis speichern
function saveSearchResult($object, $result, $meta = null)
{
    global $db;
    $searchid = md5(random_string().microtime());
    $db->query('
		INSERT INTO '.PRE."_search
		VALUES ('".$searchid."', '".addslashes($object)."', '".addslashes(serialize($result))."', '".addslashes(serialize($meta))."', '".time()."')
	");

    return $searchid;
}

//Suchergebnis auslesen
function getSearchResult($object, $searchid)
{
    global $db;
    list($result, $meta) = $db->first('
		SELECT results, options
		FROM '.PRE."_search
		WHERE object='".addslashes($object)."' AND searchid='".addslashes($searchid)."'
		ORDER BY time DESC
		LIMIT 1
	");
    if ($result) {
        return [
            unserialize($result),
            unserialize($meta),
        ];
    }

    return null;
}

////////////////////////////////////////////////////////////////////////////////// -> DATUM + UHRZEIT

//Datum+Zeit formatieren
function mkdate($time = false, $conn = false)
{
    global $set;

    if (false === $conn) {
        $conn = $set['main']['conndatetime'];
    }
    if (false === $time) {
        $time = time();
    }

    return apxdate($time).$conn.apxtime($time);
}

//Datum formatieren
function apxdate($time = false, $forceformat = false)
{
    global $set,$apx;
    static $yesterday,$today,$tomorrow;

    if ($forceformat) {
        $format = $forceformat;
    } else {
        $format = $set['main']['dateformat'];
    }

    if (false === $time) {
        $time = time();
    }

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
    if ($stamp == $yesterday) {
        return '<b>'.$apx->lang->get('YESTERDAY').'</b>';
    }
    if ($stamp == $today) {
        return '<b>'.$apx->lang->get('TODAY').'</b>';
    }
    if ($stamp == $tomorrow) {
        return '<b>'.$apx->lang->get('TOMORROW').'</b>';
    }
    $string = date($format, $time - TIMEDIFF);

    return getcalmonth(getweekday($string));
}

//Zeit formatieren
function apxtime($time = false, $forceformat = false)
{
    global $set,$apx;

    if ($forceformat) {
        $format = $forceformat;
    } else {
        $format = $set['main']['timeformat'];
    }

    if (false === $time) {
        $time = time();
    }

    return date($format, $time - TIMEDIFF).$apx->lang->get('CORE_OCLOCK');
}

//Kalendermonat holen
function getcalmonth($string)
{
    global $apx;
    static $month,$month_number;

    if (!isset($month)) {
        $month = [
            'January' => $apx->lang->get('MONTH_JAN'),
            'February' => $apx->lang->get('MONTH_FEB'),
            'March' => $apx->lang->get('MONTH_MAR'),
            'April' => $apx->lang->get('MONTH_APR'),
            'May' => $apx->lang->get('MONTH_MAY'),
            'June' => $apx->lang->get('MONTH_JUN'),
            'July' => $apx->lang->get('MONTH_JUL'),
            'August' => $apx->lang->get('MONTH_AUG'),
            'September' => $apx->lang->get('MONTH_SEP'),
            'October' => $apx->lang->get('MONTH_OCT'),
            'November' => $apx->lang->get('MONTH_NOV'),
            'December' => $apx->lang->get('MONTH_DEC'),
            'Jan' => substr($apx->lang->get('MONTH_JAN'), 0, 3),
            'Feb' => substr($apx->lang->get('MONTH_FEB'), 0, 3),
            'Mar' => substr($apx->lang->get('MONTH_MAR'), 0, 3),
            'Apr' => substr($apx->lang->get('MONTH_APR'), 0, 3),
            'May' => substr($apx->lang->get('MONTH_MAY'), 0, 3),
            'Jun' => substr($apx->lang->get('MONTH_JUN'), 0, 3),
            'Jul' => substr($apx->lang->get('MONTH_JUL'), 0, 3),
            'Aug' => substr($apx->lang->get('MONTH_AUG'), 0, 3),
            'Sep' => substr($apx->lang->get('MONTH_SEP'), 0, 3),
            'Oct' => substr($apx->lang->get('MONTH_OCT'), 0, 3),
            'Nov' => substr($apx->lang->get('MONTH_NOV'), 0, 3),
            'Dec' => substr($apx->lang->get('MONTH_DEC'), 0, 3),
        ];
    }

    if (!isset($month_number)) {
        $month_number = [
            1 => $apx->lang->get('MONTH_JAN'),
            2 => $apx->lang->get('MONTH_FEB'),
            3 => $apx->lang->get('MONTH_MAR'),
            4 => $apx->lang->get('MONTH_APR'),
            5 => $apx->lang->get('MONTH_MAY'),
            6 => $apx->lang->get('MONTH_JUN'),
            7 => $apx->lang->get('MONTH_JUL'),
            8 => $apx->lang->get('MONTH_AUG'),
            9 => $apx->lang->get('MONTH_SEP'),
            10 => $apx->lang->get('MONTH_OCT'),
            11 => $apx->lang->get('MONTH_NOV'),
            12 => $apx->lang->get('MONTH_DEC'),
        ];
    }

    if (preg_match('#^[0-9]{1,2}$#', $string) && (int) $string >= 1 && (int) $string <= 12) {
        return $month_number[(int) $string];
    }

    return strtr($string, $month);
}

//Wochentag holen
function getweekday($string)
{
    global $apx;
    static $wday;

    if (!isset($wday)) {
        $wday = [
            'Monday' => $apx->lang->get('WEEKDAY_MON'),
            'Tuesday' => $apx->lang->get('WEEKDAY_TUE'),
            'Wednesday' => $apx->lang->get('WEEKDAY_WED'),
            'Thursday' => $apx->lang->get('WEEKDAY_THU'),
            'Friday' => $apx->lang->get('WEEKDAY_FRI'),
            'Saturday' => $apx->lang->get('WEEKDAY_SAT'),
            'Sunday' => $apx->lang->get('WEEKDAY_SUN'),
            'Mon' => substr($apx->lang->get('WEEKDAY_MON'), 0, 3),
            'Tue' => substr($apx->lang->get('WEEKDAY_TUE'), 0, 3),
            'Wed' => substr($apx->lang->get('WEEKDAY_WED'), 0, 3),
            'Thu' => substr($apx->lang->get('WEEKDAY_THU'), 0, 3),
            'Fri' => substr($apx->lang->get('WEEKDAY_FRI'), 0, 3),
            'Sat' => substr($apx->lang->get('WEEKDAY_SAT'), 0, 3),
            'Sun' => substr($apx->lang->get('WEEKDAY_SUN'), 0, 3),
        ];
    }

    return strtr($string, $wday);
}

////////////////////////////////////////////////////////////////////////////////// -> LINK

function mklink($link1, $link2, $secid = false)
{
    global $set,$apx;

    //Wenn keine Sektion übergeben, auwgewählte Sektion verwenden
    if (false === $secid) {
        $secid = $apx->section_id();
    }

    //Link auswählen
    if ($set['main']['staticsites']) {
        $link = $link2;
    } else {
        $link = $link1;
    }

    //Sektion gewählt
    if ($secid) {
        if ($set['main']['staticsites']) {
            if (1 == $set['main']['staticsites_virtual'] && isset($apx->sections[$secid]['virtual'])) {
                $virtual = $apx->sections[$secid]['virtual'].'/';
            }
            $link = HTTPDIR.$virtual.$link;
        } else {
            $link = HTTPDIR.$link;
            if (preg_match('#(\?|&|&amp;)sec=[0-9]+#', $link)) {
                return $link;
            }
            if (false !== strpos($link, '?')) {
                $link .= '&amp;sec='.$secid;
            } else {
                $link .= '?sec='.$secid;
            }
        }
    }

    //Keine Sektion gewählt
    else {
        $link = HTTPDIR.$link;
    }

    if ($set['main']['staticsites_separator']) {
        $link = str_replace(',', $set['main']['staticsites_separator'], $link);
    }

    return $link;
}

//Relativen Link ohne Sektionen
function mkrellink($link1, $link2, $secid = false)
{
    global $set,$apx;

    //Link auswählen
    if ($set['main']['staticsites']) {
        $link = $link2;
    } else {
        $link = $link1;
    }

    if ($set['main']['staticsites_separator']) {
        $link = str_replace(',', $set['main']['staticsites_separator'], $link);
    }

    return $link;
}

////////////////////////////////////////////////////////////////////////////////// -> STRING-FUNKTIONEN

//PHP 5.4 compatible htmlspecialchars
function compatible_hsc($text, $ent = ENT_COMPAT, $encoding = 'ISO-8859-1', $double_encode = true)
{
    return htmlspecialchars($text, $ent, $encoding, $double_encode);
}

//IP des Nutzers auslesen
function get_remoteaddr()
{
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $match)) {
        $ipaddr = false;
        foreach ($match[0] as $thisip) {
            if (!preg_match('#^(10|172\\.16|192\\.168)\\.#', $thisip)) {
                $ipaddr = $thisip;

                break;
            }
        }
        if ($ipaddr) {
            return $ipaddr;
        }
    }

    if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('#^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$#s', $_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    if (isset($_SERVER['HTTP_FROM']) && preg_match('#^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$#s', $_SERVER['HTTP_FROM'])) {
        return $_SERVER['HTTP_FROM'];
    }

    return $_SERVER['REMOTE_ADDR'];
}

//Sonderzeichen ersetzen
function replace($text, $br = false)
{
    global $set;

    $text = compatible_hsc($text, ENT_COMPAT | ENT_HTML401, 'ISO8859-1');

    //Maskierte Sonderzeichen zurücksetzen
    $text = preg_replace('#&amp;([\#0-9a-z]+);#', '&$1;', $text);

    if ($br) {
        $text = nl2br($text);
    } else {
        $text = strtr($text, ["\n" => ' ', "\r" => '']);
    }

    return $text;
}

//Addslashes für MYSQL-Like
function addslashes_like($text)
{
    $text = addslashes($text);
    $text = str_replace('%', '\\%', $text);

    return str_replace('_', '\\_', $text);
}

//Zufalls-Zeichkette
function random_string($len = 10, $keyspace = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
{
    mt_srand((float) microtime() * 1000000);
    while (strlen($key) < $len) {
        $key .= substr($keyspace, mt_rand(0, strlen($keyspace) - 1), 1);
    }

    return $key;
}

//Text kürzen
function shorttext($text, $count)
{
    $count = (int) $count;
    $rankey = random_string();
    $makespace = [
        '<br>' => ' ',
        '<br/>' => ' ',
        '<br />' => ' ',
        '<p>' => ' ',
        '</p>' => ' ',
    ];

    $text = strtr($text, $makespace);
    $text = trim(strip_tags($text));
    if (strlen($text) <= $count) {
        return $text;
    }
    $text = preg_replace('#\s+#', ' ', $text);

    $splitmark = explode('#WORDSPLIT-'.$rankey.'#', wordwrap($text, $count, '#WORDSPLIT-'.$rankey.'#', 1));
    $text = array_shift($splitmark);

    return $text.' ...';
}

//Syntax einer eMail Adresse prüfen
function checkmail($string)
{
    if (preg_match('#^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$#i', $string)) {
        return true;
    }

    return false;
}

//Email verschlüsseln
function cryptMail($mail)
{
    $n = 0;
    $r = '';
    $s = 'mailto:'.$mail;
    for ($i = 0; $i < strlen($s); ++$i) {
        $n = ord($s[$i]);
        if ($n >= 8364) {
            $n = 128;
        }
        //$r .= chr($n-3);
        $r .= '&#'.($n - 3).';';
    }

    return "javascript:linkUncryptedMail('".$r."');";
}

//URL-Format
function urlformat($text, $connector = ',')
{
    global $set;
    if (!$set['main']['keywords']) {
        return '';
    }
    $text = strtolower(trim(strip_tags($text)));

    $replace = [
        '¢' => ' cent',
        // '£' => ' pound',
        '£' => ' pfund',
        '¥' => ' yen',
        '$' => ' dollar',
        '€' => ' euro',
        // '%' => ' percent',
        '%' => ' prozent',
        '#' => '',
        '.' => ' ',
        ':' => ' ',
        ',' => ' ',
        ';' => ' ',
        '!' => '',
        '?' => '',
        '&' => ' ',
        '§' => '',
        '-' => ' ',
        '+' => ' ',
        '_' => ' ',
        '/' => ' ',
        '\\' => ' ',
        '*' => '',
        '=' => ' ',
        '\'' => '',
        '"' => '',
        '`' => '',
        '´' => '',
        '~' => ' ',
        '(' => '',
        ')' => '',
        '[' => '',
        ']' => '',
        '{' => '',
        '}' => '',
        '^' => ' ',
        '°' => '',
        '<' => ' ',
        '>' => ' ',
        '|' => ' ',
        '¡' => 'i',
        'À' => 'a',
        'Á' => 'a',
        'Â' => 'a',
        'Ã' => 'a',
        'Ä' => 'ae',
        'Å' => 'a',
        'Æ' => 'ae',
        'Ç' => 'c',
        'È' => 'e',
        'É' => 'e',
        'Ê' => 'e',
        'Ë' => 'e',
        'Ì' => 'i',
        'Í' => 'i',
        'Î' => 'i',
        'Ï' => 'i',
        'Ñ' => 'n',
        'Ò' => 'o',
        'Ó' => 'o',
        'Ô' => 'o',
        'Õ' => 'o',
        'Ö' => 'oe',
        'Ø' => 'o',
        'Ù' => 'u',
        'Ú' => 'u',
        'Û' => 'u',
        'Ü' => 'ue',
        'Ý' => 'Y',
        'ß' => 'ss',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'ae',
        'å' => 'a',
        'æ' => 'a',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'oe',
        'ø' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ü' => 'ue',
        'ý' => 'y',
        'ÿ' => 'y',
    ];

    $remove = [
        'der', 'die', 'das', 'den', 'des', 'ist', 'fuer', 'im', 'in', 'zu', 'zur', 'zum', 'und', 'von', 'vom', 'auf', 'an', 'ein', 'einer', 'eines', 'eine', 'per', 'mit',
        'the', 'for', 'to', 'into', 'of', 'and', 'a', 'an', 'by',
    ];

    //Sonderzeichen ersetzen
    $text = strtr($text, $replace);

    //Wörter aussortieren
    $text = preg_replace('#( ){2,}#', ' ', trim($text));
    $words = explode(' ', $text);
    foreach ($words as $key => $word) {
        $word = urlencode($word);
        if (preg_match('#[a-z0-9]*%[0-9]{2}[a-z0-9]*#', $word)) {
            unset($words[$key]);
        }
        if (in_array($word, $remove)) {
            unset($words[$key]);
        }
    }

    if ($set['main']['keywords_separator']) {
        $wordconn = $set['main']['keywords_separator'];
    } else {
        $wordconn = '_';
    }
    $text = implode($wordconn, $words);

    return iif($text, $connector).$text;
}

//Wörter aus einem Text filtern
function extract_words($text)
{
    $text = strip_tags($text); //HTML-Tags entfernen
    $text = preg_replace('#\s+#si', ' ', $text); //Whitespace zu Spaces
    $text = preg_replace('#[.,;:?!={}<>§%~+*°\^\|/_"\'\(\)\[\]\\&\#\-\$]+#s', ' ', $text); //Zeichen zu Spaces

    //Wörter trennen
    $text = preg_replace('#[\s]{2,}#', ' ', $text); //Whitespace auf 1 Zeichen reduzieren

    return preg_split('#[\s]+#', $text); //An Spaces teilen
}

//IP-Adresse in Float umwandeln
function ip2float($ip)
{
    $ip = preg_replace('/(\d{1,3})\.?/e', 'sprintf("%03d", \1)', $ip);

    return (float) $ip;
}

//Float zu IP-Adresse umwandeln
function float2ip($float)
{
    $float = (string) $float;
    $parts = [
        intval(substr($float, 0, strlen($float) - 9)),
        intval(substr($float, -9, 3)),
        intval(substr($float, -6, 3)),
        intval(substr($float, -3)),
    ];

    return $parts[0].'.'.$parts[1].'.'.$parts[2].'.'.$parts[3];
}

//IP in eine Zahl konvertieren
function ip2integer($ipaddress)
{
    if ('' == $ipaddress) {
        return 0;
    }

    $ips = explode('.', $ipaddress);

    return $ips[3] + $ips[2] * 256 + $ips[1] * 65536 + $ips[0] * 16777216;
}

//Wordwrap für HTML
function wordwrapHTML($str, $cols = 75, $break = "\n")
{
    $len = strlen($str);
    $tag = 0;
    $result = '';
    $wordlen = 0;
    for ($i = 0; $i < $len; ++$i) {
        $chr = substr($str, $i, 1);

        if ('<' == $chr) {
            ++$tag;
        } elseif ('>' == $chr) {
            --$tag;
        } elseif (!$tag && ctype_space($chr)) {
            $wordlen = 0;
        } elseif (!$tag) {
            ++$wordlen;
        }

        if (!$tag && $wordlen && !($wordlen % $cols)) {
            $chr .= $break;
        }

        $result .= $chr;
    }

    return $result;
}

////////////////////////////////////////////////////////////////////////////////// -> EMAIL VERSENDEN

function sendmail($email, $langid, $input = [], $title = '', $text = '', $sender = [])
{
    global $apx,$set;

    //Überschreiben mit Langpack
    if ($langid) {
        $title = $apx->lang->get('MAIL_'.strtoupper($langid).'_TITLE');
        $text = $apx->lang->get('MAIL_'.strtoupper($langid).'_TEXT');
        if (!$title) {
            die('email: title "'.'MAIL_'.strtoupper($langid).'_TITLE'.'" not found in langpack!');
        }
        if (!$text) {
            die('email: text "'.'MAIL_'.strtoupper($langid).'_TEXT'.'" not found in langpack!');
        }
    }

    //Vars einparsen
    if (is_array($input) && count($input)) {
        foreach ($input as $find => $replace) {
            $text = str_replace('{'.$find.'}', $replace, $text);
            $title = str_replace('{'.$find.'}', $replace, $title);
        }
    }

    //Absender
    if (is_array($sender) && count($sender)) {
        $from = 'From:'.$sender[0].'<'.$sender[1].'>';
    } else {
        if ($set['main']['mailbotname']) {
            $from = 'From:'.$set['main']['mailbotname'].'<'.$set['main']['mailbot'].'>';
        } else {
            $from = 'From:'.$set['main']['mailbot'];
        }
    }

    //echo $text;

    //Mail senden
    if (!mail($email, $title, $text, $from)) {
        echo 'can not send email!';
    }
}

/////////////////////////////////////////////////////////////////////////////// DASHLISTEN ETC.

//Serialize mit Strich
function dash_serialize($array)
{
    if (!count($array) || !is_array($array)) {
        return '|';
    }

    return '|'.implode('|', $array).'|';
}

//Unserialize mit Strich
function dash_unserialize($string)
{
    if ('|' == $string) {
        return [];
    }
    if ('|' != $string[0] || '|' != $string[strlen($string) - 1]) {
        return [];
    }
    $string = substr($string, 1, strlen($string) - 2);

    return explode('|', $string);
}

//Integer-Liste zu Array umwandeln
function intlist($info, $separator = ',')
{
    $list = [];
    $pp = explode($separator, $info);
    foreach ($pp as $element) {
        $element = (int) $element;
        if ($element) {
            $list[] = $element;
        }
    }

    return $list;
}

////////////////////////////////////////////////////////////////////////////////// -> ARRAY FUNKTIONEN

$array_sort_by_index = '';

//Array sortieren
function array_sort($array, $index, $order)
{
    global $array_sort_by_index;
    $order = strtolower($order);

    $array_sort_by_index = $index;

    if ('desc' == $order) {
        uasort($array, 'array_sort_desc');
    } else {
        uasort($array, 'array_sort_asc');
    }

    return $array;
}

//Sortierfunktion ASC
function array_sort_asc($a, $b)
{
    global $array_sort_by_index;
    $index = &$array_sort_by_index;

    if ($a[$index] == $b[$index]) {
        return 0;
    }

    return ($a[$index] > $b[$index]) ? 1 : -1;
}

//Sortierfunktion DESC
function array_sort_desc($a, $b)
{
    global $array_sort_by_index;
    $index = &$array_sort_by_index;

    if ($a[$index] == $b[$index]) {
        return 0;
    }

    return ($a[$index] < $b[$index]) ? 1 : -1;
}

//Vergleichsfunktion ASC
function exec_sortby_asc($a, $b)
{
    global $sortbykey;
    if ($a[$sortbykey] == $b[$sortbykey]) {
        return 0;
    }

    return ($a[$sortbykey] > $b[$sortbykey]) ? 1 : -1;
}

//Vergleichsfunktion DESC
function exec_sortby_desc($a, $b)
{
    global $sortbykey;
    if ($a[$sortbykey] == $b[$sortbykey]) {
        return 0;
    }

    return ($a[$sortbykey] < $b[$sortbykey]) ? 1 : -1;
}

//IDs auflisten
function get_ids($array, $key = 'id')
{
    if (!is_array($array)) {
        return [];
    }
    $list = [];
    foreach ($array as $element) {
        $list[] = $element[$key];
    }

    return array_unique($list);
}

//Höchster Key
function array_key_max($array)
{
    if (!is_array($array)) {
        return false;
    }
    foreach ($array as $key => $trash) {
        if (!is_int($key)) {
            continue;
        }
        if ($key > $max) {
            $max = $key;
        }
    }

    if (!isset($max)) {
        return false;
    }

    return $max;
}

////////////////////////////////////////////////////////////////////////////////// -> SEKTIONEN

//Sektions-String zu Array
function unserialize_section($info)
{
    if ('all' == $info) {
        return ['all'];
    }
    $ss = explode('|', $info);

    $list = [];
    foreach ($ss as $id) {
        $id = (int) $id;
        if (!$id) {
            continue;
        }
        $list[] = $id;
    }

    return $list;
}

//Array zu Sektions-String
function serialize_section($info)
{
    if (!is_array($info) || 'all' == $info[0]) {
        return 'all';
    }
    $list = [];
    foreach ($info as $id) {
        $id = (int) $id;
        if (!$id) {
            continue;
        }
        $list[] = $id;
    }

    if (!count($list)) {
        return 'all';
    }

    return '|'.implode('|', $list).'|';
}

////////////////////////////////////////////////////////////////////////////////// -> MYSQL

//ORDER BY einer MySQL-Funktion holen
function getorder($info, $add = '', $pos = 1)
{
    if (!is_array($info) || !count($info)) {
        return '';
    }
    if (!$info[0]) {
        echo 'WARNING: No default "sort by" column defined!';
    }

    $sort = explode('.', $_REQUEST['sortby']);
    $sort[1] = strtoupper($sort[1]);
    if ('ASC' != $sort[1] && 'DESC' != $sort[1]) {
        $sort[1] = 'ASC';
    }
    if (!array_key_exists($sort[0], $info)) {
        $_REQUEST['sortby'] = $info[0].'.'.$info[$info[0]][1];
        $sort[0] = $info[0];
        $sort[1] = $info[$info[0]][1];
    }

    return ' ORDER BY '.iif($add && 1 == $pos, $add.', ').$info[$sort[0]][0].' '.$sort[1].iif($add && 2 == $pos, ', '.$add);
}

//MySQL LIMIT holen
function getlimit($epp = 0, $varname = 'p')
{
    global $set;

    if (!$epp && MODE == 'admin') {
        $epp = $set['main']['admin_epp'];
    }
    if (!$epp) {
        return '';
    }

    return ' LIMIT '.((intval($_REQUEST[$varname]) - 1) * $epp).','.$epp;
}

//SQL Dump splitten
function split_sql($mysql)
{
    $mysql = trim($mysql);
    $mysql = str_replace('`apx_', '`'.PRE.'_', $mysql);
    $mysql = str_replace("\r", '', $mysql);
    $queries = preg_split("#;\\s*\n#", $mysql);

    $newqueries = [];
    foreach ($queries as $ele) {
        $ele = trim($ele);
        if (!$ele) {
            continue;
        }
        $newqueries[] = $ele;
    }

    return $newqueries;
}

////////////////////////////////////////////////////////////////////////////////// -> NACHRICHTEN

//Message senden
function message($text, $link = false)
{
    global $set,$db,$apx;

    //Standard-Back-Message
    if ('back' == $text) {
        $text = $apx->lang->get('CORE_BACK');
        $link = 'javascript:history.back()';
    }

    //Standard-Back-Link
    if ('back' == $link) {
        $link = 'javascript:history.back()';
    }

    $apx->tmpl->loaddesign('message');
    if ($link) {
        $apx->tmpl->assign_static('REDIRECT', $link);
    }

    echo $text;
    if (MODE != 'admin') {
        require BASEDIR.'lib/_end.php';
    }
}

//Message aus Template senden
function tmessage($file, $input = [], $dir = false, $form = true)
{
    global $set,$db,$apx;

    if (is_array($input) && count($input)) {
        foreach ($input as $find => $replace) {
            $apx->tmpl->assign($find, $replace);
        }
    }

    if (MODE == 'admin') {
        $postto = $_SERVER['PHP_SELF'];
    } else {
        $postto = $_SERVER['REQUEST_URI'];
    }

    if ($form) {
        echo '<form action="'.$postto.'" method="post">';
    }
    $apx->tmpl->parse('msg_'.$file, $dir);
    if ($form) {
        echo '<input type="hidden" name="send" value="1" />';
        if (MODE == 'admin') {
            echo '<input type="hidden" name="action" value="'.$apx->module().'.'.$apx->action().'" />';
        }
        echo '</form>';
    }

    $apx->tmpl->loaddesign('message');
    if (MODE != 'admin') {
        require BASEDIR.'lib/_end.php';
    }
}

//Fehlermeldung ausgeben
function filenotfound()
{
    global $set,$apx;
    header('HTTP/1.0 404 Not Found');
    message($apx->lang->get('CORE_FILENOTFOUND'));
}

//Fehlermeldung ausgeben
function error($text, $die = false)
{
    global $set,$apx;
    if ($set['showerror']) {
        echo'<div class="error">'.$text.'</div>';
    }
    if ($set['errorreport']) {
        $apx->tmpl->errorreport .= '<div class="error">'.$text.'</div>';
    }
    if ($die) {
        exit;
    }
}

////////////////////////////////////////////////////////////////////////////////// -> SHELL

//Programm auf Konsole ausführen
function exec_shell($command)
{
    exec($command, $output, $returnval);
    if (is_array($output)) {
        $output = implode("\n", $output);
    }

    return [$output, $returnval];
}
