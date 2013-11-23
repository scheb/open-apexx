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
		CREATE TABLE `apx_products` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `prodid` int(11) unsigned NOT NULL,
		  `type` enum('normal','game','music','movie','book','software','hardware') NOT NULL default 'normal',
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `picture` tinytext NOT NULL,
		  `teaserpic` TINYTEXT NOT NULL,
		  `website` tinytext NOT NULL,
		  `meta_description` text NOT NULL,
		  `regisseur` tinytext NOT NULL,
		  `actors` tinytext NOT NULL,
		  `manufacturer` int(11) unsigned NOT NULL default '0',
		  `publisher` int(11) unsigned NOT NULL default '0',
		  `genre` int(11) NOT NULL default '0',
		  `systems` tinytext NOT NULL,
		  `sk` enum('','all','none','6','12','16','18') NOT NULL default '',
		  `requirements` text NOT NULL,
		  `equipment` text NOT NULL,
		  `os` tinytext NOT NULL,
		  `languages` tinytext NOT NULL,
		  `license` enum('freeware','shareware','commercial') NOT NULL,
		  `version` tinytext NOT NULL,
		  `isbn` tinytext NOT NULL,
		  `media` tinytext NOT NULL,
		  `length` varchar(50) NOT NULL default '',
		  `custom1` tinytext NOT NULL,
		  `custom2` tinytext NOT NULL,
		  `custom3` tinytext NOT NULL,
		  `custom4` tinytext NOT NULL,
		  `custom5` tinytext NOT NULL,
		  `custom6` tinytext NOT NULL,
		  `custom7` tinytext NOT NULL,
		  `custom8` tinytext NOT NULL,
		  `custom9` tinytext NOT NULL,
		  `custom10` tinytext NOT NULL,
		  `buylink` tinytext NOT NULL,
		  `price` varchar(20) NOT NULL default '0',
		  `recprice` tinytext NOT NULL,
		  `guarantee` tinytext NOT NULL,
		  `addtime` int(11) unsigned NOT NULL default '0',
		  `hits` int(11) unsigned NOT NULL,
		  `active` tinyint(1) unsigned NOT NULL,
		  `allowcoms` tinyint(1) unsigned NOT NULL default '1',
		  `allowrating` tinyint(1) unsigned NOT NULL default '1',
		  `restricted` tinyint(1) unsigned NOT NULL,
		  `top` TINYINT(1) UNSIGNED NOT NULL,
		  `searchable` tinyint(1) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `type` (`type`),
		  KEY `active` (`active`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_products_coll` (
			`userid` INT( 10 ) UNSIGNED NOT NULL ,
			`prodid` INT( 10 ) UNSIGNED NOT NULL ,
			PRIMARY KEY ( `userid` , `prodid` )
		) ENGINE = MYISAM ;
		
		CREATE TABLE `apx_products_groups` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `grouptype` enum('medium','system','genre') NOT NULL default 'medium',
		  `type` enum('normal','game','music','movie','book','software','hardware') NOT NULL default 'game',
		  `title` tinytext NOT NULL,
		  `icon` tinytext NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `grouptype` (`grouptype`),
		  KEY `type` (`type`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_products_releases` (
		  `ord` int(11) unsigned NOT NULL auto_increment,
		  `prodid` int(11) unsigned NOT NULL default '0',
		  `system` int(11) unsigned NOT NULL default '0',
		  `data` text NOT NULL,
		  `stamp` int(8) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`ord`),
		  KEY `prodid` (`prodid`),
		  KEY `stamp` (`stamp`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_products_tags` (
		  `id` int(11) unsigned NOT NULL,
		  `tagid` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`,`tagid`)
		) ENGINE=MyISAM;
		
		CREATE TABLE `apx_products_units` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `type` enum('company','person') NOT NULL default 'company',
		  `title` tinytext NOT NULL,
		  `text` text NOT NULL,
		  `meta_description` text NOT NULL,
		  `fullname` tinytext NOT NULL,
		  `picture` tinytext NOT NULL,
		  `address` tinytext NOT NULL,
		  `email` tinytext NOT NULL,
		  `phone` tinytext NOT NULL,
		  `website` tinytext NOT NULL,
		  `founder` tinytext NOT NULL,
		  `founding_year` tinytext NOT NULL,
		  `founding_country` tinytext NOT NULL,
		  `legalform` tinytext NOT NULL,
		  `headquaters` tinytext NOT NULL,
		  `executive` tinytext NOT NULL,
		  `employees` tinytext NOT NULL,
		  `turnover` tinytext NOT NULL,
		  `sector` tinytext NOT NULL,
		  `products` tinytext NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `type` (`type`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('products', 'epp', 'int', '', '20', 'VIEW', 1220200389, 1000),
		('products', 'manu_epp', 'int', '', '20', 'VIEW', 1220200389, 2000),
		('products', 'manu_searchepp', 'int', '', '20', 'VIEW', '0', '2500'),
		('products', 'manuprod_epp', 'int', '', '20', 'VIEW', 1220200389, 3000),
		('products', 'relepp', 'int', '', '20', 'VIEW', 1220200389, 4000),
		('products', 'searchepp', 'int', '', '20', 'VIEW', 1220200389, 4500),
		('products', 'sortby', 'select', 'a:2:{i:1;s:7:\"{TITLE}\";i:0;s:9:\"{RELEASE}\";}', '1', 'VIEW', 1220200389, 5000),
		
		('products', 'searchable', 'switch', '', '1', 'OPTIONS', 1220200389, 1000),
		('products', 'coms', 'switch', '', '1', 'OPTIONS', 1220200390, 2000),
		('products', 'ratings', 'switch', '', '1', 'OPTIONS', 1220200390, 3000),
		('products', 'filtermanu', 'switch', '', '1', 'OPTIONS', 1220200390, 4000),
		('products', 'collection', 'switch', '', '1', 'OPTIONS', '0', '5000'),
		
		('products', 'custom_normal', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 1000),
		('products', 'custom_game', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 2000),
		('products', 'custom_software', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 3000),
		('products', 'custom_hardware', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 4000),
		('products', 'custom_music', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 5000),
		('products', 'custom_movie', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 6000),
		('products', 'custom_book', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 7000),
		
		('products', 'pic_width', 'int', '', '120', 'IMAGES', 1220200390, 1000),
		('products', 'pic_height', 'int', '', '120', 'IMAGES', 1220200390, 2000),
		('products', 'pic_popup', 'switch', '', '1', 'IMAGES', 1220200390, 3000),
		('products', 'pic_popup_width', 'int', '', '640', 'IMAGES', 1220200390, 4000),
		('products', 'pic_popup_height', 'int', '', '480', 'IMAGES', 1220200390, 5000),
		('products', 'pic_quality', 'switch', '', '1', 'IMAGES', 1220200390, 6000);
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
	
	//Products-DIR
	require_once(BASEDIR.'lib/class.mediamanager.php');
	$mm=new mediamanager;
	$mm->createdir('products');
}


//Deinstallieren
elseif ( SETUPMODE=='uninstall' ) {
	$mysql="
		DROP TABLE `apx_products`;
		DROP TABLE `apx_products_groups`;
		DROP TABLE `apx_products_releases`;
		DROP TABLE `apx_products_tags`;
		DROP TABLE `apx_products_units`;
	";
	$queries=split_sql($mysql);
	foreach ( $queries AS $query ) $db->query($query);
}


//Update
elseif ( SETUPMODE=='update' ) {
	switch ( $installed_version ) {
		
		case 100: //zu 1.0.1
			$mysql="
				ALTER TABLE `apx_products` ADD `custom1` TINYTEXT NOT NULL AFTER `length` ,ADD `custom2` TINYTEXT NOT NULL AFTER `custom1` ,ADD `custom3` TINYTEXT NOT NULL AFTER `custom2` ,ADD `custom4` TINYTEXT NOT NULL AFTER `custom3` ,ADD `custom5` TINYTEXT NOT NULL AFTER `custom4` ,ADD `custom6` TINYTEXT NOT NULL AFTER `custom5` ,ADD `custom7` TINYTEXT NOT NULL AFTER `custom6` ,ADD `custom8` TINYTEXT NOT NULL AFTER `custom7` ,ADD `custom9` TINYTEXT NOT NULL AFTER `custom8` ,ADD `custom10` TINYTEXT NOT NULL AFTER `custom9` ;
				INSERT INTO `apx_config` VALUES ('products', 'custom_normal', 'array', '', 'a:0:{}', '0', '820');
				INSERT INTO `apx_config` VALUES ('products', 'custom_game', 'array', '', 'a:0:{}', '0', '830');
				INSERT INTO `apx_config` VALUES ('products', 'custom_music', 'array', '', 'a:0:{}', '0', '840');
				INSERT INTO `apx_config` VALUES ('products', 'custom_movie', 'array', '', 'a:0:{}', '0', '850');
				INSERT INTO `apx_config` VALUES ('products', 'custom_book', 'array', '', 'a:0:{}', '0', '860');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 101: //zu 1.0.2
			$mysql="
				ALTER TABLE `apx_products` ADD `keywords` TINYTEXT NOT NULL AFTER `picture` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 102: //zu 1.0.3
			$mysql="
				ALTER TABLE `apx_products` ADD `active` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `addtime` ;
				ALTER TABLE `apx_products` ADD INDEX ( `active` ) ;
				UPDATE `apx_products` SET active =1;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 103: //zu 1.0.4
			$mysql="
				INSERT INTO `apx_config` VALUES ('products', 'sortby', 'select', 'a:2:{i:1;s:7:\"{TITLE}\";i:0;s:9:\"{RELEASE}\";}', '1', '0', '235');
				INSERT INTO `apx_config` VALUES ('products', 'manu_epp', 'int', '', '20', '0', '265');
				INSERT INTO `apx_config` VALUES ('products', 'custom_software', 'array', '', 'a:0:{}', '0', '833');
				INSERT INTO `apx_config` VALUES ('products', 'custom_hardware', 'array', '', 'a:0:{}', '0', '836');
				ALTER TABLE `apx_products` ADD `recprice` TINYTEXT NOT NULL AFTER `price` , ADD `guarantee` TINYTEXT NOT NULL AFTER `recprice` ;
				ALTER TABLE `apx_products` ADD `prodid` INT( 11 ) UNSIGNED NOT NULL AFTER `id` ;
				ALTER TABLE `apx_products` ADD `requirements` TEXT NOT NULL AFTER `sk` ;
				ALTER TABLE `apx_products` ADD `equipment` TEXT NOT NULL AFTER `requirements` ;
				ALTER TABLE `apx_products` ADD `os` TINYTEXT NOT NULL AFTER `equipment` ,ADD `languages` TINYTEXT NOT NULL AFTER `os` ,ADD `license` ENUM( 'freeware', 'shareware', 'commercial' ) NOT NULL AFTER `languages` ,ADD `version` TINYTEXT NOT NULL AFTER `license` ;
				ALTER TABLE `apx_products` CHANGE `type` `type` ENUM( 'normal', 'game', 'music', 'movie', 'book', 'software', 'hardware' ) NOT NULL DEFAULT 'normal' ;
				ALTER TABLE `apx_products_units` ADD `text` TEXT NOT NULL AFTER `title` ;
				ALTER TABLE `apx_products_units` CHANGE `type` `type` VARCHAR( 255 ) NOT NULL DEFAULT 'game' ;
				UPDATE `apx_products_units` SET type='person' WHERE type IN ('music', 'book');
				UPDATE `apx_products_units` SET type='company' WHERE type IN ('normal', 'game', 'movie');
				ALTER TABLE `apx_products_units` CHANGE `type` `type` ENUM( 'company', 'person' ) NOT NULL DEFAULT 'company';
				ALTER TABLE `apx_products_units` ADD `fullname` TINYTEXT NOT NULL AFTER `title` ,ADD `picture` TINYTEXT NOT NULL AFTER `fullname` ,ADD `address` TINYTEXT NOT NULL AFTER `picture` ,ADD `email` TINYTEXT NOT NULL AFTER `address` ,ADD `phone` TINYTEXT NOT NULL AFTER `email` ;
				ALTER TABLE `apx_products_units` ADD `founder` TINYTEXT NOT NULL AFTER `website` ,ADD `founding_year` TINYTEXT NOT NULL AFTER `founder` ,ADD `founding_country` TINYTEXT NOT NULL AFTER `founding_year` ,ADD `legalform` TINYTEXT NOT NULL AFTER `founding_country` ,ADD `headquaters` TINYTEXT NOT NULL AFTER `legalform` ,ADD `executive` TINYTEXT NOT NULL AFTER `headquaters` ,ADD `employees` TINYTEXT NOT NULL AFTER `executive` ,ADD `turnover` TINYTEXT NOT NULL AFTER `employees` ,ADD `sector` TINYTEXT NOT NULL AFTER `turnover` ,ADD `products` TINYTEXT NOT NULL AFTER `sector` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 104: //zu 1.0.5
			$mysql="
				INSERT INTO `apx_config` VALUES ('products', 'manuprod_epp', 'int', '', '20', '0', '275');
				INSERT INTO `apx_config` VALUES ('products', 'filtermanu', 'switch', '', '1', 0, 1100);
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			//Text-Feld erzeugen, falls nicht vorhanden
			$textfound = false;
			$data = $db->fetch("SHOW COLUMNS FROM ".PRE."_products_units");
			foreach ( $data AS $res ) {
				if ( $res['Field']=='text' ) {
					$textfound = true;
				}
			}
			if ( !$textfound ) {
				$db->query("ALTER TABLE ".PRE."_products_units ADD `text` TEXT NOT NULL AFTER `title`");
			}
		
		
		case 105: //zu 1.0.6
			$mysql="
				ALTER TABLE `apx_products` ADD `hits` INT( 11 ) UNSIGNED NOT NULL AFTER `addtime` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 106: //zu 1.1.0
			
			//Indizes entfernen
			clearIndices(PRE.'_products');
			clearIndices(PRE.'_products_groups');
			clearIndices(PRE.'_products_releases');
			clearIndices(PRE.'_products_units');
			
			//config Update
			updateConfig('products', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('products', 'epp', 'int', '', '20', 'VIEW', 1220200389, 1000),
				('products', 'manu_epp', 'int', '', '20', 'VIEW', 1220200389, 2000),
				('products', 'manu_searchepp', 'int', '', '20', 'VIEW', '0', '2500'),
				('products', 'manuprod_epp', 'int', '', '20', 'VIEW', 1220200389, 3000),
				('products', 'searchepp', 'int', '', '20', 'VIEW', '0', '3500'),
				('products', 'relepp', 'int', '', '20', 'VIEW', 1220200389, 1000),
				('products', 'sortby', 'select', 'a:2:{i:1;s:7:\"{TITLE}\";i:0;s:9:\"{RELEASE}\";}', '1', 'VIEW', 1220200389, 4000),
				
				('products', 'searchable', 'switch', '', '1', 'OPTIONS', 1220200389, 1000),
				('products', 'coms', 'switch', '', '1', 'OPTIONS', 1220200390, 2000),
				('products', 'ratings', 'switch', '', '1', 'OPTIONS', 1220200390, 3000),
				('products', 'filtermanu', 'switch', '', '1', 'OPTIONS', 1220200390, 4000),
				
				('products', 'custom_normal', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 1000),
				('products', 'custom_game', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 2000),
				('products', 'custom_software', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 3000),
				('products', 'custom_hardware', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 4000),
				('products', 'custom_music', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 5000),
				('products', 'custom_movie', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 6000),
				('products', 'custom_book', 'array', '', 'a:0:{}', 'CUSTOM', 1220200390, 7000),
				
				('products', 'pic_width', 'int', '', '120', 'IMAGES', 1220200390, 1000),
				('products', 'pic_height', 'int', '', '120', 'IMAGES', 1220200390, 2000),
				('products', 'pic_popup', 'switch', '', '1', 'IMAGES', 1220200390, 3000),
				('products', 'pic_popup_width', 'int', '', '640', 'IMAGES', 1220200390, 4000),
				('products', 'pic_popup_height', 'int', '', '480', 'IMAGES', 1220200390, 5000),
				('products', 'pic_quality', 'switch', '', '1', 'IMAGES', 1220200390, 6000),
				
				('products', 'teaserpic_width', 'int', '', '120', 'IMAGES', 1220200390, 7000),
				('products', 'teaserpic_height', 'int', '', '120', 'IMAGES', 1220200390, 8000),
				('products', 'teaserpic_popup', 'switch', '', '1', 'IMAGES', 1220200390, 9000),
				('products', 'teaserpic_popup_width', 'int', '', '640', 'IMAGES', 1220200390, 10000),
				('products', 'teaserpic_popup_height', 'int', '', '480', 'IMAGES', 1220200390, 11000),
				('products', 'teaserpic_quality', 'switch', '', '1', 'IMAGES', 1220200390, 12000);
			");
			
			$mysql="
				CREATE TABLE `apx_products_tags` (
					`id` INT( 11 ) UNSIGNED NOT NULL ,
					`tagid` INT( 11 ) UNSIGNED NOT NULL ,
					PRIMARY KEY ( `id` , `tagid` )
				) ENGINE=MyISAM;
				
				ALTER TABLE `apx_products_groups` CHANGE `type` `type` ENUM( 'normal', 'game', 'music', 'movie', 'book', 'software', 'hardware' ) NOT NULL DEFAULT 'game' ;
				ALTER TABLE `apx_products` CHANGE `media` `media` TINYTEXT NOT NULL ;
				
				ALTER TABLE `apx_products` ADD INDEX ( `type` ) ;
				ALTER TABLE `apx_products` ADD INDEX ( `active` ) ;
				ALTER TABLE `apx_products_groups` ADD INDEX ( `grouptype` ) ;
				ALTER TABLE `apx_products_groups` ADD INDEX ( `type` ) ;
				ALTER TABLE `apx_products_releases` ADD INDEX ( `prodid` ) ;
				ALTER TABLE `apx_products_releases` ADD INDEX ( `stamp` ) ;
				ALTER TABLE `apx_products_units` ADD INDEX ( `type` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			//Media anpassen
			$data = $db->fetch("SELECT id, type, media, systems FROM ".PRE."_products");
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					$db->query("UPDATE ".PRE."_products SET media='|".$res['media']."|', systems='".dash_serialize(unserialize($res['media']))."' WHERE id='".$res['id']."' LIMIT 1");
					if ( in_array($res['type'], array('software', 'book', 'music')) ) {
						$db->query("UPDATE ".PRE."_products_releases SET system='".$res['media']."' WHERE prodid='".$res['id']."'");
					}
				}
			}
			
			//Tags erzeugen
			transformKeywords(PRE.'_products', PRE.'_products_tags');
			
		
		case 110: //zu 1.1.1
			
			//Indizes entfernen
			clearIndices(PRE.'_products');
			clearIndices(PRE.'_products_groups');
			clearIndices(PRE.'_products_releases');
			clearIndices(PRE.'_products_units');
			
			$mysql="
				ALTER TABLE `apx_products` ADD `restricted` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `allowrating` ;
				
				ALTER TABLE `apx_products` ADD INDEX ( `type` ) ;
				ALTER TABLE `apx_products` ADD INDEX ( `active` ) ;
				ALTER TABLE `apx_products_groups` ADD INDEX ( `grouptype` ) ;
				ALTER TABLE `apx_products_groups` ADD INDEX ( `type` ) ;
				ALTER TABLE `apx_products_releases` ADD INDEX ( `prodid` ) ;
				ALTER TABLE `apx_products_releases` ADD INDEX ( `stamp` ) ;
				ALTER TABLE `apx_products_units` ADD INDEX ( `type` ) ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 111: //zu 1.1.2
			
			$mysql="
				INSERT INTO `apx_config` VALUES ('products', 'manu_searchepp', 'int', '', '20', 'VIEW', '0', '2500');
				INSERT INTO `apx_config` VALUES ('products', 'searchepp', 'int', '', '20', 'VIEW', '0', '3500');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			
		case 112: //zu 1.1.3
			require_once(dirname(__FILE__).'/setup_funcs.php');
			
			$data = $db->fetch("
				SELECT ord, data
				FROM ".PRE."_products_releases
			");
			foreach ( $data AS $res ) {
				list($trash, $stamp) = generate_release(unserialize($res['data']));
				$db->query("
					UPDATE ".PRE."_products_releases
					SET stamp='".$stamp."'
					WHERE ord='".$res['ord']."'
				");
			}
			
			$mysql="
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
	
	
		case 113: //zu 1.1.4
			
			$mysql="
				ALTER TABLE `apx_products` ADD `meta_description` TEXT NOT NULL AFTER `website` ;
				ALTER TABLE `apx_products_units` ADD `meta_description` TEXT NOT NULL AFTER `text` ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
	
		case 114: //zu 1.1.5
			
			$mysql="
				INSERT INTO `apx_config` VALUES ('products', 'collection', 'switch', '', '1', 'OPTIONS', '0', '5000');
				
				CREATE TABLE `apx_products_coll` (
					`userid` INT( 10 ) UNSIGNED NOT NULL ,
					`prodid` INT( 10 ) UNSIGNED NOT NULL ,
					PRIMARY KEY ( `userid` , `prodid` )
				) ENGINE = MYISAM ;
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 115: //zu 1.1.6
			
			$mysql="
				ALTER TABLE `apx_products` CHANGE `sk` `sk` ENUM( '', 'all', 'none', '6', '12', '16', '18' ) NOT NULL DEFAULT '';
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		
		case 116: //zu 1.1.7
			
			//Option hinzufügen, falls Neuinstallation und die Option nicht eingefügt wurde
			$mysql="
				INSERT IGNORE INTO `apx_config` VALUES ('products', 'collection', 'switch', '', '1', 'OPTIONS', '0', '5000');
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
		
		case 117: //zu 1.1.8
			
			$mysql="
				ALTER TABLE `apx_products` ADD `teaserpic` TINYTEXT NOT NULL AFTER `picture` ;
				ALTER TABLE `apx_products` ADD `top` TINYINT( 1 ) UNSIGNED NOT NULL AFTER `restricted` ;
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('products', 'teaserpic_width', 'int', '', '120', 'IMAGES', 1220200390, 7000),
				('products', 'teaserpic_height', 'int', '', '120', 'IMAGES', 1220200390, 8000),
				('products', 'teaserpic_popup', 'switch', '', '1', 'IMAGES', 1220200390, 9000),
				('products', 'teaserpic_popup_width', 'int', '', '640', 'IMAGES', 1220200390, 10000),
				('products', 'teaserpic_popup_height', 'int', '', '480', 'IMAGES', 1220200390, 11000),
				('products', 'teaserpic_quality', 'switch', '', '1', 'IMAGES', 1220200390, 12000);
			";
			$queries=split_sql($mysql);
			foreach ( $queries AS $query ) $db->query($query);
			
			
			
			
	}
}

?>