<?php 

#
# German Language Pack
# ====================
#


/************** MODULE NAME **************/
$lang['modulename']['MODULENAME_TWITTER'] = 'Twitter';


/************** CONFIG **************/
$lang['config'] = array (
'ACCOUNT' => 'Account',
'CONTENT' => 'Inhalte',
'FORMAT' => 'Nachrichtenformat',
'OAUTH_TOKEN' => 'OAuth Token:<br /><a href="'.HTTP_HOST.HTTPDIR.'admin/action.php?action=twitter.connect" target="_blank">Jetzt konfigurieren</a>',
'OAUTH_SECRET' => 'OAuth Secret Token:<br /><a href="'.HTTP_HOST.HTTPDIR.'admin/action.php?action=twitter.connect" target="_blank">Jetzt konfigurieren</a>',
'NEWS' => 'News twittern?',
'ARTICLES' => 'Artikel twittern?',
'DOWNLOADS' => 'Downloads twittern?',
'EVENTS' => 'Termine twittern?',
'GALLERY' => 'Galerien twittern?',
'FORUM' => 'Neue Foren-Themen twittern?',
'GLOSSAR' => 'Glossar-Einträge twittern?',
'LINKS' => 'Links twittern?',
'POLL' => 'Umfragen twittern?',
'VIDEOS' => 'Videos twittern?',
'USER_BLOG' => 'Benutzer-Blogs twittern?',
'USER_GALLERY' => 'Benutzer-Galerien twittern?',
'TPL_NEWS' => 'News, verfügbare Platzhalter: {TITLE}, {SUBTITLE}, {CATTITLE}, {LINK}, {SECTION}',
'TPL_ARTICLES' => 'Artikel, verfügbare Platzhalter: {TYPE}, {TITLE}, {SUBTITLE}, {CATTITLE}, {LINK}, {SECTION}',
'TPL_DOWNLOADS' => 'Downloads, verfügbare Platzhalter: {TITLE}, {CATTITLE}, {LINK}, {SECTION}',
'TPL_EVENTS' => 'Termine, verfügbare Platzhalter: {TITLE}, {CATTITLE}, {LINK}, {SECTION}',
'TPL_GALLERY' => 'Galerien, verfügbare Platzhalter: {TITLE}, {LINK}, {SECTION}',
'TPL_FORUM' => 'Forum, verfügbare Platzhalter: {TITLE}, {LINK}',
'TPL_GLOSSAR' => 'Glossar, verfügbare Platzhalter: {TITLE}, {CATTITLE}, {LINK}',
'TPL_LINKS' => 'Links, verfügbare Platzhalter: {TITLE}, {CATTITLE}, {LINK}, {SECTION}',
'TPL_POLL' => 'Umfragen, verfügbare Platzhalter: {TITLE}, {LINK}, {SECTION}',
'TPL_VIDEOS' => 'Videos, verfügbare Platzhalter: {TITLE}, {CATTITLE}, {LINK}, {SECTION}',
'TPL_USER_BLOG' => 'Benutzer-Blogs, verfügbare Platzhalter: {TITLE}, {LINK}',
'TPL_USER_GALLERY' => 'Benutzer-Galerien, verfügbare Platzhalter: {TITLE}, {LINK}',
);


$lang['actions']['connect'] = array (
'MSG_PHP5ONLY' => 'Das Twitter-Modul funktioniert nur unter PHP5!',
'MSG_DONE' => 'Das Twitter-Modul wurde mit dem Account <strong>{ACCOUNT}</strong> verbunden.'
);

?>