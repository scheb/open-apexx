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

#
# German Language Pack
# ====================
#


/************** MODULE NAME **************/
$lang['modulename']['MODULENAME_CONTENT'] = 'Statische Seiten';


/************** HEADLINES **************/
$lang['titles'] = array (
'TITLE_CONTENT_SHOW' => 'Statische Seiten',
'TITLE_CONTENT_ADD' => 'Statische Seite erstellen',
'TITLE_CONTENT_EDIT' => 'Statische Seite bearbeiten',
'TITLE_CONTENT_DEL' => 'Statische Seite löschen',
'TITLE_CONTENT_ENABLE' => 'Statische Seite freischalten',
'TITLE_CONTENT_DISABLE' => 'Statische Seite widerrufen',
'TITLE_CONTENT_GROUP' => 'Kategorien'
);


/************** NAVIGATION **************/
$lang['navi'] = array (
'NAVI_CONTENT_SHOW' => 'Seiten zeigen',
'NAVI_CONTENT_ADD' => 'Neue Seite',
'NAVI_CONTENT_GROUP' => 'Kategorien'
);


/************** ACTION EXPLICATION **************/
$lang['expl'] = array (
'EXPL_CONTENT_EDIT' => 'Sonderrechte geben auch Zugriff auf fremde Seiten',
'EXPL_CONTENT_DEL' => 'Sonderrechte geben auch Zugriff auf fremde Seiten',
'EXPL_CONTENT_ENABLE' => 'Sonderrechte geben auch Zugriff auf fremde Seiten',
'EXPL_CONTENT_DISABLE' => 'Sonderrechte geben auch Zugriff auf fremde Seiten'
);


/************** LOG MESSAGES **************/
$lang['log'] = array (
'LOG_CONTENT_ADD' => 'Statische Seite erstellt',
'LOG_CONTENT_EDIT' => 'Statische Seite bearbeitet',
'LOG_CONTENT_DEL' => 'Statische Seite gelöscht',
'LOG_CONTENT_ENABLE' => 'Statische Seite freigeschaltet',
'LOG_CONTENT_DISABLE' => 'Statische Seite widerrufen',
'LOG_CONTENT_GROUPADD' => 'Kategorie hinzugefügt',
'LOG_CONTENT_GROUPDEL' => 'Kategorie gelöscht',
'LOG_CONTENT_GROUPCLEAN' => 'Kategorie geleert',
);


/************** MEDIAMANAGER **************/
$lang['media'] = array (
'MM_INSERTCONTENT' => 'In den Text einfügen'
);


/************** CONFIG **************/
$lang['config'] = array (
'SEARCHABLE' => 'Soll das Modul in die Suchfunktion einbezogen werden?',
'COMS' => 'Kommentare aktivieren?',
'RATINGS' => 'Bewertungen aktivieren?'
);


/************** ACTIONS **************/

//SHOW
$lang['actions']['show'] = array (
'COL_TITLE' => 'Titel',
'COL_USER' => 'Autor',
'COL_ADDTIME' => 'Erstellungsdatum',
'COL_LASTCHANGE' => 'Letzte Änderung',
'COL_HITS' => 'Klicks',
'SEARCHTEXT' => 'Stichwort',
'SEARCH' => 'Suchen',
'STITLE' => 'Begriff',
'STEXT' => 'Beschreibung',
'USERNAME' => 'Benutzer',
'SECTION' => 'Sektion',
'CATEGORY' => 'Kategorie',
'ALL' => 'Alle',
'RATINGS' => 'Bewertungen',
'COMMENTS' => 'Kommentare',
'NONE' => 'Noch keine Seiten erstellt!'
);

//ADD + EDIT
$lang['actions']['add'] = $lang['actions']['edit'] = array (
'SECTION' => 'In dieser Sektion anzeigen',
'ALLSEC' => 'Alle Sektionen',
'CATEGORY' => 'Kategorie',
'USERNAME' => 'Autor',
'TITLE' => 'Titel',
'TITLEINFO' => 'Die Zeichenfolge &quot;->&quot; teilt den Titel zu einen Pfad auf.<br />Beispiel: &quot;Home -> Unterseite 1 -> Unterseite 2&quot;',
'INLINESCREENS' => 'Inline-Bilder',
'CONTENT' => 'Inhalt',
'META_DESCRIPTION' => 'Meta Description',
'OPTIONS' => 'Optionen',
'ALLOWCOMS' => 'Kommentare erlauben',
'ALLOWRATING' => 'Bewertung erlauben',
'PUBNOW' => 'Sofort veröffentlichen',
'SEARCHABLE' => 'In die Suche einbeziehen',
'SUBMIT_ADD' => 'Seite erstellen',
'SUBMIT_EDIT' => 'Aktualisieren'
);

//DEL
$lang['actions']['del'] = array (
'MSG_TEXT' => 'Wollen Sie die Seite &quot;{TITLE}&quot; wirklich löschen?'
);

//ENABLE
$lang['actions']['enable'] = array (

);

//DISABLE
$lang['actions']['disable'] = array (

);

//GROUP
$lang['actions']['group']= array (
'COL_TITLE' => 'Titel',
'COL_CONTENTS' => 'Inhalte',
'NONE' => 'Noch keine Kategorien erstellt!',
'CATADD' => 'Kategorie erstellen',
'CATEDIT' => 'Aktualisieren',
'CLEAN' => 'Leeren &amp; Löschen',
'CATTITLE' => 'Kategorie',
'MOVETO' => 'Inhalt verschieben nach',
'DELCAT' => 'Kategorie löschen',
'SUBMIT_CLEAR' => 'Kategorie leeren',
'MSG_TEXT' => 'Soll die Kategorie &quot;{TITLE}&quot; wirklich gelöscht werden?'
);


?>