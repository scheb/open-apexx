<?php 

#
# German Language Pack
# ====================
#


//Index-Seite
$lang['index'] = array (
'HEADLINE' => 'Startseite',
'LASTGALLERY' => 'Neueste Galerien',
'LASTNEWS' => 'Aktuelle News',
'LASTARTICLES' => 'Aktuelle Artikel',
'LASTDOWNLOADS' => 'Neueste Downloads'
);


//Suche Basisplatzhalter
$lang['search_basic'] = array(
'SEARCHWEBSITE' => 'Website durchsuchen',
'SEARCHIT' => 'Suchen',
'TERM' => 'Begriff(e)',
'CONN' => 'Verknüpfung',
'CONNAND' => 'UND-Verknüpfung',
'CONNOR' => 'ODER-Verknüpfung'
);


//Suche
$lang['search'] = array_merge($lang['search_basic'],array (
'HEADLINE' => 'Suchen',
'SEARCHIN' => 'Suchen in',
'SUBMIT' => 'Suchen',
'LAST_SEARCHES' => 'Zuletzt gesucht',
'NONE' => 'Keine passenden Einträge gefunden!'
));


//Seite empfehlen
$lang['tell'] = array (
'HEADLINE' => 'Seite empfehlen',
'USERNAME' => 'Ihr Name',
'EMAIL' => 'Ihre eMail-Adresse',
'TOEMAIL' => 'Empfänger eMail-Adresse',
'SUBJECT' => 'Betreff',
'TEXT' => 'Text',
'CAPTCHA' => 'Visuelle Bestätigung',
'SUBMIT' => 'eMail senden',
'MSG_WRONGCODE' => 'Der angegebene Bestätigungscode ist nicht korrekt!',
'MSG_OK' => 'Ihre Empfehlung wurde verschickt! Sie werden nun weitergeleitet...',
'MSG_MAILNOTVALID' => 'Eine der eMail-Adressen ist nicht gültig!',
'MAIL_TELL_TITLE' => 'Interessante Seite',
'MAIL_TELL_TEXT' => "Hallo\nich habe gerade eine Seite gefunden, die dich interessieren könnte:\n{URL}"
);


//Altersabfrage
$lang['checkage'] = array(
'MSG_CHECKAGE' => 'Dieser Inhalt ist erst ab 18 Jahren freigegeben. Bitte geben Sie Ihr Geburtsdatum ein:',
'MSG_TOOYOUNG' => 'Sie sind nicht alt genug, um diesen Inhalt anzusehen!'
);


////////////////////////////////////////////////////////////////////////////////////// FUNKTIONEN

//Smilies anzeigen
$lang['showsmilies'] = array (
'SMILIES' => 'Smilie-Liste',
'CLOSE' => 'Fenster schließen'
);


//Codes anzeigen
$lang['showcodes'] = array (
'CODES' => 'Code-Liste',
'FORUM' => 'Nur im Forum',
'SIGALLOWED' => 'Code ist auch in der Signatur erlaubt',
'CLOSE' => 'Fenster schließen'
);

?>