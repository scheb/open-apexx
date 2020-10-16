<?php 

#
# German Language Pack
# ====================
#


/************** MODULE NAME **************/
$lang['modulename']['MODULENAME_LINKS'] = 'Links';


/************** HEADLINES **************/
$lang['titles'] = array (
'TITLE_LINKS_SHOW' => 'Links',
'TITLE_LINKS_ADD' => 'Link hinzufügen',
'TITLE_LINKS_EDIT' => 'Link bearbeiten',
'TITLE_LINKS_DEL' => 'Link löschen',
'TITLE_LINKS_ENABLE' => 'Link freischalten',
'TITLE_LINKS_DISABLE' => 'Link widerrufen',
'TITLE_LINKS_CATSHOW' => 'Kategorien',
'TITLE_LINKS_CATADD' => 'Kategorie erstellen',
'TITLE_LINKS_CATEDIT' => 'Kategorie bearbeiten',
'TITLE_LINKS_CATDEL' => 'Kategorie löschen',
'TITLE_LINKS_CATCLEAN' => 'Kategorie leeren',
'TITLE_LINKS_CATMOVE' => 'Kategorie verschieben'
);


/************** NAVIGATION **************/
$lang['navi'] = array (
'NAVI_LINKS_SHOW' => 'Links zeigen',
'NAVI_LINKS_ADD' => 'Neuer Link',
'NAVI_LINKS_CATSHOW' => 'Kategorien'
);


/************** ACTION EXPLICATION **************/
$lang['expl'] = array (
'EXPL_LINKS_EDIT' => 'Sonderrechte geben auch Zugriff auf fremde Links',
'EXPL_LINKS_DEL' => 'Sonderrechte geben auch Zugriff auf fremde Links',
'EXPL_LINKS_ENABLE' => 'Sonderrechte geben auch Zugriff auf fremde Links',
'EXPL_LINKS_DISABLE' => 'Sonderrechte geben auch Zugriff auf fremde Links',
'EXPL_LINKS_PADD' => 'Sonderrechte geben auch Zugriff auf die Bilder fremder Links',
'EXPL_LINKS_PDEL' => 'Sonderrechte geben auch Zugriff auf die Bilder fremder Links'
);


/************** LOG MESSAGES **************/
$lang['log'] = array (
'LOG_LINKS_ADD' => 'Link hinzugefügt',
'LOG_LINKS_EDIT' => 'Link bearbeitet',
'LOG_LINKS_DEL' => 'Link gelöscht',
'LOG_LINKS_ENABLE' => 'Link freigeschaltet',
'LOG_LINKS_DISABLE' => 'Link widerrufen',
'LOG_LINKS_CATADD' => 'Link-Kategorie erstellt',
'LOG_LINKS_CATEDIT' => 'Link-Kategorie bearbeitet',
'LOG_LINKS_CATDEL' => 'Link-Kategorie gelöscht',
'LOG_LINKS_CATCLEAN' => 'Link-Kategorie geleert'
);


/************** MEDIAMANAGER **************/
$lang['media'] = array (
'MM_INSERTTEXT' => 'In die Beschreibung einfügen',
'MM_INSERTLINKPIC' => 'Als Aufmacher-Bild einfügen'
);


/************** CONFIG **************/
$lang['config'] = array (
'VIEW' => 'Darstellung',
'OPTIONS' => 'Einstellungen',
'IMAGES' => 'Bilder',
'SEARCHABLE' => 'Soll das Modul in die Suchfunktion einbezogen werden?',
'EPP' => 'Links pro Seite: (0 = alle zeigen)',
'SEARCHEPP' => 'Links pro Seite im Suchergebnis: (0 = alle zeigen)',
'SORTBY' => 'Links sortieren nach:',
'TITLE' => 'Titel',
'DATE' => 'Veröffentlichung',
'CATONLY' => 'Nur Links aus der gewählten Kategorie anzeigen?',
'SPAMPROT' => 'Dauer in Minuten bis erneut ein Link eingesendet werden kann:',
'NEW' => 'Anzahl der Tage, die ein Link &quot;neu&quot; ist:',
'LINKPIC_WIDTH' => 'Maximale Breite des Aufmacher-Bilds:',
'LINKPIC_HEIGHT' => 'Maximale Höhe des Aufmacher-Bilds:',
'LINKPIC_POPUP' => 'Aufmacher-Bild mit Popup in Originalgröße?',
'LINKPIC_POPUP_WIDTH' => 'Maximale Breite des Popup-Bilds:',
'LINKPIC_POPUP_HEIGHT' => 'Maximale Höhe des Popup-Bilds:',
'LINKPIC_QUALITY' => 'Qualitätiv hochwertigere Verkleinerung (rechenaufwendig!)?',
'COMS' => 'Kommentare aktivieren?',
'RATINGS' => 'Bewertungen aktivieren?',
'MAXTRAFFIC' => 'Maximaler täglicher Datentraffic (in Bytes):',
'CAPTCHA' => 'Einsenden eines Links muss visuell bestätigt werden (Captcha)?',
'MAILONBROKEN' => 'eMail an diese Adressen, wenn ein Link als defekt gemeldet wird (mehrere Adressen durch Kommas trennen):',
'MAILONNEW' => 'eMail an diese Adressen, wenn ein Link eingesendet wurde (mehrere Adressen durch Kommas trennen):'
);


/************** ACTIONS **************/

//SHOW
$lang['actions']['show'] = array (
'LAYER_ALL' => 'Alle',
'LAYER_SEND' => 'Eingesendete',
'LAYER_BROKEN' => 'Defekt',
'COL_TITLE' => 'Titel',
'COL_AUTHOR' => 'Autor',
'COL_CATEGORY' => 'Kategorie',
'COL_ADDTIME' => 'Datum',
'COL_HITS' => 'Klicks',
'SORT_ADDTIME' => 'Erstellungsdatum',
'SORT_STARTTIME' => 'Veröffentlichung',
'BROKEN' => 'Defekt',
'BY' => 'von',
'SEARCHTEXT' => 'Stichwort',
'SEARCH' => 'Suchen',
'USERNAME' => 'Autor',
'STITLE' => 'Titel',
'STEXT' => 'Text',
'SECTION' => 'Sektion',
'CATEGORY' => 'Kategorie',
'ALL' => 'Alle',
'GUEST' => 'Gast', 
'NONE' => 'Keine Links gefunden!',
'COMMENTS' => 'Kommentare zeigen',
'RATINGS' => 'Bewertungen zeigen',
'MULTI_DEL' => 'Löschen',
'MULTI_ENABLE' => 'Freischalten',
'MULTI_DISABLE' => 'Widerrufen'
);


//ADD + EDIT
$lang['actions']['add'] = $lang['actions']['edit'] = array (
'UPLOADFILE' => 'Dateien/Bilder hochladen',
'OPTIONS' => 'Optionen',
'GUEST' => 'Gast',
'SECTION' => 'In dieser Sektion anzeigen',
'ALLSEC' => 'Alle Sektionen',
'CATEGORY' => 'Kategorie',
'NEWCAT' => 'Kategorie erstellen',
'TITLE' => 'Titel',
'LINKPIC' => 'Aufmacher-Bild',
'LINKPIC_UPLOAD' => 'Hochladen',
'LINKPIC_PATH' => 'Bild verwenden',
'SHOWPIC' => 'Bild anzeigen',
'DELPIC' => 'Löschen',
'INLINESCREENS' => 'Inline-Bilder',
'TEXT' => 'Beschreibung',
'TAGS' => 'Tags',
'TAGSINFO' => 'einzelne Tags durch Kommas trennen',
'META_DESCRIPTION' => 'Meta Description',
'AUTHOR' => 'Autor',
'LINKGALLERY' => 'Galerie verknüpfen',
'ALLOWCOMS' => 'Kommentare erlauben',
'ALLOWRATING' => 'Bewertung erlauben',
'RESTRICTED' => 'Altersabfrage aktivieren (ab 18 Jahren)',
'TOPLINK' => 'Dies ist ein Top-Link',
'PUBNOW' => 'Sofort freischalten?',
'SEARCHABLE' => 'In die Suche einbeziehen',
'PUBLICATION' => 'Veröffentlichung',
'STARTTIME' => 'Veröffentlichen ab',
'ENDTIME' => 'Automatisch widerrufen',
'SUBMIT_ADD' => 'Link hinzufügen',
'SUBMIT_EDIT' => 'Aktualisieren'
);


//DEL
$lang['actions']['del'] = array (
'MSG_TEXT' => 'Wollen Sie den Link &quot;{TITLE}&quot; wirklich löschen?'
);


//ENABLE
$lang['actions']['enable'] = array (
'TITLE' => 'Link',
'STARTTIME' => 'Freischalten ab',
'ENDTIME' => 'Automatisch widerrufen',
'SUBMIT' => 'Freischalten'
);


//DISABLE
$lang['actions']['disable'] = array (
'MSG_TEXT' => 'Wollen Sie den Link &quot;{TITLE}&quot; wirklich widerrufen?',
'DISABLE' => 'Widerrufen'
);


//CATSHOW
$lang['actions']['catshow'] = array (
'COL_CATNAME' => 'Titel',
'COL_LINKS' => 'Anzahl: Links',
'CLEAN' => 'Leeren &amp; Löschen',
'ATTINFO' => 'Zusatz-Informationen',
'USEDND' => 'Sie können die Kategorien per Drag &amp; Drop anordnen',
'NONE' => 'Noch keine Kategorien erstellt!'
);


//CATADD + CATEDIT
$lang['actions']['catadd'] = $lang['actions']['catedit'] = array (
'TITLE' => 'Titel',
'TEXT' => 'Text',
'ICON' => 'Symbol-Pfad',
'CREATEIN' => 'Unterkategorie von',
'ROOT' => 'Dies ist eine Hauptkategorie',
'OPEN' => 'Kann Links enthalten',
'ALL' => 'Alle Benutzergruppen',
'SUBMIT_ADD' => 'Kategorie erstellen',
'SUBMIT_EDIT' => 'Aktualisieren',
'INFO_CONTAINSLINKS' => 'Diese Kategorie enthält bereits Links! Bitte zuerst leeren oder Links erlauben.'
);


//CATDEL
$lang['actions']['catdel'] = array (
'MSG_TEXT' => 'Wollen Sie die Kategorie &quot;{TITLE}&quot; wirklich löschen?'
);


//CATCLEAN
$lang['actions']['catclean'] = array (
'TITLE' => 'Kategorie',
'MOVETO' => 'Inhalt verschieben nach',
'DELCAT' => 'Kategorie löschen',
'SUBMIT' => 'Kategorie leeren'
);

?>