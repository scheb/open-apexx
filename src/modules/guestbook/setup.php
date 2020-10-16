<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Installieren
if ( SETUPMODE=='install' ) {
	$mysql="
		CREATE TABLE `apx_guestbook` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `userid` int(11) unsigned NOT NULL default '0',
		  `username` tinytext NOT NULL,
		  `email` tinytext NOT NULL,
		  `homepage` tinytext NOT NULL,
		  `custom1` text NOT NULL,
		  `custom2` text NOT NULL,
		  `custom3` text NOT NULL,
		  `custom4` text NOT NULL,
		  `custom5` text NOT NULL,
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  `ip` varchar(15) NOT NULL,
		  `active` tinyint(1) unsigned NOT NULL default '0',
		  `com_userid` int(11) unsigned NOT NULL default '0',
		  `com_text` text NOT NULL,
		  `com_time` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `active` (`active`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('guestbook', 'blockip', 'array', 'BLOCK', 'a:0:{}', '', 0, 0),
		('guestbook', 'blockstring', 'array', 'BLOCK', 'a:0:{}', '', 0, 0),
		
		('guestbook', 'epp', 'int', '', '10', 'VIEW', 1241809127, 1000),
		('guestbook', 'breakline', 'int', '', '0', 'VIEW', 1241809127, 2000),
		('guestbook', 'cusfield_names', 'array', '', 'a:0:{}', 'VIEW', 1241809127, 3000),
		
		('guestbook', 'req_email', 'switch', '', '0', 'OPTIONS', 1241809127, 1000),
		('guestbook', 'req_homepage', 'switch', '', '0', 'OPTIONS', 1241809127, 2000),
		('guestbook', 'req_title', 'switch', '', '0', 'OPTIONS', 1241809127, 3000),
		('guestbook', 'allowsmilies', 'switch', '', '1', 'OPTIONS', 1241809127, 4000),
		('guestbook', 'allowcode', 'switch', '', '1', 'OPTIONS', 1241809127, 5000),
		('guestbook', 'badwords', 'switch', '', '1', 'OPTIONS', 1241809127, 6000),
		('guestbook', 'maxlen', 'int', '', '10000', 'OPTIONS', 1241809127, 7000),
		('guestbook', 'spamprot', 'int', '', '1', 'OPTIONS', 1241809127, 8000),
		('guestbook', 'captcha', 'switch', '', '1', 'OPTIONS', 1241809127, 9050),
		('guestbook', 'mod', 'switch', '', '1', 'OPTIONS', 1241809127, 10000),
		('guestbook', 'mailonnew', 'string', '', '', 'OPTIONS', 1241809127, 11000);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_guestbook`;
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				INSERT INTO `apx_config` VALUES ('guestbook', 'blockstring', 'array', 'BLOCK', 'a:0:{}', '0', '0');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 101: //zu 1.0.2
			$mysql="
				INSERT INTO `apx_config` VALUES ('guestbook', 'captcha', 'switch', '', '0', '0', '1150');
				INSERT INTO `apx_config` VALUES ('guestbook', 'mailonnew', 'string', '', '', '0', '1300');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 102: //zu 1.0.3
			$mysql="
				UPDATE `apx_config` SET varname = 'captcha' WHERE module = 'guestbook' AND varname = 'capcha';
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 103: //zu 1.1.0
			
			//Indizes entfernen
			clearIndices(PRE.'_guestbook');
			
			//config Update
			updateConfig('guestbook', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('guestbook', 'blockip', 'array', 'BLOCK', 'a:0:{}', '', 0, 0),
				('guestbook', 'blockstring', 'array', 'BLOCK', 'a:0:{}', '', 0, 0),
				
				('guestbook', 'epp', 'int', '', '10', 'VIEW', 1241809127, 1000),
				('guestbook', 'breakline', 'int', '', '0', 'VIEW', 1241809127, 2000),
				('guestbook', 'cusfield_names', 'array', '', 'a:0:{}', 'VIEW', 1241809127, 3000),
				
				('guestbook', 'req_email', 'switch', '', '0', 'OPTIONS', 1241809127, 1000),
				('guestbook', 'req_homepage', 'switch', '', '0', 'OPTIONS', 1241809127, 2000),
				('guestbook', 'req_title', 'switch', '', '0', 'OPTIONS', 1241809127, 3000),
				('guestbook', 'allowsmilies', 'switch', '', '1', 'OPTIONS', 1241809127, 4000),
				('guestbook', 'allowcode', 'switch', '', '1', 'OPTIONS', 1241809127, 5000),
				('guestbook', 'badwords', 'switch', '', '1', 'OPTIONS', 1241809127, 6000),
				('guestbook', 'maxlen', 'int', '', '10000', 'OPTIONS', 1241809127, 7000),
				('guestbook', 'spamprot', 'int', '', '1', 'OPTIONS', 1241809127, 8000),
				('guestbook', 'captcha', 'switch', '', '1', 'OPTIONS', 1241809127, 9050),
				('guestbook', 'mod', 'switch', '', '1', 'OPTIONS', 1241809127, 10000),
				('guestbook', 'mailonnew', 'string', '', '', 'OPTIONS', 1241809127, 11000);
			");
			
			$mysql="
				ALTER TABLE `apx_guestbook` CHANGE `ip` `ip` VARCHAR( 15 ) NOT NULL ;
				
				ALTER TABLE `apx_guestbook` ADD INDEX ( `active` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
	}
}

?>