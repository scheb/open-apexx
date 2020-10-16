<?php

//
// German Language Pack
// ====================
//

//LISTCOMS
$lang['comments'] = [
    'ICON_EMAIL' => 'eMail',
    'ICON_HOMEPAGE' => 'Homepage',
    'NONE' => 'Es wurden noch keine Kommentare abgegeben!',
    'WRITECOM' => 'Kommentar schreiben',
    'NAME' => 'Name',
    'EMAIL' => 'eMail',
    'HOMEPAGE' => 'Homepage',
    'TITLE' => 'Titel',
    'TEXT' => 'Text',
    'NOTIFY' => 'Benachrichtige mich bei einem neuen Kommentar per eMail',
    'CAPTCHA' => 'Visuelle Bestätigung',
    'COMMENT_REGONLY' => 'Um Kommentare zu schreiben, müssen Sie angemeldet sein!',
    'COMMENT_REPORT' => 'Kommentar melden',
    'SUBMITCOMMENT' => 'Absenden',
];

//ADDCOM
$lang['add'] = [
    'MAIL_SENDCOM_TITLE' => 'Neuer Kommentar',
    'MAIL_SENDCOM_TEXT' => "Hallo,\nsoeben wurde ein neuer Kommentar auf der Seite {URL} abgegeben:\n{GOTO}\n\nText:\n----------\n{TEXT}\n----------\n\napexx Mailbot",
    'MAIL_NOTIFYCOM_TITLE' => 'Neuer Kommentar',
    'MAIL_NOTIFYCOM_TEXT' => "Hallo,\nsoeben wurde ein neuer Kommentar auf der Seite {URL} abgegeben:\n{GOTO}\n\nSie erhalten diese eMail, weil Sie die Benachrichtigung aktiviert haben.",
    'MSG_COM_MAILNEEDED' => 'Um benachrichtigt zu werden, müssen Sie eine eMail-Adresse angeben!',
    'MSG_COM_TOOLONG' => 'Ihr Text ist zu lang! Bitte fassen Sie sich kürzer.',
    'MSG_COM_BLOCKSPAM' => 'Die Spamsperre ist noch {SEC} Sekunden aktiv!',
    'MSG_COM_BLOCKIP' => 'Eintragung nicht möglich! Ihre IP-Adresse ist gesperrt!',
    'MSG_COM_BLOCKTEXT' => 'Eintragung nicht möglich! Ihr Text enthält verbotene Zeichenketten.',
    'MSG_COM_EMAILNOTVALID' => 'Das ist keine gültige eMail-Adresse!',
    'MSG_COM_WRONGCODE' => 'Der angegebene Bestätigungscode ist nicht korrekt!',
    'MSG_COM_OK' => 'Der Kommentar wurde eingetragen! Sie werden nun weitergeleitet...',
];

//REPORT
$lang['report'] = [
    'HEADLINE_REPORT' => 'Verstoß melden',
    'REASON' => 'Grund',
    'SUBMIT' => 'Absenden',
    'MAIL_REPORT_TITLE' => 'Verstoß gemeldet',
    'MAIL_REPORT_TEXT' => "Hallo,\nder folgende Kommentar wurde gemeldet:\n\n------\n{TEXT}\n------\n{URL}\n\nBegründung:\n{REASON}\n\napexx Mailbot",
    'MSG_OK' => 'Danke für diesen Hinweis!',
];

//STATS
$lang['func_stats'] = [
    'COMMENTS' => 'Kommentare',
];

//COMMENTCOUNT
$lang['commentcount'] = [
    'COMMENTS' => 'Kommentare',
];
