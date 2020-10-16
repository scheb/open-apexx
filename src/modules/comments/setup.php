<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $mysql = "
		CREATE TABLE `apx_comments` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `module` varchar(50) NOT NULL default '',
		  `mid` int(11) unsigned NOT NULL default '0',
		  `userid` int(11) unsigned NOT NULL default '0',
		  `username` tinytext NOT NULL,
		  `email` tinytext NOT NULL,
		  `homepage` tinytext NOT NULL,
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  `notify` tinyint(1) unsigned NOT NULL,
		  `ip` varchar(15) NOT NULL,
		  `active` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `module` (`module`,`mid`,`active`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('comments', 'blockip', 'array', 'BLOCK', 'a:0:{}', '', 0, 0),
		('comments', 'blockstring', 'array', 'BLOCK', 'a:0:{}', '', 0, 0),
		
		('comments', 'epp', 'int', '', '5', 'VIEW', 1241811530, 100),
		('comments', 'order', 'select', 'a:2:{i:0;s:10:\"{NEWFIRST}\";i:1;s:10:\"{OLDFIRST}\";}', '0', 'VIEW', 1241811530, 1000),
		('comments', 'breakline', 'int', '', '0', 'VIEW', 1241811530, 2000),
		('comments', 'popup', 'switch', '', '1', 'VIEW', 1241811530, 3000),
		('comments', 'popup_width', 'int', '', '500', 'VIEW', 1241811530, 4000),
		('comments', 'popup_height', 'int', '', '500', 'VIEW', 1241811530, 5000),
		
		('comments', 'pub', 'switch', '', '1', 'OPTIONS', 1241811530, 1000),
		('comments', 'maxlen', 'int', '', '10000', 'OPTIONS', 1241811530, 2000),
		('comments', 'spamprot', 'int', '', '1', 'OPTIONS', 1241811530, 3000),
		('comments', 'captcha', 'switch', '', '1', 'OPTIONS', 1241811530, 4000),
		('comments', 'mod', 'switch', '', '0', 'OPTIONS', 1241811530, 5000),
		('comments', 'req_email', 'switch', '', '0', 'OPTIONS', 1241811530, 6000),
		('comments', 'req_homepage', 'switch', '', '0', 'OPTIONS', 1241811530, 7000),
		('comments', 'req_title', 'switch', '', '0', 'OPTIONS', 1241811530, 8000),
		('comments', 'allowsmilies', 'switch', '', '1', 'OPTIONS', 1241811530, 9000),
		('comments', 'allowcode', 'switch', '', '1', 'OPTIONS', 1241811530, 10000),
		('comments', 'badwords', 'switch', '', '1', 'OPTIONS', 1241811530, 11000),
		('comments', 'reportmail', 'string', '', '', 'OPTIONS', 1241811530, 12000),
		('comments', 'mailonnew', 'string', '', '', 'OPTIONS', 1241811530, 13000);
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }
}

//Deinstallieren
elseif (SETUPMODE == 'uninstall') {
}

//Update
elseif (SETUPMODE == 'update') {
    switch ($installed_version) {
        case 100: //zu 1.0.1
            $mysql = "
				INSERT INTO `apx_config` VALUES ('comments', 'blockip', 'array', 'BLOCK', 'a:0:{}', 0, 0);
				INSERT INTO `apx_config` VALUES ('comments', 'blockstring', 'array', 'BLOCK', 'a:0:{}', '0', '0');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 101: //zu 1.0.2
            $mysql = "
				INSERT INTO `apx_config` VALUES ('comments', 'capcha', 'switch', '', '0', '0', '1550');
				INSERT INTO `apx_config` VALUES ('comments', 'mailonnew', 'string', '', '', '0', '1250');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 102: //zu 1.0.3
            $mysql = "
				UPDATE `apx_config` SET varname = 'captcha' WHERE module = 'comments' AND varname = 'capcha';
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 103: //zu 1.0.4
            $mysql = "
				INSERT INTO `apx_config` VALUES ('comments', 'order', 'select', 'a:2:{i:0;s:10:\"{NEWFIRST}\";i:1;s:10:\"{OLDFIRST}\";}', '0', '0', '150');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 103: //zu 1.0.4
            $mysql = "
				INSERT INTO `apx_config` VALUES ('comments', 'reportmail', 'string', '', '', '0', '1225');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 105: //zu 1.1.0

            //Indizes entfernen
            clearIndices(PRE.'_comments');

            //config Update
            updateConfig('comments', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('comments', 'blockip', 'array', 'BLOCK', 'a:0:{}', '', 0, 0),
				('comments', 'blockstring', 'array', 'BLOCK', 'a:0:{}', '', 0, 0),
				
				('comments', 'epp', 'int', '', '5', 'VIEW', 1241811530, 100),
				('comments', 'order', 'select', 'a:2:{i:0;s:10:\"{NEWFIRST}\";i:1;s:10:\"{OLDFIRST}\";}', '0', 'VIEW', 1241811530, 1000),
				('comments', 'breakline', 'int', '', '0', 'VIEW', 1241811530, 2000),
				('comments', 'popup', 'switch', '', '1', 'VIEW', 1241811530, 3000),
				('comments', 'popup_width', 'int', '', '500', 'VIEW', 1241811530, 4000),
				('comments', 'popup_height', 'int', '', '500', 'VIEW', 1241811530, 5000),
				
				('comments', 'pub', 'switch', '', '1', 'OPTIONS', 1241811530, 1000),
				('comments', 'maxlen', 'int', '', '10000', 'OPTIONS', 1241811530, 2000),
				('comments', 'spamprot', 'int', '', '1', 'OPTIONS', 1241811530, 3000),
				('comments', 'captcha', 'switch', '', '1', 'OPTIONS', 1241811530, 4000),
				('comments', 'mod', 'switch', '', '0', 'OPTIONS', 1241811530, 5000),
				('comments', 'req_email', 'switch', '', '0', 'OPTIONS', 1241811530, 6000),
				('comments', 'req_homepage', 'switch', '', '0', 'OPTIONS', 1241811530, 7000),
				('comments', 'req_title', 'switch', '', '0', 'OPTIONS', 1241811530, 8000),
				('comments', 'allowsmilies', 'switch', '', '1', 'OPTIONS', 1241811530, 9000),
				('comments', 'allowcode', 'switch', '', '1', 'OPTIONS', 1241811530, 10000),
				('comments', 'badwords', 'switch', '', '1', 'OPTIONS', 1241811530, 11000),
				('comments', 'reportmail', 'string', '', '', 'OPTIONS', 1241811530, 12000),
				('comments', 'mailonnew', 'string', '', '', 'OPTIONS', 1241811530, 13000);
			");

            $mysql = '
				ALTER TABLE `apx_comments` ADD INDEX ( `module` , `mid` , `active` ) ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }
    }
}
