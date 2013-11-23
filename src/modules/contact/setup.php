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
		CREATE TABLE `apx_contact` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` tinytext NOT NULL,
		  `email` tinytext NOT NULL,
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('contact', 'captcha', 'switch', '', '1', '', 0, 1000);
	";
	
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_contact`;
	";
	
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				INSERT INTO `apx_config` VALUES ('contact', 'captcha', 'switch', '', '0', '0', '100');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			
		case 101: //zu 1.0.2
			$mysql="
				UPDATE `apx_config` SET varname = 'captcha' WHERE module = 'contact' AND varname = 'capcha';
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
		case 102: //zu 1.1.0
			
			//config Update
			updateConfig('contact', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('contact', 'captcha', 'switch', '', '1', '', 0, 1000);
			");
		
	}
}

?>