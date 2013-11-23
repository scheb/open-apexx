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
		CREATE TABLE IF NOT EXISTS `apx_teaser` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `secid` tinytext NOT NULL,
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `image` tinytext NOT NULL,
		  `link` tinytext NOT NULL,
		  `group` tinyint(2) unsigned NOT NULL DEFAULT '1',
		  `hits` int(11) unsigned NOT NULL DEFAULT '0',
		  `ord` smallint(4) unsigned NOT NULL DEFAULT '0',
		  `addtime` int(10) unsigned NOT NULL,
		  `starttime` int(10) NOT NULL,
		  `endtime` int(10) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `addtime` (`addtime`),
		  KEY `starttime` (`starttime`,`endtime`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('teaser', 'orderby', 'select', 'a:3:{i:1;s:12:\"{ORDERADMIN}\";i:2;s:10:\"{ORDERPUB}\";i:3;s:13:\"{ORDERRANDOM}\";}', '2', '', 1129897415, 1000),
		('teaser', 'groups', 'array', 'BLOCK', 'a:0:{}', '', 0, 0);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
	
	//Ordner für Bilder
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$mm=new mediamanager;
	$mm->createdir('teaser');
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_teaser`;
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //Zu 1.1.0
			/*
			$mysql="
				
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			*/
	}
}

?>