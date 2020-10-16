<?php 

#
# German Language Pack
# ====================
#


/************** MODULE NAME **************/
$lang['modulename']['MODULENAME_GLOSSAR'] = 'Glossar';


/************** HEADLINES **************/
$lang['titles'] = array (
'TITLE_GLOSSAR_SHOW' => 'Glossar-Übersicht',
'TITLE_GLOSSAR_ADD' => 'Begriff hinzufügen',
'TITLE_GLOSSAR_EDIT' => 'Begriff bearbeiten',
'TITLE_GLOSSAR_DEL' => 'Begriff löschen',
'TITLE_GLOSSAR_ENABLE' => 'Begriff freischalten',
'TITLE_GLOSSAR_DISABLE' => 'Begriff widerrufen',

'TITLE_GLOSSAR_CATSHOW' => 'Themengebiete',
'TITLE_GLOSSAR_CATADD' => 'Themengebiet erstellen',
'TITLE_GLOSSAR_CATEDIT' => 'Themengebiet bearbeiten',
'TITLE_GLOSSAR_CATDEL' => 'Themengebiet löschen',
'TITLE_GLOSSAR_CATCLEAN' => 'Themengebiet leeren'
);


/************** NAVIGATION **************/
$lang['navi'] = array (
'NAVI_GLOSSAR_SHOW' => 'Begriffe zeigen',
'NAVI_GLOSSAR_ADD' => 'Neuer Begriff',
'NAVI_GLOSSAR_CATSHOW' => 'Themengebiete'
);


/************** ACTION EXPLICATION **************/
$lang['expl'] = array (

);


/************** LOG MESSAGES **************/
$lang['log'] = array (
'LOG_GLOSSAR_ADD' => 'Glossar-Eintrag erstellt',
'LOG_GLOSSAR_EDIT' => 'Glossar-Eintrag bearbeitet',
'LOG_GLOSSAR_DEL' => 'Glossar-Eintrag gelöscht',
'LOG_GLOSSAR_ENABLE' => 'Glossar-Eintrag freigeschaltet',
'LOG_GLOSSAR_DISABLE' => 'Glossar-Eintrag widerrufen',

'LOG_GLOSSAR_CATADD' => 'Glossar-Themengebiet erstellt',
'LOG_GLOSSAR_CATEDIT' => 'Glossar-Themengebiet bearbeitet',
'LOG_GLOSSAR_CATDEL' => 'Glossar-Themengebiet gelöscht',
'LOG_GLOSSAR_CATCLEAN' => 'Glossar-Themengebiet geleert'
);


/************** MEDIAMANAGER **************/
$lang['media'] = array (
'MM_INSERTTEXT' => 'In den Text einfügen'
);


/************** CONFIG **************/
$lang['config'] = array (
'SEARCHABLE' => 'Soll das Modul in die Suchfunktion einbezogen werden?',
'EPP' => 'Begriffe pro Seite: (0 = alle zeigen)',
'HIGHLIGHT' => 'Begriffe aus dem Glossar in Texten hervorheben?',
'COMS' => 'Kommentare aktivieren?',
'RATINGS' => 'Bewertungen aktivieren?'
);


/************** ACTIONS **************/

//SHOW
$lang['actions']['show'] = array (
'COL_TITLE' => 'Begriff',
'COL_CATEGORY' => 'Themengebiet',
'COL_PUBDATE' => 'Datum',
'COL_HITS' => 'Klicks',
'SORT_ADDTIME' => 'Erstellungsdatum',
'SORT_STARTTIME' => 'Veröffentlichung',
'SEARCHTEXT' => 'Stichwort',
'SEARCH' => 'Suchen',
'STITLE' => 'Begriff',
'STEXT' => 'Beschreibung',
'ALL' => 'Alle', 
'NONE' => 'Keine Begriffe gefunden!',
'COMMENTS' => 'Kommentare zeigen',
'RATINGS' => 'Bewertungen zeigen'
);


//ADD + EDIT
$lang['actions']['add'] = $lang['actions']['edit'] = array (
'OPTIONS' => 'Optionen',
'CATEGORY' => 'Themengebiet',
'TITLE' => 'Begriff',
'SPELLING' => 'Schreibweisen/Synonyme',
'SEPBYCOMMA' => 'durch Komma trennen',
'INLINESCREENS' => 'Inline-Bilder',
'TEXT' => 'Beschreibung',
'TAGS' => 'Tags',
'TAGSINFO' => 'einzelne Tags durch Kommas trennen',
'META_DESCRIPTION' => 'Meta Description',
'ALLOWCOMS' => 'Kommentare erlauben',
'ALLOWRATING' => 'Bewertung erlauben',
'SEARCHABLE' => 'In die Suche einbeziehen',
'PUBNOW' => 'Sofort veröffentlichen',
'SUBMIT_ADD' => 'Begriff hinzufügen',
'SUBMIT_EDIT' => 'Aktualisieren',
'INSERT' => 'Trotzdem eintragen',
'MSG_DUPLICATE' => 'Dieser Begriff befindet sich bereits im Glossar! Um den Begriff trotzdem hinzuzufügen, senden Sie das Formular noch einmal ab.'
);


//DEL
$lang['actions']['del'] = array (
'MSG_TEXT' => 'Wollen Sie den Begriff &quot;{TITLE}&quot; wirklich löschen?'
);


//ENABLE
$lang['actions']['enable'] = array (
'TITLE' => 'Begriff',
'STARTTIME' => 'Veröffentlichen ab',
'ENDTIME' => 'Automatisch widerrufen',
'SUBMIT' => 'Veröffentlichen'
);


//DISABLE
$lang['actions']['disable'] = array (
'MSG_TEXT' => 'Wollen Sie den Begriff &quot;{TITLE}&quot; wirklich widerrufen?',
'DISABLE' => 'Widerrufen'
);


//CATSHOW
$lang['actions']['catshow'] = array (
'COL_CATNAME' => 'Titel',
'COL_ENTRIES' => 'Anzahl: Begriffe',
'CLEAN' => 'Leeren &amp; Löschen',
'NONE' => 'Noch keine Themengebiete erstellt!'
);


//CATADD + CATEDIT
$lang['actions']['catadd'] = $lang['actions']['catedit'] = array (
'TITLE' => 'Titel',
'ICON' => 'Symbol-Pfad',
'TEXT' => 'Beschreibung',
'SUBMIT_ADD' => 'Themengebiet erstellen',
'SUBMIT_EDIT' => 'Aktualisieren'
);


//CATDEL
$lang['actions']['catdel'] = array (
'MSG_TEXT' => 'Wollen Sie das Themengebiet &quot;{TITLE}&quot; wirklich löschen?'
);


//CATCLEAN
$lang['actions']['catclean'] = array (
'TITLE' => 'Themengebiet',
'MOVETO' => 'Inhalt verschieben nach',
'DELCAT' => 'Themengebiet löschen',
'SUBMIT' => 'Themengebiet leeren'
);

?>