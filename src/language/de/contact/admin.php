<?php 

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2009, Christian Scheb            |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| THIS SOFTWARE IS NOT FREE! MAKE SURE YOU OWN A VALID LICENSE! |
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/

#
# German Language Pack
# ====================
#


/************** MODULE NAME **************/
$lang['modulename']['MODULENAME_CONTACT'] = 'Kontaktformular';


/************** HEADLINES **************/
$lang['titles'] = array (
'TITLE_CONTACT_SHOW' => 'Kontakte zeigen',
'TITLE_CONTACT_ADD' => 'Kontakt erstellen',
'TITLE_CONTACT_EDIT' => 'Kontakt bearbeiten',
'TITLE_CONTACT_DEL' => 'Kontakt löschen'
);


/************** NAVIGATION **************/
$lang['navi'] = array (
'NAVI_CONTACT_SHOW' => 'Kontakte zeigen',
'NAVI_CONTACT_ADD' => 'Neuer Kontakt'
);


/************** ACTION EXPLICATION **************/
$lang['expl'] = array (

);


/************** LOG MESSAGES **************/
$lang['log'] = array (
'LOG_CONTACT_ADD' => 'Kontakt erstellt',
'LOG_CONTACT_EDIT' => 'Kontakt bearbeitet',
'LOG_CONTACT_DEL' => 'Kontakt gelöscht'
);


/************** CONFIG **************/
$lang['config'] = array (
'CAPTCHA' => 'Absender der eMail muss visuell bestätigt werden (Captcha)?'
);


/************** ACTIONS **************/

//SHOW
$lang['actions']['show'] = array (
'COL_TITLE' => 'Bezeichnung',
'COL_EMAIL' => 'eMail',
'NONE' => 'Noch keine Kontaktadressen eingetragen!'
);

//ADD + EDIT
$lang['actions']['add'] = $lang['actions']['edit'] = array (
'TITLE' => 'Bezeichnung',
'EMAIL' => 'eMail',
'SEPBYCOMMA' => 'mehrere eMail-Adressen durch Komma trennen',
'SUBMIT_ADD' => 'Kontakt eintragen',
'SUBMIT_EDIT' => 'Aktualisieren',
'INFO_NOEMAIL' => 'Die eMail-Adresse {EMAIL} ist ungültig!'
);

//DEL
$lang['actions']['del']= array (
'MSG_TEXT' => 'Wollen Sie den Kontakt &quot;{TITLE}&quot; wirklich löschen?'
);

?>