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
	$crypt = random_string();
	$mysql="
		CREATE TABLE `apx_captcha` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `code` VARCHAR( 5 ) NOT NULL,
		  `hash` varchar(32) NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `hash` (`hash`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_config` (
		  `module` varchar(50) NOT NULL default '',
		  `varname` varchar(50) NOT NULL default '',
		  `type` enum('switch','string','array','array_keys','int','float','select') NOT NULL default 'string',
		  `addnl` text NOT NULL,
		  `value` text NOT NULL,
		  `tab` varchar(30) NOT NULL,
		  `lastchange` int(11) unsigned NOT NULL default '0',
		  `ord` smallint(5) NOT NULL default '0',
		  PRIMARY KEY  (`module`,`varname`),
		  KEY `tab` (`tab`,`ord`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_cron` (
		  `funcname` varchar(50) NOT NULL default '',
		  `module` varchar(50) NOT NULL default '',
		  `period` int(11) unsigned NOT NULL default '0',
		  `lastexec` int(11) unsigned NOT NULL default '0',
		  `hash` tinytext NOT NULL,
		  PRIMARY KEY  (`funcname`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_log` (
		  `time` datetime NOT NULL,
		  `userid` int(11) unsigned NOT NULL default '0',
		  `ip` text NOT NULL,
		  `text` tinytext NOT NULL,
		  `affects` tinytext NOT NULL,
		  KEY `time` (`time`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_modules` (
		  `module` varchar(50) NOT NULL default '',
		  `active` tinyint(1) unsigned NOT NULL default '0',
		  `installed` tinyint(1) unsigned NOT NULL default '0',
		  `version` smallint(3) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`module`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_search` (
		  `searchid` varchar(32) NOT NULL,
		  `object` varchar(30) NOT NULL,
		  `results` mediumtext NOT NULL,
		  `options` text NOT NULL,
		  `time` int(11) unsigned NOT NULL,
		  KEY `searchid` (`searchid`,`object`,`time`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_search_item` (
		  `item` tinytext NOT NULL,
		  `time` int(10) unsigned NOT NULL
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_sections` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` tinytext NOT NULL,
		  `virtual` tinytext NOT NULL,
		  `theme` tinytext NOT NULL,
		  `lang` varchar(20) NOT NULL default '',
		  `active` tinyint(1) unsigned NOT NULL default '1',
		  `msg_noaccess` text NOT NULL,
		  `default` tinyint(1) NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_sessions` (
		  `id` varchar(32) NOT NULL,
		  `ownerid` varchar(32) NOT NULL,
		  `starttime` int(10) unsigned NOT NULL,
		  `data` text NOT NULL,
		  PRIMARY KEY  (`id`,`ownerid`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_snippets` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` tinytext NOT NULL,
		  `code` longtext NOT NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_tags` (
		  `tagid` int(10) unsigned NOT NULL auto_increment,
		  `tag` varchar(40) NOT NULL,
		  PRIMARY KEY  (`tagid`)
		) ENGINE=MyISAM  ROW_FORMAT=DYNAMIC;
		
		CREATE TABLE `apx_templates` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` tinytext NOT NULL,
		  `code` longtext NOT NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_cron` VALUES ('optimize_database', 'main', 86400, 1190070000, '');
		INSERT INTO `apx_cron` VALUES ('clear_cache', 'main', 86400, 1190070000, '');
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('main', 'languages', 'array', 'BLOCK', 'a:1:{s:2:\"de\";a:2:{s:5:\"title\";s:7:\"Deutsch\";s:7:\"default\";b:1;}}', '', 0, 0),
		('main', 'smilies', 'array', 'BLOCK', 'a:22:{i:1;a:3:{s:4:\"code\";s:2:\":)\";s:4:\"file\";s:24:\"design/smilies/smile.gif\";s:11:\"description\";s:14:\"Normaler Smile\";}i:2;a:3:{s:4:\"code\";s:2:\"8)\";s:4:\"file\";s:23:\"design/smilies/cool.gif\";s:11:\"description\";s:13:\"Cooler Smilie\";}i:3;a:3:{s:4:\"code\";s:8:\"*wütend*\";s:4:\"file\";s:28:\"design/smilies/angryfire.gif\";s:11:\"description\";s:15:\"Wütender Smilie\";}i:4;a:3:{s:4:\"code\";s:5:\"*!!!*\";s:4:\"file\";s:25:\"design/smilies/ausruf.gif\";s:11:\"description\";s:14:\"Ausrufezeichen\";}i:5;a:3:{s:4:\"code\";s:2:\":D\";s:4:\"file\";s:26:\"design/smilies/biggrin.gif\";s:11:\"description\";s:15:\"Breites Grinsen\";}i:6;a:3:{s:4:\"code\";s:5:\"*gut*\";s:4:\"file\";s:30:\"design/smilies/biggthumpup.gif\";s:11:\"description\";s:9:\"Sehr gut!\";}i:7;a:3:{s:4:\"code\";s:10:\"*verwirrt*\";s:4:\"file\";s:27:\"design/smilies/confused.gif\";s:11:\"description\";s:17:\"Verwirrter Smilie\";}i:8;a:3:{s:4:\"code\";s:10:\"*verrückt*\";s:4:\"file\";s:24:\"design/smilies/crazy.gif\";s:11:\"description\";s:9:\"Verrückt!\";}i:9;a:3:{s:4:\"code\";s:5:\"*hmm*\";s:4:\"file\";s:24:\"design/smilies/dozey.gif\";s:11:\"description\";s:6:\"Hmm...\";}i:10;a:3:{s:4:\"code\";s:5:\"*eek*\";s:4:\"file\";s:22:\"design/smilies/eek.gif\";s:11:\"description\";s:6:\"Wooow!\";}i:11;a:3:{s:4:\"code\";s:6:\"*hmm2*\";s:4:\"file\";s:23:\"design/smilies/eek2.gif\";s:11:\"description\";s:6:\"Hmm...\";}i:12;a:3:{s:4:\"code\";s:5:\"*???*\";s:4:\"file\";s:24:\"design/smilies/frage.gif\";s:11:\"description\";s:12:\"Fragezeichen\";}i:13;a:3:{s:4:\"code\";s:2:\":(\";s:4:\"file\";s:24:\"design/smilies/frown.gif\";s:11:\"description\";s:16:\"Trauriger Smilie\";}i:14;a:3:{s:4:\"code\";s:2:\";(\";s:4:\"file\";s:23:\"design/smilies/heul.gif\";s:11:\"description\";s:16:\"Weinender Smilie\";}i:15;a:3:{s:4:\"code\";s:5:\"*lol*\";s:4:\"file\";s:24:\"design/smilies/laugh.gif\";s:11:\"description\";s:16:\"Lachender Smilie\";}i:16;a:3:{s:4:\"code\";s:6:\"*fies*\";s:4:\"file\";s:26:\"design/smilies/naughty.gif\";s:11:\"description\";s:13:\"Fieser Smilie\";}i:17;a:3:{s:4:\"code\";s:7:\"*angst*\";s:4:\"file\";s:24:\"design/smilies/sconf.gif\";s:11:\"description\";s:18:\"Ängstlicher Smilie\";}i:18;a:3:{s:4:\"code\";s:8:\"*schrei*\";s:4:\"file\";s:25:\"design/smilies/scream.gif\";s:11:\"description\";s:18:\"Schreiender Smilie\";}i:19;a:3:{s:4:\"code\";s:8:\"*autsch*\";s:4:\"file\";s:26:\"design/smilies/shinner.gif\";s:11:\"description\";s:11:\"Blaues Auge\";}i:20;a:3:{s:4:\"code\";s:2:\":P\";s:4:\"file\";s:25:\"design/smilies/tongue.gif\";s:11:\"description\";s:6:\"Ätsch!\";}i:21;a:3:{s:4:\"code\";s:5:\"*ugh*\";s:4:\"file\";s:22:\"design/smilies/ugh.gif\";s:11:\"description\";s:6:\"Ugh...\";}i:22;a:3:{s:4:\"code\";s:2:\";)\";s:4:\"file\";s:26:\"design/smilies/zwinker.gif\";s:11:\"description\";s:7:\"Zwinker\";}}', '', 0, 0),
		('main', 'codes', 'array', 'BLOCK', 'a:9:{i:1;a:5:{s:4:\"code\";s:1:\"B\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:10:\"<b>{1}</b>\";s:7:\"example\";s:18:\"[B]fetter Text[/B]\";s:8:\"allowsig\";i:1;}i:2;a:5:{s:4:\"code\";s:1:\"I\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:10:\"<i>{1}</i>\";s:7:\"example\";s:20:\"[I]kursiver Text[/I]\";s:8:\"allowsig\";i:1;}i:3;a:5:{s:4:\"code\";s:1:\"U\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:10:\"<u>{1}</u>\";s:7:\"example\";s:27:\"[U]unterstrichener Text[/U]\";s:8:\"allowsig\";i:1;}i:4;a:5:{s:4:\"code\";s:3:\"URL\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:37:\"<a href=\"{1}\" target=\"_blank\">{1}</a>\";s:7:\"example\";s:31:\"[URL]http://www.domain.de[/URL]\";s:8:\"allowsig\";i:1;}i:5;a:5:{s:4:\"code\";s:3:\"URL\";s:5:\"count\";s:1:\"2\";s:7:\"replace\";s:37:\"<a href=\"{1}\" target=\"_blank\">{2}</a>\";s:7:\"example\";s:38:\"[URL=http://www.domain.de]Klick![/URL]\";s:8:\"allowsig\";i:1;}i:6;a:5:{s:4:\"code\";s:5:\"EMAIL\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:28:\"<a href=\"mailto:{1}\">{1}</a>\";s:7:\"example\";s:28:\"[EMAIL]ich@domain.de[/EMAIL]\";s:8:\"allowsig\";i:1;}i:7;a:5:{s:4:\"code\";s:5:\"EMAIL\";s:5:\"count\";s:1:\"2\";s:7:\"replace\";s:28:\"<a href=\"mailto:{1}\">{2}</a>\";s:7:\"example\";s:35:\"[EMAIL=ich@domain.de]Klick![/EMAIL]\";s:8:\"allowsig\";i:1;}i:8;a:5:{s:4:\"code\";s:3:\"IMG\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:15:\"<img src=\"{1}\">\";s:7:\"example\";s:19:\"[IMG]bild.jpg[/IMG]\";s:8:\"allowsig\";i:1;}i:9;a:5:{s:4:\"code\";s:5:\"QUOTE\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:28:\"<blockquote>{1}</blockquote>\";s:7:\"example\";s:20:\"[QUOTE]Zitat[/QUOTE]\";s:8:\"allowsig\";i:0;}}', '', 0, 0),
		('main', 'badwords', 'array', 'BLOCK', 'a:2:{i:1;a:2:{s:4:\"find\";s:4:\"shit\";s:7:\"replace\";s:4:\"****\";}i:2;a:2:{s:4:\"find\";s:4:\"fuck\";s:7:\"replace\";s:4:\"#%&!\";}}', '', 0, 0),
		('main', 'staticsites_virtual', 'int', 'BLOCK', '1', '', 0, 0),
		('main', 'crypt', 'string', 'BLOCK', '".$crypt."', '', 0, 0),
		('main', 'closed', 'switch', 'BLOCK', '0', '', 0, 0),
		('main', 'close_message', 'string', 'BLOCK', 'Wir führen Wartungsarbeiten durch!', '', 0, 0),
				
		('main', 'charset', 'string', '', 'ISO-8859-1', 'OPTIONS', 1247520057, 1000),
		('main', 'websitename', 'string', '', 'apexx Website', 'OPTIONS', 1247520057, 2000),
		('main', 'mailbot', 'string', '', 'apexx@my-website.com', 'OPTIONS', 1247520057, 3000),
		('main', 'mailbotname', 'string', '', 'apexx Mailbot', 'OPTIONS', 1247520057, 4000),
		('main', 'cookie_pre', 'string', '', 'apx', 'OPTIONS', 1247520057, 5000),
		('main', 'index_forwarder', 'string', '', '', 'OPTIONS', 1247520057, 6000),
		('main', 'forcesection', 'switch', '', '0', 'OPTIONS', 1247520057, 7000),
		('main', 'tell', 'switch', '', '1', 'OPTIONS', '0', '7500'),
		('main', 'tellcaptcha', 'switch', '', '1', 'OPTIONS', 1247520057, 8000),
		('main', 'admin_epp', 'int', '', '15', 'OPTIONS', 1247520057, 9000),
		('main', 'textboxwidth', 'int', '', '0', 'OPTIONS', 1247520057, 10000),
		('main', 'entermode', 'select', 'a:2:{s:2:\"br\";s:10:\"&lt;br&gt;\";s:1:\"p\";s:9:\"&lt;p&gt;\";}', 'p', 'OPTIONS', '0', '11000'),
		('main', 'old_captcha', 'switch', '', '0', 'OPTIONS', '0', '12000'),
		
		('main', 'timezone', 'select', 'a:25:{i:-12;s:10:\"GMT -12:00\";i:-11;s:10:\"GMT -11:00\";i:-10;s:10:\"GMT -10:00\";i:-9;s:9:\"GMT -9:00\";i:-8;s:9:\"GMT -8:00\";i:-7;s:9:\"GMT -7:00\";i:-6;s:9:\"GMT -6:00\";i:-5;s:9:\"GMT -5:00\";i:-4;s:9:\"GMT -4:00\";i:-3;s:9:\"GMT -3:00\";i:-2;s:9:\"GMT -2:00\";i:-1;s:9:\"GMT -1:00\";i:0;s:3:\"GMT\";i:1;s:9:\"GMT +1:00\";i:2;s:9:\"GMT +2:00\";i:3;s:9:\"GMT +3:30\";i:4;s:9:\"GMT +4:30\";i:5;s:9:\"GMT +5:30\";i:6;s:9:\"GMT +6:00\";i:7;s:9:\"GMT +7:00\";i:8;s:9:\"GMT +8:00\";i:9;s:9:\"GMT +9:30\";i:10;s:10:\"GMT +10:00\";i:11;s:10:\"GMT +11:00\";i:12;s:10:\"GMT +12:00\";}', '1', 'TIME', 1247520057, 1000),
		('main', 'dateformat', 'string', '', 'd.m.Y', 'TIME', 1247520057, 2000),
		('main', 'timeformat', 'string', '', 'H:i:s', 'TIME', 1247520057, 3000),
		('main', 'conndatetime', 'string', '', ' - ', 'TIME', 1247520057, 4000),
		
		('main', 'staticsites', 'switch', '', '0', 'SEO', 1247520057, 1000),
		('main', 'staticsites_separator', 'string', '', ',', 'SEO', 1247520057, 2000),
		('main', 'keywords', 'switch', '', '0', 'SEO', 1247520057, 3000),
		('main', 'keywords_separator', 'string', '', '_', 'SEO', 1247520057, 4000);
	";
	
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
	
	//Temp-DIR
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$mm=new mediamanager;
	$mm->createdir('temp');
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				CREATE TABLE `apx_capcha` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `code` int(5) unsigned NOT NULL default '0',
				  `hash` tinytext NOT NULL,
				  `time` int(11) unsigned NOT NULL default '0',
				  PRIMARY KEY  (`id`)
				) ENGINE=MyISAM ;
				INSERT INTO `apx_config` VALUES ('main', 'textboxwidth', 'int', '', '', '0', '650');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 101: //zu 1.0.2
			//Temp-DIR
			require_once(BASEDIR.'lib/class.mediamanager.php');
			$mm=new mediamanager;
			$mm->createdir('temp');
		
		
		case 102: //Zu 1.0.3
			$mysql="
				ALTER TABLE `apx_capcha` RENAME `apx_captcha` ;
				DELETE FROM `apx_config` WHERE varname='capcha';
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 103: //Zu 1.0.4
			$mysql="
				CREATE TABLE `apx_cron` (
				  `funcname` varchar(50) NOT NULL default '',
				  `module` varchar(50) NOT NULL default '',
				  `period` int(11) unsigned NOT NULL default '0',
				  `lastexec` int(11) unsigned NOT NULL default '0',
				  `hash` tinytext NOT NULL,
				  PRIMARY KEY  (`funcname`)
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_sections` ADD `lang` VARCHAR( 20 ) NOT NULL AFTER `theme`;
				INSERT INTO `apx_config` VALUES ('main', 'tellcaptcha', 'switch', '', '1', '0', '1700');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 104: //Zu 1.0.5
			$mysql="
				CREATE TABLE `apx_snippets` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `title` tinytext NOT NULL,
				  `code` longtext NOT NULL,
				  PRIMARY KEY  (`id`)
				) ENGINE=MyISAM;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 105: //Zu 1.0.6
			$mysql="
				INSERT INTO `apx_config` VALUES ('main', 'staticsites_virtual', 'switch', 'BLOCK', '1', '0', '0');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 106: //Zu 1.0.7
			$mysql="
				INSERT INTO `apx_cron` VALUES ('optimize_database', 'main', 86400, 1190070000, '');
				INSERT INTO `apx_cron` VALUES ('clear_cache', 'main', 86400, 1190070000, '');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 107: //Zu 1.1.0
			$crypt = random_string();
			$mysql="
				INSERT INTO `apx_config` VALUES ('main', 'crypt', 'string', 'BLOCK', '{$crypt}', '0', '0');
				CREATE TABLE `apx_search` (
				  `searchid` varchar(32) NOT NULL,
				  `object` varchar(15) NOT NULL,
				  `results` text NOT NULL,
				  `options` text NOT NULL,
				  `time` int(11) unsigned NOT NULL,
				  KEY `searchid` (`searchid`,`object`)
				) ENGINE=MyISAM;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 110: //Zu 1.2.0
			
			//Indizes entfernen
			clearIndices(PRE.'_config');
			clearIndices(PRE.'_captcha');
			clearIndices(PRE.'_loginfailed');
			clearIndices(PRE.'_search');
			
			//config Update
			$db->query("ALTER TABLE ".PRE."_config ADD `tab` VARCHAR( 30 ) NOT NULL AFTER `value`");
			updateConfig('main', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('main', 'languages', 'array', 'BLOCK', 'a:1:{s:2:\"de\";a:2:{s:5:\"title\";s:7:\"Deutsch\";s:7:\"default\";b:1;}}', '', 0, 0),
				('main', 'smilies', 'array', 'BLOCK', 'a:22:{i:1;a:3:{s:4:\"code\";s:2:\":)\";s:4:\"file\";s:24:\"design/smilies/smile.gif\";s:11:\"description\";s:14:\"Normaler Smile\";}i:2;a:3:{s:4:\"code\";s:2:\"8)\";s:4:\"file\";s:23:\"design/smilies/cool.gif\";s:11:\"description\";s:13:\"Cooler Smilie\";}i:3;a:3:{s:4:\"code\";s:8:\"*wütend*\";s:4:\"file\";s:28:\"design/smilies/angryfire.gif\";s:11:\"description\";s:15:\"Wütender Smilie\";}i:4;a:3:{s:4:\"code\";s:5:\"*!!!*\";s:4:\"file\";s:25:\"design/smilies/ausruf.gif\";s:11:\"description\";s:14:\"Ausrufezeichen\";}i:5;a:3:{s:4:\"code\";s:2:\":D\";s:4:\"file\";s:26:\"design/smilies/biggrin.gif\";s:11:\"description\";s:15:\"Breites Grinsen\";}i:6;a:3:{s:4:\"code\";s:5:\"*gut*\";s:4:\"file\";s:30:\"design/smilies/biggthumpup.gif\";s:11:\"description\";s:9:\"Sehr gut!\";}i:7;a:3:{s:4:\"code\";s:10:\"*verwirrt*\";s:4:\"file\";s:27:\"design/smilies/confused.gif\";s:11:\"description\";s:17:\"Verwirrter Smilie\";}i:8;a:3:{s:4:\"code\";s:10:\"*verrückt*\";s:4:\"file\";s:24:\"design/smilies/crazy.gif\";s:11:\"description\";s:9:\"Verrückt!\";}i:9;a:3:{s:4:\"code\";s:5:\"*hmm*\";s:4:\"file\";s:24:\"design/smilies/dozey.gif\";s:11:\"description\";s:6:\"Hmm...\";}i:10;a:3:{s:4:\"code\";s:5:\"*eek*\";s:4:\"file\";s:22:\"design/smilies/eek.gif\";s:11:\"description\";s:6:\"Wooow!\";}i:11;a:3:{s:4:\"code\";s:6:\"*hmm2*\";s:4:\"file\";s:23:\"design/smilies/eek2.gif\";s:11:\"description\";s:6:\"Hmm...\";}i:12;a:3:{s:4:\"code\";s:5:\"*???*\";s:4:\"file\";s:24:\"design/smilies/frage.gif\";s:11:\"description\";s:12:\"Fragezeichen\";}i:13;a:3:{s:4:\"code\";s:2:\":(\";s:4:\"file\";s:24:\"design/smilies/frown.gif\";s:11:\"description\";s:16:\"Trauriger Smilie\";}i:14;a:3:{s:4:\"code\";s:2:\";(\";s:4:\"file\";s:23:\"design/smilies/heul.gif\";s:11:\"description\";s:16:\"Weinender Smilie\";}i:15;a:3:{s:4:\"code\";s:5:\"*lol*\";s:4:\"file\";s:24:\"design/smilies/laugh.gif\";s:11:\"description\";s:16:\"Lachender Smilie\";}i:16;a:3:{s:4:\"code\";s:6:\"*fies*\";s:4:\"file\";s:26:\"design/smilies/naughty.gif\";s:11:\"description\";s:13:\"Fieser Smilie\";}i:17;a:3:{s:4:\"code\";s:7:\"*angst*\";s:4:\"file\";s:24:\"design/smilies/sconf.gif\";s:11:\"description\";s:18:\"Ängstlicher Smilie\";}i:18;a:3:{s:4:\"code\";s:8:\"*schrei*\";s:4:\"file\";s:25:\"design/smilies/scream.gif\";s:11:\"description\";s:18:\"Schreiender Smilie\";}i:19;a:3:{s:4:\"code\";s:8:\"*autsch*\";s:4:\"file\";s:26:\"design/smilies/shinner.gif\";s:11:\"description\";s:11:\"Blaues Auge\";}i:20;a:3:{s:4:\"code\";s:2:\":P\";s:4:\"file\";s:25:\"design/smilies/tongue.gif\";s:11:\"description\";s:6:\"Ätsch!\";}i:21;a:3:{s:4:\"code\";s:5:\"*ugh*\";s:4:\"file\";s:22:\"design/smilies/ugh.gif\";s:11:\"description\";s:6:\"Ugh...\";}i:22;a:3:{s:4:\"code\";s:2:\";)\";s:4:\"file\";s:26:\"design/smilies/zwinker.gif\";s:11:\"description\";s:7:\"Zwinker\";}}', '', 0, 0),
				('main', 'codes', 'array', 'BLOCK', 'a:9:{i:1;a:5:{s:4:\"code\";s:1:\"B\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:10:\"<b>{1}</b>\";s:7:\"example\";s:18:\"[B]fetter Text[/B]\";s:8:\"allowsig\";i:1;}i:2;a:5:{s:4:\"code\";s:1:\"I\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:10:\"<i>{1}</i>\";s:7:\"example\";s:20:\"[I]kursiver Text[/I]\";s:8:\"allowsig\";i:1;}i:3;a:5:{s:4:\"code\";s:1:\"U\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:10:\"<u>{1}</u>\";s:7:\"example\";s:27:\"[U]unterstrichener Text[/U]\";s:8:\"allowsig\";i:1;}i:4;a:5:{s:4:\"code\";s:3:\"URL\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:37:\"<a href=\"{1}\" target=\"_blank\">{1}</a>\";s:7:\"example\";s:31:\"[URL]http://www.domain.de[/URL]\";s:8:\"allowsig\";i:1;}i:5;a:5:{s:4:\"code\";s:3:\"URL\";s:5:\"count\";s:1:\"2\";s:7:\"replace\";s:37:\"<a href=\"{1}\" target=\"_blank\">{2}</a>\";s:7:\"example\";s:38:\"[URL=http://www.domain.de]Klick![/URL]\";s:8:\"allowsig\";i:1;}i:6;a:5:{s:4:\"code\";s:5:\"EMAIL\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:28:\"<a href=\"mailto:{1}\">{1}</a>\";s:7:\"example\";s:28:\"[EMAIL]ich@domain.de[/EMAIL]\";s:8:\"allowsig\";i:1;}i:7;a:5:{s:4:\"code\";s:5:\"EMAIL\";s:5:\"count\";s:1:\"2\";s:7:\"replace\";s:28:\"<a href=\"mailto:{1}\">{2}</a>\";s:7:\"example\";s:35:\"[EMAIL=ich@domain.de]Klick![/EMAIL]\";s:8:\"allowsig\";i:1;}i:8;a:5:{s:4:\"code\";s:3:\"IMG\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:15:\"<img src=\"{1}\">\";s:7:\"example\";s:19:\"[IMG]bild.jpg[/IMG]\";s:8:\"allowsig\";i:1;}i:9;a:5:{s:4:\"code\";s:5:\"QUOTE\";s:5:\"count\";s:1:\"1\";s:7:\"replace\";s:28:\"<blockquote>{1}</blockquote>\";s:7:\"example\";s:20:\"[QUOTE]Zitat[/QUOTE]\";s:8:\"allowsig\";i:0;}}', '', 0, 0),
				('main', 'badwords', 'array', 'BLOCK', 'a:2:{i:1;a:2:{s:4:\"find\";s:4:\"shit\";s:7:\"replace\";s:4:\"****\";}i:2;a:2:{s:4:\"find\";s:4:\"fuck\";s:7:\"replace\";s:4:\"#%&!\";}}', '', 0, 0),
				('main', 'staticsites_virtual', 'int', 'BLOCK', '1', '', 0, 0),
				('main', 'crypt', 'string', 'BLOCK', '".$crypt."', '', 0, 0),
				('main', 'closed', 'switch', 'BLOCK', '0', '', 0, 0),
				('main', 'close_message', 'string', 'BLOCK', 'Wir führen Wartungsarbeiten durch!', '', 0, 0),
				
				('main', 'charset', 'string', '', 'ISO-8859-1', 'OPTIONS', 1247520057, 1000),
				('main', 'websitename', 'string', '', 'apexx Website', 'OPTIONS', 1247520057, 2000),
				('main', 'mailbot', 'string', '', 'apexx@my-website.com', 'OPTIONS', 1247520057, 3000),
				('main', 'mailbotname', 'string', '', 'apexx Mailbot', 'OPTIONS', 1247520057, 4000),
				('main', 'cookie_pre', 'string', '', 'apx', 'OPTIONS', 1247520057, 5000),
				('main', 'index_forwarder', 'string', '', '', 'OPTIONS', 1247520057, 6000),
				('main', 'forcesection', 'switch', '', '0', 'OPTIONS', 1247520057, 7000),
				('main', 'tellcaptcha', 'switch', '', '1', 'OPTIONS', 1247520057, 8000),
				('main', 'admin_epp', 'int', '', '15', 'OPTIONS', 1247520057, 9000),
				('main', 'textboxwidth', 'int', '', '0', 'OPTIONS', 1247520057, 10000),
				
				('main', 'timezone', 'select', 'a:25:{i:-12;s:10:\"GMT -12:00\";i:-11;s:10:\"GMT -11:00\";i:-10;s:10:\"GMT -10:00\";i:-9;s:9:\"GMT -9:00\";i:-8;s:9:\"GMT -8:00\";i:-7;s:9:\"GMT -7:00\";i:-6;s:9:\"GMT -6:00\";i:-5;s:9:\"GMT -5:00\";i:-4;s:9:\"GMT -4:00\";i:-3;s:9:\"GMT -3:00\";i:-2;s:9:\"GMT -2:00\";i:-1;s:9:\"GMT -1:00\";i:0;s:3:\"GMT\";i:1;s:9:\"GMT +1:00\";i:2;s:9:\"GMT +2:00\";i:3;s:9:\"GMT +3:30\";i:4;s:9:\"GMT +4:30\";i:5;s:9:\"GMT +5:30\";i:6;s:9:\"GMT +6:00\";i:7;s:9:\"GMT +7:00\";i:8;s:9:\"GMT +8:00\";i:9;s:9:\"GMT +9:30\";i:10;s:10:\"GMT +10:00\";i:11;s:10:\"GMT +11:00\";i:12;s:10:\"GMT +12:00\";}', '1', 'TIME', 1247520057, 1000),
				('main', 'dateformat', 'string', '', 'd.m.Y', 'TIME', 1247520057, 2000),
				('main', 'timeformat', 'string', '', 'H:i:s', 'TIME', 1247520057, 3000),
				('main', 'conndatetime', 'string', '', ' - ', 'TIME', 1247520057, 4000),
				
				('main', 'staticsites', 'switch', '', '0', 'SEO', 1247520057, 1000),
				('main', 'staticsites_separator', 'string', '', ',', 'SEO', 1247520057, 2000),
				('main', 'keywords', 'switch', '', '0', 'SEO', 1247520057, 3000),
				('main', 'keywords_separator', 'string', '', '_', 'SEO', 1247520057, 4000);
			");
			
			$mysql="
				CREATE TABLE IF NOT EXISTS `apx_sessions` (
				  `id` varchar(32) NOT NULL,
				  `ownerid` varchar(32) NOT NULL,
				  `starttime` int(10) unsigned NOT NULL,
				  `data` text NOT NULL,
				  PRIMARY KEY (`id`,`ownerid`)
				) ENGINE=MyISAM;
				
				CREATE TABLE IF NOT EXISTS `apx_tags` (
				  `tagid` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `tag` varchar(40) NOT NULL,
				  PRIMARY KEY (`tagid`)
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_captcha` CHANGE `hash` `hash` VARCHAR( 32 ) NOT NULL ;
				ALTER TABLE `apx_comments` CHANGE `ip` `ip` VARCHAR( 15 ) NOT NULL ;
				ALTER TABLE `apx_comments` ADD `notify` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `time` ;
				ALTER TABLE `apx_log` CHANGE `time` `time` DATETIME NOT NULL ;
				ALTER TABLE `apx_search` CHANGE `object` `object` VARCHAR( 30 ) NOT NULL ;
				ALTER TABLE `apx_search` CHANGE `results` `results` MEDIUMTEXT NOT NULL;
				
				TRUNCATE TABLE `apx_loginfailed`;
				ALTER TABLE `apx_loginfailed` ADD PRIMARY KEY ( `userid` , `time` ) ;
				
				ALTER TABLE `apx_config` ADD INDEX ( `tab` , `ord` ) ;
				ALTER TABLE `apx_captcha` ADD INDEX ( `hash` ) ;
				ALTER TABLE `apx_search` ADD INDEX ( `searchid` , `object` , `time` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 120: //Zu 1.2.1
			$mysql="
				INSERT INTO `apx_config` VALUES ('main', 'entermode', 'select', 'a:2:{s:2:\"br\";s:10:\"&lt;br&gt;\";s:1:\"p\";s:9:\"&lt;p&gt;\";}', 'p', 'OPTIONS', '0', '11000');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 121: //Zu 1.2.2
			$mysql="
				INSERT INTO `apx_config` VALUES
				('main', 'tell', 'switch', '', '1', 'OPTIONS', '0', '7500'),
				('main', 'old_captcha', 'switch', '', '0', 'OPTIONS', '0', '12000') ;
				
				ALTER TABLE `apx_captcha` CHANGE `code` `code` VARCHAR( 5 ) NOT NULL ;
				
				CREATE TABLE `apx_search_item` (
				  `item` tinytext NOT NULL,
				  `time` int(10) unsigned NOT NULL
				) ENGINE=MyISAM ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
	}
}

?>