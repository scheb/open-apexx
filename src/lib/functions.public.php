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


# Diverse Funktionen (Public)
# ===========================


//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

////////////////////////////////////////////////////////////////////////////////// -> HEADLINES

//Headline
function headline($text,$url='') {
	global $apx;
	$apx->tmpl->headline(strip_tags($text),$url);
}


//Titelleiste
function titlebar($text) {
	global $apx;
	$apx->tmpl->titlebar(strip_tags($text));
}



////////////////////////////////////////////////////////////////////////////////// -> BUCHSTABEN AUFLISTEN


//Buchstaben-Auswahl
function letters($link) {
	global $apx;
	static $letters;
	if ( !isset($letters) ) $letters=array('#','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
	
	if ( strpos($link,'?')!==false ) $sticky='&amp;';
	else $sticky='?';
	
	if ( strpos($link,'{LETTER}')!==false ) $fulllink=str_replace('{LETTER}','0',$link);
	else $fulllink=$link;
	
	$letterdata[]=array(
		'TEXT' => $apx->lang->get('CORE_NOLETTER'),
		'LINK' => $fulllink,
		'SELECTED' => iif(!$_REQUEST['letter'],1,0)
	);
	
	//Buchstaben
	foreach ( $letters AS $letter ) {
		$selected=0;
		if ( $letter=='#' && $_REQUEST['letter']=='spchar' ) $selected=1;
		if ( $letter==$_REQUEST['letter'] ) $selected=1;
		
		if ( strpos($link,'{LETTER}')!==false ) $fulllink=str_replace('{LETTER}',iif($letter=='#','spchar',$letter),$link);
		else $fulllink=$link.$sticky.'letter='.iif($letter=='#',iif($letter=='#','spchar',$letter),$letter);
		
		$letterdata[]=array(
			'TEXT' => strtoupper($letter),
			'LINK' => $fulllink,
			'SELECTED' => $selected
		);
	}
	
	
	$apx->tmpl->assign('LETTER_SELECTED',$_REQUEST['letter']);
	$apx->tmpl->assign('LETTER',$letterdata);
}



////////////////////////////////////////////////////////////////////////////////// -> SEITENZAHLEN

function pages($link,$count,$epp=0,$varname='p',$pre='') {
	global $set,$apx;
	$count=(int)$count;
	$epp=(int)$epp;
	/*if ( !$epp ) {
		echo 'can not create pages! EPP is 0.';
		return;
	}*/
	
	//Variablen vorbereiten
	$_REQUEST[$varname]=(int)$_REQUEST[$varname];
	if ( strpos($link,'?')!==false ) $sticky='&amp;'; else $sticky='?';
	
	//Seitenzahlen berechnen, evtl. REQUEST berichtigen
	$pages=ceil($count/iif($epp,$epp,1));
	if ( $_REQUEST[$varname]<1 || $_REQUEST[$varname]>$pages ) $_REQUEST[$varname]=1;
	
	//Wenn kein $epp, alle Einträge zeigen -> Seiten (1): [1]
	if ( $epp==0 ) {
		$apx->tmpl->assign(iif($pre,$pre.'_').'PAGE_COUNT',1);
		$apx->tmpl->assign(iif($pre,$pre.'_').'PAGE',array(array(
			'NUMBER'=>1,
			'LINK'=>'#'
		)));
		return;
	}
	
	//Wenn es keine Einträge gibt
	if ( !$count ) {
		$apx->tmpl->assign(iif($pre,$pre.'_').'PAGE_COUNT',0);
		return;
	}
	
	//Seitenzahlen generieren
	for ( $i=1; $i<=$pages; $i++ ) {
		if ( strpos($link,'{P}')!==false ) $finallink=str_replace('{P}',$i,$link);
		else $finallink=$link.$sticky.$varname.'='.$i;
		$pagedata[$i]['NUMBER']=$i;
		$pagedata[$i]['LINK']=$finallink;
	}
	
	//Previous
	if ( $_REQUEST[$varname]>1 ) {
		if ( strpos($link,'{P}')!==false ) $link_previous=str_replace('{P}',$_REQUEST[$varname]-1,$link);
		else $link_previous=$link.$sticky.$varname.'='.($_REQUEST[$varname]-1);
	}
	
	//Next
	if ( $_REQUEST[$varname]<$pages ) {
		if ( strpos($link,'{P}')!==false ) $link_next=str_replace('{P}',$_REQUEST[$varname]+1,$link);
		else $link_next=$link.$sticky.$varname.'='.($_REQUEST[$varname]+1);
	}
	
	//First
	if ( $_REQUEST[$varname]>1 ) {
		if ( strpos($link,'{P}')!==false ) $link_first=str_replace('{P}','1',$link);
		else $link_first=$link.$sticky.$varname.'=1';
	}
	
	//Last
	if ( $_REQUEST[$varname]<$pages ) {
		if ( strpos($link,'{P}')!==false ) $link_last=str_replace('{P}',$pages,$link);
		else $link_last=$link.$sticky.$varname.'='.$pages;
	}
	
	$apx->tmpl->assign(iif($pre,$pre.'_').'PAGE_PREVIOUS',$link_previous);
	$apx->tmpl->assign(iif($pre,$pre.'_').'PAGE_NEXT',$link_next);
	$apx->tmpl->assign(iif($pre,$pre.'_').'PAGE_FIRST',$link_first);
	$apx->tmpl->assign(iif($pre,$pre.'_').'PAGE_LAST',$link_last);
	$apx->tmpl->assign(iif($pre,$pre.'_').'PAGE_COUNT',$pages);
	$apx->tmpl->assign(iif($pre,$pre.'_').'PAGE_SELECTED',$_REQUEST[$varname]);
	$apx->tmpl->assign(iif($pre,$pre.'_').'PAGE',$pagedata);
}



////////////////////////////////////////////////////////////////////////////////// -> MYSQL

//Sektionen filtern
function section_filter($and=true,$fieldname='secid') {
	global $apx,$user;
	
	$sections=$apx->sections;
	$secid=$apx->section_id();
	
	/////// Keine Sektionen verwendet -> Alles anzeigen
	if ( !is_array($sections) || !count($sections) ) return iif($and,' AND ')." 1 "; //true
	
	/////// Nur gewählte Sektion
	if ( $secid ) return iif($and,' AND ')." ( ".$fieldname."='all' OR ".$fieldname." LIKE '%|".$secid."|%' ) ";
	
	/////// Alle erlaubten Sektionen auflisten
	
	//Offene Sektionen filtern
	$opensec=array();
	foreach ( $sections AS $secid => $info ) {
		if ( !$info['active'] ) continue;
		$opensec[]=$secid;
	}
	
	//Sektionen, die der User ansehen darf
	$allowsec=array();
	$secacc=unserialize($user->info['section_access']);
	if ( is_string($user->info['section_access']) && $user->info['section_access']=='all' ) $allowed=$opensec;
	elseif ( !is_array($secacc) ) $allowed=array();
	else $allowed=array_intersect($secacc,$opensec);
	
	//Standard-Sektion immer erlaubt
	$allowed[]=$apx->section_default;
	$allowed=array_unique($allowed);
	
	foreach ( $allowed AS $secid ) {
		$secstring.=" OR ".$fieldname." LIKE '%|".$secid."|%' ";
	}
	
	return iif($and,' AND ')." ( ".$fieldname."='all' ".$secstring." )";
}



////////////////////////////////////////////////////////////////////////////////// -> SORTIEREN NACH-VARIABLEN

//Order Variablen
function ordervars($orderdef,$link) {
	global $apx;
	list($currentkey,$currentord)=explode('.',$_REQUEST['sortby']);
	
	foreach ( $orderdef AS $key => $info ) {
		if ( $key==$currentkey ) {
			if ( strpos($link,'?')!==false ) $sortlink=$link.'&amp;sortby='.$key.'.'.iif($currentord=='ASC','DESC','ASC');
			else $sortlink=$link.'?sortby='.$key.'.'.iif($currentord=='ASC','DESC','ASC');
		}
		else {
			if ( strpos($link,'?')!==false ) $sortlink=$link.'&amp;sortby='.$key.'.'.$info[1];
			else $sortlink=$link.'?sortby='.$key.'.'.$info[1];
		}
		
		$apx->tmpl->assign('SORTBY_'.strtoupper($key),$sortlink);
	}
	
	$apx->tmpl->assign('SORTBY',$_REQUEST['sortby']);
}



////////////////////////////////////////////////////////////////////////////////// -> REPLACE FUNKTIONEN

//Für RSS formatieren
function rss_replace($text,$nostrip=false) {
	$text=str_replace("\r",'',str_replace("\n",' ',$text));
	if ( !$nostrip ) $text=strip_tags($text);
	
	//Bestehende Transformationen entfernen
	/*$trans=get_html_translation_table(HTML_ENTITIES);
	$transflip=array_flip($trans);
	$transflip['&nbsp;']=' ';
	$text=strtr($text,$transflip);
	
	//Sonderzeichen ersetzen
	$text=str_replace('&','&amp;',$text);
	$text=str_replace('"','&quot;',$text);*/
	
	//Doppelte Leerzeichen entfernen
	$text=trim(preg_replace('#( ){2,}#si',' ',$text));
	
	return $text;
}


//Codes aus der Datenbank
function dbcodes($text,$sig=false) {
	global $set;
	static $syntax;
	if ( !count($set['main']['codes']) ) return $text;
	
	if ( !isset($syntax) ) {
		foreach ( $set['main']['codes'] AS $res ) {
			if ( $res['count']==2 ) {
				$find='\['.$res['code'].'=(.*)\](.*)\[/'.$res['code'].'\]';
				$replace=str_replace('{1}','$1',str_replace('{2}','$2',$res['replace']));
			}
			else {
				$find='\['.$res['code'].'\](.*)\[/'.$res['code'].'\]';
				$replace=str_replace('{1}','$1',$res['replace']);
			}
			
			$syntax[]=array($find,$replace,$res['allowsig']);
		}
	}
	
	foreach ( $syntax AS $replace ) {
		if ( $sig && !$replace[2] ) continue;
		while ( preg_match('#'.$replace[0].'#siU',$text) ) {
			$text = preg_replace('#'.$replace[0].'#siU',$replace[1],$text);
		}
	}
	
	return $text;
}


//Smilies aus der Datenbank
function dbsmilies($text) {
	global $set;
	static $smilies;
	if ( !count($set['main']['smilies']) ) return $text;
	
	if ( !isset($smilies) ) {
		foreach ( $set['main']['smilies'] AS $res ) {
			if ( $res['file'][0]!='/' && defined('BASEREL') ) $filepath=BASEREL.$res['file'];
			else $filepath=$res['file'];
			$smilies[$res['code']]='<img src="'.$filepath.'" alt="'.replace($res['code']).iif($res['description'],' = '.replace($res['description'])).'" />';
		}
	}
	
	$text=strtr($text,$smilies);
	return $text;
}


//Badwords aus der Datenbank
function badwords($text) {
	global $set;
	static $badwords_find,$badwords_replace;
	if ( !count($set['main']['badwords']) ) return $text;
	
	if ( !isset($badwords_find) ) {
		foreach ( $set['main']['badwords'] AS $res ) {
			$badwords_find[]='#'.str_replace('#','\\#',preg_quote($res['find'])).'#i';
			$badwords_replace[]=$res['replace'];
		}
	}
	
	$text=preg_replace($badwords_find,$badwords_replace,$text);
	return $text;
}


//Code-Boxen herausfiltern
function getboxes($text) {
	return $text;
}


//Code-Boxen einfügen
function insertboxes($text) {
	return $text;
}



////////////////////////////////////////////////////////////////////////////////// -> CRONJOBS

//Cronjobs erkennen und ausführen
function cronexec() {
	global $set,$apx,$db;
	$now=time();
	
	$data=$db->fetch("SELECT funcname,period,lastexec FROM ".PRE."_cron WHERE lastexec+period<='".$now."'");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$cronexec[]=$res['funcname'];
		}
	}
	
	//Cronjob-Bild ausgeben
	if ( count($cronexec) ) {
		$hash=md5(microtime());
		$db->query("UPDATE ".PRE."_cron SET hash='".addslashes($hash)."' WHERE funcname IN ('".implode("','",$cronexec)."')");
		return $hash;
	}
	
	//Nichts tun
	else return false;
}



////////////////////////////////////////////////////////////////////////////////// -> TEMPLATE-VARIABLEN IN ARRAY

//Befindet sich mindestens eine der Variablen im Template?
function in_template($find,$parse) {
	if ( is_array($find) ) {
		foreach ( $find AS $findme ) {
			if ( in_array($findme,$parse) ) return true;
		}
	}
	elseif ( in_array($find,$parse) ) return true;
	else return false;
}



////////////////////////////////////////////////////////////////////////////////// -> ALTERSABFRAGE

function checkage() {
	global $apx, $set, $db, $user;
	
	//Alter wurde bereits bestätigt
	if ( $_COOKIE[$set['main']['cookie_pre'].'_checkage'] || $user->is_team_member() || $user->info['ageconfirmed'] ) {
		return;
	}
	
	$apx->lang->drop('checkage', 'main');
	
	//Alter prüfen
	if ( $_POST['checkage'] && $_POST['birthday']['day'] && $_POST['birthday']['month'] && $_POST['birthday']['year'] ) {
		$stamp = intval(sprintf('%04d%02d%02d', $_POST['birthday']['year'], $_POST['birthday']['month'], $_POST['birthday']['day']));
		$maxstamp = intval(sprintf('%04d%02d%02d', date('Y')-18, date('n'), date('j')));
		if ( $stamp<=$maxstamp ) {
			setcookie($set['main']['cookie_pre'].'_checkage', 1);
			return;
		}
		else {
			message($apx->lang->get('MSG_TOOYOUNG'), HTTPDIR);
			require(BASEDIR.'lib/_end.php');
		}
	}
	
	//Nachricht anzeigen
	//header('HTTP/1.0 403 Forbidden');
	tmessage('checkage', array(), 'main');
	require(BASEDIR.'lib/_end.php');
}


?>