<?php

// Diverse Funktionen (Setup)
// ===========================

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Indizes entfernen
function clearIndices($table)
{
    global $db;
    $done = [];
    $data = $db->fetch('SHOW INDEX FROM `'.$table.'`');
    if (count($data)) {
        foreach ($data as $res) {
            if ('PRIMARY' != $res['Key_name'] && !in_array($res['Key_name'], $done)) {
                $db->query('ALTER TABLE `'.$table.'` DROP INDEX `'.$res['Key_name'].'`');
                $done[] = $res['Key_name'];
            }
        }
    }
}

//Rekursive Tabellen von Nested-Sets in neues Format konvertieren
function convertRecursiveTable($table)
{
    global $db;

    $db->query('
		ALTER TABLE `'.$table.'` ADD `parents` VARCHAR(255) NOT NULL ,
		ADD `children` TEXT NOT NULL ,
		ADD ord TINYINT(3) NOT NULL
	');

    $lastlevel = 0;
    $ord = [];
    $parents = [];
    $data = $db->fetch('SELECT a.id,count(*) AS level FROM '.$table.' AS a,'.$table.' AS b WHERE ( a.lft BETWEEN b.lft AND b.rgt ) GROUP BY a.lft');
    foreach ($data as $res) {
        while (count($parents) > $res['level'] - 1) {
            array_pop($parents);
        }
        if ($lastlevel < $res['level']) {
            $ord[$res['level']] = 0;
        } else {
            ++$ord[$res['level']];
        }

        //Eltern definieren
        $db->query('
			UPDATE '.$table."
			SET parents='".dash_serialize($parents)."', children='|', ord='".$ord[$res['level']]."'
			WHERE id='".$res['id']."'
		");

        //Knoten bei Eltern als Kindknoten hinzufügen
        if ($parents) {
            $db->query('
				UPDATE '.$table."
				SET children=CONCAT(children, '".$res['id']."|')
				WHERE id IN (".implode(',', $parents).')
			');
        }

        $parents[] = $res['id'];
        $lastlevel = $res['level'];
    }

    $db->query('
		ALTER TABLE `'.$table.'`
	  DROP `root_id`,
	  DROP `lft`,
	  DROP `rgt`;
	');
}

//Keywords in Tags umwandeln
function transformKeywords($table, $tagtable)
{
    global $db;

    $data = $db->fetch('SELECT id, keywords FROM '.$table." WHERE keywords!=''");
    foreach ($data as $res) {
        $tagids = produceTagIds($res['keywords']);
        foreach ($tagids as $tagid) {
            $db->query('
				INSERT IGNORE INTO '.$tagtable."
				VALUES ('".$res['id']."', '".$tagid."')
			");
        }
    }

    $db->query('
		ALTER TABLE `'.$table.'`
	  DROP `keywords`
	');
}

//Konfiguration aktualisieren
function updateConfig($module, $newsql)
{
    global $db, $set;

    //Aktuelle Werte auslesen
    $olddata = $db->fetch('SELECT varname, value FROM '.PRE."_config WHERE module='".addslashes($module)."'");

    //Alte Einträge entfernen und neue einfügen
    $db->query('DELETE FROM '.PRE."_config WHERE module='".addslashes($module)."'");
    $queries = split_sql($newsql);
    foreach ($queries as $query) {
        $db->query($query);
    }

    //Aktuelle Werte übernehmen
    if (count($olddata)) {
        foreach ($olddata as $res) {
            $db->query('
				UPDATE '.PRE."_config
				SET value='".addslashes($res['value'])."'
				WHERE module='".addslashes($module)."' AND varname='".addslashes($res['varname'])."'
			");
        }
    }
}
