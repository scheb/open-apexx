<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Installieren
if ( SETUPMODE=='install' ) {
	$mysql="
		CREATE TABLE `apx_content` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `secid` tinytext NOT NULL,
		  `catid` INT( 11 ) UNSIGNED NOT NULL,
		  `userid` int(11) unsigned NOT NULL default '0',
		  `title` tinytext NOT NULL,
		  `text` longtext NOT NULL,
		  `meta_description` text NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  `lastchange` int(11) unsigned NOT NULL default '0',
		  `lastchange_userid` int(11) unsigned NOT NULL default '0',
		  `searchable` tinyint(1) unsigned NOT NULL default '0',
		  `allowcoms` tinyint(1) unsigned NOT NULL default '0',
		  `allowrating` smallint(1) unsigned NOT NULL default '1',
		  `active` smallint(1) unsigned NOT NULL default '0',
		  `hits` int(11) NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `active` (`active`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('content', 'searchable', 'switch', '', '1', '', 0, 1000),
		('content', 'coms', 'switch', '', '1', '', 0, 2000),
		('content', 'ratings', 'switch', '', '1', '', 0, 3000),
		('content', 'groups', 'array_keys', 'BLOCK', 'a:0:{}', '', '0', '0');
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_content`;
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				INSERT INTO `apx_config` VALUES ('content', 'searchable', 'switch', '', '1', '0', '50');
				ALTER TABLE `apx_content` ADD `lastchange_userid` INT( 11 ) UNSIGNED NOT NULL AFTER `lastchange`;
				ALTER TABLE `apx_content` ADD `searchable` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `lastchange_userid`;
				UPDATE `apx_content` SET lastchange_userid=userid;
				UPDATE `apx_content` SET searchable='1';
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			
		case 101: //zu 1.1.0
			
			//Indizes entfernen
			clearIndices(PRE.'_content');
			
			//config Update
			updateConfig('content', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('content', 'searchable', 'switch', '', '1', '', 0, 1000),
				('content', 'coms', 'switch', '', '1', '', 0, 2000),
				('content', 'ratings', 'switch', '', '1', '', 0, 3000);
			");
			
			$mysql="
				ALTER TABLE `apx_content` ADD INDEX ( `active` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 110: //zu 1.1.1
		
			$mysql="
				ALTER TABLE `apx_content` ADD `catid` INT( 11 ) UNSIGNED NOT NULL AFTER `secid` ;
				UPDATE `apx_content` SET catid=1;
				INSERT INTO `apx_config` VALUES ('content', 'groups', 'array_keys', 'BLOCK', 'a:1:{i:1;s:11:\"Kategorie 1\";}', '', '0', '0');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 111: //zu 1.1.2
		
			$mysql="
				ALTER TABLE `apx_content` ADD `meta_description` TEXT NOT NULL AFTER `text` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
	}
}

?>