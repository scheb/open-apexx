<?php 

#
# German Language Pack
# ====================
#


/************** MODULE NAME **************/
$lang['modulename']['MODULENAME_NAVI'] = 'Navigation';


/************** HEADLINES **************/
$lang['titles'] = array (
'TITLE_NAVI_SHOW' => 'Navigation-Übersicht',
'TITLE_NAVI_ADD' => 'Navigationspunkt erstellen',
'TITLE_NAVI_EDIT' => 'Navigationspunkt bearbeiten',
'TITLE_NAVI_DEL' => 'Navigationspunkt löschen',
'TITLE_NAVI_MOVE' => 'Navigationspunkt verschieben',
'TITLE_NEVI_GROUP' => 'Navigationsleisten anlegen'
);


/************** NAVIGATION **************/
$lang['navi'] = array (
'NAVI_NAVI_SHOW' => 'Navigation zeigen',
'NAVI_NAVI_GROUP' => 'Navigationsleisten anlegen'
);


/************** ACTION EXPLICATION **************/
$lang['expl'] = array (

);


/************** LOG MESSAGES **************/
$lang['log'] = array (
'LOG_NAVI_ADD' => 'Navigationspunkt erstellt',
'LOG_NAVI_EDIT' => 'Navigationspunkt bearbeitet',
'LOG_NAVI_DEL' => 'Navigationspunkt gelöscht',
'LOG_NAVI_GROUPADD' => 'Bannergruppe hinzugefügt',
'LOG_NAVI_GROUPDEL' => 'Bannergruppe gelöscht',
);


/************** MEDIAMANAGER **************/
$lang['media'] = array (

);


/************** CONFIG **************/
$lang['config'] = array (
'COUNT' => 'Anzahl der verwendeten Navigationsleisten:'
);


/************** ACTIONS **************/

//SHOW
$lang['actions']['show'] = array (
'CHOOSE' => 'Bitte wählen Sie eine Navigationsleiste',
'COL_TEXT' => 'Linktext/Bezeichnung',
'USEDND' => 'Sie können die Navigationspunkte per Drag &amp; Drop anordnen',
'NONE' => 'Noch keine Navigationspunkte erstellt!'
);


//ADD + EDIT
$lang['actions']['add'] = $lang['actions']['edit'] = array (
'CREATEIN' => 'Unterpunkt von',
'ROOT' => 'Dies ist ein Hauptnavigationspunkt',
'TEXT' => 'Linktext/Bezeichnung',
'LINK' => 'Link',
'NEWWINDOW' =>'Neues Fenster?',
'CODE' => 'HTML-CODE',
'NOTHING' => 'Nichts',
'STATICSUB' => 'Untermenü immer anzeigen?',
'SUBMIT_ADD' => 'Navigationspunkt erstellen',
'SUBMIT_ADDNEXT' => 'Weiteren Navigationspunkt erstellen',
'SUBMIT_EDIT' => 'Aktualisieren'
);


//DEL
$lang['actions']['del'] = array (
'MSG_TEXT' => 'Wollen Sie den Navigationspunkt &quot;{TITLE}&quot; wirklich löschen?'
);


//GROUP
$lang['actions']['group']= array (
'COL_TITLE' => 'Bezeichnung',
'COL_ENTRIES' => 'Einträge',
'NONE' => 'Noch keine Navigationsleisten erstellt!',
'CATADD' => 'Navigationsleiste erstellen',
'CATEDIT' => 'Aktualisieren',
'MSG_TEXT' => 'Soll die Navigationsleiste &quot;{TITLE}&quot; wirklich gelöscht werden?<br /><b>ACHTUNG:</b> Wenn diese Navigationsleiste Einträge besitzt, werden diese ebenfalls gelöscht.'
);


?>