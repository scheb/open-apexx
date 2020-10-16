<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $mysql = "
		CREATE TABLE `apx_poll` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `secid` tinytext NOT NULL,
		  `question` text NOT NULL,
		  `meta_description` text NOT NULL,
		  `a1` tinytext NOT NULL,
		  `a2` tinytext NOT NULL,
		  `a3` tinytext NOT NULL,
		  `a4` tinytext NOT NULL,
		  `a5` tinytext NOT NULL,
		  `a6` tinytext NOT NULL,
		  `a7` tinytext NOT NULL,
		  `a8` tinytext NOT NULL,
		  `a9` tinytext NOT NULL,
		  `a10` tinytext NOT NULL,
		  `a11` tinytext NOT NULL,
		  `a12` tinytext NOT NULL,
		  `a13` tinytext NOT NULL,
		  `a14` tinytext NOT NULL,
		  `a15` tinytext NOT NULL,
		  `a16` tinytext NOT NULL,
		  `a17` tinytext NOT NULL,
		  `a18` tinytext NOT NULL,
		  `a19` tinytext NOT NULL,
		  `a20` tinytext NOT NULL,
		  `a1_c` int(4) unsigned NOT NULL default '0',
		  `a2_c` int(4) unsigned NOT NULL default '0',
		  `a3_c` int(4) unsigned NOT NULL default '0',
		  `a4_c` int(4) unsigned NOT NULL default '0',
		  `a5_c` int(4) unsigned NOT NULL default '0',
		  `a6_c` int(4) unsigned NOT NULL default '0',
		  `a7_c` int(4) unsigned NOT NULL default '0',
		  `a8_c` int(4) unsigned NOT NULL default '0',
		  `a9_c` int(4) unsigned NOT NULL default '0',
		  `a10_c` int(4) unsigned NOT NULL default '0',
		  `a11_c` int(11) unsigned NOT NULL default '0',
		  `a12_c` int(11) unsigned NOT NULL default '0',
		  `a13_c` int(11) unsigned NOT NULL default '0',
		  `a14_c` int(11) unsigned NOT NULL default '0',
		  `a15_c` int(11) unsigned NOT NULL default '0',
		  `a16_c` int(11) unsigned NOT NULL default '0',
		  `a17_c` int(11) unsigned NOT NULL default '0',
		  `a18_c` int(11) unsigned NOT NULL default '0',
		  `a19_c` int(11) unsigned NOT NULL default '0',
		  `a20_c` int(11) unsigned NOT NULL default '0',
		  `color1` varchar(20) NOT NULL default '',
		  `color2` varchar(20) NOT NULL default '',
		  `color3` varchar(20) NOT NULL default '',
		  `color4` varchar(20) NOT NULL default '',
		  `color5` varchar(20) NOT NULL default '',
		  `color6` varchar(20) NOT NULL default '',
		  `color7` varchar(20) NOT NULL default '',
		  `color8` varchar(20) NOT NULL default '',
		  `color9` varchar(20) NOT NULL default '',
		  `color10` varchar(20) NOT NULL default '',
		  `color11` varchar(20) NOT NULL default '',
		  `color12` varchar(20) NOT NULL default '',
		  `color13` varchar(20) NOT NULL default '',
		  `color14` varchar(20) NOT NULL default '',
		  `color15` varchar(20) NOT NULL default '',
		  `color16` varchar(20) NOT NULL default '',
		  `color17` varchar(20) NOT NULL default '',
		  `color18` varchar(20) NOT NULL default '',
		  `color19` varchar(20) NOT NULL default '',
		  `color20` varchar(20) NOT NULL default '',
		  `addtime` int(11) unsigned NOT NULL default '0',
		  `starttime` int(11) unsigned NOT NULL default '0',
		  `endtime` int(11) unsigned NOT NULL default '0',
		  `days` smallint(3) unsigned NOT NULL default '0',
		  `multiple` tinyint(1) unsigned NOT NULL default '0',
		  `searchable` tinyint(1) unsigned NOT NULL default '0',
		  `allowcoms` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `starttime` (`starttime`,`endtime`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_poll_iplog` (
		  `id` int(11) unsigned NOT NULL default '0',
		  `userid` int(11) unsigned NOT NULL,
		  `ip` int(11) unsigned NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  KEY `id` (`id`,`time`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_poll_tags` (
		  `id` int(11) unsigned NOT NULL,
		  `tagid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`,`tagid`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('poll', 'maxfirst', 'switch', '', '1', 'VIEW', 1164754067, 1000),
		('poll', 'archall', 'switch', '', '1', 'VIEW', 1164754067, 2000),
		('poll', 'barmaxwidth', 'int', '', '0', 'VIEW', 1164754067, 3000),
		('poll', 'percentdigits', 'int', '', '1', 'VIEW', 1164754067, 4000),
		
		('poll', 'searchable', 'switch', '', '1', 'OPTIONS', 1164754067, 5000),
		('poll', 'coms', 'switch', '', '1', 'OPTIONS', 1164754067, 6000),
		('poll', 'archcoms', 'switch', '', '1', 'OPTIONS', 1164754067, 7000),
		('poll', 'archvote', 'switch', '', '0', 'OPTIONS', 1164754067, 8000);
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }
}

//Deinstallieren
elseif (SETUPMODE == 'uninstall') {
    $mysql = '
		DROP TABLE `apx_poll`;
		DROP TABLE `apx_poll_iplog`;
		DROP TABLE `apx_poll_tags`;
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
				ALTER TABLE `apx_poll` ADD `starttime` INT( 11 ) UNSIGNED NOT NULL AFTER `addtime` ;
				ALTER TABLE `apx_poll` ADD `days` SMALLINT( 3 ) UNSIGNED NOT NULL AFTER `endtime` ;
				UPDATE `apx_poll` SET days=ROUND((endtime-addtime)/(24*3600)) ;
				UPDATE `apx_poll` SET starttime=addtime,endtime='3000000000' WHERE active=1 ;
				ALTER TABLE `apx_poll` DROP `active` ;
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 101: //zu 1.0.2
            $mysql = "
				INSERT INTO `apx_config` ( `module` , `varname` , `type` , `addnl` , `value` , `lastchange` , `ord` ) VALUES ('poll', 'searchable', 'switch', '', '1', '0', '50');
				ALTER TABLE `apx_poll` ADD `searchable` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `multiple` ;
				ALTER TABLE `apx_poll` ADD `color` TINYTEXT NOT NULL AFTER `a20_c` ;
				ALTER TABLE `apx_poll` ADD `color1` VARCHAR( 20 ) NOT NULL AFTER `a20_c` ,ADD `color2` VARCHAR( 20 ) NOT NULL AFTER `color1` ,ADD `color3` VARCHAR( 20 ) NOT NULL AFTER `color2` ,ADD `color4` VARCHAR( 20 ) NOT NULL AFTER `color3` ,ADD `color5` VARCHAR( 20 ) NOT NULL AFTER `color4` ,ADD `color6` VARCHAR( 20 ) NOT NULL AFTER `color5` ,ADD `color7` VARCHAR( 20 ) NOT NULL AFTER `color6` ,ADD `color8` VARCHAR( 20 ) NOT NULL AFTER `color7` ,ADD `color9` VARCHAR( 20 ) NOT NULL AFTER `color8` ,ADD `color10` VARCHAR( 20 ) NOT NULL AFTER `color9` ,ADD `color11` VARCHAR( 20 ) NOT NULL AFTER `color10` ,ADD `color12` VARCHAR( 20 ) NOT NULL AFTER `color11` ,ADD `color13` VARCHAR( 20 ) NOT NULL AFTER `color12` ,ADD `color14` VARCHAR( 20 ) NOT NULL AFTER `color13` ,ADD `color15` VARCHAR( 20 ) NOT NULL AFTER `color14` ,ADD `color16` VARCHAR( 20 ) NOT NULL AFTER `color15` ,ADD `color17` VARCHAR( 20 ) NOT NULL AFTER `color16` ,ADD `color18` VARCHAR( 20 ) NOT NULL AFTER `color17` ,ADD `color19` VARCHAR( 20 ) NOT NULL AFTER `color18` ,ADD `color20` VARCHAR( 20 ) NOT NULL AFTER `color19` ;
				ALTER TABLE `apx_poll` ADD `keywords` TINYTEXT NOT NULL AFTER `question` ;
				UPDATE `apx_poll` SET searchable='1';
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 102: //zu 1.0.3
            $mysql = "
				INSERT INTO `apx_config` VALUES ('poll', 'archall', 'switch', '', '0', '0', '700');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 103: //zu 1.0.4
            $mysql = '
				ALTER TABLE `apx_poll_iplog` ADD `userid` INT( 11 ) UNSIGNED NOT NULL AFTER `id` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 104: //zu 1.1.0

            //Indizes entfernen
            clearIndices(PRE.'_poll');

            //config Update
            updateConfig('poll', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('poll', 'maxfirst', 'switch', '', '1', 'VIEW', 1164754067, 1000),
				('poll', 'archall', 'switch', '', '1', 'VIEW', 1164754067, 2000),
				('poll', 'barmaxwidth', 'int', '', '0', 'VIEW', 1164754067, 3000),
				('poll', 'percentdigits', 'int', '', '1', 'VIEW', 1164754067, 4000),
				
				('poll', 'searchable', 'switch', '', '1', 'OPTIONS', 1164754067, 5000),
				('poll', 'coms', 'switch', '', '1', 'OPTIONS', 1164754067, 6000),
				('poll', 'archcoms', 'switch', '', '1', 'OPTIONS', 1164754067, 7000),
				('poll', 'archvote', 'switch', '', '0', 'OPTIONS', 1164754067, 8000);
			");

            $mysql = '
				CREATE TABLE `apx_poll_tags` (
				`id` INT( 11 ) UNSIGNED NOT NULL ,
				`tagid` INT( 11 ) UNSIGNED NOT NULL ,
				PRIMARY KEY ( `id` , `tagid` )
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_poll_iplog` CHANGE `ip` `ip` INT( 11 ) UNSIGNED NOT NULL ;
				TRUNCATE TABLE `apx_poll_iplog` ;
				
				ALTER TABLE `apx_poll` ADD INDEX ( `starttime` , `endtime` ) ;
				ALTER TABLE `apx_poll_iplog` ADD INDEX ( `id` , `time` ) ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            //Tags erzeugen
            transformKeywords(PRE.'_poll', PRE.'_poll_tags');

            // no break
        case 110: //zu 1.1.1
            $mysql = '
				ALTER TABLE `apx_poll` ADD `meta_description` TEXT NOT NULL AFTER `question` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }
    }
}
