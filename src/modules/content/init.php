<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

//Modul registrieren
$module = [1, 950,
    'id' => 'content',
    'dependence' => ['comments', 'ratings'],
    'requirement' => ['main' => '1.2.0'],
    'version' => '1.1.2',
    'author' => 'Christian Scheb',
    'contact' => 'http://www.stylemotion.de',
    'mediainput' => [
        1 => [
            'icon' => '<img src="design/mm/insert_text.gif" alt="{MM_INSERTCONTENT}" title="{MM_INSERTCONTENT}" style="vertical-align:middle;" />',
            'function' => 'top.opener.insert_image(\'text\',\'{PATH}\')',
            'filetype' => ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'],
            'urlrel' => 'httpdir',
        ],
    ],
];

//Aktionen registrieren     S V O R
$action['show'] = [0, 1, 1, 0];
$action['add'] = [0, 1, 2, 0];
$action['edit'] = [1, 0, 3, 0];
$action['del'] = [1, 0, 4, 0];
$action['enable'] = [1, 0, 5, 0];
$action['disable'] = [1, 0, 6, 0];
$action['group'] = [0, 1, 7, 0];

/*
S = Sonderrechte
V = Sichtbar (Visibility)
O = Anordnung (Order)
R = Rechte für Alle
*/

//Template-Funktionen     F           V
//$func['']=array('',true);
$func['CONTENT_STATS'] = ['content_stats', true];
$func['CONTENT_SHOW'] = ['content_show', true];

/*
F = Funktions-Name
V = Variablen akzeptieren
*/
