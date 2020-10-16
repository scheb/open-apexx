<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Installieren
if (SETUPMODE == 'install') {
    $since = mktime(0, 0, 0, date('m'), date('j'), date('Y'));
    $mysql = "
		INSERT INTO `apx_cron` VALUES('twitter', 'twitter', 300, ".$since.", '');
		
		INSERT INTO `apx_config` VALUES
		('twitter', 'oauth_token', 'string', '', '', 'ACCOUNT', '0', '1000'),
		('twitter', 'oauth_secret', 'string', '', '', 'ACCOUNT', '0', '2000'),
		('twitter', 'news', 'switch', '', '1', 'CONTENT', 0, 1000),
		('twitter', 'articles', 'switch', '', '1', 'CONTENT', 0, 2000),
		('twitter', 'videos', 'switch', '', '1', 'CONTENT', 0, 3000),
		('twitter', 'downloads', 'switch', '', '1', 'CONTENT', 0, 4000),
		('twitter', 'gallery', 'switch', '', '1', 'CONTENT', 0, 5000),
		('twitter', 'links', 'switch', '', '1', 'CONTENT', 0, 6000),
		('twitter', 'glossar', 'switch', '', '1', 'CONTENT', 0, 7000),
		('twitter', 'events', 'switch', '', '1', 'CONTENT', 0, 8000),
		('twitter', 'forum', 'switch', '', '1', 'CONTENT', 0, 9000),
		('twitter', 'poll', 'switch', '', '1', 'CONTENT', 0, 10000),
		('twitter', 'user_blog', 'switch', '', '1', 'CONTENT', 0, 11000),
		('twitter', 'user_gallery', 'switch', '', '1', 'CONTENT', 0, 12000),
		('twitter', 'tpl_news', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 1000),
		('twitter', 'tpl_articles', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 2000),
		('twitter', 'tpl_videos', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 3000),
		('twitter', 'tpl_downloads', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 4000),
		('twitter', 'tpl_gallery', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 5000),
		('twitter', 'tpl_links', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 6000),
		('twitter', 'tpl_glossar', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 7000),
		('twitter', 'tpl_events', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 8000),
		('twitter', 'tpl_forum', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 9000),
		('twitter', 'tpl_poll', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 10000),
		('twitter', 'tpl_user_blog', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 11000),
		('twitter', 'tpl_user_gallery', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 12000);
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }
}

//Deinstallieren
elseif (SETUPMODE == 'uninstall') {
    $mysql = "
		DELETE FROM `apx_cron` WHERE module='twitter';
	";
    $queries = split_sql($mysql);
    foreach ($queries as $query) {
        $db->query($query);
    }
}

//Update
elseif (SETUPMODE == 'update') {
    switch ($installed_version) {
        case 110: //Zu 1.1.1
            $mysql = "
			INSERT INTO `apx_config` VALUES
			('twitter', 'tpl_news', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 1000),
			('twitter', 'tpl_articles', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 2000),
			('twitter', 'tpl_videos', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 3000),
			('twitter', 'tpl_downloads', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 4000),
			('twitter', 'tpl_gallery', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 5000),
			('twitter', 'tpl_links', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 6000),
			('twitter', 'tpl_glossar', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 7000),
			('twitter', 'tpl_events', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 8000),
			('twitter', 'tpl_forum', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 9000),
			('twitter', 'tpl_poll', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 10000),
			('twitter', 'tpl_user_blog', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 11000),
			('twitter', 'tpl_user_gallery', 'string', '', '{TITLE}: {LINK}', 'FORMAT', 0, 12000);
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }

            // no break
        case 111: //Zu 1.1.2
            $mysql = "
			DELETE FROM `apx_config` WHERE module='twitter' AND varname='acc_pwd';
			DELETE FROM `apx_config` WHERE module='twitter' AND varname='acc_user';
			
			INSERT INTO `apx_config` VALUES
			('twitter', 'oauth_token', 'string', '', '', 'ACCOUNT', '0', '1000'),
			('twitter', 'oauth_secret', 'string', '', '', 'ACCOUNT', '0', '2000');
			";
            $queries = split_sql($mysql);
            foreach ($queries as $query) {
                $db->query($query);
            }
    }
}
