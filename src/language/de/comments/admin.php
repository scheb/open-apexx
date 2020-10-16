<?php

//
// German Language Pack
// ====================
//

// MODULE NAME
$lang['modulename']['MODULENAME_COMMENTS'] = 'Kommentarfunktion';

// HEADLINES
$lang['titles'] = [
    'TITLE_COMMENTS_SHOW' => 'Kommentare',
    'TITLE_COMMENTS_EDIT' => 'Kommentar bearbeiten',
    'TITLE_COMMENTS_DEL' => 'Kommentar löschen',
    'TITLE_COMMENTS_ENABLE' => 'Kommentar freischalten',
    'TITLE_COMMENTS_DISABLE' => 'Kommentar gesperrt',
    'TITLE_COMMENTS_BLOCKIP' => 'IPs sperren',
    'TITLE_COMMENTS_BLOCKCONTENT' => 'Verbotene Inhalte',
];

// NAVIGATION
$lang['navi'] = [
    'NAVI_COMMENTS_SHOW' => 'Kommentare zeigen',
];

// ACTION EXPLICATION
$lang['expl'] = [
];

// LOG MESSAGES
$lang['log'] = [
    'LOG_COMMENTS_EDIT' => 'Kommentar bearbeitet',
    'LOG_COMMENTS_DEL' => 'Kommentar gelöscht',
    'LOG_COMMENTS_ENABLE' => 'Kommentar freigeschaltet',
    'LOG_COMMENTS_DISABLE' => 'Kommentar widerrufen',
];

// CONFIG
$lang['config'] = [
    'VIEW' => 'Darstellung',
    'OPTIONS' => 'Einstellungen',
    'CAPTCHA' => 'Kommentar muss visuell bestätigt werden (Captcha)?',
    'ORDER' => 'Reihenfolge der Kommentare:',
    'NEWFIRST' => 'Neuste zuerst',
    'OLDFIRST' => 'Alte zuerst',
    'PUB' => 'Nicht angemeldeten Benutzern das schreiben von Kommentaren erlauben?',
    'MOD' => 'Kommentare müssen erst von einem Administrator freigeschaltet werden?',
    'MAXLEN' => 'Maximale Zeichenzahl eines Kommentars:',
    'BREAKLINE' => 'Erzwungener Zeilenumbruch nach X Zeichen:<br />(0 = aus)',
    'SPAMPROT' => 'Dauer in Minuten bis erneut ein Kommentar abgegeben werden kann:',
    'ALLOWSMILIES' => 'Smilies in den Kommentaren erlauben?',
    'ALLOWCODE' => 'Codes in den Kommentaren erlauben?',
    'BADWORDS' => 'Badword-Filter auf den Text anwenden?',
    'EPP' => 'Kommentare pro Seite:<br />(0 = alle anzeigen)',
    'POPUP' => 'Kommentare in einem Popup-Fenster anzeigen?',
    'POPUP_WIDTH' => 'Breite des Popup-Fensters:',
    'POPUP_HEIGHT' => 'Höhe des Popup-Fensters:',
    'REQ_EMAIL' => 'Feld "eMail" muss ausgefüllt werden?',
    'REQ_HOMEPAGE' => 'Feld "Homepage" muss ausgefüllt werden?',
    'REQ_TITLE' => 'Feld "Titel" muss ausgefüllt werden?',
    'MAILONNEW' => 'eMail an diese Adressen, wenn ein Kommentar abgegeben wurde (mehrere Adressen durch Kommas trennen):',
    'REPORTMAIL' => 'Verstoß-Meldungen an diese eMail-Adressen (mehrere Adressen durch Kommas trennen):',
];

// ACTIONS

//SHOW
$lang['actions']['show'] = [
    'COL_NAME' => 'Name',
    'COL_TEXT' => 'Text',
    'COL_IP' => 'IP-Adresse',
    'SORT_TIME' => 'Datum/Zeit',
    'BLOCK' => 'IP sperren',
    'NONE' => 'Bisher keine Kommentare erstellt!',
    'MULTI_DEL' => 'Löschen',
    'MULTI_ENABLE' => 'Freischalten',
    'MULTI_DISABLE' => 'Sperren',
];

//EDIT
$lang['actions']['edit'] = [
    'USERNAME' => 'Benutzername',
    'EMAIL' => 'eMail',
    'HOMEPAGE' => 'Homepage',
    'TITLE' => 'Titel',
    'TEXT' => 'Text',
    'CHARSLEFT' => 'Verbleibende Zeichen',
    'SUBMIT' => 'Aktualisieren',
    'INFO_TOOLONG' => 'Der Text ist länger als die Zeichenbeschränkung erlaubt!',
];

//DEL
$lang['actions']['del'] = [
    'MSG_TEXT' => 'Wollen Sie den Kommentar von &quot;{TITLE}&quot; wirklich löschen?',
];

//ENABLE
$lang['actions']['enable'] = [
];

//DISABLE
$lang['actions']['disable'] = [
];

//BLOCKIP
$lang['actions']['blockip'] = [
    'COL_IPRANGE' => 'IP / IP-Bereich',
    'NONE' => 'Keine gesperrten IPs!',
    'BLOCKIP' => 'IPs sperren',
    'ONEIP' => 'Einzelne IP',
    'IPRANGE' => 'IP-Bereich',
    'SUBMIT' => 'Hinzufügen',
    'MSG_DEL' => 'Wollen Sie den Eintrag &quot;{TITLE}&quot; wirklich aus der Sperrliste entfernen?',
];

//BLOCKSTRING
$lang['actions']['blockcontent'] = [
    'COL_STRING' => 'Verbotene Inhalte',
    'NONE' => 'Keine verbotenen Inhalte!',
    'BLOCKSTRING' => 'Zeichenkette verbieten',
    'STRING' => 'Zeichenkette',
    'JOKER' => '* als Jockerzeichen verwenden',
    'SUBMIT' => 'Hinzufügen',
    'MSG_DEL' => 'Wollen Sie den Eintrag &quot;{TITLE}&quot; wirklich aus der Sperrliste entfernen?',
];
