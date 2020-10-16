<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $mysql = "
		CREATE TABLE `apx_inlinescreens` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `module` varchar(50) NOT NULL default '',
		  `mid` int(11) unsigned NOT NULL default '0',
		  `hash` tinytext NOT NULL,
		  `picture` tinytext NOT NULL,
		  `popup` tinytext NOT NULL,
		  `align` enum('','left','right') NOT NULL default '',
		  `text` text NOT NULL,
		  `addtime` int(11) unsigned NOT NULL default '0',
		  PRIMARY KEY  (`id`),
		  KEY `module` (`module`,`mid`)
		) ENGINE=MyISAM ;
		
		CREATE TABLE `apx_mediarules` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `extension` varchar(10) NOT NULL default '',
		  `name` tinytext NOT NULL,
		  `special` enum('','undel','block') NOT NULL default '',
		  PRIMARY KEY  (`id`)
		) ENGINE=MyISAM ;
		
		INSERT INTO `apx_mediarules` VALUES (1, 'ACE', 'WinACE Archiv', '');
		INSERT INTO `apx_mediarules` VALUES (2, 'BMP', 'Bitmap', '');
		INSERT INTO `apx_mediarules` VALUES (3, 'DOC', 'Word Dokument', '');
		INSERT INTO `apx_mediarules` VALUES (4, 'EXE', 'Anwendung', '');
		INSERT INTO `apx_mediarules` VALUES (5, 'GIF', 'Compuserve GIF', '');
		INSERT INTO `apx_mediarules` VALUES (6, 'HTM', 'Hypertext Dokument', '');
		INSERT INTO `apx_mediarules` VALUES (7, 'HTML', 'Hypertext Dokument', '');
		INSERT INTO `apx_mediarules` VALUES (8, 'JPEG', 'JPEG Datei', '');
		INSERT INTO `apx_mediarules` VALUES (9, 'JPE', 'JPEG Datei', '');
		INSERT INTO `apx_mediarules` VALUES (10, 'JPG', 'JPEG Datei', '');
		INSERT INTO `apx_mediarules` VALUES (11, 'PDF', 'Adobe Acrobat PDF', '');
		INSERT INTO `apx_mediarules` VALUES (12, 'PHP', 'PHP Datei', 'block');
		INSERT INTO `apx_mediarules` VALUES (13, 'PHP3', 'PHP Datei', 'block');
		INSERT INTO `apx_mediarules` VALUES (14, 'PHP4', 'PHP Datei', 'block');
		INSERT INTO `apx_mediarules` VALUES (15, 'PHP5', 'PHP Datei', 'block');
		INSERT INTO `apx_mediarules` VALUES (16, 'RAR', 'WinRAR Archiv', '');
		INSERT INTO `apx_mediarules` VALUES (17, 'TIF', 'TIFF Image', '');
		INSERT INTO `apx_mediarules` VALUES (18, 'TIFF', 'TIFF Image', '');
		INSERT INTO `apx_mediarules` VALUES (19, 'TXT', 'Textdatei', '');
		INSERT INTO `apx_mediarules` VALUES (20, 'ZIP', 'WinZIP Archiv', '');
		INSERT INTO `apx_mediarules` VALUES (21, 'PNG', 'PNG Image', '');
		
		INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
		('mediamanager', 'watermark', 'string', '', '', '', 1111936901, 1000),
		('mediamanager', 'watermark_transp', 'int', '', '50', '', 1111936901, 2000),
		('mediamanager', 'watermark_position', 'select', 'a:9:{i:1;s:18:\"{POSTOP} {POSLEFT}\";i:2;s:20:\"{POSTOP} {POSCENTER}\";i:3;s:19:\"{POSTOP} {POSRIGHT}\";i:4;s:21:\"{POSMIDDLE} {POSLEFT}\";i:5;s:23:\"{POSMIDDLE} {POSCENTER}\";i:6;s:22:\"{POSMIDDLE} {POSRIGHT}\";i:7;s:21:\"{POSBOTTOM} {POSLEFT}\";i:8;s:23:\"{POSBOTTOM} {POSCENTER}\";i:9;s:22:\"{POSBOTTOM} {POSRIGHT}\";}', '9', '', 1111936901, 3000),
		('mediamanager', 'quality_resize', 'switch', '', '1', '', 1111936901, 4000);
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }

    //Ordner für Inline-Screens
    require_once BASEDIR.'lib/class.mediamanager.php';
    $mm = new mediamanager();
    $mm->createdir('inline');
}

//Update
elseif (SETUPMODE == 'update') {
    switch ($installed_version) {
        case 100: //zu 1.0.1
            $mysql = "
				CREATE TABLE `apx_inlinescreens` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `module` tinytext NOT NULL,
				  `mid` int(11) unsigned NOT NULL default '0',
				  `hash` tinytext NOT NULL,
				  `picture` tinytext NOT NULL,
				  `popup` tinytext NOT NULL,
				  `text` text NOT NULL,
				  `addtime` int(11) unsigned NOT NULL default '0',
				  PRIMARY KEY  (`id`)
				) ENGINE=MyISAM;
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            //Ordner für Inline-Screens
            require_once BASEDIR.'lib/class.mediamanager.php';
            $mm = new mediamanager();
            $mm->createdir('inline');

            // no break
        case 101: //zu 1.0.2
            $mysql = "
				ALTER TABLE `apx_inlinescreens` ADD `align` ENUM( '','left', 'right') NOT NULL AFTER `popup` ;
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 102: //zu 1.1.0

            //Indizes entfernen
            clearIndices(PRE.'_inlinescreens');

            //config Update
            updateConfig('mediamanager', "
				INSERT INTO `apx_config` (`module`, `varname`, `type`, `addnl`, `value`, `tab`, `lastchange`, `ord`) VALUES
				('mediamanager', 'watermark', 'string', '', '', '', 1111936901, 1000),
				('mediamanager', 'watermark_transp', 'int', '', '50', '', 1111936901, 2000),
				('mediamanager', 'watermark_position', 'select', 'a:9:{i:1;s:18:\"{POSTOP} {POSLEFT}\";i:2;s:20:\"{POSTOP} {POSCENTER}\";i:3;s:19:\"{POSTOP} {POSRIGHT}\";i:4;s:21:\"{POSMIDDLE} {POSLEFT}\";i:5;s:23:\"{POSMIDDLE} {POSCENTER}\";i:6;s:22:\"{POSMIDDLE} {POSRIGHT}\";i:7;s:21:\"{POSBOTTOM} {POSLEFT}\";i:8;s:23:\"{POSBOTTOM} {POSCENTER}\";i:9;s:22:\"{POSBOTTOM} {POSRIGHT}\";}', '9', '', 1111936901, 3000),
				('mediamanager', 'quality_resize', 'switch', '', '1', '', 1111936901, 4000);
			");

            $mysql = '
				ALTER TABLE `apx_inlinescreens` CHANGE `module` `module` VARCHAR( 50 ) NOT NULL; 
				ALTER TABLE `apx_inlinescreens` ADD INDEX ( `module` , `mid` ) ;
			';
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }
    }
}
