<?php

//
// German Language Pack
// ====================
//

// MODULE NAME
$lang['modulename']['MODULENAME_DOWNLOADS'] = 'Downloads';

// HEADLINES
$lang['titles'] = [
    'TITLE_DOWNLOADS_SHOW' => 'Downloads',
    'TITLE_DOWNLOADS_ADD' => 'Download hinzufügen',
    'TITLE_DOWNLOADS_EDIT' => 'Download bearbeiten',
    'TITLE_DOWNLOADS_DEL' => 'Download löschen',
    'TITLE_DOWNLOADS_ENABLE' => 'Download freischalten',
    'TITLE_DOWNLOADS_DISABLE' => 'Download widerrufen',

    'TITLE_DOWNLOADS_PSHOW' => 'Download-Bilder',
    'TITLE_DOWNLOADS_PADD' => 'Download-Bilder anfügen',
    'TITLE_DOWNLOADS_PDEL' => 'Download-Bilder löschen',

    'TITLE_DOWNLOADS_CATSHOW' => 'Kategorien',
    'TITLE_DOWNLOADS_CATADD' => 'Kategorie erstellen',
    'TITLE_DOWNLOADS_CATEDIT' => 'Kategorie bearbeiten',
    'TITLE_DOWNLOADS_CATDEL' => 'Kategorie löschen',
    'TITLE_DOWNLOADS_CATCLEAN' => 'Kategorie leeren',
    'TITLE_DOWNLOADS_CATMOVE' => 'Kategorie verschieben',

    'TITLE_DOWNLOADS_STATS' => 'Statistik',
];

// NAVIGATION
$lang['navi'] = [
    'NAVI_DOWNLOADS_SHOW' => 'Downloads zeigen',
    'NAVI_DOWNLOADS_ADD' => 'Neuer Download',
    'NAVI_DOWNLOADS_CATSHOW' => 'Kategorien',
    'NAVI_DOWNLOADS_STATS' => 'Statistik',
];

// ACTION EXPLICATION
$lang['expl'] = [
    'EXPL_DOWNLOADS_EDIT' => 'Sonderrechte geben auch Zugriff auf fremde Downloads',
    'EXPL_DOWNLOADS_DEL' => 'Sonderrechte geben auch Zugriff auf fremde Downloads',
    'EXPL_DOWNLOADS_ENABLE' => 'Sonderrechte geben auch Zugriff auf fremde Downloads',
    'EXPL_DOWNLOADS_DISABLE' => 'Sonderrechte geben auch Zugriff auf fremde Downloads',
    'EXPL_DOWNLOADS_PADD' => 'Sonderrechte geben auch Zugriff auf die Bilder fremder Downloads',
    'EXPL_DOWNLOADS_PDEL' => 'Sonderrechte geben auch Zugriff auf die Bilder fremder Downloads',
];

// LOG MESSAGES
$lang['log'] = [
    'LOG_DOWNLOADS_ADD' => 'Download hinzugefügt',
    'LOG_DOWNLOADS_EDIT' => 'Download bearbeitet',
    'LOG_DOWNLOADS_DEL' => 'Download gelöscht',
    'LOG_DOWNLOADS_ENABLE' => 'Download freigeschaltet',
    'LOG_DOWNLOADS_DISABLE' => 'Download widerrufen',

    'LOG_DOWNLOADS_PADD' => 'Download-Bild angefügt',
    'LOG_DOWNLOADS_PDEL' => 'Download-Bild gelöscht',

    'LOG_DOWNLOADS_CATADD' => 'Download-Kategorie erstellt',
    'LOG_DOWNLOADS_CATEDIT' => 'Download-Kategorie bearbeitet',
    'LOG_DOWNLOADS_CATDEL' => 'Download-Kategorie gelöscht',
    'LOG_DOWNLOADS_CATCLEAN' => 'Download-Kategorie geleert',
];

// MEDIAMANAGER
$lang['media'] = [
    'MM_INSERTFILE' => 'Als Download einfügen',
    'MM_INSERTTEXT' => 'In die Beschreibung einfügen',
];

// CONFIG
$lang['config'] = [
    'VIEW' => 'Darstellung',
    'OPTIONS' => 'Einstellungen',
    'IMAGES' => 'Bilder',
    'TEASERPIC' => 'Teaserbild',
    'SEARCHABLE' => 'Soll das Modul in die Suchfunktion einbezogen werden?',
    'EPP' => 'Downloads pro Seite: (0 = alle zeigen)',
    'SEARCHEPP' => 'Downloads pro Seite in den Suchergebnissen: (0 = alle zeigen)',
    'SORTBY' => 'Downloads sortieren nach:',
    'TITLE' => 'Titel',
    'DATE' => 'Veröffentlichung',
    'CATONLY' => 'Nur Downloads aus der gewählten Kategorie anzeigen?',
    'SPAMPROT' => 'Dauer in Minuten bis erneut ein Download eingesendet werden kann:',
    'REGONLY' => 'Downloads nur für registrierte Benutzer?',
    'NEW' => 'Anzahl der Tage, die ein Download &quot;neu&quot; ist:',
    'ADDPICS' => 'Anzahl der Bilder, die gleichzeitig hinzugefügt werden können:<br />(auf Serverleistung achten!)',
    'PICHEIGHT' => 'Maximale Höhe der Bilder:',
    'PICWIDTH' => 'Maximale Breite der Bilder:',
    'WATERMARK' => 'Pfad zum Wasserzeichen-Quellbild (relativ zum apexx-Ordner). Gültige Formate sind GIF, JPG und PNG.',
    'WATERMARK_TRANSP' => 'Transparenz des Wasserzeichens in Prozent (0-100). Gilt nur für Wasserzeichen im GIF- oder JPG-Format, bei PNG wird die Transparenz durch den Alpha-Channel bestimmt.',
    'WATERMARK_POSITION' => 'Position des Wasserzeichens:',
    'POSTOP' => 'oben',
    'POSMIDDLE' => 'mitte',
    'POSBOTTOM' => 'unten',
    'POSLEFT' => 'links',
    'POSCENTER' => 'zentriert',
    'POSRIGHT' => 'rechts',
    'THUMBWIDTH' => 'Maximale Breite des Vorschau-Bilds:',
    'THUMBHEIGHT' => 'Maximale Höhe des Vorschau-Bilds:',
    'QUALITY_RESIZE' => 'Qualitätiv hochwertigere Verkleinerung (rechenaufwendig!)?',
    'COMS' => 'Kommentare aktivieren?',
    'RATINGS' => 'Bewertungen aktivieren?',
    'MAXTRAFFIC' => 'Maximaler täglicher Datentraffic (in Bytes):',
    'EXTTRAFFIC' => 'Traffic von externen Downloads in die Statistik einberechnen?',
    'MIRRORSTATS' => 'Mirror-Downloads in die Statistik einberechnen?',
    'CAPTCHA' => 'Einsenden eines Downloads muss visuell bestätigt werden (Captcha)?',
    'MAILONBROKEN' => 'eMail an diese Adressen, wenn ein Download als defekt gemeldet wird (mehrere Adressen durch Kommas trennen):',
    'MAILONNEW' => 'eMail an diese Adressen, wenn ein Download eingesendet wurde (mehrere Adressen durch Kommas trennen):',
    'TEASERPIC_WIDTH' => 'Maximale Breite des Aufmacher-Bilds:',
    'TEASERPIC_HEIGHT' => 'Maximale Höhe des Aufmacher-Bilds:',
    'TEASERPIC_POPUP' => 'Aufmacher-Bild mit Popup in Originalgröße?',
    'TEASERPIC_POPUP_WIDTH' => 'Maximale Breite des Popup-Bilds:',
    'TEASERPIC_POPUP_HEIGHT' => 'Maximale Höhe des Popup-Bilds:',
    'TEASERPIC_QUALITY' => 'Qualitätiv hochwertigere Verkleinerung (rechenaufwendig!)?',
];

// ACTIONS

//SHOW
$lang['actions']['show'] = [
    'LAYER_ALL' => 'Alle',
    'LAYER_SEND' => 'Eingesendete',
    'LAYER_BROKEN' => 'Defekt',
    'COL_TITLE' => 'Titel',
    'COL_UPLOADER' => 'Uploader',
    'COL_CATEGORY' => 'Kategorie',
    'COL_ADDTIME' => 'Datum',
    'COL_DOWNLOADS' => 'Downloads',
    'SORT_ADDTIME' => 'Erstellungsdatum',
    'SORT_STARTTIME' => 'Veröffentlichung',
    'BROKEN' => 'Defekt',
    'BY' => 'von',
    'SEARCHTEXT' => 'Stichwort',
    'SEARCH' => 'Suchen',
    'USERNAME' => 'Uploader',
    'STITLE' => 'Titel',
    'STEXT' => 'Text',
    'SECTION' => 'Sektion',
    'CATEGORY' => 'Kategorie',
    'ALL' => 'Alle',
    'ADDPAGE' => 'Seite anfügen',
    'GUEST' => 'Gast',
    'NONE' => 'Keine Downloads gefunden!',
    'COPY' => 'Kopieren',
    'COMMENTS' => 'Kommentare zeigen',
    'RATINGS' => 'Bewertungen zeigen',
    'MIRRORS' => 'Mirrors',
    'PICS' => 'Bilder',
    'MULTI_DEL' => 'Löschen',
    'MULTI_ENABLE' => 'Freischalten',
    'MULTI_DISABLE' => 'Widerrufen',
];

//ADD + EDIT
$lang['actions']['add'] = $lang['actions']['edit'] = [
    'UPLOADFILE' => 'Dateien/Bilder hochladen',
    'FILE' => 'Datei',
    'OPTIONS' => 'Optionen',
    'UPLOADER' => 'Uploader',
    'GUEST' => 'Gast',
    'TEASERPIC' => 'Aufmacher-Bild',
    'TEASERPIC_UPLOAD' => 'Hochladen',
    'TEASERPIC_PATH' => 'Bild verwenden',
    'DELPIC' => 'Bild löschen',
    'SHOWPIC' => 'Bild zeigen',
    'LINKPRODUCT' => 'Produkt verknüpfen',
    'SECTION' => 'In dieser Sektion anzeigen',
    'ALLSEC' => 'Alle Sektionen',
    'CATEGORY' => 'Kategorie',
    'NEWCAT' => 'Kategorie erstellen',
    'TITLE' => 'Titel',
    'INLINESCREENS' => 'Inline-Bilder',
    'TEXT' => 'Beschreibung',
    'TAGS' => 'Tags',
    'TAGSINFO' => 'einzelne Tags durch Kommas trennen',
    'META_DESCRIPTION' => 'Meta Description',
    'AUTHOR' => 'Autor',
    'AUTHORLINK' => 'Autor-Verlinkung',
    'LINKGALLERY' => 'Galerie verknüpfen',
    'LOCATION' => 'Ort',
    'LOCAL' => 'lokal',
    'EXTERNAL' => 'extern',
    'SENDFILE' => 'Eingesendete Datei',
    'GETFILE' => 'Datei betrachten',
    'ENABLEFILE' => 'Aktivieren',
    'UPLOAD' => 'Hochladen',
    'ORURL' => 'oder Pfad/URL',
    'FILEFORMAT' => 'Dateiformat',
    'FILEFORMAT_INFO' => 'optional, ansonsten automatische Erkennung',
    'FILESIZE' => 'Dateigröße',
    'MIRRORS' => 'Mirrors',
    'MIRROR_TITLE' => 'Titel',
    'MIRROR_URL' => 'URL',
    'NEWLINE' => 'Neue Zeile',
    'PASSWORD' => 'Passwortschutz',
    'LIMIT' => 'Download-Limit pro Tag',
    'ALLOWCOMS' => 'Kommentare erlauben',
    'ALLOWRATING' => 'Bewertung erlauben',
    'RESTRICTED' => 'Altersabfrage aktivieren (ab 18 Jahren)',
    'TOPDOWNLOAD' => 'Dies ist ein Top-Download',
    'REGONLY' => 'Download nur für registrierte Benutzer',
    'PUBNOW' => 'Sofort freischalten?',
    'SEARCHABLE' => 'In die Suche einbeziehen',
    'PUBLICATION' => 'Veröffentlichung',
    'STARTTIME' => 'Veröffentlichen ab',
    'ENDTIME' => 'Automatisch widerrufen',
    'SUBMIT_ADD' => 'Download hinzufügen',
    'SUBMIT_EDIT' => 'Aktualisieren',
    'SUBMIT_PADD' => 'Hinzufügen und Bilder anfügen',
    'INFO_NOTEXISTS' => 'Die angegebene Datei existiert nicht!',
    'INFO_TOOBIG' => 'Die Datei kann aufgrund ihrer Größe nicht hochgeladen werden. Verwenden Sie FTP-Upload!',
    'INFO_NOTALLOWED' => 'Der Dateityp des Downloads darf nicht hochgeladen werden!',
];

//DEL
$lang['actions']['del'] = [
    'DELFILE' => 'Datei löschen',
    'MSG_TEXT' => 'Wollen Sie den Download &quot;{TITLE}&quot; wirklich löschen?',
];

//ENABLE
$lang['actions']['enable'] = [
    'TITLE' => 'Download',
    'STARTTIME' => 'Freischalten ab',
    'ENDTIME' => 'Automatisch widerrufen',
    'SUBMIT' => 'Freischalten',
];

//DISABLE
$lang['actions']['disable'] = [
    'MSG_TEXT' => 'Wollen Sie den Download &quot;{TITLE}&quot; wirklich widerrufen?',
    'DISABLE' => 'Widerrufen',
];

//PSHOW
$lang['actions']['pshow'] = [
    'DOWNLOAD' => 'Download',
    'COL_THUMBNAIL' => 'Thumbnail',
    'NONE' => 'Noch keine Bilder angefügt!',
    'MULTI_PDEL' => 'Löschen',
];

//PADD
$lang['actions']['padd'] = [
    'CHOOSEPICS' => 'Bilder auswählen',
    'INFOTEXT' => '<b>ACHTUNG:</b> GIF-Bilder werden in das Format JPG kovertiert!',
    'GLOBALOPTIONS' => 'Optionen für Alle',
    'PIC' => 'Bild',
    'OPTIONS' => 'Optionen',
    'WATERMARK' => 'Wasserzeichen',
    'NORESIZE' => 'Nicht verkleinern',
    'SUBMIT' => 'Bilder anfügen',
    'MSG_NOIMAGE' => 'Die Datei &quot;{NAME}&quot; ist kein gültiges Bild!',
];

//PDEL
$lang['actions']['pdel'] = [
    'MSG_TEXT' => 'Wollen Sie dieses Bild wirklich löschen?',
];

//CATSHOW
$lang['actions']['catshow'] = [
    'COL_CATNAME' => 'Titel',
    'COL_DOWNLOADS' => 'Anzahl: Downloads',
    'CLEAN' => 'Leeren &amp; Löschen',
    'ATTINFO' => 'Zusatz-Informationen',
    'USEDND' => 'Sie können die Kategorien per Drag &amp; Drop anordnen',
    'NONE' => 'Noch keine Kategorien erstellt!',
];

//CATADD + CATEDIT
$lang['actions']['catadd'] = $lang['actions']['catedit'] = [
    'TITLE' => 'Titel',
    'TEXT' => 'Text',
    'ICON' => 'Symbol-Pfad',
    'CREATEIN' => 'Unterkategorie von',
    'ROOT' => 'Dies ist eine Hauptkategorie',
    'OPEN' => 'Kann Downloads enthalten',
    'FORGROUP' => 'Benutzergruppen, die Downloads erstellen dürfen',
    'ALL' => 'Alle Benutzergruppen',
    'SUBMIT_ADD' => 'Kategorie erstellen',
    'SUBMIT_EDIT' => 'Aktualisieren',
    'INFO_CONTAINSDOWNLOADS' => 'Diese Kategorie enthält bereits Downloads! Bitte zuerst leeren oder Downloads erlauben.',
];

//CATDEL
$lang['actions']['catdel'] = [
    'MSG_TEXT' => 'Wollen Sie die Kategorie &quot;{TITLE}&quot; wirklich löschen?',
];

//CATCLEAN
$lang['actions']['catclean'] = [
    'TITLE' => 'Kategorie',
    'MOVETO' => 'Inhalt verschieben nach',
    'DELCAT' => 'Kategorie löschen',
    'SUBMIT' => 'Kategorie leeren',
];

//STATS
$lang['actions']['stats'] = [
    'TRAFFIC' => 'Traffic',
    'DOWNLOADS' => 'Downloads',
    'FILES' => 'Freigeschaltete Downloads',
    'AVGDLSDAY' => 'Durschnittliche Downloadklicks',
    'AVGSIZEDAY' => 'Durchschnittliche Datenmenge',
    'DLSCOMPLETE' => 'Gesamte Downloadsklicks',
    'DLSTHISWEEK' => 'Downloadklicks der letzten 7 Tage',
    'DLSTODAY' => 'Downloadklicks heute',
    'SIZECOMPLETE' => 'Gesamte Datenmenge',
    'SIZETHISWEEK' => 'Datenmenge der letzten 7 Tage',
    'SIZETODAY' => 'Datenmenge heute',
    'PERDAY' => 'pro Tag',
    'GRAPH' => 'im Diagramm',
    'HITS' => 'Klicks',
    'LAST50DAYS' => 'der letzten 50 Tage',
    'POPULAR' => 'Beliebteste Downloads der letzten 7 Tage',
];
