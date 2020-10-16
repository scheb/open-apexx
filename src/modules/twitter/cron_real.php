<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Tiny-URL erzeugen
function createTinyURL($url) {
	$handle = fopen('http://tinyurl.com/api-create.php?url='.urlencode(str_replace('&amp;', '&', $url)), 'r');
	$content = '';
	while ( $data = fread($handle, 1024) ) {
		$content .= $data;
	}
	fclose($handle);
	return $content;
}



//Twitter-Nachricht erzeugen
function postTwitterMessage($twitter, $template, $input) {
	$input['LINK'] = createTinyURL(HTTP_HOST.$input['LINK']);
	$text = $template;
	foreach ( $input AS $search => $value ) {
		$text = str_replace('{'.$search.'}', $value, $text);
	}
	$text = utf8_encode($text);
	try {
		$twitter->post('/statuses/update.json', array('status' => $text));
	}
	catch ( EpiTwitterException $e ) {
		
	}
	catch ( EpiOAuthException $e ) {
		
	}
}



//Sektions-Namen ermitteln
function getTwitterSectionTitle($secid) {
	global $apx;
	if ( $secid=='all' ) {
		return $apx->lang->get('ALL');
	}
	
	$secids = dash_unserialize($secid);
	if ( count($secids)==1 ) {
		return $apx->sections[$secids[0]]['title'];
	}
	else {
		return $apx->lang->get('MULTI');
	}
}



//News auf Twitter posten
function postNewsTwitter($data, &$twitter) {
	global $set, $apx, $db;
	foreach ( $data AS $entry ) {
		
		$tmp=unserialize_section($entry['secid']);
		$entry['link'] = mklink(
			'news.php?id='.$entry['id'],
			'news,id'.$entry['id'].urlformat($entry['title']).'.html',
			iif($set['main']['forcesection'],iif(unserialize_section($entry['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
		);
		
		$sectionTitle = getTwitterSectionTitle($entry['secid']);
		
		$input = array(
			'SECTION' => $sectionTitle,
			'TITLE' => $entry['title'],
			'SUBTITLE' => $entry['subtitle'],
			'CATTITLE' => $entry['cattitle'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_news'], $input);
	}
}



//Artikel auf Twitter posten
function postArticlesTwitter($data, &$twitter) {
	global $set, $apx, $db;
	$apx->lang->drop('search', 'articles');
	
	foreach ( $data AS $entry ) {
		
		//Wohin soll verlinkt werden?
		if ( $entry['type']=='normal' ) $link2file='articles';
		else $link2file=$entry['type'].'s';
		
		$tmp=unserialize_section($entry['secid']);
		$entry['link'] = mklink(
			$link2file.'.php?id='.$entry['id'],
			$link2file.',id'.$entry['id'].',0'.urlformat($entry['title']).'.html',
			iif($set['main']['forcesection'],iif(unserialize_section($entry['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
		);
		
		$sectionTitle = getTwitterSectionTitle($entry['secid']);
		
		$input = array(
			'SECTION' => $sectionTitle,
			'TYPE' => $apx->lang->get('TYPE_'.strtoupper($entry['type'])),
			'TITLE' => $entry['title'],
			'SUBTITLE' => $entry['subtitle'],
			'CATTITLE' => $entry['cattitle'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_articles'], $input);
	}
}



//Video auf Twitter posten
function postVideosTwitter($data, &$twitter) {
	global $set, $apx, $db;
	foreach ( $data AS $entry ) {
		
		$tmp=unserialize_section($entry['secid']);
		$entry['link'] = mklink(
			'videos.php?id='.$entry['id'],
			'videos,id'.$entry['id'].urlformat($entry['title']).'.html',
			iif($set['main']['forcesection'],iif(unserialize_section($entry['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
		);
		
		$sectionTitle = getTwitterSectionTitle($entry['secid']);
		
		$input = array(
			'SECTION' => $sectionTitle,
			'TITLE' => $entry['title'],
			'CATTITLE' => $entry['cattitle'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_videos'], $input);
	}
}



//Download auf Twitter posten
function postDownloadsTwitter($data, &$twitter) {
	global $set, $apx, $db;
	foreach ( $data AS $entry ) {
		
		$tmp=unserialize_section($entry['secid']);
		$entry['link'] = mklink(
			'downloads.php?id='.$entry['id'],
			'downloads,id'.$entry['id'].urlformat($entry['title']).'.html',
			iif($set['main']['forcesection'],iif(unserialize_section($entry['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
		);
		
		$sectionTitle = getTwitterSectionTitle($entry['secid']);
		
		$input = array(
			'SECTION' => $sectionTitle,
			'TITLE' => $entry['title'],
			'CATTITLE' => $entry['cattitle'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_downloads'], $input);
	}
}



//Galerie auf Twitter posten
function postGalleryTwitter($data, &$twitter) {
	global $set, $apx, $db;
	foreach ( $data AS $entry ) {
		
		$tmp=unserialize_section($entry['secid']);
		$entry['link'] = mklink(
			'gallery.php?id='.$entry['id'],
			'gallery,list'.$entry['id'].',1'.urlformat($entry['title']).'.html',
			iif($set['main']['forcesection'],iif(unserialize_section($entry['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
		);
		
		$sectionTitle = getTwitterSectionTitle($entry['secid']);
		
		$input = array(
			'SECTION' => $sectionTitle,
			'TITLE' => $entry['title'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_gallery'], $input);
	}
}



//Link auf Twitter posten
function postLinksTwitter($data, &$twitter) {
	global $set, $apx, $db;
	foreach ( $data AS $entry ) {
		
		$tmp=unserialize_section($entry['secid']);
		$entry['link'] = mklink(
			'links.php?id='.$entry['id'],
			'links,id'.$entry['id'].urlformat($entry['title']).'.html',
			iif($set['main']['forcesection'],iif(unserialize_section($entry['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
		);
		
		$sectionTitle = getTwitterSectionTitle($entry['secid']);
		
		$input = array(
			'SECTION' => $sectionTitle,
			'TITLE' => $entry['title'],
			'CATTITLE' => $entry['cattitle'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_links'], $input);
	}
}



//Glossar auf Twitter posten
function postGlossarTwitter($data, &$twitter) {
	global $set, $apx, $db;
	foreach ( $data AS $entry ) {
		
		$entry['link'] = mklink(
			'glossar.php?id='.$entry['id'],
			'glossar,id'.$entry['id'].urlformat($entry['title']).'.html'
		);
		
		$input = array(
			'TITLE' => $entry['title'],
			'CATTITLE' => $entry['cattitle'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_glossar'], $input);
	}
}



//Events auf Twitter posten
function postEventsTwitter($data, &$twitter) {
	global $set, $apx, $db;
	foreach ( $data AS $entry ) {
		
		$tmp=unserialize_section($entry['secid']);
		$entry['link'] = mklink(
			'events.php?id='.$entry['id'],
			'events,id'.$entry['id'].urlformat($entry['title']).'.html',
			iif($set['main']['forcesection'],iif(unserialize_section($entry['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
		);
		
		$sectionTitle = getTwitterSectionTitle($entry['secid']);
		
		$input = array(
			'SECTION' => $sectionTitle,
			'TITLE' => $entry['title'],
			'CATTITLE' => $entry['cattitle'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_events'], $input);
	}
}



//Forum-Thema auf Twitter posten
function postForumTwitter($data, &$twitter) {
	global $set, $apx, $db;
	foreach ( $data AS $entry ) {
		
		$forumdir=$set['forum']['directory'].'/';
		$entry['link']=mklink(
			$forumdir.'thread.php?id='.$entry['threadid'],
			$forumdir.'thread,'.$entry['threadid'].',1'.urlformat($entry['title']).'.html'
		);
		
		$input = array(
			'TITLE' => $entry['title'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_forum'], $input);
	}
}



//Umfrage auf Twitter posten
function postPollTwitter($data, &$twitter) {
	global $set, $apx, $db;
	foreach ( $data AS $entry ) {
		
		$tmp=unserialize_section($entry['secid']);
		$entry['link'] = mklink(
			'poll.php?id='.$entry['id'],
			'poll,'.$entry['id'].urlformat($entry['title']).'.html',
			iif($set['main']['forcesection'],iif(unserialize_section($entry['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
		);
		
		$sectionTitle = getTwitterSectionTitle($entry['secid']);
		
		$input = array(
			'SECTION' => $sectionTitle,
			'TITLE' => $entry['title'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_poll'], $input);
	}
}



//User-Blog auf Twitter posten
function postUserBlogTwitter($data, &$twitter) {
	global $set, $apx, $db;
	foreach ( $data AS $entry ) {
		
		$entry['link'] = mklink(
			'user.php?action=blog&amp;id='.$entry['userid'].'&amp;blogid='.$entry['id'],
			'user,blog,'.$entry['userid'].',id'.$entry['id'].urlformat($entry['title']).'.html'
		);
		
		$input = array(
			'TITLE' => $entry['title'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_user_blog'], $input);
	}
}



//User-Galerie auf Twitter posten
function postUserGalleryTwitter($data, &$twitter) {
	global $set, $apx, $db;
	foreach ( $data AS $entry ) {
		
		$entry['link'] = mklink(
			'user.php?action=gallery&amp;id='.$entry['owner'].'&amp;galid='.$entry['id'],
			'user,gallery,'.$entry['owner'].','.$entry['id'].',0.html'
		);
		
		$input = array(
			'TITLE' => $entry['title'],
			'LINK' => $entry['link']
		);
		
		postTwitterMessage($twitter, $set['twitter']['tpl_user_gallery'], $input);
	}
}



//Twitter-Objekt erzeugen
function twitter_connect() {
	global $set,$db,$apx;
	
	require(BASEDIR.getmodulepath('twitter').'epitwitter/class.epicurl.php');
	require(BASEDIR.getmodulepath('twitter').'epitwitter/class.epioauth.php');
	require(BASEDIR.getmodulepath('twitter').'epitwitter/class.epitwitter.php');
	
	$consumer_key = 'nJFE6htU7i5Bf637pvdLBg';
	$consumer_secret = '7P4dgrc5OT6Ic0ePE6xz9u69weqNwpBQxkigRJhHk';
	$twitter = new EpiTwitter($consumer_key, $consumer_secret, $set['twitter']['oauth_token'], $set['twitter']['oauth_secret']);
	return $twitter;
}



//Twitter posten
function cron_twitter_real($lastexec) {
	global $set,$db,$apx;
	$twitter = null;
	if ( version_compare(phpversion(), '5', '<') ) return;
	if ( !$set['twitter']['oauth_token'] || !$set['twitter']['oauth_secret'] ) return;
	if ( !function_exists('curl_init') ) return; //CURL benötigt
	$apx->lang->drop('twitter', 'twitter');
	
	//News
	if ( $apx->is_module('news') && $set['twitter']['news'] ) {
		$data=$db->fetch("
			SELECT a.id, a.secid, a.title, a.subtitle, b.title AS cattitle
			FROM ".PRE."_news AS a
			LEFT JOIN ".PRE."_news_cat AS b ON a.catid=b.id
			WHERE ".time()." BETWEEN starttime AND endtime AND starttime>'".$lastexec."'
			ORDER BY starttime DESC
		");
		if ( count($data) ) {
			if ( is_null($twitter) ) {
				$twitter = twitter_connect();
			}
			postNewsTwitter($data, $twitter);
		}
	}
	
	
	//Artikel
	if ( $apx->is_module('articles') && $set['twitter']['articles'] ) {
		$data=$db->fetch("
			SELECT a.id, a.secid, a.title, a.subtitle, a.type, b.title AS cattitle
			FROM ".PRE."_articles AS a
			LEFT JOIN ".PRE."_articles_cat AS b ON a.catid=b.id
			WHERE ".time()." BETWEEN starttime AND endtime AND starttime>'".$lastexec."'
			ORDER BY starttime DESC
		");
		if ( count($data) ) {
			if ( is_null($twitter) ) {
				$twitter = twitter_connect();
			}
			postArticlesTwitter($data, $twitter);
		}
	}
	
	
	//Videos
	if ( $apx->is_module('videos') && $set['twitter']['videos'] ) {
		$data=$db->fetch("
			SELECT a.id, a.secid, a.title, b.title AS cattitle
			FROM ".PRE."_videos AS a
			LEFT JOIN ".PRE."_videos_cat AS b ON a.catid=b.id
			WHERE ".time()." BETWEEN starttime AND endtime AND starttime>'".$lastexec."'
			ORDER BY starttime DESC
		");
		if ( count($data) ) {
			if ( is_null($twitter) ) {
				$twitter = twitter_connect();
			}
			postVideosTwitter($data, $twitter);
		}
	}
	
	
	//Downloads
	if ( $apx->is_module('downloads') && $set['twitter']['downloads'] ) {
		$data=$db->fetch("
			SELECT a.id, a.secid, a.title, b.title AS cattitle
			FROM ".PRE."_downloads AS a
			LEFT JOIN ".PRE."_downloads_cat AS b ON a.catid=b.id
			WHERE ".time()." BETWEEN starttime AND endtime AND starttime>'".$lastexec."'
			ORDER BY starttime DESC
		");
		if ( count($data) ) {
			if ( is_null($twitter) ) {
				$twitter = twitter_connect();
			}
			postDownloadsTwitter($data, $twitter);
		}
	}
	
	
	//Galerien
	if ( $apx->is_module('gallery') && $set['twitter']['gallery'] ) {
		$data=$db->fetch("
			SELECT a.id, a.secid, a.title
			FROM ".PRE."_gallery AS a
			WHERE ".time()." BETWEEN starttime AND endtime AND starttime>'".$lastexec."'
			ORDER BY starttime DESC
		");
		if ( count($data) ) {
			if ( is_null($twitter) ) {
				$twitter = twitter_connect();
			}
			postGalleryTwitter($data, $twitter);
		}
	}
	
	
	//Links
	if ( $apx->is_module('links') && $set['twitter']['links'] ) {
		$data=$db->fetch("
			SELECT a.id, a.secid, a.title, b.title AS cattitle
			FROM ".PRE."_links AS a
			LEFT JOIN ".PRE."_links_cat AS b ON a.catid=b.id
			WHERE ".time()." BETWEEN starttime AND endtime AND starttime>'".$lastexec."'
			ORDER BY starttime DESC
		");
		if ( count($data) ) {
			if ( is_null($twitter) ) {
				$twitter = twitter_connect();
			}
			postLinksTwitter($data, $twitter);
		}
	}
	
	
	//Glossar
	if ( $apx->is_module('glossar') && $set['twitter']['glossar'] ) {
		$data=$db->fetch("
			SELECT a.id, a.title, b.title AS cattitle
			FROM ".PRE."_glossar AS a
			LEFT JOIN ".PRE."_glossar_cat AS b ON a.catid=b.id
			WHERE starttime>'".$lastexec."'
			ORDER BY starttime DESC
		");
		if ( count($data) ) {
			if ( is_null($twitter) ) {
				$twitter = twitter_connect();
			}
			postGlossarTwitter($data, $twitter);
		}
	}
	
	
	//Termine
	if ( $apx->is_module('calendar') && $set['twitter']['events'] ) {
		$data=$db->fetch("
			SELECT a.id, a.secid, a.title, b.title AS cattitle
			FROM ".PRE."_calendar_events AS a
			LEFT JOIN ".PRE."_calendar_cat AS b ON a.catid=b.id
			WHERE a.active>'".$lastexec."'
			ORDER BY starttime DESC
		");
		if ( count($data) ) {
			if ( is_null($twitter) ) {
				$twitter = twitter_connect();
			}
			postEventsTwitter($data, $twitter);
		}
	}
	
	
	//Forum
	if ( $apx->is_module('forum') && $set['twitter']['forum'] ) {
		require_once(BASEDIR.getmodulepath('forum').'functions.php');
		$forumids=forum_allowed_forums();
		if ( count($forumids) ) {
			$data=$db->fetch("
				SELECT a.threadid, a.title
				FROM ".PRE."_forum_threads AS a
				WHERE del=0 AND moved=0 AND forumid IN (".implode(',',$forumids).") AND opentime>'".$lastexec."'
				ORDER BY opentime DESC
			");
			if ( count($data) ) {
				if ( is_null($twitter) ) {
					$twitter = twitter_connect();
				}
				postForumTwitter($data, $twitter);
			}
		}
	}
	
	
	//Umfragen
	if ( $apx->is_module('poll') && $set['twitter']['poll'] ) {
		$data=$db->fetch("
			SELECT a.id, a.secid, a.question AS title
			FROM ".PRE."_poll AS a
			WHERE ".time()." BETWEEN starttime AND endtime AND starttime>'".$lastexec."'
			ORDER BY starttime DESC
		");
		if ( count($data) ) {
			if ( is_null($twitter) ) {
				$twitter = twitter_connect();
			}
			postPollTwitter($data, $twitter);
		}
	}
	
	
	//Benuter
	if ( $apx->is_module('user') ) {
		
		//Blogs
		if ( $set['twitter']['user_blog'] ) {
			$data = $db->fetch("
				SELECT a.id, a.userid, a.title
				FROM ".PRE."_user_blog AS a
				WHERE a.time>'".$lastexec."'
				ORDER BY a.time DESC
			");
			if ( count($data) ) {
				if ( is_null($twitter) ) {
					$twitter = twitter_connect();
				}
				postUserBlogTwitter($data, $twitter);
			}
		}
		
		//Galerien
		if ( $set['twitter']['user_gallery'] ) {
			$data=$db->fetch("
				SELECT a.id, a.owner, a.title
				FROM ".PRE."_user_gallery AS a
				WHERE a.addtime>'".$lastexec."' AND a.password=''
				ORDER BY a.addtime DESC
			");
			if ( count($data) ) {
				if ( is_null($twitter) ) {
					$twitter = twitter_connect();
				}
				postUserGalleryTwitter($data, $twitter);
			}
		}
	}
}


?>