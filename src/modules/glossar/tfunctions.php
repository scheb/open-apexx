<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

require_once BASEDIR.getmodulepath('glossar').'functions.php';

//Alle Begriffe alphabetisch
function glossar_alphabetical($count = 5, $start = 0, $catid = false, $letter = false, $template = 'alphabetical')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;

    //Buchstaben-Filter
    if ($letter) {
        $letter = glossar_letter($letter);
        if ('#' == $letter) {
            $letterfilter = ' AND title NOT REGEXP("^[a-zA-Z]")';
        } elseif ('A' == $letter) {
            $letterfilter = "( title LIKE 'A%' OR title LIKE 'Ä%' )";
        } elseif ('O' == $letter) {
            $letterfilter = "( title LIKE 'O%' OR title LIKE 'Ö%' )";
        } elseif ('U' == $letter) {
            $letterfilter = "( title LIKE 'U%' OR title LIKE 'Ü%' )";
        } else {
            $letterfilter = "title LIKE '".$letter."'%";
        }
    }

    $data = $db->fetch('SELECT * FROM '.PRE."_glossar WHERE ( starttime!='0' ".iif($catid, " AND catid='".$catid."'").' '.iif($letterfilter, ' AND '.$letterfilter).' ) ORDER BY title ASC LIMIT '.iif($start, $start.',').$count);

    glossar_print($data, 'functions/'.$template, true);
}

//Letzte Begriffe
function glossar_last($count = 5, $start = 0, $catid = false, $letter = false, $template = 'last')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;

    //Buchstaben-Filter
    if ($letter) {
        $letter = glossar_letter($letter);
        if ('#' == $letter) {
            $letterfilter = ' AND title NOT REGEXP("^[a-zA-Z]")';
        } elseif ('A' == $letter) {
            $letterfilter = "( title LIKE 'A%' OR title LIKE 'Ä%' )";
        } elseif ('O' == $letter) {
            $letterfilter = "( title LIKE 'O%' OR title LIKE 'Ö%' )";
        } elseif ('U' == $letter) {
            $letterfilter = "( title LIKE 'U%' OR title LIKE 'Ü%' )";
        } else {
            $letterfilter = "title LIKE '".$letter."'%";
        }
    }

    $data = $db->fetch('SELECT * FROM '.PRE."_glossar WHERE ( starttime!='0' ".iif($catid, " AND catid='".$catid."'").' '.iif($letterfilter, ' AND '.$letterfilter).' ) ORDER BY starttime DESC LIMIT '.iif($start, $start.',').$count);

    glossar_print($data, 'functions/'.$template);
}

//Beste Begriffe: Hits
function glossar_best_hits($count = 5, $start = 0, $catid = false, $letter = false, $template = 'best_hits')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;

    //Buchstaben-Filter
    if ($letter) {
        $letter = glossar_letter($letter);
        if ('#' == $letter) {
            $letterfilter = ' AND title NOT REGEXP("^[a-zA-Z]")';
        } elseif ('A' == $letter) {
            $letterfilter = "( title LIKE 'A%' OR title LIKE 'Ä%' )";
        } elseif ('O' == $letter) {
            $letterfilter = "( title LIKE 'O%' OR title LIKE 'Ö%' )";
        } elseif ('U' == $letter) {
            $letterfilter = "( title LIKE 'U%' OR title LIKE 'Ü%' )";
        } else {
            $letterfilter = "title LIKE '".$letter."'%";
        }
    }

    $data = $db->fetch('SELECT * FROM '.PRE."_glossar WHERE ( starttime!='0' ".iif($catid, " AND catid='".$catid."'").' '.iif($letterfilter, ' AND '.$letterfilter).' ) ORDER BY hits DESC LIMIT '.iif($start, $start.',').$count);

    glossar_print($data, 'functions/'.$template);
}

//Beste Begriffe: Bewertung
function glossar_best_rating($count = 5, $start = 0, $catid = false, $letter = false, $template = 'best_rating')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;
    if (!$apx->is_module('ratings')) {
        return '';
    }
    //Buchstaben-Filter
    if ($letter) {
        $letter = glossar_letter($letter);
        if ('#' == $letter) {
            $letterfilter = ' AND title NOT REGEXP("^[a-zA-Z]")';
        } elseif ('A' == $letter) {
            $letterfilter = "( title LIKE 'A%' OR title LIKE 'Ä%' )";
        } elseif ('O' == $letter) {
            $letterfilter = "( title LIKE 'O%' OR title LIKE 'Ö%' )";
        } elseif ('U' == $letter) {
            $letterfilter = "( title LIKE 'U%' OR title LIKE 'Ü%' )";
        } else {
            $letterfilter = "title LIKE '".$letter."'%";
        }
    }

    $data = $db->fetch('SELECT avg(rating) AS rating,count(*) AS votes,b.* FROM '.PRE.'_ratings AS a LEFT JOIN '.PRE."_glossar AS b ON a.mid=b.id AND a.module='glossar' WHERE ( starttime!='0' ".iif($catid, " AND catid='".$catid."'").' '.iif($letterfilter, ' AND '.$letterfilter).' ) GROUP BY a.mid ORDER BY rating DESC,votes DESC LIMIT '.iif($start, $start.',').$count);

    glossar_print($data, 'functions/'.$template);
}

//Zufällige Begriffe
function glossar_random($count = 5, $start = 0, $catid = false, $letter = false, $template = 'random')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;

    //Buchstaben-Filter
    if ($letter) {
        $letter = glossar_letter($letter);
        if ('#' == $letter) {
            $letterfilter = ' AND title NOT REGEXP("^[a-zA-Z]")';
        } elseif ('A' == $letter) {
            $letterfilter = "( title LIKE 'A%' OR title LIKE 'Ä%' )";
        } elseif ('O' == $letter) {
            $letterfilter = "( title LIKE 'O%' OR title LIKE 'Ö%' )";
        } elseif ('U' == $letter) {
            $letterfilter = "( title LIKE 'U%' OR title LIKE 'Ü%' )";
        } else {
            $letterfilter = "title LIKE '".$letter."'%";
        }
    }

    $data = $db->fetch('SELECT * FROM '.PRE."_glossar WHERE ( starttime!='0' ".iif($catid, " AND catid='".$catid."'").' '.iif($letterfilter, ' AND '.$letterfilter).' ) ORDER BY RAND() LIMIT '.iif($start, $start.',').$count);

    glossar_print($data, 'functions/'.$template);
}

//Ähnliche Begriffe
function glossar_similar($tagids = [], $count = 5, $start = 0, $catid = false, $letter = false, $template = 'similar')
{
    global $set,$db,$apx,$user;
    $count = (int) $count;
    $start = (int) $start;

    if (!is_array($tagids)) {
        $tagids = getTagIds(strval($tagids));
    }
    $ids = glossar_search_tags($tagids);
    $ids[] = -1;
    $tagfilter = ' AND id IN ('.implode(', ', $ids).') ';

    //Buchstaben-Filter
    if ($letter) {
        $letter = glossar_letter($letter);
        if ('#' == $letter) {
            $letterfilter = ' AND title NOT REGEXP("^[a-zA-Z]")';
        } elseif ('A' == $letter) {
            $letterfilter = "( title LIKE 'A%' OR title LIKE 'Ä%' )";
        } elseif ('O' == $letter) {
            $letterfilter = "( title LIKE 'O%' OR title LIKE 'Ö%' )";
        } elseif ('U' == $letter) {
            $letterfilter = "( title LIKE 'U%' OR title LIKE 'Ü%' )";
        } else {
            $letterfilter = "title LIKE '".$letter."'%";
        }
    }

    $data = $db->fetch('SELECT * FROM '.PRE."_glossar WHERE ( starttime!='0' ".iif($catid, " AND catid='".$catid."'").' '.iif($letterfilter, ' AND '.$letterfilter).' '.$tagfilter.' ) ORDER BY starttime DESC LIMIT '.iif($start, $start.',').$count);

    glossar_print($data, 'functions/'.$template);
}

//AUSGABE
function glossar_print($data, $template, $alphabetical = false)
{
    global $set,$db,$apx,$user;
    $tmpl = new tengine();

    $apx->lang->drop('func', 'glossar');

    //Verwendete Variablen auslesen
    $parse = $apx->tmpl->used_vars($template, 'glossar');

    //Kategorien auslesen & Info vorbereiten
    if (in_array('INDEX.CATID', $parse) || in_array('INDEX.CATTITLE', $parse) || in_array('INDEX.CATTEXT', $parse) || in_array('INDEX.CATICON', $parse)) {
        $catids = get_ids($data, 'catid');
        if (count($catids)) {
            $catdata = $db->fetch('SELECT * FROM '.PRE.'_glossar_cat WHERE id IN ('.implode(',', $catids).')');
            foreach ($catdata as $res) {
                $catinfo[$res['id']] = $res;
                $catinfo[$res['id']]['link'] = mklink(
                    'glossar.php?catid='.$res['id'],
                    'glossar,'.$res['id'].',0,1'.urlformat($res['title']).'.html'
                );
            }
        }
    }

    //Begriffe auflisten
    if (count($data)) {
        //Nach Buchstaben sortieren
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ#';
        for ($i = 0; $i < strlen($letters); ++$i) {
            $index[$letters[$i]] = [];
        }

        if ($alphabetical) {
            foreach ($data as $res) {
                $letter = glossar_letter($res['title']);
                $index[$letter][] = $res;
            }
        } else {
            foreach ($data as $res) {
                $index[0][] = $res;
            }
        }

        //Index erstellen
        foreach ($index as $letter => $data) {
            //Link: Nur Begriffe mit diesem Buchstaben
            $letterlink = mklink(
                'glossar.php?catid='.$_REQUEST['catid'].'&amp;letter='.iif('#' == $letter, 'spchar', strtolower($letter)),
                'glossar,'.$_REQUEST['catid'].','.iif('#' == $letter, 'spchar', strtolower($letter)).',1'.urlformat($catinfo['title']).'.html'
            );

            foreach ($data as $res) {
                ++$i;

                //Link
                $link = mklink(
                    'glossar.php?id='.$res['id'],
                    'glossar,id'.$res['id'].urlformat($res['title']).'.html'
                );

                //Tags
                if (in_array('INDEX.TAG', $parse) || in_array('INDEX.TAG_IDS', $parse) || in_array('INDEX.KEYWORDS', $parse)) {
                    list($tagdata, $tagids, $keywords) = glossar_tags($res['id']);
                }

                $tabledata[$i]['LETTER'] = $letter;
                $tabledata[$i]['LETTERLINK'] = $letterlink;

                $tabledata[$i]['TITLE'] = $res['title'];
                $tabledata[$i]['TEXT'] = $res['text'];
                $tabledata[$i]['SPELLING'] = $res['spelling'];
                $tabledata[$i]['LINK'] = $link;
                $tabledata[$i]['TIME'] = $res['starttime'];
                $tabledata[$i]['HITS'] = number_format($res['hits'], 0, '', '.');

                //Tags
                $tabledata[$i]['TAG'] = $tagdata;
                $tabledata[$i]['TAG_IDS'] = $tagids;
                $tabledata[$i]['KEYWORDS'] = $keywords;

                $tabledata[$i]['CATID'] = $catinfo[$res['catid']]['id'];
                $tabledata[$i]['CATTITLE'] = $catinfo[$res['catid']]['title'];
                $tabledata[$i]['CATTEXT'] = $catinfo[$res['catid']]['text'];
                $tabledata[$i]['CATICON'] = $catinfo[$res['catid']]['icon'];
                $tabledata[$i]['CATLINK'] = $catinfo[$res['catid']]['link'];

                //Kommentare
                if ($apx->is_module('comments') && $set['glossar']['coms'] && $res['allowcoms']) {
                    require_once BASEDIR.getmodulepath('comments').'class.comments.php';
                    if (!isset($coms)) {
                        $coms = new comments('glossar', $res['id']);
                    } else {
                        $coms->mid = $res['id'];
                    }

                    $link = mklink(
                        'glossar.php?id='.$res['id'],
                        'glossar,id'.$res['id'].urlformat($res['title']).'.html'
                    );

                    $tabledata[$i]['COMMENT_COUNT'] = $coms->count();
                    $tabledata[$i]['COMMENT_LINK'] = $coms->link($link);
                    $tabledata[$i]['DISPLAY_COMMENTS'] = 1;
                    if (in_template(['INDEX.COMMENT_LAST_USERID', 'INDEX.COMMENT_LAST_NAME', 'INDEX.COMMENT_LAST_TIME'], $parse)) {
                        $tabledata[$i]['COMMENT_LAST_USERID'] = $coms->last_userid();
                        $tabledata[$i]['COMMENT_LAST_NAME'] = $coms->last_name();
                        $tabledata[$i]['COMMENT_LAST_TIME'] = $coms->last_time();
                    }
                }

                //Bewertungen
                if ($apx->is_module('ratings') && $set['glossar']['ratings'] && $res['allowrating']) {
                    require_once BASEDIR.getmodulepath('ratings').'class.ratings.php';
                    if (!isset($rate)) {
                        $rate = new ratings('glossar', $res['id']);
                    } else {
                        $rate->mid = $res['id'];
                    }

                    $tabledata[$i]['RATING'] = $rate->display();
                    $tabledata[$i]['RATING_VOTES'] = $rate->count();
                    $tabledata[$i]['DISPLAY_RATING'] = 1;
                }
            }
        }
    }

    $tmpl->assign('INDEX', $tabledata);
    $tmpl->parse($template, 'glossar');
}

//Tags auflisten
function glossar_tagcloud($count = 10, $random = false, $template = 'tagcloud')
{
    global $set,$db,$apx,$user;
    $tmpl = new tengine();
    $catid = (int) $catid;

    if ($random) {
        $orderby = 'RAND()';
    } else {
        $orderby = 'weight DESC';
    }

    //Sektion gewählt
    $data = $db->fetch('
		SELECT t.tagid, t.tag, count(nt.id) AS weight
		FROM '.PRE.'_glossar_tags AS nt
		LEFT JOIN '.PRE.'_tags AS t ON nt.tagid=t.tagid
		LEFT JOIN '.PRE.'_glossar_tags AS nt2 ON nt.tagid=nt2.tagid
		GROUP BY nt.tagid
		ORDER BY '.$orderby.'
		LIMIT '.$count.'
	');

    if (count($data)) {
        $maxweight = 1;
        foreach ($data as $res) {
            if ($res['weight'] > $maxweight) {
                $maxweight = $res['weight'];
            }
        }
        if (!$random) {
            shuffle($data);
        }
        foreach ($data as $res) {
            $tagdata[] = [
                'ID' => $res['tagid'],
                'NAME' => replace($res['tag']),
                'WEIGHT' => $res['weight'] / $maxweight,
            ];
        }
    }

    $tmpl->assign('TAG', $tagdata);
    $tmpl->parse('functions/'.$template, 'glossar');
}

//Statistik anzeigen
function glossar_stats($template = 'stats')
{
    global $set,$db,$apx,$user;
    $tmpl = new tengine();
    $parse = $tmpl->used_vars('functions/'.$template, 'glossar');

    $apx->lang->drop('func_stats', 'glossar');

    if (in_array('COUNT_CATEGORIES', $parse)) {
        list($count) = $db->first('SELECT count(id) FROM '.PRE.'_glossar_cat');
        $tmpl->assign('COUNT_CATEGORIES', $count);
    }
    if (in_template(['COUNT_GLOSSAR', 'AVG_HITS'], $parse)) {
        list($count, $hits) = $db->first('
			SELECT count(id), avg(hits) FROM '.PRE.'_glossar
			WHERE starttime!=0
		');
        $tmpl->assign('COUNT_GLOSSAR', $count);
        $tmpl->assign('AVG_HITS', round($hits));
    }

    $tmpl->parse('functions/'.$template, 'glossar');
}
