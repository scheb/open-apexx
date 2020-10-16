<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

//Installieren
if ( SETUPMODE=='install' ) {
	$mysql="
		CREATE TABLE `apx_banner` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `partner` tinytext NOT NULL,
		  `code` text NOT NULL,
		  `ratio` int(4) unsigned NOT NULL default '0',
		  `limit` int(11) unsigned NOT NULL default '0',
		  `views` int(11) unsigned NOT NULL default '0',
		  `capping` int(11) unsigned NOT NULL,
		  `group` tinyint(2) unsigned NOT NULL default '1',
		  `starttime` int(11) unsigned NOT NULL default '0',
		  `endtime` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `group` (`group`,`starttime`,`endtime`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('banner', 'groups', 'array', 'BLOCK', 'a:0:{}', '', 0, 0);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_banner`;
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				INSERT INTO `apx_config` VALUES ('banner', 'groups', 'array', 'BLOCK', 'a:10:{i:1;s:8:\"Gruppe 1\";i:2;s:8:\"Gruppe 2\";i:3;s:8:\"Gruppe 3\";i:4;s:8:\"Gruppe 4\";i:5;s:8:\"Gruppe 5\";i:6;s:8:\"Gruppe 6\";i:7;s:8:\"Gruppe 7\";i:8;s:8:\"Gruppe 8\";i:9;s:8:\"Gruppe 9\";i:10;s:9:\"Gruppe 10\";}', 0, 0);
				ALTER TABLE `apx_banner` ADD `starttime` INT( 11 ) UNSIGNED NOT NULL AFTER `group` ,ADD `endtime` INT( 11 ) UNSIGNED NOT NULL AFTER `starttime` ;
				UPDATE `apx_banner` SET starttime='1136070000', endtime='3000000000' WHERE active='1';
				ALTER TABLE `apx_banner` DROP `active`;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 101: //zu 1.1.0
			
			//Indizes entfernen
			clearIndices(PRE.'_banner');
			
			//config Update
			updateConfig('banner', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('banner', 'groups', 'array', 'BLOCK', 'a:0:{}', '', 0, 0);
			");
			
			$mysql="
				ALTER TABLE `apx_banner` ADD `capping` INT( 11 ) UNSIGNED NOT NULL AFTER `views` ;
				
				ALTER TABLE `apx_banner` ADD INDEX ( `group` , `starttime` , `endtime` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
	}
	
}

?>