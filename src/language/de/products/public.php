<?php 

/*****************************************************\
|                 a:pexx - PHP4/5 CMS                 |
|               =======================               |
|        (C) Copyright 2004 by Christian Scheb        |
|              http://www.stylemotion.de              |
|                                                     |
\*****************************************************/

#
# German Language Pack
# ====================
# Last edit: 10.10.2020 02:35
#


$lang['types'] = array (
'TYPE_ALL' => 'Alle',
'TYPE_NORMAL' => 'Allgemein',
'TYPE_GAME' => 'Videospiel',
'TYPE_SOFTWARE' => 'Software',
'TYPE_HARDWARE' => 'Hardware',
'TYPE_MUSIC' => 'Musik',
'TYPE_MOVIE' => 'Film',
'TYPE_BOOK' => 'Literatur',

/* Neue Variabeln mit Präfixe */
'PRODUCTS_TYPES_ALL' => 'Alle',
'PRODUCTS_TYPES_GENERAL' => 'Allgemein',
'PRODUCTS_TYPES_LITERATURE' => 'Literatur',
'PRODUCTS_TYPES_MUSIC' => 'Musik',
'PRODUCTS_TYPES_MOVIE' => 'Film',
'PRODUCTS_TYPES_VIDEOGAME' => 'Videospiel',
'PRODUCTS_TYPES_SOFTWARE' => 'Software',
'PRODUCTS_TYPES_HARDWARE' => 'Hardware',
);


$lang['fields'] = array(
'PRODUCT' => 'Produkt',
'RELEASE' => 'Veröffentlichung',
'HITS' => 'Klicks',
'TITLE' => 'Titel',
'WEBSITE' => 'Website',
'RELEASE' => 'Veröffentlichung',
'PRICE' => 'Preis',
'RECOMMENDED_PRICE' => 'UVP',
'BUYIT' => 'Kaufen',
'MANUFACTURER' => 'Hersteller',
'DEVELOPER' => 'Entwickler',
'PUBLISHER' => 'Verlag',
'AUTHOR' => 'Autor',
'LABEL' => 'Label',
'ARTIST' => 'Künstler',
'STUDIO' => 'Studio',
'GENRE' => 'Genre',
'SYSTEMS' => 'Platformen',
'MEDIA' => 'Medienformat',
'REGISSEUR' => 'Regisseur',
'ACTORS' => 'Darsteller',
'LENGTH' => 'Spieldauer',
'SYSTEMREQUIREMENTS' => 'Systemvoraussetzungen',
'EQUIPMENT' => 'Ausstattung',
'OS' => 'Betriebsysteme',
'LANGUAGES' => 'Sprachen',
'LICENSE' => 'Lizenz',
'LICENSE_FREEWARE' => 'Freeware',
'LICENSE_SHAREWARE' => 'Shareware',
'LICENSE_COMMERCIAL' => 'Kaufversion',
'VERSION' => 'Version',
'USK_NONE' => 'Noch nicht Bewertet',
'USK_IND' => 'Indiziert',
'USK_ALL' => 'Keine Altersbeschränkung',
'USK_6' => 'Ab 6 Jahren',
'USK_12' => 'Ab 12 Jahren',
'USK_16' => 'Ab 16 Jahren',
'USK_18' => 'Keine Jugendfreigabe',
'COMMENTS' => 'Kommentare',

/* Neue Variabeln mit Präfixe */
'PRODUCTS_FIELDS_PRODUCT' => 'Produkt',
'PRODUCTS_FIELDS_TITLE' => 'Titel',
'PRODUCTS_FIELDS_AUTHOR' => 'Autor',
'PRODUCTS_FIELDS_ARTIST' => 'Künstler',
'PRODUCTS_FIELDS_ACTORS' => 'Darsteller',
'PRODUCTS_FIELDS_REGISSEUR' => 'Regisseur',
'PRODUCTS_FIELDS_LABEL' => 'Label',
'PRODUCTS_FIELDS_DEVELOPER' => 'Entwickler',
'PRODUCTS_FIELDS_MANUFACTURER' => 'Hersteller',
'PRODUCTS_FIELDS_PUBLISHER' => 'Verlag',
'PRODUCTS_FIELDS_STUDIO' => 'Studio',
'PRODUCTS_FIELDS_GENRE' => 'Genre',
'PRODUCTS_FIELDS_SYSTEMS' => 'Systeme',
'PRODUCTS_FIELDS_MEDIAFORMAT' => 'Medienformat',
'PRODUCTS_FIELDS_LENGTH' => 'Spieldauer',
'PRODUCTS_FIELDS_VERSION' => 'Version',
'PRODUCTS_FIELDS_SYSTEMREQUIREMENTS' => 'Systemvoraussetzungen',
'PRODUCTS_FIELDS_EQUIPMENT' => 'Ausstattung',
'PRODUCTS_FIELDS_OS' => 'Betriebsysteme',
'PRODUCTS_FIELDS_LANGUAGES' => 'Sprachen',
'PRODUCTS_FIELDS_LICENSE' => 'Lizenz',
'PRODUCTS_FIELDS_LICENSEFREEWARE' => 'Freeware',
'PRODUCTS_FIELDS_LICENSESHAREWARE' => 'Shareware',
'PRODUCTS_FIELDS_LICENSETESTVERSION' => 'Testversion',
'PRODUCTS_FIELDS_LICENSECOMMERCIAL' => 'Kaufversion',
'PRODUCTS_FIELDS_AGERATINGNONE' => 'Noch nicht Bewertet',
'PRODUCTS_FIELDS_AGERATINGINDEXED' => 'Indiziert',
'PRODUCTS_FIELDS_AGERATINGALL' => 'Keine Altersbeschränkung',
'PRODUCTS_FIELDS_AGERATING6' => 'Ab 6 Jahren',
'PRODUCTS_FIELDS_AGERATING12' => 'Ab 12 Jahren',
'PRODUCTS_FIELDS_AGERATING16' => 'Ab 16 Jahren',
'PRODUCTS_FIELDS_AGERATING18' => 'Keine Jugendfreigabe',
'PRODUCTS_FIELDS_WEBSITE' => 'Website',
'PRODUCTS_FIELDS_RELEASE' => 'Veröffentlichung',
'PRODUCTS_FIELDS_PRICE' => 'Preis',
'PRODUCTS_FIELDS_RECOMMENDEDPRICE' => 'UVP',
'PRODUCTS_FIELDS_BUY' => 'Kaufen',
'PRODUCTS_FIELDS_HITS' => 'Aufrufe',
'PRODUCTS_FIELDS_COMMENTS' => 'Kommentare'
);


$lang['products'] = array_merge($lang['fields'],$lang['types'],array (
'PRODUCTINFO' => 'Produkt-Informationen',
'ADD_COLLECTION' => 'Zu meiner Sammlung hinzufügen',
'REMOVE_COLLECTION' => 'Aus meiner Sammlung entfernen',
'HEADLINE' => 'Produkte',
'SORTBY' => 'Sortieren nach',
'NONE' => 'Keine Produkte gefunden!',

/* Neue Variabeln mit Präfixe */
'PRODUCTS_PRODUCTS_PRODUCTS' => 'Produkt',
'PRODUCTS_PRODUCTS_PRODUCTINFO' => 'Produkt-Informationen',
'PRODUCTS_PRODUCTS_COLLECTIONPRODUCTADD' => 'Zu meiner Sammlung hinzufügen',
'PRODUCTS_PRODUCTS_COLLECTIONPRODUCTREMOVE' => 'Aus meiner Sammlung entfernen',
'PRODUCTS_PRODUCTS_PRODUCTSNONE' => 'Keine Produkte gefunden!',
'PRODUCTS_PRODUCTS_SORTBY' => 'Sortieren nach',
));


$lang['collection'] = array_merge($lang['products'],array (
'HEADLINE_COLLECTION' => 'Meine Sammlung',
'MSG_COLL_ADD' => 'Das Produkt wurde Ihrer Sammlung hinzugefügt!',
'MSG_COLL_REMOVE' => 'Das Produkt wurde aus Ihrer Sammlung entfernt!',

/* Neue Variabeln mit Präfixe */
'PRODUCTS_COLLECTION_MYCOLLECTION' => 'Meine Sammlung',
'PRODUCTS_COLLECTION_COLLECTIONPRODUCTADDMSG' => 'Das Produkt wurde Ihrer Sammlung hinzugefügt!',
'PRODUCTS_COLLECTION_COLLECTIONPRDUCTREMOVEMSG' => 'Das Produkt wurde aus Ihrer Sammlung entfernt!'
));


$lang['search'] = array (
'HEADLINE_SEARCH' => 'Suchergebnisse',
'SEARCH' => 'Produkte suchen',
'ITEM' => 'Suchbegriffe',
'CONNAND' => 'UND-Verknüpfung',
'CONNOR' => 'ODER-Verknüpfung',
'TYPE' => 'Typ',
'MSG_NORESULT' => 'Die Suchanfrage ergab keine Treffer!',

/* Neue Variabeln mit Präfixe */
'PRODUCTS_SEARCH_SEARCHRESULT' => 'Suchergebnisse',
'PRODUCTS_SEARCH_SEARCH' => 'Produkte suchen',
'PRODUCTS_SEARCH_KEYWORD' => 'Suchbegriffe',
'PRODUCTS_SEARCH_CONNAND' => 'UND-Verknüpfung',
'PRODUCTS_SEARCH_CONNOR' => 'ODER-Verknüpfung',
'PRODUCTS_SEARCH_TYPE' => 'Typ',
'PRODUCTS_SEARCH_NORESULTMSG' => 'Die Suchanfrage ergab keine Treffer!'
);


$lang['manufacturers'] = array (
'PRODUCTS_OF' => 'Produkte von',
'HEADLINE_MANU' => 'Hersteller',
'SORTBY' => 'Sortieren nach',
'NONE' => 'Keine Hersteller gefunden!',
'NOPRODUCTS' => 'Keine Produkte gefunden!',
'TITLE' => 'Name/Bezeichnung',
'TEXT' => 'Beschreibung',
'WEBSITE' => 'Website',
'FULLNAME' => 'Vollständiger Name',
'ADDRESS' => 'Anschrift',
'FOUNDER' => 'Gründer',
'FOUNDINGYEAR' => 'Gründungsjahr',
'FOUNDINGCOUNTRY' => 'Gründungsland',
'LEGALFORM' => 'Unternehmensform',
'HEADQUATERS' => 'Unternehmenssitz',
'EXECUTIVE' => 'Unternehmensleitung',
'EMPLOYEES' => 'Mitarbeiter',
'TURNOVER' => 'Jahresumsatz',
'SECTOR' => 'Branche',
'PRODUCTS' => 'Produkte',
'EMAIL' => 'eMail-Adresse',
'PHONE' => 'Telefon',

/* Neue Variabeln mit Präfixe */
'PRODUCTS_MANUFACTURERS_MANUFACTURER' => 'Hersteller',
'PRODUCTS_MANUFACTURERS_PRODUCTSOF' => 'Produkte von',
'PRODUCTS_MANUFACTURERS_SORTBY' => 'Sortieren nach',
'PRODUCTS_MANUFACTURERS_NONE' => 'Keine Hersteller gefunden!',
'PRODUCTS_MANUFACTURERS_NOPRODUCTS' => 'Keine Produkte gefunden!',
'PRODUCTS_MANUFACTURERS_TITLE' => 'Name/Bezeichnung',
'PRODUCTS_MANUFACTURERS_TEXT' => 'Beschreibung',
'PRODUCTS_MANUFACTURERS_FULLNAME' => 'Vollständiger Name',
'PRODUCTS_MANUFACTURERS_ADDRESS' => 'Anschrift',
'PRODUCTS_MANUFACTURERS_PHONE' => 'Telefon',
'PRODUCTS_MANUFACTURERS_WEBSITE' => 'Website',
'PRODUCTS_MANUFACTURERS_EMAIL' => 'E-Mail-Adresse',
'PRODUCTS_MANUFACTURERS_SECTOR' => 'Branche',
'PRODUCTS_MANUFACTURERS_PRODUCTS' => 'Produkte',
'PRODUCTS_MANUFACTURERS_LEGALFORM' => 'Unternehmensform',
'PRODUCTS_MANUFACTURERS_HEADQUATERS' => 'Unternehmenssitz',
'PRODUCTS_MANUFACTURERS_EXECUTIVE' => 'Unternehmensleitung',
'PRODUCTS_MANUFACTURERS_EMPLOYEES' => 'Mitarbeiter',
'PRODUCTS_MANUFACTURERS_FOUNDER' => 'Gründer',
'PRODUCTS_MANUFACTURERS_FOUNDINGYEAR' => 'Gründungsjahr',
'PRODUCTS_MANUFACTURERS_FOUNDINGCOUNTRY' => 'Gründungsland',
'PRODUCTS_MANUFACTURERS_TURNOVER' => 'Jahresumsatz',
);


$lang['manusearch'] = array (
'HEADLINE_SEARCH' => 'Suchergebnisse',
'SEARCH' => 'Hersteller suchen',
'ITEM' => 'Suchbegriffe',
'CONNAND' => 'UND-Verknüpfung',
'CONNOR' => 'ODER-Verknüpfung',
'TYPE' => 'Typ',
'TYPE_PERSON' => 'Person',
'TYPE_COMPANY' => 'Firma',
'MSG_NORESULT' => 'Die Suchanfrage ergab keine Treffer!',

/* Neue Variabeln mit Präfixe */
'PRODUCTS_MANUSEARCH_SEARCHRESULT' => 'Suchergebnisse',
'PRODUCTS_MANUSEARCH_SEARCHMANUFACTURER' => 'Hersteller suchen',
'PRODUCTS_MANUSEARCH_KEYWORD' => 'Suchbegriffe',
'PRODUCTS_MANUSEARCH_CONNAND' => 'UND-Verknüpfung',
'PRODUCTS_MANUSEARCH_CONNOR' => 'ODER-Verknüpfung',
'PRODUCTS_MANUSEARCH_TYPE' => 'Typ',
'PRODUCTS_MANUSEARCH_TYPEPERSON' => 'Person',
'PRODUCTS_MANUSEARCH_TYPECOMPANY' => 'Firma',
'PRODUCTS_MANUSEARCH_SEARCHNORESULT' => 'Die Suchanfrage ergab keine Treffer!',

);


$lang['func_stats'] = array (
'PRODUCTS' => 'Produkte',
'PRODUCTS_NORMAL' => 'Allgemeine Produkte',
'PRODUCTS_GAME' => 'Videospiele',
'PRODUCTS_MUSIC' => 'Musik-Produkte',
'PRODUCTS_MOVIE' => 'Filme',
'PRODUCTS_BOOK' => 'Bücher',
'PRODUCTS_SOFTWARE' => 'Software-Produkte',
'PRODUCTS_HARDWARE' => 'Hardware-Produkte',
'AVG_HITS' => 'Klicks durchschnittlich',

/* Neue Variabeln mit Präfixe */
'PRODUCTS_FUNCSTATS_PRODUCTS' => 'Produkte',
'PRODUCTS_FUNCSTATS_GENERAL' => 'Allgemeine Produkte',
'PRODUCTS_FUNCSTATS_LITERATURE' => 'Literatur',
'PRODUCTS_FUNCSTATS_MUSIC' => 'Musik-Produkte',
'PRODUCTS_FUNCSTATS_MOVIE' => 'Filme',
'PRODUCTS_FUNCSTATS_VIDEOGAME' => 'Videospiele',
'PRODUCTS_FUNCSTATS_SOFTWARE' => 'Software-Produkte',
'PRODUCTS_FUNCSTATS_HARDWARE' => 'Hardware-Produkte',
'PRODUCTS_FUNCSTATS_HITSAVG' => 'Durchschnittliche Aufrufe',
);


$lang['func_search'] = array (
'SEARCH_PRODUCTS' => 'Produkte',

/* Neue Variabeln mit Präfixe */
'PRODUCTS_FUNCSEARCH_PRODUCTS' => 'Produkte',

);


?>
