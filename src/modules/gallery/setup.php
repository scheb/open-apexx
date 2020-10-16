<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $mysql = "
		CREATE TABLE `apx_gallery` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `secid` tinytext NOT NULL,
		  `prodid` int(11) unsigned NOT NULL default '0',
		  `title` tinytext NOT NULL,
		  `description` text NOT NULL,
		  `meta_description` text NOT NULL,
		  `password` tinytext NOT NULL,
		  `preview` tinytext NOT NULL,
		  `addtime` int(11) unsigned NOT NULL default '0',
		  `starttime` int(11) unsigned NOT NULL default '0',
		  `endtime` int(11) unsigned NOT NULL default '0',
		  `lastupdate` INT( 10 ) UNSIGNED NOT NULL,
		  `searchable` tinyint(1) unsigned NOT NULL default '0',
		  `allowcoms` tinyint(1) unsigned NOT NULL,
		  `restricted` tinyint(1) unsigned NOT NULL,
		  `parents` varchar(255) NOT NULL,
		  `children` text NOT NULL,
		  `ord` tinyint(3) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `parents` (`parents`),
		  KEY `starttime` (`starttime`,`endtime`,`parents`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_gallery_pics` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `galid` int(11) unsigned NOT NULL default '0',
		  `thumbnail` tinytext NOT NULL,
		  `picture` tinytext NOT NULL,
		  `caption` tinytext NOT NULL,
		  `hits` int(11) unsigned NOT NULL default '0',
		  `addtime` int(11) unsigned NOT NULL default '0',
		  `allowcoms` tinyint(1) unsigned NOT NULL default '1',
		  `allowrating` tinyint(1) unsigned NOT NULL default '1',
		  `active` tinyint(1) unsigned NOT NULL default '0',
		  `potw` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `galid` (`galid`,`active`),
		  KEY `active` (`active`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_gallery_tags` (
		  `id` int(11) unsigned NOT NULL,
		  `tagid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`,`tagid`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('gallery', 'potw_time', 'int', 'BLOCK', '1249981414', '', 0, 0),
		
		('gallery', 'listepp', 'int', '', '5', 'VIEW', 1249816805, 1000),
		('gallery', 'galepp', 'int', '', '16', 'VIEW', 1249816805, 2000),
		('gallery', 'ordergal', 'select', 'a:3:{i:1;s:11:\"{ORDERTIME}\";i:2;s:12:\"{ORDERTITLE}\";i:3;s:12:\"{ORDERADMIN}\";}', '1', 'VIEW', 1249816805, 3000),
		('gallery', 'orderpics', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '1', 'VIEW', 1249816805, 4000),
		('gallery', 'new', 'int', '', '3', 'VIEW', 1249816805, 5000),
		
		('gallery', 'searchable', 'switch', '', '1', 'OPTIONS', 1249816805, 1000),
		('gallery', 'subgals', 'switch', '', '1', 'OPTIONS', 1249816805, 2000),
		('gallery', 'coms', 'switch', '', '1', 'OPTIONS', 1249816805, 3000),
		('gallery', 'galcoms', 'switch', '', '1', 'OPTIONS', 1249816805, 3000),
		('gallery', 'ratings', 'switch', '', '1', 'OPTIONS', 1249816805, 4000),
		('gallery', 'potw_auto', 'switch', '', '0', 'OPTIONS', 1249816805, 5000),
		
		('gallery', 'addpics', 'int', '', '10', 'IMAGES', 1249816805, 1000),
		('gallery', 'picwidth', 'int', '', '640', 'IMAGES', 1249816805, 2000),
		('gallery', 'picheight', 'int', '', '480', 'IMAGES', 1249816805, 3000),
		('gallery', 'watermark', 'string', '', '', 'IMAGES', 1249816805, 4000),
		('gallery', 'watermark_transp', 'int', '', '50', 'IMAGES', 1249816805, 5000),
		('gallery', 'watermark_position', 'select', 'a:9:{i:1;s:18:\"{POSTOP} {POSLEFT}\";i:2;s:20:\"{POSTOP} {POSCENTER}\";i:3;s:19:\"{POSTOP} {POSRIGHT}\";i:4;s:21:\"{POSMIDDLE} {POSLEFT}\";i:5;s:23:\"{POSMIDDLE} {POSCENTER}\";i:6;s:22:\"{POSMIDDLE} {POSRIGHT}\";i:7;s:21:\"{POSBOTTOM} {POSLEFT}\";i:8;s:23:\"{POSBOTTOM} {POSCENTER}\";i:9;s:22:\"{POSBOTTOM} {POSRIGHT}\";}', '9', 'IMAGES', 1249816805, 6000),
		('gallery', 'thumbwidth', 'int', '', '120', 'IMAGES', 1249816805, 7000),
		('gallery', 'thumbheight', 'int', '', '90', 'IMAGES', 1249816805, 8000),
		('gallery', 'quality_resize', 'switch', '', '1', 'IMAGES', 1249816805, 9000),
		('gallery', 'popup', 'switch', '', '1', 'IMAGES', 1249816805, 10000),
		('gallery', 'popup_addwidth', 'int', '', '60', 'IMAGES', 1249816805, 12000),
		('gallery', 'popup_addheight', 'int', '', '150', 'IMAGES', 1249816805, 13000),
		('gallery', 'popup_resizeable', 'switch', '', '1', 'IMAGES', 1249816805, 14000);
	";

    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }

    require_once BASEDIR.'lib/class.mediamanager.php';
    $mm = new mediamanager();
    $mm->createdir('gallery');
    $mm->createdir('uploads', 'gallery');
}

//Deinstallieren
elseif (SETUPMODE == 'uninstall') {
    $mysql = '
		DROP TABLE `apx_gallery`;
		DROP TABLE `apx_gallery_pics`;
		DROP TABLE `apx_gallery_tags`;
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
				ALTER TABLE `apx_gallery` ADD `endtime` INT( 11 ) UNSIGNED NOT NULL AFTER `starttime`;
				UPDATE `apx_gallery` SET endtime='3000000000' WHERE starttime!='0';
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 101: //zu 1.0.2
            $mysql = "
				INSERT INTO `apx_config` ( `module` , `varname` , `type` , `addnl` , `value` , `lastchange` , `ord` ) VALUES ('gallery', 'searchable', 'switch', '', '1', '0', '50');
				ALTER TABLE `apx_gallery` ADD `searchable` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `endtime` ;
				ALTER TABLE `apx_gallery` ADD `keywords` TINYTEXT NOT NULL AFTER `description` ;
				UPDATE `apx_gallery` SET searchable='1';
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 102: //zu 1.0.3
            $mysql = '
				ALTER TABLE `apx_gallery` ADD `prodid` INT( 11 ) UNSIGNED NOT NULL AFTER `secid` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 103: //zu 1.0.4
            $mysql = "
				INSERT INTO `apx_config` VALUES ('gallery', 'galcoms', 'switch', '', '0', '1152120685', '2050');
				ALTER TABLE `apx_gallery` ADD `allowcoms` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `searchable` ;
				UPDATE `apx_gallery` SET allowcoms=1;
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 104: //zu 1.0.4

            //Indizes entfernen
            clearIndices(PRE.'_gallery');
            clearIndices(PRE.'_gallery_pics');

            //Tabellenformat ändern
            convertRecursiveTable(PRE.'_gallery');

            //config Update
            updateConfig('gallery', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('gallery', 'potw_time', 'int', 'BLOCK', '1249981414', '', 0, 0),
				
				('gallery', 'listepp', 'int', '', '5', 'VIEW', 1249816805, 1000),
				('gallery', 'galepp', 'int', '', '16', 'VIEW', 1249816805, 2000),
				('gallery', 'ordergal', 'select', 'a:3:{i:1;s:11:\"{ORDERTIME}\";i:2;s:12:\"{ORDERTITLE}\";i:3;s:12:\"{ORDERADMIN}\";}', '1', 'VIEW', 1249816805, 3000),
				('gallery', 'orderpics', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '1', 'VIEW', 1249816805, 4000),
				('gallery', 'new', 'int', '', '3', 'VIEW', 1249816805, 5000),
				
				('gallery', 'searchable', 'switch', '', '1', 'OPTIONS', 1249816805, 1000),
				('gallery', 'subgals', 'switch', '', '1', 'OPTIONS', 1249816805, 2000),
				('gallery', 'coms', 'switch', '', '1', 'OPTIONS', 1249816805, 3000),
				('gallery', 'galcoms', 'switch', '', '1', 'OPTIONS', 1249816805, 3000),
				('gallery', 'ratings', 'switch', '', '1', 'OPTIONS', 1249816805, 4000),
				('gallery', 'potw_auto', 'switch', '', '0', 'OPTIONS', 1249816805, 5000),
				
				('gallery', 'addpics', 'int', '', '10', 'IMAGES', 1249816805, 1000),
				('gallery', 'picwidth', 'int', '', '640', 'IMAGES', 1249816805, 2000),
				('gallery', 'picheight', 'int', '', '480', 'IMAGES', 1249816805, 3000),
				('gallery', 'watermark', 'string', '', '', 'IMAGES', 1249816805, 4000),
				('gallery', 'watermark_transp', 'int', '', '50', 'IMAGES', 1249816805, 5000),
				('gallery', 'watermark_position', 'select', 'a:9:{i:1;s:18:\"{POSTOP} {POSLEFT}\";i:2;s:20:\"{POSTOP} {POSCENTER}\";i:3;s:19:\"{POSTOP} {POSRIGHT}\";i:4;s:21:\"{POSMIDDLE} {POSLEFT}\";i:5;s:23:\"{POSMIDDLE} {POSCENTER}\";i:6;s:22:\"{POSMIDDLE} {POSRIGHT}\";i:7;s:21:\"{POSBOTTOM} {POSLEFT}\";i:8;s:23:\"{POSBOTTOM} {POSCENTER}\";i:9;s:22:\"{POSBOTTOM} {POSRIGHT}\";}', '9', 'IMAGES', 1249816805, 6000),
				('gallery', 'thumbwidth', 'int', '', '120', 'IMAGES', 1249816805, 7000),
				('gallery', 'thumbheight', 'int', '', '90', 'IMAGES', 1249816805, 8000),
				('gallery', 'quality_resize', 'switch', '', '1', 'IMAGES', 1249816805, 9000),
				('gallery', 'popup', 'switch', '', '1', 'IMAGES', 1249816805, 10000),
				('gallery', 'popup_addwidth', 'int', '', '60', 'IMAGES', 1249816805, 12000),
				('gallery', 'popup_addheight', 'int', '', '150', 'IMAGES', 1249816805, 13000),
				('gallery', 'popup_resizeable', 'switch', '', '1', 'IMAGES', 1249816805, 14000);
			");

            $mysql = '
				CREATE TABLE `apx_gallery_tags` (
					`id` INT( 11 ) UNSIGNED NOT NULL ,
					`tagid` INT( 11 ) UNSIGNED NOT NULL ,
					PRIMARY KEY ( `id` , `tagid` )
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_gallery` ADD `restricted` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `allowcoms` ;
				
				ALTER TABLE `apx_gallery` ADD INDEX ( `parents` ) ;
				ALTER TABLE `apx_gallery` ADD INDEX ( `starttime` , `endtime` , `parents` ) ;
				ALTER TABLE `apx_gallery_pics` ADD INDEX ( `galid`, `active` ) ;
				ALTER TABLE `apx_gallery_pics` ADD INDEX ( `active` ) ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            //Tags erzeugen
            transformKeywords(PRE.'_gallery', PRE.'_gallery_tags');

            // no break
        case 110: //zu 1.1.1
            $mysql = '
				ALTER TABLE `apx_gallery` ADD `meta_description` TEXT NOT NULL AFTER `description` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 111: //zu 1.1.2
            $mysql = '
				ALTER TABLE `apx_gallery` ADD `lastupdate` INT( 10 ) UNSIGNED NOT NULL AFTER `endtime` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }
    }
}
