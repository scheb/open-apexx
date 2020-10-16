<?php 

#
# German Language Pack
# ====================
#


/************** MODULE NAME **************/
$lang['modulename']['MODULENAME_GUESTBOOK'] = 'Gästebuch';


/************** HEADLINES **************/
$lang['titles'] = array (
'TITLE_GUESTBOOK_SHOW' => 'Gästebuch-Einträge',
'TITLE_GUESTBOOK_EDIT' => 'Gästebuch-Eintrag bearbeiten',
'TITLE_GUESTBOOK_DEL' => 'Gästebuch-Eintrag löschen',
'TITLE_GUESTBOOK_ENABLE' => 'Gästebuch-Eintrag freischalten',
'TITLE_GUESTBOOK_DISABLE' => 'Gästebuch-Eintrag sperren',
'TITLE_GUESTBOOK_COM' => 'Gästebuch-Eintrag kommentieren',
'TITLE_GUESTBOOK_BLOCKIP' => 'Gesperrte IPs',
'TITLE_GUESTBOOK_BLOCKCONTENT' => 'Verbotene Inhalte'
);


/************** NAVIGATION **************/
$lang['navi'] = array (
'NAVI_GUESTBOOK_SHOW' => 'Einträge zeigen'
);


/************** ACTION EXPLICATION **************/
$lang['expl'] = array (

);


/************** LOG MESSAGES **************/
$lang['log'] = array (
'LOG_GUESTBOOK_EDIT' => 'Gästebuch-Eintrag bearbeitet',
'LOG_GUESTBOOK_DEL' => 'Gästebuch-Eintrag gelöscht',
'LOG_GUESTBOOK_ENABLE' => 'Gästebuch-Eintrag freigeschaltet',
'LOG_GUESTBOOK_DISABLE' => 'Gästebuch-Eintrag gesperrt',
'LOG_GUESTBOOK_COM' => 'Gästebuch-Eintrag kommentiert'
);


/************** CONFIG **************/
$lang['config'] = array (
'VIEW' => 'Darstellung',
'OPTIONS' => 'Einstellungen',
'CAPTCHA' => 'Eintrag muss visuell bestätigt werden (Captcha)?',
'MAXLEN' => 'Maximale Zeichenzahl eines Eintrags:',
'BREAKLINE' => 'Erzwungener Zeilenumbruch nach X Zeichen:<br />(0 = aus)',
'SPAMPROT' => 'Dauer in Minuten bis erneut ein Eintrag abgegeben werden kann:',
'MOD' => 'Einträge müssen erst von einem Administrator freigeschaltet werden?',
'ALLOWSMILIES' => 'Smilies in den Einträgen erlauben?',
'ALLOWCODE' => 'Codes in den Einträgen erlauben?',
'BADWORDS' => 'Badword-Filter auf den Text anwenden?',
'EPP' => 'Einträge pro Seite:',
'REQ_EMAIL' => 'Feld "eMail" muss ausgefüllt werden?',
'REQ_HOMEPAGE' => 'Feld "Homepage" muss ausgefüllt werden?',
'REQ_TITLE' => 'Feld "Titel" muss ausgefüllt werden?',
'CUSFIELD_NAMES' => 'Bezeichnungen der benutzerdefinierten Eingabefelder:<br />(maximal 5 Felder!)',
'MAILONNEW' => 'eMail an diese Adressen, wenn ein Eintrag gemacht wurde (mehrere Adressen durch Kommas trennen):'
);


/************** ACTIONS **************/

//SHOW
$lang['actions']['show'] = array (
'COL_NAME' => 'Name',
'COL_TEXT' => 'Text',
'COL_IP' => 'IP-Adresse',
'SORT_TIME' => 'Datum/Zeit',
'BLOCK' => 'IP sperren',
'ADDCOM' => 'Eintrag kommentieren',
'NONE' => 'Bisher keine Gästebuch-Einträge erstellt!',
'MULTI_DEL' => 'Löschen',
'MULTI_ENABLE' => 'Freischalten',
'MULTI_DISABLE' => 'Sperren'
);

//EDIT
$lang['actions']['edit'] = array (
'USERNAME' => 'Benutzername',
'EMAIL' => 'eMail',
'HOMEPAGE' => 'Homepage',
'TITLE' => 'Titel',
'TEXT' => 'Text',
'CHARSLEFT' => 'Verbleibende Zeichen',
'SUBMIT' => 'Aktualisieren',
'INFO_TOOLONG' => 'Der Text ist länger als die Zeichenbeschränkung erlaubt!'
);

//DEL
$lang['actions']['del'] = array (
'MSG_TEXT' => 'Wollen Sie den Gästebuch-Eintrag von &quot;{TITLE}&quot; wirklich löschen?'
);

//ENABLE
$lang['actions']['enable'] = array (

);

//DISABLE
$lang['actions']['disable'] = array (

);

//COM
$lang['actions']['com'] = array (
'USERNAME' => 'Benutzername',
'TEXT' => 'Text',
'DELCOM' => 'Kommentar löschen',
'SUBMIT' => 'Aktualisieren'
);

//BLOCKIP
$lang['actions']['blockip'] = array (
'COL_IPRANGE' => 'IP / IP-Bereich',
'NONE' => 'Keine gesperrten IPs!',
'BLOCKIP' => 'IPs sperren',
'ONEIP' => 'Einzelne IP',
'IPRANGE' => 'IP-Bereich',
'SUBMIT' => 'Hinzufügen',
'MSG_DEL' => 'Wollen Sie den Eintrag &quot;{TITLE}&quot; wirklich aus der Sperrliste entfernen?'
);


//BLOCKSTRING
$lang['actions']['blockcontent'] = array (
'COL_STRING' => 'Verbotene Inhalte',
'NONE' => 'Keine verbotenen Inhalte!',
'BLOCKSTRING' => 'Zeichenkette verbieten',
'STRING' => 'Zeichenkette',
'JOKER' => '* als Jockerzeichen verwenden',
'SUBMIT' => 'Hinzufügen',
'MSG_DEL' => 'Wollen Sie den Eintrag &quot;{TITLE}&quot; wirklich aus der Sperrliste entfernen?'
);

?>