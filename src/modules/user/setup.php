<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Installieren
if ( SETUPMODE=='install' ) {
	$mysql="
		CREATE TABLE `apx_loginfailed` (
		  `userid` int(11) unsigned NOT NULL default '0',
		  `time` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`userid`,`time`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_user` (
		  `userid` int(11) unsigned NOT NULL auto_increment,
		  `username_login` tinytext NOT NULL,
		  `username` tinytext NOT NULL,
		  `password` tinytext NOT NULL,
		  `salt` varchar(10) NOT NULL default '',
		  `reg_time` int(11) unsigned NOT NULL default '0',
		  `reg_email` tinytext NOT NULL,
		  `reg_key` varchar(10) NOT NULL default '',
		  `groupid` int(3) unsigned NOT NULL default '0',
		  `active` tinyint(1) unsigned NOT NULL default '1',
		  `email` tinytext NOT NULL,
		  `homepage` tinytext NOT NULL,
		  `icq` int(9) unsigned NOT NULL default '0',
		  `aim` tinytext NOT NULL,
		  `yim` tinytext NOT NULL,
		  `msn` tinytext NOT NULL,
		  `skype` tinytext NOT NULL,
		  `realname` tinytext NOT NULL,
		  `gender` tinyint(1) unsigned NOT NULL default '0',
		  `birthday` tinytext NOT NULL,
		  `ageconfirmed` TINYINT( 1 ) UNSIGNED NOT NULL,
		  `city` tinytext NOT NULL,
		  `plz` varchar(5) NOT NULL,
		  `country` varchar(2) NOT NULL,
		  `locid` int(11) unsigned NOT NULL,
		  `interests` tinytext NOT NULL,
		  `work` tinytext NOT NULL,
		  `status` tinytext NOT NULL,
		  `status_smiley` varchar(10) NOT NULL,
		  `lastonline` int(11) unsigned NOT NULL default '0',
		  `lastactive` int(11) unsigned NOT NULL default '0',
		  `lastpwget` int(11) unsigned NOT NULL default '0',
		  `lastpwget_by` tinytext NOT NULL,
		  `admin_editor` tinyint(1) unsigned NOT NULL default '0',
		  `admin_lang` tinytext NOT NULL,
		  `pub_lang` tinytext NOT NULL,
		  `pub_invisible` tinyint(1) unsigned NOT NULL default '0',
		  `pub_hidemail` tinyint(1) unsigned NOT NULL default '0',
		  `pub_poppm` tinyint(1) unsigned NOT NULL default '1',
		  `pub_mailpm` tinyint(1) unsigned NOT NULL default '0',
		  `pub_showbuddies` tinyint(1) unsigned NOT NULL default '0',
		  `pub_usegb` tinyint(1) unsigned NOT NULL default '1',
		  `pub_profileforfriends` tinyint(1) unsigned NOT NULL default '0',
		  `pub_gbmail` tinyint(1) unsigned NOT NULL default '0',
		  `pub_theme` tinytext NOT NULL,
		  `pmpopup` tinyint(1) unsigned NOT NULL default '0',
		  `signature` text NOT NULL,
		  `avatar` tinytext NOT NULL,
		  `avatar_title` tinytext NOT NULL,
		  `custom1` tinytext NOT NULL,
		  `custom2` tinytext NOT NULL,
		  `custom3` tinytext NOT NULL,
		  `custom4` tinytext NOT NULL,
		  `custom5` tinytext NOT NULL,
		  `custom6` tinytext NOT NULL,
		  `custom7` tinytext NOT NULL,
		  `custom8` tinytext NOT NULL,
		  `custom9` tinytext NOT NULL,
		  `custom10` tinytext NOT NULL,
		  PRIMARY KEY  (`userid`),
		  KEY `country` (`country`),
		  KEY `active` (`active`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_user_blog` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `userid` int(11) unsigned NOT NULL default '0',
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  `allowcoms` tinyint(1) unsigned NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `userid` (`userid`),
		  KEY `time` (`time`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_user_bookmarks` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `userid` int(11) unsigned NOT NULL default '0',
		  `title` tinytext NOT NULL,
		  `url` tinytext NOT NULL,
		  `addtime` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `userid` (`userid`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_user_friends` (
		  `userid` int(11) unsigned NOT NULL,
		  `friendid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`userid`,`friendid`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_user_gallery` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `owner` int(11) unsigned NOT NULL,
		  `title` tinytext NOT NULL,
		  `description` text NOT NULL,
		  `password` tinytext NOT NULL,
		  `addtime` int(11) unsigned NOT NULL,
		  `allowcoms` tinyint(1) unsigned NOT NULL,
		  `lastupdate` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `owner` (`owner`),
		  KEY `lastupdate` (`lastupdate`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_user_groups` (
		  `groupid` int(11) unsigned NOT NULL auto_increment,
		  `name` tinytext NOT NULL,
		  `gtype` enum('admin','indiv','public','guest') NOT NULL default 'public',
		  `rights` text NOT NULL,
		  `sprights` text NOT NULL,
		  `section_access` text NOT NULL,
		  PRIMARY KEY  (`groupid`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_user_guestbook` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `owner` int(11) unsigned NOT NULL default '0',
		  `userid` int(11) unsigned NOT NULL default '0',
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  `ip` tinytext NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `owner` (`owner`),
		  KEY `time` (`time`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_user_ignore` (
		  `userid` int(11) unsigned NOT NULL,
		  `ignored` int(11) unsigned NOT NULL,
		  `reason` tinytext NOT NULL,
		  PRIMARY KEY  (`userid`,`ignored`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_user_locations` (
		  `id` int(11) NOT NULL,
		  `name` varchar(250) default NULL,
		  `b` float default NULL,
		  `l` float default NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_user_locations_plz` (
		  `locid` int(11) unsigned NOT NULL,
		  `plz` varchar(5) NOT NULL,
		  `stamp` varchar(8) NOT NULL,
		  PRIMARY KEY  (`locid`,`plz`),
		  KEY `stamp` (`stamp`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_user_navord` (
		  `userid` int(11) unsigned NOT NULL,
		  `module` varchar(30) NOT NULL,
		  `ord` tinyint(4) NOT NULL,
		  PRIMARY KEY  (`userid`,`module`)
		) ENGINE=MyISAM ROW_FORMAT=DYNAMIC;
		
		CREATE TABLE `apx_user_online` (
		  `userid` int(11) unsigned NOT NULL default '0',
		  `ip` int(11) unsigned NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  `invisible` tinyint(1) unsigned NOT NULL default '0',
		  `url` tinytext NOT NULL,
		  PRIMARY KEY  (`userid`,`ip`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_user_pictures` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `galid` int(11) unsigned NOT NULL default '0',
		  `thumbnail` tinytext NOT NULL,
		  `picture` tinytext NOT NULL,
		  `caption` tinytext NOT NULL,
		  `addtime` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `galid` (`galid`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_user_pms` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `fromuser` int(11) unsigned NOT NULL default '0',
		  `touser` int(11) unsigned NOT NULL default '0',
		  `subject` varchar(50) NOT NULL default '',
		  `text` text NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  `addsig` tinyint(1) unsigned NOT NULL default '1',
		  `isread` tinyint(1) unsigned NOT NULL default '0',
		  `del_from` tinyint(1) unsigned NOT NULL default '0',
		  `del_to` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `fromuser` (`fromuser`,`del_from`),
		  KEY `touser` (`touser`,`del_to`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_user_visits` (
		  `object` varchar(20) NOT NULL,
		  `id` int(11) unsigned NOT NULL,
		  `userid` int(11) unsigned NOT NULL,
		  `time` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`object`,`id`,`userid`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_user_groups` VALUES (1, 'Administratoren', 'admin', '', '', 'all');
		INSERT INTO `apx_user_groups` VALUES (2, 'Registrierte Benutzer', 'public', '', '', 'all');
		INSERT INTO `apx_user_groups` VALUES (3, 'Gäste', 'guest', '', '', 'all');
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('user', 'defaultgroup', 'int', 'BLOCK', '2', '', 0, 0),
		('user', 'onlinerecord', 'int', 'BLOCK', '0', '', 0, 0),
		('user', 'onlinerecord_time', 'int', 'BLOCK', '0', '', 0, 0),
		('user', 'sendmail_data', 'array', 'BLOCK', '', '', 0, 0),
		('user', 'sendpm_data', 'array', 'BLOCK', '', '', 0, 0),
		
		('user', 'userlistepp', 'int', '', '20', 'VIEW', 1219935740, 1000),
		('user', 'listactiveonly', 'switch', '', '1', 'VIEW', 1302091810, 1500),
		('user', 'profile_regonly', 'switch', '', '0', 'VIEW', 1219935740, 2000),
		('user', 'friendsepp', 'int', '', '20', 'VIEW', 1219935740, 3000),
		('user', 'visitorself', 'switch', '', '0', 'VIEW', 1219935740, 4000),
		('user', 'usermap_topleft_x', 'float', '', '5.85', 'VIEW', 1219935740, 5000),
		('user', 'usermap_topleft_y', 'float', '', '55.05', 'VIEW', 1219935740, 6000),
		('user', 'usermap_bottomright_x', 'float', '', '17.15', 'VIEW', 1219935740, 7000),
		('user', 'usermap_bottomright_y', 'float', '', '45.83', 'VIEW', 1219935740, 8000),
		('user', 'usermap_width', 'int', '', '651', 'VIEW', 1219935740, 9000),
		('user', 'usermap_height', 'int', '', '843', 'VIEW', 1219935740, 10000),
		
		('user', 'searchable', 'switch', '', '1', 'OPTIONS', 1219935740, 1000),
		('user', 'useractivation', 'select', 'a:3:{i:1;s:14:\"{ACTIVATEAUTO}\";i:2;s:15:\"{ACTIVATEADMIN}\";i:3;s:16:\"{ACTIVATEREGKEY}\";}', '3', 'OPTIONS', 1219935740, 2000),
		('user', 'reactivate', 'switch', '', '1', 'OPTIONS', 1219935740, 3000),
		('user', 'acceptrules', 'switch', '', '1', 'OPTIONS', 1219935740, 4000),
		('user', 'userminlen', 'int', '', '4', 'OPTIONS', 1219935740, 5000),
		('user', 'pwdminlen', 'int', '', '6', 'OPTIONS', 1219935740, 6000),
		('user', 'mailmultiacc', 'switch', '', '1', 'OPTIONS', 1219935740, 7000),
		('user', 'captcha', 'switch', '', '1', 'OPTIONS', 1219935740, 8000),
		('user', 'blockusername', 'array', '', 'a:3:{i:0;s:5:\"admin\";i:1;s:9:\"webmaster\";i:2;s:10:\"hostmaster\";}', 'OPTIONS', 1219935740, 9000),
		('user', 'cusfield_names', 'array', '', 'a:0:{}', 'OPTIONS', 1219935740, 10000),
		('user', 'timeout', 'int', '', '10', 'OPTIONS', 1219935740, 11000),
		('user', 'onlinelist', 'switch', '', '1', 'OPTIONS', '0', '11500'),
		('user', 'mailonnew', 'string', '', '', 'OPTIONS', 1219935740, 12000),
		('user', 'reportmail', 'string', '', '', 'OPTIONS', 1219935740, 13000),
		('user', 'sendmail_guests', 'switch', '', '0', 'OPTIONS', '0', '14000'),
		
		('user', 'avatar_maxsize', 'int', '', '10240', 'AVATAR', 1219935740, 1000),
		('user', 'avatar_maxdim', 'int', '', '100', 'AVATAR', 1219935740, 2000),
		('user', 'avatar_resize', 'switch', '', '0', 'AVATAR', 1219935740, 3000),
		('user', 'avatar_badwords', 'switch', '', '1', 'AVATAR', 1219935740, 4000),
		
		('user', 'sigmaxlen', 'int', '', '300', 'SIGNATURE', 1219935740, 1000),
		('user', 'sig_allowsmilies', 'switch', '', '1', 'SIGNATURE', 1219935740, 2000),
		('user', 'sig_allowcode', 'switch', '', '1', 'SIGNATURE', 1219935740, 3000),
		('user', 'sig_badwords', 'switch', '', '1', 'SIGNATURE', 1219935740, 4000),
		
		('user', 'maxpmcount', 'int', '', '500', 'PMS', 1219935740, 1000),
		('user', 'pm_allowsmilies', 'switch', '', '1', 'PMS', 1219935740, 2000),
		('user', 'pm_allowcode', 'switch', '', '1', 'PMS', 1219935740, 3000),
		('user', 'pm_badwords', 'switch', '', '1', 'PMS', 1219935740, 4000),
		
		('user', 'guestbook', 'switch', '', '1', 'GUESTBOOKCFG', 1219935740, 1000),
		('user', 'guestbook_epp', 'int', '', '5', 'GUESTBOOKCFG', 1219935740, 2000),
		('user', 'guestbook_req_title', 'switch', '', '0', 'GUESTBOOKCFG', 1219935740, 3000),
		('user', 'guestbook_allowsmilies', 'switch', '', '1', 'GUESTBOOKCFG', 1219935740, 4000),
		('user', 'guestbook_allowcode', 'switch', '', '1', 'GUESTBOOKCFG', 1219935740, 5000),
		('user', 'guestbook_badwords', 'switch', '', '1', 'GUESTBOOKCFG', 1219935740, 6000),
		('user', 'guestbook_maxlen', 'int', '', '10000', 'GUESTBOOKCFG', 1219935740, 7000),
		('user', 'guestbook_breakline', 'int', '', '0', 'GUESTBOOKCFG', 1219935740, 8000),
		('user', 'guestbook_spamprot', 'int', '', '1', 'GUESTBOOKCFG', 1219935740, 9000),
		('user', 'guestbook_useradmin', 'switch', '', '0', 'GUESTBOOKCFG', 1219935740, 10000),
		
		('user', 'blog', 'switch', '', '1', 'BLOGCFG', 1219935740, 1000),
		('user', 'blog_epp', 'int', '', '10', 'BLOGCFG', 1219935740, 2000),
		
		('user', 'gallery', 'switch', '', '1', 'GALLERYCFG', 1219935740, 1000),
		('user', 'gallery_epp', 'int', '', '20', 'GALLERYCFG', 1219935740, 2000),
		('user', 'gallery_picwidth', 'int', '', '640', 'GALLERYCFG', 1219935740, 3000),
		('user', 'gallery_picheight', 'int', '', '480', 'GALLERYCFG', 1219935740, 4000),
		('user', 'gallery_thumbwidth', 'int', '', '120', 'GALLERYCFG', 1219935740, 5000),
		('user', 'gallery_thumbheight', 'int', '', '90', 'GALLERYCFG', 1219935740, 6000),
		('user', 'gallery_quality_resize', 'switch', '', '1', 'GALLERYCFG', 1219935740, 7000),
		('user', 'gallery_maxpics', 'int', '', '0', 'GALLERYCFG', 1219935740, 8000);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
	
	//Locations einfügen
	require_once(BASEDIR.'lib/class.linereader.php');
	$locReader = new LineReader(BASEDIR.getmodulepath('user').'locations.sql', ";\n");
	$command = '';
	while ( ($line = $locReader->getNext())!==false ) {
		if ( $line ) {
			$line=str_replace('`apx_','`'.PRE.'_',$line);
			$db->query($line);
		}
	}
	
	//User-DIR
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$mm=new mediamanager;
	$mm->createdir('user');
	$mm->createdir('gallery','user');
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				ALTER TABLE `apx_user` ADD `skype` TINYTEXT NOT NULL AFTER `msn` ;
				ALTER TABLE `apx_user` ADD `pub_theme` TINYTEXT NOT NULL AFTER `pub_poppm` ;
				INSERT INTO `apx_config` VALUES ('user', 'searchable', 'switch', '', '1', '0', '50');
				INSERT INTO `apx_config` VALUES ('user', 'captcha', 'switch', '', '0', '0', '450');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 101: //zu 1.0.2
			$mysql="
				UPDATE `apx_config` SET varname = 'captcha' WHERE module = 'user' AND varname = 'capcha';
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 102: //zu 1.0.3
			$mysql="
				INSERT INTO `apx_config` VALUES ('user', 'avatar_resize', 'switch', '', '0', '0', '950');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 103: //zu 1.0.4
			$mysql="
				ALTER TABLE `apx_user` ADD `pub_mailpm` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `pub_poppm` ;
				INSERT INTO `apx_config` VALUES ('user', 'mailonnew', 'string', '', '', '0', '2000');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 104: //zu 1.0.5
			$mysql="
				DELETE FROM `apx_config` WHERE ( module='user' AND varname='regkey' );
				INSERT INTO `apx_config` VALUES ('user', 'userminlen', 'int', '', '4', '0', '420');
				INSERT INTO `apx_config` VALUES ('user', 'pwdminlen', 'int', '', '6', '0', '430');
				INSERT INTO `apx_config` VALUES ('user', 'useractivation', 'select', 'a:3:{i:1;s:14:\"{ACTIVATEAUTO}\";i:2;s:15:\"{ACTIVATEADMIN}\";i:3;s:16:\"{ACTIVATEREGKEY}\";}', '3', '0', '200');
				INSERT INTO `apx_config` VALUES ('user', 'mailmultiacc', 'switch', '', '0', '0', '440');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 105: //zu 1.0.6
			$mysql="
				INSERT INTO `apx_config` VALUES ('user', 'defaultgroup', 'int', 'BLOCK', '2', '0', '0');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 106: //zu 1.1.0
			$mysql="
				CREATE TABLE `apx_user_blog` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `userid` int(11) unsigned NOT NULL default '0',
				  `title` tinytext NOT NULL,
				  `text` text NOT NULL,
				  `time` int(11) unsigned NOT NULL default '0',
				  PRIMARY KEY  (`id`),
				  KEY `userid` (`userid`)
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_user_bookmarks` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `userid` int(11) unsigned NOT NULL default '0',
				  `title` tinytext NOT NULL,
				  `url` tinytext NOT NULL,
				  `addtime` int(11) unsigned NOT NULL default '0',
				  PRIMARY KEY  (`id`),
				  KEY `userid` (`userid`)
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_user_gallery` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `owner` int(11) unsigned NOT NULL,
				  `title` tinytext NOT NULL,
				  `description` text NOT NULL,
				  `password` tinytext NOT NULL,
				  `addtime` int(11) unsigned NOT NULL,
				  `lastupdate` int(11) unsigned NOT NULL,
				  PRIMARY KEY  (`id`),
				  KEY `owner` (`owner`)
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_user_guestbook` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `owner` int(11) unsigned NOT NULL default '0',
				  `userid` int(11) unsigned NOT NULL default '0',
				  `title` tinytext NOT NULL,
				  `text` text NOT NULL,
				  `time` int(11) unsigned NOT NULL default '0',
				  `ip` tinytext NOT NULL,
				  PRIMARY KEY  (`id`),
				  KEY `owner` (`owner`)
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_user_locations` (
				  `location` varchar(8) NOT NULL,
				  `plz` varchar(5) NOT NULL,
				  `country` varchar(2) NOT NULL,
				  `name` tinytext NOT NULL,
				  `l` float NOT NULL,
				  `b` float NOT NULL,
				  KEY `location` (`location`)
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_user_pictures` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `galid` int(11) unsigned NOT NULL default '0',
				  `thumbnail` tinytext NOT NULL,
				  `picture` tinytext NOT NULL,
				  `caption` tinytext NOT NULL,
				  `addtime` int(11) unsigned NOT NULL default '0',
				  PRIMARY KEY  (`id`),
				  KEY `galid` (`galid`)
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_user` ADD `pub_showbuddies` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `pub_mailpm` ;
				ALTER TABLE `apx_user` ADD `pub_gbmail` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `pub_showbuddies` ;
				ALTER TABLE `apx_user` ADD `plz` VARCHAR( 5 ) NOT NULL AFTER `city` ,ADD `country` VARCHAR( 2 ) NOT NULL AFTER `plz` ;
				ALTER TABLE `apx_user` ADD INDEX ( `country` ) ;
				UPDATE `apx_config` SET ord=ord+3000 WHERE module='user' AND ord>=1900;
				INSERT INTO `apx_config` VALUES ('user', 'guestbook_epp', 'int', '', '5', 1129897987, 1900);
				INSERT INTO `apx_config` VALUES ('user', 'guestbook_req_title', 'switch', '', '0', 1129897987, 2000);
				INSERT INTO `apx_config` VALUES ('user', 'guestbook_allowsmilies', 'switch', '', '1', 1129897987, 2100);
				INSERT INTO `apx_config` VALUES ('user', 'guestbook_allowcode', 'switch', '', '1', 1129897987, 2200);
				INSERT INTO `apx_config` VALUES ('user', 'guestbook_badwords', 'switch', '', '1', 1129897987, 2300);
				INSERT INTO `apx_config` VALUES ('user', 'guestbook_maxlen', 'int', '', '10000', 1129897987, 2400);
				INSERT INTO `apx_config` VALUES ('user', 'guestbook_breakline', 'int', '', '0', 1129897987, 2500);
				INSERT INTO `apx_config` VALUES ('user', 'guestbook_spamprot', 'int', '', '1', 1129897987, 2600);
				INSERT INTO `apx_config` VALUES ('user', 'guestbook_useradmin', 'switch', '', '1', 1129897987, 2700);
				INSERT INTO `apx_config` VALUES ('user', 'blog_epp', 'int', '', '10', '0', '3000');
				INSERT INTO `apx_config` VALUES ('user', 'guestbook', 'switch', '', '1', '0', '1850');
				INSERT INTO `apx_config` VALUES ('user', 'blog', 'switch', '', '1', '0', '2900');
				INSERT INTO `apx_config` VALUES ('user', 'gallery', 'switch', '', '1', '0', '4000');
				INSERT INTO `apx_config` VALUES ('user', 'gallery_picwidth', 'int', '', '640', 1129898036, 4100);
				INSERT INTO `apx_config` VALUES ('user', 'gallery_picheight', 'int', '', '480', 1129898036, 4200);
				INSERT INTO `apx_config` VALUES ('user', 'gallery_thumbwidth', 'int', '', '120', 1129898036, 4300);
				INSERT INTO `apx_config` VALUES ('user', 'gallery_thumbheight', 'int', '', '90', 1129898036, 4400);
				INSERT INTO `apx_config` VALUES ('user', 'gallery_quality_resize', 'switch', '', '1', 1129898036, 4500);
				INSERT INTO `apx_config` VALUES ('user', 'gallery_epp', 'int', '', '20', '0', '4050');
				INSERT INTO `apx_config` VALUES ('user', 'usermap_topleft_x', 'float', '', '5.85', '0', '6000');
				INSERT INTO `apx_config` VALUES ('user', 'usermap_topleft_y', 'float', '', '55.05', '0', '6100');
				INSERT INTO `apx_config` VALUES ('user', 'usermap_bottomright_x', 'float', '', '17.15', '0', '6200');
				INSERT INTO `apx_config` VALUES ('user', 'usermap_bottomright_y', 'float', '', '45.83', '0', '6300');
				INSERT INTO `apx_config` VALUES ('user', 'usermap_width', 'int', '', '651', '0', '6400');
				INSERT INTO `apx_config` VALUES ('user', 'usermap_height', 'int', '', '843', '0', '6500');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			//Locations einfügen
			/*require_once(BASEDIR.'lib/class.linereader.php');
			$locReader = new LineReader(BASEDIR.getmodulepath('user').'locations.sql', ";\n");
			$command = '';
			while ( ($line = $locReader->getNext())!==false ) {
				if ( $line ) {
					$line=str_replace('`apx_','`'.PRE.'_',$line);
					$db->query($line);
				}
			}*/
			
			//User-Gallery-DIR
			require_once(BASEDIR.'lib/class.mediamanager.php');
			$mm=new mediamanager;
			$mm->createdir('gallery','user');
		
		
		case 110: //zu 1.1.1
			$mysql="
				INSERT INTO `apx_config` VALUES ('user', 'visitorself', 'switch', '', '', '0', '1450');
				INSERT INTO `apx_config` VALUES ('user', 'gallery_maxpics', 'int', '', '', '0', '4550');
				INSERT INTO `apx_config` VALUES ('user', 'friendsepp', 'int', '', '20', '0', '4800');
				INSERT INTO `apx_config` VALUES ('user', 'reportmail', 'string', '', '', '0', '5500');
				INSERT INTO `apx_config` VALUES ('user', 'onlinerecord', 'int', 'BLOCK', '', '0', '0');
				INSERT INTO `apx_config` VALUES ('user', 'onlinerecord_time', 'int', 'BLOCK', '', '0', '0');
				ALTER TABLE `apx_user` ADD `locid` INT( 11 ) UNSIGNED NOT NULL AFTER `country` ;
				ALTER TABLE `apx_user` ADD `pub_usegb` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `pub_showbuddies` ;
				ALTER TABLE `apx_user_online` CHANGE `ip` `ip` VARCHAR( 15 ) NOT NULL  ;
				ALTER TABLE `apx_user_online` ADD INDEX ( `time` ) ;
				DROP TABLE IF EXISTS `apx_user_locations`;
				
				CREATE TABLE `apx_user_friends` (
				  `userid` int(11) unsigned NOT NULL,
				  `friendid` int(11) unsigned NOT NULL,
				  PRIMARY KEY  (`userid`,`friendid`)
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_user_ignore` (
				  `userid` int(11) unsigned NOT NULL,
				  `ignored` int(11) unsigned NOT NULL,
				  `reason` tinytext NOT NULL,
				  PRIMARY KEY  (`userid`,`ignored`)
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_user_visits` (
				  `object` varchar(20) NOT NULL,
				  `id` int(11) unsigned NOT NULL,
				  `userid` int(11) unsigned NOT NULL,
				  `time` int(11) unsigned NOT NULL,
				  PRIMARY KEY  (`object`,`id`,`userid`)
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_user_locations` (
				  `id` int(11) NOT NULL,
				  `name` varchar(250) default NULL,
				  `b` float default NULL,
				  `l` float default NULL,
				  PRIMARY KEY  (`id`)
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_user_locations_plz` (
				  `locid` int(11) unsigned NOT NULL,
				  `plz` varchar(5) NOT NULL,
				  `stamp` varchar(8) NOT NULL,
				  PRIMARY KEY  (`locid`,`plz`),
				  KEY `stamp` (`stamp`)
				) ENGINE=MyISAM;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			//Locations einfügen
			/*require_once(BASEDIR.'lib/class.linereader.php');
			$locReader = new LineReader(BASEDIR.getmodulepath('user').'locations.sql', ";\n");
			$command = '';
			while ( ($line = $locReader->getNext())!==false ) {
				if ( $line ) {
					$line=str_replace('`apx_','`'.PRE.'_',$line);
					$db->query($line);
				}
			}*/
			
			//CityMatch laden
			include(BASEDIR.getmodulepath('user').'citymatch.php');
			
			//Updates auf Tabellen durchführen
			$data = $db->fetch("SELECT userid,city,plz,country,buddies,birthday FROM ".PRE."_user");
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					
					//Buddies
					$buddies = unserialize($res['buddies']);
					if ( is_array($buddies) ) {
						foreach ( $buddies AS $id ) {
							$db->query("INSERT INTO ".PRE."_user_friends VALUES ('".$res['userid']."','".$id."')");
						}
					}
					
					//Location
					$locid = user_get_location($res['plz'],$res['city'],$res['country']);
					
					//Geburtstag
					$bd = explode('-',$res['birthday']);
					if ( $bd[2] ) {
						$birthday = sprintf('%02d-%02d-%04d',$bd[0],$bd[1],$bd[2]);
					}
					else {
						$birthday = sprintf('%02d-%02d',$bd[0],$bd[1]);
					}
					
					//Update Tabelle
					$db->query("UPDATE ".PRE."_user SET locid='".$locid."',birthday='".$birthday."' WHERE userid='".$res['userid']."' LIMIT 1");
				}
			}
			$db->query("UPDATE ".PRE."_user SET birthday='' WHERE birthday='00-00'");
			$db->query("ALTER TABLE ".PRE."_user DROP `buddies` ;");
		
		
		case 111: //zu 1.1.2
			$mysql="
				INSERT INTO `apx_config` VALUES ('user', 'sendmail_data', 'array', 'BLOCK', '', '0', '0');
				INSERT INTO `apx_config` VALUES ('user', 'sendpm_data', 'array', 'BLOCK', '', '0', '0');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 112: //zu 1.1.3
			$mysql="
				ALTER TABLE `apx_user` ADD `pub_profileforfriends` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `pub_usegb` ;
				ALTER TABLE `apx_user_gallery` ADD `allowcoms` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `addtime` ;
				ALTER TABLE `apx_user_blog` ADD `allowcoms` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `time` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 113: //zu 1.1.4
			list($check) = $db->first("SELECT module FROM ".PRE."_config WHERE module='user' AND varname='avatar_resize' LIMIT 1");
			if ( !$check ) {
				$db->query("INSERT INTO ".PRE."_config VALUES ('user', 'avatar_resize', 'switch', '', '0', '0', '950')");
			}
		
		
		case 114: //zu 1.2.0
			
			//Indizes entfernen
			clearIndices(PRE.'_user');
			clearIndices(PRE.'_user_blog');
			clearIndices(PRE.'_user_bookmarks');
			clearIndices(PRE.'_user_gallery');
			clearIndices(PRE.'_user_guestbook');
			clearIndices(PRE.'_user_online');
			clearIndices(PRE.'_user_pictures');
			clearIndices(PRE.'_user_pms');
			
			//config Update
			updateConfig('user', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('user', 'defaultgroup', 'int', 'BLOCK', '2', '', 0, 0),
				('user', 'onlinerecord', 'int', 'BLOCK', '0', '', 0, 0),
				('user', 'onlinerecord_time', 'int', 'BLOCK', '0', '', 0, 0),
				('user', 'sendmail_data', 'array', 'BLOCK', '', '', 0, 0),
				('user', 'sendpm_data', 'array', 'BLOCK', '', '', 0, 0),
				
				('user', 'userlistepp', 'int', '', '20', 'VIEW', 1219935740, 1000),
				('user', 'profile_regonly', 'switch', '', '0', 'VIEW', 1219935740, 2000),
				('user', 'friendsepp', 'int', '', '20', 'VIEW', 1219935740, 3000),
				('user', 'visitorself', 'switch', '', '0', 'VIEW', 1219935740, 4000),
				('user', 'usermap_topleft_x', 'float', '', '5.85', 'VIEW', 1219935740, 5000),
				('user', 'usermap_topleft_y', 'float', '', '55.05', 'VIEW', 1219935740, 6000),
				('user', 'usermap_bottomright_x', 'float', '', '17.15', 'VIEW', 1219935740, 7000),
				('user', 'usermap_bottomright_y', 'float', '', '45.83', 'VIEW', 1219935740, 8000),
				('user', 'usermap_width', 'int', '', '651', 'VIEW', 1219935740, 9000),
				('user', 'usermap_height', 'int', '', '843', 'VIEW', 1219935740, 10000),
				
				('user', 'searchable', 'switch', '', '1', 'OPTIONS', 1219935740, 1000),
				('user', 'useractivation', 'select', 'a:3:{i:1;s:14:\"{ACTIVATEAUTO}\";i:2;s:15:\"{ACTIVATEADMIN}\";i:3;s:16:\"{ACTIVATEREGKEY}\";}', '3', 'OPTIONS', 1219935740, 2000),
				('user', 'reactivate', 'switch', '', '1', 'OPTIONS', 1219935740, 3000),
				('user', 'acceptrules', 'switch', '', '1', 'OPTIONS', 1219935740, 4000),
				('user', 'userminlen', 'int', '', '4', 'OPTIONS', 1219935740, 5000),
				('user', 'pwdminlen', 'int', '', '6', 'OPTIONS', 1219935740, 6000),
				('user', 'mailmultiacc', 'switch', '', '1', 'OPTIONS', 1219935740, 7000),
				('user', 'captcha', 'switch', '', '1', 'OPTIONS', 1219935740, 8000),
				('user', 'blockusername', 'array', '', 'a:3:{i:0;s:5:\"admin\";i:1;s:9:\"webmaster\";i:2;s:10:\"hostmaster\";}', 'OPTIONS', 1219935740, 9000),
				('user', 'cusfield_names', 'array', '', 'a:0:{}', 'OPTIONS', 1219935740, 10000),
				('user', 'timeout', 'int', '', '10', 'OPTIONS', 1219935740, 11000),
				('user', 'mailonnew', 'string', '', '', 'OPTIONS', 1219935740, 12000),
				('user', 'reportmail', 'string', '', '', 'OPTIONS', 1219935740, 13000),
				
				('user', 'avatar_maxsize', 'int', '', '10240', 'AVATAR', 1219935740, 1000),
				('user', 'avatar_maxdim', 'int', '', '100', 'AVATAR', 1219935740, 2000),
				('user', 'avatar_resize', 'switch', '', '0', 'AVATAR', 1219935740, 3000),
				('user', 'avatar_badwords', 'switch', '', '1', 'AVATAR', 1219935740, 4000),
				
				('user', 'sigmaxlen', 'int', '', '300', 'SIGNATURE', 1219935740, 1000),
				('user', 'sig_allowsmilies', 'switch', '', '1', 'SIGNATURE', 1219935740, 2000),
				('user', 'sig_allowcode', 'switch', '', '1', 'SIGNATURE', 1219935740, 3000),
				('user', 'sig_badwords', 'switch', '', '1', 'SIGNATURE', 1219935740, 4000),
				
				('user', 'maxpmcount', 'int', '', '500', 'PMS', 1219935740, 1000),
				('user', 'pm_allowsmilies', 'switch', '', '1', 'PMS', 1219935740, 2000),
				('user', 'pm_allowcode', 'switch', '', '1', 'PMS', 1219935740, 3000),
				('user', 'pm_badwords', 'switch', '', '1', 'PMS', 1219935740, 4000),
				
				('user', 'guestbook', 'switch', '', '1', 'GUESTBOOKCFG', 1219935740, 1000),
				('user', 'guestbook_epp', 'int', '', '5', 'GUESTBOOKCFG', 1219935740, 2000),
				('user', 'guestbook_req_title', 'switch', '', '0', 'GUESTBOOKCFG', 1219935740, 3000),
				('user', 'guestbook_allowsmilies', 'switch', '', '1', 'GUESTBOOKCFG', 1219935740, 4000),
				('user', 'guestbook_allowcode', 'switch', '', '1', 'GUESTBOOKCFG', 1219935740, 5000),
				('user', 'guestbook_badwords', 'switch', '', '1', 'GUESTBOOKCFG', 1219935740, 6000),
				('user', 'guestbook_maxlen', 'int', '', '10000', 'GUESTBOOKCFG', 1219935740, 7000),
				('user', 'guestbook_breakline', 'int', '', '0', 'GUESTBOOKCFG', 1219935740, 8000),
				('user', 'guestbook_spamprot', 'int', '', '1', 'GUESTBOOKCFG', 1219935740, 9000),
				('user', 'guestbook_useradmin', 'switch', '', '0', 'GUESTBOOKCFG', 1219935740, 10000),
				
				('user', 'blog', 'switch', '', '1', 'BLOGCFG', 1219935740, 1000),
				('user', 'blog_epp', 'int', '', '10', 'BLOGCFG', 1219935740, 2000),
				
				('user', 'gallery', 'switch', '', '1', 'GALLERYCFG', 1219935740, 1000),
				('user', 'gallery_epp', 'int', '', '20', 'GALLERYCFG', 1219935740, 2000),
				('user', 'gallery_picwidth', 'int', '', '640', 'GALLERYCFG', 1219935740, 3000),
				('user', 'gallery_picheight', 'int', '', '480', 'GALLERYCFG', 1219935740, 4000),
				('user', 'gallery_thumbwidth', 'int', '', '120', 'GALLERYCFG', 1219935740, 5000),
				('user', 'gallery_thumbheight', 'int', '', '90', 'GALLERYCFG', 1219935740, 6000),
				('user', 'gallery_quality_resize', 'switch', '', '1', 'GALLERYCFG', 1219935740, 7000),
				('user', 'gallery_maxpics', 'int', '', '0', 'GALLERYCFG', 1219935740, 8000);
			");
			
			$mysql="
				CREATE TABLE `apx_user_navord` (
				  `userid` int(11) unsigned NOT NULL,
				  `module` varchar(30) NOT NULL,
				  `ord` tinyint(4) NOT NULL,
				  PRIMARY KEY  (`userid`,`module`)
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_user_online` CHANGE `ip` `ip` INT( 11 ) UNSIGNED NOT NULL ;
				TRUNCATE TABLE `apx_user_online` ;
				ALTER TABLE `apx_user_online` ADD PRIMARY KEY ( `userid` , `ip` ) ;
				
				ALTER TABLE `apx_user` ADD INDEX ( `country` ) ;
				ALTER TABLE `apx_user` ADD INDEX ( `active` ) ;
				ALTER TABLE `apx_user_blog` ADD INDEX ( `userid` ) ;
				ALTER TABLE `apx_user_blog` ADD INDEX ( `time` ) ;
				ALTER TABLE `apx_user_bookmarks` ADD INDEX ( `userid` ) ;
				ALTER TABLE `apx_user_gallery` ADD INDEX ( `owner` ) ;
				ALTER TABLE `apx_user_gallery` ADD INDEX ( `lastupdate` ) ;
				ALTER TABLE `apx_user_guestbook` ADD INDEX ( `owner` ) ;
				ALTER TABLE `apx_user_guestbook` ADD INDEX ( `time` ) ;
				ALTER TABLE `apx_user_pictures` ADD INDEX ( `galid` ) ;
				ALTER TABLE `apx_user_pms` ADD INDEX ( `fromuser` , `del_from` ) ;
				ALTER TABLE `apx_user_pms` ADD INDEX ( `touser` , `del_to` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 120: //zu 1.2.1
			$mysql="
				INSERT IGNORE INTO `apx_user_locations_plz` VALUES (20112, '04328', 'DE-04328');
				ALTER TABLE `apx_user` ADD `ageconfirmed` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `birthday` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 121: //zu 1.2.2
			$mysql="
				ALTER TABLE `apx_user` ADD `status` TINYTEXT NOT NULL AFTER `work`, ADD `status_smiley` VARCHAR( 10 ) NOT NULL AFTER `status` ;
				TRUNCATE `apx_user_locations`;
				TRUNCATE `apx_user_locations_plz`;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			//Locations einfügen
			require_once(BASEDIR.'lib/class.linereader.php');
			$locReader = new LineReader(BASEDIR.getmodulepath('user').'locations.sql', ";\n");
			$command = '';
			while ( ($line = $locReader->getNext())!==false ) {
				if ( $line ) {
					$line=str_replace('`apx_','`'.PRE.'_',$line);
					$db->query($line);
				}
			}
			
			//CityMatch laden
			include(BASEDIR.getmodulepath('user').'citymatch.php');
			
			//Locations aktualisieren
			$data = $db->fetch("SELECT userid,city,plz,country FROM ".PRE."_user");
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					
					//Location
					$locid = user_get_location($res['plz'],$res['city'],$res['country']);
					
					//Update Tabelle
					$db->query("UPDATE ".PRE."_user SET locid='".$locid."' WHERE userid='".$res['userid']."' LIMIT 1");
				}
			}
		
		
		case 122: //zu 1.2.3
			$mysql="
				INSERT INTO `apx_config` VALUES ('user', 'sendmail_guests', 'switch', '', '0', 'OPTIONS', '0', '14000');
				INSERT INTO `apx_config` VALUES ('user', 'onlinelist', 'switch', '', '1', 'OPTIONS', '0', '11500');
				INSERT INTO `apx_config` VALUES ('user', 'listactiveonly', 'switch', '', '1', 'VIEW', 1302091810, 1500);
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
	}
}

?>