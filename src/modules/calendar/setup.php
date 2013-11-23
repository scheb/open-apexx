<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2009, Christian Scheb            |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| THIS SOFTWARE IS NOT FREE! MAKE SURE YOU OWN A VALID LICENSE! |
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Installieren
if ( SETUPMODE=='install' ) {
	$mysql="
		CREATE TABLE `apx_calendar_cat` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` tinytext NOT NULL,
		  `icon` tinytext NOT NULL,
		  `parents` varchar(255) NOT NULL,
		  `children` text NOT NULL,
		  `ord` tinyint(3) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `parents` (`parents`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_calendar_events` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `secid` tinytext NOT NULL,
		  `catid` int(11) unsigned NOT NULL default '0',
		  `userid` int(11) unsigned NOT NULL default '0',
		  `send_username` tinytext NOT NULL,
		  `send_email` tinytext NOT NULL,
		  `send_ip` tinytext NOT NULL,
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `meta_description` text NOT NULL,
		  `location` tinytext NOT NULL,
		  `location_link` tinytext NOT NULL,
		  `picture` tinytext NOT NULL,
		  `priority` enum('1','2','3') NOT NULL default '2',
		  `galid` int(11) unsigned NOT NULL default '0',
		  `links` text NOT NULL,
		  `addtime` int(11) unsigned NOT NULL default '0',
		  `startday` int(8) NOT NULL default '0',
		  `starttime` smallint(4) NOT NULL default '-1',
		  `endday` int(8) NOT NULL default '0',
		  `endtime` smallint(4) NOT NULL default '-1',
		  `searchable` tinyint(1) unsigned NOT NULL default '0',
		  `allowcoms` tinyint(1) unsigned NOT NULL default '0',
		  `allownote` tinyint(1) unsigned NOT NULL default '0',
		  `restricted` tinyint(1) unsigned NOT NULL,
		  `private` tinyint(1) unsigned NOT NULL default '0',
		  `hits` int(11) unsigned NOT NULL default '0',
		  `active` INT( 11 ) UNSIGNED NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `catid` (`catid`),
		  KEY `userid` (`userid`),
		  KEY `active` (`active`),
		  KEY `startday` (`startday`,`endday`,`starttime`,`endtime`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_calendar_parts` (
		  `eventid` int(11) unsigned NOT NULL default '0',
		  `userid` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`eventid`,`userid`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_calendar_tags` (
		  `id` int(11) unsigned NOT NULL,
		  `tagid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`,`tagid`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('calendar', 'eventdays', 'int', '', '7', 'VIEW', 1219685244, 1000),
		('calendar', 'searchepp', 'int', '', '20', 'VIEW', 1219685244, 2000),
		('calendar', 'sortby', 'select', 'a:2:{i:1;s:13:\"{SORTBY_TIME}\";i:2;s:14:\"{SORTBY_TITLE}\";}', '1', 'VIEW', 1219685244, 3000),
		('calendar', 'start', 'select', 'a:3:{s:3:\"day\";s:11:\"{START_DAY}\";s:4:\"week\";s:12:\"{START_WEEK}\";s:5:\"month\";s:13:\"{START_MONTH}\";}', 'month', 'VIEW', '0', '4000'),
		
		('calendar', 'searchable', 'switch', '', '1', 'OPTIONS', 1219685244, 1000),
		('calendar', 'subcats', 'switch', '', '1', 'OPTIONS', 1219685244, 2000),
		('calendar', 'userevents', 'switch', '', '1', 'OPTIONS', 1219685244, 3000),
		('calendar', 'captcha', 'switch', '', '1', 'OPTIONS', 1219685244, 4000),
		('calendar', 'mailonnew', 'string', '', '', 'OPTIONS', 1219685244, 5000),
		('calendar', 'coms', 'switch', '', '1', 'OPTIONS', 1219685244, 6000),
		('calendar', 'note', 'switch', '', '1', 'OPTIONS', 1219685244, 7000),
		
		('calendar', 'pic_width', 'int', '', '120', 'IMAGES', 1219685244, 1000),
		('calendar', 'pic_height', 'int', '', '120', 'IMAGES', 1219685244, 2000),
		('calendar', 'pic_popup', 'switch', '', '1', 'IMAGES', 1219685244, 3000),
		('calendar', 'pic_popup_width', 'int', '', '640', 'IMAGES', 1219685244, 4000),
		('calendar', 'pic_popup_height', 'int', '', '480', 'IMAGES', 1219685244, 5000),
		('calendar', 'pic_quality', 'switch', '', '1', 'IMAGES', 1219685244, 6000);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
	
	//Bilder-Ordner
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$mm=new mediamanager;
	$mm->createdir('calendar');
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_calendar_cat`;
		DROP TABLE `apx_calendar_events`;
		DROP TABLE `apx_calendar_parts`;
		DROP TABLE `apx_calendar_tags`;
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
	
		case 100: //zu 1.0.1
			$mysql="
				ALTER TABLE `apx_calendar_events` ADD `location` TINYTEXT NOT NULL AFTER `text` ;
				ALTER TABLE `apx_calendar_events` ADD `send_username` TINYTEXT NOT NULL AFTER `userid` , ADD `send_email` TINYTEXT NOT NULL AFTER `send_username` , ADD `send_ip` TINYTEXT NOT NULL AFTER `send_email` ;
				ALTER TABLE `apx_calendar_events` ADD `secid` TINYTEXT NOT NULL AFTER `id` ;
				UPDATE `apx_calendar_events` SET secid = 'all';
				ALTER TABLE `apx_calendar_cat` ADD `root_id` INT( 11 ) UNSIGNED NOT NULL AFTER `id` ;
				ALTER TABLE `apx_calendar_cat` ADD `lft` INT( 11 ) UNSIGNED NOT NULL , ADD `rgt` INT( 11 ) UNSIGNED NOT NULL ;
				UPDATE `apx_calendar_cat` SET root_id = id;
				INSERT INTO `apx_config` VALUES ('calendar', 'subcats', 'switch', '', '1', 1176245712, 50);
				INSERT INTO `apx_config` VALUES ('calendar', 'sortby', 'select', 'a:2:{i:1;s:13:\"{SORTBY_TIME}\";i:2;s:14:\"{SORTBY_TITLE}\";}', '1', '0', '250');
				INSERT INTO `apx_config` VALUES ('calendar', 'captcha', 'switch', '', '1', '0', '1050');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			//LFT und RGT anpassen
			$lft = 1;
			$data = $db->fetch("SELECT id FROM ".PRE."_calendar_cat ORDER BY title ASC");
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					$db->query("UPDATE ".PRE."_calendar_cat SET lft='".$lft."', rgt='".($lft+1)."' WHERE id='".$res['id']."' LIMIT 1");
					$lft += 2;
				}
			}
			
			
		case 101: //zu 1.0.2
			$mysql="
				INSERT INTO `apx_config` VALUES ('calendar', 'searchepp', 'int', '', '20', '0', '150');
				ALTER TABLE `apx_calendar_events` ADD `location_link` TINYTEXT NOT NULL AFTER `location` ;
				ALTER TABLE `apx_calendar_events` ADD `links` TEXT NOT NULL AFTER `galid` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 102: //zu 1.1.0
			
			//Indizes entfernen
			clearIndices(PRE.'_calendar_cat');
			clearIndices(PRE.'_calendar_events');
			
			//Tabellenformat ändern
			convertRecursiveTable(PRE.'_calendar_cat');
			
			//config Update
			updateConfig('calendar', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('calendar', 'eventdays', 'int', '', '7', 'VIEW', 1219685244, 1000),
				('calendar', 'searchepp', 'int', '', '20', 'VIEW', 1219685244, 2000),
				('calendar', 'sortby', 'select', 'a:2:{i:1;s:13:\"{SORTBY_TIME}\";i:2;s:14:\"{SORTBY_TITLE}\";}', '1', 'VIEW', 1219685244, 3000),
				
				('calendar', 'searchable', 'switch', '', '1', 'OPTIONS', 1219685244, 1000),
				('calendar', 'subcats', 'switch', '', '1', 'OPTIONS', 1219685244, 2000),
				('calendar', 'userevents', 'switch', '', '1', 'OPTIONS', 1219685244, 3000),
				('calendar', 'captcha', 'switch', '', '1', 'OPTIONS', 1219685244, 4000),
				('calendar', 'mailonnew', 'string', '', '', 'OPTIONS', 1219685244, 5000),
				('calendar', 'coms', 'switch', '', '1', 'OPTIONS', 1219685244, 6000),
				('calendar', 'note', 'switch', '', '1', 'OPTIONS', 1219685244, 7000),
				
				('calendar', 'pic_width', 'int', '', '120', 'IMAGES', 1219685244, 1000),
				('calendar', 'pic_height', 'int', '', '120', 'IMAGES', 1219685244, 2000),
				('calendar', 'pic_popup', 'switch', '', '1', 'IMAGES', 1219685244, 3000),
				('calendar', 'pic_popup_width', 'int', '', '640', 'IMAGES', 1219685244, 4000),
				('calendar', 'pic_popup_height', 'int', '', '480', 'IMAGES', 1219685244, 5000),
				('calendar', 'pic_quality', 'switch', '', '1', 'IMAGES', 1219685244, 6000);
			");
			
			$mysql="
				CREATE TABLE `apx_calendar_tags` (
					`id` INT( 11 ) UNSIGNED NOT NULL ,
					`tagid` INT( 11 ) UNSIGNED NOT NULL ,
					PRIMARY KEY ( `id` , `tagid` )
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_calendar_events` ADD `restricted` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `allownote` ;
				
				ALTER TABLE `apx_calendar_cat` ADD INDEX ( `parents` ) ;
				ALTER TABLE `apx_calendar_events` ADD INDEX ( `catid` ) ;
				ALTER TABLE `apx_calendar_events` ADD INDEX ( `userid` ) ;
				ALTER TABLE `apx_calendar_events` ADD INDEX ( `active` ) ;
				ALTER TABLE `apx_calendar_events` ADD INDEX ( `startday` , `endday`, `starttime`,`endtime` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			//Tags erzeugen
			transformKeywords(PRE.'_calendar_events', PRE.'_calendar_tags');
		
		
		case 110: //zu 1.1.1
			$mysql="
				ALTER TABLE `apx_calendar_events` CHANGE `active` `active` INT( 11 ) UNSIGNED NOT NULL ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 111: //zu 1.1.2
			$mysql="
				ALTER TABLE `apx_calendar_events` ADD `meta_description` TEXT NOT NULL AFTER `text` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 112: //zu 1.1.3
			$mysql="
				INSERT INTO `apx_config` VALUES ('calendar', 'start', 'select', 'a:3:{s:3:\"day\";s:11:\"{START_DAY}\";s:4:\"week\";s:12:\"{START_WEEK}\";s:5:\"month\";s:13:\"{START_MONTH}\";}', 'month', 'VIEW', '0', '4000');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
	}
}

?>