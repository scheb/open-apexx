<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $mysql = "
		CREATE TABLE `apx_glossar` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `catid` int(11) unsigned NOT NULL default '0',
		  `title` tinytext NOT NULL,
		  `spelling` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `meta_description` text NOT NULL,
		  `addtime` int(11) unsigned NOT NULL default '0',
		  `starttime` int(11) unsigned NOT NULL default '0',
		  `searchable` tinyint(1) unsigned NOT NULL default '0',
		  `allowcoms` tinyint(1) unsigned NOT NULL default '0',
		  `allowrating` tinyint(1) unsigned NOT NULL default '0',
		  `hits` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `catid` (`catid`,`starttime`),
		  KEY `starttime` (`starttime`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_glossar_cat` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `icon` tinytext NOT NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_glossar_tags` (
		  `id` int(11) unsigned NOT NULL,
		  `tagid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`,`tagid`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('glossar', 'searchable', 'switch', '', '1', '', 1181326815, 1000),
		('glossar', 'epp', 'int', '', '0', '', 1181326815, 2000),
		('glossar', 'highlight', 'switch', '', '0', '', 1181326815, 3000),
		('glossar', 'coms', 'switch', '', '1', '', 1181326815, 4000),
		('glossar', 'ratings', 'switch', '', '1', '', 1181326815, 5000);
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }
}

//Deinstallieren
elseif (SETUPMODE == 'uninstall') {
    $mysql = '
		DROP TABLE `apx_glossar`;
		DROP TABLE `apx_glossar_cat`;
		DROP TABLE `apx_glossar_tags`;
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
            $mysql = "
				ALTER TABLE `apx_glossar` ADD `spelling` TINYTEXT NOT NULL AFTER `title` ;
				INSERT INTO `apx_config` VALUES ('glossar', 'highlight', 'switch', '', '', '0', '300');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 101: //zu 1.1.0

            //Indizes entfernen
            clearIndices(PRE.'_glossar');

            //config Update
            updateConfig('glossar', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('glossar', 'searchable', 'switch', '', '1', '', 1181326815, 1000),
				('glossar', 'epp', 'int', '', '0', '', 1181326815, 2000),
				('glossar', 'highlight', 'switch', '', '0', '', 1181326815, 3000),
				('glossar', 'coms', 'switch', '', '1', '', 1181326815, 4000),
				('glossar', 'ratings', 'switch', '', '1', '', 1181326815, 5000);
			");

            $mysql = '
				CREATE TABLE `apx_glossar_tags` (
					`id` INT( 11 ) UNSIGNED NOT NULL ,
					`tagid` INT( 11 ) UNSIGNED NOT NULL ,
					PRIMARY KEY ( `id` , `tagid` )
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_glossar` ADD INDEX ( `catid` , `starttime` ) ;
				ALTER TABLE `apx_glossar` ADD INDEX ( `starttime` ) ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            //Tags erzeugen
            transformKeywords(PRE.'_glossar', PRE.'_glossar_tags');

            // no break
        case 110: //zu 1.1.1
            $mysql = '
				ALTER TABLE `apx_glossar` ADD `meta_description` TEXT NOT NULL AFTER `text` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }
    }
}
