<?php

//
// German Language Pack
// ====================
//

// MODULE NAME
$lang['modulename']['MODULENAME_CONTACT'] = 'Kontaktformular';

// HEADLINES
$lang['titles'] = [
    'TITLE_CONTACT_SHOW' => 'Kontakte zeigen',
    'TITLE_CONTACT_ADD' => 'Kontakt erstellen',
    'TITLE_CONTACT_EDIT' => 'Kontakt bearbeiten',
    'TITLE_CONTACT_DEL' => 'Kontakt löschen',
];

// NAVIGATION
$lang['navi'] = [
    'NAVI_CONTACT_SHOW' => 'Kontakte zeigen',
    'NAVI_CONTACT_ADD' => 'Neuer Kontakt',
];

// ACTION EXPLICATION
$lang['expl'] = [
];

// LOG MESSAGES
$lang['log'] = [
    'LOG_CONTACT_ADD' => 'Kontakt erstellt',
    'LOG_CONTACT_EDIT' => 'Kontakt bearbeitet',
    'LOG_CONTACT_DEL' => 'Kontakt gelöscht',
];

// CONFIG
$lang['config'] = [
    'CAPTCHA' => 'Absender der eMail muss visuell bestätigt werden (Captcha)?',
];

// ACTIONS

//SHOW
$lang['actions']['show'] = [
    'COL_TITLE' => 'Bezeichnung',
    'COL_EMAIL' => 'eMail',
    'NONE' => 'Noch keine Kontaktadressen eingetragen!',
];

//ADD + EDIT
$lang['actions']['add'] = $lang['actions']['edit'] = [
    'TITLE' => 'Bezeichnung',
    'EMAIL' => 'eMail',
    'SEPBYCOMMA' => 'mehrere eMail-Adressen durch Komma trennen',
    'SUBMIT_ADD' => 'Kontakt eintragen',
    'SUBMIT_EDIT' => 'Aktualisieren',
    'INFO_NOEMAIL' => 'Die eMail-Adresse {EMAIL} ist ungültig!',
];

//DEL
$lang['actions']['del'] = [
    'MSG_TEXT' => 'Wollen Sie den Kontakt &quot;{TITLE}&quot; wirklich löschen?',
];
