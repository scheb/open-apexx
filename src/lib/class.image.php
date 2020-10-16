<?php

// Image Function Class
// ====================

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class image
{
    public $jpgquality = 90;

    ////////////////////////////////////////////////////////////////////////////////////////// VORDEFINITIONEN

    //Startposition eines Bildes holen
    public function getpos($pos, $sx, $sy, $wx = 0, $wy = 0)
    {
        //  1: oben links          4: mitte links          7: unten links
        //  2: oben zentriert      5: mitte zentriert      8: unten zentriert
        //  3: oben rechts         6: mitte rechts         9: unten rechts

        $posx = [0, round($sx / 2) - round($wx / 2), $sx - $wx];
        $posy = [0, round($sy / 2) - round($wy / 2), $sy - $wy];

        switch ($pos) {
    case 1: return [$posx[0], $posy[0]];
    case 2: return [$posx[1], $posy[0]];
    case 3: return [$posx[2], $posy[0]];
    case 4: return [$posx[0], $posy[1]];
    case 5: return [$posx[1], $posy[1]];
    case 6: return [$posx[2], $posy[1]];
    case 7: return [$posx[0], $posy[2]];
    case 8: return [$posx[1], $posy[2]];
    case 9: return [$posx[2], $posy[2]];
    default: return [$posx[1], $posy[1]];
    }
    }

    //Resize holen
    public function getresize($px, $py, $x, $y)
    {
        $xtoy = $px / $py;

        if (0 == $x) {
            return [round($y * $xtoy), $y];
        }
        if (0 == $y) {
            return [$x, round($x / $xtoy)];
        }
        $newx = $x;
        $newy = round($x / $xtoy);

        if ($newy > $y) {
            $newx = round($y * $xtoy);
            $newy = $y;
        }

        return [$newx, $newy];
    }

    ////////////////////////////////////////////////////////////////////////////////////////// HAUPTFUNKTIONEN

    //ImageCreate
    public function getimage($image, $upload = true)
    {
        if ($upload) {
            $image = BASEDIR.getpath('uploads').$image;
        }
        if (!file_exists($image)) {
            echo'Datei "'.$image.'" wurde nicht gefunden!';
        }

        $size = getimagesize($image);

        switch ($size[2]) {
        //Wandle GIF zu JPG um...
        case 1:
            $pic = imagecreatetruecolor($size[0], $size[1]);
            $giffile = imagecreatefromgif($image);
            imagecopy($pic, $giffile, 0, 0, 0, 0, $size[0], $size[1]);
            imagedestroy($giffile);

            return [$pic, 'JPG'];
        case 2:
            return [imagecreatefromjpeg($image), 'JPG']; //JPG
        case 3:
            $imgsource = imagecreatefrompng($image);
            imagealphablending($imgsource, false);

            return [$imgsource, 'PNG'];  //PNG
        default:
            return [false, false];
    }
    }

    //Wasserzeichen einfügen
    public function watermark(&$image, $source, $position, $transp)
    {
        static $watersource,$watertype;

        if (!$watersource) {
            list($watersource, $watertype) = $this->getimage(BASEDIR.$source, false);
        }
        if (false === $watersource || false === $watertype) {
            return $image;
        }
        list($posx, $posy) = $this->getpos($position, imagesx($image), imagesy($image), imagesx($watersource), imagesy($watersource));

        imagealphablending($image, true);
        if ('PNG' == $watertype) {
            imagecopy($image, $watersource, $posx, $posy, 0, 0, imagesx($watersource), imagesy($watersource));
        } else {
            imagecopymerge($image, $watersource, $posx, $posy, 0, 0, imagesx($watersource), imagesy($watersource), intval($transp));
        }

        return $image;
    }

    //Bild skalieren
    public function resize(&$image, $x, $y, $quality = false, $fit = false)
    {
        $px = imagesx($image);
        $py = imagesy($image);

        //Wenn Bild kleiner als Skalierung
        if (!$fit && $px <= $x && $py <= $y) {
            return $image;
        }
        if (!$fit) {
            list($tx, $ty) = $this->getresize($px, $py, $x, $y);
        } //Bild strecken
    else { //Bildverhältnis beibehalten
        $tx = $x;
        $ty = $y;
    }

        $resized = imagecreatetruecolor($tx, $ty);
        $background = imagecolorallocate($resized, 0, 0, 0);
        imagecolortransparent($resized, $background);
        imagealphablending($resized, false);

        if ($quality) {
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $tx, $ty, $px, $py);
        } else {
            imagecopyresized($resized, $image, 0, 0, 0, 0, $tx, $ty, $px, $py);
        }

        return $resized;
    }

    //Bild speichern
    public function saveimage($source, $type, $file)
    {
        if (false !== $source) {
            if ('PNG' == $type) {
                imagesavealpha($source, true);
                imagepng($source, BASEDIR.getpath('uploads').$file);
            } else {
                imagejpeg($source, BASEDIR.getpath('uploads').$file, $this->jpgquality);
            }
        }

        @chmod(BASEDIR.getpath('uploads').$file, 0777);
    }
} //END CLASS
