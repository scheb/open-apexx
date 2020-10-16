<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Installieren
if ( SETUPMODE=='install' ) {
	$mysql="
		CREATE TABLE `apx_stats` (
		  `daystamp` int(8) unsigned NOT NULL default '0',
		  `weekday` tinyint(1) unsigned NOT NULL,
		  `weekstamp` int(6) unsigned NOT NULL default '0',
		  `time` int(11) unsigned NOT NULL default '0',
		  `uniques` int(11) unsigned NOT NULL default '0',
		  `uniques_0h` int(11) unsigned NOT NULL default '0',
		  `uniques_1h` int(11) unsigned NOT NULL default '0',
		  `uniques_2h` int(11) unsigned NOT NULL default '0',
		  `uniques_3h` int(11) unsigned NOT NULL default '0',
		  `uniques_4h` int(11) unsigned NOT NULL default '0',
		  `uniques_5h` int(11) unsigned NOT NULL default '0',
		  `uniques_6h` int(11) unsigned NOT NULL default '0',
		  `uniques_7h` int(11) unsigned NOT NULL default '0',
		  `uniques_8h` int(11) unsigned NOT NULL default '0',
		  `uniques_9h` int(11) unsigned NOT NULL default '0',
		  `uniques_10h` int(11) unsigned NOT NULL default '0',
		  `uniques_11h` int(11) unsigned NOT NULL default '0',
		  `uniques_12h` int(11) unsigned NOT NULL default '0',
		  `uniques_13h` int(11) unsigned NOT NULL default '0',
		  `uniques_14h` int(11) unsigned NOT NULL default '0',
		  `uniques_15h` int(11) unsigned NOT NULL default '0',
		  `uniques_16h` int(11) unsigned NOT NULL default '0',
		  `uniques_17h` int(11) unsigned NOT NULL default '0',
		  `uniques_18h` int(11) unsigned NOT NULL default '0',
		  `uniques_19h` int(11) unsigned NOT NULL default '0',
		  `uniques_20h` int(11) unsigned NOT NULL default '0',
		  `uniques_21h` int(11) unsigned NOT NULL default '0',
		  `uniques_22h` int(11) unsigned NOT NULL default '0',
		  `uniques_23h` int(11) unsigned NOT NULL default '0',
		  `hits` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`daystamp`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_stats_iplog` (
		  `ip` int(11) unsigned NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`ip`,`time`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_stats_referer` (
		  `daystamp` int(6) unsigned NOT NULL default '0',
		  `time` int(11) unsigned NOT NULL default '0',
		  `host` tinytext NOT NULL,
		  `url` text NOT NULL,
		  `hash` varchar(32) NOT NULL,
		  `searchstring` tinytext NOT NULL,
		  `hits` int(11) unsigned NOT NULL default '0',
		  KEY `daystamp` (`daystamp`),
		  KEY `hash` (`hash`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_stats_userenv` (
		  `daystamp` int(8) unsigned NOT NULL default '0',
		  `type` enum('browser','os','country') NOT NULL default 'browser',
		  `value` varchar(32) NOT NULL,
		  `hits` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`daystamp`,`type`,`value`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_cron` VALUES ('stats_clean', 'stats', 86400, 1190070000, '');
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('stats', 'referer_filter', 'array', 'BLOCK', 'a:0:{}', '', 0, 0),
		
		('stats', 'startcount', 'int', '', '0', '', 1151948463, 1000),
		('stats', 'blockip', 'int', '', '24', '', 1151948463, 2000),
		('stats', 'cookie', 'switch', '', '1', '', 1151948463, 3000),
		('stats', 'countsearchengine', 'switch', '', '0', '', 1151948463, 4000),
		('stats', 'ownreferer', 'switch', '', '0', '', 1151948463, 5000);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_stats`;
		DROP TABLE `apx_stats_iplog`;
		DROP TABLE `apx_stats_referer`;
		DROP TABLE `apx_stats_userenv`;
		DELETE FROM `apx_cron` WHERE funcname='stats_clean';
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				ALTER TABLE `apx_stats` ADD `hits` INT( 11 ) UNSIGNED NOT NULL;
				DROP TABLE `apx_stats_hits`;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 101: //zu 1.0.2
			$mysql="
				INSERT INTO `apx_config` VALUES ('stats', 'referer_filter', 'array', 'BLOCK', '', '0', '0');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 102: //zu 1.0.3
			$mysql="
				ALTER TABLE `apx_stats_referer` ADD `hash` VARCHAR( 32 ) NOT NULL AFTER `url` ;
				UPDATE `apx_stats_referer` SET hash = MD5( url ) ;
				ALTER TABLE `apx_stats_referer` ADD INDEX ( `daystamp` ) ;
				ALTER TABLE `apx_stats_referer` ADD INDEX ( `hash` ) ;
				INSERT INTO `apx_cron` VALUES ('stats_clean', 'stats', 86400, 1190070000, '');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 103: //zu 1.0.4
			$mysql="
				ALTER TABLE `apx_stats_userenv` CHANGE `value` `value` VARCHAR( 32 ) NOT NULL;
				ALTER TABLE `apx_stats_userenv` ADD INDEX ( `daystamp` , `type` , `value` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 104: //zu 1.0.5
			$mysql="
				ALTER TABLE `apx_stats` ADD `weekday` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `daystamp` ;
				UPDATE `apx_stats` SET weekday = WEEKDAY(CONCAT(LEFT(daystamp,4),'-',SUBSTR(daystamp,5,2),'-',SUBSTR(daystamp,7,2)));
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 105: //zu 1.1.0
			
			//Indizes entfernen
			clearIndices(PRE.'_stats_iplog');
			clearIndices(PRE.'_stats_referer');
			clearIndices(PRE.'_stats_userenv');
			
			//config Update
			updateConfig('stats', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('stats', 'referer_filter', 'array', 'BLOCK', 'a:0:{}', '', 0, 0),
				
				('stats', 'startcount', 'int', '', '0', '', 1151948463, 1000),
				('stats', 'blockip', 'int', '', '24', '', 1151948463, 2000),
				('stats', 'cookie', 'switch', '', '1', '', 1151948463, 3000),
				('stats', 'countsearchengine', 'switch', '', '0', '', 1151948463, 4000),
				('stats', 'ownreferer', 'switch', '', '0', '', 1151948463, 5000);
			");
			
			$mysql="
				ALTER TABLE `apx_stats_iplog` CHANGE `ip` `ip` INT( 11 ) UNSIGNED NOT NULL ;
				TRUNCATE TABLE `apx_stats_iplog` ;
				ALTER TABLE `apx_stats_iplog` ADD PRIMARY KEY ( `ip` , `time` ) ;
				
				ALTER TABLE `apx_stats_referer` ADD INDEX ( `daystamp` ) ;
				ALTER TABLE `apx_stats_referer` ADD INDEX ( `hash` ) ;
				
				CREATE TABLE `apx_stats_userenv_new` (
				  `daystamp` int(8) unsigned NOT NULL default '0',
				  `type` enum('browser','os','country') NOT NULL default 'browser',
				  `value` varchar(32) NOT NULL,
				  `hits` int(11) unsigned NOT NULL default '0',
				  PRIMARY KEY  (`daystamp`,`type`,`value`)
				) ENGINE=MyISAM;
				INSERT INTO `apx_stats_userenv_new` (
					SELECT daystamp, type, value, sum(hits) AS hits FROM `apx_stats_userenv` GROUP BY daystamp, type, value
				);
				DROP TABLE `apx_stats_userenv`;
				RENAME TABLE `apx_stats_userenv_new` TO `apx_stats_userenv` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
	}
}

?>