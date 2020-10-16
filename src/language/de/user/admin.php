<?php 

#
# German Language Pack
# ====================
#


/************** MODULE NAME **************/
$lang['modulename']['MODULENAME_USER'] = 'Benutzersystem';


/************** HEADLINES **************/
$lang['titles'] = array (
'TITLE_USER_LOGIN' => 'Anmelden',
'TITLE_USER_LOGOUT' => 'Abmelden',
'TITLE_USER_AUTOLOGOUT' => 'Automatisches Abmelden',
'TITLE_USER_SHOW' => 'Benutzer-Accounts',
'TITLE_USER_ADD' => 'Benutzer-Account erstellen',
'TITLE_USER_EDIT' => 'Benutzer-Account bearbeiten',
'TITLE_USER_DEL' => 'Benutzer-Account löschen',
'TITLE_USER_ENABLE' => 'Benutzer-Account freischalten',
'TITLE_USER_BLOG' => 'Benutzer-Blog verwalten',
'TITLE_USER_GUESTBOOK' => 'Benutzer-Gästebuch verwalten',
'TITLE_USER_GALLERY' => 'Benutzer-Galerien verwalten',
'TITLE_USER_GSHOW' => 'Benutzergruppen',
'TITLE_USER_GADD' => 'Benutzergruppe erstellen',
'TITLE_USER_GEDIT' => 'Benutzergruppe bearbeiten',
'TITLE_USER_GCLEAN' => 'Benutzergruppe leeren',
'TITLE_USER_GDEL' => 'Benutzergruppe löschen',
'TITLE_USER_SENDMAIL' => 'eMail an Benutzer senden',
'TITLE_USER_SENDPM' => 'Private Nachricht an Benutzer senden',
'TITLE_USER_MYPROFILE' => 'Eigenes Benutzerprofil bearbeiten',
'TITLE_USER_PROFILE' => 'Benutzerprofil ansehen'
);


/************** NAVIGATION **************/
$lang['navi'] = array (
'NAVI_USER_SHOW' => 'Accounts zeigen',
'NAVI_USER_ADD' => 'Neuer Account',
'NAVI_USER_GSHOW' => 'Benutzergruppen',
'NAVI_USER_MYPROFILE' => 'Mein Benutzerprofil',
'NAVI_USER_GUESTBOOK' => 'Gästebuch-Einträge',
'NAVI_USER_BLOG' => 'Blogs',
'NAVI_USER_SENDMAIL' => 'eMail an Benutzer',
'NAVI_USER_SENDPM' => 'Nachricht an Benutzer',
'NAVI_USER_GALLERY' => 'Galerien',
);


/************** ACTION EXPLICATION **************/
$lang['expl'] = array (
'EXPL_USER_ADD' => 'Rechte nur Administratoren geben! Wer Zugang zum Benutzersystem hat kann sich einen Account mit allen Rechten erstellen!',
'EXPL_USER_EDIT' => 'Rechte nur Administratoren geben! Wer Zugang zum Benutzersystem hat kann sich einen Account mit allen Rechten erstellen!',
'EXPL_USER_DEL' => 'Rechte nur Administratoren geben! Wer Zugang zum Benutzersystem hat kann sich einen Account mit allen Rechten erstellen!',
'EXPL_USER_GADD' => 'Rechte nur Administratoren geben! Wer Zugang zum Benutzersystem hat kann sich einen Account mit allen Rechten erstellen!',
'EXPL_USER_GEDIT' => 'Rechte nur Administratoren geben! Wer Zugang zum Benutzersystem hat kann sich einen Account mit allen Rechten erstellen!',
'EXPL_USER_GCLEAN' => 'Rechte nur Administratoren geben! Wer Zugang zum Benutzersystem hat kann sich einen Account mit allen Rechten erstellen!',
'EXPL_USER_GDEL' => 'Rechte nur Administratoren geben! Wer Zugang zum Benutzersystem hat kann sich einen Account mit allen Rechten erstellen!'
);


/************** LOG MESSAGES **************/
$lang['log'] = array (
'LOG_USER_LOGIN' => '> > > > > ANGEMELDET > > > > >',
'LOG_USER_LOGOUT' => '< < < < < ABGEMELDET < < < < <',
'LOG_USER_AUTOLOGOUT' => '&lt; &lt; &lt; AUTOLOGOUT &lt; &lt; &lt;',
'LOG_USER_ADD' => 'Benutzer-Account erstellt',
'LOG_USER_EDIT' => 'Benutzer-Account bearbeitet',
'LOG_USER_DEL' => 'Benutzer-Account gelöscht',
'LOG_USER_ENABLE' => 'Benutzer-Account freigeschaltet',
'LOG_USER_BLOG_EDIT' => 'Benutzer-Blog-Eintrag bearbeitet',
'LOG_USER_BLOG_DEL' => 'Benutzer-Blog-Eintrag gelöscht',
'LOG_USER_GUESTBOOK_EDIT' => 'Benutzer-Gästebuch-Eintrag bearbeitet',
'LOG_USER_GUESTBOOK_DEL' => 'Benutzer-Gästebuch-Eintrag gelöscht',
'LOG_USER_GALLERY_EDIT' => 'Benutzer-Galerie bearbeitet',
'LOG_USER_GALLERY_DEL' => 'Benutzer-Galerie gelöscht',
'LOG_USER_GADD' => 'Benutzergruppe erstellt',
'LOG_USER_GEDIT' => 'Benutzergruppe bearbeitet',
'LOG_USER_GCLEAN' => 'Benutzergruppe geleert',
'LOG_USER_GDEL' => 'Benutzergruppe gelöscht',
'LOG_USER_SENDMAIL' => 'eMail an Benutzer gesendet',
'LOG_USER_SENDPM' => 'Private Nachricht an Benutzer gesendet',
'LOG_USER_MYPROFILE' => 'Eigenes Benutzerprofil bearbeitet'
);


/************** CONFIG **************/
$lang['config'] = array (
'VIEW' => 'Darstellung',
'OPTIONS' => 'Einstellung',
'AVATAR' => 'Avatar',
'SIGNATURE' => 'Signatur',
'PMS' => 'PMs',
'GUESTBOOKCFG' => 'Gästebuch',
'BLOGCFG' => 'Blog',
'GALLERYCFG' => 'Galerie',
'LISTACTIVEONLY' => 'In der Benutzerliste nur aktivierte Accounts anzeigen?',
'SEARCHABLE' => 'Soll das Modul in die Suchfunktion einbezogen werden?',
'USERLISTEPP' => 'Einträge pro Seite in der Benutzerliste',
'USERACTIVATION' => 'Aktivierung neuer Benutzer-Accounts erfolgt:',
'ACTIVATEAUTO' => 'automatisch',
'ACTIVATEADMIN' => 'durch Administrator',
'ACTIVATEREGKEY' => 'durch Aktivierungscode',
'REACTIVATE' => 'Account muss reaktiviert werden, wenn sich die eMail-Adresse ändert? (nur bei Aktivierung durch Aktivierungscode)',
'ACCEPTRULES' => 'Benutzer muss vor der Registrierung den Regeln zustimmen? (im Sprachpaket bearbeiten)',
'MAILMULTIACC' => 'Mehrere Accounts mit der selben eMail-Adresse erlauben?',
'CAPTCHA' => 'Registrierung muss visuell bestätigt werden (Captcha)?',
'USERMINLEN' => 'Mindestlänge für Benutzernamen:',
'PWDMINLEN' => 'Mindestlänge für Passwörter:',
'BLOCKUSERNAME' => 'Zeichenfolgen, die in Benutzernamen nicht vorkommen dürfen:',
'AVATAR_MAXSIZE' => 'Maximale Dateigröße eines Avatars in Byte:',
'AVATAR_MAXDIM' => 'Maximale Höhe/Breite eines Avatars in Pixel:',
'AVATAR_BADWORDS' => 'Badword-Filter im Avatar-Titel?',
'AVATAR_RESIZE' => 'Avatare automatisch verkleinern?',
'SIGMAXLEN' => 'Maximale Zeichnzahl der Benutzer-Signatur?',
'SIG_ALLOWSMILIES' => 'Smilies in der Signatur erlauben?',
'SIG_ALLOWCODE' => 'Codes in der Signatur erlauben?',
'SIG_BADWORDS' => 'Badword-Filter in der Signatur verwenden?',
'MAXPMCOUNT' => 'PN-Zahl, die maximal gespeichert werden kann:',
'PM_ALLOWSMILIES' => 'Smilies in den persönlichen Nachrichten erlauben?',
'PM_ALLOWCODE' => 'Codes in den persönlichen Nachrichten erlauben?',
'PM_BADWORDS' => 'Badword-Filter in den persönlichen Nachrichten verwenden?',
'CUSFIELD_NAMES' => 'Bezeichnungen der benutzerdefinierten Eingabefelder:<br />(maximal 10 Felder!)',
'PROFILE_REGONLY' => 'Benutzerprofile nur für Registrierte einsehbar?',
'VISITORSELF' => 'Besucher nur dem Besitzer des Profils zeigen?',
'FRIENDSEPP' => 'Einträge pro Seite in der Freundesliste:',
'REPORTMAIL' => 'Verstoß-Meldungen an diese eMail-Adressen (mehrere Adressen durch Kommas trennen):',
'TIMEOUT' => 'Zeit in Muniten bis ein Benutzer nicht mehr als &quot;online&quot; gilt:',
'ONLINELIST' => 'Online-Liste aktivieren?',
'MAILONNEW' => 'eMail an diese Adressen, wenn sich ein neuer Benutzer registriert hat (mehrere Adressen durch Kommas trennen):',
'SENDMAIL_GUESTS' => 'eMail-senden-Formular auch für Gäste erlauben?',

//Gästebuch
'GUESTBOOK' => 'Benutzer-Gästebuch aktivieren?',
'GUESTBOOK_MAXLEN' => 'Maximale Zeichenzahl eines Eintrags:',
'GUESTBOOK_BREAKLINE' => 'Erzwungener Zeilenumbruch nach X Zeichen:<br />(0 = aus)',
'GUESTBOOK_SPAMPROT' => 'Dauer in Minuten bis erneut ein Eintrag abgegeben werden kann:',
'GUESTBOOK_ALLOWSMILIES' => 'Smilies in den Einträgen erlauben?',
'GUESTBOOK_ALLOWCODE' => 'Codes in den Einträgen erlauben?',
'GUESTBOOK_BADWORDS' => 'Badword-Filter auf den Text anwenden?',
'GUESTBOOK_EPP' => 'Einträge pro Seite:',
'GUESTBOOK_REQ_TITLE' => 'Feld &quot;Titel&quot; muss ausgefüllt werden?',
'GUESTBOOK_USERADMIN' => 'Benutzer dürfen Einträge aus ihrem Gästebuch löschen?',

//Blog
'BLOG' => 'Benutzer-Blog aktivieren?',
'BLOG_EPP' => 'Anzahl der Einträge pro Seite:',

//Gallery
'GALLERY' => 'Benutzer-Galerie aktivieren?',
'GALLERY_EPP' => 'Anzahl der Bilder pro Seite:',
'GALLERY_PICHEIGHT' => 'Maximale Höhe der Bilder:',
'GALLERY_PICWIDTH' => 'Maximale Breite der Bilder:',
'GALLERY_THUMBWIDTH' => 'Maximale Breite des Vorschau-Bilds:',
'GALLERY_THUMBHEIGHT' => 'Maximale Höhe des Vorschau-Bilds:',
'GALLERY_QUALITY_RESIZE' => 'Qualitätiv hochwertigere Verkleinerung (rechenaufwendig!)?',
'GALLERY_MAXPICS' => 'Maximale Anzahl an Bildern, die der Benutzer in seine Galerien hochladen darf:',

//Usermap
'USERMAP_TOPLEFT_X' => 'Breitengrad an der linken, oberen Ecke der Karte:',
'USERMAP_TOPLEFT_Y' => 'Längengrad an der linken, oberen Ecke der Karte:',
'USERMAP_BOTTOMRIGHT_X' => 'Breitengrad an der rechten, unteren Ecke der Karte:',
'USERMAP_BOTTOMRIGHT_Y' => 'Längengrad an der rechten, unteren Ecke der Karte:',
'USERMAP_WIDTH' => 'Breite der Karte in Pixeln:',
'USERMAP_HEIGHT' => 'Höhe der Karte in Pixeln:'
);


/************** ACTIONS **************/

//LOGIN
$lang['actions']['login'] = array (
'TITLE' => 'Anmeldung',
'USER' => 'Benutzername:',
'PWD' => 'Passwort:',
'SAVECOOKIE' => 'Cookies setzen zum automatischen Anmelden',
'COOKIETIME' => 'Tage angemeldet bleiben',
'PUBLOGIN' => 'Auch im öffentlichen Bereich anmelden',
'SUBMIT' => 'Anmelden',
'INFO_BLOCK' => 'Sie haben sich fünf mal falsch angemeldet! Ihr Account ist für 15 Minuten gesperrt.',
'INFO_FAIL' => 'Anmeldung fehlgeschlagen! Benutzername und Passwort stimmen nicht überein.',
'INFO_NOGROUP' => 'Anmeldung fehlgeschlagen! Sie sind nicht berechtigt den Adminbereich zu betreten.',
'INFO_BANNED' => 'Anmeldung fehlgeschlagen! Ihr Account wurde gesperrt.'
);


//LOGOUT
$lang['actions']['logout'] = array (
'MSG_NOLOGIN' => 'Abmeldung fehlgeschlagen! Sie sind noch nicht angemeldet.'
);


//AUTOLOGOUT
$lang['actions']['autologout'] = array (
'MSG_OK' => 'Automatische Abmeldung erfolgt! Entweder ist Ihr Account fehlerhaft, inaktiv oder sie versuchen die Session eines anderen Benutzers zu benutzen.'
);


//SHOW
$lang['actions']['show'] = array (
'LAYER_TEAM' => 'Team',
'LAYER_ALL' => 'Alle Benutzer',
'LAYER_ACTIVATE' => 'Nicht freigeschaltet',
'COL_ACTIVE' => 'Aktiv-Status',
'COL_USER_LOGIN' => 'Benutzername',
'COL_USER' => 'Angezeigter Benutzername',
'COL_GROUP' => 'Benutzergruppe',
'COL_REGTIME' => 'Registrierung',
'COL_LASTACTIVE' => 'Letzte Aktivität',
'PROFILE' => 'Profil zeigen',
'BLOG' => 'Blog',
'GUESTBOOK' => 'Gästebuch',
'GALLERY' => 'Galerien',
'SEARCHTEXT' => 'Stichwort',
'SNAME' => 'Benutzername',
'SPROFILE' => 'Profilfelder',
'SEARCHGROUP' => 'Benutzergruppe',
'SEARCH' => 'Suchen',
'MULTI_EDIT' => 'Benutzergruppe zuweisen',
'NONE' => 'Keine Benutzer-Accounts gefunden!'
);


//ADD + EDIT + MYPROFILE
$lang['actions']['add'] = 
$lang['actions']['edit'] = 
$lang['actions']['myprofile'] = array (
'ACCOUNT' => 'Account',
'ADDNL' => 'Optionale Angaben',
'CUSTOM' => 'Benutzerdefinierte Informationsfelder',
'CONTACT' => 'Kontaktmöglichkeiten',
'PERS' => 'Persönliche Angaben',
'PUBLICOPTIONS' => 'Einstellungen (Öffentlich)',
'ADMINOPTIONS' => 'Einstellungen (Administration)',
'USERNAME_LOGIN' => 'Benutzername',
'USERNAME' => 'Angezeigter Benutzername',
'PWD' => 'Passwort',
'REPEAT' => 'Wiederholung',
'EMAIL' => 'eMail',
'GROUP' => 'Benutzergruppe',
'NOGROUP' => 'Keine Benutzergruppe',
'ACTIVE' => 'Aktiv',
'REGKEY' => 'Aktivierungs-Code',
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
'AVATAR' => 'Avatar',
'DELAVATAR' => 'Avatar löschen',
'SIGNATURE' => 'Signatur',
'CHARSLEFT' => 'Verbleibende Zeichen',
'INVISIBLE' => 'Unsichtbar sein?',
'HIDEMAIL' => 'eMail-Adresse verstecken?',
'POPPM' => 'Popup bei neuer Nachricht?',
'SHOWBUDDIES' => 'Freundesliste im Profil anzeigen',
'USEGB' => 'Gästebuch-Modus',
'GBENABLED' => 'aktiviert',
'GBDISABLED' => 'deaktiviert',
'GBFRIENDS' => 'nur für meine Freunde',
'GBMAIL' => 'eMail-Benachrichtigung bei neuem Gästebuch-Eintrag',
'PROFILEFORFRIENDS' => 'Profil nur Freunden zeigen',
'AUTOSUBSCRIBE' => 'Themen im Forum automatisch abonnieren',
'LANG' => 'Sprache',
'THEME' => 'Website-Stil',
'USEDEFAULT' => 'Standard verwenden',
'EDITOR' => 'WYSIWYG-Editor verwenden?',
'SUBMIT_ADD' => 'Benutzer erstellen',
'SUBMIT_EDIT' => 'Aktualisieren',
'SUBMIT_MYPROFILE' => 'Aktualisieren',
'INFO_PWNOMATCH' => 'Passwort und Passwort-Wiederholung stimmen nicht überein!',
'INFO_USEREXISTS' => 'Unter diesem Benutzernamen existiert bereits ein Account!',
'INFO_NOMAIL' => 'Die angegebene eMail-Adresse ist nicht gültig!',
'INFO_SIGTOOLONG' => 'Diese Signatur ist zu lang!',

//Multi
'USERNAMES' => 'Benutzer',
'SUBMIT_MULTI' => 'Aktualisieren'
);


//DEL
$lang['actions']['del'] = array (
'MSG_TEXT' => 'Wollen Sie den Benutzer &quot;{TITLE}&quot; wirklich löschen?'
);


//ENABLE
$lang['actions']['enable'] = array (
'MAIL_ACTIVATION_TITLE' => 'Benutzer-Account freigeschaltet',
'MAIL_ACTIVATION_TEXT' => "Hallo {USERNAME},\nIhr Benutzer-Account \"{USERNAME}\" wurde soeben freigeschaltet. Sie können sich nun anmelden:\n{URL}\n\nGrüße, Team von {WEBSITE}"
);


//BLOG
$lang['actions']['blog'] = array (
'TITLE' => 'Titel',
'COMMENTS' => 'Kommentare',
'COL_PUB' => 'Veröffentlichung',
'COL_OWNER' => 'Benutzer',
'BLOGOF' => 'Blog von',
'TEXT' => 'Text',
'NONE' => 'Keine Blog-Einträge vorhanden!',
'EDITBLOG' => 'Blog-Eintrag bearbeiten',
'MSG_TEXT' => 'Wollen Sie den Blog-Eintrag &quot;{TITLE}&quot; wirklich löschen?'
);


//GUESTBOOK
$lang['actions']['guestbook'] = array (
'COL_NAME' => 'Name',
'COL_TEXT' => 'Text',
'COL_ADDTIME' => 'Erstellungsdatum',
'COL_OWNER' => 'Gästebuch-Besitzer',
'GUESTBOOKOF' => 'Gästebuch von',
'EDITGUESTBOOK' => 'Gästebuch-Eintrag bearbeiten',
'USERNAME' => 'Benutzername',
'TITLE' => 'Titel',
'TEXT' => 'Text',
'NONE' => 'Bisher keine Gästebuch-Einträge erstellt!',
'MSG_TEXT' => 'Wollen Sie den Gästebuch-Eintrag von &quot;{TITLE}&quot; wirklich löschen?'
);


//GALLERY
$lang['actions']['gallery'] = array (
'GALLERY' => 'Galerie',
'COMMENTS' => 'Kommentare',
'COL_TITLE' => 'Title',
'COL_PICS' => 'Bilder',
'COL_LASTUPDATE' => 'Letzte Aktualisierung',
'COL_ADDTIME' => 'Erstellungsdatum',
'COL_THUMBNAIL' => 'Thumbnail',
'COL_CAPTION' => 'Bildunterschrift',
'COL_OWNER' => 'Galerie-Besitzer',
'GALLERYOF' => 'Galerie von',
'SHOWPICS' => 'Bilder anzeigen',
'EDITGALLERY' => 'Galerie bearbeiten',
'TITLE' => 'Titel',
'DESCRIPTION' => 'Beschreibung',
'PASSWORD' => 'Passwort',
'EDITCAPTION' => 'Bildunterschrift bearbeiten',
'UPDATE' => 'Aktualisieren',
'NONE' => 'Keine Einträge gefunden!',
'MSG_TEXT' => 'Wollen Sie die Galerie &quot;{TITLE}&quot; wirklich löschen?',
'MSG_TEXT_PIC' => 'Wollen Sie dieses Galeriebild wirklich löschen?'
);


//GSHOW
$lang['actions']['gshow'] = array(
'COL_GROUP' => 'Titel',
'COL_USERS' => 'Benutzer',
'CLEAN' => 'Leeren &amp; Löschen'
);


//GADD + GEDIT
$lang['actions']['gadd'] = $lang['actions']['gedit'] = array(
'GENERAL' => 'Allgemein',
'NAME' => 'Bezeichnung',
'GTYPE' => 'Art der Benutzergruppe',
'GTYPE_INDIV' => 'Individuell (Admin-Rechte unten auswählen)',
'GTYPE_PUBLIC' => 'Öffentlich (keine Admin-Rechte)',
'SECTION_ACCESS' => 'Zugriff auf den Inhalt der Sektionen',
'ALLSEC' => 'Alle Sektionen',
'ACTION' => 'Modul.Aktion',
'RIGHTS' => 'Rechte?',
'SPRIGHTS' => 'Sonderrechte?',
'SHOWINFO' => 'Information zeigen',
'SUBMIT_ADD' => 'Benutzergruppe erstellen',
'SUBMIT_EDIT' => 'Aktualisieren',
'INFO_GROUPEXISTS' => 'Unter dieser Bezeichnung existiert bereits eine Benutzergruppe!'
);


//GDEL
$lang['actions']['gdel'] = array (
'MSG_TEXT' => 'Wollen Sie die Benutzergruppe &quot;{TITLE}&quot; wirklich löschen?'
);


//GCLEAN
$lang['actions']['gclean'] = array (
'TITLE' => 'Benutzergruppe',
'MOVETO' => 'Benutzer verschieben nach',
'DELGROUP' => 'Benutzergruppe löschen',
'SUBMIT' => 'Benutzergruppe leeren'
);


//PROFILE
$lang['actions']['profile'] = array (
'PROFILEOF' => 'Profil von',
'USERID' => 'Benutzer-ID',
'USERNAME' => 'Benutzername',
'REGDATE' => 'Registriert am',
'REGEMAIL' => 'Registrierungs-eMail',
'EMAIL' => 'eMail',
'LASTACTIVE' => 'Zuletzt aktiv',
'GROUPNAME' => 'Benutzergruppe'
);


//SENDMAIL
$lang['actions']['sendmail'] = array (
'ALL' => 'Alle',
'GROUPS' => 'An die Benutzergruppen',
'SUBJECT' => 'Betreff',
'TEXT' => 'Text',
'PLACEHOLDERS' => 'Verwenden Sie den Platzhalter {USERNAME} für den jeweiligen Benutzernamen',
'SEND' => 'Verschicken',
'MSG_SENDING' => 'Die Rundmails werden gerade verschickt, brechen Sie diesen Vorgang in keinem Fall ab!',
'MSG_OK' => 'Die Rundmails wurden verschickt!'
);


//SENDPM
$lang['actions']['sendpm'] = array (
'ALL' => 'Alle',
'GROUPS' => 'An die Benutzergruppen',
'SUBJECT' => 'Betreff',
'TEXT' => 'Text',
'PLACEHOLDERS' => 'Verwenden Sie den Platzhalter {USERNAME} für den jeweiligen Benutzernamen',
'MAIL_NEWPM_TITLE' => 'Neue Nachricht!',
'MAIL_NEWPM_TEXT' => "Sie haben auf der Website {WEBSITE} soeben eine Nachricht von {USERNAME} erhalten. Klicken Sie hier um zu Ihrem Posteingang zu gelangen: {INBOX}\n\n------\n\nBetreff: {SUBJECT}\n\n{TEXT}\n\n------\n\nGruß\nDas Team von {WEBSITE}",
'SEND' => 'Verschicken',
'MSG_SENDING' => 'Die privaten Nachrichten werden gerade verschickt, brechen Sie diesen Vorgang in keinem Fall ab!',
'MSG_OK' => 'Die privaten Nachrichten wurden verschickt!'
);



?>