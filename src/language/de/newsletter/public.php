<?php

//
// German Language Pack
// ====================
//

$lang['mails'] = [
    'MAIL_INSERT_TITLE' => 'Eintragung in den Newsletter',
    'MAIL_INSERT_TEXT' => "Ihre eMail-Adresse {EMAIL} wurde auf der Website {WEBSITE} für den Newsletter eingetragen. Sie haben folgende Kategorien gewählt: {CATEGORIES}. Um die Anmeldung zu bestätigen klicken Sie hier: {URL}. Ansonsten ignorieren Sie diese eMail einfach.\n\nGrüße, das Team von {WEBSITE}",
    'MAIL_DISCHARGE_TITLE' => 'Abmeldung vom Newsletter',
    'MAIL_DISCHARGE_TEXT' => "Sie möchten sich auf der Website {WEBSITE} vom Newsletter abmelden. Sie haben folgende Kategorien gewählt: {CATEGORIES}. Um die Abmeldung zu bestätigen klicken Sie hier: {URL}. Ansonsten ignorieren Sie diese eMail einfach.\n\nGrüße, das Team von {WEBSITE}",
];

$lang['form'] = array_merge($lang['mails'], [
    'HEADLINE' => 'Newsletter',
    'NEWSLETTER' => 'Newsletter-Service',
    'GETCODE' => 'Bestätigungs-Code nicht erhalten?',
    'EMAIL' => 'eMail-Adresse',
    'ACTION' => 'Aktion',
    'SIGNIN' => 'Eintragen',
    'SIGNOFF' => 'Austragen',
    'HTML' => 'HTML-Newsletter',
    'CATEGORIES' => 'Kategorien',
    'ALL' => 'Alle',
    'SUBMIT' => 'Ausführen',
    'MSG_NOVALIDEMAIL' => 'Das ist keine gültige eMail-Adresse!',
    'MSG_EXISTS' => 'Sie sind für diese Newsletter-Kategorien bereits eingetragen!',
    'MSG_INSERT_OK' => 'Vielen Dank für Ihre Eintragung in den Newsletter! Sie werden nun weitergeleitet...',
    'MSG_INSERT_OK_EMAIL' => 'Zur Bestätigung der Anmeldung wurde Ihnen soeben ein Link per eMail zugeschickt.',
    'MSG_NOTFOUND' => 'Diese eMail-Adresse wurde in der Datenbank nicht gefunden!',
    'MSG_DISCHARGE_OK' => 'Sie wurden von den ausgewählten Newsletter-Kategorien abgemeldet! Sie werden nun weitergeleitet...',
    'MSG_DISCHARGE_OK_EMAIL' => 'Zur Bestätigung der Abmeldung wurde Ihnen soeben ein Link per eMail zugeschickt.',
]);

$lang['activate'] = [
    'MSG_NOACC' => 'Dieser Account existiert nicht!',
    'MSG_WRONGKEY' => 'Der Bestätigungs-Code ist falsch oder veraltet! Sie können sich einen neuen Bestätigungs-Code zuschicken lassen.',
    'MSG_INSERT_OK' => 'Anmeldung für den Newsletter bestätigt! Sie werden nun weitergeleitet...',
    'MSG_DISCHARGE_OK' => 'Abmeldung vom Newsletter bestätigt! Sie werden nun weitergeleitet...',
];

$lang['getcode'] = array_merge($lang['mails'], [
    'GETCODE' => 'Bestätigungs-Code anfordern',
    'SUBMIT' => 'Code anfordern',
    'MSG_NOTHING' => 'Derzeit stehen für diese eMail-Adresse keine Bestätigungen aus!',
    'MSG_OK' => 'Ihr Bestätigungs-Code wurde Ihnen soeben zugeschickt! Sie werden nun weitergeleitet...',
]);

$lang['function'] = [
    'FUNC_NEWSLETTER' => 'Newsletter-Service',
    'FUNC_NEWSLETTER_HTML' => 'HTML-Newsletter',
    'FUNC_NEWSLETTER_INSERT' => 'Eintragen',
    'FUNC_NEWSLETTER_DISCHARGE' => 'Austragen',
    'FUNC_NEWSLETTER_SUBMIT' => 'Ausführen',
];
