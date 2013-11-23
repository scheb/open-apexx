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


# Image Function Class
# ====================

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class image {

var $jpgquality=90;


////////////////////////////////////////////////////////////////////////////////////////// VORDEFINITIONEN


//Startposition eines Bildes holen
function getpos($pos,$sx,$sy,$wx=0,$wy=0) {
//  1: oben links          4: mitte links          7: unten links
//  2: oben zentriert      5: mitte zentriert      8: unten zentriert
//  3: oben rechts         6: mitte rechts         9: unten rechts

$posx=array(0,round($sx/2)-round($wx/2),$sx-$wx);
$posy=array(0,round($sy/2)-round($wy/2),$sy-$wy);

	switch ( $pos ) {
	case 1: return array($posx[0],$posy[0]);
	case 2: return array($posx[1],$posy[0]);
	case 3: return array($posx[2],$posy[0]);
	
	case 4: return array($posx[0],$posy[1]);
	case 5: return array($posx[1],$posy[1]);	
	case 6: return array($posx[2],$posy[1]);
	
	case 7: return array($posx[0],$posy[2]);
	case 8: return array($posx[1],$posy[2]);
	case 9: return array($posx[2],$posy[2]);
	
	default: return array($posx[1],$posy[1]);
	}
}


//Resize holen
function getresize($px,$py,$x,$y) {
	
	$xtoy=$px/$py;
	
	if ( $x==0 ) return array(round($y*$xtoy),$y);
	elseif ( $y==0 ) return array($x,round($x/$xtoy));
	
	$newx=$x;
	$newy=round($x/$xtoy);
	
	if ( $newy>$y ) {
		$newx=round($y*$xtoy);
		$newy=$y;
	}
	
	return array($newx,$newy);
}


////////////////////////////////////////////////////////////////////////////////////////// HAUPTFUNKTIONEN


//ImageCreate
function getimage($image,$upload=true) {
	if ( $upload ) $image=BASEDIR.getpath('uploads').$image;
	if ( !file_exists($image) ) {
		echo'Datei "'.$image.'" wurde nicht gefunden!';
	}
	
	$size=getimagesize($image);
	
	switch ( $size[2] ) {

		//Wandle GIF zu JPG um...
		case 1:
			$pic=imagecreatetruecolor($size[0],$size[1]);
			$giffile=imagecreatefromgif($image);
			imagecopy($pic,$giffile,0,0,0,0,$size[0],$size[1]);
			imagedestroy($giffile); 
			return array($pic,'JPG');
		
		case 2:
			return array(imagecreatefromjpeg($image),'JPG'); //JPG
		
		case 3:
			$imgsource = imagecreatefrompng($image);
			imagealphablending($imgsource, false);
			return array($imgsource,'PNG');  //PNG
		
		default:
			return array(false,false);
	}
}


//Wasserzeichen einfügen
function watermark(&$image,$source,$position,$transp) {
	static $watersource,$watertype;
	
	if ( !$watersource ) list($watersource,$watertype)=$this->getimage(BASEDIR.$source,false);
	if ( $watersource===false || $watertype===false ) return $image;
	
	list($posx,$posy)=$this->getpos($position,imagesx($image),imagesy($image),imagesx($watersource),imagesy($watersource));
	
	imagealphablending($image, true);
	if ( $watertype=='PNG' ) imagecopy($image,$watersource,$posx,$posy,0,0,imagesx($watersource),imagesy($watersource));
	else imagecopymerge($image,$watersource,$posx,$posy,0,0,imagesx($watersource),imagesy($watersource),intval($transp));
	
	return $image;
}


//Bild skalieren
function resize(&$image,$x,$y,$quality=false,$fit=false) {
	$px=imagesx($image);
	$py=imagesy($image);
	
	//Wenn Bild kleiner als Skalierung
	if ( !$fit && $px<=$x && $py<=$y ) return $image;
	
	if ( !$fit ) list($tx,$ty)=$this->getresize($px,$py,$x,$y); //Bild strecken
	else { //Bildverhältnis beibehalten
		$tx=$x;
		$ty=$y;
	}
	
	$resized = imagecreatetruecolor($tx,$ty);
	$background = imagecolorallocate($resized, 0, 0, 0);
	imagecolortransparent($resized, $background);
	imagealphablending($resized, false);
	
	if ( $quality ) imagecopyresampled($resized,$image,0,0,0,0,$tx,$ty,$px,$py);
	else imagecopyresized($resized,$image,0,0,0,0,$tx,$ty,$px,$py);
	
	return $resized;
}


//Bild speichern
function saveimage($source,$type,$file) {

	if ( $source!==false ) {
		if ( $type=='PNG' ) {
			imagesavealpha($source,true);
			imagepng($source,BASEDIR.getpath('uploads').$file);
		}
		else imagejpeg($source,BASEDIR.getpath('uploads').$file,$this->jpgquality);
	}
	
	@chmod(BASEDIR.getpath('uploads').$file,0777);
}


} //END CLASS

?>