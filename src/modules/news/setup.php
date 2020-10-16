<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $mysql = "
		CREATE TABLE `apx_news` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `secid` tinytext NOT NULL,
		  `prodid` int(11) unsigned NOT NULL default '0',
		  `catid` int(11) unsigned NOT NULL default '0',
		  `userid` int(11) unsigned NOT NULL default '0',
		  `send_username` tinytext NOT NULL,
		  `send_email` tinytext NOT NULL,
		  `send_ip` tinytext NOT NULL,
		  `newspic` tinytext NOT NULL,
		  `title` tinytext NOT NULL,
		  `subtitle` tinytext NOT NULL,
		  `teaser` text NOT NULL,
		  `text` mediumtext NOT NULL,
		  `galid` int(11) unsigned NOT NULL default '0',
		  `meta_description` text NOT NULL,
		  `links` text NOT NULL,
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
		  KEY `catid` (`catid`),
		  KEY `userid` (`userid`),
		  KEY `starttime` (`starttime`,`endtime`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_news_cat` (
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
		
		CREATE TABLE `apx_news_sources` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` tinytext NOT NULL,
		  `link` tinytext NOT NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_news_tags` (
		  `id` int(11) unsigned NOT NULL,
		  `tagid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`,`tagid`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('news', 'epp', 'int', '', '5', 'VIEW', 1249584889, 1000),
		('news', 'archiveepp', 'int', '', '5', 'VIEW', 1249584889, 2000),
		('news', 'archiveall', 'switch', '', '0', 'VIEW', 1249584889, 3000),
		('news', 'searchepp', 'int', '', '10', 'VIEW', 1249584889, 4000),
		('news', 'archivesort', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '2', 'VIEW', 1249584889, 5000),
		('news', 'archiveentrysort', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '1', 'VIEW', 1249584889, 6000),
		
		('news', 'searchable', 'switch', '', '1', 'OPTIONS', 1249584889, 1000),
		('news', 'subcats', 'switch', '', '1', 'OPTIONS', 1249584889, 2000),
		('news', 'teaser', 'switch', '', '1', 'OPTIONS', 1249584889, 3000),
		('news', 'coms', 'switch', '', '1', 'OPTIONS', 1249584889, 4000),
		('news', 'ratings', 'switch', '', '1', 'OPTIONS', 1249584889, 5000),
		('news', 'archcoms', 'switch', '', '1', 'OPTIONS', 1249584889, 6000),
		('news', 'archratings', 'switch', '', '1', 'OPTIONS', 1249584889, 7000),
		('news', 'captcha', 'switch', '', '1', 'OPTIONS', 1249584889, 8000),
		('news', 'spamprot', 'int', '', '1', 'OPTIONS', 1249584889, 9000),
		('news', 'mailonnew', 'string', '', '', 'OPTIONS', 1249584889, 10000),
		
		('news', 'newspic_width', 'int', '', '120', 'IMAGES', 1249584889, 1000),
		('news', 'newspic_height', 'int', '', '120', 'IMAGES', 1249584889, 2000),
		('news', 'newspic_popup', 'switch', '', '1', 'IMAGES', 1249584889, 3000),
		('news', 'newspic_popup_width', 'int', '', '640', 'IMAGES', 1249584889, 4000),
		('news', 'newspic_popup_height', 'int', '', '480', 'IMAGES', 1249584889, 5000),
		('news', 'newspic_quality', 'switch', '', '1', 'IMAGES', 1249584889, 6000);
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }

    //Newsbilder-Ordner
    require_once BASEDIR.'lib/class.mediamanager.php';
    $mm = new mediamanager();
    $mm->createdir('news');
}

//Deinstallieren
elseif (SETUPMODE == 'uninstall') {
    $mysql = '
		DROP TABLE `apx_news`;
		DROP TABLE `apx_news_cat`;
		DROP TABLE `apx_news_tags`;
		DROP TABLE `apx_news_sources`;
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
				ALTER TABLE `apx_news` ADD `galid` INT( 11 ) UNSIGNED NOT NULL AFTER `text` ;
				INSERT INTO `apx_config` VALUES ('news', 'subcats', 'switch', '', '1', '0', '150');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 101: //zu 1.0.2
            $mysql = "
				INSERT INTO `apx_config` VALUES ('news', 'searchable', 'switch', '', '1', '0', '50');
				INSERT INTO `apx_config` VALUES ('news', 'mailonnew', 'string', '', '', '0', '1800');
				ALTER TABLE `apx_news` ADD `searchable` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `sticky` ;
				ALTER TABLE `apx_news` ADD `keywords` TINYTEXT NOT NULL AFTER `text` ;
				UPDATE `apx_news` SET searchable='1';
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 102: //zu 1.0.3
            $mysql = "
				INSERT INTO `apx_config` VALUES ('news', 'captcha', 'switch', '', '0', 0, 1650);
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 103: //zu 1.0.4
            $mysql = "
				INSERT INTO `apx_config` VALUES ('news', 'searchepp', 'int', '', '10', '0', '1630');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 104: //zu 1.0.5
            $mysql = "
				INSERT INTO `apx_config` VALUES ('news', 'archiveentrysort', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '1', 1144936997, 1625);
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 105: //zu 1.0.6
            $mysql = '
				ALTER TABLE `apx_news` ADD `prodid` INT( 11 ) UNSIGNED NOT NULL AFTER `secid` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 106: //zu 1.0.7
            list($check) = $db->first('SELECT varname FROM '.PRE."_config WHERE module='news' AND varname='searchepp' LIMIT 1");
            if (!$check) {
                $db->query('INSERT INTO '.PRE."_config VALUES ('news', 'searchepp', 'int', '', '10', '0', '1630');");
            }

            // no break
        case 107: //zu 1.0.8
            $mysql = "
				ALTER TABLE `apx_news` CHANGE `sticky` `sticky` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0' ;
				UPDATE `apx_news` SET sticky='3000000000' WHERE sticky=1;
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 108: //zu 1.1.0

            //Indizes entfernen
            clearIndices(PRE.'_news');
            clearIndices(PRE.'_news_cat');

            //Tabellenformat ändern
            convertRecursiveTable(PRE.'_news_cat');

            //config Update
            updateConfig('news', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('news', 'epp', 'int', '', '5', 'VIEW', 1249584889, 1000),
				('news', 'archiveepp', 'int', '', '5', 'VIEW', 1249584889, 2000),
				('news', 'archiveall', 'switch', '', '0', 'VIEW', 1249584889, 3000),
				('news', 'searchepp', 'int', '', '10', 'VIEW', 1249584889, 4000),
				('news', 'archivesort', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '2', 'VIEW', 1249584889, 5000),
				('news', 'archiveentrysort', 'select', 'a:2:{i:1;s:10:\"{NEWFIRST}\";i:2;s:10:\"{OLDFIRST}\";}', '1', 'VIEW', 1249584889, 6000),
				
				('news', 'searchable', 'switch', '', '1', 'OPTIONS', 1249584889, 1000),
				('news', 'subcats', 'switch', '', '1', 'OPTIONS', 1249584889, 2000),
				('news', 'teaser', 'switch', '', '1', 'OPTIONS', 1249584889, 3000),
				('news', 'coms', 'switch', '', '1', 'OPTIONS', 1249584889, 4000),
				('news', 'ratings', 'switch', '', '1', 'OPTIONS', 1249584889, 5000),
				('news', 'archcoms', 'switch', '', '1', 'OPTIONS', 1249584889, 6000),
				('news', 'archratings', 'switch', '', '1', 'OPTIONS', 1249584889, 7000),
				('news', 'captcha', 'switch', '', '1', 'OPTIONS', 1249584889, 8000),
				('news', 'spamprot', 'int', '', '1', 'OPTIONS', 1249584889, 9000),
				('news', 'mailonnew', 'string', '', '', 'OPTIONS', 1249584889, 10000),
				
				('news', 'newspic_width', 'int', '', '120', 'IMAGES', 1249584889, 1000),
				('news', 'newspic_height', 'int', '', '120', 'IMAGES', 1249584889, 2000),
				('news', 'newspic_popup', 'switch', '', '1', 'IMAGES', 1249584889, 3000),
				('news', 'newspic_popup_width', 'int', '', '640', 'IMAGES', 1249584889, 4000),
				('news', 'newspic_popup_height', 'int', '', '480', 'IMAGES', 1249584889, 5000),
				('news', 'newspic_quality', 'switch', '', '1', 'IMAGES', 1249584889, 6000);
			");

            $mysql = '
				CREATE TABLE `apx_news_tags` (
					`id` INT( 11 ) UNSIGNED NOT NULL ,
					`tagid` INT( 11 ) UNSIGNED NOT NULL ,
					PRIMARY KEY ( `id` , `tagid` )
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_news` ADD `restricted` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `allowrating` ;
				
				ALTER TABLE `apx_news` ADD INDEX ( `catid` ) ;
				ALTER TABLE `apx_news` ADD INDEX ( `userid` ) ;
				ALTER TABLE `apx_news` ADD INDEX ( `starttime` , `endtime` ) ;
				ALTER TABLE `apx_news_cat` ADD INDEX ( `parents` ) ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            //Tags erzeugen
            transformKeywords(PRE.'_news', PRE.'_news_tags');

            // no break
        case 110: //zu 1.1.1
            $mysql = '
				ALTER TABLE `apx_news` ADD `meta_description` TEXT NOT NULL AFTER `galid` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }
    }
}
