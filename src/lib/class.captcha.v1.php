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


# captcha Function Class
# =====================

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Image-Klasse laden
require_once(BASEDIR.'lib/class.image.php');


//Klasse beginn hier
class captcha extends image {

var $color = 0; //0 = wechselnd, 1 = hell, 2 = dunkel
var $width = 140;
var $height = 40;

//captcha generieren
function generate() {
	global $db;
	
	//Cache leer machen
	$this->clear_cache();
	
	//Zufallsgenerator
	srand((double)microtime()*1000000);
	
	$picture=imagecreatetruecolor($this->width,$this->height);
	$picturetype='PNG';
	
	$start=-13;
	$sizerand=array(22,23,24,25,26);
	$fontrand=array(1,2,3,4,5);
	$charspace=array(2,3,4,5,6,8,9);
	shuffle($fontrand);
	$colordiff=90;
	
	//Dunkler Text
	if ( ( $this->color==0 && rand(0,1)==1 ) || $this->color==1 ) {
		$color=imagecolorallocate($picture,rand(0,$colordiff),rand(0,$colordiff),rand(0,$colordiff));
		$background=imagecolorallocate($picture,255,255,255);
	}
	
	//Heller Text
	else {
		$color=imagecolorallocate($picture,rand(255-$colordiff,255),rand(255-$colordiff,255),rand(255-$colordiff,255));
		$background=imagecolorallocate($picture,0,0,0);
	}
	
	imagefill($picture,0,0,$background);
	
	//Sprenkeln
	for ( $col=rand(0,3); $col<=$this->width; $col+=rand(2,3) ) {
		for ( $line=rand(0,3); $line<=$this->height; $line+=rand(2,3) ) {
			imagesetpixel($picture,$col,$line,$color);
		}
	}
	
	//Zahlen schreiben
	for ( $i=0; $i<5; $i++ ) {
		$char=$charspace[array_rand($charspace)];
		$value+=$char*pow(10,4-$i);
		$angle=rand(-30,30);
		$start=$start+25;
		$fontfile=BASEDIR.'lib/captcha/'.$fontrand[$i].'.ttf';
		$fontsize=$sizerand[array_rand($sizerand)];
		
		//Textbreite feststellen
		$box=imagettfbbox($fontsize,$angle,$fontfile,$char);
		$textwidth=$box[4]-$box[0];
		$textheight=$box[1]-$box[5];
		
		$top=rand($textheight+2,$this->height-4);
		$addwidth=round((18-$textwidth)/2);
		
		//DEBUG
		//echo $char.': S:'.$fontsize.', A:'.$angle.', F:'.$fontfile.', H:'.$textheight.', T:'.$top.'<br />';
		
		imagettftext($picture,$fontsize,$angle,$start+$addwidth,$top,$color,$fontfile,$char);
	}
	
	//Sprenkeln
	for ( $line=rand(0,2); $line<=$this->height; $line+=rand(2,3) ) {
		for ( $col=rand(0,2); $col<=$this->width; $col+=rand(2,3) ) {
			imagesetpixel($picture,$col,$line,$background);
		}
	}
	
	$hash=md5($value.microtime().$value);
	$this->saveimage($picture,$picturetype,'temp/captcha_'.$hash.'.png');
	$db->query("INSERT INTO ".PRE."_captcha (code,hash,time) VALUES ('".$value."','".$hash."','".time()."')");
	
	$code='<img src="'.HTTPDIR.getpath('uploads').'temp/captcha_'.$hash.'.png" alt="" style="vertical-align:middle;width:140px;height:40px;" /><input type="hidden" name="captcha_hash" value="'.$hash.'" />';
	return $code;
}



//Code auf Korrektheit prüfen
function check() {
	global $db;
	if ( isset($_POST['capcha']) && !isset($_POST['captcha']) ) $_POST['captcha']=$_POST['capcha'];
	if ( !$_POST['captcha'] ) return true;
	list($code)=$db->first("SELECT code FROM ".PRE."_captcha WHERE hash='".addslashes($_POST['captcha_hash'])."' LIMIT 1");
	if ( $code!=$_POST['captcha'] ) return true;
	return false;
}



//Verwendetes Captcha löschen
function remove() {
	global $db;
	$res=$db->fetch("SELECT hash FROM ".PRE."_captcha WHERE hash='".addslashes($_POST['captcha_hash'])."' LIMIT 1");
	if ( $res['hash'] ) return;
	@unlink(BASEDIR.getpath('uploads').'temp/captcha_'.$res['hash'].'.png');
	$db->query("DELETE FROM ".PRE."_captcha WHERE hash='".addslashes($_POST['captcha_hash'])."' LIMIT 1");
}



//Cache löschen
function clear_cache() {
	global $db;
	$data=$db->fetch("SELECT hash FROM ".PRE."_captcha WHERE time<='".(time()-3600)."'");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			@unlink(BASEDIR.getpath('uploads').'temp/captcha_'.$res['hash'].'.png');
		}
	}
	$db->query("DELETE FROM ".PRE."_captcha WHERE time<='".(time()-3600)."'");
}


} //END CLASS

?>