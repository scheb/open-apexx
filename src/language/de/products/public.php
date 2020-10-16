<?php

//
// German Language Pack
// ====================
//

$lang['types'] = [
    'TYPE_ALL' => 'Alle',
    'TYPE_NORMAL' => 'Allgemein',
    'TYPE_GAME' => 'Videospiel',
    'TYPE_SOFTWARE' => 'Software',
    'TYPE_HARDWARE' => 'Hardware',
    'TYPE_MUSIC' => 'Musik',
    'TYPE_MOVIE' => 'Film',
    'TYPE_BOOK' => 'Literatur',
];

$lang['fields'] = [
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
];

$lang['products'] = array_merge($lang['fields'], $lang['types'], [
    'PRODUCTINFO' => 'Produkt-Informationen',
    'ADD_COLLECTION' => 'Zu meiner Sammlung hinzufügen',
    'REMOVE_COLLECTION' => 'Aus meiner Sammlung entfernen',
    'HEADLINE' => 'Produkte',
    'SORTBY' => 'Sortieren nach',
    'NONE' => 'Keine Produkte gefunden!',
]);

$lang['collection'] = array_merge($lang['products'], [
    'HEADLINE_COLLECTION' => 'Meine Sammlung',
    'MSG_COLL_ADD' => 'Das Produkt wurde Ihrer Sammlung hinzugefügt!',
    'MSG_COLL_REMOVE' => 'Das Produkt wurde aus Ihrer Sammlung entfernt!',
]);

$lang['search'] = [
    'HEADLINE_SEARCH' => 'Suchergebnisse',
    'SEARCH' => 'Produkte suchen',
    'ITEM' => 'Suchbegriffe',
    'CONNAND' => 'UND-Verknüpfung',
    'CONNOR' => 'ODER-Verknüpfung',
    'TYPE' => 'Typ',
    'MSG_NORESULT' => 'Die Suchanfrage ergab keine Treffer!',
];

$lang['manufacturers'] = [
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
];

$lang['manusearch'] = [
    'HEADLINE_SEARCH' => 'Suchergebnisse',
    'SEARCH' => 'Hersteller suchen',
    'ITEM' => 'Suchbegriffe',
    'CONNAND' => 'UND-Verknüpfung',
    'CONNOR' => 'ODER-Verknüpfung',
    'TYPE' => 'Typ',
    'TYPE_PERSON' => 'Person',
    'TYPE_COMPANY' => 'Firma',
    'MSG_NORESULT' => 'Die Suchanfrage ergab keine Treffer!',
];

$lang['func_stats'] = [
    'PRODUCTS' => 'Produkte',
    'PRODUCTS_NORMAL' => 'Allgemeine Produkte',
    'PRODUCTS_GAME' => 'Videospiele',
    'PRODUCTS_MUSIC' => 'Musik-Produkte',
    'PRODUCTS_MOVIE' => 'Filme',
    'PRODUCTS_BOOK' => 'Bücher',
    'PRODUCTS_SOFTWARE' => 'Software-Produkte',
    'PRODUCTS_HARDWARE' => 'Hardware-Produkte',
    'AVG_HITS' => 'Klicks durchschnittlich',
];

$lang['func_search'] = [
    'SEARCH_PRODUCTS' => 'Produkte',
];
