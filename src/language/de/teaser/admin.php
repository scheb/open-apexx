<?php

//
// German Language Pack
// ====================
//

// MODULE NAME
$lang['modulename']['MODULENAME_TEASER'] = 'Teaser';

// HEADLINES
$lang['titles'] = [
    'TITLE_TEASER_SHOW' => 'Teaser',
    'TITLE_TEASER_ADD' => 'Teaser hinzufügen',
    'TITLE_TEASER_EDIT' => 'Teaser bearbeiten',
    'TITLE_TEASER_DEL' => 'Teaser löschen',
    'TITLE_TEASER_ENABLE' => 'Teaser aktivieren',
    'TITLE_TEASER_DISABLE' => 'Teaser deaktivieren',
    'TITLE_TEASER_MOVE' => 'Teaser anordnen',
    'TITLE_TEASER_GROUP' => 'Bannergruppen',
];

// NAVIGATION
$lang['navi'] = [
    'NAVI_TEASER_SHOW' => 'Teaser zeigen',
    'NAVI_TEASER_ADD' => 'Neuer Teaser',
    'NAVI_TEASER_GROUP' => 'Teasergruppen',
];

// ACTION EXPLICATION
$lang['expl'] = [
];

// LOG MESSAGES
$lang['log'] = [
    'LOG_TEASER_ADD' => 'Teaser hinzugefügt',
    'LOG_TEASER_EDIT' => 'Teaser bearbeitet',
    'LOG_TEASER_DEL' => 'Teaser gelöscht',
    'LOG_TEASER_ENABLE' => 'Teaser aktiviert',
    'LOG_TEASER_DISABLE' => 'Teaser deaktiviert',
];

// CONFIG
$lang['config'] = [
    'ORDERBY' => 'Teaser sortieren nach:',
    'ORDERADMIN' => 'Reihenfolge im Adminbereich festlegen',
    'ORDERPUB' => 'Nach Datum',
    'ORDERRANDOM' => 'Zufällige Reihenfolge',
];

// ACTIONS

//SHOW
$lang['actions']['show'] = [
    'COL_TITLE' => 'Titel',
    'COL_IMAGE' => 'Bild',
    'COL_HITS' => 'Klicks',
    'CHOOSE' => 'Nach einer Teasergruppe filtern',
    'USEDND' => 'Sie können die Einträge per Drag &amp; Drop anordnen',
    'NONE' => 'Bisher keine Teaser eingetragen!',
];

//ADD + EDIT
$lang['actions']['add'] = $lang['actions']['edit'] = [
    'SECTION' => 'Sektion',
    'ALLSEC' => 'Alle',
    'GROUP' => 'Gruppe',
    'TITLE' => 'Titel',
    'TEXT' => 'Text',
    'IMAGE' => 'Bild',
    'CURRENT' => 'Aktuelles Bild',
    'DELIMAGE' => 'Bild löschen',
    'NEWIMAGE' => 'Neues Bild',
    'LINK' => 'Link',
    'PUBNOW' => 'Sofort freischalten?',
    'PUBLICATION' => 'Veröffentlichung',
    'STARTTIME' => 'Veröffentlichen ab',
    'ENDTIME' => 'Automatisch widerrufen',
    'SUBMIT_ADD' => 'Teaser hinzufügen',
    'SUBMIT_EDIT' => 'Aktualisieren',
    'INFO_NOIMAGE' => 'Diese Datei ist kein gültiges Bild! Erlaubte Formate: GIF, JPG und PNG.',
];

//DEL
$lang['actions']['del'] = [
    'MSG_TEXT' => 'Wollen Sie den Teaser &quot;{TITLE}&quot; wirklich löschen?',
];

//ENABLE
$lang['actions']['enable'] = [
    'TITLE' => 'News',
    'STARTTIME' => 'Veröffentlichen ab',
    'ENDTIME' => 'Automatisch widerrufen',
    'SUBMIT' => 'Veröffentlichen',
];

//DISABLE
$lang['actions']['disable'] = [
    'MSG_TEXT' => 'Wollen Sie en Teaser &quot;{TITLE}&quot; wirklich widerrufen?',
    'DISABLE' => 'Widerrufen',
];

//GROUP
$lang['actions']['group'] = [
    'COL_TITLE' => 'Bezeichnung',
    'COL_TEASERS' => 'Teaseranzahl',
    'NONE' => 'Noch keine Teasergruppen erstellt!',
    'CATADD' => 'Teasergruppe erstellen',
    'CATEDIT' => 'Aktualisieren',
    'MSG_TEXT' => 'Soll die Teasergruppe &quot;{TITLE}&quot; wirklich gelöscht werden?',
];
