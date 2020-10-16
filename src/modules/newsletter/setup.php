<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Installieren
if ( SETUPMODE=='install' ) {
	$mysql="
		CREATE TABLE `apx_newsletter` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `subject` tinytext NOT NULL,
		  `catid` int(11) unsigned NOT NULL DEFAULT '0',
		  `text` mediumtext NOT NULL,
		  `text_html` mediumtext NOT NULL,
		  `addtime` int(11) unsigned NOT NULL DEFAULT '0',
		  `sendtime` int(11) unsigned NOT NULL DEFAULT '0',
		  `addsig` tinyint(1) unsigned NOT NULL DEFAULT '0',
		  `done` tinyint(1) unsigned NOT NULL DEFAULT '0',
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_newsletter_emails` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `email` tinytext NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_newsletter_emails_cat` (
		  `eid` int(10) unsigned NOT NULL,
		  `catid` int(10) unsigned NOT NULL,
		  `active` tinyint(1) unsigned NOT NULL,
		  `html` tinyint(1) unsigned NOT NULL,
		  `incode` varchar(10) NOT NULL,
		  `outcode` varchar(10) NOT NULL,
		  PRIMARY KEY (`eid`,`catid`),
		  KEY `active` (`active`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('newsletter', 'regcode', 'switch', '', '1', '', 1206302365, 1000),
		('newsletter', 'sig_text', 'string', 'MULTILINE', '', '', 1206302365, 2000),
		('newsletter', 'sig_html', 'string', 'MULTILINE', '', '', 1206302365, 3000),
		('newsletter', 'categories', 'array', 'BLOCK', 'a:0:{}', '', 0, 4000);
	";
	
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_newsletter`;
		DROP TABLE `apx_newsletter_emails`;
	";
	
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				ALTER TABLE `apx_newsletter` ADD `catid` INT( 11 ) UNSIGNED NOT NULL AFTER `subject` ;
				ALTER TABLE `apx_newsletter` ADD `text_html` TEXT NOT NULL AFTER `text` ;
				ALTER TABLE `apx_newsletter_emails` ADD `catids` TINYTEXT NOT NULL AFTER `regcode` ;
				UPDATE `apx_newsletter` SET text_html=text;
				UPDATE `apx_newsletter_emails` SET catids='all';
				INSERT INTO `apx_config` VALUES ('newsletter', 'categories', 'array', 'BLOCK', 'a:0:{}', 0, 2000);
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 101: //zu 1.0.2
			$mysql="
				ALTER TABLE `apx_newsletter` ADD `addsig` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `sendtime` ;
				INSERT INTO `apx_config` VALUES ('newsletter', 'sig_text', 'string', 'MULTILINE', '', '0', '200');
				INSERT INTO `apx_config` VALUES ('newsletter', 'sig_html', 'string', 'MULTILINE', '', '0', '300');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 102: //zu 1.1.0
			
			//config Update
			updateConfig('newsletter', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('newsletter', 'regcode', 'switch', '', '1', '', 1206302365, 1000),
				('newsletter', 'sig_text', 'string', 'MULTILINE', '', '', 1206302365, 2000),
				('newsletter', 'sig_html', 'string', 'MULTILINE', '', '', 1206302365, 3000),
				('newsletter', 'categories', 'array', 'BLOCK', 'a:0:{}', '', 0, 4000);
			");
		
		
		case 110: //zu 1.1.1
			
			$mysql="
				CREATE TABLE `apx_newsletter_emails_cat` (
				  `eid` int(10) unsigned NOT NULL,
				  `catid` int(10) unsigned NOT NULL,
				  `active` tinyint(1) unsigned NOT NULL,
				  `html` tinyint(1) unsigned NOT NULL,
				  `incode` varchar(10) NOT NULL,
				  `outcode` varchar(10) NOT NULL,
				  PRIMARY KEY (`eid`,`catid`),
				  KEY `active` (`active`)
				) ENGINE=MyISAM ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			$catinfo=$set['newsletter']['categories'];
			if ( !is_array($catinfo) ) $catinfo=array();
			$allIds = array_keys($catinfo);
			
			//Catids in eigene Tabelle schreiben
			$data = $db->fetch("
				SELECT id, catids, regcode, html
				FROM ".PRE."_newsletter_emails
			");
			foreach ( $data AS $res ) {
				
				//Kategorie-IDs
				if ( $res['catids']=='all' ) {
					$ids = $allIds;
				}
				else {
					$ids = dash_unserialize($res['catids']);
				}
				
				//Eintragen
				foreach ( $ids AS $id ) {
					$db->query("
						INSERT INTO ".PRE."_newsletter_emails_cat
						(eid, catid, active, html, incode) VALUES
						('".$res['id']."', '".$id."', '".($res['regcode'] ? 0 : 1)."', '".$res['html']."', '".addslashes($res['regcode'])."')
					");
				}
			}
			$db->query("ALTER TABLE ".PRE."_newsletter_emails DROP regcode, DROP catids, DROP html");
			
	}
}

?>