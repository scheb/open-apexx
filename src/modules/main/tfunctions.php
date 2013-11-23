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



//Datum generieren
function main_mkdate($pattern='d.m.Y - H:i:s',$time=false) {
	global $apx,$set;
	static $yesterday,$today,$tomorrow;
	
	if ( $time===false ) $time=time();
	$time=(int)$time;
	
	//Timestamps
	if ( !isset($yesterday) ) $yesterday=date('d/m/Y',(time()-24*3600-TIMEDIFF));
	if ( !isset($today) ) $today=date('d/m/Y');
	if ( !isset($tomorrow) ) $tomorrow=date('d/m/Y',(time()+24*3600-TIMEDIFF));
	$stamp=date('d/m/Y',$time-TIMEDIFF);
	
	//Gestern/Heute/Morgen
	if ( strtolower($pattern)=='date' && $stamp==$yesterday ) { echo '<b>'.$apx->lang->get('YESTERDAY').'</b>'; return; }
	if ( strtolower($pattern)=='date' && $stamp==$today ) { echo '<b>'.$apx->lang->get('TODAY').'</b>'; return; }
	if ( strtolower($pattern)=='date' && $stamp==$tomorrow ) { echo '<b>'.$apx->lang->get('TOMORROW').'</b>'; return; }
	
	//Standard-Pattern verwenden
	if ( strtolower($pattern)=='date' ) $pattern=$set['main']['dateformat'];
	if ( strtolower($pattern)=='time' ) $pattern=$set['main']['timeformat'];
	
	$string=date($pattern,$time-TIMEDIFF);
	if ( strpos($pattern,'F')!==false || strpos($pattern,'M')!==false ) $string=getcalmonth($string);
	if ( strpos($pattern,'l')!==false || strpos($pattern,'D')!==false ) $string=getweekday($string);
	
	echo $string;
}



//Suchefeld ausgeben
function main_searchbox($module='', $template='search') {
	global $apx;
	$tmpl=new tengine;
	$apx->lang->drop('search_basic','main');
	
	if ( $apx->is_module($module) ) $tmpl->assign('SEARCHIN',$module);
	$tmpl->assign('POSTTO',mklink('search.php','search.html'));
	$tmpl->parse('functions/'.$template,'main');
}



//Druckversion
function main_printlink() {
	$url=str_replace('&','&amp;',$_SERVER['REQUEST_URI']);
	if ( strpos($url,'?')!==false ) $url.='&amp;print=1';
	else $url.='?print=1';
	echo $url;
}



//Seite empfehlen
function main_telllink() {
	$url=str_replace('&','&amp;',$_SERVER['REQUEST_URI']);
	
	//Add ?tell=1
	if ( strpos($url, 'tell=1')===false ) {
		if ( strpos($url,'?')!==false ) $url.='&amp;tell=1';
		else $url.='?tell=1';
	}
	
	echo $url;
}



//Shorttext
function main_shorttext($text,$length) {
	$length=(int)$length;
	echo shorttext($text,$length);
}



//Titlebar setzen
function main_set_titlebar($title='') {
	if ( !$title ) return;
	titlebar($title);
}



//Headline setzen
function main_set_headline($title='',$link='') {
	global $apx;
	static $reset;
	
	//Clean
	if ( !isset($reset) ) {
		$apx->tmpl->headline=array();
		$reset = true;
	}
	
	//Set
	headline($title,$link);
}



//Design erzwingen
function main_set_design($designid='') {
	global $apx;
	if ( !$designid ) return;
	$apx->tmpl->loaddesign($designid);
}



//Snippet ausgeben
function main_snippet($id=0) {
	global $apx,$db;
	$id = (int)$id;
	if ( !$id ) return;
	list($code) = $db->first("SELECT code FROM ".PRE."_snippets WHERE id='".$id."' LIMIT 1");
	echo $code;
}



//Sektionen
function main_sections($template='sections') {
	global $apx,$set,$db;
	$tmpl = new tengine();
	
	$secdata = array();
	if ( count($apx->sections) ) {
		foreach ( $apx->sections AS $id => $info ) {
			++$i;
			$secdata[$i]['ID'] = $id;
			$secdata[$i]['TITLE'] = $info['title'];
			$secdata[$i]['VIRTUALDIR'] = $info['virtual'];
			$secdata[$i]['LINK'] = mklink('index.php','index.html',$id);
			
		}
	}
	
	$tmpl->assign('SECTION',$secdata);
	$tmpl->parse('functions/'.$template,'main');
}

?>