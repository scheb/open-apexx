<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $mysql = "
		CREATE TABLE `apx_downloads` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `secid` tinytext NOT NULL,
		  `prodid` int(11) unsigned NOT NULL default '0',
		  `catid` int(11) unsigned NOT NULL default '0',
		  `userid` int(11) unsigned NOT NULL default '0',
		  `send_username` tinytext NOT NULL,
		  `send_email` tinytext NOT NULL,
		  `send_ip` tinytext NOT NULL,
		  `file` text NOT NULL,
		  `tempfile` tinytext NOT NULL,
		  `local` tinyint(1) unsigned NOT NULL default '1',
		  `filesize` bigint(11) unsigned NOT NULL default '0',
		  `format` TINYTEXT NOT NULL,
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `teaserpic` TINYTEXT NOT NULL,
		  `meta_description` text NOT NULL,
		  `author` tinytext NOT NULL,
		  `author_link` tinytext NOT NULL,
		  `mirrors` text NOT NULL,
		  `pictures` text NOT NULL,
		  `pictures_nextid` smallint(5) unsigned NOT NULL default '1',
		  `galid` int(11) unsigned NOT NULL default '0',
		  `addtime` int(11) unsigned NOT NULL default '0',
		  `starttime` int(11) unsigned NOT NULL default '0',
		  `endtime` int(11) unsigned NOT NULL default '0',
		  `broken` int(11) unsigned NOT NULL default '0',
		  `password` tinytext NOT NULL,
		  `limit` int(10) unsigned NOT NULL default '0',
		  `top` tinyint(1) unsigned NOT NULL default '0',
		  `regonly` tinyint(1) unsigned NOT NULL default '0',
		  `searchable` tinyint(1) unsigned NOT NULL default '0',
		  `allowcoms` tinyint(1) unsigned NOT NULL default '1',
		  `allowrating` tinyint(1) unsigned NOT NULL default '1',
		  `restricted` tinyint(1) unsigned NOT NULL,
		  `hits` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `catid` (`catid`),
		  KEY `catid_2` (`catid`),
		  KEY `userid` (`userid`),
		  KEY `starttime` (`starttime`,`endtime`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_downloads_cat` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `icon` tinytext NOT NULL,
		  `open` tinyint(1) unsigned NOT NULL default '1',
		  `forgroup` tinytext NOT NULL,
		  `parents` varchar(255) NOT NULL,
		  `children` text NOT NULL,
		  `ord` tinyint(3) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `parents` (`parents`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_downloads_stats` (
		  `daystamp` int(8) unsigned NOT NULL default '0',
		  `time` int(11) unsigned NOT NULL default '0',
		  `dlid` int(11) unsigned NOT NULL default '0',
		  `bytes` bigint(20) unsigned NOT NULL default '0',
		  `hits` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`daystamp`,`dlid`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_downloads_tags` (
		  `id` int(11) unsigned NOT NULL,
		  `tagid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`,`tagid`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('downloads', 'epp', 'int', '', '20', 'VIEW', 1249981881, 1000),
		('downloads', 'searchepp', 'string', '', '20', 'VIEW', 1249981881, 2000),
		('downloads', 'catonly', 'switch', '', '1', 'VIEW', 1249981881, 3000),
		('downloads', 'sortby', 'select', 'a:2:{i:1;s:7:\"{TITLE}\";i:2;s:6:\"{DATE}\";}', '1', 'VIEW', 1249981881, 4000),
		('downloads', 'new', 'int', '', '3', 'VIEW', 1249981881, 5000),
		
		('downloads', 'searchable', 'switch', '', '1', 'OPTIONS', 1249981881, 1000),
		('downloads', 'regonly', 'switch', '', '0', 'OPTIONS', 1249981881, 2000),
		('downloads', 'maxtraffic', 'float', '', '0', 'OPTIONS', 1249981881, 3000),
		('downloads', 'exttraffic', 'switch', '', '0', 'OPTIONS', 1249981881, 4000),
		('downloads', 'coms', 'switch', '', '1', 'OPTIONS', 1249981881, 5000),
		('downloads', 'ratings', 'switch', '', '1', 'OPTIONS', 1249981881, 6000),
		('downloads', 'captcha', 'switch', '', '1', 'OPTIONS', 1249981881, 7000),
		('downloads', 'spamprot', 'int', '', '0', 'OPTIONS', 1249981881, 8000),
		('downloads', 'mailonnew', 'string', '', '', 'OPTIONS', 1249981881, 9000),
		('downloads', 'mailonbroken', 'string', '', '', 'OPTIONS', 1249981881, 10000),
		
		('downloads', 'addpics', 'int', '', '5', 'IMAGES', 1249981881, 1000),
		('downloads', 'picwidth', 'int', '', '640', 'IMAGES', 1249981881, 2000),
		('downloads', 'picheight', 'int', '', '480', 'IMAGES', 1249981881, 3000),
		('downloads', 'watermark', 'string', '', '', 'IMAGES', 1249981881, 4000),
		('downloads', 'watermark_transp', 'int', '', '50', 'IMAGES', 1249981881, 5000),
		('downloads', 'watermark_position', 'select', 'a:9:{i:1;s:18:\"{POSTOP} {POSLEFT}\";i:2;s:20:\"{POSTOP} {POSCENTER}\";i:3;s:19:\"{POSTOP} {POSRIGHT}\";i:4;s:21:\"{POSMIDDLE} {POSLEFT}\";i:5;s:23:\"{POSMIDDLE} {POSCENTER}\";i:6;s:22:\"{POSMIDDLE} {POSRIGHT}\";i:7;s:21:\"{POSBOTTOM} {POSLEFT}\";i:8;s:23:\"{POSBOTTOM} {POSCENTER}\";i:9;s:22:\"{POSBOTTOM} {POSRIGHT}\";}', '9', 'IMAGES', 1249981881, 6000),
		('downloads', 'thumbwidth', 'int', '', '120', 'IMAGES', 1249981881, 7000),
		('downloads', 'thumbheight', 'int', '', '90', 'IMAGES', 1249981881, 8000),
		('downloads', 'quality_resize', 'switch', '', '1', 'IMAGES', 1249981881, 9000),
		
		('downloads', 'teaserpic_width', 'int', '', '120', 'TEASERPIC', 1261490672, 1000),
		('downloads', 'teaserpic_height', 'int', '', '120', 'TEASERPIC', 1261490672, 2000),
		('downloads', 'teaserpic_popup', 'switch', '', '1', 'TEASERPIC', 1261490672, 3000),
		('downloads', 'teaserpic_popup_width', 'int', '', '640', 'TEASERPIC', 1261490672, 4000),
		('downloads', 'teaserpic_popup_height', 'int', '', '480', 'TEASERPIC', 1261490672, 5000),
		('downloads', 'teaserpic_quality', 'switch', '', '1', 'TEASERPIC', 1261490672, 6000);
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }

    require_once BASEDIR.'lib/class.mediamanager.php';
    $mm = new mediamanager();
    $mm->createdir('downloads');
    $mm->createdir('pics', 'downloads');
    $mm->createdir('uploads', 'downloads');
}

//Deinstallieren
elseif (SETUPMODE == 'uninstall') {
    $mysql = '
		DROP TABLE `apx_downloads`;
		DROP TABLE `apx_downloads_cat`;
		DROP TABLE `apx_downloads_tags`;
		DROP TABLE `apx_downloads_stats`;
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
				ALTER TABLE `apx_downloads` ADD `endtime` INT( 11 ) UNSIGNED NOT NULL AFTER `starttime`;
				ALTER TABLE `apx_downloads` ADD `galid` INT( 11 ) UNSIGNED NOT NULL AFTER `pictures_nextid` ;
				UPDATE `apx_downloads` SET endtime='3000000000' WHERE starttime!='0';
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 101: //zu 1.0.2
            $mysql = "
				INSERT INTO `apx_config` ( `module` , `varname` , `type` , `addnl` , `value` , `lastchange` , `ord` ) VALUES ('downloads', 'searchable', 'switch', '', '1', '0', '50');
				INSERT INTO `apx_config` ( `module` , `varname` , `type` , `addnl` , `value` , `lastchange` , `ord` ) VALUES ('downloads', 'sortby', 'select', 'a:2:{i:1;s:7:\"{TITLE}\";i:2;s:6:\"{DATE}\";}', '1', '0', '200');
				ALTER TABLE `apx_downloads` ADD `searchable` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `regonly` ;
				ALTER TABLE `apx_downloads` ADD `keywords` TINYTEXT NOT NULL AFTER `text` ;
				UPDATE `apx_downloads` SET searchable='1';
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 102: //zu 1.0.3
            $mysql = "
				ALTER TABLE `apx_downloads` CHANGE `filesize` `filesize` BIGINT( 11 ) UNSIGNED NOT NULL DEFAULT '0';
				INSERT INTO `apx_config` VALUES ( 'downloads', 'catonly', 'switch', '', '1', '0', '250' );
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 103: //zu 1.0.4
            $mysql = "
				INSERT INTO `apx_config` VALUES ('downloads', 'captcha', 'switch', '', '0', 0, 1650);
				INSERT INTO `apx_config` VALUES ('downloads', 'mailonnew', 'string', '', '', 0, 1800);
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 104: //zu 1.0.5
            $mysql = "
				INSERT INTO `apx_config` VALUES ('downloads', 'searchepp', 'string', '', '20', '0', '1900');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 105: //zu 1.0.6
            $mysql = '
				ALTER TABLE `apx_downloads` ADD `prodid` INT( 11 ) UNSIGNED NOT NULL AFTER `secid` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 106: //zu 1.0.7
            $mysql = "
				INSERT INTO `apx_config` VALUES ('downloads', 'exttraffic', 'switch', '', '', '0', '550');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 107: //zu 1.0.8
            $mysql = "
				UPDATE `apx_config` SET type='switch' WHERE module='downloads' AND varname='exttraffic';
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 108: //zu 1.1.0

            //Indizes entfernen
            clearIndices(PRE.'_downloads');
            clearIndices(PRE.'_downloads_cat');

            //Tabellenformat ändern
            convertRecursiveTable(PRE.'_downloads_cat');

            //config Update
            updateConfig('downloads', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('downloads', 'epp', 'int', '', '20', 'VIEW', 1249981881, 1000),
				('downloads', 'searchepp', 'string', '', '20', 'VIEW', 1249981881, 2000),
				('downloads', 'catonly', 'switch', '', '1', 'VIEW', 1249981881, 3000),
				('downloads', 'sortby', 'select', 'a:2:{i:1;s:7:\"{TITLE}\";i:2;s:6:\"{DATE}\";}', '1', 'VIEW', 1249981881, 4000),
				('downloads', 'new', 'int', '', '3', 'VIEW', 1249981881, 5000),
				
				('downloads', 'searchable', 'switch', '', '1', 'OPTIONS', 1249981881, 1000),
				('downloads', 'regonly', 'switch', '', '0', 'OPTIONS', 1249981881, 2000),
				('downloads', 'maxtraffic', 'float', '', '0', 'OPTIONS', 1249981881, 3000),
				('downloads', 'exttraffic', 'switch', '', '0', 'OPTIONS', 1249981881, 4000),
				('downloads', 'mirrorstats', 'switch', '', '1', 'OPTIONS', 1301669229, 4500),
				('downloads', 'coms', 'switch', '', '1', 'OPTIONS', 1249981881, 5000),
				('downloads', 'ratings', 'switch', '', '1', 'OPTIONS', 1249981881, 6000),
				('downloads', 'captcha', 'switch', '', '1', 'OPTIONS', 1249981881, 7000),
				('downloads', 'spamprot', 'int', '', '0', 'OPTIONS', 1249981881, 8000),
				('downloads', 'mailonnew', 'string', '', '', 'OPTIONS', 1249981881, 9000),
				('downloads', 'mailonbroken', 'string', '', '', 'OPTIONS', 1249981881, 10000),
				
				('downloads', 'addpics', 'int', '', '5', 'IMAGES', 1249981881, 1000),
				('downloads', 'picwidth', 'int', '', '640', 'IMAGES', 1249981881, 2000),
				('downloads', 'picheight', 'int', '', '480', 'IMAGES', 1249981881, 3000),
				('downloads', 'watermark', 'string', '', '', 'IMAGES', 1249981881, 4000),
				('downloads', 'watermark_transp', 'int', '', '50', 'IMAGES', 1249981881, 5000),
				('downloads', 'watermark_position', 'select', 'a:9:{i:1;s:18:\"{POSTOP} {POSLEFT}\";i:2;s:20:\"{POSTOP} {POSCENTER}\";i:3;s:19:\"{POSTOP} {POSRIGHT}\";i:4;s:21:\"{POSMIDDLE} {POSLEFT}\";i:5;s:23:\"{POSMIDDLE} {POSCENTER}\";i:6;s:22:\"{POSMIDDLE} {POSRIGHT}\";i:7;s:21:\"{POSBOTTOM} {POSLEFT}\";i:8;s:23:\"{POSBOTTOM} {POSCENTER}\";i:9;s:22:\"{POSBOTTOM} {POSRIGHT}\";}', '9', 'IMAGES', 1249981881, 6000),
				('downloads', 'thumbwidth', 'int', '', '120', 'IMAGES', 1249981881, 7000),
				('downloads', 'thumbheight', 'int', '', '90', 'IMAGES', 1249981881, 8000),
				('downloads', 'quality_resize', 'switch', '', '1', 'IMAGES', 1249981881, 9000);
				
				ALTER TABLE `apx_downloads` ADD INDEX ( `catid` ) ;
				ALTER TABLE `apx_downloads` ADD INDEX ( `userid` ) ;
				ALTER TABLE `apx_downloads` ADD INDEX ( `starttime` , `endtime` ) ;
				ALTER TABLE `apx_downloads_cat` ADD INDEX ( `parents` ) ;
			");

            $mysql = '
				CREATE TABLE `apx_downloads_tags` (
					`id` INT( 11 ) UNSIGNED NOT NULL ,
					`tagid` INT( 11 ) UNSIGNED NOT NULL ,
					PRIMARY KEY ( `id` , `tagid` )
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_downloads` ADD `restricted` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `allowrating` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            //Tags erzeugen
            transformKeywords(PRE.'_downloads', PRE.'_downloads_tags');

            // no break
        case 110: //zu 1.1.1
            $mysql = '
				ALTER TABLE `apx_downloads` ADD `meta_description` TEXT NOT NULL AFTER `text` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 111: //zu 1.1.2
            $mysql = "
				INSERT INTO `apx_config` VALUES ('downloads', 'mirrorstats', 'switch', '', '1', 'OPTIONS', 1301669229, 4500);
				ALTER TABLE `apx_downloads` ADD `format` TINYTEXT NOT NULL AFTER `filesize` ;
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 111: //zu 1.1.2
            $mysql = "
				ALTER TABLE `apx_downloads` ADD `teaserpic` TINYTEXT NOT NULL AFTER `text` ;
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('downloads', 'teaserpic_width', 'int', '', '120', 'TEASERPIC', 1261490672, 1000),
				('downloads', 'teaserpic_height', 'int', '', '120', 'TEASERPIC', 1261490672, 2000),
				('downloads', 'teaserpic_popup', 'switch', '', '1', 'TEASERPIC', 1261490672, 3000),
				('downloads', 'teaserpic_popup_width', 'int', '', '640', 'TEASERPIC', 1261490672, 4000),
				('downloads', 'teaserpic_popup_height', 'int', '', '480', 'TEASERPIC', 1261490672, 5000),
				('downloads', 'teaserpic_quality', 'switch', '', '1', 'TEASERPIC', 1261490672, 6000);
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            require_once BASEDIR.'lib/class.mediamanager.php';
            $mm = new mediamanager();
            @$mm->createdir('pics', 'downloads');
    }
}
