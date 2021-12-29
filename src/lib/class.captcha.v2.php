<?php

/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2006, Christian Scheb            |
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

var $bgnoise = false;
var $width = 60;
var $height = 25;

var $size = array(16);
var $font = array(1,2,3,4,5,6,7,8,9);
//var $charspace = array(2,3,4,5,6,8,9);
var $charspace = '2345689abcdefghkmpqrstwxyzABCDEFGHKMPQRSTWXYZ';
var $angle = array(
	'min' => -30,
	'max' => 30
);

var $colorsheme;
var $colorconfig = array(
	0 => array(
		'background_min' => 200,
		'background_max' => 255,
		'middleground_min' => 100,
		'middleground_max' => 200,
		'foreground_min' => 50,
		'foreground_max' => 130
	),
	1 => array(
		'background_min' => 0,
		'background_max' => 100,
		'middleground_min' => 50,
		'middleground_max' => 150,
		'foreground_min' => 150,
		'foreground_max' => 200
	)
);



//Konstruktor
function __construct() {
	srand((double)microtime()*1000000);
}



//Captcha generieren
function generate() {
	global $db;

	//Cache leer machen
	//$this->clear_cache();

	//Farb-Modus (schwarz oder weiß)
	$this->colorsheme = $this->colorconfig[mt_rand(0,1)];

	//Bild erzeugen
	$picture=imagecreatetruecolor($this->width,$this->height);
	$picturetype='PNG';

	//Hintergrundrauschen erzeugen
	if ( $this->bgnoise ) {
		for ( $line=0; $line<=$this->height; $line++ ) {
			for ( $col=0; $col<=$this->width; $col++ ) {
				$color = $this->get_background($picture);
				imagesetpixel($picture,$col,$line,$color);
			}
		}
	}

	//Alternativ einfarbiges Hintergrund
	else {
		$bgColor = $this->get_background($picture);
		imagefill($picture,0,0,$bgColor);
	}

	//Linienzüge
	/*for ( $i=0; $i<mt_rand(5,10); $i++ ) {

		//Linien-Attribute
		$color = $this->get_middleground($picture);
		imagesetthickness($picture,mt_rand(3,7));

		//Startpunkt
		$lastx = mt_rand(10,$this->width-10);
		$lasty = mt_rand(10,$this->height-10);

		//Linienzug zeichnen
		for ( $j=0; $j<=mt_rand(2,4); $j++ ) {
			$thisx = mt_rand($lastx-15,$lastx+15);
			$thisy = mt_rand($lasty-30,$lasty+30);
			imageline($picture,$lastx,$lasty,$thisx,$thisy,$color);
			$lastx = $thisx;
			$lasty = $thisy;
		}
	}*/

	//Vorbereiten für Zahlenprint
	shuffle($this->font);
	$step = $this->width/3;
	$printx = $this->width/6-6; //5 Zeichen
	$value = 0;

	/*shuffle($this->charspace);
	$number1 = $this->charspace[0];
	//$operator = mt_rand(0,1) ? '+' : '-';
	$operator = '+';
	do {
		shuffle($this->charspace);
		$number2 = $this->charspace[0];
	}
	while ( $number2==$number1 );
	if ( $operator=='-' ) {
		if ( $number1<$number2 ) {
			$temp = $number1;
			$number1 = $number2;
			$number2 = $temp;
		}
		$value = $number1 - $number2;
	}
	else {
		$value = $number1 + $number2;
	}
	$chars = array($number1, $operator, $number2);*/

	$chars = array();
	for ( $i=0; $i<3; $i++ ) {
		$charId = mt_rand(0, strlen($this->charspace)-1);
		$chars[] = $this->charspace[$charId];
	}
	$value = implode('', $chars);

	//Zahlen schreiben
	for ( $i=0; $i<3; $i++ ) {

		//$char = $this->charspace[array_rand($this->charspace)];
		$char = $chars[$i];
		//$value += $char*pow(10,4-$i);
		$fontfile = BASEDIR.'lib/captcha/'.$this->font[$i].'.ttf';
		$fontsize = $this->size[array_rand($this->size)];
		//$angle = mt_rand($this->angle['min'],$this->angle['max']);
		$angle = 0;

		//Operator
		/*if ( $i==1 ) {
			$fontsize = round($fontsize*1.3);
		}*/

		//Bounding-Box
		$bbox = imagettfbbox($fontsize,0,$fontfile,$char);
		$charwidth = abs($bbox[4]-$bbox[0]);
		$charheight = abs($bbox[1]-$bbox[5]);

		//Y-Position zufällig wählen
		//$printy = mt_rand($charheight+15,$this->height); //+4 zum Fehlerausgleich
		$printy = 20;
		/*
		//Verschiebung durch Winkel ausgleichen
		if ( $angle>=0 ) {
			$basex = $printx-$charwidth/2+sin(deg2rad($angle))*$charwidth;
			$basey = $printy;
		}
		else {
			$basex = $printx-$charwidth/2+sin(deg2rad($angle))*($charheight/2-$charwidth/2);
			$basey = $printy+sin(deg2rad($angle))*$charheight;
		}
		*/
		//Zeichen ausgeben
		$color = $this->get_foreground($picture);
		//imagettftext($picture,$fontsize,$angle,round($basex),round($basey),$color,$fontfile,$char);
		imagettftext($picture,$fontsize,$angle,$printx,$printy,$color,$fontfile,$char);

		$printx += $step;
	}

	//Sprenkeln
	/*for ( $line=0; $line<=$this->height; $line++ ) {
		for ( $col=0; $col<=$this->width; $col++ ) {
			if ( !mt_rand(0,3) ) {
				$color = $this->get_background($picture);
				imagesetpixel($picture,$col,$line,$color);
			}
		}
	}*/

	$hash=md5($value.microtime().$value);
	//$this->saveimage($picture,$picturetype,'temp/captcha_'.$hash.'.png');
	$db->query("INSERT INTO ".PRE."_captcha (code,hash,time) VALUES ('".$value."','".$hash."','".time()."')");

	$code = $this->createCaptchaCode($picture, $bgColor).'<input type="hidden" name="captcha_hash" value="'.$hash.'" />';
	//$code='<img src="'.HTTPDIR.getpath('uploads').'temp/captcha_'.$hash.'.png" alt="" style="vertical-align:middle;" /><input type="hidden" name="captcha_hash" value="'.$hash.'" />';
	return $code;
}



//HTML-Code für Captcha erzeugen
function createCaptchaCode($picture, $bgColor) {
	$captchaClass = strtolower(random_string(1, 'abcdefghijklmopqrstuvwxyz').random_string(rand(15, 30)));
	$code = '<style type="text/css">.'.$captchaClass.'{display:inline-block;width:'.$this->width.'px;height:'.$this->height.'px;vertical-align:top;font-size:0;background:'.$this->hexcolor($bgColor).'}.'.$captchaClass.' b{display:inline-block;width:1px;height:1px;vertical-align:baseline;overflow:hidden;}</style>';
	$code .= '<span class="'.$captchaClass.'">';
	$code .= $this->image2Html($picture, $bgColor);
	$code .= '</span>';
	return $code;
}



//HTML-Code für Bild erzeugen
function image2Html($picture, $bgColor) {
	$im = $picture;
	$intWidth = $this->width;
	$intHeight = $this->height;
	$bgColor = $this->hexcolor($bgColor);
	for( $y = 0; $y<$intHeight; $y++ ) {
		for ( $x = 0; $x < $intWidth; $x++ ) {
			$beginx = $x;
			$rgb = imagecolorat($im, $x, $y);
			$color_old = $this->hexcolor($rgb);
			$color = $color_old;

			do {
				$x++;
				$rgb = imagecolorat($im, $x, $y);
				$color = $this->hexcolor($rgb);
			}
			while( $x < $intWidth && $color==$color_old );
			--$x;

			$width = $x - $beginx + 1;
			if ( $color_old==$bgColor ) {
				if ( $width <= 1 ) {
					$html .= '<b></b>';
				}
				else {
					$html .= '<b style="width:'.$width.'px"></b>';
				}
			}
			else {
				if ( $width <= 1 ) {
					$html .= '<b style="background:'.$color_old.'"></b>';
				}
				else {
					$html .= '<b style="background:'.$color_old.';width:'.$width.'px"></b>';
				}
			}
		}

	}
	return $html;
}



//Hexadezimalcode erzeugen
function hexcolor($c) {
	$r = ($c >> 16) & 0xFF;
	$g = ($c >> 8) & 0xFF;
	$b = $c & 0xFF;
	return '#'.str_pad(dechex($r), 2, '0', STR_PAD_LEFT).str_pad(dechex($g), 2, '0', STR_PAD_LEFT).str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}



//Zufällige Hintergrundfarbe
function get_background( & $picture ) {
	$color = imagecolorallocate(
		$picture,
		mt_rand($this->colorsheme['background_min'],$this->colorsheme['background_max']),
		mt_rand($this->colorsheme['background_min'],$this->colorsheme['background_max']),
		mt_rand($this->colorsheme['background_min'],$this->colorsheme['background_max'])
	);
	return $color;
}



//Zufällige Hintergrundfarbe
function get_middleground( & $picture ) {
	$color = imagecolorallocate(
		$picture,
		mt_rand($this->colorsheme['middleground_min'],$this->colorsheme['middleground_max']),
		mt_rand($this->colorsheme['middleground_min'],$this->colorsheme['middleground_max']),
		mt_rand($this->colorsheme['middleground_min'],$this->colorsheme['middleground_max'])
	);
	return $color;
}



//Zufällige Vordergrundfarbe
function get_foreground( & $picture ) {
	$color = imagecolorallocate(
		$picture,
		mt_rand($this->colorsheme['foreground_min'],$this->colorsheme['foreground_max']),
		mt_rand($this->colorsheme['foreground_min'],$this->colorsheme['foreground_max']),
		mt_rand($this->colorsheme['foreground_min'],$this->colorsheme['foreground_max'])
	);
	return $color;
}



//Code auf Korrektheit prüfen
function check() {
	global $db;
	if ( isset($_POST['capcha']) && !isset($_POST['captcha']) ) $_POST['captcha']=$_POST['capcha'];
	if ( !$_POST['captcha'] ) return true;
	list($code)=$db->first("SELECT code FROM ".PRE."_captcha WHERE hash='".addslashes($_POST['captcha_hash'])."' LIMIT 1");
	if ( strtolower($code)!=strtolower($_POST['captcha']) ) return true;
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
	$data=$db->fetch("SELECT id,hash FROM ".PRE."_captcha WHERE time<='".(time()-3600)."'");
	if ( count($data) ) {
		$idlist=array();
		foreach ( $data AS $res ) {
			@unlink(BASEDIR.getpath('uploads').'temp/captcha_'.$res['hash'].'.png');
			if ( !file_exists(BASEDIR.getpath('uploads').'temp/captcha_'.$res['hash'].'.png') ) $idlist[]=$res['id'];
		}
		$db->query("DELETE FROM ".PRE."_captcha WHERE id IN (".implode(',',$idlist).")");
	}
}


} //END CLASS

?>
