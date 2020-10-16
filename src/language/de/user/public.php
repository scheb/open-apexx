<?php

//
// German Language Pack
// ====================
//

//Wird immer geladen
$lang['all'] = [
    'HEADLINE' => 'Benutzersystem',
    'VISITORS' => 'Letzte Besucher',
    'REPORTTHIS' => 'Verstoß melden',
    'USERNAME' => 'Benutzername',
    'PASSWORD' => 'Passwort',
    'SUBMIT_LOGIN' => 'Anmelden',
    'MSG_REGONLY' => 'Benutzerprofile können nur von registrierten Benutzern eingesehen werden!',
    'MSG_FRIENDSONLY' => 'Das Profil kann nur von den Freunden des Benutzers eingesehen werden!',
];

//Icon-Beschriftung
$lang['icons'] = [
    'ICON_PROFILE' => 'Profil ansehen',
    'ICON_SENDMAIL' => 'eMail senden',
    'ICON_SENDPM' => 'Nachricht senden',
    'ICON_ADDBUDDY' => 'Zur Freundesliste',
    'ICON_DELBUDDY' => 'Freund löschen',
    'ICON_DELBOOKMARK' => 'Bookmark löschen',
    'ICON_ONLINE' => 'Online',
    'ICON_OFFLINE' => 'Offline',
    'ICON_ASC' => 'Aufsteigend',
    'ICON_DESC' => 'Absteigend',
];

//Profil-Felder: Registrieren, Profil zeigen, Profil bearbeiten
$lang['profile_field'] = [
    'MINLENGTH' => 'Mindestlänge',
    'CHARS' => 'Zeichen',
    'REQ' => 'Benötigte Angaben',
    'ADDNL' => 'Optionale Angaben',
    'PERSONAL' => 'Persönliche Angaben',
    'CONTACT' => 'Kontaktmöglichkeiten',
    'OPTIONS' => 'Einstellungen',
    'CAPTCHA' => 'Visuelle Bestätigung',
    'USERID' => 'Benutzer-ID',
    'USERNAME' => 'Benutzername',
    'PWD' => 'Passwort',
    'REPEAT' => 'Wiederholung',
    'EMAIL' => 'eMail',
    'GROUP' => 'Benutzergruppe',
    'LASTACTIVE' => 'Zuletzt aktiv',
    'COMMENTS' => 'Kommentare',
    'FORUM' => 'Forum',
    'POSTINGS' => 'Beiträge',
    'FINDPOSTS' => 'Beiträge finden',
    'STATUS' => 'Status',
    'SENDEMAIL' => 'eMail senden',
    'SENDPM' => 'Nachricht senden',
    'ADDBUDDY' => 'Zur Freundesliste hinzufügen',
    'ADDIGNORE' => 'Zur Ignorier-Liste hinzufügen',
    'HOMEPAGE' => 'Website',
    'REALNAME' => 'Echter Name',
    'GENDER' => 'Geschlecht',
    'SECRET' => 'geheim',
    'MALE' => 'männlich',
    'FEMALE' => 'weiblich',
    'BDAY' => 'Geburtstag',
    'COUNTRY' => 'Land',
    'CITY' => 'Ort',
    'INTERESTS' => 'Interessen',
    'WORK' => 'Beruf',
    'INVISIBLE' => 'Unsichtbar sein',
    'HIDEMAIL' => 'eMail-Adresse verstecken',
    'POPPM' => 'Popup bei neuer Nachricht',
    'MAILPM' => 'eMail-Benachrichtigung bei neuer Nachricht',
    'SHOWBUDDIES' => 'Freundesliste im Profil anzeigen',
    'USEGB' => 'Gästebuch-Modus',
    'GBENABLED' => 'aktiviert',
    'GBDISABLED' => 'deaktiviert',
    'GBFRIENDS' => 'nur für meine Freunde',
    'GBMAIL' => 'eMail-Benachrichtigung bei neuem Gästebuch-Eintrag',
    'PROFILEFORFRIENDS' => 'Profil nur meinen Freunden zeigen',
    'AUTOSUBSCRIBE' => 'Themen im Forum automatisch abonnieren',
    'LANG' => 'Sprache',
    'THEME' => 'Website-Stil',
    'USEDEFAULT' => 'Standard verwenden',
];

//Benutzer-Funktionen
$lang['profile_global'] = [
    'USER_PROFILE' => 'Profil',
    'USER_BLOG' => 'Blog',
    'USER_GUESTBOOK' => 'Gästebuch',
    'USER_GALLERY' => 'Galerien',
    'USER_COLLECTION' => 'Sammlung',
];

////////////////////////////////////////////////////////////////////////////////////////// -> AKTIONEN

//Login
$lang['login'] = [
    'HEADLINE_LOGIN' => 'Anmeldung',
    'USER' => 'Benutzername',
    'PWD' => 'Passwort',
    'SUBMIT' => 'Anmelden',
    'REGISTER' => 'Registrieren',
    'GETPWD' => 'Passwort vergessen?',
    'GETKEY' => 'Aktivierungs-Key anfordern',
    'MSG_ADMINACTIVATION' => 'Ihr Account muss erst von einem Administrator freigeschaltet werden!',
    'MSG_FAIL' => 'Anmeldung fehlgeschlagen! Benutzername und Passwort stimmen nicht überein!',
    'MSG_BLOCK' => 'Sie haben sich fünf mal falsch angemeldet! Ihr Account ist für 15 Minuten gesperrt.',
    'MSG_BANNED' => 'Anmeldung fehlgeschlagen! Ihr Account wurde von einem Administrator gesperrt.',
    'MSG_NOTACTIVE' => 'Anmeldung fehlgeschlagen! Sie müssen Ihren Account erst mit dem Aktivierungs-Code freischalten.',
    'MSG_OK' => 'Anmeldung erfolgreich! Sie werden nun weitergeleitet...',
];

//Logout
$lang['logout'] = [
    'MSG_OK' => 'Abmeldung erfolgreich! Sie werden nun weitergeleitet...',
];

//Registrieren
$lang['register'] = array_merge($lang['profile_field'], [
    'HEADLINE_REGISTER' => 'Registrieren',
    'RULES' => '[Keine Regeln]',
    'ACCEPT' => 'Akzeptieren',
    'DECLINE' => 'Ablehnen',
    'SUBMIT' => 'Registrieren',
    'MSG_USERLENGTH' => 'Der Benutzername muss mindestens {LENGTH} Zeichen lang sein!',
    'MSG_PWDLENGTH' => 'Das Passwort muss mindestens {LENGTH} Zeichen lang sein!',
    'MSG_PWNOMATCH' => 'Passwort und Passwort-Wiederholung stimmen nicht überein!',
    'MSG_EMAILNOMATCH' => 'eMail und eMail-Wiederholung stimmen nicht überein!',
    'MSG_USEREXISTS' => 'Unter diesem Benutzernamen existiert bereits ein Account!',
    'MSG_USERNOTALLOWED' => 'Ihr Benutzername enthält die verbotene Zeichenfolge "{STRING}"!',
    'MSG_MAILEXISTS' => 'Unter dieser eMail-Adresse existiert bereits ein Account!',
    'MSG_NOMAIL' => 'Die angegebene eMail-Adresse ist nicht gültig!',
    'MSG_WRONGCODE' => 'Der angegebene Bestätigungscode ist nicht korrekt!',
    'MSG_OK' => 'Registrierung erfolgreich! Sie werden nun zur Anmeldung weitergeleitet...',
    'MSG_OK_ADMINACTIVATE' => 'Registrierung erfolgreich! Bevor Sie Ihren Account nutzen können, muss er erst von einen Administrator freigeschaltet werden.',
    'MSG_OK_ACTIVATE' => 'Registrierung erfolgreich! Bevor Sie Ihren Account nutzen können, müssen Sie ihn erst mit dem Code aktivieren, der Ihnen per eMail zugeschickt wurde.',
    'MAIL_NEWREG_TITLE' => 'Benutzer registriert',
    'MAIL_NEWREG_TEXT' => "Hallo,\nsoeben hat sich auf der Seite {URL} ein neuer Benutzer mit Namen \"{USERNAME}\" registriert.\n\napexx Mailbot",
    'MAIL_REG_TITLE' => 'Registrierung',
    'MAIL_REG_TEXT' => "Hallo {USERNAME},\nSie haben sich soeben auf der Website {WEBSITE} registriert, hier sind Ihre Benutzerdaten:\n\nBenutzername: {USERNAME}\nPasswort:     {PASSWORD}\n\nGrüße, das Team von {WEBSITE}",
    'MAIL_REGACTIVATION_TITLE' => 'Registrierung',
    'MAIL_REGACTIVATION_TEXT' => "Hallo {USERNAME},\nSie haben sich soeben auf der Website {WEBSITE} registriert, hier sind Ihre Benutzerdaten:\n\nBenutzername: {USERNAME}\nPasswort:     {PASSWORD}\n\nUm den Account nutzen zu können, müssen Sie ihn zunächst aktivieren. Klicken Sie dazu auf diese URL: {URL}\n\nGrüße, Team von {WEBSITE}",
    'MAIL_REGADMINACTIVATION_TITLE' => 'Registrierung',
    'MAIL_REGADMINACTIVATION_TEXT' => "Hallo {USERNAME},\nSie haben sich soeben auf der Website {WEBSITE} registriert, hier sind Ihre Benutzerdaten:\n\nBenutzername: {USERNAME}\nPasswort:     {PASSWORD}\n\nIhr Account muss erst von einem Administrator freigeschaltet werden, damit Sie ihn nutzen können.\n\nGrüße, Team von {WEBSITE}",
]);

//Passwort anfordern + Aktivierungs-Key anfordern
$lang['getpwd'] = $lang['getregkey'] = [
    'HEADLINE_GETPWD' => 'Passwort anfordern',
    'HEADLINE_GETREGKEY' => 'Aktivierungs-Code anfordern',
    'USERNAME' => 'Benutzername',
    'EMAIL' => 'eMail',
    'SUBMIT_PWD' => 'Neues Passwort anfordern',
    'SUBMIT_REGKEY' => 'Code anfordern',
    'MSG_NOMATCH' => 'Benutzername nicht gefunden!',
    'MSG_NOTALLOWED' => 'Der Bestätigungscode ist nicht korrekt!',
    'MSG_ISACTIVE' => 'Ihr Benutzer-Account ist bereits aktiviert!',
    'MSG_OK_PWD' => 'Ein neues Passwort wurde Ihnen soeben zugeschickt! Sie werden nun weitergeleitet...',
    'MSG_OK_PWDREQ' => 'Es wurde ein Bestätigungslink an {EMAIL} geschickt. Sie werden nun weitergeleitet...',
    'MSG_OK_REGKEY' => 'Ihr Aktivierungs-Code wurde Ihnen soeben zugeschickt! Sie werden nun weitergeleitet...',
    'MAIL_GETPWD_TITLE' => 'Neues Passwort erstellt',
    'MAIL_GETPWD_TEXT' => "Hallo {USERNAME},\nein neues Passwort wurde für Sie erzeugt.\n\nPasswort: {PWD}\n\nGrüße, das Team von {WEBSITE}",
    'MAIL_GETPWDREQ_TITLE' => 'Neues Passwort angefordert',
    'MAIL_GETPWDREQ_TEXT' => "Hallo {USERNAME},\nSie haben soeben auf {WEBSITE} ein neues Passwort angefordert. Bitte bestätigen Sie mit dem folgenden Link: {URL}\nFalls Sie das Passwort nicht angefordert haben, ignorieren Sie diese eMail einfach.\n\nGrüße, das Team von {WEBSITE}",
    'MAIL_GETKEY_TITLE' => 'Ihr Aktivierungs-Code',
    'MAIL_GETKEY_TEXT' => "Hallo {USERNAME},\nSie haben soeben auf {WEBSITE} ihren Aktivierungskey erneut angefordert. Klicken Sie dazu auf diese URL: {URL}\n\nGrüße, das Team von {WEBSITE}",
];

//Account aktivieren
$lang['activate'] = [
    'MSG_ISACTIVE' => 'Dieser Benutzer-Account ist bereits aktiviert!',
    'MSG_WRONGKEY' => 'Der Aktivierung-Code ist falsch, wenden Sie sich an einen Administrator!',
    'MSG_OK' => 'Der Benutzer-Account wurde aktiviert! Sie werden nun weitergeleitet...',
];

//Userliste
$lang['userlist'] = array_merge($lang['icons'], [
    'HEADLINE_USERLIST' => 'Registrierte Benutzer',
    'SEARCHUSER' => 'Benutzer suchen',
    'USERCOUNT' => 'Registrierte Benutzer gesamt',
    'TODAYNEW' => 'Neue Registrierungen heute',
    'USERNAME' => 'Benutzername',
    'FORUMPOSTS' => 'Forumbeiträge',
    'REGSINCE' => 'Registriert seit...',
    'OPTIONS' => 'Optionen',
    'DAYS' => 'Tage',
    'SORTBY' => 'Sortieren nach',
    'NONE' => 'Keine Benutzer gefunden!',
    'SORT_USERNAME' => 'Benutzername',
    'SORT_REGDATE' => 'Registrierung',
]);

//Suche
$lang['search'] = [
    'HEADLINE_SEARCH' => 'Benutzersuche',
    'ITEM' => 'Stichwort/Name',
    'AGE' => 'Alter',
    'YEARS' => 'Jahre',
    'GENDER' => 'Geschlecht',
    'NOGENDER' => 'egal',
    'MALE' => 'männlich',
    'FEMALE' => 'weiblich',
    'CITY' => 'Wohnort',
    'ONLINE' => 'Nur Online-User?',
    'DISTANCE' => 'Entfernung',
    'STARTSEARCH' => 'Suche starten',
    'SELECT' => 'Auswählen',
    'MSG_CHOOSECITY' => 'Die Ortsangabe ist nicht eindeutig! Bitte wählen Sie:',
    'MSG_NOCITY' => 'Kein Ort mit diesen Angaben gefunden!',
    'MSG_NORESULT' => 'Die Suche lieferte kein Ergebnis!',
];

//Online-Liste
$lang['onlinelist'] = array_merge($lang['icons'], [
    'HEADLINE_ONLINELIST' => 'Wer ist online?',
    'USERNAME' => 'Benutzer',
    'LASTACTIVE' => 'Zuletzt aktiv',
    'NONE' => 'Kein registrierter Benutzer online',
    'TOTAL' => 'Benutzer online',
    'USERS' => 'Registrierte',
    'GUESTS' => 'Gäste',
    'INV' => 'Geister',
]);

//Teamseite
$lang['team'] = array_merge($lang['icons'], [
    'HEADLINE' => 'Team',
    'USERNAME' => 'Benutzer',
    'LASTACTIVE' => 'Zuletzt aktiv',
    'FORUM_MOD' => 'Forum-Moderatoren',
]);

//Online-Liste
$lang['usermap'] = [
    'HEADLINE_USERMAP' => 'Mitgliederkarte',
];

//Index
$lang['index'] = [
    'WELCOME' => 'Willkommen im Benutzer-Center, ',
    'PROFILE' => 'Profil bearbeiten',
    'SHOWPROFILE' => 'Profil betrachten',
    'SIGNATURE' => 'Signatur bearbeiten',
    'AVATAR' => 'Avatar hochladen',
    'MESSAGES' => 'Persönliche Nachrichten',
    'IGNORELIST' => 'Ignorier-Liste verwalten',
    'FRIENDS' => 'Freundesliste anzeigen',
    'NEWMESSAGE' => 'Neue Nachricht',
    'NEWMAIL' => 'Neue eMail',
    'SUBSCRIPTIONS' => 'Forum-Abonnements',
    'BLOG' => 'Blog verwalten',
    'GALLERY' => 'Galerien verwalten',
    'SHOWBLOG' => 'Blog ansehen',
    'SHOWGALLERY' => 'Galerien ansehen',
    'SHOWGUESTBOOK' => 'Gästebuch ansehen',
    'SHOWCOLLECTION' => 'Meine Sammlung ansehen',
    'LOGOUT' => 'Abmelden',
];

//Eigenes Profil
$lang['myprofile'] = array_merge($lang['profile_field'], [
    'HEADLINE_MYPROFILE' => 'Profil bearbeiten',
    'SUBMIT' => 'Profil aktualisieren',
    'CONFIRMAGE' => 'Alter bestätigen',
    'MSG_IDENT_INCOMPLETE' => 'Die Personalausweis-Nummer ist nicht vollständig!',
    'MSG_IDENT_INVALIDCHARS' => 'Die Personalausweis-Nummer darf nur aus Ziffern bestehen!',
    'MSG_IDENT_INVALID' => 'Die Personalausweis-Nummer ist ungültig!',
    'MSG_IDENT_EXPIRED' => 'Der Personalausweis ist nicht mehr gültig!',
    'MSG_IDENT_TOOYOUNG' => 'Sie sind {AGE} Jahre alt und somit nicht berechtigt für Inhalte ab 18 Jahren!',
    'MSG_PWDLENGTH' => 'Das Passwort muss mindestens {LENGTH} Zeichen lang sein!',
    'MSG_PWNOMATCH' => 'Passwort und Passwort-Wiederholung stimmen nicht überein!',
    'MSG_USEREXISTS' => 'Unter diesem Benutzernamen existiert bereits ein Account!',
    'MSG_NOMAIL' => 'Die angegebene eMail-Adresse ist nicht gültig!',
    'MSG_OK' => 'Ihr Profil wurde aktualisiert! Sie werden nun weitergeleitet...',
    'MSG_OK_NEWPWD' => 'Ihr Profil wurde aktualisiert! Sie werden nun automatisch abgemeldet, weil Sie ihr Passwort geändert haben...',
    'MSG_OK_NEWEMAIL' => 'Ihr Profil wurde aktualisiert! Sie müssen Ihren Account erst reaktivieren, da Sie Ihre eMail-Adresse geändert haben.',
    'MAIL_GETKEY_TITLE' => 'Ihr Aktivierungs-Code',
    'MAIL_GETKEY_TEXT' => "Hallo {USERNAME},\nSie haben soeben auf {WEBSITE} Ihre eMail-Adresse geändert, daher müssen Sie Ihren Account erst reaktivieren. Klicken Sie dazu auf diese URL: {URL}\n\nGrüße,das Team von {WEBSITE}",
]);

//Profil zeigen
$lang['profile'] = array_merge($lang['profile_global'], $lang['profile_field'], [
    'HEADLINE_PROFILE' => 'Benutzerprofil',
    'ACCOUNT' => 'Account',
    'IDENT' => 'Erkennungsmerkmale',
    'REGSINCE' => 'Registriert seit',
    'DAYS' => 'Tage',
    'SIGNATURE' => 'Signatur',
    'AVATAR' => 'Avatar',
    'BUDDIES' => 'Freunde',
]);

//Gästebuch
$lang['guestbook'] = array_merge($lang['profile_global'], [
    'HEADLINE_GUESTBOOK' => 'Benutzer-Gästebuch',
    'COL_NAME' => 'Name',
    'COL_TEXT' => 'Kommentar',
    'ICON_EMAIL' => 'eMail senden',
    'ICON_HOMEPAGE' => 'Homepage besuchen',
    'NONE' => 'Keine Einträge vorhanden!',
    'SIGNGB' => 'Eintragen',
    'NAME' => 'Name',
    'TITLE' => 'Titel',
    'TEXT' => 'Text',
    'SUBMIT' => 'Eintragen',
    'DELENTRY' => 'Löschen',
    'MAIL_SENDENTRY_TITLE' => 'Neuer Gästebuch-Eintrag',
    'MAIL_SENDENTRY_TEXT' => "Hallo,\nsoeben wurde in Ihrem Benutzer-Gästebuch ein neuer Eintrag von {USERNAME} erstellt:\n{GOTO}\n\napexx Mailbot",
    'MSG_IGNORED' => 'Eintragen nicht möglich! Der Benutzer ignoriert Sie.',
    'MSG_IGNORED_REASON' => 'Eintragen nicht möglich! Der Benutzer ignoriert Sie mit dem Grund: {REASON}',
    'MSG_DISABLED' => 'Der Benutzer hat sein Gästebuch deaktiviert!',
    'MSG_FRIENDSONLY' => 'Der Benutzer hat sein Gästebuch nur für Freunde zugänglich gemacht!',
    'MSG_TOOLONG' => 'Ihr Text ist zu lang! Bitte fassen Sie sich kürzer.',
    'MSG_BLOCKSPAM' => 'Die Spamsperre ist noch {SEC} Sekunden aktiv!',
    'MSG_TEXT' => 'Wollen Sie diesen Eintrag wirklich löschen?',
    'MSG_DEL_OK' => 'Der Eintrag wurde gelöscht! Sie werden nun weitergeleitet...',
    'MSG_OK' => 'Danke für Ihren Eintrag im Gästebuch! Sie werden nun weitergeleitet...',
]);

//Blog
$lang['blog'] = array_merge($lang['profile_global'], [
    'HEADLINE_BLOG' => 'Benutzer-Blog',
    'COMMENTS' => 'Kommentare',
]);

//Galerie
$lang['gallery'] = array_merge($lang['profile_global'], [
    'HEADLINE_GALLERY' => 'Benutzer-Galerien',
    'PICTURES' => 'Bilder',
    'HITS' => 'Klicks',
    'COMMENTS' => 'Kommentare',
    'MSG_PWDREQUIRED' => 'Diese Galerie ist durch ein Passwort geschützt!',
    'SUBMITPWD' => 'Passwort senden',
]);

//Signatur
$lang['report'] = [
    'HEADLINE_REPORT' => 'Verstoß melden',
    'REASON' => 'Grund',
    'SUBMIT' => 'Absenden',
    'MAIL_REPORT_TITLE' => 'Verstoß gemeldet',
    'MAIL_REPORT_TEXT' => "Hallo,\nder folgenden Beitrag wurde gemeldet:\n{URL}\n\nBegründung:\n{REASON}\n\napexx Mailbot",
    'MSG_OK' => 'Danke für diesen Hinweis!',
];

//Signatur
$lang['signature'] = [
    'HEADLINE_SIGNATURE' => 'Signatur bearbeiten',
    'SIGPREVIEW' => 'Vorschau',
    'SIGNATURE' => 'Signatur',
    'CHARSLEFT' => 'Verbleibende Zeichen',
    'SUBMIT' => 'Aktualisieren',
    'PREVIEW' => 'Vorschau zeigen',
    'MSG_SIGTOOLONG' => 'Ihre Signatur ist zu lang!',
    'MSG_OK' => 'Ihre Signatur wurde aktualisiert! Sie werden nun weitergeleitet...',
];

//Avatar hochladen
$lang['avatar'] = [
    'HEADLINE_AVATAR' => 'Avatar hochladen',
    'CURRENT' => 'Aktueller Avatar',
    'AVATAR' => 'Avatar',
    'EDITAVATAR' => 'Avatar bearbeiten',
    'DELAVATAR' => 'Avatar Löschen',
    'CHOOSEFILE' => 'Neuen Avatar hochladen',
    'AVTITLE' => 'Avatar-Titel',
    'MAX_FILESIZE' => 'Maximale Dateigröße',
    'MAX_DIMENSIONS' => 'Maximale Höhe/Breite',
    'MSG_MAXSIZE' => 'Der Avatar ist größer als die erlaubte Datenmenge!',
    'MSG_MAXDIM' => 'Der Avatar ist breiter oder höher als die erlaubte Größe!',
    'MSG_NOTALLOWED' => 'Dieser Dateityp ist für einen Avatar ungültig!',
    'MSG_OK' => 'Ihr Avatar wurde aktualisiert! Sie werden nun weitergeleitet...',
    'SUBMIT' => 'Aktualisieren',
];

//PNs zeigen
$lang['pms'] = [
    'HEADLINE_PMS' => 'Persönliche Nachrichten',
    'NEWPM' => 'Neue Nachricht',
    'PMSPACE' => 'Speicher',
    'FREE' => 'frei',
    'INBOX' => 'Posteingang',
    'OUTBOX' => 'Postausgang',
    'SUBJECT' => 'Betreff',
    'SENDER' => 'Absender',
    'RECIEVER' => 'Empfänger',
    'DATE' => 'Datum/Zeit',
    'NONE' => 'Keine Nachrichten gefunden!',
    'DELPMS' => 'Nachrichten löschen',
    'SORTBY' => 'Sortieren nach',
    'SORT_SUBJECT' => 'Betreff',
    'SORT_USERNAME' => 'Benutzer',
    'SORT_TIME' => 'Datum/Zeit',
];

//Neue Nachricht
$lang['newpm'] = [
    'HEADLINE_NEWPM' => 'Neue Nachricht',
    'RECIEVER' => 'An Benutzer',
    'SEARCH' => 'Benutzer suchen',
    'SUBJECT' => 'Betreff',
    'TEXT' => 'Text',
    'ADDSIG' => 'Signatur anfügen',
    'PREVIEW' => 'Vorschau',
    'MSG_NOTEXISTS' => 'Unter diesem Namen existiert kein Benutzeraccount!',
    'MSG_SELF' => 'Sie können sich selbst keine Nachrichten schicken!',
    'MSG_OWNFULL' => 'Sie können derzeit keine Nachrichten verschicken! Ihr Postfach ist voll.',
    'MSG_FULL' => 'Die Nachricht konnte nicht verschickt werden! Das Postfach des Empfägers ist voll.',
    'MSG_OK' => 'Die Nachricht wurde verschickt! Sie werden nun weitergeleitet...',
    'MSG_IGNORED' => 'Nachricht kann nicht verschickt werden! Der Benutzer ignoriert Sie.',
    'MSG_IGNORED_REASON' => 'Nachricht kann nicht verschickt werden! Der Benutzer ignoriert Sie mit dem Grund: {REASON}',
    'MAIL_FULL_TITLE' => 'Postfach voll!',
    'MAIL_FULL_TEXT' => "Ihr Postfach auf der Website {WEBSITE} ist voll, {USERNAME} wollte Ihnen gerade eine Nachricht zusenden! Löschen Sie alte Nachrichten damit Sie wieder neue empfangen können!\n\nGruß\nDas Team von {WEBSITE}",
    'MAIL_NEWPM_TITLE' => 'Neue Nachricht!',
    'MAIL_NEWPM_TEXT' => "Sie haben auf der Website {WEBSITE} soeben eine Nachricht von {USERNAME} erhalten. Klicken Sie hier um zu Ihrem Posteingang zu gelangen: {INBOX}\n\n------\n\nBetreff: {SUBJECT}\n\n{TEXT}\n\n------\n\nGruß\nDas Team von {WEBSITE}",
    'SUBMIT' => 'Abschicken',
];

//Neue eMail
$lang['newmail'] = [
    'HEADLINE_NEWMAIL' => 'Neue eMail',
    'NAME' => 'Ihr Name',
    'EMAIL' => 'Ihre eMail-Adresse',
    'RECIEVER' => 'An Benutzer',
    'SEARCH' => 'Benutzer suchen',
    'SUBJECT' => 'Betreff',
    'TEXT' => 'Text',
    'CAPTCHA' => 'Visuelle Bestätigung',
    'SUBMIT' => 'Abschicken',
    'MSG_WRONGCODE' => 'Der angegebene Bestätigungscode ist nicht korrekt!',
    'MSG_NOEMAIL' => 'Die angegebene Adresse ist keine gültige eMail-Adresse!',
    'MSG_IGNORED' => 'eMail kann nicht verschickt werden! Der Benutzer ignoriert Sie.',
    'MSG_IGNORED_REASON' => 'eMail kann nicht verschickt werden! Der Benutzer ignoriert Sie mit dem Grund: {REASON}',
    'MSG_NOTEXISTS' => 'Unter diesem Namen existiert kein Benutzeraccount!',
    'MSG_SELF' => 'Sie können sich selbst keine eMail schicken!',
    'MSG_OK' => 'Die eMail wurde verschickt! Sie werden nun weitergeleitet...',
];

//PN lesen
$lang['readpm'] = [
    'HEADLINE_READPM' => 'Nachricht lesen',
    'DELETE' => 'Löschen',
    'ANSWER' => 'Antworten',
    'IGNORE' => 'Benutzer ignorieren',
];

//PN löschen
$lang['delpm'] = [
    'MSG_TEXT' => 'Wollen Sie diese Nachricht wirklich löschen?',
    'DELPM' => 'Löschen',
    'MSG_OK' => 'Die Nachricht wurde gelöscht! Sie werden nun weitergeleitet...',
];

//Freundesliste
$lang['friends'] = [
    'HEADLINE_FRIENDS' => 'Freundesliste',
    'NONE' => 'Keine Freunde',
];

//Ignorier-Liste
$lang['ignorelist'] = [
    'HEADLINE_IGNORELIST' => 'Ignorier-Liste',
    'ADDUSER' => 'Benutzer hinzufügen',
    'USERNAME' => 'Benutzername',
    'REASON' => 'Begründung',
    'OPTIONAL' => 'optional',
    'DELETE' => 'Von der Liste entfernen',
    'NONE' => 'Keine Benutzer ignoriert!',
    'MSG_TEXT' => 'Wollen Sie diesen Benutzer von der Ignorier-Liste entfernen?',
    'MSG_NOMATCH' => 'Es wurde kein Benutzer mit diesem Namen gefunden!',
    'MSG_NOTSELF' => 'Sie können sich selbst nicht auf die Ignorier-Liste setzen!',
    'MSG_EXISTS' => 'Dieser Benutzer befindet sich bereits auf der Ignorier-Liste!',
    'MSG_ADD_OK' => 'Der Benutzer wurde hinzugefügt! Sie werden nun weitergeleitet...',
    'MSG_DEL_OK' => 'Der Benutzer wurde von der Liste entfernt! Sie werden nun weitergeleitet...',
];

//Buddy hinzufügen
$lang['addbuddy'] = [
    'ADDBUDDY' => 'Hinzufügen',
    'MAIL_NEWPM_TITLE' => 'Neue Kontaktanfrage!',
    'MAIL_NEWPM_TEXT' => "Hallo {USERNAME},\nSie haben auf der Website {WEBSITE} soeben eine Kontaktanfrage von {SENDER} erhalten. Klicken Sie hier um zu Ihrem Posteingang zu gelangen: {INBOX}\n\nGruß\nDas Team von {WEBSITE}",
    'PM_TITLE' => 'Kontaktanfrage',
    'PM_TEXT' => "Hallo {USERNAME},\n{SENDER} möchte Sie auf {WEBSITE} in seine Freundesliste aufnehmen. Um zu bestätigen [url={URL}]klicken Sie einfach hier[/url]. {SENDER} wird dann auch automatisch in Ihre Freundesliste aufgenommen.",
    'MAIL_FINISHED_TITLE' => 'Kontaktanfrage bestätigt',
    'MAIL_FINISHED_TEXT' => "Hallo {USERNAME},\n{SENDER} hat Ihre Kontaktanfrage bestätigt und befindet sich nun in Ihrer Freundesliste.",
    'MSG_INVALIDKEY' => 'Der Bestätigungscode ist nicht korrekt!',
    'MSG_IGNORED' => 'Hinzufügen nicht möglich! Der Benutzer ignoriert Sie.',
    'MSG_IGNORED_REASON' => 'Hinzufügen nicht möglich! Der Benutzer ignoriert Sie mit dem Grund: {REASON}',
    'MSG_EXISTS' => 'Dieser Benutzer befindet sich bereits auf Ihrer Freundesliste!',
    'MSG_NOTEXISTS' => 'Dieser Benutzer existiert nicht!',
    'MSG_NOTSELF' => 'Sie können sich nicht selbst Ihrer Freundesliste hinzufügen!',
    'MSG_NOTSELFCONFIRM' => 'Sie können Ihre Freundschaftsanfrage nicht selbst bestätigen!',
    'MSG_TEXT' => 'Wollen Sie diesen Benutzer Ihrer Freundesliste hinzufügen?',
    'MSG_REQ_OK' => 'Die Kontaktanfrage wurde verschickt! Der Benutzer muss die Anfrage nun bestätigen.',
    'MSG_OK' => 'Der Freund wurde hinzugefügt! Sie werden nun weitergeleitet...',
];

//Buddy löschen
$lang['delbuddy'] = [
    'DELBUDDY' => 'Löschen',
    'MSG_TEXT' => 'Wollen Sie diesen Benutzer von Ihrer Freundesliste löschen?',
    'MSG_OK' => 'Der Freund wurde gelöscht! Sie werden nun weitergeleitet...',
];

//Bookmarks
$lang['bookmarks'] = [
    'ADDBOOKMARK' => 'Bookmark erstellen',
    'DELBOOKMARK' => 'Löschen',
    'TITLE' => 'Bezeichnung',
    'MSG_TEXT' => 'Wollen Sie dieses Bookmark wirklich löschen?',
    'MSG_OK_ADD' => 'Das Bookmark wurde erstellt! Sie werden nun weitergeleitet...',
    'MSG_OK_DEL' => 'Das Bookmark wurde gelöscht! Sie werden nun weitergeleitet...',
];

//Blog verwalten
$lang['myblog'] = [
    'HEADLINE_MYBLOG' => 'Blog verwalten',
    'TITLE' => 'Titel',
    'DATE' => 'Veröffentlichung',
    'TEXT' => 'Text',
    'ALLOWCOMS' => 'Kommentare erlauben?',
    'NONE' => 'Noch keine Blog-Einträge erstellt!',
    'NEWENTRY' => 'Eintrag erstellen',
    'EDITENTRY' => 'Eintrag bearbeiten',
    'DELBLOGENTRY' => 'Eintrag löschen',
    'PREVIEW' => 'Vorschau',
    'MSG_TEXT' => 'Soll dieser Blog-Eintrag wirklich gelöscht werden?',
    'MSG_ADD_OK' => 'Der Blog-Eintrag wurde erstellt! Sie werden nun weitergeleitet...',
    'MSG_EDIT_OK' => 'Der Blog-Eintrag wurde bearbeitet! Sie werden nun weitergeleitet...',
    'MSG_DEL_OK' => 'Der Blog-Eintrag wurde gelöscht! Sie werden nun weitergeleitet...',
];

//Galerie verwalten
$lang['mygallery'] = [
    'HEADLINE_MYGALLERY' => 'Galerien verwalten',
    'PICSPACE' => 'Speicher',
    'FREE' => 'frei',
    'ADDGALLERY' => 'Neue Galerie',
    'EDITGALLERY' => 'Galerie bearbeiten',
    'DELGALLERY' => 'Galerie löschen',
    'DELPICTURE' => 'Bild löschen',
    'UPLOADIMAGES' => 'Bilder hochladen',
    'MAXPICS' => 'Verbleibende',
    'TITLE' => 'Titel',
    'DESCRIPTION' => 'Beschreibung',
    'PASSWORD' => 'Passwort',
    'OPTIONAL' => 'optional',
    'ALLOWCOMS' => 'Kommentare erlauben?',
    'CAPTION' => 'Bildunterschrift',
    'PICTURE' => 'Bild',
    'PICTURECOUNT' => 'Anzahl Bilder',
    'NONE' => 'Noch keine Galerien erstellt!',
    'NONE_PICS' => 'Noch keine Bilder hochgeladen!',
    'EDITCAPTION' => 'Bildunterschrift bearbeiten',
    'UPDATE' => 'Aktualisieren',
    'MSG_PICLIMIT' => 'Upload nicht möglich! Die Kapazität der Galerien ({LIMIT} Bilder gesamt) wird überschritten.',
    'MSG_PICLIMITREACHED' => 'Sie haben das Bild-Limit ({LIMIT} Bilder gesamt) erreicht!',
    'MSG_DELPICTURE' => 'Soll dieses Bild wirklich gelöscht werden?',
    'MSG_DELGALLERY' => 'Soll diese Galerie wirklich gelöscht werden?',
    'MSG_ADD_OK' => 'Die Galerie wurde erstellt! Sie werden nun weitergeleitet...',
    'MSG_EDIT_OK' => 'Die Galerie wurde bearbeitet! Sie werden nun weitergeleitet...',
    'MSG_DEL_OK' => 'Die Galerie wurde gelöscht! Sie werden nun weitergeleitet...',
    'MSG_ADDPICS_OK' => 'Die Bilder wurden hochgeladen! Sie werden nun weitergeleitet...',
    'MSG_EDITPICS_OK' => 'Die Bildunterschrift wurde bearbeitet! Sie werden nun weitergeleitet...',
    'MSG_DELPICS_OK' => 'Das Bild wurde gelöscht! Sie werden nun weitergeleitet...',
];

////////////////////////////////////////////////////////////////////////////////////////// -> FUNKTIONEN

//Online-Anzeige
$lang['func_online'] = [
    'USERONLINE' => 'Besucher online',
    'ONLINERECORD' => 'Rekord',
];

//Online-Liste
$lang['func_onlinelist'] = [
    'ONLINELIST' => 'Wer ist online?',
    'ONLINELIST_NOBODY' => 'Keine registrierten Benutzer online',
];

//Neue Nachrichten-Anzeige
$lang['func_newpms'] = [
    'NEWPMS' => 'neue Nachrichten',
];

//Neue Gästebucheinträge-Anzeige
$lang['func_newgbs'] = [
    'NEWGBS' => 'neue Einträge im Gästebuch',
];

//Login-Box
$lang['func_loginbox'] = [
];

//Status
$lang['func_status'] = [
    'SET_YOUR_STATUS' => 'Was tust du gerade?',
    'SAVE_STATUS' => 'Status speichern',
];

//Geburtstage
$lang['func_birthdays'] = [
    'BIRTHDAYS' => 'Heutige Geburtstage',
    'BIRTHDAYS_NOBODY' => 'Heute hat niemand Geburtstag',
];

//Geburtstage
$lang['func_birthdays_tomorrow'] = [
    'BIRTHDAYS_TOMORROW' => 'Morgige Geburtstage',
    'BIRTHDAYS_TOMORROW_NOBODY' => 'Morgen hat niemand Geburtstag',
];

//Geburtstage
$lang['func_birthdays_nextdays'] = [
    'BIRTHDAYS_TOMORROW' => 'Geburtstage der nächsten Tage',
    'BIRTHDAYS_TOMORROW_NOBODY' => 'In den nächsten Tagen hat niemand Geburtstag',
];

//Buddyliste
$lang['func_buddylist'] = array_merge($lang['icons'], [
    'BUDDYLIST' => 'Freunde',
    'BUDDYLIST_NONE' => 'Keine Freunde',
]);

//Bookmarks
$lang['func_bookmarks'] = array_merge($lang['icons'], [
    'BOOKMARKS' => 'Bookmarks',
    'BOOKMARKS_NONE' => 'Keine Bookmarks',
]);

//Neue User
$lang['func_newuser'] = [
    'WELCOMENEW' => 'Unsere neuesten Mitglieder',
];

//Statistik
$lang['func_stats'] = [
    'USERS' => 'Benutzer',
    'USERS_MALE' => 'männlich',
    'USERS_FEMALE' => 'weiblich',
    'BLOGS' => 'Blog-Einträge',
    'GALLERIES' => 'Galerien',
    'PICTURES' => 'Bilder',
];

//Suche
$lang['func_search'] = [
    'SEARCH_USER' => 'Registrierte Benutzer',
];

//Benachrichtigung: Neue PN
$lang['pmpopup'] = [
    'MSG_PMPOPUP' => 'Sie haben neue Nachrichten! Klicken Sie auf OK um ein neues Fenster zu öffnen.',
];
