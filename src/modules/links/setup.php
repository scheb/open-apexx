<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $mysql = "
		CREATE TABLE `apx_links` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `secid` tinytext NOT NULL,
		  `catid` int(11) unsigned NOT NULL default '0',
		  `userid` int(11) unsigned NOT NULL default '0',
		  `send_username` tinytext NOT NULL,
		  `send_email` tinytext NOT NULL,
		  `send_ip` tinytext NOT NULL,
		  `title` tinytext NOT NULL,
		  `url` text NOT NULL,
		  `linkpic` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `meta_description` text NOT NULL,
		  `galid` int(11) unsigned NOT NULL default '0',
		  `addtime` int(11) unsigned NOT NULL default '0',
		  `starttime` int(11) unsigned NOT NULL default '0',
		  `endtime` int(11) unsigned NOT NULL default '0',
		  `broken` int(11) unsigned NOT NULL default '0',
		  `top` tinyint(1) unsigned NOT NULL default '0',
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
		
		CREATE TABLE `apx_links_cat` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `icon` tinytext NOT NULL,
		  `open` tinyint(1) unsigned NOT NULL default '1',
		  `parents` varchar(255) NOT NULL,
		  `children` text NOT NULL,
		  `ord` tinyint(3) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `parents` (`parents`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_links_tags` (
		  `id` int(11) unsigned NOT NULL,
		  `tagid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`,`tagid`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('links', 'epp', 'int', '', '20', 'VIEW', 1249981968, 1000),
		('links', 'searchepp', 'string', '', '20', 'VIEW', 1249981881, 2000),
		('links', 'catonly', 'switch', '', '1', 'VIEW', 1249981968, 3000),
		('links', 'sortby', 'select', 'a:2:{i:1;s:7:\"{TITLE}\";i:2;s:6:\"{DATE}\";}', '1', 'VIEW', 1249981968, 4000),
		('links', 'new', 'int', '', '3', 'VIEW', 1249981968, 5000),
		
		('links', 'searchable', 'switch', '', '1', 'OPTIONS', 1249981968, 1000),
		('links', 'coms', 'switch', '', '1', 'OPTIONS', 1249981968, 2000),
		('links', 'ratings', 'switch', '', '1', 'OPTIONS', 1249981968, 3000),
		('links', 'captcha', 'switch', '', '1', 'OPTIONS', 1249981968, 4000),
		('links', 'spamprot', 'int', '', '1', 'OPTIONS', 1249981968, 5000),
		('links', 'mailonnew', 'string', '', '', 'OPTIONS', 1249981968, 6000),
		('links', 'mailonbroken', 'string', '', '', 'OPTIONS', 1249981968, 7000),
		
		('links', 'linkpic_width', 'int', '', '120', 'IMAGES', 1249981968, 1000),
		('links', 'linkpic_height', 'int', '', '120', 'IMAGES', 1249981968, 2000),
		('links', 'linkpic_popup', 'switch', '', '1', 'IMAGES', 1249981968, 3000),
		('links', 'linkpic_popup_width', 'int', '', '640', 'IMAGES', 1249981968, 4000),
		('links', 'linkpic_popup_height', 'int', '', '480', 'IMAGES', 1249981968, 5000),
		('links', 'linkpic_quality', 'switch', '', '1', 'IMAGES', 1249981968, 6000);
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }

    //Links-DIR
    require_once BASEDIR.'lib/class.mediamanager.php';
    $mm = new mediamanager();
    $mm->createdir('links');
}

//Deinstallieren
elseif (SETUPMODE == 'uninstall') {
    $mysql = '
		DROP TABLE `apx_links`;
		DROP TABLE `apx_links_cat`;
		DROP TABLE `apx_links_tags`;
	';

    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }
}

//Update
elseif (SETUPMODE == 'update') {
    switch ($installed_version) {
        case 100: //Zu 1.0.1
            $mysql = "
				INSERT INTO `apx_config` VALUES ('links', 'mailonnew', 'string', '', '', 0, 1800);
				INSERT INTO `apx_config` VALUES ('links', 'captcha', 'switch', '', '0', 0, 1350);
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 101: //Zu 1.0.2
            $mysql = '
				ALTER TABLE `apx_links` ADD `broken` INT( 11 ) UNSIGNED NOT NULL AFTER `endtime` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 102: //Zu 1.1.0

            //Indizes entfernen
            clearIndices(PRE.'_links');
            clearIndices(PRE.'_links_cat');

            //Tabellenformat ändern
            convertRecursiveTable(PRE.'_links_cat');

            //config Update
            updateConfig('links', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('links', 'epp', 'int', '', '20', 'VIEW', 1249981968, 1000),
				('links', 'searchepp', 'string', '', '20', 'VIEW', 1249981881, 2000),
				('links', 'catonly', 'switch', '', '1', 'VIEW', 1249981968, 3000),
				('links', 'sortby', 'select', 'a:2:{i:1;s:7:\"{TITLE}\";i:2;s:6:\"{DATE}\";}', '1', 'VIEW', 1249981968, 4000),
				('links', 'new', 'int', '', '3', 'VIEW', 1249981968, 5000),
				
				('links', 'searchable', 'switch', '', '1', 'OPTIONS', 1249981968, 1000),
				('links', 'coms', 'switch', '', '1', 'OPTIONS', 1249981968, 2000),
				('links', 'ratings', 'switch', '', '1', 'OPTIONS', 1249981968, 3000),
				('links', 'captcha', 'switch', '', '1', 'OPTIONS', 1249981968, 4000),
				('links', 'spamprot', 'int', '', '1', 'OPTIONS', 1249981968, 5000),
				('links', 'mailonnew', 'string', '', '', 'OPTIONS', 1249981968, 6000),
				('links', 'mailonbroken', 'string', '', '', 'OPTIONS', 1249981968, 7000),
				
				('links', 'linkpic_width', 'int', '', '120', 'IMAGES', 1249981968, 1000),
				('links', 'linkpic_height', 'int', '', '120', 'IMAGES', 1249981968, 2000),
				('links', 'linkpic_popup', 'switch', '', '1', 'IMAGES', 1249981968, 3000),
				('links', 'linkpic_popup_width', 'int', '', '640', 'IMAGES', 1249981968, 4000),
				('links', 'linkpic_popup_height', 'int', '', '480', 'IMAGES', 1249981968, 5000),
				('links', 'linkpic_quality', 'switch', '', '1', 'IMAGES', 1249981968, 6000);
			");

            $mysql = '
				CREATE TABLE `apx_links_tags` (
					`id` INT( 11 ) UNSIGNED NOT NULL ,
					`tagid` INT( 11 ) UNSIGNED NOT NULL ,
					PRIMARY KEY ( `id` , `tagid` )
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_links` ADD `restricted` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `allowrating` ;
				
				ALTER TABLE `apx_links` ADD INDEX ( `catid` ) ;
				ALTER TABLE `apx_links` ADD INDEX ( `userid` ) ;
				ALTER TABLE `apx_links` ADD INDEX ( `starttime` , `endtime` ) ;
				ALTER TABLE `apx_links_cat` ADD INDEX ( `parents` ) ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            //Tags erzeugen
            transformKeywords(PRE.'_links', PRE.'_links_tags');

            // no break
        case 110: //Zu 1.1.1
            $mysql = '
				ALTER TABLE `apx_links` ADD `meta_description` TEXT NOT NULL AFTER `text` ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }
    }
}
