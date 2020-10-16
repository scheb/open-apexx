<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Installieren
if ( SETUPMODE=='install' ) {
	$mysql="
		CREATE TABLE `apx_faq` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `question` text NOT NULL,
		  `answer` text NOT NULL,
		  `meta_description` text NOT NULL,
		  `addtime` int(11) unsigned NOT NULL default '0',
		  `starttime` int(11) unsigned NOT NULL default '0',
		  `searchable` tinyint(1) unsigned NOT NULL default '0',
		  `hits` int(11) unsigned NOT NULL default '0',
		  `parents` varchar(255) NOT NULL,
		  `children` text NOT NULL,
		  `ord` tinyint(3) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `parents` (`parents`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('faq', 'searchable', 'switch', '', '1', '', 0, 1000);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_faq`;
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				INSERT INTO `apx_config` ( `module` , `varname` , `type` , `addnl` , `value` , `lastchange` , `ord` ) VALUES ('faq', 'searchable', 'switch', '', '1', '0', '50');
				ALTER TABLE `apx_faq` ADD `searchable` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `starttime` ;
				UPDATE `apx_faq` SET starttime=addtime;
				UPDATE `apx_faq` SET searchable='1';
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 101: //zu 1.1.0
			
			//Indizes entfernen
			clearIndices(PRE.'_faq');
			
			//Tabellenformat ändern
			convertRecursiveTable(PRE.'_faq');
			
			//config Update
			updateConfig('faq', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('faq', 'searchable', 'switch', '', '1', '', 0, 1000);
			");
			
			$mysql="
				ALTER TABLE `apx_faq` ADD INDEX ( `parents` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 110: //zu 1.1.1
			$mysql="
				ALTER TABLE `apx_faq` ADD `meta_description` TEXT NOT NULL AFTER `answer` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
	}
}


?>