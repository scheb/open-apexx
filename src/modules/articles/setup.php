<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Installieren
if ( SETUPMODE=='install' ) {
	$mysql="
		CREATE TABLE `apx_articles` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `type` enum('normal','preview','review') NOT NULL default 'normal',
		  `secid` tinytext NOT NULL,
		  `prodid` int(11) unsigned NOT NULL default '0',
		  `catid` int(11) unsigned NOT NULL default '0',
		  `userid` int(11) unsigned NOT NULL default '0',
		  `artpic` tinytext NOT NULL,
		  `title` tinytext NOT NULL,
		  `subtitle` tinytext NOT NULL,
		  `teaser` text NOT NULL,
		  `meta_description` text NOT NULL,
		  `galid` int(11) unsigned NOT NULL default '0',
		  `links` text NOT NULL,
		  `pictures` text NOT NULL,
		  `pictures_nextid` int(11) unsigned NOT NULL default '1',
		  `addtime` int(11) unsigned NOT NULL default '0',
		  `starttime` int(11) unsigned NOT NULL default '0',
		  `endtime` int(11) unsigned NOT NULL default '0',
		  `top` tinyint(1) unsigned NOT NULL default '0',
		  `sticky` int(11) unsigned NOT NULL default '0',
		  `searchable` tinyint(1) unsigned NOT NULL default '0',
		  `allowcoms` tinyint(1) unsigned NOT NULL default '1',
		  `allowrating` tinyint(1) unsigned NOT NULL default '1',
		  `restricted` tinyint(1) unsigned NOT NULL,
		  `hits` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `userid` (`userid`),
		  KEY `catid` (`catid`),
		  KEY `starttime` (`starttime`,`endtime`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_articles_cat` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` tinytext NOT NULL,
		  `icon` tinytext NOT NULL,
		  `open` tinyint(1) unsigned NOT NULL default '1',
		  `forgroup` tinytext NOT NULL,
		  `parents` varchar(255) NOT NULL,
		  `children` text NOT NULL,
		  `ord` tinyint(3) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `parents` (`parents`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_articles_pages` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `artid` int(11) unsigned NOT NULL default '0',
		  `title` tinytext NOT NULL,
		  `text` mediumtext NOT NULL,
		  `addtime` int(11) unsigned NOT NULL default '0',
		  `ord` tinyint(3) unsigned NOT NULL default '1',
		  PRIMARY KEY  (`id`),
		  KEY `artid` (`artid`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_articles_previews` (
		  `artid` int(11) unsigned NOT NULL default '0',
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
		  `impression` tinytext NOT NULL,
		  `conclusion` text NOT NULL,
		  PRIMARY KEY  (`artid`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_articles_reviews` (
		  `artid` int(11) unsigned NOT NULL default '0',
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
		  `rate1` tinytext NOT NULL,
		  `rate2` tinytext NOT NULL,
		  `rate3` tinytext NOT NULL,
		  `rate4` tinytext NOT NULL,
		  `rate5` tinytext NOT NULL,
		  `rate6` tinytext NOT NULL,
		  `rate7` tinytext NOT NULL,
		  `rate8` tinytext NOT NULL,
		  `rate9` tinytext NOT NULL,
		  `rate10` tinytext NOT NULL,
		  `final_rate` tinytext NOT NULL,
		  `positive` text NOT NULL,
		  `negative` text NOT NULL,
		  `conclusion` text NOT NULL,
		  `award` tinytext NOT NULL,
		  PRIMARY KEY  (`artid`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_articles_tags` (
		  `id` int(11) unsigned NOT NULL,
		  `tagid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`,`tagid`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('articles', 'epp', 'int', '', '10', 'VIEW', 1165596044, 1000),
		('articles', 'searchepp', 'int', '', '10', 'VIEW', 1165596044, 2000),
		('articles', 'archiveepp', 'int', '', '10', 'VIEW', 1165596044, 3000),
		('articles', 'archiveall', 'switch', '', '0', 'VIEW', 1165596044, 4000),
		('articles', 'archivesort', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '1', 'VIEW', 1165596044, 5000),
		('articles', 'archiveentrysort', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '1', 'VIEW', 1165596044, 6000),
		
		('articles', 'searchable', 'switch', '', '1', 'OPTIONS', 1165596044, 1000),
		('articles', 'subcats', 'switch', '', '1', 'OPTIONS', 1165596044, 2000),
		('articles', 'teaser', 'switch', '', '1', 'OPTIONS', 1165596044, 3000),
		('articles', 'normalonly', 'switch', '', '1', 'OPTIONS', 1165596044, 4000),
		('articles', 'coms', 'switch', '', '1', 'OPTIONS', 1165596044, 5000),
		('articles', 'ratings', 'switch', '', '1', 'OPTIONS', 1165596044, 6000),
		('articles', 'archcoms', 'switch', '', '1', 'OPTIONS', 1165596044, 7000),
		('articles', 'archratings', 'switch', '', '1', 'OPTIONS', 1165596044, 8000),
		('articles', 'previews_conclusionpage', 'switch', '', '1', 'OPTIONS', 1165596044, 9000),
		('articles', 'reviews_conclusionpage', 'switch', '', '1', 'OPTIONS', 1165596044, 10000),
		
		('articles', 'custom_preview', 'array', '', 'a:0:{}', 'CUSTOM', 1165596044, 1000),
		('articles', 'custom_review', 'array', '', 'a:0:{}', 'CUSTOM', 1165596044, 3000),
		('articles', 'ratefields', 'array', '', 'a:0:{}', 'CUSTOM', 1165596044, 4000),
		('articles', 'awards', 'array', '', 'a:0:{}', 'CUSTOM', 1165596044, 5000),
		
		('articles', 'artpic_width', 'int', '', '120', 'IMAGES', 1165596044, 1000),
		('articles', 'artpic_height', 'int', '', '120', 'IMAGES', 1165596044, 2000),
		('articles', 'artpic_popup', 'switch', '', '1', 'IMAGES', 1165596044, 3000),
		('articles', 'artpic_popup_width', 'int', '', '640', 'IMAGES', 1165596044, 4000),
		('articles', 'artpic_popup_height', 'int', '', '480', 'IMAGES', 1165596044, 5000),
		('articles', 'picwidth', 'int', '', '640', 'IMAGES', 1165596044, 6000),
		('articles', 'picheight', 'int', '', '480', 'IMAGES', 1165596044, 7000),
		('articles', 'watermark', 'string', '', '', 'IMAGES', 1165596044, 8000),
		('articles', 'watermark_transp', 'int', '', '50', 'IMAGES', 1165596044, 9000),
		('articles', 'watermark_position', 'select', 'a:9:{i:1;s:18:\"{POSTOP} {POSLEFT}\";i:2;s:20:\"{POSTOP} {POSCENTER}\";i:3;s:19:\"{POSTOP} {POSRIGHT}\";i:4;s:21:\"{POSMIDDLE} {POSLEFT}\";i:5;s:23:\"{POSMIDDLE} {POSCENTER}\";i:6;s:22:\"{POSMIDDLE} {POSRIGHT}\";i:7;s:21:\"{POSBOTTOM} {POSLEFT}\";i:8;s:23:\"{POSBOTTOM} {POSCENTER}\";i:9;s:22:\"{POSBOTTOM} {POSRIGHT}\";}', '9', 'IMAGES', 1165596044, 10000),
		('articles', 'thumbwidth', 'int', '', '120', 'IMAGES', 1165596044, 11000),
		('articles', 'thumbheight', 'int', '', '90', 'IMAGES', 1165596044, 12000),
		('articles', 'popup_addwidth', 'int', '', '60', 'IMAGES', 1165596044, 13000),
		('articles', 'popup_addheight', 'int', '', '150', 'IMAGES', 1165596044, 14000),
		('articles', 'popup_resizeable', 'switch', '', '1', 'IMAGES', 1165596044, 15000),
		('articles', 'artpic_quality', 'switch', '', '1', 'IMAGES', 1165596044, 16000);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
	
	//Order Artikelbilder + Bilder
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$mm=new mediamanager;
	$mm->createdir('articles');
	$mm->createdir('gallery','articles');
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_articles`;
		DROP TABLE `apx_articles_cat`;
		DROP TABLE `apx_articles_pages`;
		DROP TABLE `apx_articles_previews`;
		DROP TABLE `apx_articles_reviews`;
		DROP TABLE `apx_articles_tags`;
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				ALTER TABLE `apx_articles` ADD `galid` INT( 11 ) UNSIGNED NOT NULL AFTER `teaser` ;
				INSERT INTO `apx_config` VALUES ('articles', 'subcats', 'switch', '', '1', '0', '150');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 101: //zu 1.0.2
			$mysql="
				INSERT INTO `apx_config` ( `module` , `varname` , `type` , `addnl` , `value` , `lastchange` , `ord` ) VALUES ('articles', 'searchable', 'switch', '', '1', '0', '50');
				ALTER TABLE `apx_articles` ADD `searchable` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `sticky` ;
				ALTER TABLE `apx_articles` ADD `keywords` TINYTEXT NOT NULL AFTER `teaser` ;
				UPDATE `apx_articles` SET searchable='1';
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 102: //zu 1.1.0
			$mysql="
				CREATE TABLE `apx_articles_previews` (
				  `artid` int(11) unsigned NOT NULL default '0',
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
				  `impression` tinytext NOT NULL,
				  `conclusion` text NOT NULL
				) ENGINE=MyISAM;
				
				CREATE TABLE `apx_articles_reviews` (
				  `artid` int(11) unsigned NOT NULL default '0',
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
				  `rate1` tinytext NOT NULL,
				  `rate2` tinytext NOT NULL,
				  `rate3` tinytext NOT NULL,
				  `rate4` tinytext NOT NULL,
				  `rate5` tinytext NOT NULL,
				  `rate6` tinytext NOT NULL,
				  `rate7` tinytext NOT NULL,
				  `rate8` tinytext NOT NULL,
				  `rate9` tinytext NOT NULL,
				  `rate10` tinytext NOT NULL,
				  `final_rate` tinytext NOT NULL,
				  `positive` text NOT NULL,
				  `negative` text NOT NULL,
				  `conclusion` text NOT NULL,
				  `award` tinytext NOT NULL
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_articles` ADD `links` TEXT NOT NULL AFTER `galid` ;
				ALTER TABLE `apx_articles` ADD `type` ENUM( 'normal', 'preview', 'review' ) NOT NULL AFTER `id` ;
				ALTER TABLE `apx_articles` ADD `pictures` TEXT NOT NULL AFTER `links` ;
				ALTER TABLE `apx_articles` ADD `pictures_nextid` INT( 11 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `pictures` ;
				
				UPDATE `apx_config` SET ord = ord +700 WHERE module = 'articles' AND ord >800;
				UPDATE `apx_config` SET ord = ord +400 WHERE module = 'articles' AND ord >1600;
				INSERT INTO `apx_config` VALUES ('articles', 'picwidth', 'int', '', '640', 1141134229, 900);
				INSERT INTO `apx_config` VALUES ('articles', 'picheight', 'int', '', '480', 1141134229, 1000);
				INSERT INTO `apx_config` VALUES ('articles', 'watermark', 'string', '', '', 1141134229, 1100);
				INSERT INTO `apx_config` VALUES ('articles', 'watermark_transp', 'int', '', '50', 1141134229, 1200);
				INSERT INTO `apx_config` VALUES ('articles', 'watermark_position', 'select', 'a:9:{i:1;s:18:\"{POSTOP} {POSLEFT}\";i:2;s:20:\"{POSTOP} {POSCENTER}\";i:3;s:19:\"{POSTOP} {POSRIGHT}\";i:4;s:21:\"{POSMIDDLE} {POSLEFT}\";i:5;s:23:\"{POSMIDDLE} {POSCENTER}\";i:6;s:22:\"{POSMIDDLE} {POSRIGHT}\";i:7;s:21:\"{POSBOTTOM} {POSLEFT}\";i:8;s:23:\"{POSBOTTOM} {POSCENTER}\";i:9;s:22:\"{POSBOTTOM} {POSRIGHT}\";}', '9', 1141134229, 1300);
				INSERT INTO `apx_config` VALUES ('articles', 'thumbwidth', 'int', '', '120', 1141134229, 1400);
				INSERT INTO `apx_config` VALUES ('articles', 'thumbheight', 'int', '', '90', 1141134229, 1500);
				INSERT INTO `apx_config` VALUES ('articles', 'popup_addwidth', 'int', '', '60', 1143727472, 1520);
				INSERT INTO `apx_config` VALUES ('articles', 'popup_addheight', 'int', '', '150', 1143727472, 1540);
				INSERT INTO `apx_config` VALUES ('articles', 'popup_resizeable', 'switch', '', '1', 1143727472, 1560);
				INSERT INTO `apx_config` VALUES ('articles', 'custom_preview', 'array', '', 'a:0:{}', '0', '1700');
				INSERT INTO `apx_config` VALUES ('articles', 'custom_review', 'array', '', 'a:0:{}', '0', '1800');
				INSERT INTO `apx_config` VALUES ('articles', 'ratefields', 'array', '', 'a:0:{}', '0', '1900');
				INSERT INTO `apx_config` VALUES ('articles', 'awards', 'array', '', 'a:0:{}', '0', '2000');
				INSERT INTO `apx_config` VALUES ('articles', 'reviews_conclusionpage', 'switch', '', '1', '0', '2025');
				INSERT INTO `apx_config` VALUES ('articles', 'previews_conclusionpage', 'switch', '', '1', '0', '2025');
				INSERT INTO `apx_config` VALUES ('articles', 'normalonly', 'switch', '', '1', '0', '2050');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			require_once(BASEDIR.'lib/class.mediamanager.php');
			$mm=new mediamanager;
			$mm->createdir('gallery','articles');
		
		
		case 110: //zu 1.1.1
			$mysql="
				INSERT INTO `apx_config` VALUES ('articles', 'searchepp', 'int', '', '10', '0', '2800');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 111: //zu 1.1.2
			$mysql="
				INSERT INTO `apx_config` VALUES ('articles', 'archiveentrysort', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '1', 1144936997, 2750);
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 112: //zu 1.1.3
			$mysql="
				ALTER TABLE `apx_articles` ADD `prodid` INT( 11 ) UNSIGNED NOT NULL AFTER `secid` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 113: //zu 1.1.4
			$mysql="
				ALTER TABLE `apx_articles` CHANGE `sticky` `sticky` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' ;
				UPDATE `apx_articles` SET sticky='3000000000' WHERE sticky=1;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 114: //zu 1.2.0
			
			//Indizes entfernen
			clearIndices(PRE.'_articles');
			clearIndices(PRE.'_articles_cat');
			clearIndices(PRE.'_articles_pages');
			
			//Tabellenformat ändern
			convertRecursiveTable(PRE.'_articles_cat');
			
			//config Update
			updateConfig('articles', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('articles', 'epp', 'int', '', '10', 'VIEW', 1165596044, 1000),
				('articles', 'searchepp', 'int', '', '10', 'VIEW', 1165596044, 2000),
				('articles', 'archiveepp', 'int', '', '10', 'VIEW', 1165596044, 3000),
				('articles', 'archiveall', 'switch', '', '0', 'VIEW', 1165596044, 4000),
				('articles', 'archivesort', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '1', 'VIEW', 1165596044, 5000),
				('articles', 'archiveentrysort', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '1', 'VIEW', 1165596044, 6000),
				
				('articles', 'searchable', 'switch', '', '1', 'OPTIONS', 1165596044, 1000),
				('articles', 'subcats', 'switch', '', '1', 'OPTIONS', 1165596044, 2000),
				('articles', 'teaser', 'switch', '', '1', 'OPTIONS', 1165596044, 3000),
				('articles', 'normalonly', 'switch', '', '1', 'OPTIONS', 1165596044, 4000),
				('articles', 'coms', 'switch', '', '1', 'OPTIONS', 1165596044, 5000),
				('articles', 'ratings', 'switch', '', '1', 'OPTIONS', 1165596044, 6000),
				('articles', 'archcoms', 'switch', '', '1', 'OPTIONS', 1165596044, 7000),
				('articles', 'archratings', 'switch', '', '1', 'OPTIONS', 1165596044, 8000),
				('articles', 'previews_conclusionpage', 'switch', '', '1', 'OPTIONS', 1165596044, 9000),
				('articles', 'reviews_conclusionpage', 'switch', '', '1', 'OPTIONS', 1165596044, 10000),
				
				('articles', 'custom_preview', 'array', '', 'a:0:{}', 'CUSTOM', 1165596044, 1000),
				('articles', 'custom_review', 'array', '', 'a:0:{}', 'CUSTOM', 1165596044, 3000),
				('articles', 'ratefields', 'array', '', 'a:0:{}', 'CUSTOM', 1165596044, 4000),
				('articles', 'awards', 'array', '', 'a:0:{}', 'CUSTOM', 1165596044, 5000),
				
				('articles', 'artpic_width', 'int', '', '120', 'IMAGES', 1165596044, 1000),
				('articles', 'artpic_height', 'int', '', '120', 'IMAGES', 1165596044, 2000),
				('articles', 'artpic_popup', 'switch', '', '1', 'IMAGES', 1165596044, 3000),
				('articles', 'artpic_popup_width', 'int', '', '640', 'IMAGES', 1165596044, 4000),
				('articles', 'artpic_popup_height', 'int', '', '480', 'IMAGES', 1165596044, 5000),
				('articles', 'picwidth', 'int', '', '640', 'IMAGES', 1165596044, 6000),
				('articles', 'picheight', 'int', '', '480', 'IMAGES', 1165596044, 7000),
				('articles', 'watermark', 'string', '', '', 'IMAGES', 1165596044, 8000),
				('articles', 'watermark_transp', 'int', '', '50', 'IMAGES', 1165596044, 9000),
				('articles', 'watermark_position', 'select', 'a:9:{i:1;s:18:\"{POSTOP} {POSLEFT}\";i:2;s:20:\"{POSTOP} {POSCENTER}\";i:3;s:19:\"{POSTOP} {POSRIGHT}\";i:4;s:21:\"{POSMIDDLE} {POSLEFT}\";i:5;s:23:\"{POSMIDDLE} {POSCENTER}\";i:6;s:22:\"{POSMIDDLE} {POSRIGHT}\";i:7;s:21:\"{POSBOTTOM} {POSLEFT}\";i:8;s:23:\"{POSBOTTOM} {POSCENTER}\";i:9;s:22:\"{POSBOTTOM} {POSRIGHT}\";}', '9', 'IMAGES', 1165596044, 10000),
				('articles', 'thumbwidth', 'int', '', '120', 'IMAGES', 1165596044, 11000),
				('articles', 'thumbheight', 'int', '', '90', 'IMAGES', 1165596044, 12000),
				('articles', 'popup_addwidth', 'int', '', '60', 'IMAGES', 1165596044, 13000),
				('articles', 'popup_addheight', 'int', '', '150', 'IMAGES', 1165596044, 14000),
				('articles', 'popup_resizeable', 'switch', '', '1', 'IMAGES', 1165596044, 15000),
				('articles', 'artpic_quality', 'switch', '', '1', 'IMAGES', 1165596044, 16000);
			");
			
			$mysql="
				CREATE TABLE `apx_articles_tags` (
					`id` INT( 11 ) UNSIGNED NOT NULL ,
					`tagid` INT( 11 ) UNSIGNED NOT NULL ,
					PRIMARY KEY ( `id` , `tagid` )
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_articles` ADD `restricted` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `allowrating` ;
				
				ALTER TABLE `apx_articles` ADD INDEX (`userid`) ;
				ALTER TABLE `apx_articles` ADD INDEX (`catid`) ;
				ALTER TABLE `apx_articles` ADD INDEX (`starttime`,`endtime`) ;
				ALTER TABLE `apx_articles_cat` ADD INDEX ( `parents` ) ;
				ALTER TABLE `apx_articles_pages` ADD INDEX ( `artid` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			//Tags erzeugen
			transformKeywords(PRE.'_articles', PRE.'_articles_tags');
		
		
		case 120: //zu 1.2.1
			$mysql="
				ALTER TABLE `apx_articles` ADD `meta_description` TEXT NOT NULL AFTER `teaser` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
	}
}

?>