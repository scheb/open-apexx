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



//Glossar-Wörter in einem Text hervorheben
function glossar_highlight($text,$module=false,$ignore=false) {
	global $apx,$db,$set;
	static $highlights;
	$ignore = (int)$ignore;
	if ( !$set['glossar']['highlight'] || !$text ) return $text;
	
	$apx->lang->drop('highlights','glossar');
	
	$classname_word = 'glossar_highlight';
	$classname_title = 'glossar_info_title';
	$classname_text = 'glossar_info_text';
	$classname_readmore = 'glossar_info_readmore';
	
	//Daten auslesen
	if ( !isset($highlights) ) {
		$highlights = array();
		$data = $db->fetch("SELECT id,title,spelling,text FROM ".PRE."_glossar WHERE starttime!=0".iif($ignore, " AND id!='".$ignore."'"));
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$words = array();
				if ( $res['spelling'] ) $words = explode(',',strtolower($res['spelling']));
				$words[] = strtolower($res['title']);
				$words = array_unique(array_map('trim',$words));
				$link=mklink(
					'glossar.php?id='.$res['id'],
					'glossar,id'.$res['id'].urlformat($res['title']).'.html'
				);
				$content = '<div class="'.$classname_title.'"><a href="'.$link.'">'.$res['title'].'</a></div><div class="'.$classname_text.'">'.shorttext($res['text'],200).'</div><div class="'.$classname_readmore.'"><a href="'.$link.'">'.$apx->lang->get('READMORE').'</a></div>';
				$content = strtr(compatible_hsc($content), array(
					"\n" => ' ',
					"\r" => '',
					'\'' => '\\\'',
					'\\' => '\\\\'
				));
				$highlights[] = array(
					'words' => $words,
					'content' => $content
				);
			}
		}
	}
	
	//Text nach Highlights durchsuchen
	$lowertext = strtolower($text);
	foreach ( $highlights AS $element ) {
		$words = $element['words'];
		foreach ( $words AS $wkey => $word ) {
			if ( strpos($lowertext,strtolower($word))===false ) {
				unset($words[$wkey]);
			}
		}
		if ( !count($words) ) continue;
		$words = array_map('preg_quote',$words);
		$searchfor = implode('|',$words);
		$hover = 'Tip(\''.$element['content'].'\')';
		$text=preg_replace('#((<[^>]*)|($|[\s<>,.:;_!-])('.$searchfor.')([\s<>,.:;_!-]|$))#ie', '"\2"=="\1" ? glossar_stripslashes("\1") : glossar_stripslashes("\3")."<span class=\"'.$classname_word.'\" onmouseover=\"'.strtr($hover, array('\\'=>'\\\\')).'\">".glossar_stripslashes("\4")."</span>".glossar_stripslashes("\5")', $text);
		//$text=preg_replace('#((<[^>]*)|('.$searchfor.'))#ie', '"\2"=="\1" ? glossar_stripslashes("\1") : "<span class=\"'.$classname_word.'\" onmouseover=\"'.$hover.'\">".glossar_stripslashes("\3")."</span>"', $text);
	}
	
	return $text;
}



//Slashes vor ' entfernen
function glossar_stripslashes($text) {
	return str_replace("\\'","'",$text);
}


?>