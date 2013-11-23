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
		CREATE TABLE `apx_ratings` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `module` varchar(50) NOT NULL default '',
		  `mid` int(11) unsigned NOT NULL default '0',
		  `rating` smallint(5) NOT NULL default '0',
		  `ip` varchar(15) NOT NULL,
		  `time` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `module` (`module`,`mid`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('ratings', 'possible', 'array_keys', '', 'a:5:{i:1;s:1:\"1\";i:2;s:1:\"2\";i:3;s:1:\"3\";i:4;s:1:\"4\";i:5;s:1:\"5\";}', '', 1120514753, 1000),
		('ratings', 'digits', 'int', '', '1', '', 1120514753, 2000),
		('ratings', 'block', 'int', '', '1440', '', 1120514753, 3000);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_ratings`;
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //Zu 1.1.0
			
			//Indizes entfernen
			clearIndices(PRE.'_ratings');
			
			//config Update
			updateConfig('ratings', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('ratings', 'possible', 'array_keys', '', 'a:5:{i:1;s:1:\"1\";i:2;s:1:\"2\";i:3;s:1:\"3\";i:4;s:1:\"4\";i:5;s:1:\"5\";}', '', 1120514753, 1000),
				('ratings', 'digits', 'int', '', '1', '', 1120514753, 2000),
				('ratings', 'block', 'int', '', '1440', '', 1120514753, 3000);
			");
			
			$mysql="
				ALTER TABLE `apx_ratings` CHANGE `ip` `ip` VARCHAR( 15 ) NOT NULL ;
				
				ALTER TABLE `apx_ratings` CHANGE `module` `module` VARCHAR( 50 ) NOT NULL ; 
				ALTER TABLE `apx_ratings` ADD INDEX ( `module` , `mid` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
	}
}

?>