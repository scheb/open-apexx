<?php

//
// German Language Pack
// ====================
//

// MODULE NAME
$lang['modulename']['MODULENAME_STATS'] = 'Besucherstatistik';

// HEADLINES
$lang['titles'] = [
    'TITLE_STATS_VISITORS' => 'Besucherzahlen',
    'TITLE_STATS_REFERER' => 'Verweisende Seiten',
    'TITLE_STATS_SEARCHED' => 'Suchmaschinen-Treffer',
    'TITLE_STATS_AGENTS' => 'Browser',
    'TITLE_STATS_OS' => 'Betriebssysteme',
    'TITLE_STATS_COUNTRIES' => 'Herkunftsländer',
];

// NAVIGATION
$lang['navi'] = [
    'NAVI_STATS_VISITORS' => 'Besucherzahlen',
    'NAVI_STATS_REFERER' => 'Verweisende Seiten',
    'NAVI_STATS_SEARCHED' => 'Suchmaschinen-Treffer',
    'NAVI_STATS_AGENTS' => 'Browser',
    'NAVI_STATS_OS' => 'Betriebssysteme',
    'NAVI_STATS_COUNTRIES' => 'Herkunftsländer',
];

// ACTION EXPLICATION
$lang['expl'] = [
];

// LOG MESSAGES
$lang['log'] = [
];

// CONFIG
$lang['config'] = [
    'STARTCOUNT' => 'Startwert des Besucherzählers',
    'BLOCKIP' => 'Dauer der IP-Sperre in Stunden:',
    'OWNREFERER' => 'Referer-URLs von der eigenen Seite speichern?',
    'COOKIE' => 'Mehrfach-Zählungen mit Cookie verhindern?',
    'COUNTSEARCHENGINE' => 'Suchmaschinen in die Besucherstatistik einbeziehen?',
];

// ACTIONS

//VISITORS
$lang['actions']['visitors'] = [
    'LAYER_TABLE' => 'Tabellen',
    'LAYER_GRAPH' => 'Diagramme',
    'TOTAL' => 'Gesamt',
    'VISITORS' => 'Besucher',
    'HITS' => 'Klicks',
    'RECORD' => 'Besucherrekord am',
    'PROGNOSIS' => 'Prognose für heute',
    'LAST30DAYS' => 'Die letzten 30 Tage',
    'LAST50DAYS' => 'Die letzten 50 Tage',
    'LAST30WEEKS' => 'Die letzten 30 Wochen',
    'LAST6MONTHS' => 'Die letzten 6 Monate',
    'HOURVISITS' => 'Besucher pro Stunde',
    'WEEKDAYVISITS' => 'Besucher je Wochentag',
    'CALWEEK' => 'Kalenderwoche',
];

//REFERER
$lang['actions']['referer'] =
$lang['actions']['searched'] =
[
    'REFERER_INFO' => 'Diese Statistik bezieht sich auf die letzten 30 Tage',
    'SEARCHED_INFO' => 'Wenn der Benutzer die Seite über eine Suchmaschine gefunden hat, wird der Suchbegriff hier aufgezeichnet (letzte 30 Tage)',
    'LAYER_STATS' => 'Statistik',
    'LAYER_FILTER' => 'Filter',
    'MOSTHITS' => 'Top 20 - Meiste Verweise',
    'LATEST' => 'Neueste Verweise',
    'HOSTHITS' => 'Top 20 - Hosts mit den meisten Verweisen',
    'FROMHOST' => 'Verweise vom Host',
    'GOBACK' => 'Zurück zur Übersicht',
    'ADDFILTER' => 'Filter hinzufügen',
    'MSG_TEXT_FILTERDEL' => 'Wollen Sie den Filter wirklich löschen?',
    'NONE' => 'Keine Filter eingetragen!',
    'HOST' => 'Host/Domain',
    'WILDCARD' => '* = beliebige Zeichen',
    'HITS' => 'Klicks',
    'URL' => 'Adresse',
    'SEARCHSTRING' => 'Suchbegriffe',
];

//BROWSER + OS + COUNTRIES
$lang['actions']['agents'] =
$lang['actions']['os'] =
$lang['actions']['countries'] =
[
    'INFO' => 'Diese Statistik bezieht sich auf die letzten 30 Tage',
    'BROWSER' => 'Browser',
    'OS' => 'Betriebsystem',
    'COUNTRY' => 'Herkunft',
    'PART' => 'Anteil',
    'SEARCHENGINE' => 'Suchmaschine',
    'UNKNOWN' => 'Unbekannt',
];
