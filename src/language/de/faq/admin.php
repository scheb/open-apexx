<?php 

#
# German Language Pack
# ====================
#


/************** MODULE NAME **************/
$lang['modulename']['MODULENAME_FAQ'] = 'FAQ';


/************** HEADLINES **************/
$lang['titles'] = array (
'TITLE_FAQ_SHOW' => 'FAQ-Übersicht',
'TITLE_FAQ_ADD' => 'Frage hinzufügen',
'TITLE_FAQ_EDIT' => 'Frage bearbeiten',
'TITLE_FAQ_DEL' => 'Frage löschen',
'TITLE_FAQ_ENABLE' => 'Frage aktivieren',
'TITLE_FAQ_DISABLE' => 'Frage deaktivieren',
'TITLE_FAQ_MOVE' => 'Fragen anordnen'
);


/************** NAVIGATION **************/
$lang['navi'] = array (
'NAVI_FAQ_SHOW' => 'Fragen zeigen',
'NAVI_FAQ_ADD' => 'Neue Frage',
);


/************** ACTION EXPLICATION **************/
$lang['expl'] = array (

);


/************** LOG MESSAGES **************/
$lang['log'] = array (
'LOG_FAQ_ADD' => 'Frage hinzugefügt',
'LOG_FAQ_EDIT' => 'Frage bearbeitet',
'LOG_FAQ_DEL' => 'Frage gelöscht',
'LOG_FAQ_ENABLE' => 'Frage aktiviert',
'LOG_FAQ_DISABLE' => 'Frage deaktiviert'
);


/************** CONFIG **************/
$lang['config'] = array (
'SEARCHABLE' => 'Soll das Modul in die Suchfunktion einbezogen werden?'
);


/************** ACTIONS **************/

//SHOW
$lang['actions']['show'] = array (
'COL_QUESTION' => 'Frage',
'COL_HITS' => 'Klicks',
'USEDND' => 'Sie können die Einträge per Drag &amp; Drop anordnen',
'NONE' => 'Noch keine Fragen erstellt!'
);


//ADD + EDIT
$lang['actions']['add'] = $lang['actions']['edit'] = array (
'CREATEIN' => 'Unterpunkt von',
'ROOT' => 'Dies ist eine Hauptfrage',
'QUESTION' => 'Frage',
'INLINESCREENS' => 'Inline-Bilder',
'ANSWER' => 'Antwort',
'META_DESCRIPTION' => 'Meta Description',
'OPTIONS' => 'Optionen',
'SEARCHABLE' => 'In die Suche einbeziehen?',
'PUBNOW' => 'Sofort freischalten?',
'SUBMIT_ADD' => 'Frage hinzufügen',
'SUBMIT_ADDNEXT' => 'Weitere Frage hinzufügen',
'SUBMIT_EDIT' => 'Aktualisieren'
);


//DEL
$lang['actions']['del'] = array (
'MSG_TEXT' => 'Wollen Sie die Frage &quot;{TITLE}&quot; wirklich löschen?'
);


//ENABLE
$lang['actions']['enable'] = array (

);


//DISABLE
$lang['actions']['disable'] = array (

);


?>