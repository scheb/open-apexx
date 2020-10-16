<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Installieren
if ( SETUPMODE=='install' ) {
	$mysql="
		CREATE TABLE `apx_affiliates` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` tinytext NOT NULL,
		  `image` tinytext NOT NULL,
		  `link` tinytext NOT NULL,
		  `hits` int(11) unsigned NOT NULL default '0',
		  `ord` smallint(4) unsigned NOT NULL default '0',
		  `active` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `active` (`active`)
		) ENGINE=MyISAM;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('affiliates', 'orderby', 'select', 'a:4:{i:1;s:12:\"{ORDERADMIN}\";i:2;s:16:\"{ORDERHITS_DESC}\";i:3;s:15:\"{ORDERHITS_ASC}\";i:4;s:13:\"{ORDERRANDOM}\";}', '4', '', 1129897415, 1000);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
	
	//Ordner für Bilder
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$mm=new mediamanager;
	$mm->createdir('affiliates');
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_affiliates`;
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //Zu 1.1.0
			
			//Indizes entfernen
			clearIndices(PRE.'_affiliates');
			
			//config Update
			updateConfig('affiliates', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('affiliates', 'orderby', 'select', 'a:4:{i:1;s:12:\"{ORDERADMIN}\";i:2;s:16:\"{ORDERHITS_DESC}\";i:3;s:15:\"{ORDERHITS_ASC}\";i:4;s:13:\"{ORDERRANDOM}\";}', '4', '', 1129897415, 1000);
			");
			
			$mysql="
				ALTER TABLE `apx_affiliates` ADD INDEX ( `active` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
	}
}

?>