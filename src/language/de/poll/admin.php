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


# German Language Pack
# ====================


/************** MODULE NAME **************/
$lang['modulename']['MODULENAME_POLL'] = 'Umfragen';


/************** HEADLINES **************/
$lang['titles'] = array (
'TITLE_POLL_SHOW' => 'Umfragen',
'TITLE_POLL_ADD' => 'Umfrage erstellen',
'TITLE_POLL_EDIT' => 'Umfrage bearbeiten',
'TITLE_POLL_DEL' => 'Umfrage löschen',
'TITLE_POLL_ENABLE' => 'Umfrage freischalten',
'TITLE_POLL_DISABLE' => 'Umfrage widerrufen'
);


/************** NAVIGATION **************/
$lang['navi'] = array (
'NAVI_POLL_SHOW' => 'Umfragen zeigen',
'NAVI_POLL_ADD' => 'Neue Umfrage'
);


/************** ACTION EXPLICATION **************/
$lang['expl'] = array (
'EXPL_POLL_CENABLE' => 'Diese Aktion wird nur benötigt wenn die Kommentare moderiert werden.',
'EXPL_POLL_CDISABLE' => 'Diese Aktion wird nur benötigt wenn die Kommentare moderiert werden.'
);


/************** LOG MESSAGES **************/
$lang['log'] = array (
'LOG_POLL_ADD' => 'Umfrage erstellt',
'LOG_POLL_EDIT' => 'Umfrage bearbeitet',
'LOG_POLL_DEL' => 'Umfrage gelöscht',
'LOG_POLL_ENABLE' => 'Umfrage freigeschaltet',
'LOG_POLL_DISABLE' => 'Umfrage widerrufen'
);


/************** CONFIG **************/
$lang['config'] = array (
'VIEW' => 'Darstellung',
'OPTIONS' => 'Einstellung',
'SEARCHABLE' => 'Soll das Modul in die Suchfunktion einbezogen werden?',
'MAXFIRST' => 'Ergebnis nach Anzahl der Stimmen ordnen?',
'ARCHVOTE' => 'Abstimmen bei archivierten Umfragen erlauben?',
'BARMAXWIDTH' => 'Maximale Breite des Ergebnis-Balken in Pixel:<br />(wenn 0 gesetzt wird ein Prozentwert ausgegeben)',
'PERCENTDIGITS' => 'Anzahl der Nachkommastellen bei Prozentangaben:',
'COMS' => 'Kommentare aktivieren?',
'ARCHCOMS' => 'Kommentare in archivierten Umfragen aktivieren?',
'ARCHALL' => 'Alle Umfragen im Archiv anzeigen?'
);


/************** ACTIONS **************/

//SHOW
$lang['actions']['show'] = array (
'COL_QUESTION' => 'Frage',
'COL_ADDTIME' => 'Erstellungsdatum',
'COL_STARTTIME' => 'Start der Umfrage',
'COL_ENDTIME' => 'Ende der Umfrage',
'NONE' => 'Bisher keine Umfragen erstellt!',
'COMMENTS' => 'Kommentare zeigen'
);

//ADD + EDIT
$lang['actions']['add'] = $lang['actions']['edit'] = array (
'INFOTEXT' => '<b>Tipp:</b> Wenn Sie nicht alle 10 Optionen verwenden wollen lassen Sie die übrigen Felder einfach leer!',
'POSSANS' => 'Mögliche Antworten',
'SECTION' => 'In dieser Sektion anzeigen',
'ALLSEC' => 'Alle Sektionen',
'QUESTION' => 'Frage',
'DAYS' => 'Dauer der Umfrage (in Tagen)',
'TAGS' => 'Tags',
'TAGSINFO' => 'einzelne Tags durch Kommas trennen',
'META_DESCRIPTION' => 'Meta Description',
'OPTIONS' => 'Optionen',
'ALLOWCOMS' => 'Kommentare erlauben',
'PUBNOW' => 'Sofort freischalten',
'SEARCHABLE' => 'In die Suche einbeziehen',
'MULTIPLE' => 'Mehrfache Auswahl möglich',
'ANSWER' => 'Antwort',
'VOTES' => 'Stimmen',
'NEWLINE' => 'Neue Zeile',
'PUBLICATION' => 'Veröffentlichung',
'STARTTIME' => 'Veröffentlichen ab',
'ENDTIME' => 'Automatisch widerrufen',
'SUBMIT_ADD' => 'Umfrage erstellen',
'SUBMIT_EDIT' => 'Aktualisieren'
);

//DEL
$lang['actions']['del'] = array (
'MSG_TEXT' => 'Wollen Sie die Umfrage &quot;{TITLE}&quot; wirklich löschen?'
);

//ENABLE
$lang['actions']['enable'] = array (
'TITLE' => 'Umfrage',
'STARTTIME' => 'Freischalten ab',
'ENDTIME' => 'Automatisch widerrufen',
'SUBMIT' => 'Freischalten'
);

//DISABLE
$lang['actions']['disable'] = array (
'MSG_TEXT' => 'Wollen Sie die Umfrage &quot;{TITLE}&quot; wirklich widerrufen?',
'DISABLE' => 'Widerrufen'
);

?>