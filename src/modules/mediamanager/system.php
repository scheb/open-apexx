<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Inlinescreens
function mediamanager_inline($text) {
	//if ( strpos($text,'{IMAGE(')===false ) return $text;
	$found=preg_match('#{IMAGE\( *[0-9]+ *\)}#s',$text);
	if ( !$found ) return $text;
	
	//Image IDs auslesen
	preg_match_all('#{IMAGE\(( *[0-9]+ *)\)}#se',$text,$match);
	$imageids=$match[1];
	
	if ( count($imageids) ) {
		//Puffer auslesen
		$tempcontent=ob_get_contents();
		ob_end_clean();
		
		//Ersetzung durchführen
		$replaceme=mediamanager_inline_code($imageids);
		$text=strtr($text,$replaceme);
		
		//Puffer wieder starten
		ob_start();
		echo $tempcontent;
	}
	
	return $text;
}



//Inline-Codes ersetzen
function mediamanager_inline_code($ids) {
	global $set,$db,$apx;
	$tmpl=new tengine;
	
	$data=$db->fetch("SELECT id,picture,popup,text,align FROM ".PRE."_inlinescreens WHERE id IN (".implode(',',$ids).")");
	if ( !count($data) ) array();
	
	//Codes generieren
	ob_start();
	$code=array();
	foreach ( $data AS $res ) {
		if ( $res['popup'] ) {
			$size=getimagesize(BASEDIR.getpath('uploads').$res['popup']);
			$tmpl->assign('POPUP',"javascript:popuppic('misc.php?action=picture&amp;pic=".$res['popup']."','".$size[0]."','".$size[1]."',0);");
		}
		
		$tmpl->assign('ID',$res['id']);
		$tmpl->assign('PICTURE',HTTPDIR.getpath('uploads').$res['picture']);
		$tmpl->assign('FULLSIZE',HTTPDIR.getpath('uploads').$res['popup']);
		$tmpl->assign('TEXT',$res['text']);
		$tmpl->assign('ALIGN',$res['align']);
		
		$tmpl->parse('inlinepic','main');
		$imagecode=ob_get_contents();
		ob_clean();
		
		$code[$res['id']]=$imagecode;
	}
	ob_end_clean();
	
	//Replacement
	$replace=array();
	foreach ( $ids AS $id ) {
		$replace['{IMAGE('.$id.')}']=$code[intval($id)];
	}
	
	return $replace;
}


?>