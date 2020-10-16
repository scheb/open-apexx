<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $mysql = "
		CREATE TABLE `apx_navi` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `nid` tinyint(2) unsigned NOT NULL default '0',
		  `text` tinytext NOT NULL,
		  `link` text NOT NULL,
		  `link_popup` tinyint(1) unsigned NOT NULL default '0',
		  `code` text NOT NULL,
		  `staticsub` tinyint(1) NOT NULL default '0',
		  `parents` varchar(255) NOT NULL,
		  `children` text NOT NULL,
		  `ord` tinyint(3) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `parents` (`parents`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('navi', 'groups', 'array', 'BLOCK', 'a:5:{i:1;s:12:\"Navigation 1\";i:2;s:12:\"Navigation 2\";i:3;s:12:\"Navigation 3\";i:4;s:12:\"Navigation 4\";i:5;s:12:\"Navigation 5\";}', '', 0, 0);
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }
}

//Deinstallieren
elseif (SETUPMODE == 'uninstall') {
    $mysql = '
		DROP TABLE `apx_navi`;
	';
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }
}

//Update
elseif (SETUPMODE == 'update') {
    switch ($installed_version) {
        case 100: //zu 1.0.1

        case 101: //zu 1.0.2
            $mysql = '
				ALTER TABLE `apx_navi` ADD `link_popup` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `link` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 102: //zu 1.1.0

            //Indizes entfernen
            clearIndices(PRE.'_navi');

            //Tabellenformat ändern
            convertRecursiveTable(PRE.'_navi');

            //config Update
            updateConfig('navi', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('navi', 'groups', 'array', 'BLOCK', 'a:5:{i:1;s:12:\"Navigation 1\";i:2;s:12:\"Navigation 2\";i:3;s:12:\"Navigation 3\";i:4;s:12:\"Navigation 4\";i:5;s:12:\"Navigation 5\";}', '', 0, 0);
			");

            $mysql = '
				ALTER TABLE `apx_navi` ADD INDEX ( `parents` ) ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 110: //zu 1.1.1

            //Navigation 2 erzeugen
            list($check) = $db->first('SELECT id FROM '.PRE."_navi WHERE nid='2' LIMIT 1");
            if ($check && !isset($set['navi']['groups'][2])) {
                $set['navi']['groups'][2] = 'Navigation 2';
                $db->query('UPDATE '.PRE."_config SET value='".addslashes(serialize($set['navi']['groups']))."' WHERE module='navi' AND varname='groups' LIMIT 1");
            }
    }
}
