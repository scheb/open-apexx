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
		CREATE TABLE `apx_forums` (
		  `forumid` int(11) unsigned NOT NULL auto_increment,
		  `iscat` tinyint(1) unsigned NOT NULL default '0',
		  `title` tinytext NOT NULL,
		  `description` text NOT NULL,
		  `meta_description` text NOT NULL,
		  `link` tinytext NOT NULL,
		  `moderator` tinytext NOT NULL,
		  `password` tinytext NOT NULL,
		  `open` tinyint(1) unsigned NOT NULL default '0',
		  `searchable` tinyint(1) unsigned NOT NULL default '0',
		  `countposts` tinyint(1) unsigned NOT NULL default '0',
		  `inherit` tinyint(1) NOT NULL default '0',
		  `stylesheet` tinytext NOT NULL,
		  `right_visible` tinytext NOT NULL,
		  `right_read` tinytext NOT NULL,
		  `right_open` tinytext NOT NULL,
		  `right_announce` tinytext NOT NULL,
		  `right_post` tinytext NOT NULL,
		  `right_editpost` tinytext NOT NULL,
		  `right_delpost` tinytext NOT NULL,
		  `right_delthread` tinytext NOT NULL,
		  `right_addattachment` tinytext NOT NULL,
		  `right_readattachment` tinytext NOT NULL,
		  `parents` varchar(255) NOT NULL default '',
		  `children` text NOT NULL,
		  `lastpost` int(11) unsigned NOT NULL,
		  `lastposter` tinytext NOT NULL,
		  `lastposter_userid` int(11) unsigned NOT NULL default '0',
		  `lastposttime` int(11) unsigned NOT NULL default '0',
		  `lastthread` int(11) unsigned NOT NULL,
		  `lastthread_title` tinytext NOT NULL,
		  `lastthread_icon` int(11) NOT NULL default '-1',
		  `lastthread_prefix` int(11) unsigned NOT NULL,
		  `threads` int(11) unsigned NOT NULL default '0',
		  `posts` int(11) unsigned NOT NULL default '0',
		  `ord` smallint(5) unsigned NOT NULL default '1',
		  PRIMARY KEY  (`forumid`),
		  KEY `parents` (`parents`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_forum_activity` (
		  `userid` int(11) unsigned NOT NULL,
		  `ip` int(11) unsigned NOT NULL,
		  `type` enum('forum','thread') NOT NULL,
		  `id` int(11) unsigned NOT NULL,
		  `time` int(11) unsigned NOT NULL,
		  `invisible` tinyint(1) unsigned NOT NULL,
		  PRIMARY KEY  (`userid`,`ip`,`type`,`id`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_forum_anndisplay` (
		  `id` int(11) unsigned NOT NULL,
		  `forumid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`,`forumid`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_forum_announcements` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `userid` int(11) unsigned NOT NULL,
		  `title` varchar(255) NOT NULL default '',
		  `text` text NOT NULL,
		  `addtime` int(11) unsigned NOT NULL,
		  `starttime` int(11) unsigned NOT NULL,
		  `endtime` int(11) unsigned NOT NULL,
		  `views` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `starttime` (`starttime`,`endtime`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_forum_attachments` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `postid` int(11) unsigned NOT NULL default '0',
		  `hash` tinytext NOT NULL,
		  `file` tinytext NOT NULL,
		  `thumbnail` tinytext NOT NULL,
		  `name` tinytext NOT NULL,
		  `size` int(11) unsigned NOT NULL default '0',
		  `mime` tinytext NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `postid` (`postid`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_forum_filetypes` (
		  `ext` varchar(10) NOT NULL default '',
		  `icon` tinytext NOT NULL,
		  `size` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`ext`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_forum_index` (
		  `word` varchar(50) NOT NULL default '',
		  `threadid` mediumint(11) unsigned NOT NULL default '0',
		  `postid` int(11) unsigned NOT NULL default '0',
		  `istitle` tinyint(1) unsigned NOT NULL default '0',
		  KEY `word` (`word`),
		  KEY `postid` (`postid`,`istitle`),
		  KEY `threadid` (`threadid`,`istitle`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_forum_posts` (
		  `postid` int(11) unsigned NOT NULL auto_increment,
		  `threadid` int(11) unsigned NOT NULL default '0',
		  `userid` int(11) unsigned NOT NULL default '0',
		  `username` varchar(100) NOT NULL default '',
		  `title` tinytext NOT NULL,
		  `text` mediumtext NOT NULL,
		  `allowsmilies` tinyint(1) unsigned NOT NULL default '0',
		  `allowcodes` tinyint(1) unsigned NOT NULL default '0',
		  `allowsig` tinyint(1) unsigned NOT NULL default '0',
		  `time` int(11) unsigned NOT NULL default '0',
		  `lastedit_by` tinytext NOT NULL,
		  `lastedit_time` int(11) unsigned NOT NULL default '0',
		  `del` tinyint(1) unsigned NOT NULL default '0',
		  `ip` tinytext NOT NULL,
		  `hash` tinytext NOT NULL,
		  PRIMARY KEY  (`postid`),
		  KEY `time` (`time`),
		  KEY `threadid` (`threadid`),
		  KEY `del` (`del`),
		  KEY `username` (`username`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_forum_prefixes` (
		  `prefixid` int(11) unsigned NOT NULL auto_increment,
		  `forumid` int(11) unsigned NOT NULL,
		  `title` tinytext NOT NULL,
		  `code` tinytext NOT NULL,
		  PRIMARY KEY  (`prefixid`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_forum_ranks` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` text NOT NULL,
		  `color` varchar(6) NOT NULL default '',
		  `image` tinytext NOT NULL,
		  `minposts` int(11) NOT NULL default '-1',
		  `userid` int(11) NOT NULL default '0',
		  `groupid` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `userid` (`userid`,`groupid`,`minposts`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_forum_search` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `userid` int(11) unsigned NOT NULL default '0',
		  `result` mediumtext NOT NULL,
		  `display` enum('threads','posts') NOT NULL default 'threads',
		  `highlight` text NOT NULL,
		  `ignored` text NOT NULL,
		  `order_field` varchar(20) NOT NULL default '',
		  `order_dir` enum('ASC','DESC') NOT NULL default 'ASC',
		  `time` int(10) unsigned NOT NULL default '0',
		  `hash` varchar(32) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `hash` (`hash`,`time`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_forum_subscriptions` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `userid` int(11) unsigned NOT NULL default '0',
		  `type` enum('forum','thread') NOT NULL default 'forum',
		  `source` int(11) unsigned NOT NULL default '0',
		  `notification` enum('none','instant','daily','weekly') NOT NULL default 'none',
		  PRIMARY KEY  (`id`),
		  KEY `userid` (`userid`),
		  KEY `notification` (`notification`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_forum_threads` (
		  `threadid` int(11) unsigned NOT NULL auto_increment,
		  `forumid` int(11) unsigned NOT NULL default '0',
		  `prefix` int(11) unsigned NOT NULL,
		  `title` varchar(255) NOT NULL default '',
		  `icon` int(11) NOT NULL default '-1',
		  `opener` tinytext NOT NULL,
		  `opener_userid` int(11) unsigned NOT NULL default '0',
		  `opentime` int(11) unsigned NOT NULL default '0',
		  `firstpost` int(11) unsigned NOT NULL default '0',
		  `lastposter` tinytext NOT NULL,
		  `lastposter_userid` int(11) unsigned NOT NULL default '0',
		  `lastposttime` int(11) unsigned NOT NULL default '0',
		  `lastpost` int(11) unsigned NOT NULL default '0',
		  `del` tinyint(1) unsigned NOT NULL default '0',
		  `moved` int(11) unsigned NOT NULL default '0',
		  `open` tinyint(1) unsigned NOT NULL default '0',
		  `sticky` tinyint(1) unsigned NOT NULL default '0',
		  `sticky_text` tinytext NOT NULL,
		  `views` int(11) unsigned NOT NULL default '0',
		  `posts` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`threadid`),
		  KEY `forumid` (`forumid`,`del`)
		) ENGINE=MyISAM ;
		
		ALTER TABLE `apx_user` ADD `forum_lastactive` INT( 11 ) UNSIGNED NOT NULL , ADD `forum_lastonline` INT( 11 ) UNSIGNED NOT NULL , ADD `forum_posts` INT( 11 ) UNSIGNED NOT NULL ;
		ALTER TABLE `apx_user` ADD `forum_tpp` INT( 11 ) UNSIGNED NOT NULL AFTER `forum_posts` , ADD `forum_ppp` INT( 11 ) UNSIGNED NOT NULL AFTER `forum_tpp` ;
		ALTER TABLE `apx_user` ADD `forum_autosubscribe` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `forum_posts` ;
		UPDATE `apx_user` SET forum_lastactive=lastactive,forum_lastonline=lastonline;
		
		INSERT INTO `apx_cron` VALUES ('subscr_instant', 'forum', 300, 1151708400, '');
		INSERT INTO `apx_cron` VALUES ('subscr_daily', 'forum', 86400, 1151708400, '');
		INSERT INTO `apx_cron` VALUES ('subscr_weekly', 'forum', 604800, 1151708400, '');
		INSERT INTO `apx_cron` VALUES ('subscr_forum_daily', 'forum', 86400, 1151708400, '');
		INSERT INTO `apx_cron` VALUES ('subscr_forum_weekly', 'forum', 604800, 1151708400, '');
		INSERT INTO `apx_cron` VALUES ('clean', 'forum', '86400', '1268002800', '');
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('forum', 'icons', 'array', 'BLOCK', 'a:6:{i:0;a:2:{s:4:\"file\";s:25:\"/design/smilies/smile.gif\";s:3:\"ord\";i:1;}i:1;a:2:{s:4:\"file\";s:27:\"/design/smilies/shinner.gif\";s:3:\"ord\";i:5;}i:2;a:2:{s:4:\"file\";s:29:\"/design/smilies/angryfire.gif\";s:3:\"ord\";i:4;}i:3;a:2:{s:4:\"file\";s:25:\"/design/smilies/frown.gif\";s:3:\"ord\";i:3;}i:4;a:2:{s:4:\"file\";s:27:\"/design/smilies/biggrin.gif\";s:3:\"ord\";i:0;}i:5;a:2:{s:4:\"file\";s:25:\"/design/smilies/frage.gif\";s:3:\"ord\";i:2;}}', '', 0, 0),
		('forum', 'rate_possible', 'array_keys', 'BLOCK', 'a:5:{i:1;s:1:\"1\";i:2;s:1:\"2\";i:3;s:1:\"3\";i:4;s:1:\"4\";i:5;s:1:\"5\";}', '', 1146074744, 0),
		('forum', 'directory', 'string', 'BLOCK', 'forum', '', 0, 0),
		('forum', 'rate_digits', 'int', 'BLOCK', '0', '', 0, 0),
		
		('forum', 'forumtitle', 'string', '', 'apexx Forum', 'VIEW', 1213548598, 1000),
		('forum', 'tpp', 'int', '', '20', 'VIEW', 1213548598, 2000),
		('forum', 'ppp', 'int', '', '10', 'VIEW', 1213548598, 3000),
		('forum', 'hot_posts', 'int', '', '30', 'VIEW', 1213548598, 4000),
		('forum', 'hot_views', 'int', '', '1000', 'VIEW', 1213548598, 5000),
		
		('forum', 'codes', 'switch', '', '1', 'OPTIONS', 1213548598, 1000),
		('forum', 'smilies', 'switch', '', '1', 'OPTIONS', 1213548598, 2000),
		('forum', 'badwords', 'switch', '', '1', 'OPTIONS', 1213548598, 3000),
		('forum', 'edittime', 'int', '', '10', 'OPTIONS', 1213548598, 4000),
		('forum', 'timeout', 'int', '', '10', 'OPTIONS', 1213548598, 5000),
		('forum', 'spamprot', 'int', '', '0', 'OPTIONS', 1213548598, 6000),
		('forum', 'captcha', 'switch', '', '1', 'OPTIONS', 1213548598, 7000),
		('forum', 'autosubscribe', 'switch', '', '0', 'OPTIONS', 1213548598, 8000),
		('forum', 'ratings', 'switch', '', '1', 'OPTIONS', 1213548598, 9000);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
	
	//Forum-DIR
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$mm=new mediamanager;
	$mm->createdir('forum');
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_forums`;
		DROP TABLE `apx_forum_attachments`;
		DROP TABLE `apx_forum_filetypes`;
		DROP TABLE `apx_forum_index`;
		DROP TABLE `apx_forum_posts`;
		DROP TABLE `apx_forum_ranks`;
		DROP TABLE `apx_forum_search`;
		DROP TABLE `apx_forum_subscriptions`;
		DROP TABLE `apx_forum_threads`;
		
		ALTER TABLE `apx_user`
		  DROP `forum_lastactive`,
		  DROP `forum_lastonline`,
		  DROP `forum_posts`,
		  DROP `forum_autosubscribe`,
		  DROP `forum_tpp`,
		  DROP `forum_ppp`;
		
		DELETE FROM `apx_cron` WHERE funcname IN ('subscr_instant','subscr_daily','subscr_weekly','subscr_forum_daily','subscr_forum_weekly');
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				INSERT INTO `apx_config` VALUES ('forum', 'rate_digits', 'int', 'BLOCK', '0', '0', '0');
				INSERT INTO `apx_config` VALUES ('forum', 'badwords', 'switch', '', '1', '0', '450');
				ALTER TABLE `apx_forums` ADD `right_addattachment` TINYTEXT NOT NULL AFTER `right_delthread` , ADD `right_readattachment` TINYTEXT NOT NULL AFTER `right_addattachment` ;
				UPDATE `apx_forums` SET `right_addattachment` = `right_read`, `right_readattachment` = 'all';
				ALTER TABLE `apx_forum_attachments` ADD `mime` TINYTEXT NOT NULL AFTER `size` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 101: //zu 1.0.2
			$mysql="
				ALTER TABLE `apx_forum_ranks` ADD `color` VARCHAR( 6 ) NOT NULL AFTER `title` ,ADD `image` TINYTEXT NOT NULL AFTER `color` ;
				INSERT INTO `apx_config` VALUES ('forum', 'hot_posts', 'int', '', '', '30', '630');
				INSERT INTO `apx_config` VALUES ('forum', 'hot_views', 'int', '', '', '1000', '660');
				INSERT INTO `apx_config` VALUES ('forum', 'timeout', 'int', '', '10', '0', '750');
			";
				
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			//Thread- und Beitragszahlen berichtigen
			$data = $db->fetch("SELECT forumid FROM ".PRE."_forums");
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					$forumid = $res['forumid'];
					$threads = $posts = 0;
					
					//Threads auslesen
					$threaddata = $db->fetch("SELECT threadid FROM ".PRE."_forum_threads WHERE del=0 AND moved=0 AND forumid='".$forumid."'");
					if ( count($threaddata) ) {
						foreach ( $threaddata AS $tres ) {
							list($tposts) = $db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE del=0 AND threadid='".$tres['threadid']."'");
							$db->query("UPDATE ".PRE."_forum_threads SET posts='".$tposts."' WHERE threadid='".$tres['threadid']."'");
							$posts += $tposts;
						}
					}
					
					//Forum aktualisieren
					list($threads) = $db->first("SELECT count(threadid) FROM ".PRE."_forum_threads WHERE del=0 AND moved=0 AND forumid='".$forumid."'");
					$db->query("UPDATE ".PRE."_forums SET threads='".$threads."',posts='".$posts."' WHERE forumid='".$forumid."' LIMIT 1");
				}
			}
		
		
		case 102: //zu 1.0.3
			$mysql="
				INSERT INTO `apx_config` VALUES ('forum', 'spamprot', 'int', '', '1', '0', '775');
				INSERT INTO `apx_config` VALUES ('forum', 'autosubscribe', 'switch', '', '', '0', '850');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 103: //zu 1.0.4
			$mysql="
				ALTER TABLE `apx_user` ADD `forum_autosubscribe` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `forum_posts` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 104: //zu 1.1.0
			
			//Indizes entfernen
			clearIndices(PRE.'_forums');
			clearIndices(PRE.'_forum_attachments');
			clearIndices(PRE.'_forum_index');
			clearIndices(PRE.'_forum_ranks');
			clearIndices(PRE.'_forum_search');
			clearIndices(PRE.'_forum_subscriptions');
			clearIndices(PRE.'_forum_threads');
			
			//config Update
			updateConfig('forum', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('forum', 'icons', 'array', 'BLOCK', 'a:6:{i:0;a:2:{s:4:\"file\";s:25:\"/design/smilies/smile.gif\";s:3:\"ord\";i:1;}i:1;a:2:{s:4:\"file\";s:27:\"/design/smilies/shinner.gif\";s:3:\"ord\";i:5;}i:2;a:2:{s:4:\"file\";s:29:\"/design/smilies/angryfire.gif\";s:3:\"ord\";i:4;}i:3;a:2:{s:4:\"file\";s:25:\"/design/smilies/frown.gif\";s:3:\"ord\";i:3;}i:4;a:2:{s:4:\"file\";s:27:\"/design/smilies/biggrin.gif\";s:3:\"ord\";i:0;}i:5;a:2:{s:4:\"file\";s:25:\"/design/smilies/frage.gif\";s:3:\"ord\";i:2;}}', '', 0, 0),
				('forum', 'rate_possible', 'array_keys', 'BLOCK', 'a:5:{i:1;s:1:\"1\";i:2;s:1:\"2\";i:3;s:1:\"3\";i:4;s:1:\"4\";i:5;s:1:\"5\";}', '', 1146074744, 0),
				('forum', 'directory', 'string', 'BLOCK', 'forum', '', 0, 0),
				('forum', 'rate_digits', 'int', 'BLOCK', '0', '', 0, 0),
				
				('forum', 'forumtitle', 'string', '', 'apexx Forum', 'VIEW', 1213548598, 1000),
				('forum', 'tpp', 'int', '', '20', 'VIEW', 1213548598, 2000),
				('forum', 'ppp', 'int', '', '10', 'VIEW', 1213548598, 3000),
				('forum', 'hot_posts', 'int', '', '30', 'VIEW', 1213548598, 4000),
				('forum', 'hot_views', 'int', '', '1000', 'VIEW', 1213548598, 5000),
				
				('forum', 'codes', 'switch', '', '1', 'OPTIONS', 1213548598, 1000),
				('forum', 'smilies', 'switch', '', '1', 'OPTIONS', 1213548598, 2000),
				('forum', 'badwords', 'switch', '', '1', 'OPTIONS', 1213548598, 3000),
				('forum', 'edittime', 'int', '', '10', 'OPTIONS', 1213548598, 4000),
				('forum', 'timeout', 'int', '', '10', 'OPTIONS', 1213548598, 5000),
				('forum', 'spamprot', 'int', '', '0', 'OPTIONS', 1213548598, 6000),
				('forum', 'captcha', 'switch', '', '1', 'OPTIONS', 1213548598, 7000),
				('forum', 'autosubscribe', 'switch', '', '0', 'OPTIONS', 1213548598, 8000),
				('forum', 'ratings', 'switch', '', '1', 'OPTIONS', 1213548598, 9000);
			");
			
			$mysql="
				ALTER TABLE `apx_forum_search` CHANGE `hash` `hash` VARCHAR( 32 ) NOT NULL ;
				ALTER TABLE `apx_forum_search` CHANGE `time` `time` INT UNSIGNED NOT NULL DEFAULT '0';
				ALTER TABLE `apx_forums` CHANGE `children` `children` TEXT NOT NULL ;
				
				ALTER TABLE `apx_forums` ADD INDEX ( `parents` ) ;
				ALTER TABLE `apx_forum_attachments` ADD INDEX ( `postid` ) ;
				ALTER TABLE `apx_forum_index` ADD INDEX ( `postid` , `istitle` ) ;
				ALTER TABLE `apx_forum_index` ADD INDEX ( `threadid` , `istitle` ) ;
				ALTER TABLE `apx_forum_ranks` ADD INDEX ( `userid` , `groupid`, `minposts` ) ;
				ALTER TABLE `apx_forum_search` ADD INDEX ( `hash`, `time` ) ;
				ALTER TABLE `apx_forum_subscriptions` ADD INDEX ( `userid` ) ;
				ALTER TABLE `apx_forum_subscriptions` ADD INDEX ( `notification` ) ;
				ALTER TABLE `apx_forum_threads` ADD INDEX ( `forumid` ) ;
				ALTER TABLE `apx_forum_threads` ADD INDEX ( `forumid` , `del` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 110: //zu 1.1.1
			
			//Indizes entfernen
			clearIndices(PRE.'_forum_threads');
			
			$mysql="
				ALTER TABLE `apx_forum_threads` ADD INDEX ( `forumid` , `del` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 111: //zu 1.2.0
			
			//Beiträge gelöschter Themen als nicht-gelöscht markieren
			$data = $db->fetch("SELECT threadid FROM ".PRE."_forum_threads WHERE del!=0");
			$threadIds = get_ids($data, 'threadid');
			if ( $threadIds ) {
				$db->query("UPDATE ".PRE."_forum_posts SET del=0 WHERE threadid IN (".implode(',', $threadIds).")");
			}
			
			$mysql="
				ALTER TABLE `apx_forums` ADD `stylesheet` TINYTEXT NOT NULL AFTER `inherit` ;
				ALTER TABLE `apx_forum_threads` ADD `prefix` INT( 11 ) UNSIGNED NOT NULL AFTER `forumid` ;
				ALTER TABLE `apx_forum_attachments` ADD `thumbnail` TINYTEXT NOT NULL AFTER `file` ; 
				ALTER TABLE `apx_forums` ADD `lastpost` INT( 11 ) UNSIGNED NOT NULL AFTER `children` ;
				ALTER TABLE `apx_forums` ADD `lastthread` INT( 11 ) UNSIGNED NOT NULL AFTER `lastposttime` , ADD `lastthread_title` TINYTEXT NOT NULL AFTER `lastthread`, ADD `lastthread_icon` INT( 11 ) NOT NULL DEFAULT '-1' AFTER `lastthread_title` , ADD `lastthread_prefix` INT( 11 ) UNSIGNED NOT NULL AFTER `lastthread_icon` ;
				
				INSERT INTO `apx_cron` VALUES ('clean', 'forum', '86400', '1268002800', '');
				
				CREATE TABLE `apx_forum_activity` (
				  `userid` int(11) unsigned NOT NULL,
				  `ip` int(11) unsigned NOT NULL,
				  `type` enum('forum','thread') NOT NULL,
				  `id` int(11) unsigned NOT NULL,
				  `time` int(11) unsigned NOT NULL,
				  `invisible` tinyint(1) unsigned NOT NULL,
				  PRIMARY KEY  (`userid`,`ip`,`type`,`id`)
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_forum_anndisplay` (
				  `id` int(11) unsigned NOT NULL,
				  `forumid` int(11) unsigned NOT NULL,
				  PRIMARY KEY  (`id`,`forumid`)
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_forum_announcements` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `userid` int(11) unsigned NOT NULL,
				  `title` varchar(255) NOT NULL default '',
				  `text` text NOT NULL,
				  `addtime` int(11) unsigned NOT NULL,
				  `starttime` int(11) unsigned NOT NULL,
				  `endtime` int(11) unsigned NOT NULL,
				  `views` int(11) unsigned NOT NULL default '0',
				  PRIMARY KEY  (`id`),
				  KEY `starttime` (`starttime`,`endtime`)
				) ENGINE=MyISAM ;
				
				CREATE TABLE `apx_forum_prefixes` (
				  `prefixid` int(11) unsigned NOT NULL auto_increment,
				  `forumid` int(11) unsigned NOT NULL,
				  `title` tinytext NOT NULL,
				  `code` tinytext NOT NULL,
				  PRIMARY KEY  (`prefixid`)
				) ENGINE=MyISAM ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			//Anhänge aktualisieren
			$attachments='';
			$data=$db->fetch("
				SELECT id, file FROM
				".PRE."_forum_attachments
			");
			if ( count($data) ) {
				require(BASEDIR.'lib/class.mediamanager.php');
				require(BASEDIR.'lib/class.image.php');
				$mm=new mediamanager;
				$img=new image;
				foreach ( $data AS $res ) {
					$ext=strtolower($mm->getext($res['file']));
					if ( in_array($ext, array('gif', 'jpg', 'jpe', 'jpeg', 'png')) ) {
						$fileid = substr($res['file'], 0, -1*(strlen($ext)-1));
						$thumbnailPath = $fileid.'_thumb.'.$ext;
						list($picture,$picturetype)=$img->getimage($res['file']);
						
						//////// THUMBNAIL
						$thumbnail=$img->resize($picture, 120, 90, true);
						$img->saveimage($thumbnail,$picturetype,$thumbnailPath);
						
						//Cleanup
						imagedestroy($picture);
						imagedestroy($thumbnail);
						unset($picture,$thumbnail);
						
						//Update SQL
						$db->query("UPDATE ".PRE."_forum_attachments SET thumbnail='".addslashes($thumbnailPath)."' WHERE id='".$res['id']."' LIMIT 1");
					}
				}
			}
			
			//Thread- und Beitragszahlen berichtigen
			@set_time_limit(600);
			$data = $db->fetch("
				SELECT forumid
				FROM ".PRE."_forums
			");
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					$forumid = $res['forumid'];
					$forumThreads = 0;
					$forumPosts = 0;
					$forumLastpost = array();
					$forumLastthread = array();
					
					//Threads auslesen
					$threaddata = $db->fetch("
						SELECT threadid, prefix, title, icon, del
						FROM ".PRE."_forum_threads
						WHERE del=0 AND moved=0 AND forumid='".$forumid."'
					");
					if ( count($threaddata) ) {
						foreach ( $threaddata AS $tres ) {
							$threadid = $tres['threadid'];
							list($threadPosts) = $db->first("
								SELECT count(postid)
								FROM ".PRE."_forum_posts
								WHERE del=0 AND threadid='".$threadid."'
							");
							$threadLastpost = $db->first("
								SELECT postid, userid, username, time
								FROM ".PRE."_forum_posts
								WHERE del=0 AND threadid='".$threadid."'
								ORDER BY time DESC
								LIMIT 1
							");
							$db->query("
								UPDATE ".PRE."_forum_threads
								SET
									posts='".$threadPosts."',
									lastpost='".$threadLastpost['postid']."',
									lastposter='".addslashes($threadLastpost['username'])."',
									lastposter_userid='".$threadLastpost['userid']."',
									lastposttime='".$threadLastpost['time']."'
								WHERE threadid='".$threadid."'
							");
							
							//Themen/Beiträge im Forum
							if ( !$tres['del'] ) {
								++$forumThreads;
							}
							$forumPosts += $threadPosts;
							
							//Lastpost im Forum
							if ( !$forumLastpost || $forumLastpost['time']<$threadLastpost['time'] ) {
								$forumLastthread = $tres;
								$forumLastpost = $threadLastpost;
							}
						}
					}
					
					//Forum aktualisieren
					$db->query("
						UPDATE ".PRE."_forums
						SET
							threads='".$forumThreads."',
							posts='".$forumPosts."',
							lastpost='".$forumLastpost['postid']."',
							lastposter='".addslashes($forumLastpost['username'])."',
							lastposter_userid='".$forumLastpost['userid']."',
							lastposttime='".$forumLastpost['time']."',
							lastthread='".$forumLastthread['threadid']."',
							lastthread_title='".addslashes($forumLastthread['title'])."',
							lastthread_icon='".addslashes($forumLastthread['icon'])."',
							lastthread_prefix='".addslashes($forumLastthread['prefix'])."'
						WHERE forumid='".$forumid."'
						LIMIT 1");
				}
			}
		
		
		case 120: //zu 1.2.1
			
			$mysql="
				ALTER TABLE `apx_forums` ADD `meta_description` TEXT NOT NULL AFTER `description` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
	}
}

?>