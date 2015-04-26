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


//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Kalenderwoche berechnen
function stats_weekstamp($time) {
	//Wenn Kalenderwoche >= 52 und wir uns im Januar befinden
	//-> Kalenderwoche gehört zum vorherigen Jahr!
	if ( intval(date('W',$time-TIMEDIFF))>=52 && intval(date('n',$time-TIMEDIFF))==1 ) {
		return (date('Y',$time-TIMEDIFF)-1).sprintf('%02d',date('W',$time-TIMEDIFF));
	}
	
	return date('Y',$time-TIMEDIFF).sprintf('%02d',date('W',$time-TIMEDIFF));
}



//Betriebsystem holen 
function stats_os($info) {
	if ( strpos(strtolower($info), 'windows nt 5.1')!==false || strpos(strtolower($info), 'windows xp')!==false ) return 'Windows XP';
	elseif ( strpos(strtolower($info), 'windows nt 10')!==false || strpos(strtolower($info), 'windows 10')!==false ) return 'Windows 10';
	elseif ( strpos(strtolower($info), 'windows nt 6.4')!==false || strpos(strtolower($info), 'windows 10')!==false ) return 'Windows 10';
	elseif ( strpos(strtolower($info), 'windows nt 6.3')!==false || strpos(strtolower($info), 'windows 8.1')!==false ) return 'Windows 8.1';
	elseif ( strpos(strtolower($info), 'windows nt 6.2')!==false || strpos(strtolower($info), 'windows 8')!==false ) return 'Windows 8';
	elseif ( strpos(strtolower($info), 'windows nt 6.1')!==false || strpos(strtolower($info), 'windows 7')!==false ) return 'Windows 7';
	elseif ( strpos(strtolower($info), 'windows nt 6.0')!==false || strpos(strtolower($info), 'windows vista')!==false ) return 'Windows Vista';
	elseif ( strpos(strtolower($info), 'windows nt 5.2')!==false ) return 'Windows 2003 Server';
	elseif ( strpos(strtolower($info), 'windows nt 4.0')!==false ) return 'Windows NT 4.0';
	elseif ( strpos(strtolower($info), 'windows nt 5.0')!==false || strpos(strtolower($info), 'windows 2000')!==false ) return 'Windows 2000';
	elseif ( strpos(strtolower($info), 'win 9x 4.90')!==false || strpos(strtolower($info), 'windows me')!==false ) return 'Windows ME';
	elseif ( strpos(strtolower($info), 'windows 98')!==false || strpos(strtolower($info), 'windows 95')!==false ) return 'Windows 98/95';
	elseif ( strpos(strtolower($info), 'linux')!==false ) return 'Linux';
	elseif ( strpos(strtolower($info), 'mac')!==false || strpos(strtolower($info), 'ppc')!==false ) return 'Mac OS';
	elseif ( strpos(strtolower($info), 'freebsd')!==false ) return 'FreeBSD';
	elseif ( strpos(strtolower($info), 'sunos')!==false ) return 'Sun OS';
	elseif ( strpos(strtolower($info), 'irix')!==false ) return 'IRIX';
	elseif ( strpos(strtolower($info), 'beos')!==false ) return 'BeOS';
	elseif ( strpos(strtolower($info), 'os/2')!==false ) return 'OS/2';
	elseif ( strpos(strtolower($info), 'aix')!==false ) return 'AIX';
	elseif( 
	   strpos(strtolower($info), 'bot')!==false
	|| strpos(strtolower($info), 'google')!==false
	|| strpos(strtolower($info), 'slurp')!==false
	|| strpos(strtolower($info), 'scooter')!==false
	|| strpos(strtolower($info), 'spider')!==false
	|| strpos(strtolower($info), 'crawler')!==false
	|| strpos(strtolower($info), 'infoseek')!==false
	) return 'SEARCHENGINE';
	
	return 'UNKNOWN';
}



//Browser holen
function stats_browser($info) {
	if (
	   strpos(strtolower($info), 'bot')!==false
	|| strpos(strtolower($info), 'google')!==false
	|| strpos(strtolower($info), 'slurp')!==false
	|| strpos(strtolower($info), 'scooter')!==false
	|| strpos(strtolower($info), 'spider')!==false
	|| strpos(strtolower($info), 'crawler')!==false
	|| strpos(strtolower($info), 'infoseek')!==false
	) return 'SEARCHENGINE';
	elseif( strpos(strtolower($info), 'opera')!==false ) return 'Opera';
	elseif( strpos(strtolower($info), 'chrome')!==false ) return 'Chrome';
	elseif( strpos(strtolower($info), 'msie 9.0')!==false ) return 'MSIE 9.0';
	elseif( strpos(strtolower($info), 'msie 8.0')!==false ) return 'MSIE 8.0';
	elseif( strpos(strtolower($info), 'msie 7.0')!==false ) return 'MSIE 7.0';
	elseif( strpos(strtolower($info), 'msie')!==false ) return 'MSIE';
	elseif( strpos(strtolower($info), 'konqueror')!==false ) return 'Konqueror';
	elseif( strpos(strtolower($info), 'lynx')!==false ) return 'Lynx';
	elseif( strpos(strtolower($info), 'safari')!==false ) return 'Safari';
	elseif( strpos(strtolower($info), 'firefox')!==false ) return 'Firefox';
	elseif( strpos(strtolower($info), 'netscape')!==false ) return 'Netscape';
	elseif( preg_match('#(rv:)([0-9]{1,2}.[0-9]{1,3})#', $info) ) return 'Mozilla';
	elseif( strpos(strtolower($info), 'mozilla')!==false ) return 'Mozilla';
	
	return 'UNKNOWN';
}



//Herkunftsland holen
function stats_country($info) {
	static $country_definition;
	$info=strtolower($info);
	
	if ( !isset($country_definition) ) $country_definition = array (
		'ac'=>'Ascension Island',
		'ad'=>'Andorra',
		'ae'=>'United Arab Emirates',
		'af'=>'Afghanistan',
		'ag'=>'Antigua and Barbuda',
		'ai'=>'Anguilla',
		'al'=>'Albania',
		'am'=>'Armenia',
		'an'=>'Netherlands Antilles',
		'ao'=>'Angola',
		'aq'=>'Antarctica',
		'ar'=>'Argentina',
		'as'=>'American Samoa',
		'at'=>'Austria',
		'au'=>'Australia',
		'aw'=>'Aruba',
		'az'=>'Azerbaijan',
		'ba'=>'Bosnia and Herzegovina',
		'bb'=>'Barbados',
		'bd'=>'Bangladesh',
		'be'=>'Belgium',
		'bf'=>'Burkina Faso',
		'bg'=>'Bulgaria',
		'bh'=>'Bahrain',
		'bi'=>'Burundi',
		'bj'=>'Benin',
		'bm'=>'Bermuda',
		'bn'=>'Brunei Darussalam',
		'bo'=>'Bolivia',
		'br'=>'Brazil',
		'bs'=>'Bahamas',
		'bt'=>'Bhutan',
		'bv'=>'Bouvet Island',
		'bw'=>'Botswana',
		'by'=>'Belarus',
		'bz'=>'Belize',
		'ca'=>'Canada',
		'cc'=>'Cocos (Keeling) Islands',
		'cd'=>'Congo, Democratic Republic of the',
		'cf'=>'Central African Republic',
		'cg'=>'Congo, Republic of',
		'ch'=>'Switzerland',
		'ci'=>'Cote d\'Ivoire',
		'ck'=>'Cook Islands',
		'cl'=>'Chile',
		'cm'=>'Cameroon',
		'cn'=>'China',
		'co'=>'Colombia',
		'cr'=>'Costa Rica',
		'cu'=>'Cuba',
		'cv'=>'Cap Verde',
		'cx'=>'Christmas Island',
		'cy'=>'Cyprus',
		'cz'=>'Czech Republic',
		'de'=>'Germany',
		'dj'=>'Djibouti',
		'dk'=>'Denmark',
		'dm'=>'Dominica',
		'do'=>'Dominican Republic',
		'dz'=>'Algeria',
		'ec'=>'Ecuador',
		'ee'=>'Estonia',
		'eg'=>'Egypt',
		'eh'=>'Western Sahara',
		'er'=>'Eritrea',
		'es'=>'Spain',
		'et'=>'Ethiopia',
		'fi'=>'Finland',
		'fj'=>'Fiji',
		'fk'=>'Falkland Islands (Malvina)',
		'fm'=>'Micronesia, Federal State of',
		'fo'=>'Faroe Islands',
		'fr'=>'France',
		'ga'=>'Gabon',
		'gd'=>'Grenada',
		'ge'=>'Georgia',
		'gf'=>'French Guiana',
		'gg'=>'Guernsey',
		'gh'=>'Ghana',
		'gi'=>'Gibraltar',
		'gl'=>'Greenland',
		'gm'=>'Gambia',
		'gn'=>'Guinea',
		'gp'=>'Guadeloupe',
		'gq'=>'Equatorial Guinea',
		'gr'=>'Greece',
		'gs'=>'South Georgia and the South Sandwich Islands',
		'gt'=>'Guatemala',
		'gu'=>'Guam',
		'gw'=>'Guinea Bissau',
		'gy'=>'Guyana',
		'hk'=>'Hong Kong',
		'hm'=>'Heard and McDonald Islands',
		'hn'=>'Honduras',
		'hr'=>'Croatia/Hrvatska',
		'ht'=>'Haiti', 'hu'=>'Hungary',
		'id'=>'Indonesia',
		'ie'=>'Ireland',
		'il'=>'Israel',
		'im'=>'Isle of Man',
		'in'=>'India',
		'io'=>'British Indian Ocean Territory',
		'iq'=>'Iraq',
		'ir'=>'Iran (Islamic Republic of)',
		'is'=>'Iceland',
		'it'=>'Italy',
		'je'=>'Jersey',
		'jm'=>'Jamaica',
		'jo'=>'Jordan',
		'jp'=>'Japan',
		'ke'=>'Kenya',
		'kg'=>'Kyrgyzstan',
		'kh'=>'Cambodia',
		'ki'=>'Kiribati',
		'km'=>'Comoros',
		'kn'=>'Saint Kitts and Nevis',
		'kp'=>'Korea, Democratic People\'s Republic',
		'kr'=>'Korea, Republic of',
		'kw'=>'Kuwait',
		'ky'=>'Cayman Islands',
		'kz'=>'Kazakhstan',
		'la'=>'Lao People\'s Democratic Republic',
		'lb'=>'Lebanon',
		'lc'=>'Saint Lucia',
		'li'=>'Liechtenstein',
		'lk'=>'Sri Lanka',
		'lr'=>'Liberia',
		'ls'=>'Lesotho',
		'lt'=>'Lithuania',
		'lu'=>'Luxembourg',
		'lv'=>'Latvia',
		'ly'=>'Libyan Arab Jamahiriya',
		'ma'=>'Morocco',
		'mc'=>'Monaco',
		'md'=>'Moldova, Republic of',
		'mg'=>'Madagascar',
		'mh'=>'Marshall Islands',
		'mk'=>'Macedonia, Former Yugoslav Republic',
		'ml'=>'Mali',
		'mm'=>'Myanmar',
		'mn'=>'Mongolia',
		'mo'=>'Macau',
		'mp'=>'Northern Mariana Islands',
		'mq'=>'Martinique',
		'mr'=>'Mauritania',
		'ms'=>'Montserrat',
		'mt'=>'Malta',
		'mu'=>'Mauritius',
		'mv'=>'Maldives',
		'mw'=>'Malawi',
		'mx'=>'Mexico',
		'my'=>'Malaysia',
		'mz'=>'Mozambique',
		'na'=>'Namibia',
		'nc'=>'New Caledonia',
		'ne'=>'Niger',
		'nf'=>'Norfolk Island',
		'ng'=>'Nigeria',
		'ni'=>'Nicaragua',
		'nl'=>'Netherlands',
		'no'=>'Norway',
		'np'=>'Nepal',
		'nr'=>'Nauru',
		'nu'=>'Niue',
		'nz'=>'New Zealand',
		'om'=>'Oman',
		'pa'=>'Panama',
		'pe'=>'Peru',
		'pf'=>'French Polynesia',
		'pg'=>'Papua New Guinea',
		'ph'=>'Philippines',
		'pk'=>'Pakistan',
		'pl'=>'Poland',
		'pm'=>'St Pierre and Miquelon',
		'pn'=>'Pitcairn Island',
		'pr'=>'Puerto Rico',
		'ps'=>'Palestinian Territories',
		'pt'=>'Portugal',
		'pw'=>'Palau',
		'py'=>'Paraguay',
		'qa'=>'Qatar',
		're'=>'Reunion Island',
		'ro'=>'Romania',
		'ru'=>'Russian Federation',
		'rw'=>'Rwanda',
		'sa'=>'Saudi Arabia',
		'sb'=>'Solomon Islands',
		'sc'=>'Seychelles',
		'sd'=>'Sudan',
		'se'=>'Sweden',
		'sg'=>'Singapore',
		'sh'=>'St Helena',
		'si'=>'Slovenia',
		'sj'=>'Svalbard and Jan Mayen Islands',
		'sk'=>'Slovak Republic',
		'sl'=>'Sierra Leone',
		'sm'=>'San Marino',
		'sn'=>'Senegal',
		'so'=>'Somalia',
		'sr'=>'Suriname',
		'st'=>'Sao Tome and Principe',
		'sv'=>'El Salvador',
		'sy'=>'Syrian Arab Republic',
		'sz'=>'Swaziland',
		'tc'=>'Turks and Caicos Islands',
		'td'=>'Chad',
		'tf'=>'French Southern Territories',
		'tg'=>'Togo',
		'th'=>'Thailand',
		'tj'=>'Tajikistan',
		'tk'=>'Tokelau',
		'tm'=>'Turkmenistan',
		'tn'=>'Tunisia',
		'to'=>'Tonga',
		'tp'=>'East Timor',
		'tr'=>'Turkey',
		'tt'=>'Trinidad and Tobago',
		'tv'=>'Tuvalu',
		'tw'=>'Taiwan',
		'tz'=>'Tanzania',
		'ua'=>'Ukraine',
		'ug'=>'Uganda',
		'uk'=>'United Kingdom',
		'um'=>'US Minor Outlying Islands',
		'us'=>'United States',
		'uy'=>'Uruguay',
		'uz'=>'Uzbekistan',
		'va'=>'Holy See (City Vatican State)',
		'vc'=>'Saint Vincent and the Grenadines',
		've'=>'Venezuela',
		'vg'=>'Virgin Islands (British)',
		'vi'=>'Virgin Islands (USA)',
		'vn'=>'Vietnam',
		'vu'=>'Vanuatu',
		'wf'=>'Wallis and Futuna Islands',
		'ws'=>'Western Samoa',
		'ye'=>'Yemen',
		'yt'=>'Mayotte',
		'yu'=>'Yugoslavia',
		'za'=>'South Africa',
		'zm'=>'Zambia',
		'zw'=>'Zimbabwe',
		'aero'=>'Aviation (.aero)',
		'biz'=>'Business (.biz)',
		'coop'=>'Co-Operative Orga. (.coop)',
		'info'=>'Info (.info)',
		'int'=>'Intern.Organisation (.int)',
		'name'=>'Homepage (.name)',
		'us'=>'Museum (.museum)',
		'us'=>'USA (.us)',
		'org'=>'Organisation (.org)',
		'com'=>'Commercial (.com)',
		'net'=>'Network (.net)',
		'org'=>'Organisation (.org)',
		'edu'=>'University USA (.edu)',
		'gov'=>'Government USA (.gov)',
		'mil'=>'US Army (.mil)'
	);
	
	if ( array_key_exists($info,$country_definition) ) return $country_definition[$info];
	else return 'UNKNOWN';
}


//Host auslesen
function stats_host($url) {
	$host=substr($url,7);
	if ( strpos($host,'/')!==false ) $host=substr($host,0,strpos($host,'/'));
	return $host;
}



//Suchstring
function stats_searchstring($url) {
	$url=urldecode($url);
	$host=strtolower(stats_host($url));
	
	if ( preg_match('#^(www\.)?google\.[a-z]{2,4}$#',$host) ) $searchstring=stats_param_value($url,'q'); //Google
	if ( preg_match('#^([a-z]+\.)?search.yahoo\.com$#',$host) ) $searchstring=stats_param_value($url,'p'); //Yahoo
	if ( preg_match('#^search.msn\.[a-z]{2,4}$#',$host) ) $searchstring=stats_param_value($url,'q'); //MSN
	if ( preg_match('#^([a-z]+\.)?altavista\.com$#',$host) ) $searchstring=stats_param_value($url,'q'); //Altavista
	if ( $host=='brisbane.t-online.de' ) $searchstring=stats_param_value($url,'q'); //T-Online
	if ( $host=='suche.lycos.com' ) $searchstring=stats_param_value($url,'query'); //Lycos
	if ( $host=='suche.fireball.de' ) $searchstring=stats_param_value($url,'query'); //Fireball
	if ( $host=='search.aol.com' ) $searchstring=stats_param_value($url,'query'); //AOL
	if ( $host=='suche.aol.de' ) $searchstring=stats_param_value($url,'q'); //AOL Deutsch
	if ( $host=='search.live.com' ) $searchstring=stats_param_value($url,'q'); //Live.com
	
	return trim($searchstring);
}



//Wert des Parameter auslesen
function stats_param_value($url,$getparam) {
	if ( strpos($url,'?')===false ) return '';
	$parts=explode('?',$url,2);
	$qstring=array_pop($parts);
	$params=explode('&',$qstring);
	
	foreach ( $params AS $param ) {
		$pp=explode('=',$param,2);
		$varname=trim($pp[0]);
		$value=trim($pp[1]);
		if ( $varname!=$getparam ) continue;
		if ( !$value ) break;
		return $value;
	}
}

?>