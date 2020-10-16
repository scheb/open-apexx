<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $mysql = "
		CREATE TABLE `apx_videos` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `secid` tinytext NOT NULL,
		  `prodid` int(11) unsigned NOT NULL default '0',
		  `catid` int(11) unsigned NOT NULL default '0',
		  `userid` int(11) unsigned NOT NULL default '0',
		  `file` text NOT NULL,
		  `filesize` BIGINT UNSIGNED NOT NULL,
		  `flvfile` tinytext NOT NULL,
		  `status` enum('new','converting','failed','finished') NOT NULL default 'finished',
		  `source` varchar(30) NOT NULL,
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `teaserpic` tinytext NOT NULL,
		  `meta_description` text NOT NULL,
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
		  `downloads` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `catid` (`catid`),
		  KEY `userid` (`userid`),
		  KEY `starttime` (`starttime`,`endtime`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_videos_cat` (
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
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_videos_screens` (
		  `pictureid` int(11) unsigned NOT NULL auto_increment,
		  `videoid` int(11) unsigned NOT NULL,
		  `thumbnail` tinytext NOT NULL,
		  `picture` tinytext NOT NULL,
		  PRIMARY KEY  (`pictureid`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_videos_stats` (
		  `daystamp` int(8) unsigned NOT NULL default '0',
		  `time` int(11) unsigned NOT NULL default '0',
		  `dlid` int(11) unsigned NOT NULL default '0',
		  `bytes` bigint(20) unsigned NOT NULL default '0',
		  `hits` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`daystamp`,`dlid`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_videos_tags` (
		  `id` int(11) unsigned NOT NULL,
		  `tagid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`,`tagid`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('videos', 'ffmpeg', 'string', 'BLOCK', '', '', 0, 0),
		('videos', 'flvtool2', 'string', 'BLOCK', '', '', 0, 0),
		('videos', 'mencoder', 'string', 'BLOCK', '', '', 0, 0),
		
		('videos', 'flvwidth', 'int', '', '400', 'CONVERTER', 1261490672, 1000),
		('videos', 'flvheight', 'int', '', '300', 'CONVERTER', 1261490672, 2000),
		('videos', 'vbitrate', 'int', '', '1200', 'CONVERTER', 1261490672, 3000),
		('videos', 'abitrate', 'int', '', '128', 'CONVERTER', 1261490672, 4000),
		
		('videos', 'searchable', 'switch', '', '1', 'OPTIONS', 1261490672, 1000),
		('videos', 'regonly', 'switch', '', '0', 'OPTIONS', 1261490672, 2000),
		('videos', 'maxtraffic', 'float', '', '0', 'OPTIONS', 1261490672, 3000),
		('videos', 'exttraffic', 'switch', '', '1', 'OPTIONS', 1251291273, 3500),
		('videos', 'coms', 'switch', '', '1', 'OPTIONS', 1261490672, 4000),
		('videos', 'ratings', 'switch', '', '1', 'OPTIONS', 1261490672, 5000),
		('videos', 'mailonbroken', 'string', '', '', 'OPTIONS', 1261490672, 6000),
		
		('videos', 'addpics', 'int', '', '5', 'SCREENSHOTS', 1261490672, 1000),
		('videos', 'picwidth', 'int', '', '640', 'SCREENSHOTS', 1261490672, 2000),
		('videos', 'picheight', 'int', '', '480', 'SCREENSHOTS', 1261490672, 3000),
		('videos', 'watermark', 'string', '', '', 'SCREENSHOTS', 1261490672, 4000),
		('videos', 'watermark_transp', 'int', '', '50', 'SCREENSHOTS', 1261490672, 5000),
		('videos', 'watermark_position', 'select', 'a:9:{i:1;s:18:\"{POSTOP} {POSLEFT}\";i:2;s:20:\"{POSTOP} {POSCENTER}\";i:3;s:19:\"{POSTOP} {POSRIGHT}\";i:4;s:21:\"{POSMIDDLE} {POSLEFT}\";i:5;s:23:\"{POSMIDDLE} {POSCENTER}\";i:6;s:22:\"{POSMIDDLE} {POSRIGHT}\";i:7;s:21:\"{POSBOTTOM} {POSLEFT}\";i:8;s:23:\"{POSBOTTOM} {POSCENTER}\";i:9;s:22:\"{POSBOTTOM} {POSRIGHT}\";}', '9', 'SCREENSHOTS', 1261490672, 6000),
		('videos', 'thumbwidth', 'int', '', '120', 'SCREENSHOTS', 1261490672, 7000),
		('videos', 'thumbheight', 'int', '', '90', 'SCREENSHOTS', 1261490672, 8000),
		('videos', 'quality_resize', 'switch', '', '1', 'SCREENSHOTS', 1261490672, 9000),
		
		('videos', 'teaserpic_width', 'int', '', '120', 'TEASERPIC', 1261490672, 1000),
		('videos', 'teaserpic_height', 'int', '', '120', 'TEASERPIC', 1261490672, 2000),
		('videos', 'teaserpic_popup', 'switch', '', '1', 'TEASERPIC', 1261490672, 3000),
		('videos', 'teaserpic_popup_width', 'int', '', '640', 'TEASERPIC', 1261490672, 4000),
		('videos', 'teaserpic_popup_height', 'int', '', '480', 'TEASERPIC', 1261490672, 5000),
		('videos', 'teaserpic_quality', 'switch', '', '1', 'TEASERPIC', 1261490672, 6000),
		
		('videos', 'epp', 'int', '', '20', 'VIEW', 1261490672, 1000),
		('videos', 'searchepp', 'string', '', '20', 'VIEW', 1261490672, 2000),
		('videos', 'catonly', 'switch', '', '1', 'VIEW', 1261490672, 3000),
		('videos', 'sortby', 'select', 'a:2:{i:1;s:7:\"{TITLE}\";i:2;s:6:\"{DATE}\";}', '1', 'VIEW', 1261490672, 4000),
		('videos', 'new', 'int', '', '3', 'VIEW', 1261490672, 5000),
		('videos', 'embed_width', 'int', '', '320', 'VIEW', '0', '6000'),
		('videos', 'embed_height', 'int', '', '240', 'VIEW', '0', '7000');
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }

    require_once BASEDIR.'lib/class.mediamanager.php';
    $mm = new mediamanager();
    $mm->createdir('videos');
    $mm->createdir('flv', 'videos');
    $mm->createdir('logs', 'videos');
    $mm->createdir('pics', 'videos');
    $mm->createdir('screens', 'videos');
}

//Deinstallieren
elseif (SETUPMODE == 'uninstall') {
    $mysql = '
		DROP TABLE `apx_videos`;
		DROP TABLE `apx_videos_cat`;
		DROP TABLE `apx_videos_screens`;
		DROP TABLE `apx_videos_stats`;
		DROP TABLE `apx_videos_tags`;
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
				ALTER TABLE `apx_videos` ADD `filesize` BIGINT UNSIGNED NOT NULL AFTER `file` ;
				INSERT INTO `apx_config` VALUES ('videos', 'exttraffic', 'switch', '', '1', 'OPTIONS', 1251291273, 3500);
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 101: //zu 1.0.2
            $mysql = "
				UPDATE `apx_videos` SET downloads=hits, hits=0;
				INSERT INTO `apx_config` VALUES ('videos', 'embed_width', 'int', '', '320', 'VIEW', '0', '6000');
				INSERT INTO `apx_config` VALUES ('videos', 'embed_height', 'int', '', '240', 'VIEW', '0', '7000');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 102: //zu 1.0.3
            $mysql = '
				ALTER TABLE `apx_videos` ADD `meta_description` TEXT NOT NULL AFTER `teaserpic` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }
    }
}
